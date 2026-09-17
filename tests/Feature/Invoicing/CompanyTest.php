<?php

use App\Models\Company;
use App\Rules\Gstin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mints fixtures whose GSTIN would survive the real rule', function () {
    // The factory builds a GSTIN by computing its own checksum. If that ever
    // drifts from the rule, every downstream test would pass against a GSTIN no
    // client could actually have.
    $company = Company::factory()->inState('27')->create();

    expect(Gstin::isValid($company->gstin))->toBeTrue()
        ->and(substr($company->gstin, 0, 2))->toBe('27');
});

it('drops blank lines out of the printed address', function () {
    $company = Company::factory()->create([
        'address_line1' => 'B-12, Lajpat Nagar',
        'address_line2' => null,
        'address_city' => 'New Delhi',
        'address_state' => 'Delhi',
        'address_postal_code' => '110024',
    ]);

    expect($company->addressLines())->toBe([
        'B-12, Lajpat Nagar',
        'New Delhi, Delhi 110024',
    ]);
});

it('labels its state from the code', function () {
    $company = Company::factory()->create(['address_state_code' => '07']);

    expect($company->stateLabel())->toBe('Delhi');
});

it('scopes to active companies', function () {
    Company::factory()->create(['is_active' => true]);
    Company::factory()->create(['is_active' => false]);

    expect(Company::active()->count())->toBe(1);
});

it('registers the three branding collections an invoice needs', function () {
    $company = Company::factory()->create();

    expect(collect($company->getRegisteredMediaCollections())->pluck('name')->all())
        ->toBe(['logo', 'signature', 'stamp']);
});

it('can be unregistered, in which case it carries no GSTIN', function () {
    $company = Company::factory()->unregistered()->create();

    expect($company->is_gst_registered)->toBeFalse()
        ->and($company->gstin)->toBeNull();
});
