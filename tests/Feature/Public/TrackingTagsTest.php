<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    // The tags are suppressed in local and testing so dev pageviews never reach
    // the studio's real Meta and GA properties. Every assertion about what they
    // render therefore has to run as a real environment.
    app()->detectEnvironment(fn () => 'production');
});

it('renders the Meta Pixel with the configured id', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain("fbq('init', '".SiteSetting::metaPixelId()."')");
    expect($html)->toContain('connect.facebook.net/en_US/fbevents.js');
});

it('holds both tags behind the cookie banner', function () {
    // The site runs a consent dialog and publishes a cookie policy. A tag that
    // fires before the visitor answers makes the banner decoration.
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain("gtag('consent', 'default'");
    expect($html)->toContain("analytics_storage: 'denied'");
    // Revoke must be printed before track(), or the PageView it gates has gone.
    $revoke = strpos($html, "fbq('consent', tlcPixelConsent)");
    $track = strpos($html, "fbq('track', 'PageView')");
    expect($revoke)->toBeLessThan($track);
});

it('omits the no-script pixel, which cannot read a consent choice', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('facebook.com/tr?id=');
});

it('loads nothing in local and testing', function () {
    app()->detectEnvironment(fn () => 'local');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('fbevents.js');
    expect($html)->not->toContain('googletagmanager.com');
});

it('renders no pixel when the id is blank', function () {
    SiteSetting::set('meta_pixel_id', '');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('fbevents.js');
});

it('ignores an id that is not in the expected format', function () {
    // The value is interpolated into an inline <script>. A pasted apostrophe
    // would break every script on the page; a deliberate one is stored XSS with
    // an admin as the author. Pattern-checked, never printed as typed.
    foreach (["123'); alert(1); //", '<script>alert(1)</script>', 'not-an-id', ''] as $bad) {
        SiteSetting::set('meta_pixel_id', $bad);

        expect(SiteSetting::metaPixelId())->toBeNull($bad);

        $html = $this->get('/')->assertOk()->getContent();
        expect($html)->not->toContain('fbevents.js');
        expect($html)->not->toContain('alert(1)');
    }
});

it('rejects a Universal Analytics id, which collects nothing', function () {
    // UA properties stopped collecting in 2024; firing at one looks like working
    // analytics while recording nothing.
    SiteSetting::set('ga_measurement_id', 'UA-12345678-1');

    expect(SiteSetting::gaMeasurementId())->toBeNull();
});

it('prefers the admin GA id over the env fallback', function () {
    SiteSetting::set('ga_measurement_id', 'G-ADMINSET99');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('G-ADMINSET99');
    expect($html)->not->toContain(config('services.google_analytics.id'));
});

it('falls back to the env GA id when the setting is blank', function () {
    SiteSetting::set('ga_measurement_id', '');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain(config('services.google_analytics.id'));
});

it('allowlists both tag hosts in the CSP', function () {
    // A tag whose hosts are missing from the policy is a tag that breaks the day
    // the header stops being Report-Only.
    $csp = $this->get('/')->assertOk()->headers->get('Content-Security-Policy-Report-Only');

    expect($csp)->toContain('https://www.googletagmanager.com');
    expect($csp)->toContain('https://connect.facebook.net');
    expect($csp)->toContain('https://www.google-analytics.com');
    expect($csp)->toContain('https://www.facebook.com');
});
