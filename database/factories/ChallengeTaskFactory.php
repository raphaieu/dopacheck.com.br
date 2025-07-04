<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\ChallengeTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChallengeTask>
 */
final class ChallengeTaskFactory extends Factory
{
    public function definition(): array
    {
        $taskExamples = [
            ['name' => 'Ler 30 minutos', 'hashtag' => 'leitura', 'icon' => '📚', 'color' => '#8B5CF6'],
            ['name' => 'Exercitar 30 minutos', 'hashtag' => 'treino', 'icon' => '🏃‍♂️', 'color' => '#F59E0B'],
            ['name' => 'Meditar 10 minutos', 'hashtag' => 'meditacao', 'icon' => '🧘‍♀️', 'color' => '#10B981'],
            ['name' => 'Beber 2L de água', 'hashtag' => 'agua', 'icon' => '💧', 'color' => '#06B6D4'],
            ['name' => 'Dormir 8 horas', 'hashtag' => 'sono', 'icon' => '😴', 'color' => '#6366F1'],
        ];

        $task = fake()->randomElement($taskExamples);

        return [
            'challenge_id' => Challenge::factory(),
            'name' => $task['name'],
            'hashtag' => $task['hashtag'] . fake()->randomNumber(3),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 5),
            'is_required' => fake()->boolean(80),
            'icon' => $task['icon'],
            'color' => $task['color'],
            'validation_rules' => [
                'required_objects' => fake()->randomElements(['book', 'exercise', 'water', 'meditation'], rand(1, 2)),
            ],
        ];
    }

    public function required(): self
    {
        return $this->state(['is_required' => true]);
    }

    public function optional(): self
    {
        return $this->state(['is_required' => false]);
    }
}
