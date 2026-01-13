<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_listing_shows_user_participation_status(): void
    {
        // Criar usuário com todos os campos necessários
        $user = User::factory()->create([
            'plan' => 'free',
            'username' => 'testuser',
        ]);
        
        // Criar desafio
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
            'is_featured' => false,
        ]);
        
        // Usuário participa do desafio
        UserChallenge::factory()->create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
        ]);
        
        // Fazer requisição para listagem de desafios
        $response = $this->actingAs($user)->get('/challenges');
        
        $response->assertStatus(200);
        
        // Verificar se o desafio tem a informação de participação
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Index')
                ->has('challenges.data', 1)
                ->where('challenges.data.0.user_is_participating', true)
        );
    }
    
    public function test_challenge_listing_shows_user_not_participating(): void
    {
        // Criar usuário com todos os campos necessários
        $user = User::factory()->create([
            'plan' => 'free',
            'username' => 'testuser',
        ]);
        
        // Criar desafio
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
            'is_featured' => false,
        ]);
        
        // Fazer requisição para listagem de desafios
        $response = $this->actingAs($user)->get('/challenges');
        
        $response->assertStatus(200);
        
        // Verificar se o desafio tem a informação de não participação
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Index')
                ->has('challenges.data', 1)
                ->where('challenges.data.0.user_is_participating', false)
        );
    }
    
    public function test_featured_challenges_show_user_participation_status(): void
    {
        // Criar usuário com todos os campos necessários
        $user = User::factory()->create([
            'plan' => 'free',
            'username' => 'testuser',
        ]);
        
        // Criar desafio em destaque
        $featuredChallenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
            'is_featured' => true,
        ]);
        
        // Usuário participa do desafio em destaque
        UserChallenge::factory()->create([
            'user_id' => $user->id,
            'challenge_id' => $featuredChallenge->id,
            'status' => 'active',
        ]);
        
        // Fazer requisição para listagem de desafios
        $response = $this->actingAs($user)->get('/challenges');
        
        $response->assertStatus(200);
        
        // Verificar se o desafio em destaque tem a informação de participação
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Index')
                ->has('featuredChallenges', 1)
                ->where('featuredChallenges.0.user_is_participating', true)
        );
    }
    
    public function test_anonymous_user_does_not_have_participation_info(): void
    {
        // Criar desafio
        $challenge = Challenge::factory()->create([
            'visibility' => Challenge::VISIBILITY_GLOBAL,
            'is_featured' => false,
        ]);
        
        // Fazer requisição para listagem de desafios sem autenticação
        $response = $this->get('/challenges');
        
        $response->assertStatus(200);
        
        // Verificar se o desafio não tem informação de participação para usuário anônimo
        $response->assertInertia(fn ($page) => 
            $page->component('Challenges/Index')
                ->has('challenges.data', 1)
                ->missing('challenges.data.0.user_is_participating')
        );
    }
} 