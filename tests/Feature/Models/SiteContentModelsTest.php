<?php

use App\Models\Industry;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates service / industry rows', function () {
    expect(Service::factory()->create()->slug)->not->toBeEmpty()
        ->and(Industry::factory()->create()->slug)->not->toBeEmpty();
});

it('stores + reads a site setting', function () {
    SiteSetting::set('contact_email', 'hi@x.com');
    expect(SiteSetting::get('contact_email'))->toBe('hi@x.com')
        ->and(SiteSetting::get('missing', 'fallback'))->toBe('fallback');
});

it('defaults the work tile ratio to vertical', function () {
    // Production rendered wide tiles while local rendered vertical ones: the
    // ratio lives in site_settings, local had been changed by hand, and prod
    // had no row so it fell back to this constant. The default is the shape the
    // homepage strip is actually built around.
    expect(SiteSetting::where('key', 'work_tile_ratio')->exists())->toBeFalse()
        ->and(SiteSetting::workTileRatio())->toBe('9 / 16');
});

it('lets an admin choice beat the default tile ratio', function () {
    // The default must not be seeded or forced, or picking a ratio in Site
    // Settings would be undone on the next deploy.
    SiteSetting::set('work_tile_ratio', '16 / 9');

    expect(SiteSetting::workTileRatio())->toBe('16 / 9');
});

it('falls back when the stored tile ratio is not allowlisted', function () {
    SiteSetting::set('work_tile_ratio', '13 / 7');

    expect(SiteSetting::workTileRatio())->toBe('9 / 16');
});
