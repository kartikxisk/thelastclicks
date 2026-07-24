<?php

use App\Filament\Pages\SiteSettingsPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
    SiteSetting::set('brand_logo', 'branding/logo.png');
});

it('keeps the brand logo when the upload field comes back empty', function () {
    // Exactly what happens when the media disk is unreachable and the field
    // cannot hydrate the existing file — saving must not destroy the logo.
    Livewire::test(SiteSettingsPage::class)
        ->fillForm(['brand_logo' => null])
        ->call('save');

    expect(SiteSetting::get('brand_logo'))->toBe('branding/logo.png');
});

it('removes the brand logo only when explicitly asked', function () {
    Livewire::test(SiteSettingsPage::class)
        ->fillForm(['remove_brand_logo' => true])
        ->call('save');

    expect(SiteSetting::get('brand_logo'))->toBe('');
});

it('still saves the rest of the settings when the logo is untouched', function () {
    Livewire::test(SiteSettingsPage::class)
        ->fillForm(['contact_email' => 'studio@thelastclicks.com'])
        ->call('save');

    expect(SiteSetting::get('contact_email'))->toBe('studio@thelastclicks.com')
        ->and(SiteSetting::get('brand_logo'))->toBe('branding/logo.png');
});
