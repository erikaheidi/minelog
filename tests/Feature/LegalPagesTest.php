<?php

test('privacy policy page renders', function () {
    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('Privacy Policy');
});

test('terms of service page renders', function () {
    $this->get(route('terms-of-service'))
        ->assertOk()
        ->assertSee('Terms of Service');
});

test('legal pages are linked in the public footer', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('privacy-policy'))
        ->assertSee(route('terms-of-service'));
});
