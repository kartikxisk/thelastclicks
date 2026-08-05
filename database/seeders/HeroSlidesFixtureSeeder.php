<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Support\MediaSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Rebuilds the homepage hero from database/seeders/data/hero-slides.json.
 *
 * Not to be confused with HeroSlidesSeeder, which derives slides from featured
 * Work and stays outside db:seed. This one replays a captured hero, so a fresh
 * database comes up with the same background it had rather than a blank one —
 * hero.blade.php renders no background layer at all when there is no active
 * slide, which is correct behaviour but a poor way to launch a rebuilt site.
 *
 * Create-only, keyed on label. The hero is admin-managed: once a slide exists,
 * whoever owns it owns it, and a deploy must not overwrite their choice.
 * Refresh the fixture with `php artisan app:export-hero-slides`.
 */
class HeroSlidesFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/hero-slides.json');

        if (! File::exists($path)) {
            $this->command?->warn('No hero fixture — run `php artisan app:export-hero-slides` first.');

            return;
        }

        $rows = json_decode((string) File::get($path), true);

        if (! is_array($rows)) {
            $this->command?->error('hero-slides.json is not valid JSON.');

            return;
        }

        $slides = 0;
        $media = 0;

        foreach ($rows as $row) {
            $attributes = $row['attributes'] ?? [];
            $label = $attributes['label'] ?? null;

            if (! is_string($label) || $label === '') {
                continue;
            }

            if (HeroSlide::where('label', $label)->exists()) {
                continue;
            }

            $slide = HeroSlide::create($attributes);
            $slides++;
            $media += MediaSnapshot::restore($slide, $row['media'] ?? []);
        }

        $this->command?->info("Seeded {$slides} hero slide(s) and {$media} media row(s).");
    }
}
