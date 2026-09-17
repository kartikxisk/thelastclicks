<?php

use App\Models\Company;
use App\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores the rate as paise', function () {
    $item = ServiceItem::factory()->create(['rate_paise' => 15000000]);

    expect($item->fresh()->rate_paise)->toBe(15000000)
        ->and($item->formattedRate())->toBe('₹1,50,000.00');
});

it('offers shared items to every company alongside that company own items', function () {
    $mine = Company::factory()->create();
    $theirs = Company::factory()->create();

    ServiceItem::factory()->create(['company_id' => null, 'name' => 'Shared']);
    ServiceItem::factory()->create(['company_id' => $mine->id, 'name' => 'Mine']);
    ServiceItem::factory()->create(['company_id' => $theirs->id, 'name' => 'Theirs']);

    expect(ServiceItem::forCompany($mine->id)->pluck('name')->sort()->values()->all())
        ->toBe(['Mine', 'Shared']);
});

it('defaults to the event photography SAC', function () {
    // 998383 — event photography and videography, 18%. Wrong on an invoice is a
    // filing mismatch, so the default is the one the studio bills most.
    expect(ServiceItem::factory()->create()->sac_code)->toBe('998383');
});

it('marks pass-through costs so the invoice can group them', function () {
    $expense = ServiceItem::factory()->expense()->create();

    expect($expense->is_expense)->toBeTrue();
});

it('scopes to active items', function () {
    ServiceItem::factory()->create(['is_active' => true]);
    ServiceItem::factory()->create(['is_active' => false]);

    expect(ServiceItem::active()->count())->toBe(1);
});
