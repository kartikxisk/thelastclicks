<?php

namespace App\Policies;

use App\Models\BillingClient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * BillingClient does not use SoftDeletes, so this policy deliberately carries no
 * restore/restoreAny/forceDelete/forceDeleteAny. Shield's stock template
 * generates them; kept, they answer a question nothing can ask and advertise a
 * recovery path that does not exist — a delete here is final. `reorder` stays:
 * shield generates it and the table may gain a drag handle later.
 */
class BillingClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_billing::client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BillingClient $billingClient): bool
    {
        return $user->can('view_billing::client');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_billing::client');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BillingClient $billingClient): bool
    {
        return $user->can('update_billing::client');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BillingClient $billingClient): bool
    {
        return $user->can('delete_billing::client');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_billing::client');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, BillingClient $billingClient): bool
    {
        return $user->can('replicate_billing::client');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_billing::client');
    }
}
