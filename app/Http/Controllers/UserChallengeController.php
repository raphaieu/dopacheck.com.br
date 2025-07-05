<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserChallenge;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserChallengeController extends Controller
{
    /**
     * Display user's challenges
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $userChallenges = $user->userChallenges()
            ->with(['challenge.tasks'])
            ->orderBy('started_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('UserChallenges/Index', [
            'userChallenges' => $userChallenges
        ]);
    }
    
    /**
     * Show specific user challenge
     */
    public function show(Request $request, UserChallenge $userChallenge): Response
    {
        // Check authorization
        if ($userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $userChallenge->load(['challenge.tasks', 'checkins.task']);
        
        return Inertia::render('UserChallenges/Show', [
            'userChallenge' => $userChallenge
        ]);
    }
    
    /**
     * Pause a user challenge
     */
    public function pause(Request $request, UserChallenge $userChallenge): RedirectResponse
    {
        // Check authorization
        if ($userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if ($userChallenge->status !== 'active') {
            return back()->with('error', 'Apenas desafios ativos podem ser pausados.');
        }
        
        $userChallenge->pause();
        
        return back()->with('success', 'Desafio pausado. Você pode retomar quando quiser.');
    }
    
    /**
     * Resume a paused user challenge
     */
    public function resume(Request $request, UserChallenge $userChallenge): RedirectResponse
    {
        // Check authorization
        if ($userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if ($userChallenge->status !== 'paused') {
            return back()->with('error', 'Apenas desafios pausados podem ser retomados.');
        }
        
        $userChallenge->resume();
        
        return back()->with('success', 'Desafio retomado! Continue sua jornada.');
    }
    
    /**
     * Abandon a user challenge
     */
    public function abandon(Request $request, UserChallenge $userChallenge): RedirectResponse
    {
        // Check authorization
        if ($userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if (!in_array($userChallenge->status, ['active', 'paused'])) {
            return back()->with('error', 'Este desafio não pode ser abandonado.');
        }
        
        $userChallenge->abandon();
        
        return back()->with('success', 'Desafio abandonado. Você pode criar um novo quando quiser!');
    }

    /**
     * Progresso detalhado de um user challenge (API)
     */
    public function progress(Request $request, UserChallenge $userChallenge): JsonResponse
    {
        // Verificar autorização
        if ($userChallenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        
        $currentDay = $this->calculateCurrentDay($userChallenge);
        $tasks = $userChallenge->challenge->tasks;
        
        // Buscar todos os check-ins do desafio
        $checkins = $userChallenge->checkins()
            ->with('task')
            ->orderBy('challenge_day')
            ->orderBy('checked_at')
            ->get();
        
        // Organizar progresso por dia
        $progressByDay = [];
        for ($day = 1; $day <= $currentDay; $day++) {
            $dayCheckins = $checkins->where('challenge_day', $day);
            $dayTasks = $tasks->map(function ($task) use ($dayCheckins) {
                $checkin = $dayCheckins->where('task_id', $task->id)->first();
                return [
                    'task_id' => $task->id,
                    'task_name' => $task->name,
                    'hashtag' => $task->hashtag,
                    'icon' => $task->icon,
                    'color' => $task->color,
                    'is_required' => $task->is_required,
                    'is_completed' => !is_null($checkin),
                    'checkin' => $checkin ? [
                        'id' => $checkin->id,
                        'checked_at' => $checkin->checked_at,
                        'source' => $checkin->source,
                        'has_image' => !is_null($checkin->image_url),
                        'message' => $checkin->message
                    ] : null
                ];
            });
            
            $completedCount = $dayTasks->where('is_completed', true)->count();
            $requiredCount = $dayTasks->where('is_required', true)->count();
            $requiredCompleted = $dayTasks->where('is_required', true)->where('is_completed', true)->count();
            
            $progressByDay[] = [
                'day' => $day,
                'date' => $userChallenge->started_at->addDays($day - 1)->format('Y-m-d'),
                'day_name' => $userChallenge->started_at->addDays($day - 1)->format('D'),
                'tasks' => $dayTasks->values(),
                'completed_count' => $completedCount,
                'total_count' => $tasks->count(),
                'required_completed' => $requiredCompleted,
                'required_count' => $requiredCount,
                'is_complete' => $requiredCompleted === $requiredCount,
                'completion_rate' => $tasks->count() > 0 ? round(($completedCount / $tasks->count()) * 100, 1) : 0
            ];
        }
        
        return response()->json([
            'progress' => $progressByDay,
            'current_day' => $currentDay,
            'total_days' => $userChallenge->challenge->duration_days,
            'challenge' => [
                'id' => $userChallenge->challenge->id,
                'title' => $userChallenge->challenge->title,
                'description' => $userChallenge->challenge->description,
                'duration_days' => $userChallenge->challenge->duration_days
            ],
            'overall_stats' => [
                'total_checkins' => $userChallenge->total_checkins,
                'streak_days' => $userChallenge->streak_days,
                'best_streak' => $userChallenge->best_streak,
                'completion_rate' => $userChallenge->completion_rate,
                'days_remaining' => max(0, $userChallenge->challenge->duration_days - $currentDay + 1),
                'status' => $userChallenge->status,
                'started_at' => $userChallenge->started_at,
                'expected_end_date' => $userChallenge->started_at->addDays($userChallenge->challenge->duration_days - 1)
            ]
        ]);
    }

    /**
     * Calcular dia atual do desafio
     */
    private function calculateCurrentDay($userChallenge): int
    {
        $startDate = $userChallenge->started_at;
        $today = now();
        $diffDays = $startDate->diffInDays($today) + 1;
        
        return min($diffDays, $userChallenge->challenge->duration_days);
    }
}
