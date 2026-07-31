<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Models\Work;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Seeds the homepage hero from the studio's own featured work.
 *
 * The hero is the first thing anyone sees and it had no records at all, so the
 * homepage opened on a black rectangle. Rather than invent placeholder assets,
 * this copies the preview video and cover from the top featured projects —
 * material the studio already chose to lead with.
 *
 * Not called from DatabaseSeeder: it pulls real files over the network, which
 * has no place in a test database. Run once per environment, and afterwards
 * treat the slides as admin-owned — an editor replacing them under Hero Slides
 * is the expected end state, not a regression.
 *
 *     php artisan db:seed --class=HeroSlidesSeeder
 */
class HeroSlidesSeeder extends Seeder
{
    /** Enough to establish a rotation without turning the hero into a reel. */
    private const SLIDES = 3;

    public function run(): void
    {
        if (HeroSlide::exists()) {
            $this->command?->info('Hero slides already present — leaving them alone.');

            return;
        }

        $works = Work::published()
            ->where('is_featured', true)
            ->with(['media', 'mediaItems.media'])
            ->orderBy('order')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Work $work) => $work->coverUrl())
            ->take(self::SLIDES);

        if ($works->isEmpty()) {
            $this->command?->warn('No featured work with a cover — nothing to seed.');

            return;
        }

        foreach ($works->values() as $index => $work) {
            $slide = HeroSlide::create([
                'label' => $work->title,
                'order' => $index,
                'is_active' => true,
            ]);

            $video = $work->previewVideoUrl();
            $cover = $work->coverUrl();

            // The poster is always the still: it paints while a video buffers
            // and it is the LCP element on slide one, so it must never be the
            // thing that is still downloading.
            $this->attach($slide, $cover, 'poster');

            // Prefer the video as the asset; fall back to the still, which
            // leaves isVideo() false and the hero renders an image.
            $this->attach($slide, $video ?: $cover, 'asset');

            $this->command?->info("Hero slide {$index}: {$work->title}");
        }
    }

    /**
     * Copy a remote file into a collection.
     *
     * Failure is warned about rather than thrown: one unreachable asset should
     * not abort a seeder that has already created usable slides, and a slide
     * missing its video still renders from its poster.
     */
    private function attach(HeroSlide $slide, ?string $url, string $collection): void
    {
        if (blank($url)) {
            return;
        }

        try {
            $slide->addMediaFromUrl($url)->toMediaCollection($collection);
        } catch (Throwable $e) {
            $this->command?->warn("  {$collection} failed: {$e->getMessage()}");
        }
    }
}
