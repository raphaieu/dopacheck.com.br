<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    /**
     * Quando true, não envia de fato para a Evolution API; apenas registra em log
     * o payload que seria enviado. Útil em dev para ver a mensagem que chegaria no WhatsApp.
     */
    private function isLogOnlyMode(): bool
    {
        return (bool) env('EVOLUTION_LOG_ONLY', false);
    }

    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $apiKey = null,
    ) {
    }

    private function getBaseUrl(): string
    {
        $url = $this->baseUrl ?? (string) env('EVOLUTION_API_URL', '');
        return rtrim($url, '/');
    }

    private function getApiKey(): string
    {
        return $this->apiKey ?? (string) env('EVOLUTION_API_KEY', '');
    }

    /**
     * Reage a uma mensagem (grupo ou 1:1).
     *
     * Evolution API v2 costuma aceitar:
     * - POST /message/sendReaction/{instance}
     * - body com key{remoteJid,id,fromMe} e reaction
     * - em grupos, number pode ser o groupJid (ex: 123-456@g.us)
     */
    public function sendReaction(
        string $instance,
        string $remoteJid,
        string $messageId,
        string $emoji,
        ?string $senderPhone = null,
        ?string $participantJid = null,
    ): void {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '') {
            throw new \RuntimeException('EVOLUTION_API_URL não configurada');
        }

        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            throw new \RuntimeException('EVOLUTION_API_KEY não configurada');
        }

        $url = $baseUrl . '/message/sendReaction/' . urlencode($instance);

        $isGroup = str_contains($remoteJid, '@g.us');

        // Payload recomendado (docs v2): em grupo precisa de key.participant
        $body = [
            'key' => array_filter([
                'remoteJid' => $remoteJid,
                'fromMe' => false,
                'id' => $messageId,
                'participant' => $isGroup ? $participantJid : null,
            ], fn ($v) => $v !== null && $v !== ''),
            'reaction' => $emoji,
        ];

        // Compat: algumas builds aceitam forma "simples" também
        if (!$isGroup && $senderPhone) {
            $body['number'] = $this->normalizeNumber($senderPhone);
            $body['messageId'] = $messageId;
        }

        if ($this->isLogOnlyMode()) {
            Log::channel('stack')->info('Evolution API [LOG-ONLY]: sendReaction — reação não enviada (EVOLUTION_LOG_ONLY=true)', [
                'instance' => $instance,
                'remote_jid' => $remoteJid,
                'message_id' => $messageId,
                'emoji' => $emoji,
            ]);
            return;
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(15)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                Log::error('Evolution API sendReaction: resposta não sucedida', [
                    'instance' => $instance,
                    'remote_jid' => $remoteJid,
                    'message_id' => $messageId,
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                throw new \RuntimeException('HTTP ' . $response->status() . ' ao enviar reaction: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar reaction via Evolution API', [
                'error' => $e->getMessage(),
                'instance' => $instance,
                'remote_jid' => $remoteJid,
                'message_id' => $messageId,
                'is_group' => $isGroup,
                'has_participant' => $participantJid !== null && $participantJid !== '',
            ]);
            throw $e;
        }
    }

    /**
     * Envia uma mensagem de texto (1:1 ou grupo).
     * Para grupos (JID terminando em @g.us), o "number" deve ser o JID completo; para contatos, número normalizado.
     */
    public function sendTextMessage(
        string $instance,
        string $remoteJid,
        string $text,
    ): void {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '') {
            throw new \RuntimeException('EVOLUTION_API_URL não configurada');
        }

        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            throw new \RuntimeException('EVOLUTION_API_KEY não configurada');
        }

        // Grupos: Evolution API espera o JID completo (ex: 120363123456789@g.us). Não normalizar.
        $numberOrJid = str_contains($remoteJid, '@g.us')
            ? $remoteJid
            : $this->normalizeNumber($remoteJid);

        $body = [
            'number' => $numberOrJid,
            'text' => $text,
            'delay' => 1200, // micro-delay humano
            'linkPreview' => true,
        ];

        if ($this->isLogOnlyMode()) {
            Log::channel('stack')->info('Evolution API [LOG-ONLY]: sendText — mensagem não enviada (EVOLUTION_LOG_ONLY=true)', [
                'instance' => $instance,
                'remote_jid' => $remoteJid,
                'number_or_jid_sent' => $numberOrJid,
                'is_group' => str_contains($remoteJid, '@g.us'),
                'text_length' => strlen($text),
                'text_preview' => strlen($text) > 500 ? substr($text, 0, 500) . '...' : $text,
            ]);
            return;
        }

        $url = $baseUrl . '/message/sendText/' . urlencode($instance);

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(20)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                Log::error('Evolution API sendText: resposta não sucedida', [
                    'instance' => $instance,
                    'remote_jid' => $remoteJid,
                    'status' => $response->status(),
                    'body_sent_preview' => ['number' => $numberOrJid, 'text_length' => strlen($text)],
                    'response_body' => $response->body(),
                ]);
                throw new \RuntimeException('HTTP ' . $response->status() . ' ao enviar text: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar mensagem de texto via Evolution API', [
                'error' => $e->getMessage(),
                'instance' => $instance,
                'remote_jid' => $remoteJid,
                'is_group' => str_contains($remoteJid, '@g.us'),
            ]);
            throw $e;
        }
    }

    private function normalizeNumber(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?: '';
        if ($digits === '') {
            return $phone;
        }

        // Normaliza Brasil: se vier 11 dígitos sem 55, prefixa.
        if (strlen($digits) === 11 && !str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        return $digits;
    }
}

