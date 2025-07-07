<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsappBufferService;

class ProcessWhatsappBufferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;

    public function __construct(string $phone)
    {
        $this->phone = $phone;
    }

    public function handle(WhatsappBufferService $bufferService)
    {
        $buffer = $bufferService->pullBuffer($this->phone);
        if (empty($buffer)) {
            return;
        }
        // Aqui você pode integrar com os agents de IA de imagem/texto
        // Exemplo:
        // $images = array_filter($buffer, fn($msg) => $msg['type'] === 'image');
        // $texts = array_filter($buffer, fn($msg) => $msg['type'] === 'text');
        // ...
    }
} 