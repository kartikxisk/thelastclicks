<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Generate per-resource permissions for all auto-discovered Filament Resources.
        // --ignore-existing-policies preserves any custom policy logic (e.g. QuotePolicy).
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--ignore-existing-policies' => true,
        ]);

        $this->assignRolePermissions();
    }

    protected function assignRolePermissions(): void
    {
        $superAdmin = Role::findOrCreate('Super-admin', 'web');
        $editor = Role::findOrCreate('Editor', 'web');
        $sales = Role::findOrCreate('Sales', 'web');
        $viewer = Role::findOrCreate('Viewer', 'web');

        $all = Permission::pluck('name')->all();

        // Super-admin: everything
        $superAdmin->syncPermissions($all);

        // Editor: CRUD on content resources (post, portfolio, service, industry, crew,
        // category, tag, work category, testimonial). Shield names WorkCategory perms
        // with its "::" separator, e.g. update_work::category.
        $editorResources = 'post|portfolio|service|industry|crew|category|tag|work::category|testimonial';
        $editor->syncPermissions(array_filter($all, fn ($p) => preg_match('/_('.$editorResources.')$/', $p) === 1
            || preg_match('/_any_('.$editorResources.')$/', $p) === 1
        ));

        // Sales: all perms on Quote + QuoteNote
        $sales->syncPermissions(array_filter($all, fn ($p) => str_ends_with($p, '_quote') || str_ends_with($p, '_quote_note')
        ));

        // Viewer: only view_* perms
        $viewer->syncPermissions(array_filter($all, fn ($p) => str_starts_with($p, 'view_')
        ));
    }
}
