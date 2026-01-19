<?php

declare(strict_types=1);

use App\Models\Challenge;
use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\User;
use App\Models\UserChallenge;

it('cria desafio com período e recalcula duration_days pelas datas', function () {
    // Mantém como FREE para não depender de subscription_ends_at nos testes
    $user = User::factory()->create();

    $start = now()->subDays(3)->toDateString();
    $end = now()->addDays(10)->toDateString(); // 14 dias incluindo início
    $expectedDuration = (int) (\Carbon\Carbon::parse($start)->startOfDay()->diffInDays(\Carbon\Carbon::parse($end)->startOfDay(), false) + 1);

    $payload = [
        'title' => 'Desafio teste período',
        'description' => 'Descrição com tamanho suficiente para passar na validação.',
        'duration_days' => 21, // será recalculado para bater com start/end
        'start_date' => $start,
        'end_date' => $end,
        'category' => 'fitness',
        'difficulty' => 'beginner',
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'team_id' => null,
        'tasks' => [
            [
                'name' => 'Treinar',
                'hashtag' => 'treino',
                'description' => null,
                'is_required' => true,
                'icon' => '🏋️',
                'color' => '#3B82F6',
            ],
        ],
    ];

    $this->actingAs($user)
        ->post('/challenges', $payload)
        ->assertRedirect();

    /** @var Challenge $challenge */
    $challenge = Challenge::query()->latest('id')->firstOrFail();

    expect($challenge->start_date?->toDateString())->toBe($start)
        ->and($challenge->end_date?->toDateString())->toBe($end)
        ->and($challenge->duration_days)->toBe($expectedDuration);

    expect(UserChallenge::query()->where('challenge_id', $challenge->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

it('permite quick-checkin retroativo dentro do período e calcula challenge_day pelo start_date global', function () {
    $user = User::factory()->create();

    $start = now()->subDays(2)->startOfDay();
    $end = $start->copy()->addDays(6)->startOfDay(); // 7 dias

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'is_public' => true,
        'duration_days' => 7,
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'hashtag' => 'leitura',
        'name' => 'Ler',
    ]);

    $userChallenge = UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'status' => 'active',
        'current_day' => 1,
    ]);

    $checkedDate = $start->copy()->addDay()->toDateString(); // day 2

    $this->actingAs($user)
        ->postJson('/api/quick-checkin', [
            'task_id' => $task->id,
            'user_challenge_id' => $userChallenge->id,
            'checked_date' => $checkedDate,
            'source' => 'web',
        ])
        ->assertCreated();

    $checkin = Checkin::query()->where('user_challenge_id', $userChallenge->id)->where('task_id', $task->id)->firstOrFail();
    expect($checkin->challenge_day)->toBe(2)
        ->and(\Carbon\Carbon::parse($checkin->checked_at)->toDateString())->toBe($checkedDate);
});

it('bloqueia quick-checkin no futuro mesmo que esteja dentro do end_date', function () {
    $user = User::factory()->create();

    $start = now()->subDays(1)->startOfDay();
    $end = now()->addDays(10)->startOfDay();

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'is_public' => true,
        'duration_days' => 12,
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'hashtag' => 'agua',
        'name' => 'Beber água',
    ]);

    $userChallenge = UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'status' => 'active',
        'current_day' => 1,
    ]);

    $this->actingAs($user)
        ->postJson('/api/quick-checkin', [
            'task_id' => $task->id,
            'user_challenge_id' => $userChallenge->id,
            'checked_date' => now()->addDay()->toDateString(),
            'source' => 'web',
        ])
        ->assertStatus(422);
});

it('bloqueia quick-checkin antes do start_date do desafio', function () {
    $user = User::factory()->create();

    $start = now()->subDays(1)->startOfDay();
    $end = now()->addDays(5)->startOfDay();

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'is_public' => true,
        'duration_days' => 7,
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'hashtag' => 'sono',
        'name' => 'Dormir cedo',
    ]);

    $userChallenge = UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'status' => 'active',
        'current_day' => 1,
    ]);

    $this->actingAs($user)
        ->postJson('/api/quick-checkin', [
            'task_id' => $task->id,
            'user_challenge_id' => $userChallenge->id,
            'checked_date' => $start->copy()->subDay()->toDateString(),
            'source' => 'web',
        ])
        ->assertStatus(422);
});

