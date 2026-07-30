<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use App\Models\Work;
use Database\Seeders\DevWorksSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Import a batch of finished films into the portfolio from a build manifest.
 *
 * The manifest is produced by transcoding masters down to web MP4 + poster;
 * this command only takes the finished files and turns them into Work rows.
 *
 * Manifest entries may point at local paths OR at http(s) URLs. That is what
 * makes the same import replayable on a server that has none of the masters:
 * the local run writes a second manifest (`--emit-remote`) whose paths are the
 * CDN copies it just uploaded, and the server replays that one.
 *
 * Idempotent: rows are keyed on slug, and a re-run replaces that work's media
 * rather than stacking duplicates. Safe to run repeatedly while curating.
 */
class ImportPortfolioCommand extends Command
{
    protected $signature = 'portfolio:import
        {manifest : Path to the JSON manifest produced by the transcode step}
        {--previews= : Directory of 6s <slug>.mp4 hover loops to upload alongside the films}
        {--emit-remote= : Write a replayable manifest of CDN URLs to this path}
        {--dry-run : List what would be created without touching the database or S3}
        {--unpublish-dev : Unpublish any leftover DevWorksSeeder placeholder rows}';

    protected $description = 'Create portfolio works from a transcoded video manifest';

    /** Where hover-preview loops live on S3. Stable keys, so a re-run overwrites. */
    private const PREVIEW_PREFIX = 'portfolio/previews';

    /** Highest `order` seen so far, so imported works queue after existing ones. */
    private int $order = 0;

    /** Verticals already given a homepage slot this run. @var array<string, true> */
    private array $seenVerticals = [];

    /** Temp files downloaded for the row in flight. @var list<string> */
    private array $temp = [];

    public function handle(): int
    {
        $rows = $this->readManifest((string) $this->argument('manifest'));

        if ($rows === null) {
            return self::FAILURE;
        }

        $this->order = (int) Work::max('order');
        $dry = (bool) $this->option('dry-run');
        $remote = [];
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            if (! $this->readable($row['mp4'])) {
                $this->warn("Skipping {$row['slug']} — video missing at {$row['mp4']}");

                continue;
            }

            // First film of each vertical carries the homepage; the rest fill the grid.
            $featured = ! isset($this->seenVerticals[$row['vertical']]);
            $this->seenVerticals[$row['vertical']] = true;

            if ($dry) {
                $this->line(sprintf(
                    '  %-46s %-12s %s%s',
                    $row['slug'], $row['category'], $row['vertical'], $featured ? '  [homepage]' : ''
                ));

                continue;
            }

            $existing = Work::where('slug', $row['slug'])->exists();
            $remote[] = $this->importRow($row, $featured);
            $existing ? $updated++ : $created++;

            $this->line(sprintf('  %s %s', $existing ? '~' : '+', $row['slug']));
        }

        if ($dry) {
            $this->info(count($rows).' works would be imported.');

            return self::SUCCESS;
        }

        if ($out = $this->option('emit-remote')) {
            file_put_contents($out, json_encode($remote, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Replayable manifest written to '.$out);
        }

        if ($this->option('unpublish-dev')) {
            $n = Work::where('slug', 'like', DevWorksSeeder::PREFIX.'%')
                ->update(['is_published' => false, 'is_featured' => false]);
            $this->info("Unpublished {$n} DevWorksSeeder placeholder works.");
        }

        $this->info("Imported: {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>|null null when the manifest is unusable
     */
    private function readManifest(string $path): ?array
    {
        if (! is_file($path)) {
            $this->error("Manifest not found: {$path}");

            return null;
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows) || $rows === []) {
            $this->error('Manifest is empty or unreadable.');

            return null;
        }

        return $rows;
    }

    /**
     * Create/refresh one work and its media.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed> the same row rewritten to CDN URLs
     */
    private function importRow(array $row, bool $featured): array
    {
        // Pull remote sources down BEFORE anything is deleted. A replayed
        // manifest can name the very media rows this import is about to clear —
        // fetch first and the source is already safely on disk either way.
        $mp4 = $this->localCopy($row['mp4']);
        $poster = $row['poster'] ? $this->localCopy($row['poster']) : null;

        $work = Work::updateOrCreate(['slug' => $row['slug']], [
            'title' => $row['title'],
            'client' => $row['client'] ?? null,
            'summary' => $row['vertical'].' — shot, cut and graded in-house.',
            'category' => $row['category'],
            'crafts' => $row['crafts'],
            'year' => $this->guessYear($row['source']),
            // A replayed manifest already carries a CDN preview; a local run has
            // to upload one first.
            'preview_video_url' => $row['preview'] ?? $this->uploadPreview($row['slug']),
            'order' => ++$this->order,
            'is_published' => true,
            'is_featured' => $featured,
        ]);

        // Rebuild media so a re-run replaces rather than appends. Eloquent delete
        // (not a mass delete) so medialibrary clears S3 behind it.
        $work->mediaItems()->cursor()->each->delete();
        $work->clearMediaCollection('cover');

        if ($poster) {
            $work->addMedia($poster)->preservingOriginal()
                ->usingFileName($row['slug'].'.jpg')->toMediaCollection('cover');
        }

        $item = MediaItem::create([
            'mediable_type' => $work->getMorphClass(),
            'mediable_id' => $work->id,
            'type' => 'video',
            'caption' => $row['title'],
            'order' => 0,
        ]);

        $item->addMedia($mp4)->preservingOriginal()
            ->usingFileName($row['slug'].'.mp4')->toMediaCollection('file');

        $this->discardTemp();

        return [
            'slug' => $row['slug'],
            'title' => $row['title'],
            'client' => $row['client'] ?? null,
            'source' => $row['source'],
            'vertical' => $row['vertical'],
            'category' => $row['category'],
            'crafts' => $row['crafts'],
            'mp4' => $item->getFirstMediaUrl('file'),
            'poster' => $work->getFirstMediaUrl('cover') ?: null,
            'preview' => $work->preview_video_url,
        ];
    }

    /**
     * A local path for a manifest entry, downloading it first when it is a URL.
     * Downloads are tracked so discardTemp() can clean up after the row lands.
     */
    private function localCopy(string $source): string
    {
        if (! $this->isRemote($source)) {
            return $source;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'tlc-import-');

        // Streamed to disk rather than held in memory — these are 20-70 MB films.
        // An explicit User-Agent is required: CloudFront answers 403 to a request
        // that sends none, which is what PHP's bare fopen() wrapper does.
        $response = Http::withHeaders(['User-Agent' => 'TheLastClicks/portfolio-import'])
            ->timeout(300)
            ->sink($tmp)
            ->get($source);

        if (! $response->successful()) {
            @unlink($tmp);

            throw new RuntimeException("Cannot read {$source} — HTTP {$response->status()}");
        }

        $this->temp[] = $tmp;

        return $tmp;
    }

    /** Drop the downloads made for the row that has just been imported. */
    private function discardTemp(): void
    {
        foreach ($this->temp as $file) {
            @unlink($file);
        }

        $this->temp = [];
    }

    /** True when the manifest entry names something we can actually read. */
    private function readable(?string $source): bool
    {
        return $source !== null && ($this->isRemote($source) || is_file($source));
    }

    private function isRemote(string $source): bool
    {
        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://');
    }

    /**
     * Push this work's hover loop to a stable S3 key and hand back its CDN URL.
     * Null when no preview directory was given or the clip is missing — the grid
     * tile then falls back to its poster still, which is the correct degradation.
     */
    private function uploadPreview(string $slug): ?string
    {
        $dir = $this->option('previews');

        if (! $dir || ! is_file($local = rtrim($dir, '/')."/{$slug}.mp4")) {
            return null;
        }

        $key = self::PREVIEW_PREFIX."/{$slug}.mp4";
        Storage::disk('s3')->put($key, (string) file_get_contents($local), ['ContentType' => 'video/mp4']);

        return rtrim((string) config('filesystems.disks.s3.url'), '/').'/'.$key;
    }

    /** Year baked into the filename if there is one, else the current year. */
    private function guessYear(string $source): string
    {
        return preg_match('~\b(20[12]\d)\b~', $source, $m) ? $m[1] : (string) now()->year;
    }
}
