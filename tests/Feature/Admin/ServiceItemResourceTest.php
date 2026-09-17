<?php

use App\Filament\Resources\ServiceItemResource\Pages\CreateServiceItem;
use App\Filament\Resources\ServiceItemResource\Pages\EditServiceItem;
use App\Filament\Resources\ServiceItemResource\Pages\ListServiceItems;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list service items', function () {
    Livewire::test(ListServiceItems::class)->assertCanSeeTableRecords(ServiceItem::all());
});

it('stores a typed rupee rate as paise', function () {
    // The column is paise; the field is rupees. If that conversion is ever
    // dropped, a ₹1,50,000 rate silently becomes ₹1,500 and every invoice built
    // from it is wrong by two orders of magnitude.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Wedding full-day coverage',
            'unit' => 'day',
            'rate_paise' => '1,50,000',
            'tax_rate_bps' => 1800,
            'sac_code' => '998383',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceItem::where('name', 'Wedding full-day coverage')->first()->rate_paise)->toBe(15000000);
});

it('shows the stored paise back as rupees when editing', function () {
    $item = ServiceItem::factory()->create(['rate_paise' => 15000000]);

    Livewire::test(EditServiceItem::class, ['record' => $item->getRouteKey()])
        ->assertFormSet(['rate_paise' => '150000.00']);
});

it('rejects a rate with three decimal places', function () {
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Odd rate',
            'rate_paise' => '100.555',
        ])
        ->call('create')
        ->assertHasFormErrors(['rate_paise']);
});

it('rejects a lone minus sign as a form error rather than throwing', function () {
    // The regex used to admit '-' with no digits at all. That passed
    // validation and reached Money::fromRupees('-'), which throws an
    // uncaught InvalidArgumentException — an admin sees a 500 instead of a
    // field error for what is obviously not an amount.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Bare minus',
            'rate_paise' => '-',
        ])
        ->call('create')
        ->assertHasFormErrors(['rate_paise']);
});
