<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('makes the first company the default without being asked', function () {
    // An admin who creates one company and goes straight to raising an invoice
    // must not meet "no default company" — there is only one, so the answer is
    // never ambiguous.
    $first = Company::factory()->create(['is_default' => false]);

    expect($first->fresh()->is_default)->toBeTrue();
});

it('keeps exactly one default', function () {
    $first = Company::factory()->create();
    $second = Company::factory()->create();

    $second->makeDefault();

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse()
        ->and(Company::where('is_default', true)->count())->toBe(1);
});

it('refuses to deactivate the default company', function () {
    // Deactivating it would leave Company::default() null and every invoice form
    // with an empty company field — a state the admin cannot see the cause of.
    $default = Company::factory()->create();
    Company::factory()->create();

    expect(fn () => $default->update(['is_active' => false]))
        ->toThrow(RuntimeException::class, 'default company');
});

it('promotes a successor when the default is deleted', function () {
    $default = Company::factory()->create();
    $other = Company::factory()->create();

    $default->delete();

    expect($other->fresh()->is_default)->toBeTrue();
});

it('lets the last company be deleted without promoting a ghost', function () {
    $only = Company::factory()->create();

    $only->delete();

    expect(Company::count())->toBe(0)
        ->and(Company::default())->toBeNull();
});
