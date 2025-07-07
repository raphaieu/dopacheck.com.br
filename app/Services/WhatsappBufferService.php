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
        $buffer = Cache::store('cache')->get($key, []);
        $buffer[] = $message;
        Cache::store('cache')->put($key, $buffer, now()->addSeconds($this->bufferTtl));
    }

    public function scheduleProcessingJob(string $phone): void
    {
        $lockKey = $this->lockPrefix . $phone;
        if (!Cache::store('cache')->has($lockKey)) {
            ProcessWhatsappBufferJob::dispatch($phone)->delay(now()->addSeconds($this->lockTtl));
            Cache::store('cache')->put($lockKey, true, now()->addSeconds($this->lockTtl));
        }
    }

    public function pullBuffer(string $phone): array
    {
        $key = $this->bufferPrefix . $phone;
        $buffer = Cache::store('cache')->pull($key, []);
        return $buffer;
    }
} 