<?php

use App\Models\User;
use App\Models\World;
use Livewire\Livewire;

test('a slug is generated from the name when creating a world', function () {
    $world = World::factory()->create(['name' => 'Survival Realm', 'slug' => null]);

    expect($world->slug)->toBe('survival-realm');
});

test('slugs are made unique on collision', function () {
    World::factory()->create(['name' => 'My World', 'slug' => null]);
    $second = World::factory()->create(['name' => 'My World', 'slug' => null]);

    expect($second->slug)->toBe('my-world-2');
});

test('creating a world through the index attaches it to the acting user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::worlds.index')
        ->set('name', 'Survival Realm')
        ->set('description', 'Our main base')
        ->set('seed', '123456789')
        ->set('is_public', true)
        ->call('createWorld')
        ->assertHasNoErrors();

    $world = World::firstOrFail();
    expect($world->user_id)->toBe($user->id)
        ->and($world->name)->toBe('Survival Realm')
        ->and($world->is_public)->toBeTrue()
        ->and($world->seed)->toBe('123456789');
});

test('the worlds index only lists the acting users worlds', function () {
    $me = User::factory()->create();
    World::factory()->for($me)->create(['name' => 'Mine']);
    World::factory()->create(['name' => 'Theirs']);

    $this->actingAs($me);

    Livewire::test('pages::worlds.index')
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

test('a user can delete their own world', function () {
    $me = User::factory()->create();
    $world = World::factory()->for($me)->create();

    $this->actingAs($me);

    Livewire::test('pages::worlds.index')
        ->call('deleteWorld', $world->id)
        ->assertHasNoErrors();

    expect(World::whereKey($world->id)->exists())->toBeFalse();
});

test('a user cannot delete someone elses world', function () {
    $theirs = World::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test('pages::worlds.index')
        ->call('deleteWorld', $theirs->id)
        ->assertForbidden();

    expect(World::whereKey($theirs->id)->exists())->toBeTrue();
});

test('guests are redirected from the worlds area to login', function () {
    $this->get(route('worlds.index'))->assertRedirect(route('login'));
});

test('the owner can load the worlds index and a world workspace', function () {
    $user = User::factory()->create();
    $world = World::factory()->for($user)->create(['name' => 'Survival Realm']);

    $this->actingAs($user);

    $this->get(route('worlds.index'))->assertOk();
    $this->get(route('worlds.show', $world))->assertOk()->assertSee('Survival Realm');
    $this->get(route('worlds.map', $world))->assertOk();
});

test('a non-owner cannot open the world map', function () {
    $world = World::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('worlds.map', $world))->assertForbidden();
});
