<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Checkin extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_challenge_id',
        'task_id',
        'image_path',
        'image_url',
        'message',
        'source',
        'status',
        'ai_analysis',
        'confidence_score',
        'challenge_day',
        'checked_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'ai_analysis' => 'array',
            'confidence_score' => 'decimal:2',
            'checked_at' => 'datetime',
        ];
    }

    /**
     * Get the user challenge this checkin belongs to
     */
    public function userChallenge(): BelongsTo
    {
        return $this->belongsTo(UserChallenge::class);
    }

    /**
     * Get the task this checkin is for
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ChallengeTask::class, 'task_id');
    }

    /**
     * Get the user through user challenge
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            UserChallenge::class,
            'id', // Foreign key on UserChallenge table
            'id', // Foreign key on User table
            'user_challenge_id', // Local key on Checkin table
            'user_id' // Local key on UserChallenge table
        );
    }

    /**
     * Get the challenge through user challenge
     */
    public function challenge()
    {
        return $this->userChallenge->challenge();
    }

    /**
     * Get full image URL
     */
    public function getImageUrlAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        if ($this->image_path) {
            return Storage::url($this->image_path);
        }

        return null;
    }

    /**
     * Check if checkin has image
     */
    public function getHasImageAttribute(): bool
    {
        return !empty($this->image_path) || !empty($this->attributes['image_url']);
    }

    /**
     * Check if checkin was made via WhatsApp
     */
    public function getIsWhatsappCheckinAttribute(): bool
    {
        return $this->source === 'whatsapp';
    }

    /**
     * Check if checkin was made via web
     */
    public function getIsWebCheckinAttribute(): bool
    {
        return $this->source === 'web';
    }

    /**
     * Check if AI analysis is available
     */
    public function getHasAiAnalysisAttribute(): bool
    {
        return !empty($this->ai_analysis);
    }

    /**
     * Get AI analysis confidence level
     */
    public function getConfidenceLevelAttribute(): string
    {
        if (!$this->confidence_score) {
            return 'unknown';
        }

        if ($this->confidence_score >= 0.8) {
            return 'high';
        } elseif ($this->confidence_score >= 0.6) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get formatted check-in time
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->checked_at->format('H:i');
    }

    /**
     * Get formatted check-in date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->checked_at->format('d/m/Y');
    }

    /**
     * Get time since check-in
     */
    public function getTimeSinceAttribute(): string
    {
        return $this->checked_at->diffForHumans();
    }

    /**
     * Approve the checkin
     */
    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    /**
     * Reject the checkin
     */
    public function reject(string $reason = null): void
    {
        $this->status = 'rejected';
        
        if ($reason) {
            $analysis = $this->ai_analysis ?? [];
            $analysis['rejection_reason'] = $reason;
            $this->ai_analysis = $analysis;
        }
        
        $this->save();
    }

    /**
     * Add AI analysis results
     */
    public function addAiAnalysis(array $analysis): void
    {
        $this->ai_analysis = $analysis;
        $this->confidence_score = $analysis['confidence'] ?? null;
        
        // Auto-approve/reject based on confidence
        if (isset($analysis['valid'])) {
            $this->status = $analysis['valid'] ? 'approved' : 'rejected';
        }
        
        $this->save();
    }

    /**
     * Get share text for social media
     */
    public function getShareTextAttribute(): string
    {
        $task = $this->task;
        $challenge = $this->userChallenge->challenge;
        $day = $this->challenge_day;
        $user = $this->userChallenge->user;

        return "🎯 Dia {$day} do desafio '{$challenge->title}' concluído!\n" .
               "✅ {$task->name}\n" .
               "💪 Continue acompanhando em: " . url('/u/' . ($user->username ?: $user->id));
    }

    /**
     * Delete image file when checkin is deleted
     */
    protected static function booted(): void
    {
        static::deleted(function ($checkin) {
            if ($checkin->image_path && Storage::exists($checkin->image_path)) {
                Storage::delete($checkin->image_path);
            }
        });
    }

    /**
     * Scope for approved checkins
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending checkins
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for rejected checkins
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for checkins with images
     */
    public function scopeWithImage($query)
    {
        return $query->where(function ($query) {
            $query->whereNotNull('image_path')
                  ->orWhereNotNull('image_url');
        });
    }

    /**
     * Scope for WhatsApp checkins
     */
    public function scopeFromWhatsapp($query)
    {
        return $query->where('source', 'whatsapp');
    }

    /**
     * Scope for web checkins
     */
    public function scopeFromWeb($query)
    {
        return $query->where('source', 'web');
    }

    /**
     * Scope for today's checkins
     */
    public function scopeToday($query)
    {
        return $query->whereDate('checked_at', today());
    }

    /**
     * Scope for checkins within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checked_at', [$startDate, $endDate]);
    }

    /**
     * Scope for high confidence AI analysis
     */
    public function scopeHighConfidence($query, float $threshold = 0.8)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Scope for checkins that need manual review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where(function ($query) {
            $query->where('status', 'pending')
                  ->orWhere(function ($query) {
                      $query->where('confidence_score', '<', 0.6)
                            ->where('status', 'approved');
                  });
        });
    }
}
