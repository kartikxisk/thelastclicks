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
 */
class CompanyObserver
{
    public function creating(Company $company): void
    {
        // The first company is the only one it can be, so don't make someone
        // press a button to say so.
        if (Company::count() === 0) {
            $company->is_default = true;
        }
    }

    public function updating(Company $company): void
    {
        if ($company->is_default && $company->isDirty('is_active') && ! $company->is_active) {
            throw new RuntimeException(
                'Cannot deactivate the default company. Make another company the default first.'
            );
        }
    }

    public function saved(Company $company): void
    {
        if ($company->wasChanged('is_default') && $company->is_default) {
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
