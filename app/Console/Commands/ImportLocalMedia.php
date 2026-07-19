<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ImportLocalMedia extends Command
{
    protected $signature = 'media:import-local
        {--source= : Directory holding the legacy video files (defaults to public/videos)}';

    protected $description = 'Attach legacy public/videos files to seeded portfolios as media and move public-disk media rows to the configured media disk';

    /** Seeded portfolio slug => legacy video basename (mirrors PortfoliosSeeder).
     * @var array<string, string>
     */
    protected array $videoMap = [
        'ins-navy' => 'ins-navy-blackdog',
        'salesforce-blr' => 'salesforce-blr',
        'rahul-dravid-teaser' => 'rahul-dravid-teaser',
        'range-rover' => 'range-rover',
        'black-label' => 'black-label',
        'pramod-pooja-prewedding' => 'prewedding-pramod-pooja',
        'birthday-reel' => 'birthday-reel',
        'jw-fashion-show' => 'jw-fashion-show',
        'diwali-motion' => 'diwali-motion',
    ];

    public function handle(): int
    {
        $source = rtrim($this->option('source') ?: public_path('videos'), '/');
        $failures = 0;

        foreach ($this->videoMap as $slug => $video) {
            $portfolio = Portfolio::where('slug', $slug)->first();

            if (! $portfolio) {
                $this->warn("skip {$slug}: portfolio not found");

                continue;
            }

            $failures += $this->attach($portfolio, 'cover', "{$source}/posters/{$video}.jpg");
            $failures += $this->attach($portfolio, 'gallery', "{$source}/{$video}.mp4");
        }

        $failures += $this->migratePublicDiskMedia();

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function attach(Portfolio $portfolio, string $collection, string $path): int
    {
        if ($portfolio->getMedia($collection)->isNotEmpty()) {
            $this->line("skip {$portfolio->slug}/{$collection}: already has media");

            return 0;
        }

        if (! is_file($path)) {
            $this->warn("skip {$portfolio->slug}/{$collection}: {$path} missing");

            return 0;
        }

        try {
            $portfolio->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
            $this->info("attached {$portfolio->slug}/{$collection}: ".basename($path));

            return 0;
        } catch (Throwable $e) {
            $this->error("FAILED {$portfolio->slug}/{$collection}: {$e->getMessage()}");

            return 1;
        }
    }

    protected function migratePublicDiskMedia(): int
    {
        $target = config('media-library.disk_name');

        if ($target === 'public') {
            return 0;
        }

        $failures = 0;

        Media::query()->where('disk', 'public')->eachById(function (Media $media) use ($target, &$failures) {
            try {
                $relative = $media->getPathRelativeToRoot();
                $stream = Storage::disk('public')->readStream($relative);

                if (! is_resource($stream)) {
                    throw new \RuntimeException('source file unreadable');
                }

                Storage::disk($target)->writeStream($relative, $stream);
                $media->update(['disk' => $target, 'conversions_disk' => $target]);
                $this->info("moved media #{$media->id} to {$target}");
            } catch (Throwable $e) {
                $failures++;
                $this->error("FAILED media #{$media->id}: {$e->getMessage()}");
            }
        });

        return $failures;
    }
}
