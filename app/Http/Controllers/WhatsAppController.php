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
use App\Services\WhatsappSessionService;
use App\Services\WhatsappBufferService;
use App\Jobs\ProcessWhatsappBufferJob;

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
        // Grava sessão no Redis
        $whatsappSession->store($cleanPhone, $user->id, $user->is_pro ? 'pro' : 'free');
        // Retorna URL do WhatsApp para o frontend abrir
        $botNumber = env('WHATSAPP_BOT_NUMBER', '5571993676365'); // Número padrão se não configurado
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
        // Exemplo para EvolutionAPI: $data['event'] == 'messages.upsert'
        if (isset($data['event']) && $data['event'] === 'messages.upsert' && isset($data['data'])) {
            $eventData = $data['data'];
            // Extrair número e mensagem
            $phone = $eventData['from'] ?? null;
            $message = $eventData['body'] ?? null;
            $type = $eventData['type'] ?? 'text';
            if ($phone && $message) {
                // Bufferizar mensagem no Redis
                $bufferService->addMessage($phone, [
                    'type' => $type,
                    'content' => $message,
                    'timestamp' => now()->toIso8601String(),
                ]);
                // Agendar job de processamento se necessário
                $bufferService->scheduleProcessingJob($phone);
            }
        }
        return response()->json(['status' => 'ok']);
    }
}
