<?php

namespace App\Policies;

use App\Models\Industry;
use App\Models\User;

class IndustryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_industry');
    }

    public function view(User $user, Industry $ind): bool
    {
        return $user->can('view_industry');
    }

    public function create(User $user): bool
    {
        return $user->can('create_industry');
    }

    public function update(User $user, Industry $ind): bool
    {
        return $user->can('update_industry');
    }

    public function delete(User $user, Industry $ind): bool
    {
        return $user->can('delete_industry');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_industry');
    }
}
