<?php

namespace App\Console\Commands;

use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Dumps the work archive to a JSON fixture that WorksSeeder replays.
 *
 * DatabaseSeeder never covered works or their media, so `migrate:fresh --seed`
 * rebuilt the site with an empty portfolio and the uploads orphaned on S3. This
 * captures the current state so a fresh database is a real rebuild.
 *
 * Media rows are exported WITH their ids. Medialibrary's default path generator
 * is `{media id}/{file name}`, so replaying the same id points the new row at
 * the object already sitting on S3 — no re-upload, no duplicate file, and the
 * CloudFront URL is byte-identical to the one that was there before.
 *
 *     php artisan app:export-works
 */
class ExportWorks extends Command
{
    protected $signature = 'app:export-works {--path= : Where to write the fixture}';

    protected $description = 'Export works, their media items and media rows to a JSON fixture for WorksSeeder';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: database_path('seeders/data/works.json'));

        $works = Work::query()
            ->with(['media', 'mediaItems.media', 'industries', 'services'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($works->isEmpty()) {
            $this->warn('No works to export.');

            return self::SUCCESS;
        }

        $payload = $works->map(fn (Work $work) => [
            'attributes' => collect($work->getAttributes())
                ->except(['id', 'created_at', 'updated_at'])
                ->all(),
            'media' => $work->media->map(fn (Media $m) => $this->media($m))->all(),
            // Pivots, by slug rather than id. Ids are assigned per environment,
            // so exporting them would attach a rebuilt archive to whichever
            // industry happened to land on that number. Omitting these was a
            // real bug: a `migrate:fresh --seed` restored every work with its
            // media intact and left every industry page empty, because nothing
            // walked the pivots.
            'industries' => $work->industries->pluck('slug')->all(),
            'services' => $work->services->pluck('slug')->all(),
            'media_items' => $work->mediaItems->map(fn ($item) => [
                'attributes' => collect($item->getAttributes())
                    ->except(['id', 'mediable_id', 'mediable_type', 'created_at', 'updated_at'])
                    ->all(),
                'media' => $item->media->map(fn (Media $m) => $this->media($m))->all(),
            ])->all(),
        ])->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $mediaCount = collect($payload)->sum(
            fn (array $w) => count($w['media']) + collect($w['media_items'])->sum(fn ($i) => count($i['media']))
        );

        $this->info("Exported {$works->count()} works and {$mediaCount} media rows to {$path}");

        return self::SUCCESS;
    }

    /**
     * The id is deliberately kept: it IS the S3 directory, so replaying it is
     * what makes the seeded row resolve to the existing object.
     *
     * @return array<string, mixed>
     */
    protected function media(Media $m): array
    {
        return [
            'id' => $m->id,
            'collection_name' => $m->collection_name,
            'name' => $m->name,
            'file_name' => $m->file_name,
            'mime_type' => $m->mime_type,
            'disk' => $m->disk,
            'conversions_disk' => $m->conversions_disk,
            'size' => $m->size,
            'manipulations' => $m->manipulations,
            'custom_properties' => $m->custom_properties,
            'generated_conversions' => $m->generated_conversions,
            'responsive_images' => $m->responsive_images,
            'order_column' => $m->order_column,
        ];
    }
}
