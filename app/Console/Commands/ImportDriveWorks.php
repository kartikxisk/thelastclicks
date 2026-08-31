<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Builds the portfolio from the client-works Drive export.
 *
 * The archive this replaces was a mix of seeded placeholder projects and real
 * client footage nobody had grouped. The studio's own client list — the one it
 * pitches from — arrived as a PDF of Drive links, so that list is now the
 * source of truth: database/seeders/data/drive-works.json carries one row per
 * project with the industries it belongs under.
 *
 * Reads the transcoded renditions produced by the ffmpeg pass, not the Drive
 * masters. Each project directory is expected to hold:
 *
 *     poster.jpg    -> the `cover` collection, used by the tile and as og:image
 *     preview.mp4   -> uploaded to portfolio/previews/, set as preview_video_url
 *     full.mp4      -> a `video` MediaItem, which is what the lightbox plays
 *
 * A project whose directory is missing or empty is still created, without media
 * and unpublished. That is deliberate: several Drive links were not shareable,
 * and a row waiting in the admin for an upload is more useful than a silent
 * omission — while publishing it would put a blank tile on a live grid.
 *
 * Idempotent. Works are matched on slug, and media is only uploaded when the
 * collection is empty, so a second run repairs what is missing and re-uploads
 * nothing. Re-run it after fixing a Drive permission and only that project moves.
 *
 *     php artisan works:import-drive --dry-run
 *     php artisan works:import-drive
 *
 * Afterwards run `php artisan app:export-works` to refresh works.json, which is
 * what actually rebuilds this content on a fresh database.
 */
class ImportDriveWorks extends Command
{
    protected $signature = 'works:import-drive
        {--dir= : Directory of transcoded renditions (default ~/thelastclicks-web)}
        {--manifest= : Path to the works manifest}
        {--dry-run : Report what would happen and write nothing}';

    protected $description = 'Create works from the client-works manifest, uploading their transcoded renditions';

    /** Where the tile hover loops live. A plain key, not a medialibrary row. */
    private const PREVIEW_PREFIX = 'portfolio/previews';

    public function handle(): int
    {
        $manifestPath = (string) ($this->option('manifest') ?: database_path('seeders/data/drive-works.json'));
        $dir = rtrim((string) ($this->option('dir') ?: getenv('HOME').'/thelastclicks-web'), '/');
        $dry = (bool) $this->option('dry-run');

        if (! File::exists($manifestPath)) {
            $this->error("No manifest at {$manifestPath}");

            return self::FAILURE;
        }

        // Typed loosely on purpose: this is decoded JSON from a file on disk, so
        // the shape is a claim until the guards below have checked it.
        /** @var mixed $manifest */
        $manifest = json_decode((string) File::get($manifestPath), true);

        if (! is_array($manifest) || ! is_array($manifest['works'] ?? null)) {
            $this->error('Manifest is not valid JSON, or has no `works` key.');

            return self::FAILURE;
        }

        $industries = Industry::pluck('id', 'slug');
        $groups = is_array($manifest['groups'] ?? null) ? $manifest['groups'] : [];

        foreach (array_keys($groups) as $slug) {
            if (! $industries->has($slug)) {
                $this->error("Manifest names industry `{$slug}`, which is not in the database. Run IndustriesSeeder first.");

                return self::FAILURE;
            }
        }

        $created = $updated = $withMedia = $withoutMedia = 0;
        $failed = [];

        foreach ($manifest['works'] as $i => $row) {
            $title = (string) $row['title'];
            $slug = Str::slug($title);
            // The renditions were foldered by the download script, whose slug rule
            // is not Str::slug's — it spells `&` as "and", so "Haldi & Mehandi"
            // landed in haldi-and-mehandi while Str::slug gives haldi-mehandi.
            // An explicit `dir` on the manifest entry beats teaching either side
            // about the other's rule.
            $source = $dir.'/'.((string) ($row['dir'] ?? $slug));
            $hasMedia = File::isDirectory($source) && File::exists($source.'/poster.jpg');

            $this->line(sprintf(
                '%2d. %-24s %s',
                $i + 1,
                Str::limit($title, 24),
                $hasMedia ? 'media ready' : 'NO MEDIA — will be created unpublished'
            ));

            if ($dry) {
                $hasMedia ? $withMedia++ : $withoutMedia++;

                continue;
            }

            try {
                $work = Work::where('slug', $slug)->first();
                $existed = (bool) $work;

                $work ??= new Work(['slug' => $slug]);
                $work->title = $title;
                // Only projects that actually have imagery go live. The rest wait
                // in the admin rather than rendering as a blank tile.
                $work->is_published = $hasMedia;
                $work->order = $i;
                $work->save();

                $existed ? $updated++ : $created++;

                if ($hasMedia) {
                    $this->attachMedia($work, $source);
                    $withMedia++;
                } else {
                    $withoutMedia++;
                }

                $ids = [];

                foreach ((array) ($row['groups'] ?? []) as $group) {
                    if ($id = $industries[$group] ?? null) {
                        $ids[] = $id;
                    }
                }

                // sync, not attach: re-running after a manifest correction should
                // move a project between industries rather than accumulate both.
                $work->industries()->sync($ids);
            } catch (Throwable $e) {
                $failed[] = $title.' — '.$e->getMessage();
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s created=%d updated=%d with-media=%d without-media=%d failed=%d',
            $dry ? 'DRY RUN' : 'Imported',
            $created, $updated, $withMedia, $withoutMedia, count($failed)
        ));

        foreach ($failed as $f) {
            $this->error('FAILED: '.$f);
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Attach the three renditions, skipping anything already present.
     *
     * The guards are what make a re-run cheap: medialibrary would happily add a
     * second cover and a second video row, leaving the tile showing whichever
     * won the sort and the lightbox playing the file twice.
     */
    private function attachMedia(Work $work, string $source): void
    {
        if ($work->getMedia('cover')->isEmpty() && File::exists($source.'/poster.jpg')) {
            $work->addMedia($source.'/poster.jpg')
                ->preservingOriginal()
                ->usingFileName($work->slug.'.jpg')
                ->toMediaCollection('cover');
        }

        // The hover loop is a plain object rather than a medialibrary row: it is
        // never resized, never converted, and previewVideoUrl() reads the column
        // directly. Stored under the same prefix the existing archive uses.
        if (blank($work->preview_video_url) && File::exists($source.'/preview.mp4')) {
            $key = self::PREVIEW_PREFIX.'/'.$work->slug.'.mp4';
            Storage::disk(config('media-library.disk_name'))
                ->put($key, File::get($source.'/preview.mp4'));

            // Absolute URL, matching every row already in the archive —
            // previewVideoUrl() returns this column verbatim.
            $work->preview_video_url = rtrim((string) config('filesystems.disks.s3.url'), '/').'/'.$key;
            $work->save();
        }

        $hasVideoRow = $work->mediaItems()->where('type', 'video')->exists();

        if (! $hasVideoRow && File::exists($source.'/full.mp4')) {
            $item = $work->mediaItems()->create(['type' => 'video', 'order' => 1]);
            $item->addMedia($source.'/full.mp4')
                ->preservingOriginal()
                ->usingFileName($work->slug.'.mp4')
                ->toMediaCollection('file');
        }
    }
}
