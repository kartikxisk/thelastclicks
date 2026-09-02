<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The studio's own tracking IDs and social profiles.
 *
 * Both tags stay behind the cookie banner and neither loads locally or under
 * test, so seeding real IDs here does not start measuring development traffic.
 */
it('seeds the studio meta pixel', function () {
    $this->seed();

    expect(SiteSetting::get('meta_pixel_id'))->toBe('1412855920724528');
});

it('seeds the ga4 measurement id', function () {
    $this->seed();

    expect(SiteSetting::get('ga_measurement_id'))->toBe('G-LHT5WBY4MR');
});

it('replaces the superseded pixel rather than leaving it in place', function () {
    // setIfMissing alone would strand the old ID on every environment that had
    // already been seeded — which is all of them — so the retired value is
    // recognised and moved on.
    SiteSetting::set('meta_pixel_id', '2292935938118631');

    $this->seed();

    expect(SiteSetting::get('meta_pixel_id'))->toBe('1412855920724528');
});

it('leaves a pixel someone deliberately set alone', function () {
    // An environment pointed at a client's own pixel, or a test one, must not be
    // dragged back to the studio's by the next deploy.
    SiteSetting::set('meta_pixel_id', '9999999999999999');

    $this->seed();

    expect(SiteSetting::get('meta_pixel_id'))->toBe('9999999999999999');
});

it('leaves a ga property someone deliberately set alone', function () {
    SiteSetting::set('ga_measurement_id', 'G-CLIENTOWNED');

    $this->seed();

    expect(SiteSetting::get('ga_measurement_id'))->toBe('G-CLIENTOWNED');
});

it('seeds only social profiles that were verified to exist', function () {
    $this->seed();

    $socials = SiteSetting::get('socials');

    expect($socials)->toHaveKeys(['instagram', 'youtube', 'linkedin', 'behance', 'pinterest'])
        ->and($socials['linkedin'])->toBe('https://www.linkedin.com/company/thelastclicks')
        ->and($socials['behance'])->toBe('https://www.behance.net/thelastclicks')
        ->and($socials['pinterest'])->toBe('https://www.pinterest.com/thelastclicks/');

    // Facebook serves a login wall to every crawler and could not be checked at
    // all. x.com/thelastclicks is registered — a control handle 404s there — but
    // no public endpoint would serve the profile, and a registered handle is not
    // proof of ownership. Seeding either would put a dead link, or a stranger's
    // account, in the footer of every page.
    expect($socials)->not->toHaveKey('facebook')
        ->and($socials)->not->toHaveKey('x');
});

it('renders the linkedin profile in the footer', function () {
    $this->seed();

    $this->get('/')
        ->assertOk()
        ->assertSee('https://www.linkedin.com/company/thelastclicks', false);
});

it('replaces the superseded map link but keeps a deliberate one', function () {
    // The owner supplied the listing's own share link; the earlier seeded URL
    // bounced through a Google interstitial instead of resolving to the place.
    SiteSetting::set('map_url', 'https://share.google/QlMQkefJfn2iRnma3');
    $this->seed();
    expect(SiteSetting::get('map_url'))->toBe('https://maps.app.goo.gl/gu7Jr8BPX5PyhupCA');

    SiteSetting::set('map_url', 'https://maps.app.goo.gl/deliberately-different');
    $this->seed();
    expect(SiteSetting::get('map_url'))->toBe('https://maps.app.goo.gl/deliberately-different');
});
