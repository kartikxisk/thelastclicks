<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Export and replay medialibrary rows without moving a single byte.
 *
 * The whole trick is the id. Medialibrary's default path generator is
 * `{media id}/{file name}`, so a row recreated under its original id resolves to
 * the object already sitting on S3: nothing is downloaded, nothing is
 * re-uploaded, no duplicate objects appear in the bucket, and the CloudFront URL
 * comes out byte-identical to the one that was there before.
 *
 * addMediaFromUrl() would do the opposite — fetch every file and push it back up
 * under a fresh id, leaving the originals orphaned.
 *
 * Both halves live here so an export and the seeder that replays it cannot drift
 * into disagreeing about the shape.
 */
final class MediaSnapshot
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function export(HasMedia&Model $model): array
    {
        // Queried rather than read off the relation or getMedia(). The `media`
        // relation is declared by the trait, not the HasMedia interface, so the
        // intersection type does not carry it — and getMedia() defaults to the
        // 'default' collection, which silently exported nothing for a hero image
        // filed under 'hero'. This is collection-agnostic by construction.
        return Media::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->orderBy('id')
            ->get()
            ->map(fn (Media $m) => [
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
            ])
            ->values()
            ->all();
    }

    /**
     * Recreate rows against the objects already on the disk.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int how many were created
     */
    public static function restore(Model $owner, array $rows): int
    {
        $created = 0;

        foreach ($rows as $row) {
            $id = $row['id'] ?? null;

            if (! is_int($id)) {
                continue;
            }

            // The id IS the storage directory, so a row already holding it is
            // left alone rather than duplicated or overwritten.
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

            $created++;
        }

        return $created;
    }
}
