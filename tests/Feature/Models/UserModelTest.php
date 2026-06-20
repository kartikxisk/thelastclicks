<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Traits\HasRoles;

uses(RefreshDatabase::class);

it('uses HasRoles trait', function () {
    expect(class_uses_recursive(User::class))
        ->toContain(HasRoles::class);
});

it('creates a user with name + email + password', function () {
    $u = User::factory()->create(['email' => 'a@b.com']);
    expect($u->email)->toBe('a@b.com');
});
