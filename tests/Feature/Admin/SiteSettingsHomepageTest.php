<?php

use App\Filament\Pages\SiteSettingsPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $admin = User::where('email', config('app.admin_seed_email'))->firstOrFail();
    $this->actingAs($admin);
});

it('loads existing homepage settings into the form', function () {
    Livewire::test(SiteSettingsPage::class)
        ->assertFormSet(function (array $state) {
            expect($state['home_strip'])->toHaveCount(6)
                ->and($state['hero_videos'])->toHaveCount(3);

            return true;
        });
});

it('saves strip and hero settings', function () {
    Livewire::test(SiteSettingsPage::class)
        ->fillForm([
            'home_strip' => [
                ['portfolio_slug' => 'range-rover', 'tag' => '001 · Auto · 2026', 'title' => 'Range <em>Rover.</em>', 'meta' => 'Reel'],
            ],
            'hero_videos' => [['portfolio_slug' => 'range-rover']],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SiteSetting::get('home_strip'))->toHaveCount(1)
        ->and(SiteSetting::get('home_strip')[0]['portfolio_slug'])->toBe('range-rover')
        ->and(SiteSetting::get('hero_videos'))->toBe(['range-rover']);
});
