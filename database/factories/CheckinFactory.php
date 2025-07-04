<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Checkin;
use App\Models\UserChallenge;
use App\Models\ChallengeTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Checkin>
 */
final class CheckinFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_challenge_id' => UserChallenge::factory(),
            'task_id' => ChallengeTask::factory(),
            'image_path' => fake()->optional(0.8)->filePath(),
            'message' => fake()->optional(0.5)->sentence(),
            'source' => fake()->randomElement(['web', 'whatsapp']),
            'status' => 'approved',
            'challenge_day' => fake()->numberBetween(1, 30),
            'checked_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function withImage(): self
    {
        return $this->state([
            'image_path' => 'checkins/' . fake()->uuid() . '.jpg',
            'image_url' => fake()->imageUrl(800, 600),
        ]);
    }

    public function fromWhatsapp(): self
    {
        return $this->state(['source' => 'whatsapp']);
    }

    public function fromWeb(): self
    {
        return $this->state(['source' => 'web']);
    }

    public function pending(): self
    {
        return $this->state(['status' => 'pending']);
    }

    public function rejected(): self
    {
        return $this->state(['status' => 'rejected']);
    }

    public function withAiAnalysis(): self
    {
        return $this->state([
            'ai_analysis' => [
                'valid' => fake()->boolean(85),
                'confidence' => fake()->randomFloat(2, 0.5, 1.0),
                'detected_objects' => [
                    ['name' => 'book', 'confidence' => 0.95],
                    ['name' => 'person', 'confidence' => 0.87],
                ],
                'analysis_time' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
            'confidence_score' => fake()->randomFloat(2, 0.5, 1.0),
        ]);
    }
}
