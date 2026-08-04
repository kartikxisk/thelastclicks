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

it('seeds shield permissions for the Testimonial resource', function () {
    $this->seed();
    foreach (['view_any_testimonial', 'update_testimonial'] as $p) {
        expect(Permission::where('name', $p)->exists())->toBeTrue();
    }
});

it('lets an Editor reach the Homepage Hero resource', function () {
    // The resource and its policy both existed while the permission was never
    // handed to Editor, so the nav item was invisible to everyone but
    // Super-admin — indistinguishable from the module not being built.
    $this->seed();

    $editor = Role::findByName('Editor');

    foreach (['view_any_hero::slide', 'create_hero::slide', 'update_hero::slide', 'delete_hero::slide'] as $p) {
        expect(Permission::where('name', $p)->exists())->toBeTrue()
            ->and($editor->hasPermissionTo($p))->toBeTrue();
    }
});
