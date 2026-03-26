<?php

namespace Database\Factories;

use App\Models\Water;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Water>
 */
class WaterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'wr_1' => fake()->randomElement(['0', '50', '100', 'В разработке']),
            'wr_2' => fake()->randomElement(['0', '50', '100', 'В разработке']),
            'wr_3' => fake()->numberBetween(0, 100),
            'wr_4' => fake()->numberBetween(0, 100),
            'wr_5' => fake()->sentence(3),
        ];
    }
}
