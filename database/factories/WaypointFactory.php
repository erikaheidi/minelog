<?php

namespace Database\Factories;

use App\Models\Waypoint;
use App\Models\World;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Waypoint>
 */
class WaypointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'world_id' => World::factory(),
            'external_id' => $this->faker->uuid(),
            'name' => $this->faker->words(2, true),
            'x' => $this->faker->numberBetween(-2000, 2000),
            'y' => $this->faker->numberBetween(-64, 320),
            'z' => $this->faker->numberBetween(-2000, 2000),
            'dimension' => $this->faker->randomElement(Waypoint::DIMENSIONS),
            'note' => $this->faker->optional()->sentence(),
            'tags' => [],
            'captured_at' => now(),
            'status' => 'confirmed',
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'name' => null]);
    }
}
