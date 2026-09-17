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

it('refuses a full-form update that unticks both is_default and is_active at once', function () {
    // By the time updating() fires, fill() has already applied the incoming
    // attributes, so a payload that also clears is_default — exactly what a
    // Filament edit form resubmits when every field is unticked — makes
    // is_default read as already false unless the guard compares against the
    // original value. Guarded on the post-fill value, this update would have
    // sailed through and left zero default companies.
    $default = Company::factory()->create();
    Company::factory()->create();

    expect(fn () => $default->update(['is_default' => false, 'is_active' => false]))
        ->toThrow(RuntimeException::class, 'default company');

    expect(Company::where('is_default', true)->count())->toBe(1);
});

it('refuses to unset the default flag directly', function () {
    // Nothing reacts to is_default turning false — saved() only ever demotes
    // the OTHER companies when this one turns true — so clearing it here with
    // no replacement default would leave Company::default() null.
    $default = Company::factory()->create();
    Company::factory()->create();

    expect(fn () => $default->update(['is_default' => false]))
        ->toThrow(RuntimeException::class, 'default company');

    expect(Company::where('is_default', true)->count())->toBe(1);
});

it('demotes the current default when another company is made default by a direct update', function () {
    // makeDefault() is the supported, transactional path, but a bare
    // update(['is_default' => true]) is a realistic way for this to happen
    // too (e.g. from a form that saves the model directly) and saved() must
    // still hold the invariant for it.
    $first = Company::factory()->create();
    $second = Company::factory()->create();

    $second->update(['is_default' => true]);

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse()
        ->and(Company::where('is_default', true)->count())->toBe(1);
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
