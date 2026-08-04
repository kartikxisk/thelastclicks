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

        // Uploads that belong to rows the seeders above recreate. Kept separate
        // because they are medialibrary rows, which those seeders know nothing
        // about — without these a rebuilt database comes up with the content
        // present and its imagery blank, while the files sit untouched on S3.
        //
        // Skipped under testing: the feature suite asserts against the handful
        // of records its own tests create, and dropping 83 works into every
        // RefreshDatabase run would rewrite those expectations and slow the
        // suite for no benefit.
        //
        // Hero slides are deliberately absent. The only ones that ever existed
        // were copied from featured Work by HeroSlidesSeeder, which is not part
        // of db:seed; the hero is admin-managed and should come up empty until
        // somebody uploads to it.
        if (! app()->environment('testing')) {
            $this->call([
                WorksSeeder::class,
                ServiceMediaSeeder::class,
            ]);
        }
    }
}
