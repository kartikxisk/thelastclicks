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

        // The work archive, so `migrate:fresh --seed` rebuilds a real portfolio
        // instead of an empty one with the uploads stranded on S3.
        //
        // Skipped under testing: the feature suite asserts against the handful
        // of works its own tests create, and dropping 83 fixtures into every
        // RefreshDatabase run would rewrite those expectations and slow the
        // suite for no benefit.
        if (! app()->environment('testing')) {
            $this->call(WorksSeeder::class);
        }
    }
}
