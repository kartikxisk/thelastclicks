<?php

namespace App\Observers;

use App\Models\Company;
use RuntimeException;

/**
 * Holds the "exactly one active default" invariant.
 *
 * Every invoice form prefills from Company::default(). If that returns null the
 * form comes up with an empty company field and no explanation, so the ways of
 * reaching that state are closed here rather than being discovered later.
 *
 * This holds only for writes that go through Eloquent. `Company::query()->update(...)`
 * and `DB::table('companies')->...` fire no model events, so they bypass every
 * guard below — there is no hook Eloquent offers to intercept a mass update.
 * A bulk action on the Filament companies resource must therefore save each
 * row through the model, not issue one mass update.
 */
class CompanyObserver
{
    public function creating(Company $company): void
    {
        // The first company is the only one it can be, so don't make someone
        // press a button to say so.
        //
        // is_active is forced alongside it, mirroring makeDefault(). The create
        // form leaves the Active toggle live for a new record — it is only
        // disabled once $record->is_default is true, and $record is null on
        // create — so unticking it while creating the very first company saved
        // is_default = true with is_active = false. Company::default() filters
        // on is_active and so returned null, permanently: creating a second
        // company does not repair it, because this branch only promotes while
        // the table is empty. A default that is not active is the same as
        // having no default at all.
        if (Company::count() === 0) {
            $company->is_default = true;
            $company->is_active = true;
        }
    }

    public function updating(Company $company): void
    {
        // By the time this fires, fill() has already applied the incoming
        // attributes, so $company->is_default is the value being saved TO, not
        // the value in the database. Guarding on that post-fill value would let
        // a single update(['is_default' => false, 'is_active' => false]) — the
        // payload a full-form Filament edit submits when the admin unticks
        // both toggles — read is_default as already false and slip through
        // unguarded. Comparing against the original tells us whether this row
        // is *currently* the default, which is the question that matters.
        $wasDefault = (bool) $company->getOriginal('is_default');

        if (! $wasDefault) {
            return;
        }

        if ($company->isDirty('is_active') && ! $company->is_active) {
            throw new RuntimeException(
                'Cannot deactivate the default company. Make another company the default first.'
            );
        }

        // Clearing is_default directly (rather than through makeDefault() on a
        // different company) would leave zero defaults, because saved() below
        // only ever acts when is_default turns true — nothing reacts to it
        // turning false.
        if ($company->isDirty('is_default') && ! $company->is_default) {
            throw new RuntimeException(
                'Cannot remove the default company flag directly. Make another company the default first.'
            );
        }
    }

    /**
     * Demote every other company whenever this one's is_default turns true.
     *
     * `makeDefault()` is the supported path and runs this atomically with its
     * own save, because both happen inside its transaction. A bare
     * `$company->update(['is_default' => true])` also reaches this and works,
     * but only best-effort: that row's save and this demotion are two separate
     * statements outside any transaction of the caller's, so a crash between
     * them could leave two permanent defaults. It also bypasses no guard above,
     * since is_default turning true is never the thing updating() refuses.
     */
    public function saved(Company $company): void
    {
        if ($company->wasChanged('is_default') && $company->is_default) {
            // A query-builder mass update, not an Eloquent save — it fires no
            // model events, so it cannot re-trigger updating()'s guards above
            // or recurse into this method for the rows it demotes.
            Company::query()->where('id', '!=', $company->id)->update(['is_default' => false]);
        }
    }

    public function deleted(Company $company): void
    {
        if (! $company->is_default) {
            return;
        }

        // Oldest surviving company wins — arbitrary, but deterministic, and the
        // admin can change it in one click.
        Company::query()->where('is_active', true)->oldest('id')->first()?->makeDefault();
    }
}
