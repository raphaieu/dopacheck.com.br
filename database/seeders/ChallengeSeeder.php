<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeTask;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Desafio de Leitura (21 dias)
        $readingChallenge = Challenge::create([
            'title' => '21 Dias de Leitura',
            'description' => 'Desenvolva o hábito de leitura diária por 21 dias consecutivos. Ideal para quem quer criar uma rotina de aprendizado.',
            'duration_days' => 21,
            'is_template' => true,
            'is_public' => true,
            'is_featured' => true,
            'category' => 'learning',
            'difficulty' => 'beginner',
            'image_url' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800',
            'tags' => ['leitura', 'aprendizado', 'hábitos', 'desenvolvimento pessoal'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $readingChallenge->id,
            'name' => 'Ler pelo menos 30 minutos',
            'hashtag' => 'leitura',
            'description' => 'Leia qualquer livro, artigo ou material educativo por no mínimo 30 minutos',
            'order' => 1,
            'icon' => '📚',
            'color' => '#8B5CF6',
            'validation_rules' => [
                'required_objects' => ['book', 'text', 'reading'],
                'forbidden_objects' => ['phone', 'tv', 'computer'],
            ],
        ]);

        // 2. Desafio de Exercícios (30 dias)
        $fitnessChallenge = Challenge::create([
            'title' => '30 Dias de Movimento',
            'description' => 'Transforme seu corpo e mente com 30 dias de atividade física. Pode ser academia, corrida, yoga ou qualquer exercício.',
            'duration_days' => 30,
            'is_template' => true,
            'is_public' => true,
            'is_featured' => true,
            'category' => 'fitness',
            'difficulty' => 'intermediate',
            'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
            'tags' => ['exercício', 'fitness', 'saúde', 'movimento'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $fitnessChallenge->id,
            'name' => 'Exercitar por 30 minutos',
            'hashtag' => 'treino',
            'description' => 'Faça qualquer atividade física: academia, corrida, caminhada, yoga, dança',
            'order' => 1,
            'icon' => '🏃‍♂️',
            'color' => '#F59E0B',
            'validation_rules' => [
                'required_objects' => ['exercise', 'gym', 'running', 'sports'],
            ],
        ]);

        ChallengeTask::create([
            'challenge_id' => $fitnessChallenge->id,
            'name' => 'Beber 2L de água',
            'hashtag' => 'agua',
            'description' => 'Mantenha-se hidratado bebendo pelo menos 2 litros de água',
            'order' => 2,
            'icon' => '💧',
            'color' => '#06B6D4',
            'is_required' => false,
        ]);

        // 3. Desafio de Meditação (14 dias)
        $meditationChallenge = Challenge::create([
            'title' => '14 Dias de Mindfulness',
            'description' => 'Cultive paz interior e foco com 2 semanas de prática de meditação e mindfulness.',
            'duration_days' => 14,
            'is_template' => true,
            'is_public' => true,
            'category' => 'mindfulness',
            'difficulty' => 'beginner',
            'image_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
            'tags' => ['meditação', 'mindfulness', 'bem-estar', 'mental'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $meditationChallenge->id,
            'name' => 'Meditar por 10 minutos',
            'hashtag' => 'meditacao',
            'description' => 'Pratique meditação, respiração consciente ou mindfulness por 10 minutos',
            'order' => 1,
            'icon' => '🧘‍♀️',
            'color' => '#10B981',
        ]);

        // 4. Desafio Detox Digital (7 dias)
        $detoxChallenge = Challenge::create([
            'title' => '7 Dias Detox Digital',
            'description' => 'Reduza o tempo em redes sociais e reconecte-se com o mundo real por uma semana.',
            'duration_days' => 7,
            'is_template' => true,
            'is_public' => true,
            'category' => 'lifestyle',
            'difficulty' => 'advanced',
            'image_url' => 'https://images.unsplash.com/photo-1515378791036-0648a814c963?w=800',
            'tags' => ['detox', 'digital', 'produtividade', 'foco'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $detoxChallenge->id,
            'name' => 'Máximo 1h de redes sociais',
            'hashtag' => 'detox',
            'description' => 'Limite seu tempo em redes sociais a no máximo 1 hora por dia',
            'order' => 1,
            'icon' => '📱',
            'color' => '#EF4444',
        ]);

        ChallengeTask::create([
            'challenge_id' => $detoxChallenge->id,
            'name' => 'Atividade offline',
            'hashtag' => 'offline',
            'description' => 'Faça uma atividade sem telas: ler, conversar, passear, cozinhar',
            'order' => 2,
            'icon' => '🌿',
            'color' => '#059669',
        ]);

        // 5. Desafio de Gratidão (21 dias)
        $gratitudeChallenge = Challenge::create([
            'title' => '21 Dias de Gratidão',
            'description' => 'Transforme sua perspectiva de vida praticando gratidão diariamente por 21 dias.',
            'duration_days' => 21,
            'is_template' => true,
            'is_public' => true,
            'category' => 'mindfulness',
            'difficulty' => 'beginner',
            'image_url' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800',
            'tags' => ['gratidão', 'positividade', 'bem-estar', 'journaling'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $gratitudeChallenge->id,
            'name' => 'Escrever 3 gratidões',
            'hashtag' => 'gratidao',
            'description' => 'Anote 3 coisas pelas quais você é grato hoje',
            'order' => 1,
            'icon' => '🙏',
            'color' => '#F59E0B',
        ]);

        // 6. Desafio de Produtividade (14 dias)
        $productivityChallenge = Challenge::create([
            'title' => '14 Dias de Foco Total',
            'description' => 'Maximize sua produtividade com técnicas de foco e organização por 2 semanas.',
            'duration_days' => 14,
            'is_template' => true,
            'is_public' => true,
            'category' => 'productivity',
            'difficulty' => 'intermediate',
            'image_url' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=800',
            'tags' => ['produtividade', 'foco', 'organização', 'trabalho'],
        ]);

        ChallengeTask::create([
            'challenge_id' => $productivityChallenge->id,
            'name' => 'Pomodoro de 25 minutos',
            'hashtag' => 'pomodoro',
            'description' => 'Complete pelo menos um bloco de 25 minutos de trabalho focado',
            'order' => 1,
            'icon' => '⏰',
            'color' => '#DC2626',
        ]);

        ChallengeTask::create([
            'challenge_id' => $productivityChallenge->id,
            'name' => 'Organizar workspace',
            'hashtag' => 'organizacao',
            'description' => 'Organize sua mesa/ambiente de trabalho',
            'order' => 2,
            'icon' => '🗂️',
            'color' => '#7C3AED',
            'is_required' => false,
        ]);

        // Update participant counts to simulate popularity
        $readingChallenge->update(['participant_count' => 847]);
        $fitnessChallenge->update(['participant_count' => 623]);
        $meditationChallenge->update(['participant_count' => 412]);
        $detoxChallenge->update(['participant_count' => 289]);
        $gratitudeChallenge->update(['participant_count' => 334]);
        $productivityChallenge->update(['participant_count' => 456]);
    }
}
