<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WhatsAppSession;
use Illuminate\Http\Request;
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
                'is_active' => false, // Will be activated when EvolutionAPI confirms
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
        
        return redirect()->back()->with('success', 'Sessão WhatsApp configurada! Aguarde a ativação.');
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
        
        $session->markAsDisconnected();
        
        return redirect()->back()->with('success', 'Sessão WhatsApp desconectada com sucesso.');
    }
    
    /**
     * Get WhatsApp session status
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
            'connected' => $session->is_connected,
            'status' => $session->connection_status,
            'bot_number' => $session->formatted_bot_number,
            'whatsapp_link' => $session->whatsapp_link,
            'last_activity' => $session->last_activity_time,
            'stats' => $session->getStats(),
        ]);
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
     * Create session in EvolutionAPI
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
     * Disconnect session from EvolutionAPI
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
