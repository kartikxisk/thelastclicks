<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Super-admin') && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Super-admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('Super-admin');
    }
}
