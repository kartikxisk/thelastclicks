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
        // The hero is deliberately absent: it is admin-owned, and a captured
        // fixture only re-created slides an editor had already replaced. A
        // rebuilt database therefore comes up with no hero background at all
        // (hero.blade.php omits the layer when no slide is active) until
        // somebody adds slides under Hero Slides, or runs HeroSlidesSeeder
        // once to derive them from featured Work.
        //
        // Both of these are create-only, so an editor's work is never undone
        // by a deploy.
        if (! app()->environment('testing')) {
            $this->call([
                WorksSeeder::class,
                ServiceMediaSeeder::class,
                // Titles and descriptions for the eleven non-service routes.
                // Was run-once-by-hand, which meant a rebuilt site launched with
                // no <title> or description on its homepage, about, portfolio or
                // contact pages until somebody remembered the command. The
                // testing guard above is what keeps it out of the suite: those
                // tests create their own rows for '/' and '/about' and assert
                // the no-row fallback path, which pre-seeded rows would break.
                PageSeoSeeder::class,
            ]);
        }
    }
}
