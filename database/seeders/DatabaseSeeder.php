<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste principal
        $testUser = User::factory()->withPersonalTeam()->create([
            'name' => 'Raphael Martins',
            'email' => 'rapha@raphael-martins.com',
            'username' => 'raphaieu',
            'password' => Hash::make('password'),
            'plan' => 'pro',
            'whatsapp_number' => '5511948863848',
            'phone' => '11948863848',
            'subscription_ends_at' => now()->addYear(),
            'preferences' => [
                'notifications' => [
                    'email' => true,
                    'whatsapp' => true,
                    'daily_reminder' => true,
                ],
                'privacy' => [
                    'public_profile' => true,
                    'show_progress' => true,
                ],
            ],
        ]);

        // Criar usuário free para testes
        $freeUser = User::factory()->withPersonalTeam()->create([
            'name' => 'Usuário Teste Free',
            'email' => 'free@test.com',
            'username' => 'usuarioteste',
            'password' => Hash::make('password'),
            'plan' => 'free',
            'whatsapp_number' => '5511999998888',
            'phone' => '11999998888',
        ]);

        // Criar alguns usuários adicionais para simular comunidade
        User::factory(10)->withPersonalTeam()->create([
            'plan' => 'free',
        ]);

        User::factory(3)->withPersonalTeam()->create([
            'plan' => 'pro',
            'subscription_ends_at' => now()->addMonths(rand(1, 12)),
        ]);

        // Executar seeders de desafios
        $this->call([
            ChallengeSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('👤 Test Users created:');
        $this->command->info('   • PRO: rapha@raphael-martins.com (password: password)');
        $this->command->info('   • FREE: free@test.com (password: password)');
        $this->command->info('🎯 Challenge templates created: 6');
        $this->command->info('📱 Ready for DOPA Check development!');
    }
}
