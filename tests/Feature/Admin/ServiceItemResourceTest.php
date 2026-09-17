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

it('rejects a negative rate-card rate', function () {
    // A rate card is a price list; a negative price is not one. RupeeInput
    // itself still admits a leading minus on purpose — phase 2 needs negatives
    // for discount and round-off lines — so the constraint belongs here, at
    // the call site, rather than in the component.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Negative rate',
            'rate_paise' => '-100.00',
        ])
        ->call('create')
        ->assertHasFormErrors(['rate_paise']);
});

it('refuses a negative sort position', function () {
    // `sort` is an unsignedInteger column and ->numeric() alone passes -1.
    // Zero is allowed — it is the column default and the top of the list — so
    // the floor is 0 here rather than the 1 the payment-term fields use.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Negative sort',
            'rate_paise' => '100.00',
            'sort' => -1,
        ])
        ->call('create')
        ->assertHasFormErrors(['sort']);
});

it('still accepts a sort position of zero', function () {
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Top of the list',
            'rate_paise' => '100.00',
            'sort' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('refuses a unit outside the shipped list', function () {
    // A plain ->options() Select adds no server-side rule, so the option list is
    // a client-side affordance only and a crafted Livewire payload — or a future
    // importer reusing this form's rules — writes anything it likes.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Fortnightly retainer',
            'unit' => 'fortnight',
            'rate_paise' => '1000',
            'tax_rate_bps' => 1800,
        ])
        ->call('create')
        ->assertHasFormErrors(['unit']);
});

it('refuses a GST rate that is not a real slab', function () {
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Creative rate',
            'unit' => 'project',
            'rate_paise' => '1000',
            'tax_rate_bps' => 1300,
        ])
        ->call('create')
        ->assertHasFormErrors(['tax_rate_bps']);
});

it('refuses a fractional sort position', function () {
    // The Eloquent cast added for this column is read-side only: "1.5" still
    // reaches an unsignedInteger column, where MySQL rounds it and SQLite keeps
    // it, and the two disagree about the order the rate card is in.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Half a position',
            'unit' => 'project',
            'rate_paise' => '1000',
            'tax_rate_bps' => 1800,
            'sort' => '1.5',
        ])
        ->call('create')
        ->assertHasFormErrors(['sort']);
});
