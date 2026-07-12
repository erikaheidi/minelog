<?php

use App\Models\Waypoint;
use App\Models\WaypointScreenshot;
use App\Models\World;

test('the home page renders the default social card meta', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('og:image', false)
        ->assertSee('og-image.png', false)
        ->assertSee('twitter:card', false)
        ->assertSee('summary_large_image', false)
        ->assertSee('<meta name="description"', false)
        ->assertSee('rel="canonical"', false);
});

test('a public world page uses its chosen cover screenshot as the social image', function () {
    $world = World::factory()->public()->create(['name' => 'Ocean Outpost']);
    $waypoint = Waypoint::factory()->for($world)->create();
    $shot = WaypointScreenshot::factory()->for($waypoint)->create(['disk' => 'public']);
    $world->update(['cover_screenshot_id' => $shot->id]);

    $this->get(route('worlds.public', $world))
        ->assertOk()
        ->assertSee('property="og:image"', false)
        ->assertSee($shot->url(), false)
        ->assertSee('property="og:title"', false)
        ->assertSee(route('worlds.public', $world), false) // canonical / og:url
        ->assertSee('content="article"', false);
});

test('a public world without a cover falls back to the default social image', function () {
    $world = World::factory()->public()->create(['name' => 'Bare World']);
    $waypoint = Waypoint::factory()->for($world)->create();
    // A screenshot exists but is not chosen as the cover.
    WaypointScreenshot::factory()->for($waypoint)->create(['disk' => 'public']);

    $this->get(route('worlds.public', $world))
        ->assertOk()
        ->assertSee('og-image.png', false);
});

test('the world social description falls back to a generated summary', function () {
    $world = World::factory()->public()->create([
        'name' => 'Ancient Ruins',
        'description' => null,
    ]);
    Waypoint::factory()->for($world)->count(2)->create();

    $this->get(route('worlds.public', $world))
        ->assertOk()
        ->assertSee('Ancient Ruins', false)
        ->assertSee('2 waypoints', false);
});
