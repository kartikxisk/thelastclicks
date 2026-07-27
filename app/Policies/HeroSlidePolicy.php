<?php

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HeroSlidePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_hero::slide');
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('view_hero::slide');
    }

    public function create(User $user): bool
    {
        return $user->can('create_hero::slide');
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('update_hero::slide');
    }

    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('delete_hero::slide');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_hero::slide');
    }

    public function forceDelete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('force_delete_hero::slide');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_hero::slide');
    }

    public function restore(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('restore_hero::slide');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_hero::slide');
    }

    public function replicate(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('replicate_hero::slide');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_hero::slide');
    }
}
