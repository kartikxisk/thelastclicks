<?php

use App\Filament\Pages\SiteSettingsPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('renders existing settings into the form on load', function () {
    Livewire::test(SiteSettingsPage::class)
        ->assertFormFieldExists('contact_email')
        ->assertFormSet(['contact_email' => 'hello@thelastclicks.com']);
});

it('saves changes to the site_settings KV store', function () {
    Livewire::test(SiteSettingsPage::class)
        ->fillForm([
            'contact_email' => 'hi@new.com',
            'contact_phone' => '+91-99999-00000',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SiteSetting::get('contact_email'))->toBe('hi@new.com')
        ->and(SiteSetting::get('contact_phone'))->toBe('+91-99999-00000');
});

it('Non-Super-admin cannot access the settings page', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    $this->actingAs($editor);
    expect(SiteSettingsPage::canAccess())->toBeFalse();
});
