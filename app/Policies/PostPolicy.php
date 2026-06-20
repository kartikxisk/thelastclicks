<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_post');
    }

    public function view(User $user, Post $p): bool
    {
        return $user->can('view_post');
    }

    public function create(User $user): bool
    {
        return $user->can('create_post');
    }

    public function update(User $user, Post $p): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }
        if (! $user->can('update_post')) {
            return false;
        }
        if ($user->hasRole('Editor')) {
            return $p->author_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, Post $p): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }
        if (! $user->can('delete_post')) {
            return false;
        }
        if ($user->hasRole('Editor')) {
            return $p->author_id === $user->id;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_post');
    }
}
