<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'challenge_id',
        'name',
        'hashtag',
        'description',
        'order',
        'is_required',
        'icon',
        'color',
        'validation_rules',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'validation_rules' => 'array',
        ];
    }

    /**
     * Get the challenge this task belongs to
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Get check-ins for this task
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class, 'task_id');
    }

    /**
     * Get today's check-ins for this task
     */
    public function todayCheckins(): HasMany
    {
        return $this->checkins()->whereDate('checked_at', today());
    }

    /**
     * Get hashtag with # prefix
     */
    public function getHashtagWithPrefixAttribute(): string
    {
        return '#' . $this->hashtag;
    }

    /**
     * Get formatted hashtag for WhatsApp
     */
    public function getFormattedHashtagAttribute(): string
    {
        return strtolower(str_replace(' ', '', $this->hashtag));
    }

    /**
     * Check if user has checked in today for this task
     */
    public function hasUserCheckedInToday(User $user): bool
    {
        return $this->checkins()
            ->whereHas('userChallenge', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'active');
            })
            ->whereDate('checked_at', today())
            ->exists();
    }

    /**
     * Get user's check-in for this task on specific day
     */
    public function getUserCheckinForDay(User $user, int $challengeDay): ?Checkin
    {
        return $this->checkins()
            ->whereHas('userChallenge', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'active');
            })
            ->where('challenge_day', $challengeDay)
            ->first();
    }

    /**
     * Get user's latest check-in for this task
     */
    public function getUserLatestCheckin(User $user): ?Checkin
    {
        return $this->checkins()
            ->whereHas('userChallenge', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'active');
            })
            ->latest('checked_at')
            ->first();
    }

    /**
     * Get completion rate for this task across all users
     */
    public function getCompletionRateAttribute(): float
    {
        $totalDays = $this->challenge->userChallenges()
            ->where('status', '!=', 'abandoned')
            ->sum('current_day');

        $completedDays = $this->checkins()->count();

        return $totalDays > 0 ? round(($completedDays / $totalDays) * 100, 2) : 0;
    }

    /**
     * Get task statistics
     */
    public function getStats(): array
    {
        $totalCheckins = $this->checkins()->count();
        $uniqueUsers = $this->checkins()
            ->distinct('user_challenge_id')
            ->count('user_challenge_id');

        $todayCheckins = $this->todayCheckins()->count();

        return [
            'total_checkins' => $totalCheckins,
            'unique_users' => $uniqueUsers,
            'today_checkins' => $todayCheckins,
            'completion_rate' => $this->completion_rate,
            'average_checkins_per_user' => $uniqueUsers > 0 ? round($totalCheckins / $uniqueUsers, 2) : 0,
        ];
    }

    /**
     * Scope for required tasks
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for optional tasks
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Scope tasks by hashtag
     */
    public function scopeByHashtag($query, string $hashtag)
    {
        return $query->where('hashtag', strtolower(str_replace('#', '', $hashtag)));
    }

    /**
     * Validate if content matches task requirements (for AI analysis)
     */
    public function validateContent(array $content): array
    {
        $rules = $this->validation_rules ?? [];
        $results = [
            'valid' => true,
            'confidence' => 1.0,
            'issues' => [],
            'suggestions' => [],
        ];

        // Basic validation logic - can be enhanced
        if (isset($rules['required_objects']) && isset($content['detected_objects'])) {
            $requiredObjects = $rules['required_objects'];
            $detectedObjects = array_column($content['detected_objects'], 'name');
            
            $missingObjects = array_diff($requiredObjects, $detectedObjects);
            if (!empty($missingObjects)) {
                $results['valid'] = false;
                $results['confidence'] *= 0.5;
                $results['issues'][] = 'Objetos necessários não detectados: ' . implode(', ', $missingObjects);
            }
        }

        if (isset($rules['forbidden_objects']) && isset($content['detected_objects'])) {
            $forbiddenObjects = $rules['forbidden_objects'];
            $detectedObjects = array_column($content['detected_objects'], 'name');
            
            $foundForbidden = array_intersect($forbiddenObjects, $detectedObjects);
            if (!empty($foundForbidden)) {
                $results['valid'] = false;
                $results['confidence'] *= 0.3;
                $results['issues'][] = 'Objetos não permitidos detectados: ' . implode(', ', $foundForbidden);
            }
        }

        return $results;
    }

    /**
     * Get example usage text for WhatsApp
     */
    public function getExampleUsageAttribute(): string
    {
        return "Envie uma foto + #{$this->hashtag}\nExemplo: Foto da sua corrida + \"#{$this->hashtag}\"";
    }
}
