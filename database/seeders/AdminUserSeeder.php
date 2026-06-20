<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrCreate(
            ['email' => config('app.admin_seed_email')],
            ['name' => 'Admin', 'password' => config('app.admin_seed_password')]
        );
        $u->syncRoles(['Super-admin']);
    }
}
