<?php

namespace App\Policies;

use App\Models\Crew;
use App\Models\User;

class CrewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_crew');
    }

    public function view(User $user, Crew $c): bool
    {
        return $user->can('view_crew');
    }

    public function create(User $user): bool
    {
        return $user->can('create_crew');
    }

    public function update(User $user, Crew $c): bool
    {
        return $user->can('update_crew');
    }

    public function delete(User $user, Crew $c): bool
    {
        return $user->can('delete_crew');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_crew');
    }
}
