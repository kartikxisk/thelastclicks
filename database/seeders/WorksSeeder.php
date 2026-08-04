<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use App\Models\Work;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Rebuilds the work archive from database/seeders/data/works.json.
 *
 * DatabaseSeeder covered roles, services, industries, clients and posts but
 * never works or their uploads, so `migrate:fresh --seed` produced a site with
 * an empty portfolio and 100+ files stranded on S3 with nothing pointing at
 * them. Refresh the fixture with `php artisan app:export-works`.
 *
 * Media rows are inserted with their original ids on purpose. Medialibrary's
 * default path generator is `{media id}/{file name}`, so reusing the id makes
 * the row resolve to the object already on S3 — nothing is re-uploaded and the
 * CloudFront URL is unchanged. addMediaFromUrl() would instead download and
 * re-upload every file under a fresh id, duplicating ~100 objects in the bucket.
 *
 * Idempotent: works are matched on slug, so re-running updates rather than
 * duplicating.
 */
class WorksSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/works.json');

        if (! File::exists($path)) {
            $this->command?->warn('No works fixture — run `php artisan app:export-works` first.');

            return;
        }

        /** @var list<array<string, mixed>>|null $rows */
        $rows = json_decode((string) File::get($path), true);

        if (! is_array($rows)) {
            $this->command?->error('works.json is not valid JSON.');

            return;
        }

        $works = 0;
        $media = 0;

        foreach ($rows as $row) {
            $attributes = $row['attributes'] ?? [];
            $slug = $attributes['slug'] ?? null;

            if (! is_string($slug) || $slug === '') {
                continue;
            }

            /** @var Work $work */
            $work = Work::updateOrCreate(['slug' => $slug], $attributes);
            $works++;

            $media += $this->attach($work, $row['media'] ?? []);

            // Replaced wholesale rather than merged: the fixture is the source of
            // truth for ordering, and matching child rows individually would need
            // an identity they do not carry.
            $work->mediaItems()->each(fn (MediaItem $item) => $item->delete());

            foreach ($row['media_items'] ?? [] as $itemRow) {
                /** @var MediaItem $item */
                $item = $work->mediaItems()->create($itemRow['attributes'] ?? []);
                $media += $this->attach($item, $itemRow['media'] ?? []);
            }
        }

        $this->command?->info("Seeded {$works} works and {$media} media rows.");
    }

    /**
     * Recreate media rows against an existing S3 object.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function attach(Work|MediaItem $owner, array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $id = $row['id'] ?? null;

            if (! is_int($id)) {
                continue;
            }

            // The id is the S3 directory. Keeping it is the whole point, so an
            // existing row with that id is left alone rather than duplicated.
            if (Media::query()->whereKey($id)->exists()) {
                continue;
            }

            Media::query()->create([
                'id' => $id,
                'model_type' => $owner->getMorphClass(),
                'model_id' => $owner->getKey(),
                'uuid' => (string) Str::uuid(),
                'collection_name' => $row['collection_name'] ?? 'default',
                'name' => $row['name'] ?? '',
                'file_name' => $row['file_name'] ?? '',
                'mime_type' => $row['mime_type'] ?? null,
                'disk' => $row['disk'] ?? config('media-library.disk_name'),
                'conversions_disk' => $row['conversions_disk'] ?? null,
                'size' => $row['size'] ?? 0,
                'manipulations' => $row['manipulations'] ?? [],
                'custom_properties' => $row['custom_properties'] ?? [],
                'generated_conversions' => $row['generated_conversions'] ?? [],
                'responsive_images' => $row['responsive_images'] ?? [],
                'order_column' => $row['order_column'] ?? null,
            ]);

            $count++;
        }

        return $count;
    }
}
