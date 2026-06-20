<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Super-admin', 'web');
    Role::findOrCreate('Viewer', 'web');
});

it('anonymous gets redirected to admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('user with no role cannot access /admin', function () {
    $u = User::factory()->create();
    $this->actingAs($u)->get('/admin')->assertForbidden();
});

it('user with Super-admin role can access /admin', function () {
    $u = User::factory()->create();
    $u->assignRole('Super-admin');
    $this->actingAs($u)->get('/admin')->assertOk();
});
