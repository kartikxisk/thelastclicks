<?php

namespace App\Policies;

use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_service::item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('view_service::item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_service::item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('update_service::item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('delete_service::item');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_service::item');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('force_delete_service::item');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_service::item');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('restore_service::item');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_service::item');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ServiceItem $serviceItem): bool
    {
        return $user->can('replicate_service::item');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_service::item');
    }
}
