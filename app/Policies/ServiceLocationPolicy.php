<?php

namespace App\Policies;

use App\Models\ServiceLocation;
use App\Models\User;

class ServiceLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_service::location');
    }

    public function view(User $user, ServiceLocation $location): bool
    {
        return $user->can('view_service::location');
    }

    public function create(User $user): bool
    {
        return $user->can('create_service::location');
    }

    public function update(User $user, ServiceLocation $location): bool
    {
        return $user->can('update_service::location');
    }

    public function delete(User $user, ServiceLocation $location): bool
    {
        return $user->can('delete_service::location');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_service::location');
    }
}
