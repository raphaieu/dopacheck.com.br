<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamApplication;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

final class TeamOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_join_page_loads_by_team_slug(): void
    {
        $team = Team::factory()->create();
        $team->forceFill(['slug' => 'salvado'])->save();

        $response = $this->get('/join/salvado');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Join/TeamJoin')
                ->where('team.slug', 'salvado')
        );
    }

    public function test_public_join_submission_creates_a_pending_application_and_enforces_uniqueness_per_team(): void
    {
        $team = Team::factory()->create();
        $team->forceFill(['slug' => 'salvado'])->save();

        $payload = [
            'name' => 'Raphael Martins',
            'birthdate' => '1983-02-11',
            'email' => 'rapha@raphael-martins.com',
            'whatsapp_number' => '11948863848',
            'city' => 'Salvador',
            'neighborhood' => 'Itapuã',
            'circle_url' => 'https://comunidade.reservatoriodedopamina.com.br/u/23af3871',
        ];

        $this->post('/join/salvado', $payload)->assertSessionHasNoErrors();

        $this->assertSame(1, TeamApplication::query()->count());
        $app = TeamApplication::query()->firstOrFail();
        $this->assertSame($team->id, $app->team_id);
        $this->assertSame('pending', $app->status);

        // Duplicado por email (mesmo team)
        $this->post('/join/salvado', [
            ...$payload,
            'whatsapp_number' => '11999990000',
        ])->assertSessionHasErrors(['email']);

        // Duplicado por whatsapp (mesmo team)
        $this->post('/join/salvado', [
            ...$payload,
            'email' => 'outro@exemplo.com',
        ])->assertSessionHasErrors(['whatsapp_number']);
    }

    public function test_only_team_owner_or_admin_can_view_and_approve_or_reject_applications(): void
    {
        $owner = User::factory()->withPersonalTeam()->create([
            'plan' => 'free',
            'username' => 'owner-user',
        ]);
        $team = Team::factory()->create([
            'user_id' => $owner->id,
            'personal_team' => false,
        ]);
        $team->forceFill(['slug' => 'salvado'])->save();

        $admin = User::factory()->create([
            'plan' => 'free',
            'username' => 'admin-user',
        ]);
        $team->users()->attach($admin, ['role' => 'admin']);

        $viewer = User::factory()->create([
            'plan' => 'free',
            'username' => 'viewer-user',
        ]);
        $team->users()->attach($viewer, ['role' => 'viewer']);

        $app = TeamApplication::create([
            'team_id' => $team->id,
            'name' => 'Teste',
            'birthdate' => '2000-01-01',
            'email' => 'teste@example.com',
            'whatsapp_number' => '11911112222',
            'city' => 'Salvador',
            'neighborhood' => 'Centro',
            'circle_url' => 'https://example.com/u/teste',
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)->get("/teams/{$team->id}/applications")->assertStatus(403);

        $this->actingAs($admin)->get("/teams/{$team->id}/applications")->assertStatus(200);
        $this->actingAs($owner)->get("/teams/{$team->id}/applications")->assertStatus(200);

        $this->actingAs($admin)
            ->patch("/teams/{$team->id}/applications/{$app->id}", ['action' => 'approve'])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $app->fresh()->status);
    }

    public function test_approved_applications_are_claimed_on_login_and_user_is_added_to_team_as_viewer(): void
    {
        $team = Team::factory()->create();
        $team->forceFill(['slug' => 'salvado'])->save();

        $application = TeamApplication::create([
            'team_id' => $team->id,
            'name' => 'Teste',
            'birthdate' => '2000-01-01',
            'email' => 'claim@example.com',
            'whatsapp_number' => '5511999998888',
            'city' => 'Salvador',
            'neighborhood' => 'Centro',
            'circle_url' => 'https://example.com/u/teste',
            'status' => 'approved',
            'approved_by' => null,
            'approved_at' => now(),
            'user_id' => null,
        ]);

        $user = User::factory()->create([
            'email' => 'claim@example.com',
            'whatsapp_number' => '55 (11) 99999-8888',
        ]);

        event(new Login('web', $user, false));

        $this->assertSame($user->id, $application->fresh()->user_id);
        $this->assertSame(1, DB::table('team_user')->where('team_id', $team->id)->where('user_id', $user->id)->count());
        $this->assertSame('viewer', DB::table('team_user')->where('team_id', $team->id)->where('user_id', $user->id)->value('role'));
    }
}

