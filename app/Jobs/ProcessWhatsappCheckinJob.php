<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\Team;
use App\Models\User;
use App\Models\UserChallenge;
use App\Services\EvolutionApiService;
use App\Services\PhoneNumberService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessWhatsappCheckinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array{
     *   instance?: string|null,
     *   remote_jid?: string|null,
     *   message_id?: string|null,
     *   sender_phone?: string|null,
     *   participant_jid?: string|null,
     *   caption?: string|null,
     *   hashtags?: array<int,string>,
     *   has_image?: bool|null,
     *   image_path?: string|null,
     *   image_url?: string|null,
     *   image_mime?: string|null,
     * } $payload
     */
    public function __construct(public array $payload)
    {
    }

    public function handle(EvolutionApiService $evolution): void
    {
        $instance = (string) ($this->payload['instance'] ?? env('EVOLUTION_INSTANCE', ''));
        $remoteJid = (string) ($this->payload['remote_jid'] ?? '');
        $messageId = (string) ($this->payload['message_id'] ?? '');
        $senderPhoneRaw = (string) ($this->payload['sender_phone'] ?? '');
        $participantJid = (string) ($this->payload['participant_jid'] ?? '');
        $caption = (string) ($this->payload['caption'] ?? '');
        $hashtags = is_array($this->payload['hashtags'] ?? null) ? $this->payload['hashtags'] : [];
        $hasImage = (bool) ($this->payload['has_image'] ?? false);
        $imagePath = (string) ($this->payload['image_path'] ?? '');
        $imageUrl = (string) ($this->payload['image_url'] ?? '');
        $storageError = (string) ($this->payload['storage_error'] ?? '');

        $react = function (string $emoji) use ($evolution, $instance, $remoteJid, $messageId, $senderPhoneRaw, $participantJid): void {
            if ($instance === '' || $remoteJid === '' || $messageId === '') {
                return;
            }
            try {
                $evolution->sendReaction(
                    instance: $instance,
                    remoteJid: $remoteJid,
                    messageId: $messageId,
                    emoji: $emoji,
                    senderPhone: $senderPhoneRaw !== '' ? $senderPhoneRaw : null,
                    participantJid: $participantJid !== '' ? $participantJid : null,
                );
            } catch (\Throwable) {
                // reação é best-effort
            }
        };

        // Valida mínimos
        if ($senderPhoneRaw === '' || $remoteJid === '' || $messageId === '' || (!$hasImage && $imagePath === '' && $imageUrl === '')) {
            Log::warning('WhatsApp checkin: payload incompleto', [
                'instance' => $instance,
                'remote_jid' => $remoteJid,
                'message_id' => $messageId,
                'sender_phone' => $senderPhoneRaw,
                'has_image' => $hasImage,
                'has_image_path' => $imagePath !== '',
                'has_image_url' => $imageUrl !== '',
                'storage_error' => $storageError !== '' ? $storageError : null,
            ]);
            $react('❌');
            return;
        }

        // Normaliza telefone (Brasil)
        $phoneService = app(PhoneNumberService::class);
        $senderPhone = $phoneService->normalize($senderPhoneRaw);

        // Log cirúrgico: telefone original + normalizado
        Log::info('WhatsApp checkin: normalização de telefone', [
            'phone_original' => $senderPhoneRaw,
            'phone_normalized' => $senderPhone,
            'message_id' => $messageId,
        ]);

        if ($senderPhone === '') {
            $react('❌');
            return;
        }

        // Hashtag (primeira)
        $hashtag = '';
        foreach ($hashtags as $t) {
            if (is_string($t) && $t !== '') {
                $hashtag = strtolower(trim($t));
                break;
            }
        }
        if ($hashtag === '') {
            Log::info('WhatsApp checkin: sem hashtag na legenda', [
                'sender_phone' => $senderPhone,
                'remote_jid' => $remoteJid,
                'message_id' => $messageId,
            ]);
            $react('❌');
            $this->safeDeleteImage($imagePath);
            return;
        }

        // Usuário - busca usando variações (com e sem o 9 após o DDD)
        $userResult = $phoneService->findUserByPhoneWithInfo($senderPhoneRaw);
        $user = $userResult['user'];
        $queryInfo = $userResult['query_info'];

        // Log cirúrgico: query usada e contagem de matches
        Log::info('WhatsApp checkin: busca de usuário', [
            'phone_original' => $senderPhoneRaw,
            'phone_normalized' => $senderPhone,
            'user_matches' => $user ? 1 : 0,
            'whatsapp_matches' => $queryInfo['whatsapp_matches'],
            'phone_matches' => $queryInfo['phone_matches'],
            'total_variations' => $queryInfo['total_variations'],
            'message_id' => $messageId,
        ]);

        if (!$user) {
            Log::info('WhatsApp checkin: usuário não encontrado', [
                'sender_phone' => $senderPhone,
                'hashtag' => $hashtag,
                'whatsapp_matches' => $queryInfo['whatsapp_matches'],
                'phone_matches' => $queryInfo['phone_matches'],
            ]);
            $react('❌');
            $this->safeDeleteImage($imagePath);
            return;
        }

        $isGroup = str_contains($remoteJid, '@g.us');
        $teamFromGroup = null;
        if ($isGroup) {
            $teamFromGroup = Team::query()->where('whatsapp_group_jid', $remoteJid)->first();
            if (!$teamFromGroup) {
                Log::info('WhatsApp checkin: grupo sem mapeamento para time', [
                    'remote_jid' => $remoteJid,
                    'sender_phone' => $senderPhone,
                    'user_id' => $user->id,
                    'hashtag' => $hashtag,
                ]);
                $react('❌');
                $this->safeDeleteImage($imagePath);
                return;
            }

            $isOwner = $user->ownedTeams()->whereKey($teamFromGroup->id)->exists();
            $isMember = $user->teams()->whereKey($teamFromGroup->id)->exists();
            if (!$isOwner && !$isMember) {
                Log::info('WhatsApp checkin: usuário não é membro do time do grupo', [
                    'remote_jid' => $remoteJid,
                    'team_id' => $teamFromGroup->id,
                    'sender_phone' => $senderPhone,
                    'user_id' => $user->id,
                    'hashtag' => $hashtag,
                ]);
                $react('❌');
                $this->safeDeleteImage($imagePath);
                return;
            }
        }

        // Task:
        // - se veio de grupo (@g.us), resolve por escopo do time (scope_team_id = team_id)
        // - senão, prefere desafios onde o usuário está ativo (global/privado via DM)
        $task = null;
        if ($teamFromGroup instanceof Team) {
            $task = ChallengeTask::query()
                ->where('scope_team_id', $teamFromGroup->id)
                ->byHashtag($hashtag)
                ->first();
        } else {
            $task = ChallengeTask::query()
                ->byHashtag($hashtag)
                ->whereHas('challenge.userChallenges', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('status', 'active');
                })
                ->first();
        }

        if (!$task) {
            Log::info('WhatsApp checkin: task não encontrada/usuário não participa de desafio ativo', [
                'user_id' => $user->id,
                'sender_phone' => $senderPhone,
                'hashtag' => $hashtag,
                'remote_jid' => $remoteJid,
                'team_id' => $teamFromGroup instanceof Team ? $teamFromGroup->id : null,
            ]);
            $react('❌');
            $this->safeDeleteImage($imagePath);
            return;
        }

        // UserChallenge ativo do usuário para o desafio dessa task
        $userChallenge = UserChallenge::query()
            ->where('user_id', $user->id)
            ->where('challenge_id', $task->challenge_id)
            ->where('status', 'active')
            ->with('challenge')
            ->first();

        if (!$userChallenge) {
            $react('❌');
            $this->safeDeleteImage($imagePath);
            return;
        }

        // Atualiza dia/status do desafio
        $userChallenge->updateCurrentDay();
        $userChallenge->refresh();
        if ($userChallenge->status !== 'active') {
            $react('❌');
            $this->safeDeleteImage($imagePath);
            return;
        }

        // Idempotência por message_id: se já existe check-in com esse message_id, considera sucesso
        if ($messageId !== '') {
            $existingByMessageId = Checkin::query()
                ->where('whatsapp_message_id', $messageId)
                ->whereNull('deleted_at')
                ->exists();

            if ($existingByMessageId) {
                Log::info('WhatsApp checkin: check-in já processado (idempotência por message_id)', [
                    'message_id' => $messageId,
                    'user_id' => $user->id,
                ]);
                $react('✅');
                $this->safeDeleteImage($imagePath);
                return;
            }
        }

        // Idempotência: se já existe check-in hoje para essa task, considera sucesso e só reage ✅
        // Também checa por challenge_day do "hoje" (unique) para evitar crash quando existir check-in retroativo
        // que ocupe o mesmo day index.
        $challengeStart = $userChallenge->getChallengeStartDate()->startOfDay();
        $today = today()->startOfDay();
        $challengeDayToday = (int) ($challengeStart->diffInDays($today, false) + 1);
        $challengeDayToday = max(1, min($challengeDayToday, (int) $userChallenge->challenge->duration_days));

        $already = Checkin::query()
            ->where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->where(function ($q) use ($challengeDayToday) {
                $q->whereDate('checked_at', today())
                    ->orWhere('challenge_day', $challengeDayToday);
            })
            ->whereNull('deleted_at')
            ->exists();

        if ($already) {
            $react('✅');
            $this->safeDeleteImage($imagePath);
            return;
        }

        if ($imagePath === '' && $imageUrl !== '') {
            // Baixa a imagem a partir do URL da Evolution (quando webhook não inclui base64)
            $imagePath = $this->downloadImageToPublicDisk($imageUrl, $senderPhone);
        }

        if ($imagePath === '' || !Storage::disk('public')->exists($imagePath)) {
            Log::warning('WhatsApp checkin: imagem não encontrada no storage', [
                'image_path' => $imagePath,
                'image_url' => $imageUrl,
                'user_id' => $user->id,
            ]);
            $react('❌');
            return;
        }

        $imageUrl = Storage::url($imagePath);

        try {
            DB::transaction(function () use ($userChallenge, $task, $caption, $imagePath, $imageUrl, $messageId): void {
                // addCheckin já atualiza stats + current day
                $userChallenge->addCheckin($task, [
                    'image_path' => $imagePath,
                    'image_url' => $imageUrl,
                    'message' => $caption !== '' ? $caption : null,
                    'source' => 'whatsapp',
                    'whatsapp_message_id' => $messageId !== '' ? $messageId : null,
                    'status' => 'approved',
                ]);
            });

            $react('✅');
        } catch (UniqueConstraintViolationException $e) {
            // Duplicata (unique por day) -> trata como idempotente
            $react('✅');
            $this->safeDeleteImage($imagePath);
            return;
        } catch (\Throwable $e) {
            Log::error('WhatsApp checkin: erro ao criar check-in', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_challenge_id' => $userChallenge->id,
                'task_id' => $task->id,
                'image_path' => $imagePath,
            ]);

            $react('❌');
            // se falhou, remove a imagem pra não deixar lixo
            $this->safeDeleteImage($imagePath);
            throw $e;
        }
    }

    private function downloadImageToPublicDisk(string $url, string $senderPhone): string
    {
        try {
            $apiKey = (string) env('EVOLUTION_API_KEY', '');
            $request = Http::timeout(25);
            // Alguns endpoints de mídia da Evolution pedem apikey; se não precisar, não atrapalha.
            if ($apiKey !== '') {
                $request = $request->withHeaders(['apikey' => $apiKey]);
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $request->get($url);
            if (!$response->successful()) {
                throw new \RuntimeException('Falha ao baixar imagem: HTTP ' . $response->status());
            }

            $bytes = $response->body();
            if (!is_string($bytes) || $bytes === '') {
                throw new \RuntimeException('Resposta vazia ao baixar imagem');
            }

            // Valida que parece imagem
            $detectedMime = null;
            try {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo->buffer($bytes) ?: null;
            } catch (\Throwable) {
                $detectedMime = null;
            }
            if ($detectedMime !== null && !in_array($detectedMime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
                throw new \RuntimeException('Conteúdo baixado não parece imagem (mime=' . $detectedMime . ')');
            }

            $sender = preg_replace('/\D/', '', $senderPhone) ?: 'unknown';
            $ext = match ($detectedMime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $filename = 'whatsapp_' . $sender . '_' . now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::uuid() . '.' . $ext;
            $path = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/' . $filename;

            try {
                Storage::disk('public')->makeDirectory(\dirname($path));
            } catch (\Throwable) {
                // best-effort
            }

            $ok = Storage::disk('public')->put($path, $bytes);
            if (!$ok) {
                throw new \RuntimeException('Falha ao salvar bytes da imagem');
            }

            return $path;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp checkin: falha ao baixar imagem via URL', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    private function safeDeleteImage(string $imagePath): void
    {
        if ($imagePath === '') {
            return;
        }
        try {
            Storage::disk('public')->delete($imagePath);
        } catch (\Throwable) {
            // best-effort
        }
    }
}

