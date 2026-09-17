<?php

use App\Models\Company;
use App\Models\ServiceItem;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a default company', function () {
    $this->seed(BillingSeeder::class);

    expect(Company::count())->toBe(1)
        ->and(Company::default())->not->toBeNull();
});

it('leaves the tax registration blank rather than inventing one', function () {
    // A seeded placeholder GSTIN would be a fake number on a real invoice. The
    // company arrives unregistered so that phase 2 validation refuses to issue
    // until a human has entered the real details.
    $this->seed(BillingSeeder::class);

    $company = Company::default();

    expect($company->gstin)->toBeNull()
        ->and($company->is_gst_registered)->toBeFalse();
});

it('seeds a starter rate card', function () {
    $this->seed(BillingSeeder::class);

    expect(ServiceItem::count())->toBeGreaterThan(0)
        ->and(ServiceItem::where('is_expense', true)->count())->toBeGreaterThan(0);
});

it('is idempotent', function () {
    // Seeders are this project fixture layer and get re-run on every deploy;
    // a second run must not produce a second company or a duplicated rate card.
    $this->seed(BillingSeeder::class);
    $this->seed(BillingSeeder::class);

    expect(Company::count())->toBe(1)
        ->and(ServiceItem::count())->toBe(ServiceItem::distinct('name')->count('name'));
});

it('stays idempotent across an APP_NAME change', function () {
    // A lookup keyed on config('app.name') would silently create a second
    // company the day APP_NAME differs from whatever it was when the database
    // was first seeded — a rebrand, or simply an environment configured
    // differently. Testing for "no company at all" instead of a name match is
    // what this seeder relies on to stay a no-op here.
    $this->seed(BillingSeeder::class);

    config(['app.name' => 'Something Else']);
    $this->seed(BillingSeeder::class);

    expect(Company::count())->toBe(1);
});

it('leaves an admin chosen default alone on re-seed', function () {
    // DatabaseSeeder runs on every deploy. Promoting whichever company this
    // seeder finds — because it happens not to be the default — cannot tell
    // "never been default" apart from "an admin deliberately made a different,
    // real, GST-registered company the default last week". It must not revert
    // that choice.
    $this->seed(BillingSeeder::class);

    $seeded = Company::default();
    $chosen = Company::factory()->create();
    $chosen->makeDefault();

    $this->seed(BillingSeeder::class);

    expect(Company::default()->id)->toBe($chosen->id)
        ->and($seeded->fresh()->is_default)->toBeFalse();
});

it('does not duplicate a rate-card row an admin renamed', function () {
    // The docblock on rateCard() invites an admin to edit these placeholder
    // rows. A per-name lookup would miss a renamed row on the next deploy and
    // seed a fresh zero-priced duplicate under the original placeholder name.
    $this->seed(BillingSeeder::class);

    $countBefore = ServiceItem::count();
    ServiceItem::whereNull('company_id')->first()->update(['name' => 'Renamed by admin']);

    $this->seed(BillingSeeder::class);

    expect(ServiceItem::count())->toBe($countBefore);
});
