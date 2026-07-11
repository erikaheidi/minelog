<?php

namespace Database\Factories;

use App\Models\Waypoint;
use App\Models\WaypointScreenshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaypointScreenshot>
 */
class WaypointScreenshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'waypoint_id' => Waypoint::factory(),
            'disk' => 'public',
            'path' => 'screenshots/'.$this->faker->uuid().'.png',
        ];
    }
}
