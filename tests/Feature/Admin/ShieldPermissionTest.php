<?php

use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('PermissionsSeeder runs without error', function () {
    $this->seed(PermissionsSeeder::class);
    expect(true)->toBeTrue();
});

it('Super-admin role exists after seeding', function () {
    $this->seed();
    expect(Role::findByName('Super-admin'))->not->toBeNull();
});

it('Viewer role exists after seeding', function () {
    $this->seed();
    expect(Role::findByName('Viewer'))->not->toBeNull();
});

it('shield-managed Role resource permissions exist after seed', function () {
    $this->seed();
    // shield always creates per-resource perms for its own RoleResource
    foreach (['view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role'] as $p) {
        expect(Permission::where('name', $p)->exists())->toBeTrue();
    }
});

it('seeds shield permissions for Quote resource', function () {
    $this->seed();
    foreach (['view_any_quote', 'view_quote', 'create_quote', 'update_quote', 'delete_quote', 'delete_any_quote'] as $p) {
        expect(Permission::where('name', $p)->exists())->toBeTrue();
    }
});

it('seeds shield permissions for WorkCategory and Testimonial resources', function () {
    $this->seed();
    foreach (['view_any_work::category', 'update_work::category', 'view_any_testimonial', 'update_testimonial'] as $p) {
        expect(Permission::where('name', $p)->exists())->toBeTrue();
    }
});
