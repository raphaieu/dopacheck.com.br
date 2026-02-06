<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Challenge;
use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\UserChallenge;
use App\Helpers\CacheHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ChallengeController extends Controller
{
    private function ensureChallengeEditableByUser(?\App\Models\User $user, Challenge $challenge): void
    {
        abort_unless($user && $challenge->created_by === $user->id, 404);
        abort_if((bool) ($challenge->is_template ?? false), 404);
        // Apenas desafios privados podem ser editados; públicos e de grupo não.
        if ($challenge->visibility !== Challenge::VISIBILITY_PRIVATE) {
            abort(403, 'Desafios públicos ou de grupo não podem ser editados.');
        }
    }

    private function userVisibleTeamIds(?\App\Models\User $user): array
    {
        if (! $user) {
            return [];
        }

        // Jetstream: inclui times em que o usuário é membro (inclui personal team também).
        return $user->allTeams()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function ensureChallengeVisibleToUser(?\App\Models\User $user, Challenge $challenge): void
    {
        if ($challenge->visibility === Challenge::VISIBILITY_GLOBAL) {
            return;
        }

        if ($challenge->visibility === Challenge::VISIBILITY_PRIVATE) {
            abort_unless($user && $challenge->created_by === $user->id, 404);
            return;
        }

        if ($challenge->visibility === Challenge::VISIBILITY_TEAM) {
            abort_unless($user && $challenge->team_id, 404);
            $team = Team::query()->find($challenge->team_id);
            abort_unless($team && $user->belongsToTeam($team), 404);
            return;
        }

        abort(404);
    }

    /**
     * Display challenges listing
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $showPrivate = $request->boolean('show_private', true);

        $teamIds = $user ? $this->userVisibleTeamIds($user) : [];

        // Base query - visibilidade do usuário (agrupada para não quebrar filtros)
        $query = Challenge::query()
            ->with(['creator', 'tasks', 'team'])
            ->where(function ($q) use ($user, $teamIds, $showPrivate) {
                $q->where('visibility', Challenge::VISIBILITY_GLOBAL);

                if ($user && ! empty($teamIds)) {
                    $q->orWhere(function ($subQ) use ($teamIds) {
                        $subQ->where('visibility', Challenge::VISIBILITY_TEAM)
                            ->whereIn('team_id', $teamIds);
                    });
                }

                if ($showPrivate && $user) {
                    $q->orWhere(function ($subQ) use ($user) {
                        $subQ->where('visibility', Challenge::VISIBILITY_PRIVATE)
                            ->where('created_by', $user->id);
                    });
                }
            });
        
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
        
        // Ordenação primária: desafios ativos primeiro, depois futuros, depois encerrados.
        // (Mantém expirados visíveis como histórico, mas mais abaixo na lista.)
        $today = now()->toDateString();
        $query->orderByRaw(
            "CASE
                WHEN start_date <= ? AND end_date >= ? THEN 0
                WHEN start_date > ? THEN 1
                ELSE 2
            END",
            [$today, $today, $today]
        );

        // Sort
        $sort = $request->sort ?? 'newest';
        match ($sort) {
            'popular' => $query->popular(),
            'newest' => $query->latest(),
            'featured' => $query->featured(),
            default => $query->popular(),
        };
        
        $challenges = $query->paginate(6);
        
        // Get user participation info and completion rate for each challenge
        $user = $request->user();
        if ($user) {
            $userChallengeIds = $user->userChallenges()
                ->whereIn('challenge_id', $challenges->pluck('id'))
                ->where('status', 'active')
                ->pluck('challenge_id')
                ->toArray();
            
            // Add user participation info and completion rate to each challenge
            $challenges->getCollection()->transform(function ($challenge) use ($userChallengeIds) {
                $today = Carbon::now()->startOfDay();
                $startDate = $challenge->start_date
                    ? Carbon::parse($challenge->start_date)->startOfDay()
                    : Carbon::parse($challenge->created_at)->startOfDay();
                $endDate = $challenge->end_date
                    ? Carbon::parse($challenge->end_date)->startOfDay()
                    : $startDate->copy()->addDays(((int) $challenge->duration_days) - 1)->startOfDay();

                $challenge->setAttribute('start_date', $startDate->toDateString());
                $challenge->setAttribute('end_date', $endDate->toDateString());
                $challenge->setAttribute('is_expired', $endDate->lt($today));
                $challenge->setAttribute('is_active', $startDate->lte($today) && $endDate->gte($today));
                $challenge->setAttribute('is_future', $startDate->gt($today));

                // Calcular participantes reais (active + completed) ANTES de adicionar atributos dinâmicos
                $totalParticipants = $challenge->userChallenges()
                    ->whereIn('status', ['active', 'completed', 'expired'])
                    ->count();
                
                // Atualizar participant_count se estiver desatualizado (usando query direta para evitar salvar atributos dinâmicos)
                if (abs($challenge->participant_count - $totalParticipants) > 0) {
                    // Usar query direta para atualizar apenas a coluna específica
                    DB::table('challenges')
                        ->where('id', $challenge->id)
                        ->update(['participant_count' => $totalParticipants]);
                    
                    // Atualizar o atributo no modelo para refletir a mudança
                    $challenge->participant_count = $totalParticipants;
                }
                
                // Adicionar atributos dinâmicos DEPOIS da atualização do banco
                $challenge->user_is_participating = in_array($challenge->id, $userChallengeIds);
                
                // Calculate completion rate based on all participants (not filtered)
                $completedParticipants = $challenge->completedParticipants()->count();
                $completionRate = $totalParticipants > 0 
                    ? round(($completedParticipants / $totalParticipants) * 100, 0) 
                    : 0;
                
                // Adicionar como atributo dinâmico para o frontend
                $challenge->setAttribute('completion_rate', $completionRate);
                return $challenge;
            });
        } else {
            // For non-authenticated users, still calculate completion rate
            $challenges->getCollection()->transform(function ($challenge) {
                $today = Carbon::now()->startOfDay();
                $startDate = $challenge->start_date
                    ? Carbon::parse($challenge->start_date)->startOfDay()
                    : Carbon::parse($challenge->created_at)->startOfDay();
                $endDate = $challenge->end_date
                    ? Carbon::parse($challenge->end_date)->startOfDay()
                    : $startDate->copy()->addDays(((int) $challenge->duration_days) - 1)->startOfDay();

                $challenge->setAttribute('start_date', $startDate->toDateString());
                $challenge->setAttribute('end_date', $endDate->toDateString());
                $challenge->setAttribute('is_expired', $endDate->lt($today));
                $challenge->setAttribute('is_active', $startDate->lte($today) && $endDate->gte($today));
                $challenge->setAttribute('is_future', $startDate->gt($today));

                // Calcular participantes reais (active + completed) ANTES de adicionar atributos dinâmicos
                $totalParticipants = $challenge->userChallenges()
                    ->whereIn('status', ['active', 'completed', 'expired'])
                    ->count();
                
                // Atualizar participant_count se estiver desatualizado (usando query direta para evitar salvar atributos dinâmicos)
                if (abs($challenge->participant_count - $totalParticipants) > 0) {
                    // Usar query direta para atualizar apenas a coluna específica
                    DB::table('challenges')
                        ->where('id', $challenge->id)
                        ->update(['participant_count' => $totalParticipants]);
                    
                    // Atualizar o atributo no modelo para refletir a mudança
                    $challenge->participant_count = $totalParticipants;
                }
                
                // Calculate completion rate based on all participants (not filtered)
                $completedParticipants = $challenge->completedParticipants()->count();
                $completionRate = $totalParticipants > 0 
                    ? round(($completedParticipants / $totalParticipants) * 100, 0) 
                    : 0;
                
                // Adicionar como atributo dinâmico para o frontend
                $challenge->setAttribute('completion_rate', $completionRate);
                return $challenge;
            });
        }
        
        // Get featured challenges for hero section
        $featuredChallenges = Challenge::query()
            ->where('visibility', Challenge::VISIBILITY_GLOBAL)
            ->where('is_featured', true)
            ->with(['creator', 'tasks', 'team'])
            ->limit(3)
            ->get();
        
        // Add user participation info and completion rate to featured challenges
        if ($user) {
            $featuredUserChallengeIds = $user->userChallenges()
                ->whereIn('challenge_id', $featuredChallenges->pluck('id'))
                ->where('status', 'active')
                ->pluck('challenge_id')
                ->toArray();
            
            $featuredChallenges->transform(function ($challenge) use ($featuredUserChallengeIds) {
                $today = Carbon::now()->startOfDay();
                $startDate = $challenge->start_date
                    ? Carbon::parse($challenge->start_date)->startOfDay()
                    : Carbon::parse($challenge->created_at)->startOfDay();
                $endDate = $challenge->end_date
                    ? Carbon::parse($challenge->end_date)->startOfDay()
                    : $startDate->copy()->addDays(((int) $challenge->duration_days) - 1)->startOfDay();

                $challenge->setAttribute('start_date', $startDate->toDateString());
                $challenge->setAttribute('end_date', $endDate->toDateString());
                $challenge->setAttribute('is_expired', $endDate->lt($today));
                $challenge->setAttribute('is_active', $startDate->lte($today) && $endDate->gte($today));
                $challenge->setAttribute('is_future', $startDate->gt($today));

                // Calcular participantes reais (active + completed) ANTES de adicionar atributos dinâmicos
                $totalParticipants = $challenge->userChallenges()
                    ->whereIn('status', ['active', 'completed', 'expired'])
                    ->count();
                
                // Atualizar participant_count se estiver desatualizado (usando query direta para evitar salvar atributos dinâmicos)
                if (abs($challenge->participant_count - $totalParticipants) > 0) {
                    // Usar query direta para atualizar apenas a coluna específica
                    DB::table('challenges')
                        ->where('id', $challenge->id)
                        ->update(['participant_count' => $totalParticipants]);
                    
                    // Atualizar o atributo no modelo para refletir a mudança
                    $challenge->participant_count = $totalParticipants;
                }
                
                // Adicionar atributos dinâmicos DEPOIS da atualização do banco
                $challenge->user_is_participating = in_array($challenge->id, $featuredUserChallengeIds);
                
                // Calculate completion rate based on all participants (not filtered)
                $completedParticipants = $challenge->completedParticipants()->count();
                $completionRate = $totalParticipants > 0 
                    ? round(($completedParticipants / $totalParticipants) * 100, 0) 
                    : 0;
                
                // Adicionar como atributo dinâmico para o frontend
                $challenge->setAttribute('completion_rate', $completionRate);
                
                // Calculate trending score based on recent activity
                $recentParticipants = $challenge->userChallenges()
                    ->where('started_at', '>=', now()->subDays(7))
                    ->count();
                $challenge->trending_score = $recentParticipants > 10 ? '🔥' 
                    : ($recentParticipants > 5 ? '🚀' 
                    : ($recentParticipants > 2 ? '⭐' 
                    : ($challenge->participant_count > 100 ? '💎' : '🌟')));
                return $challenge;
            });
        } else {
            // For non-authenticated users, still calculate completion rate and trending
            $featuredChallenges->transform(function ($challenge) {
                $today = Carbon::now()->startOfDay();
                $startDate = $challenge->start_date
                    ? Carbon::parse($challenge->start_date)->startOfDay()
                    : Carbon::parse($challenge->created_at)->startOfDay();
                $endDate = $challenge->end_date
                    ? Carbon::parse($challenge->end_date)->startOfDay()
                    : $startDate->copy()->addDays(((int) $challenge->duration_days) - 1)->startOfDay();

                $challenge->setAttribute('start_date', $startDate->toDateString());
                $challenge->setAttribute('end_date', $endDate->toDateString());
                $challenge->setAttribute('is_expired', $endDate->lt($today));
                $challenge->setAttribute('is_active', $startDate->lte($today) && $endDate->gte($today));
                $challenge->setAttribute('is_future', $startDate->gt($today));

                // Calcular participantes reais (active + completed) ANTES de adicionar atributos dinâmicos
                $totalParticipants = $challenge->userChallenges()
                    ->whereIn('status', ['active', 'completed', 'expired'])
                    ->count();
                
                // Atualizar participant_count se estiver desatualizado (usando query direta para evitar salvar atributos dinâmicos)
                if (abs($challenge->participant_count - $totalParticipants) > 0) {
                    // Usar query direta para atualizar apenas a coluna específica
                    DB::table('challenges')
                        ->where('id', $challenge->id)
                        ->update(['participant_count' => $totalParticipants]);
                    
                    // Atualizar o atributo no modelo para refletir a mudança
                    $challenge->participant_count = $totalParticipants;
                }
                
                $completedParticipants = $challenge->completedParticipants()->count();
                $completionRate = $totalParticipants > 0 
                    ? round(($completedParticipants / $totalParticipants) * 100, 0) 
                    : 0;
                
                // Adicionar como atributo dinâmico para o frontend
                $challenge->setAttribute('completion_rate', $completionRate);
                $recentParticipants = $challenge->userChallenges()
                    ->where('started_at', '>=', now()->subDays(7))
                    ->count();
                $challenge->trending_score = $recentParticipants > 10 ? '🔥' 
                    : ($recentParticipants > 5 ? '🚀' 
                    : ($recentParticipants > 2 ? '⭐' 
                    : ($challenge->participant_count > 100 ? '💎' : '🌟')));
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
        $this->ensureChallengeVisibleToUser($request->user(), $challenge);

        $challenge->load(['creator', 'tasks', 'team', 'activeParticipants.user']);

        // Status do período global (para UI bloquear join quando encerrado)
        $today = Carbon::now()->startOfDay();
        $startDate = $challenge->start_date
            ? Carbon::parse($challenge->start_date)->startOfDay()
            : Carbon::parse($challenge->created_at)->startOfDay();
        $endDate = $challenge->end_date
            ? Carbon::parse($challenge->end_date)->startOfDay()
            : $startDate->copy()->addDays(((int) $challenge->duration_days) - 1)->startOfDay();

        $challenge->setAttribute('start_date', $startDate->toDateString());
        $challenge->setAttribute('end_date', $endDate->toDateString());
        $challenge->setAttribute('is_expired', $endDate->lt($today));
        $challenge->setAttribute('is_active', $startDate->lte($today) && $endDate->gte($today));
        $challenge->setAttribute('is_future', $startDate->gt($today));
        
        $user = $request->user();
        $userChallenge = null;
        $canJoin = true;
        
        if ($user) {
            $userChallenge = $user->userChallenges()
                ->where('challenge_id', $challenge->id)
                ->where('status', 'active')
                ->first();
            
            $canJoin = !$userChallenge && $user->canCreateChallenge() && !((bool) $challenge->getAttribute('is_expired'));
        }
        
        // Get challenge stats
        $stats = $challenge->getStats();
        
        // Get recent participants
        $recentParticipants = $challenge->activeParticipants()
            ->with(['user', 'challenge.tasks']) // Carregar challenge e tasks para calcular progress_percentage
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($userChallenge) {
                return [
                    'id' => $userChallenge->id,
                    'user' => $userChallenge->user,
                    'status' => $userChallenge->status,
                    'current_day' => $userChallenge->current_day,
                    'started_at' => $userChallenge->started_at,
                    'streak_days' => $userChallenge->streak_days,
                    'completion_rate' => $userChallenge->completion_rate,
                    'progress_percentage' => $userChallenge->progress_percentage, // Progresso baseado em dias completos
                ];
            });
        
        $appUrl = rtrim((string) config('app.url', 'https://dopacheck.com.br'), '/');
        $description = Str::of((string) $challenge->description)->squish()->limit(180)->toString();

        // Para WhatsApp não “falhar” com imagens grandes/diferentes, usamos um OG padronizado (1200x630).
        $ogImageUrl = route('og.challenge', $challenge);

        return Inertia::render('Challenges/Show', [
            'challenge' => $challenge,
            'userChallenge' => $userChallenge,
            'canJoin' => $canJoin,
            'stats' => $stats,
            'recentParticipants' => $recentParticipants,
            'isAuthenticated' => (bool) $user,
        ])->withViewData([
            // OG tags precisam sair no HTML inicial para WhatsApp/Telegram/Discord.
            'seo' => [
                'type' => 'website',
                'title' => $challenge->title.' | DOPA Check',
                'description' => $description,
                'image' => $ogImageUrl,
                'image_type' => 'image/jpeg',
                'image_width' => '1200',
                'image_height' => '630',
                'image_alt' => 'Imagem do desafio: '.$challenge->title,
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $challenge->title,
                    'description' => $description,
                    'url' => url()->current(),
                    'image' => $ogImageUrl,
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => 'DOPA Check',
                        'url' => $appUrl.'/',
                    ],
                ],
            ],
        ]);
    }
    
    /**
     * Show all participants for a challenge
     */
    public function participants(Request $request, Challenge $challenge): Response
    {
        $this->ensureChallengeVisibleToUser($request->user(), $challenge);
        
        $challenge->load(['creator', 'tasks']);
        
        // Get all participants with pagination
        // Ordenar por progresso (dias completos) - maior primeiro
        $participants = $challenge->userChallenges()
            ->with([
                'user:id,name,username,profile_photo_path,plan,subscription_ends_at',
                'challenge.tasks' // Carregar challenge e tasks para calcular progress_percentage
            ])
            ->whereIn('status', ['active', 'completed', 'expired'])
            ->get()
            ->map(function ($userChallenge) {
                return [
                    'id' => $userChallenge->id,
                    'user' => $userChallenge->user,
                    'status' => $userChallenge->status,
                    'current_day' => $userChallenge->current_day,
                    'started_at' => $userChallenge->started_at,
                    'streak_days' => $userChallenge->streak_days,
                    'completion_rate' => $userChallenge->completion_rate,
                    'progress_percentage' => $userChallenge->progress_percentage, // Progresso baseado em dias completos
                ];
            })
            ->sortByDesc('progress_percentage')
            ->values();
        
        // Paginar manualmente
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $items = $participants->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $participants->count();
        
        $participants = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
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
        
        return Inertia::render('Challenges/Create', [
            'teams' => $user->allTeams()->map(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'personal_team' => (bool) ($team->personal_team ?? false),
            ])->values(),
        ]);
    }

    /**
     * Show edit challenge form (reusa a tela de criação)
     */
    public function edit(Request $request, Challenge $challenge): Response
    {
        $user = $request->user();
        $this->ensureChallengeEditableByUser($user, $challenge);

        $challenge->load(['tasks']);

        return Inertia::render('Challenges/Create', [
            'teams' => $user->allTeams()->map(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'personal_team' => (bool) ($team->personal_team ?? false),
            ])->values(),
            'challenge' => [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'description' => $challenge->description,
                'duration_days' => $challenge->duration_days,
                'start_date' => ($challenge->start_date
                    ? Carbon::parse($challenge->start_date)->toDateString()
                    : Carbon::parse($challenge->created_at)->toDateString()
                ),
                'end_date' => ($challenge->end_date
                    ? Carbon::parse($challenge->end_date)->toDateString()
                    : Carbon::parse($challenge->start_date ?? $challenge->created_at)->startOfDay()->addDays(((int) $challenge->duration_days) - 1)->toDateString()
                ),
                'category' => $challenge->category,
                'difficulty' => $challenge->difficulty,
                'visibility' => $challenge->visibility,
                'team_id' => $challenge->team_id,
                'tasks' => $challenge->tasks->map(fn (ChallengeTask $task) => [
                    'id' => $task->id,
                    'name' => $task->name,
                    'hashtag' => $task->hashtag,
                    'description' => $task->description,
                    'is_required' => (bool) $task->is_required,
                    'icon' => $task->icon,
                    'color' => $task->color,
                    'order' => $task->order,
                ])->values(),
            ],
        ]);
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
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'category' => ['required', 'string', 'max:50'],
            'difficulty' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'visibility' => ['required', 'string', Rule::in([
                Challenge::VISIBILITY_PRIVATE,
                Challenge::VISIBILITY_TEAM,
                Challenge::VISIBILITY_GLOBAL,
            ])],
            'team_id' => ['nullable', 'integer'],
            'tasks' => ['required', 'array', 'min:1', 'max:10'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.hashtag' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
            'tasks.*.description' => ['nullable', 'string', 'max:500'],
            'tasks.*.is_required' => ['boolean'],
            'tasks.*.icon' => ['nullable', 'string', 'max:10'],
            'tasks.*.color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // Normaliza período global do desafio (se não vier, mantém comportamento atual: inicia hoje)
        $startDate = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfDay();

        $endDate = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : $startDate->copy()->addDays(((int) $validated['duration_days']) - 1);

        if ($endDate->lt($startDate)) {
            return redirect()->back()
                ->withErrors(['end_date' => 'A data fim deve ser maior ou igual à data de início.'])
                ->withInput();
        }

        $durationDays = (int) ($startDate->diffInDays($endDate) + 1);
        if ($durationDays < 1 || $durationDays > 365) {
            return redirect()->back()
                ->withErrors(['duration_days' => 'Duração deve ser entre 1 e 365 dias (com base nas datas).'])
                ->withInput();
        }

        // Usa as datas como fonte da verdade para evitar inconsistência
        $validated['duration_days'] = $durationDays;
        $validated['start_date'] = $startDate->toDateString();
        $validated['end_date'] = $endDate->toDateString();

        // Regras de visibilidade:
        // - private: team_id deve ser null
        // - global: team_id deve ser null
        // - team: team_id obrigatório e o usuário precisa pertencer ao time
        if (in_array($validated['visibility'], [Challenge::VISIBILITY_PRIVATE, Challenge::VISIBILITY_GLOBAL], true)) {
            $validated['team_id'] = null;
        }

        if ($validated['visibility'] === Challenge::VISIBILITY_TEAM) {
            $teamId = (int) ($validated['team_id'] ?? 0);
            if ($teamId <= 0) {
                return redirect()->back()
                    ->withErrors(['team_id' => 'Selecione um time para compartilhar este desafio.'])
                    ->withInput();
            }

            $team = Team::query()->find($teamId);
            if (! $team || ! $user->belongsToTeam($team)) {
                return redirect()->back()
                    ->withErrors(['team_id' => 'Você não tem acesso a este time.'])
                    ->withInput();
            }

            $validated['team_id'] = $teamId;
        }
        
        // Validação customizada: verificar hashtags duplicadas dentro do array
        $hashtags = array_map(function ($task) {
            return strtolower($task['hashtag']);
        }, $validated['tasks']);
        
        $duplicates = array_diff_assoc($hashtags, array_unique($hashtags));
        if (!empty($duplicates)) {
            return redirect()->back()
                ->withErrors(['tasks' => 'Hashtags duplicadas não são permitidas: ' . implode(', ', array_unique($duplicates))])
                ->withInput();
        }

        // Unicidade por escopo:
        // - global => scope_team_id = 0
        // - team => scope_team_id = team_id
        // - private => scope_team_id = 1e12 + challenge_id (definido após criar o challenge)
        $scopeTeamId = 0;
        if (($validated['visibility'] ?? null) === Challenge::VISIBILITY_TEAM) {
            $scopeTeamId = (int) ($validated['team_id'] ?? 0);
        }

        if (($validated['visibility'] ?? null) !== Challenge::VISIBILITY_PRIVATE) {
            $existing = DB::table('challenge_tasks')
                ->where('scope_team_id', $scopeTeamId)
                ->whereIn('hashtag', $hashtags)
                ->pluck('hashtag')
                ->map(fn ($h) => "#{$h}")
                ->unique()
                ->values()
                ->all();

            if (! empty($existing)) {
                return redirect()->back()
                    ->withErrors([
                        'tasks' => 'Algumas hashtags já existem neste escopo (global/time): ' . implode(', ', $existing),
                    ])
                    ->withInput();
            }
        }
        
        // Create challenge
        $challenge = Challenge::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration_days' => $validated['duration_days'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            // Legado (compat):
            'is_public' => $validated['visibility'] !== Challenge::VISIBILITY_PRIVATE,
            'visibility' => $validated['visibility'],
            'team_id' => $validated['team_id'] ?? null,
            'participant_count' => 1,
            'created_by' => $user->id,
        ]);

        $taskScopeTeamId = match ($challenge->visibility) {
            Challenge::VISIBILITY_TEAM => (int) ($challenge->team_id ?? 0),
            Challenge::VISIBILITY_PRIVATE => Challenge::PRIVATE_HASHTAG_SCOPE_OFFSET + (int) $challenge->id,
            default => 0,
        };
        
        // Create tasks
        foreach ($validated['tasks'] as $index => $taskData) {
            $challenge->tasks()->create([
                'name' => $taskData['name'],
                'scope_team_id' => $taskScopeTeamId,
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
            'team_id' => $challenge->team_id,
            'status' => 'active',
            'started_at' => now(),
        ]);
        
        // Invalidar cache relacionado
        CacheHelper::invalidateUserCache($user->id);
        CacheHelper::invalidateChallengeCache($challenge->id);
        
        return redirect()->route('challenges.show', $challenge)
            ->with('success', 'Desafio criado com sucesso! Você já está participando.');
    }

    /**
     * Update existing challenge
     */
    public function update(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();
        $this->ensureChallengeEditableByUser($user, $challenge);

        $challenge->load(['tasks']);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'category' => ['required', 'string', 'max:50'],
            'difficulty' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'visibility' => ['required', 'string', Rule::in([
                Challenge::VISIBILITY_PRIVATE,
                Challenge::VISIBILITY_TEAM,
                Challenge::VISIBILITY_GLOBAL,
            ])],
            'team_id' => ['nullable', 'integer'],
            'tasks' => ['required', 'array', 'min:1', 'max:10'],
            'tasks.*.id' => ['nullable', 'integer'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.hashtag' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
            'tasks.*.description' => ['nullable', 'string', 'max:500'],
            'tasks.*.is_required' => ['boolean'],
            'tasks.*.icon' => ['nullable', 'string', 'max:10'],
            'tasks.*.color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'confirm_reset_progress' => ['sometimes', 'boolean'],
        ]);

        // Normaliza período global do desafio
        $startDate = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : ($challenge->start_date ? Carbon::parse($challenge->start_date)->startOfDay() : now()->startOfDay());

        $endDate = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : ($challenge->end_date
                ? Carbon::parse($challenge->end_date)->startOfDay()
                : $startDate->copy()->addDays(((int) $validated['duration_days']) - 1)
            );

        if ($endDate->lt($startDate)) {
            return redirect()->back()
                ->withErrors(['end_date' => 'A data fim deve ser maior ou igual à data de início.'])
                ->withInput();
        }

        $durationDays = (int) ($startDate->diffInDays($endDate) + 1);
        if ($durationDays < 1 || $durationDays > 365) {
            return redirect()->back()
                ->withErrors(['duration_days' => 'Duração deve ser entre 1 e 365 dias (com base nas datas).'])
                ->withInput();
        }

        $validated['duration_days'] = $durationDays;
        $validated['start_date'] = $startDate->toDateString();
        $validated['end_date'] = $endDate->toDateString();

        // Regras de visibilidade:
        // - private/global: team_id null
        // - team: team_id obrigatório e usuário precisa pertencer ao time
        if (in_array($validated['visibility'], [Challenge::VISIBILITY_PRIVATE, Challenge::VISIBILITY_GLOBAL], true)) {
            $validated['team_id'] = null;
        }

        if ($validated['visibility'] === Challenge::VISIBILITY_TEAM) {
            $teamId = (int) ($validated['team_id'] ?? 0);
            if ($teamId <= 0) {
                return redirect()->back()
                    ->withErrors(['team_id' => 'Selecione um time para compartilhar este desafio.'])
                    ->withInput();
            }

            $team = Team::query()->find($teamId);
            if (! $team || ! $user->belongsToTeam($team)) {
                return redirect()->back()
                    ->withErrors(['team_id' => 'Você não tem acesso a este time.'])
                    ->withInput();
            }

            $validated['team_id'] = $teamId;
        }

        // Validação customizada: hashtags duplicadas dentro do array
        $hashtags = array_map(function ($task) {
            return strtolower((string) ($task['hashtag'] ?? ''));
        }, $validated['tasks']);

        $duplicates = array_diff_assoc($hashtags, array_unique($hashtags));
        if (! empty($duplicates)) {
            return redirect()->back()
                ->withErrors(['tasks' => 'Hashtags duplicadas não são permitidas: ' . implode(', ', array_unique($duplicates))])
                ->withInput();
        }

        // Evita hijack: ids enviados precisam pertencer ao desafio
        $existingTaskIds = $challenge->tasks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $incomingTaskIds = array_values(array_filter(array_map(
            fn ($t) => isset($t['id']) ? (int) $t['id'] : null,
            $validated['tasks']
        )));

        foreach ($incomingTaskIds as $incomingId) {
            if (! in_array($incomingId, $existingTaskIds, true)) {
                return redirect()->back()
                    ->withErrors(['tasks' => 'Task inválida para este desafio.'])
                    ->withInput();
            }
        }

        // Regra de remoção: só permite remover task se não existir check-in e não houver outros participantes.
        $removedIds = array_values(array_diff($existingTaskIds, $incomingTaskIds));
        if (! empty($removedIds)) {
            $hasOtherParticipants = UserChallenge::query()
                ->where('challenge_id', $challenge->id)
                ->where('user_id', '!=', $user->id)
                ->exists();
            $hasCheckins = Checkin::query()->whereIn('task_id', $removedIds)->exists();

            if ($hasOtherParticipants || $hasCheckins) {
                return redirect()->back()
                    ->withErrors(['tasks' => 'Este desafio já tem participação/check-ins. Não é possível remover tasks existentes.'])
                    ->withInput();
            }
        }

        // Alterações sensíveis: datas ou novas tasks — exigem confirmação para zerar progresso
        $sensitiveDateChange = ($challenge->start_date?->format('Y-m-d') !== $validated['start_date'])
            || ($challenge->end_date?->format('Y-m-d') !== $validated['end_date'])
            || ((int) $challenge->duration_days !== (int) $validated['duration_days']);
        $newTasksAdded = count($validated['tasks']) > count($existingTaskIds)
            || count(array_filter($validated['tasks'], fn ($t) => empty($t['id'] ?? null))) > 0;
        $sensitiveChanges = $sensitiveDateChange || $newTasksAdded;

        $hasProgress = Checkin::query()
            ->whereHas('userChallenge', fn ($q) => $q->where('challenge_id', $challenge->id))
            ->exists();

        if ($sensitiveChanges && $hasProgress && ! ($validated['confirm_reset_progress'] ?? false)) {
            return redirect()->back()
                ->withErrors([
                    'confirm_reset_progress' => 'Alterações em data ou em tasks zeram todo o progresso (check-ins, sequência). Marque a opção de confirmação e salve novamente.',
                ])
                ->withInput();
        }

        // Calcula o scope_team_id alvo com base na nova visibilidade
        $taskScopeTeamId = match ($validated['visibility']) {
            Challenge::VISIBILITY_TEAM => (int) ($validated['team_id'] ?? 0),
            Challenge::VISIBILITY_PRIVATE => Challenge::PRIVATE_HASHTAG_SCOPE_OFFSET + (int) $challenge->id,
            default => 0,
        };

        // Unicidade por escopo (global/time): checa conflitos fora deste desafio
        if ($validated['visibility'] !== Challenge::VISIBILITY_PRIVATE) {
            $conflicts = DB::table('challenge_tasks')
                ->where('scope_team_id', $taskScopeTeamId)
                ->whereIn('hashtag', $hashtags)
                ->whereNotIn('id', $existingTaskIds)
                ->pluck('hashtag')
                ->map(fn ($h) => "#{$h}")
                ->unique()
                ->values()
                ->all();

            if (! empty($conflicts)) {
                return redirect()->back()
                    ->withErrors([
                        'tasks' => 'Algumas hashtags já existem neste escopo (global/time): ' . implode(', ', $conflicts),
                    ])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($challenge, $validated, $existingTaskIds, $incomingTaskIds, $removedIds, $taskScopeTeamId, $sensitiveChanges, $hasProgress): void {
            // Se houve alterações sensíveis e o usuário confirmou (ou não havia progresso), zera progresso para relatórios limpos
            if ($sensitiveChanges && ($hasProgress && ($validated['confirm_reset_progress'] ?? false))) {
                $userChallengeIds = UserChallenge::query()
                    ->where('challenge_id', $challenge->id)
                    ->pluck('id');
                Checkin::query()->whereIn('user_challenge_id', $userChallengeIds)->forceDelete();
                UserChallenge::query()
                    ->where('challenge_id', $challenge->id)
                    ->update([
                        'current_day' => 1,
                        'total_checkins' => 0,
                        'streak_days' => 0,
                        'best_streak' => 0,
                        'completion_rate' => 0,
                        'stats' => null,
                    ]);
            }

            // Atualiza challenge
            $challenge->forceFill([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration_days' => $validated['duration_days'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'category' => $validated['category'],
                'difficulty' => $validated['difficulty'],
                // Legado (compat):
                'is_public' => $validated['visibility'] !== Challenge::VISIBILITY_PRIVATE,
                'visibility' => $validated['visibility'],
                'team_id' => $validated['team_id'] ?? null,
            ])->save();

            // Remove tasks (se permitido)
            if (! empty($removedIds)) {
                ChallengeTask::query()->whereIn('id', $removedIds)->delete();
            }

            $existingById = ChallengeTask::query()
                ->whereIn('id', $existingTaskIds)
                ->get()
                ->keyBy('id');

            foreach ($validated['tasks'] as $index => $taskData) {
                $taskId = isset($taskData['id']) ? (int) $taskData['id'] : null;

                $payload = [
                    'name' => $taskData['name'],
                    'scope_team_id' => $taskScopeTeamId,
                    'hashtag' => strtolower((string) $taskData['hashtag']),
                    'description' => $taskData['description'] ?? null,
                    'is_required' => $taskData['is_required'] ?? true,
                    'icon' => $taskData['icon'] ?? '✅',
                    'color' => $taskData['color'] ?? '#3B82F6',
                    'order' => $index + 1,
                ];

                if ($taskId && $existingById->has($taskId)) {
                    /** @var ChallengeTask $task */
                    $task = $existingById->get($taskId);
                    $task->forceFill($payload)->save();
                } else {
                    $challenge->tasks()->create($payload);
                }
            }
        });

        CacheHelper::invalidateChallengeCache($challenge->id);

        return redirect()->route('challenges.show', $challenge)
            ->with('success', 'Desafio atualizado com sucesso!');
    }
    
    /**
     * Join a challenge
     */
    public function join(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();

        $this->ensureChallengeVisibleToUser($user, $challenge);

        // Bloqueia entrada em desafios encerrados (mantém visíveis como histórico)
        $endDate = $challenge->end_date
            ? Carbon::parse($challenge->end_date)->startOfDay()
            : null;
        if ($endDate && $endDate->lt(now()->startOfDay())) {
            return redirect()->back()->with('error', 'Este desafio já encerrou e não aceita novas participações.');
        }
        
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
            // Reset challenge progress when re-joining
            $anyParticipation->update([
                'status' => 'active',
                'started_at' => now(),
                'current_day' => 1,
                'total_checkins' => 0,
                'streak_days' => 0,
                'completion_rate' => 0.00,
            ]);
            
            // Delete old check-ins when re-joining a challenge
            // This prevents showing old check-ins from previous participation
            $anyParticipation->checkins()->forceDelete();
        } else {
            // Create new participation
            UserChallenge::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'team_id' => $challenge->team_id,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }
        
        // Update challenge participant count
        $challenge->updateParticipantCount();
        
        // Invalidar cache relacionado
        CacheHelper::invalidateUserCache($user->id);
        CacheHelper::invalidateChallengeCache($challenge->id);
        
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
        
        // Invalidar cache relacionado
        CacheHelper::invalidateUserCache($user->id);
        CacheHelper::invalidateChallengeCache($challenge->id);
        
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
            return Challenge::query()
                ->where('visibility', Challenge::VISIBILITY_GLOBAL)
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
        $user = $request->user();
        // Verificar visibilidade (global/team/private) OU permitir se o usuário participa.
        try {
            $this->ensureChallengeVisibleToUser($user, $challenge);
        } catch (\Throwable) {
            if (! $user || ! $challenge->isUserParticipating($user)) {
                return response()->json(['message' => 'Desafio não encontrado'], 404);
            }
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
