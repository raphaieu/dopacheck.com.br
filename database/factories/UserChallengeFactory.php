<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Challenge;
use App\Models\UserChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserChallenge>
 */
final class UserChallengeFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $currentDay = fake()->numberBetween(1, 21);

        return [
            'user_id' => User::factory(),
            'challenge_id' => Challenge::factory(),
            'status' => 'active',
            'started_at' => $startedAt,
            'current_day' => $currentDay,
            'total_checkins' => fake()->numberBetween(0, $currentDay * 2),
            'streak_days' => fake()->numberBetween(0, $currentDay),
            'best_streak' => fake()->numberBetween(0, $currentDay),
            'completion_rate' => fake()->randomFloat(2, 0, 100),
            'stats' => [
                'total_images' => fake()->numberBetween(0, 50),
                'whatsapp_checkins' => fake()->numberBetween(0, 30),
                'web_checkins' => fake()->numberBetween(0, 20),
            ],
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    public function completed(): self
    {
        return $this->state([
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'completion_rate' => fake()->randomFloat(2, 80, 100),
        ]);
    }

    public function paused(): self
    {
        return $this->state([
            'status' => 'paused',
            'paused_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    public function abandoned(): self
    {
        return $this->state([
            'status' => 'abandoned',
            'completion_rate' => fake()->randomFloat(2, 0, 50),
        ]);
    }
}
