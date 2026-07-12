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
    // The default disk is intentionally the private 'local' disk to prove screenshots
    // follow the dedicated 'screenshots' disk config, not the framework default.
    config(['filesystems.default' => 'local', 'filesystems.screenshots' => 'public']);
    Storage::fake('public');
});

test('a user can attach multiple screenshots to a waypoint one at a time', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshot', UploadedFile::fake()->create('a.png', 100, 'image/png'))
        ->assertHasNoErrors()
        ->assertSet('newScreenshot', null)
        ->set('newScreenshot', UploadedFile::fake()->create('b.png', 100, 'image/png'))
        ->assertHasNoErrors()
        ->assertSet('newScreenshot', null);

    expect($wp->screenshots()->count())->toBe(2);

    $wp->screenshots->each(function (WaypointScreenshot $shot) {
        expect($shot->disk)->toBe('public');
        Storage::disk('public')->assertExists($shot->path);
    });
});

test('a waypoint accepts at most six screenshots', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();
    WaypointScreenshot::factory()->for($wp)->count(6)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshot', UploadedFile::fake()->create('extra.png', 100, 'image/png'))
        ->assertHasNoErrors()
        ->assertSet('newScreenshot', null);

    expect($wp->screenshots()->count())->toBe(6);
});

test('deleting a single screenshot removes the row and the file', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create();
    $wp = Waypoint::factory()->for($world)->create();

    $this->actingAs($user);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->set('newScreenshot', UploadedFile::fake()->create('a.png', 100, 'image/png'))
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
        ->set('newScreenshot', UploadedFile::fake()->create('a.png', 100, 'image/png'))
        ->assertHasNoErrors()
        ->set('newScreenshot', UploadedFile::fake()->create('b.png', 100, 'image/png'))
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
        ->set('newScreenshot', UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf'))
        ->assertHasErrors(['newScreenshot']);

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

test('the owner can set and clear a screenshot as the world cover', function () {
    $owner = User::factory()->create();
    $world = World::factory()->for($owner)->create();
    $wp = Waypoint::factory()->for($world)->create();
    $shot = WaypointScreenshot::factory()->for($wp)->create();

    $this->actingAs($owner);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('setCover', $shot->id)
        ->assertHasNoErrors();

    expect($world->fresh()->cover_screenshot_id)->toBe($shot->id);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('clearCover')
        ->assertHasNoErrors();

    expect($world->fresh()->cover_screenshot_id)->toBeNull();
});

test('setting a cover is scoped to the mounted world', function () {
    $owner = User::factory()->create();
    $world = World::factory()->for($owner)->create();

    $otherWorld = World::factory()->create();
    $otherWp = Waypoint::factory()->for($otherWorld)->create();
    $foreignShot = WaypointScreenshot::factory()->for($otherWp)->create();

    $this->actingAs($owner);

    expect(fn () => Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('setCover', $foreignShot->id))
        ->toThrow(ModelNotFoundException::class);

    expect($world->fresh()->cover_screenshot_id)->toBeNull();
});

test('deleting the cover screenshot clears the world cover', function () {
    $owner = User::factory()->create();
    $world = World::factory()->for($owner)->create();
    $wp = Waypoint::factory()->for($world)->create();
    $shot = WaypointScreenshot::factory()->for($wp)->create(['disk' => 'public']);
    $world->update(['cover_screenshot_id' => $shot->id]);

    $this->actingAs($owner);

    Livewire::test('pages::worlds.show', ['world' => $world])
        ->call('startEdit', $wp->id)
        ->call('deleteScreenshot', $shot->id)
        ->assertHasNoErrors();

    expect($world->fresh()->cover_screenshot_id)->toBeNull();
});
