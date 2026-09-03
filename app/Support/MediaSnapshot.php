<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
        $collections = [];

        foreach ($rows as $row) {
            $id = $row['id'] ?? null;

            if (! is_int($id)) {
                continue;
            }

            $collections[] = $row['collection_name'] ?? 'default';
            $existing = Media::query()->whereKey($id)->first();

            // Already replayed here: the id IS the storage directory, so a row
            // holding it for this owner is left alone rather than duplicated.
            if ($existing && (int) $existing->model_id === (int) $owner->getKey()
                && $existing->model_type === $owner->getMorphClass()) {
                continue;
            }

            // The id is taken by something else entirely. Ids are only unique
            // within one database, and every environment auto-increments its
            // own: production had client logos sitting on the ids the homepage
            // artist frames were exported under, so those frames restored with
            // no file behind them and the reel rendered a single image. Take a
            // fresh id and bring the bytes along — a server-side copy on the
            // same disk, so nothing is downloaded or re-uploaded.
            $sourceId = $id;
            $useId = $existing ? null : $id;

            Media::query()->create([
                'id' => $useId,
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

            if ($existing) {
                /** @var Media $fresh */
                $fresh = Media::query()->latest('id')->firstOrFail();
                self::copyObject($row, $sourceId, (int) $fresh->id);
            }

            $created++;
        }

        self::pruneMissing($owner, array_unique($collections));

        return $created;
    }

    /**
     * Move the bytes to the directory the new id points at.
     *
     * @param  array<string, mixed>  $row
     */
    private static function copyObject(array $row, int $fromId, int $toId): void
    {
        $file = (string) ($row['file_name'] ?? '');
        $disk = (string) ($row['disk'] ?? config('media-library.disk_name'));

        if ($file === '') {
            return;
        }

        $from = "{$fromId}/{$file}";
        $to = "{$toId}/{$file}";
        $storage = Storage::disk($disk);

        if ($storage->exists($from) && ! $storage->exists($to)) {
            $storage->copy($from, $to);
        }
    }

    /**
     * Drop rows in the restored collections whose file is not on the disk.
     *
     * A fixture that named an id the bucket never had — or one whose object was
     * later pruned — leaves a row pointing at a 403. `hero` is a singleFile
     * collection, so the dead row does not merely sit there: it sorts ahead of
     * the good one and becomes the image the page renders. That is exactly what
     * blanked Post Production on production. Only rows with no object behind
     * them go; a real upload an editor made is never touched.
     *
     * @param  list<string>  $collections
     */
    private static function pruneMissing(Model $owner, array $collections): void
    {
        if ($collections === []) {
            return;
        }

        Media::query()
            ->where('model_type', $owner->getMorphClass())
            ->where('model_id', $owner->getKey())
            ->whereIn('collection_name', $collections)
            ->get()
            ->each(function (Media $media): void {
                $path = "{$media->id}/{$media->file_name}";

                if (! Storage::disk($media->disk)->exists($path)) {
                    $media->delete();
                }
            });
    }
}
