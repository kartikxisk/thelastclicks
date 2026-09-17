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

it('hands back an integer sort position, like the other three integer columns', function () {
    // `sort` was the one integer column with no cast, so the model handed back
    // whatever the driver returned. A whole number hides it — SQLite and MySQL
    // both return an int for that — so this uses the value that does not:
    // "1.5" is stored by SQLite as a float and came back as 1.5, a rate card
    // ordered by something that is not a position. rate_paise and
    // tax_rate_bps were always ints; this column was the odd one out.
    $item = ServiceItem::factory()->create(['sort' => '1.5']);

    expect($item->fresh()->sort)->toBeInt()->toBe(1);
});

it('deletes a company-scoped rate card with its company and leaves the shared rows', function () {
    // service_items.company_id is cascadeOnDelete and three separate comments
    // call that out as dangerous and silent — but until now only the delete
    // confirmation's row count was tested, never the delete itself. SQLite
    // enforces foreign keys in this repo, so this genuinely exercises the
    // constraint rather than asserting a comment.
    $doomed = Company::factory()->create();
    $survivor = Company::factory()->create();

    $scoped = ServiceItem::factory()->count(2)->create(['company_id' => $doomed->id]);
    $shared = ServiceItem::factory()->create(['company_id' => null]);
    $theirs = ServiceItem::factory()->create(['company_id' => $survivor->id]);

    $doomed->delete();

    expect(ServiceItem::whereIn('id', $scoped->pluck('id'))->count())->toBe(0)
        // The shared row belongs to no company and must survive — it is most of
        // the rate card, and losing it to an unrelated company's delete would
        // be silent.
        ->and($shared->fresh())->not->toBeNull()
        ->and($theirs->fresh())->not->toBeNull();
});
