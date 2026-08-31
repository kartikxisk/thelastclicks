<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * The seeded brand logo must be the wordmark, not the favicon mark.
 *
 * brand_logo defaulted to branding/logo-be3963b5c6.png — the 320x320 circular
 * emblem — so any environment that had not set the value by hand rendered a
 * near-invisible dark mark on the dark nav. It went unnoticed because production
 * carried an editor's value, until a rebuilt database fell back to the default.
 */
it('seeds the wordmark as the brand logo, not the square emblem', function () {
    $this->seed();

    expect(SiteSetting::get('brand_logo'))->toBe('branding/logo-a6a2cd4afe.png');
});

it('resolves the seeded branding keys to files that exist on the media disk', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    foreach (['logo-a6a2cd4afe.png', 'logo-dark-f65ca5e2f7.png', 'favicon-34d5039f93.png'] as $file) {
        Storage::disk('s3')->put('branding/'.$file, 'bytes');
    }

    $this->seed();

    foreach (['brand_logo', 'brand_logo_dark', 'favicon'] as $key) {
        $value = SiteSetting::get($key);

        expect($value)->not->toBeNull();
        Storage::disk('s3')->assertExists($value);
    }
});
