<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'phone_number',
        'bot_number',
        'session_id',
        'instance_name',
        'is_active',
        'last_activity',
        'connected_at',
        'disconnected_at',
        'metadata',
        'message_count',
        'checkin_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_activity' => 'datetime',
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneNumberAttribute(): string
    {
        $phone = preg_replace('/\D/', '', $this->phone_number);
        
        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            // Brazilian number: +55 (11) 99999-9999
            return '+55 (' . substr($phone, 2, 2) . ') ' . 
                   substr($phone, 4, 5) . '-' . substr($phone, 9, 4);
        }
        
        return $this->phone_number;
    }

    /**
     * Get formatted bot number
     */
    public function getFormattedBotNumberAttribute(): ?string
    {
        if (!$this->bot_number) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $this->bot_number);
        
        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            return '+55 (' . substr($phone, 2, 2) . ') ' . 
                   substr($phone, 4, 5) . '-' . substr($phone, 9, 4);
        }
        
        return $this->bot_number;
    }

    /**
     * Check if session is connected
     */
    public function getIsConnectedAttribute(): bool
    {
        return $this->is_active && 
               $this->connected_at && 
               !$this->disconnected_at;
    }

    /**
     * Get connection status
     */
    public function getConnectionStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->disconnected_at) {
            return 'disconnected';
        }

        if ($this->connected_at) {
            return 'connected';
        }

        return 'pending';
    }

    /**
     * Get last activity time
     */
    public function getLastActivityTimeAttribute(): ?string
    {
        return $this->last_activity?->diffForHumans();
    }

    /**
     * Get session duration
     */
    public function getSessionDurationAttribute(): ?string
    {
        if (!$this->connected_at) {
            return null;
        }

        $endTime = $this->disconnected_at ?? now();
        return $this->connected_at->diff($endTime)->format('%H:%I:%S');
    }

    /**
     * Calculate messages per day
     */
    public function getMessagesPerDayAttribute(): float
    {
        if (!$this->connected_at) {
            return 0;
        }

        $days = max(1, $this->connected_at->diffInDays(now()) + 1);
        return round($this->message_count / $days, 2);
    }

    /**
     * Calculate checkins per day
     */
    public function getCheckinsPerDayAttribute(): float
    {
        if (!$this->connected_at) {
            return 0;
        }

        $days = max(1, $this->connected_at->diffInDays(now()) + 1);
        return round($this->checkin_count / $days, 2);
    }

    /**
     * Mark as connected
     */
    public function markAsConnected(string $botNumber = null): void
    {
        $this->is_active = true;
        $this->connected_at = now();
        $this->disconnected_at = null;
        $this->last_activity = now();
        
        if ($botNumber) {
            $this->bot_number = $botNumber;
        }
        
        $this->save();
    }

    /**
     * Mark as disconnected
     */
    public function markAsDisconnected(): void
    {
        $this->is_active = false;
        $this->disconnected_at = now();
        $this->save();
    }

    /**
     * Update last activity
     */
    public function updateLastActivity(): void
    {
        $this->last_activity = now();
        $this->save();
    }

    /**
     * Increment message count
     */
    public function incrementMessageCount(): void
    {
        $this->increment('message_count');
        $this->updateLastActivity();
    }

    /**
     * Increment checkin count
     */
    public function incrementCheckinCount(): void
    {
        $this->increment('checkin_count');
        $this->updateLastActivity();
    }

    /**
     * Update session metadata
     */
    public function updateMetadata(array $data): void
    {
        $this->metadata = array_merge($this->metadata ?? [], $data);
        $this->save();
    }

    /**
     * Generate WhatsApp link
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->bot_number) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $this->bot_number);
        $message = urlencode('Olá! Gostaria de conectar minha conta DOPA Check.');
        
        return "https://wa.me/{$phone}?text={$message}";
    }

    /**
     * Get session statistics
     */
    public function getStats(): array
    {
        return [
            'total_messages' => $this->message_count,
            'total_checkins' => $this->checkin_count,
            'messages_per_day' => $this->messages_per_day,
            'checkins_per_day' => $this->checkins_per_day,
            'connection_status' => $this->connection_status,
            'session_duration' => $this->session_duration,
            'last_activity' => $this->last_activity_time,
        ];
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for connected sessions
     */
    public function scopeConnected($query)
    {
        return $query->where('is_active', true)
                     ->whereNotNull('connected_at')
                     ->whereNull('disconnected_at');
    }

    /**
     * Scope for disconnected sessions
     */
    public function scopeDisconnected($query)
    {
        return $query->where('is_active', false)
                     ->orWhereNotNull('disconnected_at');
    }

    /**
     * Scope for sessions by phone number
     */
    public function scopeByPhoneNumber($query, string $phoneNumber)
    {
        $cleanPhone = preg_replace('/\D/', '', $phoneNumber);
        return $query->where('phone_number', 'like', "%{$cleanPhone}%");
    }

    /**
     * Scope for recent activity
     */
    public function scopeRecentActivity($query, int $hours = 24)
    {
        return $query->where('last_activity', '>=', now()->subHours($hours));
    }

    /**
     * Scope for inactive sessions (no activity for X hours)
     */
    public function scopeInactive($query, int $hours = 48)
    {
        return $query->where('last_activity', '<', now()->subHours($hours))
                     ->orWhereNull('last_activity');
    }
}
