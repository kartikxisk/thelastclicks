<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Super-admin', 'Editor', 'Sales', 'Viewer'] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
