<?php

namespace App\Console\Commands;

use App\Models\HeroSlide;
use App\Support\MediaSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Dumps the homepage hero to the fixture HeroSlidesFixtureSeeder replays.
 *
 * Distinct from HeroSlidesSeeder, which derives slides from featured Work and
 * is deliberately not part of db:seed. This captures whatever is actually on the
 * hero right now, whether that came from an admin upload or anywhere else, so a
 * rebuilt database comes up with the same hero rather than a blank one.
 *
 *     php artisan app:export-hero-slides
 */
class ExportHeroSlides extends Command
{
    protected $signature = 'app:export-hero-slides {--path= : Where to write the fixture}';

    protected $description = 'Export hero slides and their media for HeroSlidesFixtureSeeder';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: database_path('seeders/data/hero-slides.json'));

        $payload = HeroSlide::query()
            ->with('media')
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(fn (HeroSlide $slide) => [
                'attributes' => collect($slide->getAttributes())
                    ->except(['id', 'created_at', 'updated_at'])
                    ->all(),
                'media' => MediaSnapshot::export($slide),
            ])
            ->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $media = array_sum(array_map(fn (array $r) => count($r['media']), $payload));
        $this->info('Exported '.count($payload)." hero slide(s) and {$media} media row(s) to {$path}");

        return self::SUCCESS;
    }
}
