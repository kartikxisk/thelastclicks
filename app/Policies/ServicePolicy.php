<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_service');
    }

    public function view(User $user, Service $svc): bool
    {
        return $user->can('view_service');
    }

    public function create(User $user): bool
    {
        return $user->can('create_service');
    }

    public function update(User $user, Service $svc): bool
    {
        return $user->can('update_service');
    }

    public function delete(User $user, Service $svc): bool
    {
        return $user->can('delete_service');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_service');
    }
}
