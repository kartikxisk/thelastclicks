<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\MediaSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Dumps journal covers to the fixture PostMediaSeeder replays.
 *
 * Keyed by slug rather than id, because PostsSeeder recreates the rows and
 * their ids are not stable across a rebuild — the slug is.
 *
 *     php artisan app:export-post-media
 */
class ExportPostMedia extends Command
{
    protected $signature = 'app:export-post-media {--path= : Where to write the fixture}';

    protected $description = 'Export journal cover media rows for PostMediaSeeder';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: database_path('seeders/data/post-media.json'));

        $payload = Post::query()
            ->with('media')
            ->orderBy('id')
            ->get()
            ->map(fn (Post $p) => ['slug' => $p->slug, 'media' => MediaSnapshot::export($p)])
            ->filter(fn (array $row) => $row['media'] !== [])
            ->values()
            ->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $count = array_sum(array_map(fn (array $r) => count($r['media']), $payload));
        $this->info("Exported {$count} post cover row(s) to {$path}");

        return self::SUCCESS;
    }
}
