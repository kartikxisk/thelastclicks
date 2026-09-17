<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
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

it('lets every role RolesSeeder creates reach /admin', function () {
    // Reads the role list from the database rather than restating it as a
    // literal here, because canAccessPanel() is a hardcoded allow-list that
    // gates entry before any policy runs — a role added to RolesSeeder and
    // forgotten in that allow-list holds every permission it was granted and
    // is still bounced at the door. This is what caught Accounts the first
    // time and is what keeps the next role from slipping through the same
    // gap unnoticed.
    $this->seed(RolesSeeder::class);

    foreach (Role::pluck('name') as $roleName) {
        $u = User::factory()->create();
        $u->assignRole($roleName);

        $this->actingAs($u)->get('/admin')->assertOk();
    }
});
