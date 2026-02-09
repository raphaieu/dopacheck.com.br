<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
    use HasFactory;

    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_TEAM = 'team';
    public const VISIBILITY_GLOBAL = 'global';
    public const PRIVATE_HASHTAG_SCOPE_OFFSET = 1000000000000;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'duration_days',
        'start_date',
        'end_date',
        'is_template',
        'is_public', // legado: será substituído por visibility
        'visibility',
        'is_featured',
        'created_by',
        'team_id',
        'participant_count',
        'category',
        'difficulty',
        'image_url',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'participant_count' => 'integer',
            'is_template' => 'boolean',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'tags' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the user who created this challenge
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the team this challenge belongs to (quando visibility = team)
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the tasks for this challenge
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ChallengeTask::class)->orderBy('order');
    }

    /**
     * Get user participations in this challenge
     */
    public function userChallenges(): HasMany
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Get active participants
     */
    public function activeParticipants(): HasMany
    {
        return $this->userChallenges()->where('status', 'active');
    }

    /**
     * Get completed participants
     */
    public function completedParticipants(): HasMany
    {
        return $this->userChallenges()->where('status', 'completed');
    }

    /**
     * Check if user is participating in this challenge
     */
    public function isUserParticipating(User $user): bool
    {
        return $this->userChallenges()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Calculate completion rate for this challenge
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->userChallenges()->count();
        $completed = $this->completedParticipants()->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Get average progress of active participants
     */
    public function getAverageProgressAttribute(): float
    {
        return (float) $this->activeParticipants()
            ->avg('completion_rate') ?? 0;
    }

    /**
     * Update participant count (cache)
     */
    public function updateParticipantCount(): void
    {
        $this->participant_count = $this->userChallenges()
            ->whereIn('status', ['active', 'completed', 'expired'])
            ->count();
        $this->save();
    }

    /**
     * Scope for global challenges (visíveis para todos)
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_GLOBAL);
    }

    /**
     * Scope for team challenges (visíveis para membros do time)
     */
    public function scopeTeam($query)
    {
        return $query->where('visibility', self::VISIBILITY_TEAM);
    }

    /**
     * Scope for private challenges (apenas o criador)
     */
    public function scopePrivate($query)
    {
        return $query->where('visibility', self::VISIBILITY_PRIVATE);
    }

    /**
     * Scope for template challenges
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Scope for featured challenges
     */
    public function scopeFeatured($query)
    {
        // Featured aparece para qualquer usuário => somente global.
        return $query->where('is_featured', true)->where('visibility', self::VISIBILITY_GLOBAL);
    }

    /**
     * Scope for popular challenges (by participant count)
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('participant_count', 'desc')->limit($limit);
    }

    /**
     * Scope for challenges by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for challenges by difficulty
     */
    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Search challenges by title or description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
        });
    }

    /**
     * Get recommended challenges for user
     */
    public static function getRecommendedForUser(User $user, int $limit = 5)
    {
        // Basic recommendation logic - can be enhanced with ML later
        $userCategories = $user->userChallenges()
            ->with('challenge')
            ->get()
            ->pluck('challenge.category')
            ->filter()
            ->unique();

        $query = self::public()
            ->with(['tasks', 'creator'])
            ->withCount(['activeParticipants', 'completedParticipants']);

        if ($userCategories->isNotEmpty()) {
            $query->whereIn('category', $userCategories->toArray());
        }

        return $query->popular($limit)->get();
    }

    /**
     * Get challenge statistics
     */
    public function getStats(): array
    {
        return [
            'total_participants' => $this->participant_count,
            'active_participants' => $this->activeParticipants()->count(),
            'completed_participants' => $this->completedParticipants()->count(),
            'completion_rate' => $this->completion_rate,
            'average_progress' => $this->average_progress,
            'total_checkins' => $this->userChallenges()
                ->withCount('checkins')
                ->get()
                ->sum('checkins_count'),
        ];
    }
}
