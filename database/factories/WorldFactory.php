<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\World;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<World>
 */
class WorldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'user_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'seed' => fake()->optional()->numerify('##########'),
            'is_public' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(['is_public' => true]);
    }
}
