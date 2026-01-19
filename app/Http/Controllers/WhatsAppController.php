<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WhatsAppSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\WhatsappSessionService;
use App\Services\WhatsappBufferService;
use App\Jobs\ProcessWhatsappBufferJob;
use App\Jobs\ProcessWhatsappCheckinJob;

class WhatsAppController extends Controller
{
    /**
     * Show WhatsApp connection page
     */
    public function connect(Request $request): Response
    {
        $user = $request->user();
        return Inertia::render('WhatsApp/Connect', [
            'user' => [
                'phone' => $user->phone,
                'whatsapp_number' => $user->whatsapp_number,
            ],
        ]);
    }
    
    /**
     * Registrar número e gravar sessão no Redis
     */
    public function store(Request $request, WhatsappSessionService $whatsappSession): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'phone_number' => [
                'required', 
                'string', 
                'regex:/^[0-9]{10,15}$/',
            ],
        ]);
        $cleanPhone = preg_replace('/\D/', '', $validated['phone_number']);
        if (strlen($cleanPhone) === 11 && !str_starts_with($cleanPhone, '55')) {
            $cleanPhone = '55' . $cleanPhone;
        }
        $user->update(['whatsapp_number' => $cleanPhone]);
        
        $whatsappSession->store($cleanPhone, $user->id, $user->is_pro ? 'pro' : 'free');
        
        $botNumber = env('WHATSAPP_BOT_NUMBER', '5571993676365');
        $whatsappUrl = 'https://wa.me/' . $botNumber . '?text=Oi%20DOPA%20Check';
        
        return response()->json([
            'success' => true,
            'whatsapp_url' => $whatsappUrl,
            'message' => 'WhatsApp conectado com sucesso!'
        ]);
    }
    
    /**
     * Disconnect WhatsApp session
     */
    public function disconnect(Request $request)
    {
        $user = $request->user();
        $service = app(WhatsappSessionService::class);

        if (!$service->get($user->whatsapp_number)) {
            return response()->json(['error' => 'Nenhuma sessão WhatsApp encontrada.'], 404);
        }

        $service->remove($user->whatsapp_number);

        return response()->json(['success' => true]);
    }
    
    /**
     * Get WhatsApp session status (API)
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $service = app(WhatsappSessionService::class);

        $sessionData = $service->get($user->whatsapp_number);

        if (!$sessionData) {
            return response()->json([
                'connected' => false,
                'status' => 'not_configured',
                'session' => null,
                'bot_number' => null,
                'whatsapp_link' => null,
                'last_activity' => null,
                'stats' => [
                    'message_count' => 0,
                    'checkin_count' => 0
                ]
            ]);
        }

        return response()->json([
            'connected' => true,
            'status' => 'connected',
            'session' => [
                'phone_number' => $sessionData['phone_number'] ?? null,
                'bot_number' => $sessionData['bot_number'] ?? null,
                'is_active' => $sessionData['is_active'] ?? true,
                'last_activity' => $sessionData['last_activity'] ?? null,
                'message_count' => $sessionData['message_count'] ?? 0,
                'checkin_count' => $sessionData['checkin_count'] ?? 0,
                'connected_at' => $sessionData['connected_at'] ?? null,
            ],
            'bot_number' => $sessionData['bot_number'] ?? null,
            'whatsapp_link' => isset($sessionData['bot_number']) ? "https://wa.me/{$sessionData['bot_number']}" : null,
            'last_activity' => $sessionData['last_activity'] ?? null,
            'stats' => [
                'message_count' => $sessionData['message_count'] ?? 0,
                'checkin_count' => $sessionData['checkin_count'] ?? 0
            ]
        ]);
    }

    /**
     * Webhook para processar mensagens do WhatsApp
     */
    public function webhook(Request $request, WhatsappBufferService $bufferService): JsonResponse
    {
        $data = $request->all();
        $event = $data['event'] ?? null;
        $payload = $data['data'] ?? null;

        Log::info('WhatsApp webhook recebido', [
            'event' => $event,
            'instance' => $data['instance'] ?? null,
            'has_data' => is_array($payload),
            'destination' => $data['destination'] ?? null,
        ]);

        if ($event === 'messages.upsert' && is_array($payload)) {
            $items = $this->extractUpsertMessages($payload);

            if (empty($items)) {
                Log::warning('WhatsApp webhook messages.upsert sem mensagem parseável', [
                    'keys' => array_slice(array_keys($payload), 0, 30),
                    'has_messages_array' => isset($payload['messages']) && is_array($payload['messages']),
                ]);
            }

            foreach ($items as $item) {
                Log::info('WhatsApp mensagem parseada', [
                    'type' => $item['type'] ?? null,
                    'remote_jid' => $item['remote_jid'] ?? null,
                    'message_id' => $item['message_id'] ?? null,
                    'sender_phone' => $item['sender_phone'] ?? null,
                    'hashtags' => $item['hashtags'] ?? [],
                    'has_base64' => !empty($item['image_base64']),
                    'has_image_url' => !empty($item['image_url']),
                ]);

                // Para imagem com hashtag: processa check-in
                if (($item['type'] ?? null) === 'image' && !empty($item['hashtags'])) {
                    try {
                        $stored = null;
                        $shouldSkipStorage = $this->shouldSkipStorageBecauseAlreadyCheckedIn($item);

                        if (!$shouldSkipStorage) {
                            // Prioriza base64 se disponível, senão faz download via Evolution API
                            if (!empty($item['image_base64'])) {
                                $stored = $this->storeIncomingWhatsappImage(
                                    base64: (string) $item['image_base64'],
                                    mimeType: $item['image_mime'] ?? null,
                                    senderPhone: $item['sender_phone'] ?? 'unknown'
                                );
                            } elseif (!empty($item['image_url'])) {
                                $instance = $data['instance'] ?? null;
                                $messageId = $item['message_id'] ?? null;
                                
                                if ($instance && $messageId) {
                                    $stored = $this->downloadMediaFromEvolutionApi(
                                        instance: $instance,
                                        messageId: $messageId,
                                        mimeType: $item['image_mime'] ?? null,
                                        senderPhone: $item['sender_phone'] ?? 'unknown'
                                    );
                                }
                            }
                        }

                        ProcessWhatsappCheckinJob::dispatch([
                            'instance' => $data['instance'] ?? null,
                            'remote_jid' => $item['remote_jid'] ?? null,
                            'message_id' => $item['message_id'] ?? null,
                            'sender_phone' => $item['sender_phone'] ?? null,
                            'participant_jid' => $item['participant_jid'] ?? null,
                            'caption' => $item['content'] ?? null,
                            'hashtags' => $item['hashtags'] ?? [],
                            'has_image' => true,
                            'image_path' => $stored['path'] ?? null,
                            'image_mime' => $stored['mime'] ?? ($item['image_mime'] ?? null),
                            'image_url' => $item['image_url'] ?? null,
                        ]);

                        Log::info('WhatsApp check-in job enfileirado', [
                            'instance' => $data['instance'] ?? null,
                            'remote_jid' => $item['remote_jid'] ?? null,
                            'message_id' => $item['message_id'] ?? null,
                            'sender_phone' => $item['sender_phone'] ?? null,
                            'hashtags' => $item['hashtags'] ?? [],
                            'stored_path' => $stored['path'] ?? null,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Falha ao persistir/processar imagem do WhatsApp', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'remote_jid' => $item['remote_jid'] ?? null,
                            'message_id' => $item['message_id'] ?? null,
                            'sender_phone' => $item['sender_phone'] ?? null,
                        ]);
                    }

                    continue;
                }

                // Fluxo antigo (mensagens texto etc.) – mantém buffer
                if (!empty($item['sender_phone'])) {
                    $bufferService->addMessage($item['sender_phone'], [
                        'type' => $item['type'],
                        'content' => $item['content'],
                        'timestamp' => now()->toIso8601String(),
                    ]);
                    $bufferService->scheduleProcessingJob($item['sender_phone']);
                    Log::info('WhatsApp mensagem enfileirada no buffer', [
                        'sender_phone' => $item['sender_phone'],
                        'type' => $item['type'] ?? null,
                        'remote_jid' => $item['remote_jid'] ?? null,
                        'message_id' => $item['message_id'] ?? null,
                    ]);
                }
            }
        }
        return response()->json(['status' => 'ok']);
    }

    /**
     * Suporta formatos antigos e novos do EvolutionAPI/Baileys.
     */
    private function extractUpsertMessages(array $payload): array
    {
        $items = [];

        // Formato antigo
        if (!empty($payload['from']) && !empty($payload['body'])) {
            $phone = preg_replace('/\D/', '', (string) $payload['from']);
            if ($phone !== '') {
                $items[] = [
                    'sender_phone' => $phone,
                    'remote_jid' => null,
                    'message_id' => null,
                    'type' => (string) ($payload['type'] ?? 'text'),
                    'content' => (string) $payload['body'],
                    'hashtags' => $this->extractHashtags((string) $payload['body']),
                ];
            }
            return $items;
        }

        // Formato novo
        $messages = [];
        if (isset($payload['messages']) && is_array($payload['messages'])) {
            $messages = $payload['messages'];
        } elseif (isset($payload['message']) || isset($payload['key'])) {
            $messages = [$payload];
        }

        foreach ($messages as $msg) {
            if (!is_array($msg)) {
                continue;
            }

            $remoteJid = $msg['key']['remoteJid'] ?? null;
            if (!is_string($remoteJid) || $remoteJid === '') {
                continue;
            }

            $senderCandidate = null;
            $participantJid = null;
            if (str_contains($remoteJid, '@g.us')) {
                $senderCandidate = $msg['key']['participantAlt'] ?? $msg['key']['participant'] ?? null;
                $participantJid = $msg['key']['participant'] ?? $msg['key']['participantAlt'] ?? null;
            } else {
                $senderCandidate = $remoteJid;
            }

            $senderPhone = is_string($senderCandidate) ? preg_replace('/\D/', '', $senderCandidate) : '';
            if ($senderPhone === '') {
                continue;
            }

            if (($msg['key']['fromMe'] ?? false) === true) {
                continue;
            }

            $content = null;
            $type = 'text';
            $imageBase64 = null;
            $imageMime = null;
            $imageUrl = null;
            $messageId = $msg['key']['id'] ?? null;

            $m = $msg['message'] ?? [];
            if (is_array($m)) {
                if (isset($m['conversation']) && is_string($m['conversation'])) {
                    $content = $m['conversation'];
                } elseif (isset($m['extendedTextMessage']['text']) && is_string($m['extendedTextMessage']['text'])) {
                    $content = $m['extendedTextMessage']['text'];
                } elseif (isset($m['imageMessage'])) {
                    $type = 'image';
                    $content = $m['imageMessage']['caption'] ?? '';
                    
                    // Base64 (dev)
                    if (isset($m['base64']) && is_string($m['base64'])) {
                        $imageBase64 = $m['base64'];
                    }
                    
                    // URL (produção)
                    if (isset($m['imageMessage']['url']) && is_string($m['imageMessage']['url'])) {
                        $imageUrl = $m['imageMessage']['url'];
                    }
                    
                    if (isset($m['imageMessage']['mimetype']) && is_string($m['imageMessage']['mimetype'])) {
                        $imageMime = $m['imageMessage']['mimetype'];
                    }
                } elseif (isset($m['videoMessage']['caption']) && is_string($m['videoMessage']['caption'])) {
                    $type = 'video';
                    $content = $m['videoMessage']['caption'];
                } elseif (isset($m['audioMessage'])) {
                    $type = 'audio';
                    $content = '[audio]';
                }
            }

            if (is_string($content) && trim($content) !== '') {
                $items[] = [
                    'sender_phone' => $senderPhone,
                    'remote_jid' => $remoteJid,
                    'message_id' => is_string($messageId) ? $messageId : null,
                    'participant_jid' => is_string($participantJid) ? $participantJid : null,
                    'type' => $type,
                    'content' => $content,
                    'hashtags' => $this->extractHashtags($content),
                    'image_base64' => $imageBase64,
                    'image_mime' => $imageMime,
                    'image_url' => $imageUrl,
                ];
            }
        }

        return $items;
    }

    /**
     * Extrai hashtags do texto
     */
    private function extractHashtags(string $text): array
    {
        $tags = [];
        if (preg_match_all('/#([0-9A-Za-z_]+)/u', $text, $m)) {
            foreach ($m[1] as $raw) {
                $norm = strtolower((string) $raw);
                if ($norm !== '') {
                    $tags[] = $norm;
                }
            }
        }
        return array_values(array_unique($tags));
    }

    /**
     * Persiste imagem do WhatsApp (base64) no disk public
     */
    private function storeIncomingWhatsappImage(string $base64, ?string $mimeType, string $senderPhone): array
    {
        $raw = base64_decode($base64, true);
        if ($raw === false) {
            throw new \RuntimeException('Base64 inválido');
        }

        $ext = match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $sender = preg_replace('/\D/', '', $senderPhone);
        $filename = 'whatsapp_' . ($sender !== '' ? $sender : 'unknown') . '_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.' . $ext;
        $path = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/' . $filename;

        $ok = Storage::disk('public')->put($path, $raw);
        if (!$ok) {
            throw new \RuntimeException('Falha ao salvar imagem no storage');
        }

        Log::info('Imagem WhatsApp salva (base64)', [
            'path' => $path,
            'size' => strlen($raw),
            'mime' => $mimeType,
        ]);

        return [
            'path' => $path,
            'mime' => $mimeType,
        ];
    }

    /**
     * Faz download da mídia usando a Evolution API
     */
    private function downloadMediaFromEvolutionApi(string $instance, string $messageId, ?string $mimeType, string $senderPhone): array
    {
        $evolutionApiUrl = env('EVOLUTION_API_URL', 'https://whatsapp.dopacheck.com.br');
        $evolutionApiKey = env('EVOLUTION_API_KEY');

        if (!$evolutionApiKey) {
            throw new \RuntimeException('EVOLUTION_API_KEY não configurada');
        }

        $url = "{$evolutionApiUrl}/s3/getMediaUrl/{$instance}";

        Log::info('Solicitando download de mídia via Evolution API', [
            'url' => $url,
            'instance' => $instance,
            'message_id' => $messageId,
        ]);

        $response = Http::timeout(30)
            ->withHeaders([
                'apikey' => $evolutionApiKey,
            ])
            ->post($url, [
                'messageId' => $messageId,
            ]);

        if (!$response->successful()) {
            Log::error('Falha ao obter URL da mídia via Evolution API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Falha ao obter URL da mídia: HTTP ' . $response->status());
        }

        $data = $response->json();
        
        // A resposta pode variar dependendo da versão da Evolution API
        // Normalmente retorna { "mediaUrl": "...", "base64": "..." } ou similar
        $mediaUrl = $data['mediaUrl'] ?? $data['url'] ?? null;
        $base64Data = $data['base64'] ?? null;

        Log::info('Resposta da Evolution API para mídia', [
            'has_media_url' => !empty($mediaUrl),
            'has_base64' => !empty($base64Data),
            'response_keys' => array_keys($data),
        ]);

        // Se já veio base64 direto, usa ele
        if ($base64Data) {
            return $this->storeIncomingWhatsappImage(
                base64: $base64Data,
                mimeType: $mimeType,
                senderPhone: $senderPhone
            );
        }

        // Se veio URL, faz download
        if ($mediaUrl) {
            $mediaResponse = Http::timeout(30)->get($mediaUrl);

            if (!$mediaResponse->successful()) {
                throw new \RuntimeException('Falha ao fazer download da mídia: HTTP ' . $mediaResponse->status());
            }

            $raw = $mediaResponse->body();
            if (empty($raw)) {
                throw new \RuntimeException('Mídia baixada está vazia');
            }

            // Detecta mimetype se não foi fornecido
            if (!$mimeType) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($raw);
            }

            $ext = match ($mimeType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $sender = preg_replace('/\D/', '', $senderPhone);
            $filename = 'whatsapp_' . ($sender !== '' ? $sender : 'unknown') . '_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.' . $ext;
            $path = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/' . $filename;

            $ok = Storage::disk('public')->put($path, $raw);
            if (!$ok) {
                throw new \RuntimeException('Falha ao salvar imagem no storage');
            }

            Log::info('Imagem WhatsApp salva (Evolution API)', [
                'path' => $path,
                'size' => strlen($raw),
                'mime' => $mimeType,
                'media_url' => $mediaUrl,
            ]);

            return [
                'path' => $path,
                'mime' => $mimeType,
            ];
        }

        throw new \RuntimeException('Evolution API não retornou URL nem base64 da mídia');
    }

    /**
     * Verifica se já existe check-in hoje para evitar duplicação
     */
    private function shouldSkipStorageBecauseAlreadyCheckedIn(array $item): bool
    {
        try {
            $senderPhoneRaw = (string) ($item['sender_phone'] ?? '');
            $remoteJid = (string) ($item['remote_jid'] ?? '');
            $hashtags = is_array($item['hashtags'] ?? null) ? $item['hashtags'] : [];

            $senderPhone = preg_replace('/\D/', '', $senderPhoneRaw) ?: '';
            if (strlen($senderPhone) === 11 && !str_starts_with($senderPhone, '55')) {
                $senderPhone = '55' . $senderPhone;
            }
            if ($senderPhone === '' || $remoteJid === '' || empty($hashtags)) {
                return false;
            }

            $hashtag = '';
            foreach ($hashtags as $t) {
                if (is_string($t) && $t !== '') {
                    $hashtag = strtolower(trim($t));
                    break;
                }
            }
            if ($hashtag === '') {
                return false;
            }

            $user = \App\Models\User::query()
                ->where('whatsapp_number', $senderPhone)
                ->orWhere('phone', $senderPhone)
                ->first();
            if (!$user) {
                return false;
            }

            $isGroup = str_contains($remoteJid, '@g.us');
            $task = null;

            if ($isGroup) {
                $team = \App\Models\Team::query()->where('whatsapp_group_jid', $remoteJid)->first();
                if (!$team) {
                    return false;
                }
                $task = \App\Models\ChallengeTask::query()
                    ->where('scope_team_id', $team->id)
                    ->byHashtag($hashtag)
                    ->first();
            } else {
                $task = \App\Models\ChallengeTask::query()
                    ->byHashtag($hashtag)
                    ->whereHas('challenge.userChallenges', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'active');
                    })
                    ->first();
            }

            if (!$task) {
                return false;
            }

            $userChallenge = \App\Models\UserChallenge::query()
                ->where('user_id', $user->id)
                ->where('challenge_id', $task->challenge_id)
                ->where('status', 'active')
                ->first();

            if (!$userChallenge) {
                return false;
            }

            return \App\Models\Checkin::query()
                ->where('user_challenge_id', $userChallenge->id)
                ->where('task_id', $task->id)
                ->whereDate('checked_at', today())
                ->whereNull('deleted_at')
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
