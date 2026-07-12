<?php

test('consent banner and analytics are absent when no measurement id is configured', function () {
    config(['services.google_analytics.id' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('id="cookie-consent"', false)
        ->assertDontSee('googletagmanager.com');
});

test('consent banner is shown when a measurement id is configured', function () {
    config(['services.google_analytics.id' => 'G-TEST12345']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="cookie-consent"', false)
        ->assertSee('minelog_analytics_consent')
        ->assertSee('G-TEST12345');
});

test('analytics loader is gated behind consent, not loaded on page render', function () {
    config(['services.google_analytics.id' => 'G-TEST12345']);

    $response = $this->get(route('home'))->assertOk();

    // The gtag tag must be injected by JS only after opt-in, never as a static
    // <script src> in the delivered HTML.
    expect($response->getContent())
        ->not->toContain('<script async src="https://www.googletagmanager.com');
});
