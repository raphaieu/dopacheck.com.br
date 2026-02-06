<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Carbon\CarbonInterface;

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
     * Início global do desafio (base para day index).
     * Fallback para started_at por segurança (dados legados).
     */
    public function getChallengeStartDate(): CarbonInterface
    {
        $start = $this->challenge?->start_date;
        if ($start) {
            return Carbon::parse($start)->startOfDay();
        }

        return $this->started_at instanceof CarbonInterface
            ? $this->started_at->copy()->startOfDay()
            : Carbon::parse($this->started_at)->startOfDay();
    }

    /**
     * Fim global do desafio (base para janela válida).
     * Fallback: start + duration - 1.
     */
    public function getChallengeEndDate(): CarbonInterface
    {
        $end = $this->challenge?->end_date;
        if ($end) {
            return Carbon::parse($end)->endOfDay();
        }

        $start = $this->getChallengeStartDate();
        $duration = (int) ($this->challenge?->duration_days ?? 1);
        $duration = max(1, $duration);

        return $start->copy()->addDays($duration - 1)->endOfDay();
    }

    /**
     * Data âncora para stats (nunca no futuro; respeita fim do desafio).
     */
    public function getStatsAnchorDate(): CarbonInterface
    {
        return now()->endOfDay()->min($this->getChallengeEndDate());
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
     * Check if today is completed (all required tasks done)
     */
    public function hasCompletedToday(): bool
    {
        $requiredTasks = $this->challenge->tasks()->where('is_required', true)->get();
        
        if ($requiredTasks->isEmpty()) {
            return true; // Se não há tarefas obrigatórias, considera completo
        }
        
        // Verificar check-ins de hoje para todas as tarefas obrigatórias
        $todayCheckins = $this->checkins()
            ->whereIn('task_id', $requiredTasks->pluck('id'))
            ->whereDate('checked_at', today())
            ->whereNull('deleted_at')
            ->pluck('task_id')
            ->toArray();
        
        // Verificar se todas as tarefas obrigatórias têm check-in hoje
        $requiredTaskIds = $requiredTasks->pluck('id')->toArray();
        $allCompleted = count($todayCheckins) === count($requiredTaskIds) && 
                       empty(array_diff($requiredTaskIds, $todayCheckins));
        
        return $allCompleted;
    }

    /**
     * Calculate days remaining
     * Se o dia atual está 100% completo, considera que esse dia já foi "consumido"
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        $baseRemaining = max(0, $this->challenge->duration_days - $this->current_day + 1);
        
        // Se o dia atual está completo, diminui 1 dia dos restantes
        if ($this->hasCompletedToday()) {
            return max(0, $baseRemaining - 1);
        }
        
        return $baseRemaining;
    }

    /**
     * Calculate days elapsed
     */
    public function getDaysElapsedAttribute(): int
    {
        if ($this->status === 'active') {
            $start = $this->getChallengeStartDate();
            $anchor = $this->getStatsAnchorDate()->startOfDay();
            $daysSinceStart = $start->diffInDays($anchor, false) + 1; // signed
            return max(0, (int) $daysSinceStart);
        }

        return $this->current_day;
    }

    /**
     * Check if challenge is completed (with success)
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if challenge is finalized (completed or expired)
     */
    public function getIsFinalizedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'expired']);
    }

    /**
     * Get progress percentage based on completed days (not just current_day)
     * Progresso geral do desafio: quantos dias foram 100% completados.
     * Se o desafio ainda não começou (start_date no futuro), retorna 0.
     */
    public function getProgressPercentageAttribute(): float
    {
        $startDate = $this->getChallengeStartDate()->startOfDay();
        $today = now()->startOfDay();
        if ($today->lt($startDate)) {
            return 0;
        }

        $durationDays = $this->challenge->duration_days;
        $completedDays = $this->getCompletedDaysCount();
        return round(($completedDays / $durationDays) * 100, 2);
    }

    /**
     * Progresso do dia: % de tarefas obrigatórias feitas hoje (0-100).
     * Usado no spinner da lista de relatórios. Fora do período do desafio retorna 0.
     */
    public function getTodayProgressPercentageAttribute(): float
    {
        $startDate = $this->getChallengeStartDate()->startOfDay();
        $endDate = $this->getChallengeEndDate()->startOfDay();
        $today = now()->startOfDay();
        if ($today->lt($startDate) || $today->gt($endDate)) {
            return 0;
        }

        $requiredTasks = $this->challenge->tasks()->where('is_required', true)->get();
        if ($requiredTasks->isEmpty()) {
            return 100; // sem obrigatórias = dia considerado completo
        }

        $requiredCount = $requiredTasks->count();
        $doneToday = $this->checkins()
            ->whereIn('task_id', $requiredTasks->pluck('id'))
            ->whereDate('checked_at', $today)
            ->whereNull('deleted_at')
            ->pluck('task_id')
            ->unique()
            ->count();

        return $requiredCount > 0 ? round(($doneToday / $requiredCount) * 100, 2) : 0;
    }

    /**
     * Get count of days that were 100% completed (all required tasks done)
     */
    public function getCompletedDaysCount(): int
    {
        $startDate = $this->getChallengeStartDate()->startOfDay();
        $today = now()->startOfDay();
        if ($today->lt($startDate)) {
            return 0;
        }

        $requiredTasksCount = $this->challenge->tasks()->where('is_required', true)->count();
        if ($requiredTasksCount === 0) {
            $endDate = $this->getChallengeEndDate()->startOfDay();
            $anchorDay = $today->copy()->min($endDate);
            $daysElapsed = $startDate->diffInDays($anchorDay, false) + 1;
            $daysElapsed = max(0, min($daysElapsed, $this->challenge->duration_days));
            return $daysElapsed;
        }

        $endDate = $this->getChallengeEndDate();
        
        // Buscar todos os check-ins obrigatórios do desafio
        $allCheckins = $this->checkins()
            ->whereIn('task_id', $this->challenge->tasks()->where('is_required', true)->pluck('id'))
            ->whereDate('checked_at', '>=', $startDate)
            ->whereDate('checked_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->get();
        
        // Agrupar check-ins por data
        $checkinsByDate = $allCheckins->groupBy(function ($checkin) {
            return $checkin->checked_at->format('Y-m-d');
        });
        
        // Contar quantos dias têm todas as tarefas obrigatórias completas
        $completedDays = 0;
        $requiredTaskIds = $this->challenge->tasks()->where('is_required', true)->pluck('id')->toArray();
        
        foreach ($checkinsByDate as $date => $checkins) {
            $checkinTaskIds = $checkins->pluck('task_id')->unique()->toArray();
            
            // Verificar se todas as tarefas obrigatórias têm check-in nesta data
            $allCompleted = count($checkinTaskIds) === count($requiredTaskIds) && 
                          empty(array_diff($requiredTaskIds, $checkinTaskIds));
            
            if ($allCompleted) {
                $completedDays++;
            }
        }
        
        return min($completedDays, $this->challenge->duration_days);
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

        // Dias desde o início global do desafio até hoje (ou até o fim do desafio)
        $startDate = $this->getChallengeStartDate();
        $anchorDay = $this->getStatsAnchorDate()->startOfDay(); // min(today, end_date)
        $duration = (int) $this->challenge->duration_days;

        // Calcula o número de dias válidos (não pode passar do duration_days)
        $daysSinceStart = $startDate->diffInDays($anchorDay, false) + 1; // signed
        $validDays = max(0, min($daysSinceStart, $duration));

        // Check-ins esperados
        $expected = $requiredTasksCount * $validDays;

        // Check-ins realizados (apenas tarefas obrigatórias e apenas até hoje)
        // Considera apenas check-ins até a data de hoje para evitar contar check-ins futuros
        $actual = $this->checkins()
            ->whereIn('task_id', $this->challenge->tasks()->where('is_required', true)->pluck('id'))
            ->whereDate('checked_at', '<=', $anchorDay)
            ->count();

        $this->completion_rate = $expected > 0 ? round(($actual / $expected) * 100, 2) : 0;
        $this->save();
    }

    /**
     * Update current day based on start date
     * Marca automaticamente como completo se ultrapassar duration_days
     */
    public function updateCurrentDay(): void
    {
        if ($this->status !== 'active') {
            return;
        }

        // Usa período global do desafio como referência
        $startDate = $this->getChallengeStartDate();
        $today = now()->startOfDay();
        $endDay = $this->getChallengeEndDate()->startOfDay();
        $anchorDay = $today->copy()->min($endDay); // clamp para cálculo do current_day

        // Calcular diferença em dias (diffInDays retorna o número de dias completos)
        // Se começou hoje, diffInDays = 0, então current_day = 1
        // Se começou ontem, diffInDays = 1, então current_day = 2
        $daysSinceStartClamped = $startDate->diffInDays($anchorDay, false) + 1; // signed
        if ($daysSinceStartClamped < 1) {
            $daysSinceStartClamped = 1; // desafio ainda não começou (start_date no futuro)
        }
        
        // Limita ao duration_days do desafio
        $newCurrentDay = min($daysSinceStartClamped, $this->challenge->duration_days);
        
        // Garante que seja pelo menos 1
        $this->current_day = max(1, $newCurrentDay);

        // Check if challenge should be finalized (completed or expired)
        // O desafio só é finalizado quando JÁ PASSOU do último dia válido
        // Para um desafio de N dias, o último dia válido é o dia N
        // Então só finaliza quando daysSinceStart > duration_days
        // (ou seja, quando já passou do último dia)
        // O método complete() verifica se completou todos os check-ins obrigatórios
        // Se sim, marca como 'completed', se não, marca como 'expired'
        $daysSinceStartReal = $startDate->diffInDays($today, false) + 1; // signed (não clamped)
        if ($daysSinceStartReal > $this->challenge->duration_days) {
            $this->complete(); // Verifica se completou tudo, senão marca como expired
        } else {
            $this->save();
        }
        
        // Nota: completion_rate NÃO é atualizado aqui para manter rastreabilidade
        // Ele é atualizado apenas quando há check-ins (via updateStats)
        // Isso permite que relatórios calculem o progresso histórico baseado nos check-ins
    }

    /**
     * Check if user completed all required check-ins for the challenge
     */
    public function hasCompletedAllRequiredCheckins(): bool
    {
        $requiredTasksCount = $this->challenge->tasks()->where('is_required', true)->count();
        
        if ($requiredTasksCount === 0) {
            return true; // Se não há tarefas obrigatórias, considera completo
        }
        
        // Verificar se completou todos os check-ins obrigatórios para todos os dias
        $startDate = $this->getChallengeStartDate();
        $endDate = $this->getChallengeEndDate();
        
        // Contar check-ins obrigatórios realizados
        $actualCheckins = $this->checkins()
            ->whereIn('task_id', $this->challenge->tasks()->where('is_required', true)->pluck('id'))
            ->whereDate('checked_at', '>=', $startDate)
            ->whereDate('checked_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->count();
        
        // Check-ins esperados = tarefas obrigatórias × dias do desafio
        $expectedCheckins = $requiredTasksCount * $this->challenge->duration_days;
        
        return $actualCheckins >= $expectedCheckins;
    }

    /**
     * Mark challenge as completed (only if all required check-ins are done)
     */
    public function complete(): void
    {
        // Verificar se completou todos os check-ins obrigatórios
        if (!$this->hasCompletedAllRequiredCheckins()) {
            // Se não completou tudo, marca como expirado ao invés de completo
            $this->expire();
            return;
        }
        
        $this->status = 'completed';
        $this->completed_at = now();
        $this->current_day = $this->challenge->duration_days;
        $this->updateCompletionRate();
        $this->save();

        // Update challenge participant count
        $this->challenge->updateParticipantCount();
    }

    /**
     * Mark challenge as expired (time ended but not all required check-ins completed)
     */
    public function expire(): void
    {
        $this->status = 'expired';
        $this->completed_at = now(); // Mantém completed_at para rastreabilidade
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
        // Baseia challenge_day na data real do check-in (checked_at) e no start_date global do desafio,
        // para evitar conflitos quando existem check-ins retroativos.
        $checkedAt = $data['checked_at'] ?? now();
        $checkedAtCarbon = $checkedAt instanceof \Carbon\CarbonInterface
            ? $checkedAt
            : \Carbon\Carbon::parse($checkedAt);

        $challengeStart = $this->getChallengeStartDate()->startOfDay();
        $checkedDay = $checkedAtCarbon->copy()->startOfDay();
        $day = (int) ($challengeStart->diffInDays($checkedDay, false) + 1);
        $duration = (int) ($this->challenge?->duration_days ?? 1);
        $duration = max(1, $duration);
        $day = max(1, min($day, $duration));

        $checkin = $this->checkins()->create(array_merge([
            'task_id' => $task->id,
            'challenge_day' => $day,
            'checked_at' => $checkedAtCarbon,
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

        // Streaks:
        // - streak_days: sequência atual (terminando hoje/anchor)
        // - best_streak: melhor sequência histórica (importante para retroativos)
        $this->streak_days = $this->calculateCurrentStreak();
        $best = $this->calculateBestStreak();
        $this->best_streak = max((int) $this->best_streak, (int) $best);

        // Update completion rate
        $this->updateCompletionRate();

        // Update current day
        $this->updateCurrentDay();
    }

    /**
     * Calcula a melhor sequência (máximo) dentro da janela do desafio até o anchor.
     * Necessário para retroativos, pois a sequência atual pode ser 0 mesmo com histórico consistente.
     */
    private function calculateBestStreak(): int
    {
        $tasksPerDay = (int) $this->challenge->tasks()->required()->count();
        if ($tasksPerDay === 0) {
            return 0;
        }

        $startDate = $this->getChallengeStartDate()->startOfDay();
        $anchor = $this->getStatsAnchorDate()->startOfDay();

        $checkinsByDate = $this->checkins()
            ->whereDate('checked_at', '>=', $startDate)
            ->whereDate('checked_at', '<=', $anchor)
            ->selectRaw('DATE(checked_at) as check_date, COUNT(*) as check_count')
            ->groupByRaw('DATE(checked_at)')
            ->reorder()
            ->get()
            ->keyBy('check_date');

        $best = 0;
        $current = 0;
        $date = $startDate->copy();

        while ($date->lessThanOrEqualTo($anchor)) {
            $dateString = $date->format('Y-m-d');
            $checkinsForDay = $checkinsByDate->get($dateString)?->check_count ?? 0;

            if ($checkinsForDay >= $tasksPerDay) {
                $current++;
                if ($current > $best) {
                    $best = $current;
                }
            } else {
                $current = 0;
            }

            $date->addDay();
        }

        return $best;
    }

    /**
     * Calculate current streak (otimizado)
     * Usa query única para buscar todos os check-ins necessários
     */
    private function calculateCurrentStreak(): int
    {
        $tasksPerDay = (int) $this->challenge->tasks()->required()->count();
        
        if ($tasksPerDay === 0) {
            return 0;
        }

        // Buscar todos os check-ins desde o início do desafio até hoje
        // Agrupar por data e contar quantos check-ins por dia
        // Usar reorder() para remover o orderBy padrão do relacionamento
        $startDate = $this->getChallengeStartDate();
        $anchor = $this->getStatsAnchorDate()->endOfDay();

        $checkinsByDate = $this->checkins()
            ->whereDate('checked_at', '>=', $startDate)
            ->whereDate('checked_at', '<=', $anchor)
            ->selectRaw('DATE(checked_at) as check_date, COUNT(*) as check_count')
            ->groupByRaw('DATE(checked_at)')
            ->reorder() // Remove qualquer orderBy padrão do relacionamento
            ->orderByRaw('DATE(checked_at) DESC')
            ->get()
            ->keyBy('check_date');

        $streak = 0;
        $date = $anchor->copy()->startOfDay();

        // Iterar apenas pelos dias que têm check-ins, começando de hoje
        while ($date->greaterThanOrEqualTo($startDate->toDateString())) {
            $dayNumber = $startDate->diffInDays($date, false) + 1;
            
            if ($dayNumber > (int) $this->current_day) {
                break;
            }

            $dateString = $date->format('Y-m-d');
            $checkinsForDay = $checkinsByDate->get($dateString)?->check_count ?? 0;

            if ($checkinsForDay >= $tasksPerDay) {
                $streak++;
                $date = $date->copy()->subDay();
            } else {
                break;
            }
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
     * Scope for completed challenges (with success)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for expired challenges
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for finalized challenges (completed or expired)
     */
    public function scopeFinalized($query)
    {
        return $query->whereIn('status', ['completed', 'expired']);
    }

    /**
     * Scope for challenges with high completion rate
     */
    public function scopeHighPerformance($query, float $threshold = 80.0)
    {
        return $query->where('completion_rate', '>=', $threshold);
    }
}
