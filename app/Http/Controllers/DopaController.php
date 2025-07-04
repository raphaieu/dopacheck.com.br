<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DopaController extends Controller
{
    /**
     * Show the DOPA Check dashboard
     */
    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        
        // Get user's current active challenge
        $currentChallenge = $user->currentChallenge();
        
        // Get today's tasks if user has an active challenge
        $todayTasks = [];
        $missingTasks = [];
        $todayCheckins = [];
        
        if ($currentChallenge) {
            // Get all tasks for the current challenge
            $allTasks = $currentChallenge->challenge->tasks()->orderBy('order')->get();
            
            // Get today's completed check-ins
            $todayCheckins = $currentChallenge->todayCheckins()
                ->with('task')
                ->get()
                ->keyBy('task_id');
            
            // Build today's tasks with completion status
            $todayTasks = $allTasks->map(function ($task) use ($todayCheckins) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'hashtag' => $task->hashtag,
                    'hashtag_with_prefix' => $task->hashtag_with_prefix,
                    'description' => $task->description,
                    'icon' => $task->icon,
                    'color' => $task->color,
                    'is_required' => $task->is_required,
                    'completed' => $todayCheckins->has($task->id),
                    'checkin' => $todayCheckins->get($task->id),
                    'example_usage' => $task->example_usage,
                ];
            });
            
            // Get missing tasks (not completed today)
            $missingTasks = $todayTasks->where('completed', false)->where('is_required', true);
        }
        
        // Get user's overall stats
        $userStats = $user->calculateStats();
        
        // Get popular challenges for recommendations
        $recommendedChallenges = Challenge::getRecommendedForUser($user, 3);
        
        // Get recent checkins for activity feed
        $recentCheckins = $user->checkins()
            ->with(['task', 'userChallenge.challenge'])
            ->latest('checked_at')
            ->limit(5)
            ->get();
        
        // Check if user can create more challenges
        $canCreateChallenge = $user->canCreateChallenge();
        
        return Inertia::render('DOPA/Dashboard', [
            'currentChallenge' => $currentChallenge ? [
                'id' => $currentChallenge->id,
                'challenge' => $currentChallenge->challenge,
                'status' => $currentChallenge->status,
                'current_day' => $currentChallenge->current_day,
                'days_remaining' => $currentChallenge->days_remaining,
                'progress_percentage' => $currentChallenge->progress_percentage,
                'completion_rate' => $currentChallenge->completion_rate,
                'streak_days' => $currentChallenge->streak_days,
                'best_streak' => $currentChallenge->best_streak,
                'started_at' => $currentChallenge->started_at,
            ] : null,
            'todayTasks' => $todayTasks,
            'missingTasks' => $missingTasks->values(),
            'todayCheckins' => $todayCheckins->values(),
            'userStats' => $userStats,
            'recommendedChallenges' => $recommendedChallenges,
            'recentCheckins' => $recentCheckins,
            'canCreateChallenge' => $canCreateChallenge,
            'whatsappSession' => $user->whatsappSession,
        ]);
    }
}
