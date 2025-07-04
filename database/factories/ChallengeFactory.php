<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
final class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $categories = ['fitness', 'mindfulness', 'learning', 'productivity', 'lifestyle', 'health'];
        $difficulties = ['beginner', 'intermediate', 'advanced'];
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'duration_days' => fake()->randomElement([7, 14, 21, 30]),
            'is_template' => false,
            'is_public' => fake()->boolean(80),
            'is_featured' => fake()->boolean(20),
            'created_by' => User::factory(),
            'participant_count' => fake()->numberBetween(0, 500),
            'category' => fake()->randomElement($categories),
            'difficulty' => fake()->randomElement($difficulties),
            'image_url' => fake()->imageUrl(800, 600, 'abstract'),
            'tags' => fake()->randomElements(['motivação', 'hábitos', 'saúde', 'foco', 'bem-estar'], rand(2, 4)),
        ];
    }

    /**
     * Indicate that the challenge is a template.
     */
    public function template(): self
    {
        return $this->state([
            'is_template' => true,
            'is_public' => true,
            'created_by' => null,
        ]);
    }

    /**
     * Indicate that the challenge is featured.
     */
    public function featured(): self
    {
        return $this->state([
            'is_featured' => true,
            'is_public' => true,
            'participant_count' => fake()->numberBetween(100, 1000),
        ]);
    }

    /**
     * Indicate that the challenge is private.
     */
    public function private(): self
    {
        return $this->state([
            'is_public' => false,
            'is_featured' => false,
        ]);
    }

    /**
     * Set specific category.
     */
    public function category(string $category): self
    {
        return $this->state([
            'category' => $category,
        ]);
    }

    /**
     * Set specific difficulty.
     */
    public function difficulty(string $difficulty): self
    {
        return $this->state([
            'difficulty' => $difficulty,
        ]);
    }
}
