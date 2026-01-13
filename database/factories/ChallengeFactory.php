<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Team;
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

        $visibility = fake()->boolean(80) ? Challenge::VISIBILITY_GLOBAL : Challenge::VISIBILITY_PRIVATE;
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'duration_days' => fake()->randomElement([7, 14, 21, 30]),
            'is_template' => false,
            // Legado: mantemos consistente com visibility
            'is_public' => $visibility !== Challenge::VISIBILITY_PRIVATE,
            'visibility' => $visibility,
            'is_featured' => fake()->boolean(20),
            'created_by' => User::factory(),
            'team_id' => null,
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
            'visibility' => Challenge::VISIBILITY_GLOBAL,
            'created_by' => null,
            'team_id' => null,
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
            'visibility' => Challenge::VISIBILITY_GLOBAL,
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
            'visibility' => Challenge::VISIBILITY_PRIVATE,
            'team_id' => null,
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the challenge is shared with a team.
     */
    public function team(): self
    {
        return $this->state([
            'is_public' => true,
            'visibility' => Challenge::VISIBILITY_TEAM,
            'team_id' => Team::factory()->state(['personal_team' => false]),
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
