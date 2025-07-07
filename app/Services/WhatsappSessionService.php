<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WhatsappSessionService
{
    protected string $prefix = 'whatsapp_session:';
    protected int $ttlMinutes = 1440; // 24h

    public function store(string $phone, int $userId, string $status, ?int $ttl = null): void
    {
        $key = $this->prefix . $phone;
        $data = [
            'user_id' => $userId,
            'status' => $status, // 'pro' ou 'free'
            'expires_at' => now()->addMinutes($ttl ?? $this->ttlMinutes)->toIso8601String(),
        ];
        Cache::store(config('cache.default'))->put($key, $data, now()->addMinutes($ttl ?? $this->ttlMinutes));
    }

    public function get(string $phone): ?array
    {
        $key = $this->prefix . $phone;
        return Cache::store(config('cache.default'))->get($key);
    }

    public function remove(string $phone): void
    {
        $key = $this->prefix . $phone;
        Cache::store(config('cache.default'))->forget($key);
    }
} 