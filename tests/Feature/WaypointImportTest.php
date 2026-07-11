<?php

use App\Models\User;
use App\Models\Waypoint;
use App\Models\World;
use App\Services\WaypointImporter;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

/**
 * @param  array<int, array<string, mixed>>  $waypoints
 */
function exportJson(array $waypoints): string
{
    return json_encode($waypoints);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function sampleWaypoint(array $overrides = []): array
{
    return array_merge([
        'id' => 'abc-123',
        'label' => 'Diamond cave',
        'x' => 128,
        'y' => -12,
        'z' => -340,
        'dimension' => 'overworld',
        'capturedAt' => '2026-07-11T14:03:00.000Z',
        'player' => 'Erika',
    ], $overrides);
}

test('importing valid json creates confirmed waypoints under the world', function () {
    $world = World::factory()->create();

    $result = app(WaypointImporter::class)->importForWorld($world, exportJson([sampleWaypoint()]));

    expect($result)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 0]);

    $wp = Waypoint::firstOrFail();
    expect($wp->world_id)->toBe($world->id)
        ->and($wp->name)->toBe('Diamond cave')
        ->and([$wp->x, $wp->y, $wp->z])->toBe([128, -12, -340])
        ->and($wp->status)->toBe('confirmed')
        ->and($wp->external_id)->toBe('abc-123');
});

test('re-importing the same export into a world updates rather than duplicates', function () {
    $world = World::factory()->create();
    $importer = app(WaypointImporter::class);

    $importer->importForWorld($world, exportJson([sampleWaypoint()]));
    $result = $importer->importForWorld($world, exportJson([sampleWaypoint(['label' => 'Renamed cave', 'x' => 200])]));

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0]);
    expect(Waypoint::count())->toBe(1);
    expect(Waypoint::firstOrFail()->name)->toBe('Renamed cave');
});

test('the same external id can live in two different worlds', function () {
    $importer = app(WaypointImporter::class);
    $a = World::factory()->create();
    $b = World::factory()->create();

    $importer->importForWorld($a, exportJson([sampleWaypoint()]));
    $importer->importForWorld($b, exportJson([sampleWaypoint()]));

    expect(Waypoint::count())->toBe(2);
});

test('dimension aliases map correctly and unknown falls back to overworld', function () {
    $world = World::factory()->create();

    app(WaypointImporter::class)->importForWorld($world, exportJson([
        sampleWaypoint(['id' => 'a', 'dimension' => 'minecraft:the_end']),
        sampleWaypoint(['id' => 'b', 'dimension' => 'nether']),
        sampleWaypoint(['id' => 'c', 'dimension' => 'moon']),
    ]));

    expect(Waypoint::where('external_id', 'a')->value('dimension'))->toBe('end')
        ->and(Waypoint::where('external_id', 'b')->value('dimension'))->toBe('nether')
        ->and(Waypoint::where('external_id', 'c')->value('dimension'))->toBe('overworld');
});

test('entries without an id or coordinates are skipped', function () {
    $world = World::factory()->create();

    $result = app(WaypointImporter::class)->importForWorld($world, exportJson([
        sampleWaypoint(['id' => 'ok']),
        ['label' => 'no id', 'x' => 1, 'y' => 2, 'z' => 3],
        sampleWaypoint(['id' => 'nocoords', 'x' => null, 'y' => null, 'z' => null]),
    ]));

    expect($result)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 2]);
    expect(Waypoint::count())->toBe(1);
});

test('malformed json throws a validation error and imports nothing', function () {
    $world = World::factory()->create();

    expect(fn () => app(WaypointImporter::class)->importForWorld($world, 'not json'))
        ->toThrow(ValidationException::class);

    expect(Waypoint::count())->toBe(0);
});

test('importing through the world workspace is scoped to that world', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->set('payload', exportJson([sampleWaypoint()]))
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('payload', '');

    expect($world->waypoints()->count())->toBe(1);
});

test('a non-owner cannot open the world workspace', function () {
    $world = World::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('worlds.show', $world))->assertForbidden();
});

test('a user can edit and confirm a waypoint in their world', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->draft()->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('name', 'Diamond cave')
        ->set('tags', 'mining, diamonds')
        ->call('saveEdit')
        ->assertHasNoErrors();

    $wp->refresh();
    expect($wp->name)->toBe('Diamond cave')
        ->and($wp->status)->toBe('confirmed')
        ->and($wp->tags)->toBe(['mining', 'diamonds']);
});

test('a user can delete a waypoint in their world', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('delete', $wp->id)
        ->assertHasNoErrors();

    expect(Waypoint::whereKey($wp->id)->exists())->toBeFalse();
});
