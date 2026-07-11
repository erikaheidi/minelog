<?php

use App\Models\User;
use App\Models\Waypoint;
use App\Models\WaypointScreenshot;
use App\Models\World;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    config(['filesystems.default' => 'public']);
    Storage::fake('public');
});

test('a user can attach multiple screenshots to a waypoint when editing it', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshots', [
            UploadedFile::fake()->create('a.png', 100, 'image/png'),
            UploadedFile::fake()->create('b.png', 100, 'image/png'),
        ])
        ->call('saveEdit')
        ->assertHasNoErrors()
        ->assertSet('newScreenshots', []);

    expect($wp->screenshots()->count())->toBe(2);

    $wp->screenshots->each(function (WaypointScreenshot $shot) {
        expect($shot->disk)->toBe('public');
        Storage::disk('public')->assertExists($shot->path);
    });
});

test('deleting a single screenshot removes the row and the file', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshots', [UploadedFile::fake()->create('a.png', 100, 'image/png')])
        ->call('saveEdit')
        ->assertHasNoErrors();

    $shot = $wp->screenshots()->firstOrFail();

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->call('deleteScreenshot', $shot->id)
        ->assertHasNoErrors();

    expect(WaypointScreenshot::whereKey($shot->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($shot->path);
});

test('deleting a waypoint removes all of its screenshot files', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshots', [
            UploadedFile::fake()->create('a.png', 100, 'image/png'),
            UploadedFile::fake()->create('b.png', 100, 'image/png'),
        ])
        ->call('saveEdit')
        ->assertHasNoErrors();

    $paths = $wp->screenshots->pluck('path');

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('delete', $wp->id)
        ->assertHasNoErrors();

    expect(WaypointScreenshot::where('waypoint_id', $wp->id)->exists())->toBeFalse();
    $paths->each(fn (string $path) => Storage::disk('public')->assertMissing($path));
});

test('screenshot uploads are validated as images under the size limit', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshots', [UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf')])
        ->call('saveEdit')
        ->assertHasErrors(['newScreenshots.0']);

    expect($wp->screenshots()->count())->toBe(0);
});

test('a non-owner cannot open the world workspace to manage screenshots', function () {
    $world = World::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('worlds.show', $world))->assertForbidden();
});

test('deleting a screenshot is scoped to the mounted world', function () {
    $owner = User::factory()->create();
    $world = World::factory()->for($owner)->create();

    $otherWorld = World::factory()->create();
    $otherWp = Waypoint::factory()->for($otherWorld)->create();
    $shot = WaypointScreenshot::factory()->for($otherWp)->create();

    $this->actingAs($owner);

    expect(fn () => Livewire::test('pages::worlds.show', ['world' => $world])
        ->set('editingId', $otherWp->id)
        ->call('deleteScreenshot', $shot->id))
        ->toThrow(ModelNotFoundException::class);

    expect(WaypointScreenshot::whereKey($shot->id)->exists())->toBeTrue();
});
