<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * This is an allow-list, not a policy check: Filament calls it before any
     * resource policy runs, so a role missing here is rejected at the panel
     * door no matter what permissions it holds — that is what happened to
     * Accounts, which had every billing permission and still couldn't log
     * in. `AdminPanelAccessTest`'s "every role RolesSeeder creates" test
     * reads the role list from the database rather than restating it, so the
     * next new role fails loudly here instead of shipping locked out.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['Super-admin', 'Editor', 'Sales', 'Viewer', 'Accounts']);
    }

    /** @return HasMany<Quote, $this> */
    public function assignedQuotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'assigned_to');
    }
}
