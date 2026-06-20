<?php

use App\Models\Crew;
use App\Models\Industry;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates service / industry / crew rows', function () {
    expect(Service::factory()->create()->slug)->not->toBeEmpty()
        ->and(Industry::factory()->create()->slug)->not->toBeEmpty()
        ->and(Crew::factory()->create()->slug)->not->toBeEmpty();
});

it('stores + reads a site setting', function () {
    SiteSetting::set('contact_email', 'hi@x.com');
    expect(SiteSetting::get('contact_email'))->toBe('hi@x.com')
        ->and(SiteSetting::get('missing', 'fallback'))->toBe('fallback');
});
