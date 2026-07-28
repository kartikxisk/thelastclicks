<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use App\Models\Work;
use Illuminate\Database\Seeder;

/**
 * DEVELOPMENT ONLY — 30 placeholder works so the portfolio grid, the homepage
 * collage and the lightbox have something to render.
 *
 * Deliberately NOT wired into DatabaseSeeder: run it by hand with
 *   php artisan db:seed --class=DevWorksSeeder
 * and remove the rows with
 *   php artisan db:seed --class=DevWorksSeeder -- --fresh   (see below)
 *
 * Covers come from YouTube poster URLs rather than uploaded files, so the
 * seeder writes nothing to S3 or the local media disk and needs no fixtures.
 * core.js already falls back from maxresdefault to hqdefault when a poster is
 * missing, so every tile resolves to a real image.
 *
 * Every row is keyed on a `dev-` slug prefix, so re-running updates in place
 * instead of duplicating, and the teardown below can find them again.
 */
class DevWorksSeeder extends Seeder
{
    /** Slug prefix that marks a row as disposable dev data. */
    public const PREFIX = 'dev-';

    /**
     * Blender Foundation open movies and other CC-licensed shorts. Chosen
     * because they permit embedding — commercial music videos serve a poster
     * frame happily but refuse the iframe, so the lightbox opens onto "Video
     * unavailable" and you can't tell a broken player from blocked content.
     */
    private const VIDEO_IDS = [
        'YE7VzlLtp-4', // Big Buck Bunny
        'eRsGyueVLvQ', // Sintel
        'R6MlUcmOul8', // Tears of Steel
        'TLkA0RELQ1g', // Elephants Dream
        'Y-rmzh0PI3c', // Cosmos Laundromat
        'WhWc3b3KhnY', // Spring
        'mN0zPOpADL4', // Agent 327
        'Gr3O0Fp3VXo', // Caminandes 3
        'MoQeUhtlvfM', // Coffee Run
        'UXqq0ZvbOnk', // Charge
        'ZfPBriZQviU', // Hero
        'pKmSdY56VtY', // Wing It!
    ];

    private const TITLES = [
        'Northlight Launch Film', 'Meridian Brand Campaign', 'Aurora Motors Reveal',
        'Saffron House Wedding', 'Vertex Annual Summit', 'Halcyon Product Story',
        'Ember & Oak Editorial', 'Bluewave Music Video', 'Copperfield Corporate Reel',
        'Lantern Festival Coverage', 'Ironclad Defence Feature', 'Sable Interiors Tour',
        'Quill Publishing Portrait', 'Monsoon Collection Lookbook', 'Granite Group Report',
        'Willow Lane Ceremony', 'Nightshift Club Sessions', 'Terra Firma Documentary',
        'Cobalt Watches Commercial', 'Harbour City Timelapse', 'Palladium Gala',
        'Fern & Fig Restaurant Film', 'Skyline Realty Walkthrough', 'Vanta Sportswear Spot',
        'Solstice Retreat Story', 'Kestrel Aviation Feature', 'Marble Arch Fashion Week',
        'Driftwood Coastal Series', 'Union Square Activation', 'Zenith Tech Keynote',
    ];

    private const CLIENTS = [
        'Northlight', 'Meridian Group', 'Aurora Motors', 'Private Client',
        'Vertex Industries', 'Halcyon', 'Ember & Oak', 'Bluewave Records',
        'Copperfield', 'City of Lanterns', 'Ministry of Defence', 'Sable Studio',
    ];

    private const LOCATIONS = [
        'Mumbai', 'New Delhi', 'Bengaluru', 'Goa', 'Jaipur',
        'Hyderabad', 'Chennai', 'Udaipur', 'Pune', 'Kolkata',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DevWorksSeeder refuses to run in production.');

            return;
        }

        $categories = array_keys(Work::CATEGORIES);
        $crafts = array_keys(Work::CRAFTS);

        foreach (self::TITLES as $i => $title) {
            $slug = self::PREFIX.str($title)->slug();

            $work = Work::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'summary' => 'Placeholder summary for '.$title.'. Development data — safe to delete.',
                'client' => self::CLIENTS[$i % count(self::CLIENTS)],
                'category' => $categories[$i % count($categories)],
                // Two crafts each, rotating, so the craft filter has real spread.
                'crafts' => [
                    $crafts[$i % count($crafts)],
                    $crafts[($i + 2) % count($crafts)],
                ],
                'credits' => [
                    ['role' => 'Director', 'name' => 'A. Placeholder'],
                    ['role' => 'DOP', 'name' => 'B. Placeholder'],
                ],
                'location' => self::LOCATIONS[$i % count(self::LOCATIONS)],
                'agency' => $i % 3 === 0 ? 'In-house' : null,
                'year' => (string) (2026 - ($i % 4)),
                'order' => $i,
                'is_published' => true,
                // First six carry the homepage; the controller falls back to
                // recent works anyway, but this exercises the featured path.
                'is_featured' => $i < 6,
            ]);

            // Rebuild the media rows so a re-run can't stack duplicates.
            $work->mediaItems()->cursor()->each->delete();

            // Two or three rows each, so the lightbox has something to page through.
            $count = 2 + ($i % 2);
            for ($n = 0; $n < $count; $n++) {
                MediaItem::create([
                    'mediable_type' => $work->getMorphClass(),
                    'mediable_id' => $work->id,
                    'type' => 'youtube',
                    'youtube_url' => 'https://www.youtube.com/watch?v='
                        .self::VIDEO_IDS[($i + $n) % count(self::VIDEO_IDS)],
                    'caption' => $title.' — frame '.($n + 1),
                    'order' => $n,
                ]);
            }
        }

        $this->command?->info('Seeded '.count(self::TITLES).' dev works (slug prefix "'.self::PREFIX.'").');
        $this->command?->line('Remove them with: php artisan tinker --execute="App\\Models\\Work::where(\'slug\',\'like\',\''.self::PREFIX.'%\')->cursor()->each->delete();"');
    }
}
