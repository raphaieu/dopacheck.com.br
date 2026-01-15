<?php

declare(strict_types=1);

use App\Jobs\ProcessWhatsappCheckinJob;
use App\Models\Challenge;
use App\Models\ChallengeTask;
use App\Models\Checkin;
use App\Models\Team;
use App\Models\User;
use App\Models\UserChallenge;
use App\Services\EvolutionApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('cria check-in via WhatsApp (DM, imagem + #hashtag) e reage com ✅', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'whatsapp_number' => '5511948863848',
        'phone' => '5511948863848',
    ]);

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'is_public' => true,
        'duration_days' => 7,
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'hashtag' => 'treino',
        'name' => 'Treino',
    ]);

    $userChallenge = UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'status' => 'active',
        'current_day' => 1,
        'started_at' => now()->startOfDay(),
    ]);

    $imagePath = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/test.jpg';
    Storage::disk('public')->put($imagePath, 'fake-image-bytes');

    $mock = \Mockery::mock(EvolutionApiService::class);
    $mock->shouldReceive('sendReaction')
        ->once()
        ->withArgs(function (...$args) {
            [$instance, $remoteJid, $messageId, $emoji] = $args;
            return $instance === 'DOPACheck'
                && $remoteJid === '5511948863848@s.whatsapp.net'
                && $messageId === 'MSG123'
                && $emoji === '✅';
        });
    app()->instance(EvolutionApiService::class, $mock);

    $job = new ProcessWhatsappCheckinJob([
        'instance' => 'DOPACheck',
        'remote_jid' => '5511948863848@s.whatsapp.net',
        'message_id' => 'MSG123',
        'sender_phone' => '5511948863848',
        'caption' => '#treino',
        'hashtags' => ['treino'],
        'image_path' => $imagePath,
        'image_mime' => 'image/jpeg',
    ]);

    $job->handle(app(EvolutionApiService::class));

    expect(Checkin::query()->count())->toBe(1);
    $checkin = Checkin::query()->first();

    expect($checkin->user_challenge_id)->toBe($userChallenge->id)
        ->and($checkin->task_id)->toBe($task->id)
        ->and($checkin->source)->toBe('whatsapp')
        ->and($checkin->status)->toBe('approved')
        ->and($checkin->image_path)->toBe($imagePath);
});

it('falha quando usuário não existe e reage com ❌ (apagando a imagem)', function () {
    Storage::fake('public');

    $imagePath = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/test2.jpg';
    Storage::disk('public')->put($imagePath, 'fake-image-bytes');

    $mock = \Mockery::mock(EvolutionApiService::class);
    $mock->shouldReceive('sendReaction')
        ->once()
        ->withArgs(function (...$args) {
            [, , , $emoji] = $args;
            return $emoji === '❌';
        });
    app()->instance(EvolutionApiService::class, $mock);

    $job = new ProcessWhatsappCheckinJob([
        'instance' => 'DOPACheck',
        'remote_jid' => '5511999999999@s.whatsapp.net',
        'message_id' => 'MSG124',
        'sender_phone' => '5511999999999',
        'caption' => '#treino',
        'hashtags' => ['treino'],
        'image_path' => $imagePath,
        'image_mime' => 'image/jpeg',
    ]);

    $job->handle(app(EvolutionApiService::class));

    expect(Checkin::query()->count())->toBe(0);
    expect(Storage::disk('public')->exists($imagePath))->toBeFalse();
});

it('baixa a imagem via image_url quando não vem base64 (DM) e cria check-in', function () {
    Storage::fake('public');
    Http::fake([
        'mmg.whatsapp.net/*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $user = User::factory()->create([
        'whatsapp_number' => '5511948863848',
        'phone' => '5511948863848',
    ]);

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_GLOBAL,
        'is_public' => true,
        'duration_days' => 7,
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'hashtag' => 'leitura',
        'name' => 'Leitura',
    ]);

    UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'status' => 'active',
        'current_day' => 1,
        'started_at' => now()->startOfDay(),
    ]);

    $mock = \Mockery::mock(EvolutionApiService::class);
    $mock->shouldReceive('sendReaction')->once();
    app()->instance(EvolutionApiService::class, $mock);

    $job = new ProcessWhatsappCheckinJob([
        'instance' => 'DOPACheck',
        'remote_jid' => '5511948863848@s.whatsapp.net',
        'message_id' => 'MSG125',
        'sender_phone' => '5511948863848',
        'caption' => '#leitura',
        'hashtags' => ['leitura'],
        'image_url' => 'https://mmg.whatsapp.net/o1/v/t24/f2/test.jpg',
        'image_mime' => 'image/jpeg',
    ]);

    $job->handle(app(EvolutionApiService::class));

    expect(Checkin::query()->count())->toBe(1);
    $checkin = Checkin::query()->first();
    expect($checkin->image_path)->not->toBeNull();
    expect(Storage::disk('public')->exists($checkin->image_path))->toBeTrue();
});

it('cria check-in via WhatsApp em grupo mapeado para um time (scope_team_id) e reage com ✅', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'whatsapp_number' => '5511948863848',
        'phone' => '5511948863848',
    ]);

    $team = Team::factory()->create([
        'personal_team' => false,
        'whatsapp_group_jid' => '120363404774829500@g.us',
        'whatsapp_group_name' => 'Time Teste',
    ]);

    // garante membership no time do grupo
    $team->users()->attach($user->id, ['role' => 'member']);

    $challenge = Challenge::factory()->create([
        'visibility' => Challenge::VISIBILITY_TEAM,
        'team_id' => $team->id,
        'is_public' => true,
        'duration_days' => 7,
    ]);

    $task = ChallengeTask::factory()->create([
        'challenge_id' => $challenge->id,
        'scope_team_id' => $team->id,
        'hashtag' => 'leitura',
        'name' => 'Leitura',
    ]);

    $userChallenge = UserChallenge::factory()->create([
        'user_id' => $user->id,
        'challenge_id' => $challenge->id,
        'team_id' => $team->id,
        'status' => 'active',
        'current_day' => 1,
        'started_at' => now()->startOfDay(),
    ]);

    $imagePath = 'checkins/whatsapp/' . now()->format('Y-m-d') . '/group-test.jpg';
    Storage::disk('public')->put($imagePath, 'fake-image-bytes');

    $mock = \Mockery::mock(EvolutionApiService::class);
    $mock->shouldReceive('sendReaction')
        ->once()
        ->withArgs(function (...$args) {
            [$instance, $remoteJid, $messageId, $emoji, $senderPhone, $participantJid] = $args;
            return $instance === 'DOPACheck'
                && $remoteJid === '120363404774829500@g.us'
                && $messageId === 'MSG126'
                && $emoji === '✅'
                && $senderPhone === '5511948863848'
                && $participantJid === '5511948863848@s.whatsapp.net';
        });
    app()->instance(EvolutionApiService::class, $mock);

    $job = new ProcessWhatsappCheckinJob([
        'instance' => 'DOPACheck',
        'remote_jid' => '120363404774829500@g.us',
        'message_id' => 'MSG126',
        'sender_phone' => '5511948863848',
        'participant_jid' => '5511948863848@s.whatsapp.net',
        'caption' => '#leitura',
        'hashtags' => ['leitura'],
        'has_image' => true,
        'image_path' => $imagePath,
        'image_mime' => 'image/jpeg',
    ]);

    $job->handle(app(EvolutionApiService::class));

    expect(Checkin::query()->count())->toBe(1);
    $checkin = Checkin::query()->first();
    expect($checkin->user_challenge_id)->toBe($userChallenge->id)
        ->and($checkin->task_id)->toBe($task->id)
        ->and($checkin->source)->toBe('whatsapp')
        ->and($checkin->status)->toBe('approved');
});

