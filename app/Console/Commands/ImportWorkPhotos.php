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
 * Attach a folder of stills to an existing work as ordered image rows.
 *
 * The Drive import (works:import-drive) only ever knew about the three video
 * renditions, because that is all the client-works export carried. The artist
 * shoots arrive separately — a Drive folder of camera masters per performer —
 * and until now the only way in was dragging sixteen files through the admin
 * repeater one project at a time.
 *
 * Files are expected to be watermarked and web-sized already (scripts/
 * watermark-photos.sh does that pass); this command does no image processing,
 * it only uploads. Uploading a 7MB camera master would put the master itself
 * behind CloudFront, unwatermarked.
 *
 * Idempotent. Each file lands under a deterministic name — `<work>-<source>.jpg`
 * — and a name already present on the work is skipped, so a re-run after adding
 * three more photos uploads only those three instead of duplicating the set.
 *
 *     php artisan works:import-photos javed-ali --dir=~/photos/javed --dry-run
 *     php artisan works:import-photos javed-ali --dir=~/photos/javed --feature=DSC00261.jpg
 *
 * --feature additionally appends that one photo to the work's industry as an
 * image MediaItem, which is what the homepage artist band renders (see
 * home.blade.php — the band reads the industry's own media items, not the
 * works').
 *
 * Afterwards run `app:export-works` and `app:export-industry-media` to refresh
 * the fixtures, which is what rebuilds this on a fresh database.
 */
class ImportWorkPhotos extends Command
{
    protected $signature = 'works:import-photos
        {slug : Slug of the work to attach the photos to}
        {--dir= : Directory of prepared (watermarked, resized) jpgs}
        {--feature= : Filename in --dir to also append to the work\'s industry reel}
        {--cover= : Filename in --dir to promote to the work\'s tile cover}
        {--create : Create the work when the slug does not exist yet}
        {--title= : Title for a work being created (defaults to the slug, title-cased)}
        {--industry= : Industry slug to attach a created work to}
        {--dry-run : Report what would happen and write nothing}';

    protected $description = 'Attach a folder of prepared stills to a work as ordered image rows';

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $dir = rtrim((string) str_replace('~', (string) getenv('HOME'), (string) $this->option('dir')), '/');
        $dry = (bool) $this->option('dry-run');

        if (! File::isDirectory($dir)) {
            $this->error("No such directory: {$dir}");

            return self::FAILURE;
        }

        /** @var list<string> $files */
        $files = collect(File::files($dir))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->map(fn ($f) => $f->getPathname())
            ->sort()
            ->values()
            ->all();

        if ($files === []) {
            $this->error("No images in {$dir}");

            return self::FAILURE;
        }

        $work = Work::where('slug', $slug)->first();

        if (! $work && ! $this->option('create')) {
            $this->error("No work with slug `{$slug}`. Pass --create, or run works:import-drive.");

            return self::FAILURE;
        }

        if (! $work) {
            $industrySlug = (string) $this->option('industry');
            $industry = $industrySlug === '' ? null : Industry::firstWhere('slug', $industrySlug);

            if ($industrySlug !== '' && ! $industry) {
                $this->error("No industry with slug `{$industrySlug}`.");

                return self::FAILURE;
            }

            $title = (string) ($this->option('title') ?: Str::headline($slug));

            if ($dry) {
                $this->info("would create work `{$title}` ({$slug})");
            } else {
                $work = new Work(['title' => $title]);
                // Set after construction: slug is not fillable, and HasSlug would
                // otherwise derive one from the title — which is not necessarily
                // the slug the caller is importing under.
                $work->slug = $slug;
                // A stills project is published on creation because, unlike the
                // Drive import, the photos are in hand: there is no empty-tile
                // window to protect against.
                $work->is_published = true;
                $work->order = (int) Work::max('order') + 1;
                $work->save();

                $industry?->works()->attach($work);

                $this->info("created work `{$title}` ({$slug})".($industry ? " under {$industry->slug}" : ''));
            }
        }

        if ($dry && ! $work) {
            // Nothing to attach to yet; the rest of the run is a file-by-file
            // preview of what the created work would receive.
            foreach ($files as $path) {
                $this->info('would add '.$this->fileName($slug, $path));
            }

            return self::SUCCESS;
        }

        $feature = (string) $this->option('feature');

        if ($feature !== '' && ! File::exists($dir.'/'.$feature)) {
            $this->error("--feature={$feature} is not in {$dir}");

            return self::FAILURE;
        }

        // Existing file names, so a second run adds only what is new.
        $present = $work->mediaItems
            ->flatMap(fn ($item) => $item->getMedia('file')->pluck('file_name'))
            ->all();

        $order = (int) $work->mediaItems()->max('order');
        $added = $skipped = 0;
        $failed = [];

        foreach ($files as $path) {
            $name = $this->fileName($work->slug, $path);

            if (in_array($name, $present, true)) {
                $this->line("skip     {$name}");
                $skipped++;

                continue;
            }

            $this->info(($dry ? 'would add ' : 'add      ').$name);

            if ($dry) {
                $added++;

                continue;
            }

            try {
                $item = $work->mediaItems()->create(['type' => 'image', 'order' => ++$order]);
                $item->addMedia($path)
                    ->preservingOriginal()
                    ->usingFileName($name)
                    ->toMediaCollection('file');
                $added++;
            } catch (Throwable $e) {
                $failed[] = $name.' — '.$e->getMessage();
            }
        }

        $cover = (string) $this->option('cover');

        if ($cover !== '' && ! File::exists($dir.'/'.$cover)) {
            $this->error("--cover={$cover} is not in {$dir}");

            return self::FAILURE;
        }

        $featured = $feature === '' ? false : $this->feature($work, $dir.'/'.$feature, $dry);
        $covered = $cover === '' ? false : $this->cover($work, $dir.'/'.$cover, $dry);

        $this->newLine();
        $this->info(sprintf(
            '%s %s: added=%d skipped=%d failed=%d%s',
            $dry ? 'DRY RUN' : 'Imported',
            $work->slug, $added, $skipped, count($failed),
            ($featured ? ' (+1 industry frame)' : '').($covered ? ' (+cover)' : '')
        ));

        foreach ($failed as $f) {
            $this->error('FAILED: '.$f);
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Append one photo to the work's industry as an image row — the homepage
     * artist band. Keyed on the file name like everything else here, so running
     * the command twice does not put the same frame in the reel twice.
     */
    private function feature(Work $work, string $path, bool $dry): bool
    {
        $industry = $work->industries()->first();

        if (! $industry) {
            $this->warn('--feature ignored: this work is not attached to an industry.');

            return false;
        }

        $name = $this->fileName($work->slug, $path);

        $already = $industry->mediaItems
            ->flatMap(fn ($item) => $item->getMedia('file')->pluck('file_name'))
            ->contains($name);

        if ($already) {
            $this->line("skip     {$industry->slug} reel already has {$name}");

            return false;
        }

        $this->info(($dry ? 'would add ' : 'add      ')."{$name} -> {$industry->slug} reel");

        if ($dry) {
            return true;
        }

        $item = $industry->mediaItems()->create([
            'type' => 'image',
            'order' => (int) $industry->mediaItems()->max('order') + 1,
        ]);

        $item->addMedia($path)
            ->preservingOriginal()
            ->usingFileName($name)
            ->toMediaCollection('file');

        return true;
    }

    /**
     * Promote one photo to the work's tile cover.
     *
     * `cover` is a singleFile() collection, so medialibrary drops the previous
     * row and its object as part of the swap — and that object is shared. Every
     * database built from works.json resolves the cover through the media id, so
     * production's tile is pointed at the very same `138/javed-ali.jpg` this
     * delete would remove, and it would start rendering a broken image the
     * moment this command ran against a local database. The bytes are therefore
     * read first and written back to the old key afterwards: the row is gone
     * here, the object stays for whoever else still references it.
     *
     * That leaves an object with no row behind it in this database, which is
     * exactly what media:prune-orphans looks for — do not point that command at
     * this bucket while production is still on the old cover.
     */
    private function cover(Work $work, string $path, bool $dry): bool
    {
        $name = $this->fileName($work->slug, $path);
        $current = $work->getFirstMedia('cover');

        if ($current && $current->file_name === $name) {
            $this->line("skip     cover already {$name}");

            return false;
        }

        $this->info(($dry ? 'would set ' : 'set      ').$name.' as cover');

        if ($dry) {
            return true;
        }

        $keep = null;

        if ($current) {
            $disk = Storage::disk($current->disk);
            $key = $current->id.'/'.$current->file_name;

            if ($disk->exists($key)) {
                $keep = [$current->disk, $key, $disk->get($key)];
            }
        }

        $work->addMedia($path)
            ->preservingOriginal()
            ->usingFileName($name)
            ->toMediaCollection('cover');

        if ($keep) {
            [$keepDisk, $keepKey, $bytes] = $keep;
            Storage::disk($keepDisk)->put($keepKey, $bytes);
            $this->line("kept     {$keepKey} on the disk for databases still pointing at it");
        }

        return true;
    }

    /** Deterministic object name: `<work>-<source>.jpg`, which is what makes a re-run a no-op. */
    private function fileName(string $slug, string $path): string
    {
        $base = pathinfo($path, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $slug.'-'.Str::slug($base).'.'.$ext;
    }
}
