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

class WhatsAppController extends Controller
{
    /**
     * Show WhatsApp connection page
     */
    public function connect(Request $request): Response
    {
        $user = $request->user();
        $session = $user->whatsappSession;
        
        return Inertia::render('WhatsApp/Connect', [
            'session' => $session,
            'user' => [
                'phone' => $user->phone,
                'whatsapp_number' => $user->whatsapp_number,
            ],
        ]);
    }
    
    /**
     * Create or update WhatsApp session
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'phone_number' => [
                'required', 
                'string', 
                'regex:/^[0-9]{10,15}$/',
                'unique:whatsapp_sessions,phone_number,' . ($user->whatsappSession?->id ?? 'NULL')
            ],
        ]);
        
        // Clean phone number (remove any formatting)
        $cleanPhone = preg_replace('/\D/', '', $validated['phone_number']);
        
        // Ensure Brazilian format
        if (strlen($cleanPhone) === 11 && !str_starts_with($cleanPhone, '55')) {
            $cleanPhone = '55' . $cleanPhone;
        }
        
        // Generate unique session ID and bot number
        $sessionId = 'dopa_' . $user->id . '_' . Str::random(8);
        $botNumber = $this->generateBotNumber();
        
        // Create or update session
        $session = WhatsAppSession::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $cleanPhone,
                'bot_number' => $botNumber,
                'session_id' => $sessionId,
                'instance_name' => 'dopacheck_' . $user->id,
                'is_active' => true, // Simular ativo por enquanto
                'connected_at' => now(),
                'last_activity' => now(),
                'metadata' => [
                    'created_via' => 'web',
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ],
            ]
        );
        
        // Update user's whatsapp_number
        $user->update(['whatsapp_number' => $cleanPhone]);
        
        // TODO: Integrate with EvolutionAPI to create session
        // $this->createEvolutionApiSession($session);
        
        return redirect()->route('dopa.dashboard')->with('success', 'WhatsApp conectado com sucesso! Número do bot: ' . $botNumber);
    }
    
    /**
     * Disconnect WhatsApp session
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $session = $user->whatsappSession;
        
        if (!$session) {
            return redirect()->back()->with('error', 'Nenhuma sessão WhatsApp encontrada.');
        }
        
        // TODO: Disconnect from EvolutionAPI
        // $this->disconnectEvolutionApiSession($session);
        
        $session->update([
            'is_active' => false,
            'disconnected_at' => now(),
            'last_activity' => now()
        ]);
        
        return redirect()->route('dopa.dashboard')->with('success', 'WhatsApp desconectado com sucesso.');
    }
    
    /**
     * Get WhatsApp session status (API)
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $session = $user->whatsappSession;
        
        if (!$session) {
            return response()->json([
                'connected' => false,
                'status' => 'not_configured',
                'message' => 'WhatsApp não configurado',
            ]);
        }
        
        return response()->json([
            'connected' => $session->is_active,
            'status' => $session->is_active ? 'connected' : 'disconnected',
            'session' => [
                'id' => $session->id,
                'phone_number' => $session->phone_number,
                'bot_number' => $session->bot_number,
                'is_active' => $session->is_active,
                'last_activity' => $session->last_activity,
                'message_count' => $session->message_count,
                'checkin_count' => $session->checkin_count,
                'connected_at' => $session->connected_at
            ],
            'bot_number' => $session->bot_number,
            'whatsapp_link' => $session->bot_number ? "https://wa.me/{$session->bot_number}" : null,
            'last_activity' => $session->last_activity,
            'stats' => [
                'message_count' => $session->message_count ?? 0,
                'checkin_count' => $session->checkin_count ?? 0
            ]
        ]);
    }

    /**
     * Webhook para processar mensagens do WhatsApp (Sprint 3)
     */
    public function webhook(Request $request): JsonResponse
    {
        // Log da requisição para debug
        Log::info('WhatsApp webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
        
        // Verificar se é um ping/health check
        if ($request->has('ping')) {
            return response()->json(['status' => 'pong'], 200);
        }
        
        // TODO: Implementar no Sprint 3
        // Por enquanto, apenas validar estrutura básica esperada
        $data = $request->all();
        
        // Estrutura básica esperada do EvolutionAPI
        if (isset($data['event']) && isset($data['data'])) {
            $event = $data['event'];
            $eventData = $data['data'];
            
            switch ($event) {
                case 'messages.upsert':
                    // TODO: Processar mensagem recebida
                    Log::info('Message received', ['data' => $eventData]);
                    break;
                    
                case 'connection.update':
                    // TODO: Atualizar status de conexão
                    Log::info('Connection update', ['data' => $eventData]);
                    break;
                    
                case 'qrcode.updated':
                    // TODO: Atualizar QR code
                    Log::info('QR Code updated', ['data' => $eventData]);
                    break;
                    
                default:
                    Log::info('Unknown webhook event', ['event' => $event, 'data' => $eventData]);
            }
        }
        
        // Por enquanto, sempre retornar sucesso para não quebrar a integração
        return response()->json([
            'status' => 'received',
            'message' => 'Webhook processado com sucesso',
            'timestamp' => now()
        ], 200);
    }
    
    /**
     * Generate a unique bot number for the user
     */
    private function generateBotNumber(): string
    {
        // In a real implementation, this would be managed by your WhatsApp provider
        // For now, we'll generate a fake number for demonstration
        
        $areaCode = '11'; // São Paulo area code
        $baseNumber = '9' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        return '55' . $areaCode . $baseNumber;
    }
    
    /**
     * Create session in EvolutionAPI (TODO: Sprint 3)
     */
    private function createEvolutionApiSession(WhatsAppSession $session): void
    {
        // TODO: Implement EvolutionAPI integration
        /*
        $response = Http::post(config('services.evolution_api.base_url') . '/instance/create', [
            'instanceName' => $session->instance_name,
            'token' => $session->session_id,
            'qrcode' => true,
            'number' => $session->phone_number,
            'webhook' => route('webhook.whatsapp'),
            'webhook_by_events' => false,
            'events' => [
                'APPLICATION_STARTUP',
                'QRCODE_UPDATED',
                'CONNECTION_UPDATE',
                'MESSAGES_UPSERT',
                'MESSAGES_UPDATE',
            ],
        ], [
            'Authorization' => 'Bearer ' . config('services.evolution_api.api_key'),
        ]);
        
        if ($response->successful()) {
            $session->markAsConnected();
        }
        */
    }
    
    /**
     * Disconnect session from EvolutionAPI (TODO: Sprint 3)
     */
    private function disconnectEvolutionApiSession(WhatsAppSession $session): void
    {
        // TODO: Implement EvolutionAPI integration
        /*
        $response = Http::delete(config('services.evolution_api.base_url') . '/instance/delete/' . $session->instance_name, [], [
            'Authorization' => 'Bearer ' . config('services.evolution_api.api_key'),
        ]);
        */
    }
}
