<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_company');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        return $user->can('view_company');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_company');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        return $user->can('update_company');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Hand-written, and it must stay that way. Spec §4.1: the policy refuses to
     * delete or deactivate the last active company. CompanyObserver holds the
     * deactivate half; this is the delete half.
     *
     * Deleting the last active company leaves Company::default() null and every
     * invoice form with an empty company field and no explanation — the same
     * end state the observer already refuses to reach the other way. Delete is
     * one click from the EditCompany header action, and `service_items.company_id`
     * is cascadeOnDelete, so a company-scoped rate card goes with it silently.
     *
     * PermissionsSeeder runs `shield:generate --ignore-existing-policies`, so
     * regenerating permissions does not overwrite this file. Nobody needs to
     * "restore" it to the stock template.
     */
    public function delete(User $user, Company $company): bool
    {
        if (! $user->can('delete_company')) {
            return false;
        }

        // Only an active company can be the last active one. Deleting an
        // already-inactive row takes nothing away from Company::default(),
        // which filters on is_active and was never going to return it.
        if (! $company->is_active) {
            return true;
        }

        return Company::query()
            ->where('is_active', true)
            ->whereKeyNot($company->getKey())
            ->exists();
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_company');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $user->can('force_delete_company');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_company');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->can('restore_company');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_company');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Company $company): bool
    {
        return $user->can('replicate_company');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_company');
    }
}
