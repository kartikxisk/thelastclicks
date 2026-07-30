<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns global site settings', function () {
    SiteSetting::set('contact_email', 'hello@thelastclicks.com');
    SiteSetting::set('contact_phone', '+441234567890');
    SiteSetting::set('work_tile_ratio', '16 / 9');

    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.contact_email', 'hello@thelastclicks.com')
        ->assertJsonPath('data.contact_phone', '+441234567890')
        ->assertJsonPath('data.work_tile_ratio', '16 / 9')
        ->assertJsonStructure([
            'data' => [
                'contact_email',
                'contact_phone',
                'whatsapp_url',
                'socials' => ['instagram', 'youtube', 'facebook', 'linkedin', 'x', 'behance', 'pinterest'],
                'brand_logo_url',
                'favicon_url',
                'cta_video_url',
                'work_tile_ratio',
                'seo_defaults' => ['title', 'description', 'og_image'],
            ],
        ]);
});

it('exposes every social link the admin can set', function () {
    SiteSetting::set('socials', [
        'instagram' => 'https://instagram.com/tlc',
        'behance' => 'https://behance.net/tlc',
    ]);

    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.socials.instagram', 'https://instagram.com/tlc')
        ->assertJsonPath('data.socials.behance', 'https://behance.net/tlc')
        // Unset platforms are null, never absent — the frontend maps over a
        // fixed key set rather than guarding each one.
        ->assertJsonPath('data.socials.youtube', null);
});

it('falls back to the default work tile ratio when unset', function () {
    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.work_tile_ratio', SiteSetting::DEFAULT_WORK_TILE_RATIO);
});

it('rejects a work tile ratio that is not an offered option', function () {
    SiteSetting::set('work_tile_ratio', '13 / 7');

    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.work_tile_ratio', SiteSetting::DEFAULT_WORK_TILE_RATIO);
});

it('falls back to the bundled cta video when none is uploaded', function () {
    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.cta_video_url', SiteSetting::DEFAULT_CTA_VIDEO);
});

it('returns null brand logo rather than substituting a bundled file', function () {
    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.brand_logo_url', null);
});

it('serves settings within the query budget', function () {
    // SiteSetting::get() is one indexed primary-key lookup per key and holds no
    // memo. That is fine here: the frontend caches this response under the
    // `settings` ISR tag, so it is fetched once per revalidation window rather
    // than once per visitor. The ceiling exists to catch a relation or a loop
    // creeping in, not to force a cache.
    assertQueryCount(12, fn () => $this->getJson('/api/v1/settings')->assertOk());
});
