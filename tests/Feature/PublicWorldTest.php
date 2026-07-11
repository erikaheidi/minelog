<?php

use App\Models\Waypoint;
use App\Models\World;

test('a guest can view a public world page', function () {
    $world = World::factory()->public()->create([
        'name' => 'Skyblock Realm',
        'description' => 'A floating island world',
        'seed' => '987654321',
    ]);
    Waypoint::factory()->for($world)->create(['name' => 'Starter island']);

    $this->get(route('worlds.public', $world))
        ->assertOk()
        ->assertSee('Skyblock Realm')
        ->assertSee('A floating island world')
        ->assertSee('987654321')
        ->assertSee('Starter island');
});

test('a private world returns 404 to guests', function () {
    $world = World::factory()->create(['is_public' => false]);

    $this->get(route('worlds.public', $world))->assertNotFound();
});

test('the landing lists public worlds and excludes private ones', function () {
    World::factory()->public()->create(['name' => 'Public Explorer']);
    World::factory()->create(['name' => 'Secret Base', 'is_public' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Public Explorer')
        ->assertDontSee('Secret Base');
});

test('the landing search filters public worlds by name', function () {
    World::factory()->public()->create(['name' => 'Desert Kingdom']);
    World::factory()->public()->create(['name' => 'Frozen Tundra']);

    $this->get(route('home', ['q' => 'Desert']))
        ->assertOk()
        ->assertSee('Desert Kingdom')
        ->assertDontSee('Frozen Tundra');
});

test('the public world map exposes only waypoints with coordinates', function () {
    $world = World::factory()->public()->create();
    Waypoint::factory()->for($world)->create(['name' => 'Has coords']);
    Waypoint::factory()->for($world)->create(['x' => null, 'z' => null]);

    expect($world->mapMarkers())->toHaveCount(1);
});
