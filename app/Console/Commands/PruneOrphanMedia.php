<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Industry;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Delete media-disk objects nothing in the database points at any more.
 *
 * Replacing the portfolio archive left the previous set of uploads stranded on
 * S3: medialibrary removes a file when its row is deleted, but the older content
 * also referenced plain keys (portfolio/, videos/) that no model owns, so those
 * survive every delete and are invisible from the admin.
 *
 * The keep-set is built by ASKING THE DATABASE rather than by listing prefixes
 * to remove. That direction matters: a prefix blocklist silently deletes
 * anything nobody remembered to exclude, whereas an allowlist derived from live
 * rows can only ever be wrong in the safe direction — an orphan survives.
 *
 * Chrome (branding/, headers/, logo.png) is kept unconditionally on top of that.
 * Those are the brand logo, favicon and page-header images; several are
 * referenced only from SiteSetting values that this sweep also reads, but they
 * are pinned here as well because losing them breaks every page at once.
 *
 *     php artisan media:prune-orphans --dry-run
 *     php artisan media:prune-orphans
 */
class PruneOrphanMedia extends Command
{
    protected $signature = 'media:prune-orphans
        {--dry-run : List what would be deleted and delete nothing}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Delete media-disk objects no database row references any more';

    /**
     * Prefixes kept whatever the database says. Site chrome, not portfolio work.
     *
     * @var list<string>
     */
    private const ALWAYS_KEEP = ['branding/', 'headers/', 'logo.png', 'industries/'];

    public function handle(): int
    {
        $disk = Storage::disk((string) config('media-library.disk_name'));
        $keep = $this->referencedKeys();

        $all = $disk->allFiles();
        $orphans = [];
        $keptBytes = 0;
        $orphanBytes = 0;

        foreach ($all as $key) {
            $pinned = false;

            foreach (self::ALWAYS_KEEP as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $pinned = true;
                    break;
                }
            }

            if ($pinned || $keep->has($key)) {
                $keptBytes += $disk->size($key);

                continue;
            }

            $orphans[] = $key;
            $orphanBytes += $disk->size($key);
        }

        $this->info(sprintf(
            'objects=%d  keep=%d (%.2f GB)  orphan=%d (%.2f GB)',
            count($all),
            count($all) - count($orphans),
            $keptBytes / 1073741824,
            count($orphans),
            $orphanBytes / 1073741824,
        ));

        if ($orphans === []) {
            $this->info('Nothing to prune.');

            return self::SUCCESS;
        }

        foreach (array_slice($orphans, 0, 20) as $key) {
            $this->line('  '.$key);
        }

        if (count($orphans) > 20) {
            $this->line(sprintf('  … and %d more', count($orphans) - 20));
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — nothing deleted.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(sprintf('Permanently delete %d objects?', count($orphans)))) {
            $this->comment('Aborted.');

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($orphans as $key) {
            if ($disk->delete($key)) {
                $deleted++;
            }
        }

        $this->info(sprintf('Deleted %d objects, freeing %.2f GB.', $deleted, $orphanBytes / 1073741824));

        return self::SUCCESS;
    }

    /**
     * Every disk key a live row still points at.
     *
     * Covers all four ways this app references the media disk: medialibrary's
     * own `{id}/{file}` paths, the plain keys stored on Work::preview_video_url,
     * Industry::image_url, and the SiteSetting values behind the logo, favicon
     * and page headers.
     *
     * @return Collection<string, true>
     */
    private function referencedKeys(): Collection
    {
        $keys = collect();

        // Medialibrary stores the directory as the media id and the file name
        // separately; conversions and responsive images sit alongside the
        // original under the same directory, so the whole prefix is kept.
        foreach (Media::all() as $media) {
            $keys->put($media->id.'/'.$media->file_name, true);

            foreach (Storage::disk((string) config('media-library.disk_name'))->allFiles((string) $media->id) as $sibling) {
                $keys->put($sibling, true);
            }
        }

        $addRaw = function (mixed $value) use ($keys): void {
            // A column may hold a single key or a list of them (gallery_urls).
            foreach (is_array($value) ? $value : [$value] as $entry) {
                if (! is_string($entry) || blank($entry)) {
                    continue;
                }

                // Stored either as a bare disk key or as a full CDN URL —
                // normalise to the key, which is what a disk listing returns.
                $key = Str::startsWith($entry, ['http://', 'https://'])
                    ? ltrim((string) parse_url($entry, PHP_URL_PATH), '/')
                    : ltrim($entry, '/');

                if ($key !== '') {
                    $keys->put($key, true);
                }
            }
        };

        // Every plain-string reference to the media disk in the schema.
        //
        // Declared as a map rather than as a hand-written sequence of queries
        // because forgetting one entry here deletes live files: the first
        // version of this command omitted Client::logo_path, and a dry run
        // proposed deleting all eighteen client logos off the marquee. If a
        // column that points at the disk is added later, it belongs in here.
        $plainColumns = [
            Work::class => ['preview_video_url'],
            Industry::class => ['image_url'],
            Client::class => ['logo_path'],
            SeoPage::class => ['og_image_path', 'og_image_url'],
            Service::class => ['hero_url', 'gallery_urls'],
        ];

        foreach ($plainColumns as $model => $columns) {
            /** @var Model $instance */
            $instance = new $model;

            foreach ($instance->newQuery()->get($columns) as $row) {
                foreach ($columns as $column) {
                    // getAttribute rather than ->$column: the column set differs
                    // per model here, so a property access cannot be checked.
                    $addRaw($row->getAttribute($column));
                }
            }
        }

        foreach (SiteSetting::all() as $setting) {
            // Typed array on the model, but the column is a JSON blob an editor
            // can write, so the shape is a claim until it has been checked.
            /** @var mixed $value */
            $value = $setting->value_json;

            $addRaw(is_array($value) ? ($value['v'] ?? null) : $value);
        }

        return $keys;
    }
}
