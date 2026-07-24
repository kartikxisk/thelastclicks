<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Dev-only placeholder content: sample works plus YouTube reel galleries on
 * every work and industry, so the "Our Work" and industry detail pages have
 * something to render without any S3 uploads.
 *
 * Deliberately NOT wired into DatabaseSeeder — production seeds stay lean and
 * the test suite keeps its clean-slate baseline. Run on demand:
 *   php artisan db:seed --class=DummyMediaSeeder
 */
class DummyMediaSeeder extends Seeder
{
    /** Public-domain / demo reels — YouTube needs no file upload. */
    protected array $reelPool = [
        'https://youtu.be/aqz-KE-bpKQ',
        'https://youtu.be/ScMzIvxBSi4',
        'https://youtu.be/V-_O7nl0Ii0',
        'https://youtu.be/kJQP7kiw5Fk',
        'https://youtu.be/Sagg08DrO5U',
        'https://youtu.be/9bZkp7q19f0',
        'https://youtu.be/e-ORhEE9VVg',
        'https://youtu.be/ysz5S6PUM-U',
    ];

    public function run(): void
    {
        $this->seedWorks();
        $this->seedIndustryGalleries();
    }

    protected function seedWorks(): void
    {
        // [slug, title, summary, client, year, is_featured]
        $rows = [
            ['aurora-launch-film', 'Aurora — Launch Film', 'A 90-second launch film for a flagship beverage, shot across three cities.', 'Aurora Beverages', '2025', true],
            ['vanguard-brand-reel', 'Vanguard — Brand Reel', 'Corporate brand reel cut for the annual investor summit.', 'Vanguard Group', '2025', true],
            ['helios-motors-campaign', 'Helios Motors — Campaign', 'Automotive campaign film with controlled-light studio and highway plates.', 'Helios Motors', '2024', true],
            ['meher-weds-arjun', 'Meher & Arjun — Wedding Film', 'A three-day destination wedding, condensed into one cinematic story.', 'Private Commission', '2025', false],
            ['loom-fashion-week', 'Loom — Fashion Week', 'Runway coverage and backstage editorial for the SS25 showcase.', 'Loom Studio', '2025', false],
            ['nocturne-festival', 'Nocturne — Festival Aftermovie', 'High-energy aftermovie for a two-night electronic music festival.', 'Nocturne Live', '2024', false],
        ];

        foreach ($rows as $i => [$slug, $title, $summary, $client, $year, $featured]) {
            $work = Work::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'summary' => $summary,
                'client' => $client,
                'year' => $year,
                'order' => $i,
                'is_published' => true,
                'is_featured' => $featured,
            ]);

            $this->attachReels($work, $i, 3 + ($i % 3)); // 3–5 clips
        }
    }

    protected function seedIndustryGalleries(): void
    {
        foreach (Industry::orderBy('order')->orderBy('id')->get() as $i => $industry) {
            $this->attachReels($industry, $i, 3 + ($i % 2)); // 3–4 reels
        }
    }

    /** Rebuild a model's placeholder reel gallery idempotently (clear, then re-add). */
    protected function attachReels(Model $model, int $offset, int $count): void
    {
        $model->mediaItems()->get()->each->delete();

        for ($n = 0; $n < $count; $n++) {
            $url = $this->reelPool[($offset + $n) % count($this->reelPool)];
            $model->mediaItems()->create([
                'type' => 'youtube',
                'youtube_url' => $url,
                'caption' => $model->title.' — reel '.($n + 1),
                'order' => $n,
            ]);
        }
    }
}
