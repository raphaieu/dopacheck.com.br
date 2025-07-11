<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserChallenge extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'challenge_id',
        'status',
        'started_at',
        'completed_at',
        'paused_at',
        'current_day',
        'total_checkins',
        'streak_days',
        'best_streak',
        'completion_rate',
        'stats',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'paused_at' => 'datetime',
            'stats' => 'array',
            'completion_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the user who owns this challenge participation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the challenge
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Get check-ins for this user challenge
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class)->orderBy('checked_at', 'desc');
    }

    /**
     * Get today's check-ins
     */
    public function todayCheckins(): HasMany
    {
        return $this->checkins()->whereDate('checked_at', today());
    }

    /**
     * Get check-ins for specific day
     */
    public function checkinsForDay(int $day): HasMany
    {
        return $this->checkins()->where('challenge_day', $day);
    }

    /**
     * Calculate days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return max(0, $this->challenge->duration_days - $this->current_day + 1);
    }

    /**
     * Calculate days elapsed
     */
    public function getDaysElapsedAttribute(): int
    {
        if ($this->status === 'active') {
            return (int) ($this->started_at->diffInDays(now()) + 1);
        }

        return $this->current_day;
    }

    /**
     * Check if challenge is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || 
               $this->current_day >= $this->challenge->duration_days;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        return round((float) $this->current_day / (float) $this->challenge->duration_days * 100, 2);
    }

    /**
     * Get expected check-ins count
     */
    public function getExpectedCheckinsAttribute(): int
    {
        $tasksPerDay = $this->challenge->tasks()->required()->count();
        return (int) $this->current_day * $tasksPerDay;
    }

    /**
     * Update completion rate
     */
    public function updateCompletionRate(): void
    {
        // Tarefas obrigatórias do desafio
        $requiredTasksCount = $this->challenge->tasks()->where('is_required', true)->count();

        // Dias desde o início do desafio até hoje (ou até o fim do desafio)
        $startDate = $this->started_at->copy()->startOfDay();
        $today = now()->startOfDay();
        $duration = $this->challenge->duration_days;

        // Calcula o número de dias válidos (não pode passar do duration_days)
        $daysSinceStart = $startDate->diffInDays($today) + 1;
        $validDays = min($daysSinceStart, $duration);

        // Check-ins esperados
        $expected = $requiredTasksCount * $validDays;

        // Check-ins realizados (apenas tarefas obrigatórias)
        $actual = $this->checkins()
            ->whereIn('task_id', $this->challenge->tasks()->where('is_required', true)->pluck('id'))
            ->count();

        $this->completion_rate = $expected > 0 ? round(($actual / $expected) * 100, 2) : 0;
        $this->save();
    }

    /**
     * Update current day based on start date
     */
    public function updateCurrentDay(): void
    {
        if ($this->status !== 'active') {
            return;
        }

        $daysSinceStart = $this->started_at->diffInDays(now()) + 1;
        $this->current_day = min($daysSinceStart, $this->challenge->duration_days);

        // Check if challenge should be completed
        if ($this->current_day >= $this->challenge->duration_days) {
            $this->complete();
        } else {
            $this->save();
        }
    }

    /**
     * Mark challenge as completed
     */
    public function complete(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->current_day = $this->challenge->duration_days;
        $this->updateCompletionRate();
        $this->save();

        // Update challenge participant count
        $this->challenge->updateParticipantCount();
    }

    /**
     * Pause the challenge
     */
    public function pause(): void
    {
        $this->status = 'paused';
        $this->paused_at = now();
        $this->save();
    }

    /**
     * Resume the challenge
     */
    public function resume(): void
    {
        if ($this->status !== 'paused' || !$this->paused_at) {
            return;
        }

        // Calculate paused time before clearing the field
        $pausedDays = $this->paused_at->diffInDays(now());
        
        $this->status = 'active';
        $this->paused_at = null;
        
        // Adjust start date to account for paused time
        $this->started_at = $this->started_at->addDays($pausedDays);
        
        $this->save();
    }

    /**
     * Abandon the challenge
     */
    public function abandon(): void
    {
        $this->status = 'abandoned';
        $this->save();

        // Update challenge participant count
        $this->challenge->updateParticipantCount();
    }

    /**
     * Add a check-in and update stats
     */
    public function addCheckin(ChallengeTask $task, array $data = []): Checkin
    {
        $checkin = $this->checkins()->create(array_merge([
            'task_id' => $task->id,
            'challenge_day' => $this->current_day,
            'checked_at' => now(),
        ], $data));

        $this->updateStats();

        return $checkin;
    }

    /**
     * Update all stats (counts, streaks, completion rate)
     */
    public function updateStats(): void
    {
        // Update total check-ins
        $this->total_checkins = $this->checkins()->count();

        // Update current streak
        $this->streak_days = $this->calculateCurrentStreak();

        // Update best streak
        if ($this->streak_days > $this->best_streak) {
            $this->best_streak = $this->streak_days;
        }

        // Update completion rate
        $this->updateCompletionRate();

        // Update current day
        $this->updateCurrentDay();
    }

    /**
     * Calculate current streak
     */
    private function calculateCurrentStreak(): int
    {
        $streak = 0;
        $date = today();
        $tasksPerDay = (int) $this->challenge->tasks()->required()->count();
        $maxDays = 365; // Limite de segurança para evitar loop infinito
        $daysChecked = 0;

        while ($date->greaterThanOrEqualTo($this->started_at->toDateString()) && $daysChecked < $maxDays) {
            $dayNumber = $this->started_at->diffInDays($date) + 1;
            
            if ($dayNumber > $this->current_day) {
                break;
            }

            $checkinsForDay = $this->checkins()
                ->whereDate('checked_at', $date)
                ->count();

            if ($checkinsForDay >= $tasksPerDay) {
                $streak++;
                $date = $date->copy()->subDay(); // Usar copy() para não modificar a data original
            } else {
                break;
            }
            
            $daysChecked++;
        }

        return $streak;
    }

    /**
     * Get missing tasks for today
     */
    public function getMissingTasksForToday()
    {
        $todayCheckins = $this->todayCheckins()
            ->pluck('task_id')
            ->toArray();

        return $this->challenge->tasks()
            ->required()
            ->whereNotIn('id', $todayCheckins)
            ->get();
    }

    /**
     * Get tasks status for specific day
     */
    public function getTasksStatusForDay(int $day): array
    {
        $tasks = $this->challenge->tasks()->get();
        $checkins = $this->checkinsForDay($day)->get()->keyBy('task_id');

        return $tasks->map(function ($task) use ($checkins) {
            return [
                'task' => $task,
                'completed' => $checkins->has($task->id),
                'checkin' => $checkins->get($task->id),
            ];
        })->toArray();
    }

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed challenges
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for challenges with high completion rate
     */
    public function scopeHighPerformance($query, float $threshold = 80.0)
    {
        return $query->where('completion_rate', '>=', $threshold);
    }
}
