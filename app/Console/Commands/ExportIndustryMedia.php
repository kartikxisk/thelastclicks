<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Support\MediaSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Dumps industry imagery to the fixture IndustryMediaSeeder replays.
 *
 * Two kinds of upload live under an industry and both were lost on a rebuild:
 * the cover in the 'hero' collection, and the ordered media items behind the
 * homepage artist reel. Keyed by slug, because IndustriesSeeder recreates the
 * rows and their ids are not stable across a rebuild — the slug is.
 *
 *     php artisan app:export-industry-media
 */
class ExportIndustryMedia extends Command
{
    protected $signature = 'app:export-industry-media {--path= : Where to write the fixture}';

    protected $description = 'Export industry cover and media-item rows for IndustryMediaSeeder';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: database_path('seeders/data/industry-media.json'));

        $payload = Industry::query()
            ->with(['media', 'mediaItems.media'])
            ->orderBy('id')
            ->get()
            ->map(fn (Industry $i) => [
                'slug' => $i->slug,
                'media' => MediaSnapshot::export($i),
                'media_items' => $i->mediaItems
                    ->sortBy('order')
                    ->map(fn ($item) => [
                        'attributes' => [
                            'type' => $item->type,
                            'youtube_url' => $item->youtube_url,
                            'caption' => $item->caption,
                            'order' => $item->order,
                        ],
                        'media' => MediaSnapshot::export($item),
                    ])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $row) => $row['media'] !== [] || $row['media_items'] !== [])
            ->values()
            ->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $covers = array_sum(array_map(fn (array $r) => count($r['media']), $payload));
        $items = array_sum(array_map(fn (array $r) => count($r['media_items']), $payload));
        $this->info("Exported {$covers} cover row(s) and {$items} media item(s) to {$path}");

        return self::SUCCESS;
    }
}
