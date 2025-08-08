<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Checkin;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ChallengeController extends Controller
{
    /**
     * Display challenges listing
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $showPrivate = $request->boolean('show_private', false);
        
        // Base query - public challenges
        $query = Challenge::public()->with(['creator', 'tasks']);
        
        // If user wants to see private challenges, include them
        if ($showPrivate && $user) {
            $query = Challenge::where(function ($q) use ($user) {
                $q->public() // Public challenges
                  ->orWhere(function ($subQ) use ($user) {
                      $subQ->where('is_public', false) // Private challenges
                           ->where('created_by', $user->id); // Created by current user
                  });
            })->with(['creator', 'tasks']);
        }
        
        // Filter by category
        if ($request->category) {
            $query->byCategory($request->category);
        }
        
        // Filter by difficulty
        if ($request->difficulty) {
            $query->byDifficulty($request->difficulty);
        }
        
        // Search
        if ($request->search) {
            $query->search($request->search);
        }
        
        // Sort
        $sort = $request->sort ?? 'newest';
        match ($sort) {
            'popular' => $query->popular(),
            'newest' => $query->latest(),
            'featured' => $query->featured(),
            default => $query->popular(),
        };
        
        $challenges = $query->paginate(6);
        
        // Get user participation info for each challenge
        $user = $request->user();
        if ($user) {
            $userChallengeIds = $user->userChallenges()
                ->whereIn('challenge_id', $challenges->pluck('id'))
                ->where('status', 'active')
                ->pluck('challenge_id')
                ->toArray();
            
            // Add user participation info to each challenge
            $challenges->getCollection()->transform(function ($challenge) use ($userChallengeIds) {
                $challenge->user_is_participating = in_array($challenge->id, $userChallengeIds);
                return $challenge;
            });
        }
        
        // Get featured challenges for hero section
        $featuredChallenges = Challenge::featured()
            ->with(['creator', 'tasks'])
            ->limit(3)
            ->get();
        
        // Add user participation info to featured challenges
        if ($user) {
            $featuredUserChallengeIds = $user->userChallenges()
                ->whereIn('challenge_id', $featuredChallenges->pluck('id'))
                ->where('status', 'active')
                ->pluck('challenge_id')
                ->toArray();
            
            $featuredChallenges->transform(function ($challenge) use ($featuredUserChallengeIds) {
                $challenge->user_is_participating = in_array($challenge->id, $featuredUserChallengeIds);
                return $challenge;
            });
        }
        
        // Get categories for filter
        $categories = Challenge::public()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        
        return Inertia::render('Challenges/Index', [
            'challenges' => $challenges,
            'featuredChallenges' => $featuredChallenges,
            'categories' => $categories,
            'filters' => [
                'category' => $request->category,
                'difficulty' => $request->difficulty,
                'search' => $request->search,
                'sort' => $sort,
                'show_private' => $showPrivate,
            ],
        ]);
    }
    
    /**
     * Show challenge details
     */
    public function show(Request $request, Challenge $challenge): Response
    {
        $challenge->load(['creator', 'tasks', 'activeParticipants.user']);
        
        $user = $request->user();
        $userChallenge = null;
        $canJoin = true;
        
        if ($user) {
            $userChallenge = $user->userChallenges()
                ->where('challenge_id', $challenge->id)
                ->where('status', 'active')
                ->first();
            
            $canJoin = !$userChallenge && $user->canCreateChallenge();
        }
        
        // Get challenge stats
        $stats = $challenge->getStats();
        
        // Get recent participants
        $recentParticipants = $challenge->activeParticipants()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
        
        return Inertia::render('Challenges/Show', [
            'challenge' => $challenge,
            'userChallenge' => $userChallenge,
            'canJoin' => $canJoin,
            'stats' => $stats,
            'recentParticipants' => $recentParticipants,
            'isAuthenticated' => (bool) $user,
        ]);
    }
    
    /**
     * Show all participants for a challenge
     */
    public function participants(Request $request, Challenge $challenge): Response
    {
        // Check if challenge is public or user is participating
        $user = $request->user();
        if (!$challenge->is_public && (!$user || !$challenge->isUserParticipating($user))) {
            abort(404);
        }
        
        $challenge->load(['creator', 'tasks']);
        
        // Get all participants with pagination
        $participants = $challenge->userChallenges()
            ->with(['user:id,name,username,profile_photo_path,plan,subscription_ends_at'])
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('started_at', 'desc')
            ->paginate(20);
        
        // Get challenge stats
        $stats = $challenge->getStats();
        
        return Inertia::render('Challenges/Participants', [
            'challenge' => $challenge,
            'participants' => $participants,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Show create challenge form
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        
        if (!$user->canCreateChallenge()) {
            return redirect()->route('challenges.index')
                ->with('error', 'Você já tem o máximo de desafios ativos. Upgrade para PRO para desafios ilimitados.');
        }
        
        return Inertia::render('Challenges/Create');
    }
    
    /**
     * Store new challenge
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if (!$user->canCreateChallenge()) {
            return redirect()->route('challenges.index')
                ->with('error', 'Você já tem o máximo de desafios ativos. Upgrade para PRO para desafios ilimitados.');
        }
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'category' => ['required', 'string', 'max:50'],
            'difficulty' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_public' => ['boolean'],
            'tasks' => ['required', 'array', 'min:1', 'max:10'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.hashtag' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:challenge_tasks,hashtag'],
            'tasks.*.description' => ['nullable', 'string', 'max:500'],
            'tasks.*.is_required' => ['boolean'],
            'tasks.*.icon' => ['nullable', 'string', 'max:10'],
            'tasks.*.color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        
        // Create challenge
        $challenge = Challenge::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration_days' => $validated['duration_days'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'is_public' => $validated['is_public'] ?? true,
            'participant_count' => 1,
            'created_by' => $user->id,
        ]);
        
        // Create tasks
        foreach ($validated['tasks'] as $index => $taskData) {
            $challenge->tasks()->create([
                'name' => $taskData['name'],
                'hashtag' => strtolower($taskData['hashtag']),
                'description' => $taskData['description'] ?? null,
                'is_required' => $taskData['is_required'] ?? true,
                'icon' => $taskData['icon'] ?? '✅',
                'color' => $taskData['color'] ?? '#3B82F6',
                'order' => $index + 1,
            ]);
        }
        
        // Auto-join the creator to their own challenge
        $userChallenge = UserChallenge::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
        
        return redirect()->route('challenges.show', $challenge)
            ->with('success', 'Desafio criado com sucesso! Você já está participando.');
    }
    
    /**
     * Join a challenge
     */
    public function join(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();
        
        // Check if user can join
        if (!$user->canCreateChallenge()) {
            return redirect()->back()
                ->with('error', 'Você já tem o máximo de desafios ativos. Upgrade para PRO para desafios ilimitados.');
        }
        
        // Check if user is already participating with active status
        $existingParticipation = $user->userChallenges()
            ->where('challenge_id', $challenge->id)
            ->where('status', 'active')
            ->first();
        
        if ($existingParticipation) {
            return redirect()->back()
                ->with('error', 'Você já está participando deste desafio.');
        }
        
        // Check if user has any participation (regardless of status)
        $anyParticipation = $user->userChallenges()
            ->where('challenge_id', $challenge->id)
            ->first();
        
        if ($anyParticipation) {
            // Update existing participation to active
            $anyParticipation->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
        } else {
            // Create new participation
            UserChallenge::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }
        
        // Update challenge participant count
        $challenge->updateParticipantCount();
        
        return redirect()->route('dopa.dashboard')
            ->with('success', "Você entrou no desafio '{$challenge->title}'! Boa sorte! 🎯");
    }
    
    /**
     * Leave a challenge
     */
    public function leave(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();
        
        $userChallenge = $user->userChallenges()
            ->where('challenge_id', $challenge->id)
            ->where('status', 'active')
            ->first();
        
        if (!$userChallenge) {
            return redirect()->back()
                ->with('error', 'Você não está participando deste desafio.');
        }
        
        $userChallenge->abandon();
        $challenge->updateParticipantCount();
        
        return redirect()->back()
            ->with('success', 'Você saiu do desafio. Você pode retornar quando quiser!');
    }

    /**
     * Desafios recomendados para o usuário (API)
     */
    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $recommended = Cache::remember("recommended_challenges_user_{$user->id}", 1800, function () use ($user) {
            return Challenge::public()
                ->where('is_featured', true)
                ->withCount('userChallenges')
                ->orderBy('participant_count', 'desc')
                ->take(6)
                ->get()
                ->map(function ($challenge) use ($user) {
                    return [
                        'id' => $challenge->id,
                        'title' => $challenge->title,
                        'description' => $challenge->description,
                        'duration_days' => $challenge->duration_days,
                        'category' => $challenge->category,
                        'difficulty' => $challenge->difficulty,
                        'participant_count' => $challenge->participant_count,
                        'image_url' => $challenge->image_url,
                        'tasks_count' => $challenge->tasks()->count(),
                        'is_participating' => $challenge->isUserParticipating($user)
                    ];
                });
        });

        return response()->json([
            'challenges' => $recommended,
            'user_can_join' => $user->canCreateChallenge()
        ]);
    }

    /**
     * Estatísticas de um desafio específico (API)
     */
    public function stats(Request $request, Challenge $challenge): JsonResponse
    {
        // Verificar se é público ou se o usuário está participando
        if (!$challenge->is_public && !$challenge->isUserParticipating($request->user())) {
            return response()->json(['message' => 'Desafio não encontrado'], 404);
        }
        
        $stats = Cache::remember("challenge_stats_{$challenge->id}", 900, function () use ($challenge) {
            $participants = $challenge->userChallenges();
            $totalParticipants = $participants->count();
            $activeParticipants = $participants->where('status', 'active')->count();
            $completedParticipants = $participants->where('status', 'completed')->count();
            $totalCheckins = Checkin::whereIn('user_challenge_id', $participants->pluck('id'))->count();
            
            // Taxa de conclusão
            $completionRate = $totalParticipants > 0 ? round(($completedParticipants / $totalParticipants) * 100, 1) : 0;
            
            // Média de check-ins por participante
            $avgCheckinsPerUser = $totalParticipants > 0 ? round($totalCheckins / $totalParticipants, 1) : 0;
            
            // Últimos participantes (público)
            $recentParticipants = $participants
                ->with('user:id,name,avatar')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($userChallenge) {
                    return [
                        'user_name' => $userChallenge->user->name,
                        'user_avatar' => $userChallenge->user->avatar,
                        'started_at' => $userChallenge->started_at,
                        'status' => $userChallenge->status,
                        'completion_rate' => $userChallenge->completion_rate
                    ];
                });
            
            return [
                'total_participants' => $totalParticipants,
                'active_participants' => $activeParticipants,
                'completed_participants' => $completedParticipants,
                'completion_rate' => $completionRate,
                'total_checkins' => $totalCheckins,
                'avg_checkins_per_user' => $avgCheckinsPerUser,
                'recent_participants' => $recentParticipants,
                'created_at' => $challenge->created_at,
                'is_trending' => $activeParticipants > 10,
                'difficulty_level' => $challenge->difficulty,
                'category' => $challenge->category
            ];
        });

        return response()->json(['stats' => $stats]);
    }
}
