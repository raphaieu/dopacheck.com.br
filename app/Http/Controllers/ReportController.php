<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Show reports index
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get all user challenges with stats
        $userChallenges = $user->userChallenges()
            ->with(['challenge.tasks'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($userChallenge) {
                return [
                    'id' => $userChallenge->id,
                    'challenge' => [
                        'id' => $userChallenge->challenge->id,
                        'title' => $userChallenge->challenge->title,
                        'duration_days' => $userChallenge->challenge->duration_days,
                        'category' => $userChallenge->challenge->category,
                    ],
                    'status' => $userChallenge->status,
                    'started_at' => $userChallenge->started_at,
                    'completed_at' => $userChallenge->completed_at,
                    'current_day' => $userChallenge->current_day,
                    'total_checkins' => $userChallenge->total_checkins,
                    'streak_days' => $userChallenge->streak_days,
                    'best_streak' => $userChallenge->best_streak,
                    'completion_rate' => $userChallenge->completion_rate,
                    'progress_percentage' => $userChallenge->progress_percentage,
                ];
            });
        
        // Calculate overall stats
        $overallStats = [
            'total_challenges' => $userChallenges->count(),
            'active_challenges' => $userChallenges->where('status', 'active')->count(),
            'completed_challenges' => $userChallenges->where('status', 'completed')->count(),
            'total_checkins' => $user->checkins()->count(),
            'current_streak' => $user->activeChallenges()->first()?->streak_days ?? 0,
            'best_streak' => $userChallenges->max('best_streak') ?? 0,
            'average_completion_rate' => $userChallenges->avg('completion_rate') ?? 0,
        ];
        
        return Inertia::render('Reports/Index', [
            'userChallenges' => $userChallenges,
            'overallStats' => $overallStats,
        ]);
    }
    
    /**
     * Show detailed report for a specific challenge
     */
    public function challenge(Request $request, UserChallenge $userChallenge): Response
    {
        $user = $request->user();
        
        // Verify ownership
        if ($userChallenge->user_id !== $user->id) {
            abort(403);
        }
        
        $userChallenge->load(['challenge.tasks', 'checkins.task']);
        
        // Atualizar current_day antes de gerar o relatório
        $userChallenge->updateCurrentDay();
        $userChallenge->refresh();
        
        // Get progress by day
        $progressByDay = [];
        $startDate = $userChallenge->started_at->copy()->startOfDay();
        
        for ($day = 1; $day <= $userChallenge->current_day; $day++) {
            $dayCheckins = $userChallenge->checkins()
                ->where('challenge_day', $day)
                ->get();
            
            $dayTasks = $userChallenge->challenge->tasks->map(function ($task) use ($dayCheckins) {
                $checkin = $dayCheckins->where('task_id', $task->id)->first();
                return [
                    'task' => $task,
                    'completed' => !is_null($checkin),
                    'checkin' => $checkin,
                ];
            });
            
            // Calcular a data correta do dia (usando startOfDay para garantir consistência)
            $dayDate = $startDate->copy()->addDays($day - 1);
            
            $progressByDay[] = [
                'day' => $day,
                'date' => $dayDate->format('Y-m-d'),
                'tasks' => $dayTasks,
                'completed_count' => $dayTasks->where('completed', true)->count(),
                'total_count' => $dayTasks->count(),
            ];
        }
        
        return Inertia::render('Reports/Challenge', [
            'userChallenge' => $userChallenge,
            'progressByDay' => $progressByDay,
        ]);
    }
    
    /**
     * Export reports data
     */
    public function export(Request $request)
    {
        $user = $request->user();
        
        // TODO: Implement export to CSV/PDF
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
