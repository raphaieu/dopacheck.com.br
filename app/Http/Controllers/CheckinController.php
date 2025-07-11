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
            // $userChallenge->updateStats();

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

        // Verificar se já fez check-in hoje para esta task (com lock para evitar race condition)
        $existingCheckin = Checkin::where('user_challenge_id', $userChallenge->id)
            ->where('task_id', $task->id)
            ->where('challenge_day', $currentDay)
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
            // deletando o check-in
            $checkin->forceDelete();

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
        
        return (int) min($diffDays, $userChallenge->challenge->duration_days);
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
            \Log::error('Erro ao gerar card compartilhável: ' . $e->getMessage());
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
            
            \Log::warning('Template de card não encontrado em: ' . $templatePath);
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
