<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Support\MediaSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Re-attaches service hero uploads to a rebuilt database.
 *
 * ServicesSeeder recreates the service rows, but a hero image uploaded through
 * the admin is a medialibrary row it knows nothing about — so a fresh database
 * came up with the service pages present and their headers blank, while the
 * files sat untouched on S3.
 *
 * Rows are replayed under their original ids, which is what makes the URL
 * resolve to the object already in the bucket rather than needing a re-upload.
 * See MediaSnapshot for why the id is load-bearing.
 *
 * Refresh the fixture with `php artisan app:export-service-media`.
 */
class ServiceMediaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/service-media.json');

        if (! File::exists($path)) {
            $this->command?->warn('No service media fixture — run `php artisan app:export-service-media`.');

            return;
        }

        $rows = json_decode((string) File::get($path), true);

        if (! is_array($rows)) {
            $this->command?->error('service-media.json is not valid JSON.');

            return;
        }

        $restored = 0;

        foreach ($rows as $row) {
            $slug = $row['slug'] ?? null;

            if (! is_string($slug)) {
                continue;
            }

            $service = Service::firstWhere('slug', $slug);

            // A service the seeder no longer creates (a retired slug still in an
            // old fixture) simply has nothing to attach to.
            if (! $service) {
                continue;
            }

            $restored += MediaSnapshot::restore($service, $row['media'] ?? []);
        }

        $this->command?->info("Restored {$restored} service media row(s).");
    }
}
