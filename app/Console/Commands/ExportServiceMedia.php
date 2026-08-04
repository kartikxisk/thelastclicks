<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Support\MediaSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Dumps service hero uploads to the fixture ServiceMediaSeeder replays.
 *
 * Keyed by slug rather than id, because ServicesSeeder recreates the rows and
 * their ids are not stable across a rebuild — the slug is.
 *
 *     php artisan app:export-service-media
 */
class ExportServiceMedia extends Command
{
    protected $signature = 'app:export-service-media {--path= : Where to write the fixture}';

    protected $description = 'Export service hero media rows for ServiceMediaSeeder';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: database_path('seeders/data/service-media.json'));

        $payload = Service::query()
            ->with('media')
            ->orderBy('id')
            ->get()
            ->map(fn (Service $s) => ['slug' => $s->slug, 'media' => MediaSnapshot::export($s)])
            ->filter(fn (array $row) => $row['media'] !== [])
            ->values()
            ->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $count = array_sum(array_map(fn (array $r) => count($r['media']), $payload));
        $this->info("Exported {$count} service media row(s) to {$path}");

        return self::SUCCESS;
    }
}
