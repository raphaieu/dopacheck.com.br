<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
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
        $query = Challenge::public()->with(['creator', 'tasks']);
        
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
        $sort = $request->sort ?? 'popular';
        match ($sort) {
            'popular' => $query->popular(),
            'newest' => $query->latest(),
            'featured' => $query->featured(),
            default => $query->popular(),
        };
        
        $challenges = $query->paginate(12);
        
        // Get featured challenges for hero section
        $featuredChallenges = Challenge::featured()
            ->with(['creator', 'tasks'])
            ->limit(3)
            ->get();
        
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
            ],
        ]);
    }
    
    /**
     * Show challenge details
     */
    public function show(Request $request, Challenge $challenge): Response
    {
        $challenge->load(['creator', 'tasks', 'activeParticipants.user']);
        
        $user = $request->user(); // Mudança aqui
        $userChallenge = null;
        $canJoin = true;
        
        if ($user) {
            $userChallenge = $user->userChallenges()
                ->where('challenge_id', $challenge->id)
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
        
        // Check if user is already participating
        $existingParticipation = $user->userChallenges()
            ->where('challenge_id', $challenge->id)
            ->first();
        
        if ($existingParticipation) {
            return redirect()->back()
                ->with('error', 'Você já está participando deste desafio.');
        }
        
        // Create participation
        $userChallenge = UserChallenge::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
        
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
}
