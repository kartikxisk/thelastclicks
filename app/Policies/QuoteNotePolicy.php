<?php

namespace App\Policies;

use App\Models\QuoteNote;
use App\Models\User;

class QuoteNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_quote_note');
    }

    public function view(User $user, QuoteNote $note): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }
        if ($user->hasRole('Sales')) {
            return $note->quote->assigned_to === $user->id;
        }

        return $user->can('view_quote_note');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }

        return $user->can('create_quote_note');
    }

    public function update(User $user, QuoteNote $note): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }

        return $note->author_id === $user->id && $user->can('update_quote_note');
    }

    public function delete(User $user, QuoteNote $note): bool
    {
        if ($user->hasRole('Super-admin')) {
            return true;
        }

        return $note->author_id === $user->id && $user->can('delete_quote_note');
    }
}
