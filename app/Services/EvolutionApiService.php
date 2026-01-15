<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
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

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(15)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                throw new \RuntimeException('HTTP ' . $response->status() . ' ao enviar reaction');
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

