<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\WhatsappSessionService;

class DopaController extends Controller
{
    /**
     * Show the DOPA Check dashboard
     */
    public function dashboard(Request $request, WhatsappSessionService $whatsappSessionService): Response
    {
        $user = $request->user();
        
        // Get user's current active challenge
        $currentChallenge = $user->activeChallenges()
            ->with(['challenge.tasks'])
            ->first();
        
        // Initialize variables
        $todayTasks = collect([]);
        $missingTasks = collect([]);
        $todayCheckins = collect([]);
        $currentDay = 0;
        
        if ($currentChallenge) {
            // Calculate current day
            $currentDay = $this->calculateCurrentDay($currentChallenge);
            
            // Get all tasks for the current challenge
            $allTasks = $currentChallenge->challenge->tasks()->orderBy('order')->get();
            
            // Get today's completed check-ins
            $todayCheckins = $currentChallenge->checkins()
                ->where('challenge_day', $currentDay)
                ->with('task')
                ->get()
                ->keyBy('task_id');
            // Build today's tasks with completion status
            $todayTasks = $allTasks->map(function ($task) use ($todayCheckins) {
                $checkin = $todayCheckins->get($task->id);
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'hashtag' => $task->hashtag,
                    'description' => $task->description,
                    'icon' => $task->icon,
                    'color' => $task->color,
                    'is_required' => $task->is_required,
                    'order' => $task->order,
                    'is_completed' => !is_null($checkin),
                    'checkin' => $checkin ? [
                        'id' => $checkin->id,
                        'image_url' => $checkin->image_url,
                        'message' => $checkin->message,
                        'source' => $checkin->source,
                        'checked_at' => $checkin->checked_at,
                        'ai_analysis' => $checkin->ai_analysis,
                        'confidence_score' => $checkin->confidence_score
                    ] : null,
                ];
            });
            
            // Get missing tasks (not completed today and required)
            $missingTasks = $todayTasks->filter(function ($task) {
                return !$task['is_completed'] && $task['is_required'];
            });
        }
        
        // Get user's overall stats
        $userStats = [];
        
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
        
        $whatsappSession = $user->whatsapp_number
            ? $whatsappSessionService->get($user->whatsapp_number)
            : null;
        
        $userArray = $user->toArray();
        $userArray['whatsapp_connected'] = $whatsappSession !== null;
        $userArray['whatsapp_session'] = $whatsappSession;
        
        return Inertia::render('Dashboard/Index', [
            'currentChallenge' => $currentChallenge ? [
                'id' => $currentChallenge->id,
                'status' => $currentChallenge->status,
                'started_at' => $currentChallenge->started_at,
                'current_day' => $currentDay,
                'total_checkins' => $currentChallenge->total_checkins,
                'streak_days' => $currentChallenge->streak_days,
                'best_streak' => $currentChallenge->best_streak,
                'completion_rate' => $currentChallenge->completion_rate,
                'challenge' => [
                    'id' => $currentChallenge->challenge->id,
                    'title' => $currentChallenge->challenge->title,
                    'description' => $currentChallenge->challenge->description,
                    'duration_days' => $currentChallenge->challenge->duration_days,
                    'category' => $currentChallenge->challenge->category,
                    'difficulty' => $currentChallenge->challenge->difficulty,
                    'image_url' => $currentChallenge->challenge->image_url,
                    'tasks' => $currentChallenge->challenge->tasks->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->name,
                            'hashtag' => $task->hashtag,
                            'description' => $task->description,
                            'icon' => $task->icon,
                            'color' => $task->color,
                            'is_required' => $task->is_required,
                            'order' => $task->order
                        ];
                    })
                ]
            ] : null,
            'todayTasks' => $todayTasks->values(),
            'missingTasks' => $missingTasks->values(),
            'todayCheckins' => $todayCheckins->values(),
            'userStats' => $userStats,
            'recommendedChallenges' => $recommendedChallenges,
            'recentCheckins' => $recentCheckins,
            'canCreateChallenge' => $canCreateChallenge,
            'auth' => [
                'user' => $userArray,
            ],
        ]);
    }

    /**
     * API endpoint para buscar tasks de hoje (AJAX)
     */
    public function todayTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentChallenge = $user->activeChallenges()->with('challenge.tasks')->first();

        if (!$currentChallenge) {
            return response()->json([
                'tasks' => [],
                'current_day' => 0,
                'challenge' => null
            ]);
        }

        $currentDay = $this->calculateCurrentDay($currentChallenge);
        $tasks = $currentChallenge->challenge->tasks;
        $todayTasks = [];

        foreach ($tasks as $task) {
            // Verificar se já fez check-in hoje
            $todayCheckin = Checkin::where('user_challenge_id', $currentChallenge->id)
                ->where('task_id', $task->id)
                ->where('challenge_day', $currentDay)
                ->whereNull('deleted_at')
                ->first();

            $todayTasks[] = [
                'id' => $task->id,
                'name' => $task->name,
                'hashtag' => $task->hashtag,
                'description' => $task->description,
                'icon' => $task->icon,
                'color' => $task->color,
                'is_required' => $task->is_required,
                'order' => $task->order,
                'is_completed' => !is_null($todayCheckin),
                'checkin' => $todayCheckin ? [
                    'id' => $todayCheckin->id,
                    'image_url' => $todayCheckin->image_url,
                    'message' => $todayCheckin->message,
                    'source' => $todayCheckin->source,
                    'checked_at' => $todayCheckin->checked_at,
                    'ai_analysis' => $todayCheckin->ai_analysis,
                    'confidence_score' => $todayCheckin->confidence_score
                ] : null
            ];
        }

        // Ordenar por order
        usort($todayTasks, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return response()->json([
            'tasks' => $todayTasks,
            'current_day' => $currentDay,
            'challenge' => [
                'id' => $currentChallenge->id,
                'title' => $currentChallenge->challenge->title,
                'total_checkins' => $currentChallenge->total_checkins,
                'streak_days' => $currentChallenge->streak_days,
                'completion_rate' => $currentChallenge->completion_rate
            ]
        ]);
    }

    /**
     * Quick stats para widgets (AJAX)
     */
    public function quickStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $stats = Cache::remember("user_quick_stats_{$user->id}", 900, function () use ($user) {
            $activeChallenge = $user->activeChallenges()->first();
            $totalChallenges = $user->userChallenges()->count();
            $completedChallenges = $user->userChallenges()->where('status', 'completed')->count();
            $totalCheckins = $user->checkins()->count();
            $todayCheckins = $user->checkins()->whereDate('checked_at', today())->count();
            
            return [
                'has_active_challenge' => !is_null($activeChallenge),
                'current_streak' => $activeChallenge?->streak_days ?? 0,
                'total_challenges' => $totalChallenges,
                'completed_challenges' => $completedChallenges,
                'completion_rate' => $totalChallenges > 0 ? round(($completedChallenges / $totalChallenges) * 100, 1) : 0,
                'total_checkins' => $totalCheckins,
                'today_checkins' => $todayCheckins,
                'whatsapp_connected' => $user->whatsappSession?->is_active ?? false,
                'is_pro' => $user->is_pro
            ];
        });

        return response()->json(['stats' => $stats]);
    }

    /**
     * Feed de atividades recentes
     */
    public function activityFeed(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Buscar check-ins recentes
        $recentCheckins = $user->checkins()
            ->with(['task', 'userChallenge.challenge'])
            ->orderBy('checked_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($checkin) {
                return [
                    'type' => 'checkin',
                    'id' => $checkin->id,
                    'task_name' => $checkin->task->name,
                    'task_hashtag' => $checkin->task->hashtag,
                    'challenge_title' => $checkin->userChallenge->challenge->title,
                    'checked_at' => $checkin->checked_at,
                    'source' => $checkin->source,
                    'has_image' => !is_null($checkin->image_url),
                    'challenge_day' => $checkin->challenge_day
                ];
            });

        return response()->json([
            'activities' => $recentCheckins,
            'has_more' => $recentCheckins->count() === 10
        ]);
    }

    /**
     * Calcular dia atual do desafio
     */
    private function calculateCurrentDay(UserChallenge $userChallenge): int
    {
        $startDate = $userChallenge->started_at;
        $today = now();
        $diffDays = $startDate->diffInDays($today) + 1;
        
        return (int) min($diffDays, $userChallenge->challenge->duration_days);
    }
}
