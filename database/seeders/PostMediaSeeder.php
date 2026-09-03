<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Support\MediaSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Re-attaches journal covers to a rebuilt database.
 *
 * PostsSeeder recreates the posts, but a cover uploaded through the admin is a
 * medialibrary row it knows nothing about — so /blog came up with every card
 * showing an empty placeholder while the files sat untouched on S3. The same
 * gap ServiceMediaSeeder and IndustryMediaSeeder close on their own models.
 *
 * Refresh the fixture with `php artisan app:export-post-media`.
 */
class PostMediaSeeder extends Seeder
{
    /** Overridable so a test can point at its own fixture. */
    public function __construct(public ?string $fixturePath = null) {}

    public function run(): void
    {
        $path = $this->fixturePath ?? database_path('seeders/data/post-media.json');

        if (! File::exists($path)) {
            $this->command?->warn('No post media fixture — run `php artisan app:export-post-media`.');

            return;
        }

        $rows = json_decode((string) File::get($path), true);

        if (! is_array($rows)) {
            $this->command?->error('post-media.json is not valid JSON.');

            return;
        }

        $restored = 0;

        foreach ($rows as $row) {
            $slug = $row['slug'] ?? null;

            if (! is_string($slug)) {
                continue;
            }

            $post = Post::firstWhere('slug', $slug);

            // A post the seeder no longer creates (a retired slug still in an
            // old fixture) simply has nothing to attach to.
            if (! $post) {
                continue;
            }

            $restored += MediaSnapshot::restore($post, $row['media'] ?? []);
        }

        $this->command?->info("Restored {$restored} post cover row(s).");
    }
}
