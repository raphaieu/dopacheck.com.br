<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\UserChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckinController extends Controller
{
    /**
     * Lista de check-ins do usuário
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $checkins = $user->checkins()
            ->with(['task', 'userChallenge.challenge'])
            ->orderBy('checked_at', 'desc')
            ->paginate(20);

        return Inertia::render('Checkins/Index', [
            'checkins' => $checkins->through(function ($checkin) {
                return [
                    'id' => $checkin->id,
                    'image_url' => $checkin->image_url,
                    'message' => $checkin->message,
                    'source' => $checkin->source,
                    'status' => $checkin->status,
                    'checked_at' => $checkin->checked_at,
                    'challenge_day' => $checkin->challenge_day,
                    'ai_analysis' => $checkin->ai_analysis,
                    'confidence_score' => $checkin->confidence_score,
                    'task' => [
                        'id' => $checkin->task->id,
                        'name' => $checkin->task->name,
                        'hashtag' => $checkin->task->hashtag,
                        'icon' => $checkin->task->icon,
                        'color' => $checkin->task->color
                    ],
                    'challenge' => [
                        'id' => $checkin->userChallenge->challenge->id,
                        'title' => $checkin->userChallenge->challenge->title
                    ]
                ];
            }),
            'pagination' => [
                'current_page' => $checkins->currentPage(),
                'last_page' => $checkins->lastPage(),
                'per_page' => $checkins->perPage(),
                'total' => $checkins->total()
            ]
        ]);
    }

    /**
     * Criar novo check-in (Web form)
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Validação
        $validator = Validator::make($request->all(), [
            'task_id' => ['required', 'exists:challenge_tasks,id'],
            'user_challenge_id' => ['required', 'exists:user_challenges,id'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB
            'message' => ['nullable', 'string', 'max:500'],
            'source' => ['required', Rule::in(['web', 'whatsapp'])],
            'use_ai_analysis' => ['nullable', 'boolean']
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Verificar se o user challenge pertence ao usuário
        $userChallenge = UserChallenge::where('id', $request->user_challenge_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$userChallenge) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Desafio não encontrado ou inativo'], 404);
            }
            return back()->withErrors(['message' => 'Desafio não encontrado ou inativo']);
        }

        // Verificar se a task pertence ao desafio
        $task = ChallengeTask::where('id', $request->task_id)
            ->where('challenge_id', $userChallenge->challenge_id)
            ->first();

        if (!$task) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Task não encontrada'], 404);
            }
            return back()->withErrors(['message' => 'Task não encontrada']);
        }

        // Calcular dia atual do desafio
        $currentDay = $this->calculateCurrentDay($userChallenge);

        // Verificar se já fez check-in hoje para esta task
        $existingCheckin = Checkin::where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->where('challenge_day', $currentDay)
            ->whereNull('deleted_at')
            ->first();

        if ($existingCheckin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Check-in já realizado hoje para esta task'], 409);
            }
            return back()->withErrors(['message' => 'Check-in já realizado hoje para esta task']);
        }

        try {
            // Upload da imagem (se fornecida)
            $imagePath = null;
            $imageUrl = null;
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = 'checkin_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                
                // Salvar localmente por enquanto (futuro: Cloudflare R2)
                $imagePath = $image->storeAs('checkins', $filename, 'public');
                $imageUrl = Storage::url($imagePath);
            }

            // Criar check-in
            $checkin = Checkin::create([
                'user_challenge_id' => $userChallenge->id,
                'task_id' => $task->id,
                'image_path' => $imagePath,
                'image_url' => $imageUrl,
                'message' => $request->message,
                'source' => $request->source,
                'status' => 'approved', // Auto-approve por enquanto
                'challenge_day' => $currentDay,
                'checked_at' => now()
            ]);

            // Processar IA se for PRO e solicitado
            if ($user->is_pro && $request->use_ai_analysis && $imageUrl) {
                // TODO: Queue job para análise IA
                // ProcessCheckinWithAI::dispatch($checkin);
            }

            // Atualizar stats do user challenge
            $userChallenge->updateStats();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Check-in realizado com sucesso!',
                    'checkin' => [
                        'id' => $checkin->id,
                        'image_url' => $checkin->image_url,
                        'message' => $checkin->message,
                        'source' => $checkin->source,
                        'checked_at' => $checkin->checked_at,
                        'ai_analysis' => $checkin->ai_analysis,
                        'confidence_score' => $checkin->confidence_score
                    ]
                ], 201);
            }

            return redirect()->back()->with('success', 'Check-in realizado com sucesso!');

        } catch (\Exception $e) {
            // Cleanup da imagem se houver erro
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erro interno do servidor'], 500);
            }
            return back()->withErrors(['message' => 'Erro ao realizar check-in. Tente novamente.']);
        }
    }

    /**
     * Quick check-in sem imagem (AJAX)
     */
    public function quickCheckin(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validação
        $validator = Validator::make($request->all(), [
            'task_id' => ['required', 'exists:challenge_tasks,id'],
            'user_challenge_id' => ['required', 'exists:user_challenges,id'],
            'source' => ['required', Rule::in(['web', 'whatsapp'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar se o user challenge pertence ao usuário
        $userChallenge = UserChallenge::where('id', $request->user_challenge_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$userChallenge) {
            return response()->json(['message' => 'Desafio não encontrado ou inativo'], 404);
        }

        // Verificar se a task pertence ao desafio
        $task = ChallengeTask::where('id', $request->task_id)
            ->where('challenge_id', $userChallenge->challenge_id)
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task não encontrada'], 404);
        }

        // Calcular dia atual do desafio
        $currentDay = $this->calculateCurrentDay($userChallenge);

        // Verificar se já fez check-in hoje para esta task
        $existingCheckin = Checkin::where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->where('challenge_day', $currentDay)
            ->whereNull('deleted_at')
            ->first();

        if ($existingCheckin) {
            return response()->json(['message' => 'Check-in já realizado hoje para esta task'], 409);
        }

        try {
            // Criar check-in rápido
            $checkin = Checkin::create([
                'user_challenge_id' => $userChallenge->id,
                'task_id' => $task->id,
                'source' => $request->source,
                'status' => 'approved',
                'challenge_day' => $currentDay,
                'checked_at' => now()
            ]);

            // Atualizar stats do user challenge
            $userChallenge->updateStats();

            return response()->json([
                'message' => 'Check-in realizado com sucesso!',
                'checkin' => [
                    'id' => $checkin->id,
                    'image_url' => null,
                    'message' => null,
                    'source' => $checkin->source,
                    'checked_at' => $checkin->checked_at,
                    'ai_analysis' => null,
                    'confidence_score' => null
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Remover check-in
     */
    public function destroy(Request $request, Checkin $checkin): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Verificar se o check-in pertence ao usuário
        if ($checkin->userChallenge->user_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Check-in não encontrado'], 404);
            }
            return back()->withErrors(['message' => 'Check-in não encontrado']);
        }

        try {
            // Soft delete do check-in
            $checkin->delete();

            // Remover imagem se existir
            if ($checkin->image_path) {
                Storage::disk('public')->delete($checkin->image_path);
            }

            // Atualizar stats do user challenge
            $checkin->userChallenge->updateStats();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Check-in removido com sucesso!']);
            }

            return redirect()->back()->with('success', 'Check-in removido com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erro interno do servidor'], 500);
            }
            return back()->withErrors(['message' => 'Erro ao remover check-in. Tente novamente.']);
        }
    }

    /**
     * API para buscar tasks de hoje
     */
    public function todayTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentChallenge = $user->activeChallenges()->with('challenge.tasks')->first();

        if (!$currentChallenge) {
            return response()->json([
                'tasks' => [],
                'current_day' => 0
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
            'current_day' => $currentDay
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
        
        return min($diffDays, $userChallenge->challenge->duration_days);
    }

    /**
     * Processar webhook do WhatsApp (para check-ins automáticos)
     */
    public function processWhatsAppCheckin(Request $request): JsonResponse
    {
        // TODO: Implementar webhook do WhatsApp
        // Será implementado no Sprint 3
        
        return response()->json(['message' => 'Webhook processado'], 200);
    }

    /**
     * Estatísticas de check-ins do usuário
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $stats = [
            'total_checkins' => $user->checkins()->count(),
            'checkins_this_week' => $user->checkins()
                ->where('checked_at', '>=', now()->startOfWeek())
                ->count(),
            'checkins_this_month' => $user->checkins()
                ->where('checked_at', '>=', now()->startOfMonth())
                ->count(),
            'whatsapp_checkins' => $user->checkins()
                ->where('source', 'whatsapp')
                ->count(),
            'web_checkins' => $user->checkins()
                ->where('source', 'web')
                ->count(),
            'checkins_with_images' => $user->checkins()
                ->whereNotNull('image_url')
                ->count(),
            'average_per_day' => $this->calculateAverageCheckinsPerDay($user)
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Calcular média de check-ins por dia
     */
    private function calculateAverageCheckinsPerDay($user): float
    {
        $firstCheckin = $user->checkins()->oldest('checked_at')->first();
        
        if (!$firstCheckin) {
            return 0;
        }

        $daysSinceFirst = $firstCheckin->checked_at->diffInDays(now()) + 1;
        $totalCheckins = $user->checkins()->count();

        return round($totalCheckins / $daysSinceFirst, 1);
    }
}
