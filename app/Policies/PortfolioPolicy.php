<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;

class PortfolioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_portfolio');
    }

    public function view(User $user, Portfolio $p): bool
    {
        return $user->can('view_portfolio');
    }

    public function create(User $user): bool
    {
        return $user->can('create_portfolio');
    }

    public function update(User $user, Portfolio $p): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }
        if (! $user->can('update_portfolio')) {
            return false;
        }
        if ($user->hasRole('Editor')) {
            return $p->owner_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, Portfolio $p): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }
        if (! $user->can('delete_portfolio')) {
            return false;
        }
        if ($user->hasRole('Editor')) {
            return $p->owner_id === $user->id;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_portfolio');
    }
}
