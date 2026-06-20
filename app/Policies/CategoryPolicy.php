<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_category');
    }

    public function view(User $user, Category $c): bool
    {
        return $user->can('view_category');
    }

    public function create(User $user): bool
    {
        return $user->can('create_category');
    }

    public function update(User $user, Category $c): bool
    {
        return $user->can('update_category');
    }

    public function delete(User $user, Category $c): bool
    {
        return $user->can('delete_category');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_category');
    }
}
