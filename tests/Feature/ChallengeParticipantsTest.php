<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_challenge_participants_page(): void
    {
        // Criar desafio global
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
        ]);
        
        // Fazer requisição para a página de participantes
        $response = $this->get("/challenges/{$challenge->id}/participants");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Participants')
                ->has('challenge')
                ->has('participants')
                ->has('stats')
        );
    }
    
    public function test_private_challenge_participants_requires_authentication(): void
    {
        // Criar desafio privado
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_PRIVATE,
        ]);
        
        // Tentar acessar sem autenticação
        $response = $this->get("/challenges/{$challenge->id}/participants");
        
        $response->assertStatus(404);
    }
    
    public function test_private_challenge_participants_allowed_for_participant(): void
    {
        // Em "private", apenas o criador pode ver/participar.
        // Criar usuário com todos os campos necessários
        $user = User::factory()->create([
            'plan' => 'free',
            'username' => 'testuser',
            'profile_photo_path' => null,
        ]);
        
        // Criar desafio privado
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_PRIVATE,
            'created_by' => $user->id,
        ]);
        
        // Fazer requisição autenticada
        $response = $this->actingAs($user)->get("/challenges/{$challenge->id}/participants");
        
        $response->assertStatus(200);
    }
    
    public function test_participants_page_shows_participants_list(): void
    {
        // Criar desafio
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
        ]);
        
        // Criar usuários com campos necessários
        $users = User::factory()->count(3)->create([
            'profile_photo_path' => null,
        ]);
        
        // Criar participantes
        foreach ($users as $user) {
            UserChallenge::factory()->create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'status' => 'active',
            ]);
        }
        
        // Fazer requisição
        $response = $this->get("/challenges/{$challenge->id}/participants");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Participants')
                ->where('participants.total', 3)
        );
    }
} 