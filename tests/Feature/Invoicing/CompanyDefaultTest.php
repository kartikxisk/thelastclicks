<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('activates the first company as well as promoting it to default', function () {
    // The create form leaves the Active toggle live for a new record (it is
    // only disabled once $record->is_default is true, and $record is null on
    // create), so unticking it while creating the very first company used to
    // save is_default = true alongside is_active = false. Company::default()
    // filters on is_active, so it returned null — permanently, because
    // creating a second company does not repair it: creating() only promotes
    // when the table is empty. A default that is not active is the same as
    // having no default at all.
    $first = Company::factory()->create(['is_default' => false, 'is_active' => false]);

    expect($first->fresh()->is_active)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeTrue()
        ->and(Company::default())->not->toBeNull();
});

it('asks the transaction to retry, because two concurrent promotions deadlock', function () {
    // makeDefault() fires CompanyObserver::saved(), whose demotion is
    // `UPDATE companies SET is_default = 0 WHERE id <> ?`. On MySQL that takes
    // a PRIMARY range scan, so two concurrent promotions each hold the rows
    // the other needs and InnoDB kills one. The invariant survives — the
    // victim rolls back whole — but DB::transaction() defaults to a single
    // attempt, so the loser reaches the admin as a 500 on a button that
    // would have worked.
    //
    // Asserting the attempt count rather than the deadlock: SQLite cannot
    // produce an InnoDB deadlock, so the retry itself is not reproducible in
    // this suite. What is checkable is that the retry was asked for.
    $company = Company::factory()->create();

    $attempts = null;

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function (...$args) use (&$attempts) {
            $attempts = $args[1] ?? 1;

            return null;
        });

    $company->makeDefault();

    expect($attempts)->toBe(3);
});
