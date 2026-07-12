<?php

use App\Models\World;

test('the sitemap lists public worlds and static pages as xml', function () {
    $public = World::factory()->public()->create(['name' => 'Charted World']);
    $private = World::factory()->create(['name' => 'Hidden World', 'is_public' => false]);

    $response = $this->get('/sitemap.xml')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('application/xml');

    $response->assertSee('<urlset', false)
        ->assertSee(route('home'), false)
        ->assertSee(route('how-it-works'), false)
        ->assertSee(route('worlds.public', $public), false)
        ->assertDontSee(route('worlds.public', $private), false);
});

test('robots.txt is served dynamically and points at the sitemap', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('User-agent: *')
        ->assertSee('Sitemap: '.url('/sitemap.xml'));
});
