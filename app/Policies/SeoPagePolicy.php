<?php

namespace App\Policies;

use App\Models\SeoPage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeoPagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_seo::page');
    }

    public function view(User $user, SeoPage $seoPage): bool
    {
        return $user->can('view_seo::page');
    }

    public function create(User $user): bool
    {
        return $user->can('create_seo::page');
    }

    public function update(User $user, SeoPage $seoPage): bool
    {
        return $user->can('update_seo::page');
    }

    public function delete(User $user, SeoPage $seoPage): bool
    {
        return $user->can('delete_seo::page');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_seo::page');
    }

    public function forceDelete(User $user, SeoPage $seoPage): bool
    {
        return $user->can('force_delete_seo::page');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_seo::page');
    }

    public function restore(User $user, SeoPage $seoPage): bool
    {
        return $user->can('restore_seo::page');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_seo::page');
    }

    public function replicate(User $user, SeoPage $seoPage): bool
    {
        return $user->can('replicate_seo::page');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_seo::page');
    }
}
