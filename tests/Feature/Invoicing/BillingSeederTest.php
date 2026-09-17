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
