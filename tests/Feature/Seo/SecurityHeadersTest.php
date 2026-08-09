<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('sends the baseline security headers on a public page', function () {
    $this->get('/about')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

/**
 * Report-Only, not enforcing. The policy is a hypothesis until real violation
 * reports say otherwise, and an enforcing CSP that is wrong blanks the page
 * rather than degrading — this site carries inline JSON-LD and inline style
 * attributes written by Blade.
 */
it('publishes the CSP in report-only mode and does not enforce it', function () {
    $response = $this->get('/about')->assertOk();

    $response->assertHeader('Content-Security-Policy-Report-Only');
    expect($response->headers->has('Content-Security-Policy'))->toBeFalse();
});

/**
 * HSTS over plaintext is meaningless — the browser ignores it — and in local dev
 * over http it would be a lie. It must appear only on a secure request.
 */
it('omits HSTS on a plaintext request', function () {
    $response = $this->get('/about');

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('sends a preload-ready HSTS header on a secure request', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    $response = $this->get('https://thelastclicks.com/about', ['Host' => 'thelastclicks.com']);

    $hsts = $response->headers->get('Strict-Transport-Security');

    expect($hsts)->toContain('max-age=31536000')
        ->and($hsts)->toContain('includeSubDomains')
        ->and($hsts)->toContain('preload');
});

it('serves the IndexNow key file only for the configured key', function () {
    $key = str_repeat('a1b2c3d4', 4);
    config(['services.indexnow.key' => $key]);

    $this->get("/{$key}.txt")
        ->assertOk()
        ->assertSee($key, false);

    $this->get('/'.str_repeat('f0e1d2c3', 4).'.txt')->assertNotFound();
});

it('404s the IndexNow key route when no key is configured', function () {
    config(['services.indexnow.key' => '']);

    $this->get('/'.str_repeat('a1b2c3d4', 4).'.txt')->assertNotFound();
});
