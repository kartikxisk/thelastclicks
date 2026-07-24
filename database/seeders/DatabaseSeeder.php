<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            AdminUserSeeder::class,
            ServicesSeeder::class,
            IndustriesSeeder::class,
            ClientsSeeder::class,
            TestimonialsSeeder::class,
            PostsSeeder::class,
            SiteSettingsSeeder::class,
            SeoPagesSeeder::class,
        ]);
    }
}
