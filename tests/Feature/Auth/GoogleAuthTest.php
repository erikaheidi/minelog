<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeGoogleUser(array $overrides = []): void
{
    Socialite::fake('google', (new SocialiteUser)->map(array_merge([
        'id' => 'google-123',
        'name' => 'Erika Heidi',
        'email' => 'erika@example.com',
        'avatar' => 'https://example.com/avatar.jpg',
    ], $overrides)));
}

test('user is redirected to google', function () {
    Socialite::fake('google');

    $this->get(route('google.redirect'))->assertRedirect();
});

test('a new user is created and logged in via google', function () {
    fakeGoogleUser();

    $response = $this->get(route('google.callback'));

    $response->assertRedirect(config('fortify.home'));
    $this->assertAuthenticated();

    $user = User::where('email', 'erika@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->google_id)->toBe('google-123')
        ->and($user->avatar)->toBe('https://example.com/avatar.jpg')
        ->and($user->password)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();
});

test('an existing google user is logged in without creating a duplicate', function () {
    $existing = User::factory()->create([
        'email' => 'erika@example.com',
        'google_id' => 'google-123',
    ]);

    fakeGoogleUser();

    $this->get(route('google.callback'))->assertRedirect(config('fortify.home'));

    $this->assertAuthenticatedAs($existing);
    expect(User::count())->toBe(1);
});

test('a password user with the same email is linked to google', function () {
    $existing = User::factory()->create([
        'email' => 'erika@example.com',
        'google_id' => null,
    ]);

    fakeGoogleUser();

    $this->get(route('google.callback'))->assertRedirect(config('fortify.home'));

    $this->assertAuthenticatedAs($existing);
    expect(User::count())->toBe(1)
        ->and($existing->fresh()->google_id)->toBe('google-123');
});

test('login and register pages show the google button', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('google.redirect'));
    $this->get(route('register'))->assertOk()->assertSee(route('google.redirect'));
});
