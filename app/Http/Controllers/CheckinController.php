<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\UserChallenge;
use App\Helpers\CacheHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\ImageManager;

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
            ->with('challenge')
            ->first();

        if (!$userChallenge) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Desafio não encontrado ou inativo'], 404);
            }
            return back()->withErrors(['message' => 'Desafio não encontrado ou inativo']);
        }

        // Verificar se o desafio não está expirado
        $userChallenge->updateCurrentDay();
        $userChallenge->refresh();
        
        if ($userChallenge->status === 'completed') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Este desafio já foi concluído e não aceita mais check-ins'], 403);
            }
            return back()->withErrors(['message' => 'Este desafio já foi concluído e não aceita mais check-ins']);
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

        // Calcular dia atual do desafio (já atualizado no passo anterior)
        $currentDay = $this->calculateCurrentDay($userChallenge);
        
        // Verificar se o dia atual não ultrapassa o duration_days
        if ($currentDay > $userChallenge->challenge->duration_days) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Este desafio já expirou e não aceita mais check-ins'], 403);
            }
            return back()->withErrors(['message' => 'Este desafio já expirou e não aceita mais check-ins']);
        }

        // Verificar se já fez check-in hoje para esta task (com lock para evitar race condition)
        // Verifica apenas a data real (checked_at) para evitar duplicatas
        // O challenge_day pode variar se o dia mudou, mas o checked_at é sempre a data real
        $existingCheckin = Checkin::where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->whereDate('checked_at', today())
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->first();

        if ($existingCheckin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Check-in já realizado hoje para esta task',
                    'checkin' => [
                        'id' => $existingCheckin->id,
                        'image_url' => $existingCheckin->image_url,
                        'message' => $existingCheckin->message,
                        'source' => $existingCheckin->source,
                        'checked_at' => $existingCheckin->checked_at,
                        'ai_analysis' => $existingCheckin->ai_analysis,
                        'confidence_score' => $existingCheckin->confidence_score
                    ]
                ], 409);
            }
            return back()->withErrors(['message' => 'Check-in já realizado hoje para esta task']);
        }

        $imagePath = null;
        $imageUrl = null;
        $checkin = null;

        try {
            // Upload da imagem (se fornecida) - com validação adicional
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Validação adicional de tipo e tamanho
                if (!$image->isValid()) {
                    throw new \Exception('Arquivo de imagem inválido');
                }
                
                $maxSize = 5 * 1024 * 1024; // 5MB
                if ($image->getSize() > $maxSize) {
                    throw new \Exception('Imagem muito grande. Máximo: 5MB');
                }
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    throw new \Exception('Tipo de arquivo não permitido. Use: JPEG, PNG ou WebP');
                }
                
                $filename = 'checkin_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Salvar localmente por enquanto (futuro: Cloudflare R2)
                $imagePath = $image->storeAs('checkins', $filename, 'public');
                
                if (!$imagePath) {
                    throw new \Exception('Falha ao salvar imagem');
                }
                
                $imageUrl = Storage::url($imagePath);
            }

            // Criar check-in dentro de transação
            $checkin = DB::transaction(function () use ($userChallenge, $task, $currentDay, $request, $imagePath, $imageUrl) {
                return Checkin::create([
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
            });

            // Processar IA se for PRO e solicitado
            if ($user->is_pro && $request->use_ai_analysis && $imageUrl) {
                // TODO: Queue job para análise IA
                // ProcessCheckinWithAI::dispatch($checkin);
            }

            // Atualizar stats do user challenge
            $userChallenge->updateStats();
            
            // Invalidar cache relacionado
            CacheHelper::invalidateCheckinCache($user->id, $userChallenge->challenge_id);

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Cleanup da imagem se houver erro de validação
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e; // Re-throw para Laravel tratar
        } catch (\Exception $e) {
            // Log do erro para debug
            Log::error('Erro ao criar check-in', [
                'user_id' => $user->id,
                'user_challenge_id' => $userChallenge->id,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Cleanup da imagem se houver erro
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                try {
                    Storage::disk('public')->delete($imagePath);
                } catch (\Exception $cleanupException) {
                    Log::error('Erro ao limpar imagem após falha', [
                        'image_path' => $imagePath,
                        'error' => $cleanupException->getMessage()
                    ]);
                }
            }

            // Se check-in foi criado mas houve erro depois, tentar remover
            if ($checkin) {
                try {
                    $checkin->delete();
                } catch (\Exception $deleteException) {
                    Log::error('Erro ao remover check-in após falha', [
                        'checkin_id' => $checkin->id,
                        'error' => $deleteException->getMessage()
                    ]);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Erro ao realizar check-in: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors([
                'message' => 'Erro ao realizar check-in. Por favor, tente novamente. Se o problema persistir, entre em contato com o suporte.'
            ])->withInput();
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
            ->with('challenge')
            ->first();

        if (!$userChallenge) {
            return response()->json(['message' => 'Desafio não encontrado ou inativo'], 404);
        }

        // Verificar se o desafio não está expirado
        $userChallenge->updateCurrentDay();
        $userChallenge->refresh();
        
        if ($userChallenge->status === 'completed') {
            return response()->json(['message' => 'Este desafio já foi concluído e não aceita mais check-ins'], 403);
        }

        // Verificar se a task pertence ao desafio
        $task = ChallengeTask::where('id', $request->task_id)
            ->where('challenge_id', $userChallenge->challenge_id)
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task não encontrada'], 404);
        }

        // Calcular dia atual do desafio (já atualizado no passo anterior)
        $currentDay = $this->calculateCurrentDay($userChallenge);
        
        // Verificar se o dia atual não ultrapassa o duration_days
        if ($currentDay > $userChallenge->challenge->duration_days) {
            return response()->json(['message' => 'Este desafio já expirou e não aceita mais check-ins'], 403);
        }

        // Verificar se já fez check-in hoje para esta task (com lock para evitar race condition)
        // Verifica apenas a data real (checked_at) para evitar duplicatas
        // O challenge_day pode variar se o dia mudou, mas o checked_at é sempre a data real
        $existingCheckin = Checkin::where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->whereDate('checked_at', today())
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->first();

        if ($existingCheckin) {
            return response()->json([
                'message' => 'Check-in já realizado hoje para esta task',
                'checkin' => [
                    'id' => $existingCheckin->id,
                    'image_url' => $existingCheckin->image_url,
                    'message' => $existingCheckin->message,
                    'source' => $existingCheckin->source,
                    'checked_at' => $existingCheckin->checked_at,
                    'ai_analysis' => $existingCheckin->ai_analysis,
                    'confidence_score' => $existingCheckin->confidence_score
                ]
            ], 409);
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

            // Recarregar o check-in para garantir que está atualizado
            $checkin->refresh();

            // Atualizar stats do user challenge (pode lançar exceção)
            try {
                $userChallenge->updateStats();
            } catch (\Exception $statsError) {
                Log::warning('Erro ao atualizar stats após check-in', [
                    'user_challenge_id' => $userChallenge->id,
                    'error' => $statsError->getMessage()
                ]);
                // Não falhar o check-in por causa de erro nas stats
            }
            
            // Invalidar cache relacionado (já tem tratamento de erros interno)
            CacheHelper::invalidateCheckinCache($user->id, $userChallenge->challenge_id);

            // Serializar checked_at corretamente para JSON
            $checkedAt = $checkin->checked_at;
            if ($checkedAt instanceof \Carbon\Carbon) {
                $checkedAt = $checkedAt->toDateTimeString();
            } elseif ($checkedAt) {
                $checkedAt = (string) $checkedAt;
            } else {
                $checkedAt = null;
            }

            // Preparar resposta JSON
            $responseData = [
                'message' => 'Check-in realizado com sucesso!',
                'checkin' => [
                    'id' => (int) $checkin->id,
                    'image_url' => $checkin->image_url ?? null,
                    'message' => $checkin->message ?? null,
                    'source' => $checkin->source ?? 'web',
                    'checked_at' => $checkedAt,
                    'ai_analysis' => $checkin->ai_analysis ?? null,
                    'confidence_score' => $checkin->confidence_score ? (float) $checkin->confidence_score : null
                ]
            ];

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar quick check-in', [
                'user_id' => $user->id,
                'user_challenge_id' => $request->user_challenge_id,
                'task_id' => $request->task_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro ao realizar check-in'
            ], 500);
        }
    }

    /**
     * Remover check-in
     */
    public function destroy(Request $request, Checkin $checkin): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Carregar relacionamentos necessários antes de deletar
        $checkin->load('userChallenge.challenge');

        // Verificar se o check-in pertence ao usuário
        if (!$checkin->userChallenge || $checkin->userChallenge->user_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Check-in não encontrado'], 404);
            }
            return back()->withErrors(['message' => 'Check-in não encontrado']);
        }

        // Salvar dados necessários antes de deletar
        $userChallenge = $checkin->userChallenge;
        $challengeId = $userChallenge->challenge_id;
        $imagePath = $checkin->image_path;

        try {
            // Remover imagem se existir (antes de deletar o check-in)
            if ($imagePath) {
                try {
                    Storage::disk('public')->delete($imagePath);
                } catch (\Exception $imageError) {
                    Log::warning('Erro ao remover imagem do check-in', [
                        'checkin_id' => $checkin->id,
                        'image_path' => $imagePath,
                        'error' => $imageError->getMessage()
                    ]);
                    // Não falhar o delete por causa de erro na imagem
                }
            }

            // Deletar o check-in
            $checkin->forceDelete();

            // Atualizar stats do user challenge (pode lançar exceção)
            try {
                $userChallenge->refresh();
                $userChallenge->updateStats();
            } catch (\Exception $statsError) {
                Log::warning('Erro ao atualizar stats após deletar check-in', [
                    'user_challenge_id' => $userChallenge->id,
                    'error' => $statsError->getMessage()
                ]);
                // Não falhar o delete por causa de erro nas stats
            }
            
            // Invalidar cache relacionado (já tem tratamento de erros interno)
            CacheHelper::invalidateCheckinCache($user->id, $challengeId);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Check-in removido com sucesso!']);
            }

            return redirect()->back()->with('success', 'Check-in removido com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao deletar check-in', [
                'checkin_id' => $checkin->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Erro interno do servidor',
                    'error' => config('app.debug') ? $e->getMessage() : 'Erro ao remover check-in'
                ], 500);
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
            // Verificar se já fez check-in hoje (data real)
            // Busca check-ins de hoje independentemente do challenge_day para garantir que encontre todos
            $todayCheckin = Checkin::where('user_challenge_id', $currentChallenge->id)
                ->where('task_id', $task->id)
                ->whereDate('checked_at', today())
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
     * Usa o current_day do model que já está atualizado e limitado corretamente
     */
    private function calculateCurrentDay(UserChallenge $userChallenge): int
    {
        // Recarregar para pegar o current_day atualizado
        $userChallenge->refresh();
        
        // Se o desafio está completo, retorna o último dia
        if ($userChallenge->status === 'completed') {
            return $userChallenge->challenge->duration_days;
        }
        
        // Retorna o current_day que já foi atualizado pelo updateCurrentDay()
        return max(1, (int) $userChallenge->current_day);
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
        $totalCheckins = $user->checkins()->count();
        $daysSinceRegistration = $user->created_at->diffInDays(now()) + 1;
        
        return $daysSinceRegistration > 0 ? $totalCheckins / $daysSinceRegistration : 0;
    }

    /**
     * Gerar card compartilhável do dia
     */
    public function shareCard(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        // Validação
        $validator = Validator::make($request->all(), [
            'challenge_id' => ['required', 'exists:user_challenges,id'],
            'day' => ['required', 'integer', 'min:1'],
            'total_days' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.completed' => ['required', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar se o user challenge pertence ao usuário
        $userChallenge = UserChallenge::where('id', $request->challenge_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$userChallenge) {
            return response()->json(['message' => 'Desafio não encontrado ou inativo'], 404);
        }

        try {
            // Gerar imagem usando Intervention Image
            $image = $this->generateShareCardImage($request->all(), $user);
            
            // Retornar imagem como resposta
            return response($image, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="dopa-check-card-' . date('Y-m-d') . '.png"'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar card compartilhável: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar imagem'], 500);
        }
    }

    /**
     * Gerar imagem do card compartilhável baseado no template DOPA Check
     */
    private function generateShareCardImage(array $data, $user): string
    {
        $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        
        // Carregar template base
        $templatePath = public_path('images/share_card_template.png');
        
        if (file_exists($templatePath)) {
            // Usar template existente
            $canvas = $manager->read($templatePath);
            
            // Redimensionar se necessário para garantir o tamanho correto
            $canvas = $canvas->resize(1080, 1920);
        } else {
            // Fallback: criar template básico se não existir
            $width = 1080;
            $height = 1920;
            $canvas = $manager->create($width, $height);
            $canvas->fill('#f8fafc');
            
            // Área azul do rodapé como fallback
            $footerHeight = 240;
            $footer = $manager->create($width, $footerHeight);
            $footer->fill('#22d3ee');
            $canvas->place($footer, '0', (int)($height - $footerHeight));
            
            Log::warning('Template de card não encontrado em: ' . $templatePath);
        }

        // Caminhos das fontes (com fallback)
        $fontRegular = public_path('fonts/Poppins-Regular.ttf');
        $fontSemiBold = public_path('fonts/Poppins-SemiBold.ttf');
        $fontBold = public_path('fonts/Poppins-Bold.ttf');
        
        // Verificar se as fontes existem
        if (!file_exists($fontRegular)) $fontRegular = null;
        if (!file_exists($fontSemiBold)) $fontSemiBold = null;
        if (!file_exists($fontBold)) $fontBold = null;

        // ========================================
        // TÍTULO DO DESAFIO (abaixo do logo)
        // ========================================
        
        $canvas->text(strtoupper($data['title']), 540, 480, function ($font) use ($fontBold) {
            if ($fontBold) $font->file($fontBold);
            $font->size(48);
            $font->color('#0f766e'); // Verde escuro (teal)
            $font->align('center');
            $font->valign('center');
        });

        // ========================================
        // DESCRIÇÃO (logo abaixo do título)
        // ========================================
        
        // Quebrar descrição em múltiplas linhas se necessário
        $description = $this->wrapText($data['description'], 65);
        $descriptionLines = explode("\n", $description);
        
        $descY = 560;
        foreach ($descriptionLines as $line) {
            $canvas->text($line, 540, $descY, function ($font) use ($fontRegular) {
                if ($fontRegular) $font->file($fontRegular);
                $font->size(32);
                $font->color('#374151'); // Cinza médio
                $font->align('center');
                $font->valign('center');
            });
            $descY += 42;
        }

        // ========================================
        // LISTA DE TASKS
        // ========================================
        
        // Margin top maior para evitar sobreposição com descrições longas
        $tasksStartY = $descY + 100; // Aumentado de 60 para 100
        $taskSpacing = 80;
        $leftMargin = 140;
        
        foreach ($data['tasks'] as $index => $task) {
            $y = $tasksStartY + ($index * $taskSpacing);
            
            // Definir ícone baseado no status
            if ($task['completed']) {
                // Usar imagem PNG para check verde
                $checkIconPath = public_path('images/check-green.png');
                if (file_exists($checkIconPath)) {
                    $checkIcon = $manager->read($checkIconPath);
                    $checkIcon = $checkIcon->resize(40, 40); // Redimensionar para 40x40px
                    $canvas->place($checkIcon, 'top-left', (int)($leftMargin - 20), (int)($y - 20));
                } else {
                    // Fallback: usar caractere
                    $canvas->text('✓', $leftMargin, $y, function ($font) use ($fontBold) {
                        if ($fontBold) $font->file($fontBold);
                        $font->size(48);
                        $font->color('#10b981');
                        $font->align('center');
                        $font->valign('center');
                    });
                }
                $textColor = '#374151'; // Cinza escuro
            } else {
                // Usar imagem PNG para X vermelho
                $xIconPath = public_path('images/x-red.png');
                if (file_exists($xIconPath)) {
                    $xIcon = $manager->read($xIconPath);
                    $xIcon = $xIcon->resize(40, 40); // Redimensionar para 40x40px
                    $canvas->place($xIcon, 'top-left', (int)($leftMargin - 20), (int)($y - 20));
                } else {
                    // Fallback: usar caractere
                    $canvas->text('✗', $leftMargin, $y, function ($font) use ($fontBold) {
                        if ($fontBold) $font->file($fontBold);
                        $font->size(48);
                        $font->color('#ef4444');
                        $font->align('center');
                        $font->valign('center');
                    });
                }
                $textColor = '#6b7280'; // Cinza médio
            }
            
            // Desenhar texto da task (maiúsculo)
            $taskText = strtoupper($task['name']);
            $canvas->text($taskText, $leftMargin + 80, $y, function ($font) use ($fontSemiBold, $textColor) {
                if ($fontSemiBold) $font->file($fontSemiBold);
                $font->size(36);
                $font->color($textColor);
                $font->align('left');
                $font->valign('center');
            });
        }

        // ========================================
        // INDICADOR DE PROGRESSO (CANTO DIREITO DO RODAPÉ)
        // ========================================
        
        // Posição no canto direito da área azul do rodapé
        $progressX = 940; // Canto direito
        $progressY = 1760; // Na área azul do rodapé
        
        // "dia." (pequeno, branco)
        $canvas->text('dia.', $progressX, $progressY - 25, function ($font) use ($fontRegular) {
            if ($fontRegular) $font->file($fontRegular);
            $font->size(22);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('center');
        });
        
        // Número do dia (grande, branco)
        $canvas->text((string)$data['day'], $progressX, $progressY + 15, function ($font) use ($fontBold) {
            if ($fontBold) $font->file($fontBold);
            $font->size(72);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('center');
        });
        
        // "/" + total de dias (menor, branco) - posição mais à esquerda
        $canvas->text('/' . $data['total_days'], $progressX + 35, $progressY + 40, function ($font) use ($fontSemiBold) {
            if ($fontSemiBold) $font->file($fontSemiBold);
            $font->size(36);
            $font->color('#ffffff');
            $font->align('left');
            $font->valign('center');
        });

        return $canvas->encode(new \Intervention\Image\Encoders\PngEncoder())->toString();
    }

    /**
     * Quebra texto em múltiplas linhas
     */
    private function wrapText(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $word) {
            $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
            
            if (strlen($testLine) <= $maxLength) {
                $currentLine = $testLine;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    // Palavra muito longa, forçar quebra
                    $lines[] = $word;
                    $currentLine = '';
                }
            }
        }
        
        if ($currentLine) {
            $lines[] = $currentLine;
        }
        
        return implode("\n", $lines);
    }
}
