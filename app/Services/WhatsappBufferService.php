<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessWhatsappBufferJob;

class WhatsappBufferService
{
    protected string $bufferPrefix = 'whatsapp_buffer:';
    protected string $lockPrefix = 'whatsapp_buffer_lock:';
    protected int $bufferTtl = 120; // segundos
    protected int $lockTtl = 10; // segundos

    public function addMessage(string $phone, array $message): void
    {
        $key = $this->bufferPrefix . $phone;
        // Usa o cache padrão do app (ex: redis/database), evitando store inexistente.
        $buffer = Cache::get($key, []);
        $buffer[] = $message;
        Cache::put($key, $buffer, now()->addSeconds($this->bufferTtl));
    }

    public function scheduleProcessingJob(string $phone): void
    {
        $lockKey = $this->lockPrefix . $phone;
        if (!Cache::has($lockKey)) {
            ProcessWhatsappBufferJob::dispatch($phone)->delay(now()->addSeconds($this->lockTtl));
            Cache::put($lockKey, true, now()->addSeconds($this->lockTtl));
        }
    }

    public function pullBuffer(string $phone): array
    {
        $key = $this->bufferPrefix . $phone;
        $buffer = Cache::pull($key, []);
        return $buffer;
    }
} 