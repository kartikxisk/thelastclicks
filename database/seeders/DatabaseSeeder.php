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
        // HeroSlidesFixtureSeeder replays the hero that exists, rather than
        // HeroSlidesSeeder, which derives one from featured Work and stays out
        // of db:seed. hero.blade.php renders no background at all without an
        // active slide — correct behaviour, but a poor way to launch a rebuilt
        // site. All three are create-only, so an editor's work is never undone
        // by a deploy.
        if (! app()->environment('testing')) {
            $this->call([
                WorksSeeder::class,
                ServiceMediaSeeder::class,
                HeroSlidesFixtureSeeder::class,
            ]);
        }
    }
}
