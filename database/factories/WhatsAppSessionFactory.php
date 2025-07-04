<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\WhatsAppSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsAppSession>
 */
final class WhatsAppSessionFactory extends Factory
{
    public function definition(): array
    {
        $phoneNumber = '55' . fake()->areaCode() . fake()->cellphone(false, false);
        $botNumber = '55' . fake()->areaCode() . '9' . fake()->randomNumber(8);

        return [
            'user_id' => User::factory(),
            'phone_number' => $phoneNumber,
            'bot_number' => $botNumber,
            'session_id' => fake()->uuid(),
            'instance_name' => 'dopacheck_' . fake()->randomNumber(4),
            'is_active' => fake()->boolean(70),
            'last_activity' => fake()->dateTimeBetween('-24 hours', 'now'),
            'connected_at' => fake()->dateTimeBetween('-7 days', '-1 hour'),
            'message_count' => fake()->numberBetween(0, 200),
            'checkin_count' => fake()->numberBetween(0, 50),
            'metadata' => [
                'device' => fake()->randomElement(['android', 'ios', 'web']),
                'version' => fake()->semver(),
                'last_seen' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    public function active(): self
    {
        return $this->state([
            'is_active' => true,
            'connected_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'disconnected_at' => null,
        ]);
    }

    public function inactive(): self
    {
        return $this->state([
            'is_active' => false,
            'disconnected_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function withHighActivity(): self
    {
        return $this->state([
            'message_count' => fake()->numberBetween(100, 500),
            'checkin_count' => fake()->numberBetween(20, 100),
            'last_activity' => fake()->dateTimeBetween('-2 hours', 'now'),
        ]);
    }
}
