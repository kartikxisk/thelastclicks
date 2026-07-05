<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Database\Seeder;

class PortfoliosSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first() ?? User::factory()->create();
        $serviceIds = Service::pluck('id', 'slug');

        $cases = [
            [
                'slug' => 'atlas', 'service' => 'videography',
                'title' => 'Atlas — brand film', 'hero_html' => 'Atlas, in <em>motion.</em>',
                'client' => 'Atlas Studio', 'year' => '2026', 'location' => 'Mumbai · Goa',
                'body' => '<p>A two-week sprint to capture Atlas\'s flagship event — keynote, B-roll across three cities, customer interviews. We delivered a 90-second hero film, a 6-min keynote recap, and 14 social cutdowns within three weeks of wrap.</p><p>The treatment leaned cinematic: anamorphic 2.39:1, motivated lighting, no green-screen. Every frame had to feel earned.</p>',
                'approach' => '<p>We pre-built a 3-day shot board with the marketing team, then ran 2 cameras for keynote coverage and a separate B-roll unit for cutaways. Edit was on FCP with DaVinci grade, sound design from a custom score by Anaya Singh.</p>',
                'credits' => ['Director' => 'Aarav Khanna', 'DP' => 'Maya Iyer', 'Editor' => 'Rohan Bose', 'Color' => 'TLC Post', 'Sound' => 'Anaya Singh', 'Producer' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1600&q=85',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=85',
                    'https://images.unsplash.com/photo-1551434678-e076c223a692?w=2000&q=85',
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=85',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85',
                ],
            ],
            [
                'slug' => 'udaipur', 'service' => 'videography',
                'title' => 'Udaipur · S & R', 'hero_html' => 'A wedding, <em>by the lake.</em>',
                'client' => 'Sanya & Rohan', 'year' => '2026', 'location' => 'Udaipur',
                'body' => '<p>Five days, four events, two cities. We followed Sanya & Rohan from their Mumbai roka through the lakefront sangeet at Taj Lake Palace. The treatment: timeless, golden, never staged.</p>',
                'approach' => '<p>Three-shooter team, two on photo and one on film. Drone for arrivals only, never during ceremony. Same-day reel cut on a portable rig delivered before guests left.</p>',
                'credits' => ['Lead Photo' => 'Aarav K.', 'Lead Film' => 'Maya I.', 'Same-day Edit' => 'Rohan B.', 'Producer' => 'TLC Weddings'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1600&q=85',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=1600&q=85',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=1200&q=85',
                    'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&q=85',
                    'https://images.unsplash.com/photo-1606800052052-a08af7148866?w=1200&q=85',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=2000&q=85',
                ],
            ],
            [
                'slug' => 'aurelia', 'service' => 'videography',
                'title' => 'Aurelia GT reveal', 'hero_html' => 'Built for <em>speed.</em>',
                'client' => 'Aurelia Motors', 'year' => '2025', 'location' => 'Mumbai',
                'body' => '<p>Reveal film for the new Aurelia GT — closed-set studio shoot with a controlled-rolling sequence on the Bandra-Worli Sea Link at 4 AM.</p>',
                'approach' => '<p>Phantom Flex 4K for hero shots at 1000fps. 18m motion-control rig in the studio. Color graded for that signature deep cyan.</p>',
                'credits' => ['Director' => 'Maya I.', 'DP' => 'Aarav K.', 'VFX' => 'Pixelpaint', 'Producer' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=2200&q=85',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&q=85',
                    'https://images.unsplash.com/photo-1542362567-b07e54358753?w=1600&q=85',
                ],
            ],
            [
                'slug' => 'conf25', 'service' => 'videography',
                'title' => 'Annual Conference \'25', 'hero_html' => 'Three days. <em>Forty stages.</em>',
                'client' => 'Quanta Inc.', 'year' => '2025', 'location' => 'Bengaluru',
                'body' => '<p>End-to-end coverage of Quanta\'s flagship industry conference. 40 sessions, 8 stages, 12 shooters, 3-day same-day asset delivery.</p>',
                'approach' => '<p>Distributed crew with 1 floor producer per stage. Full-resolution stills from each session edited and delivered to the marketing portal within two hours of each session ending.</p>',
                'credits' => ['Field Producer' => 'Aarav K.', 'Lead Photo' => 'Maya I.', 'Post' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=85',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85',
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=2000&q=85',
                ],
            ],
            [
                'slug' => 'beverage', 'service' => 'photography',
                'title' => 'Premium beverage — campaign', 'hero_html' => 'Pour with <em>purpose.</em>',
                'client' => 'Solera & Co.', 'year' => '2025', 'location' => 'Goa',
                'body' => '<p>National launch campaign — 6 stills, 2 films, 1 director\'s cut. Set in a beach-cliff villa over 5 shoot days.</p>',
                'approach' => '<p>Macro tabletop unit for product, second unit for environmental and talent. Practical lighting only.</p>',
                'credits' => ['Director' => 'Aarav K.', 'DP' => 'Maya I.', 'Stylist' => 'Nisha Rao', 'Producer' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=2200&q=85',
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1600&q=85',
                    'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1600&q=85',
                ],
            ],
            [
                'slug' => 'reel', 'service' => 'post-production',
                'title' => 'Commercial reel', 'hero_html' => 'A year, in <em>frames.</em>',
                'client' => 'Various', 'year' => '2025', 'location' => 'India',
                'body' => '<p>Annual commercial reel — selected commercial work from 2025.</p>',
                'approach' => null,
                'credits' => ['Editor' => 'TLC Post'],
                'gallery_urls' => ['https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=2200&q=85'],
            ],
            [
                'slug' => 'editorial', 'service' => 'photography',
                'title' => 'Editorial — fashion', 'hero_html' => 'Cloth and <em>light.</em>',
                'client' => 'Indé Magazine', 'year' => '2025', 'location' => 'Mumbai',
                'body' => '<p>An 8-page editorial spread for Indé Magazine\'s autumn issue. Studio + rooftop, single day.</p>',
                'approach' => '<p>Hasselblad H6D-100c, mixed continuous and strobe. Minimal retouch — film-like skin tones.</p>',
                'credits' => ['Photographer' => 'Maya I.', 'Stylist' => 'Nisha Rao', 'Hair/Makeup' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=85',
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=85',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&q=85',
                ],
            ],
            [
                'slug' => 'goa', 'service' => 'photography',
                'title' => 'Goa · M & A', 'hero_html' => 'Seafoam and <em>sunlight.</em>',
                'client' => 'Meera & Aarav', 'year' => '2024', 'location' => 'Goa',
                'body' => '<p>A three-day beach wedding in South Goa. Quiet, tropical, sun-led.</p>',
                'approach' => '<p>Two photographers, one cinematographer. Drones for ceremony arrivals only. Same-day reel.</p>',
                'credits' => ['Lead Photo' => 'Aarav K.', 'Film' => 'Maya I.'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1600&q=85',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=1600&q=85',
                ],
            ],
            [
                'slug' => 'tech-keynote', 'service' => 'videography',
                'title' => 'Tech keynote — Mumbai', 'hero_html' => 'Ten thousand <em>watching.</em>',
                'client' => 'Quanta Inc.', 'year' => '2024', 'location' => 'Mumbai',
                'body' => '<p>Multi-cam keynote coverage — 90-min show, 8 cameras, live-mix delivered for stream and recap reel.</p>',
                'approach' => '<p>Tricaster live mix on-site. Recap reel cut overnight, delivered next morning.</p>',
                'credits' => ['TD' => 'Aarav K.', 'Lead Editor' => 'Rohan B.', 'Producer' => 'TLC Studio'],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=2200&q=85',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=85',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85',
                ],
            ],
        ];

        foreach ($cases as $c) {
            Portfolio::updateOrCreate(['slug' => $c['slug']], [
                'owner_id' => $owner->id,
                'service_id' => $serviceIds[$c['service']] ?? null,
                'title' => $c['title'],
                'hero_html' => $c['hero_html'],
                'client' => $c['client'],
                'year' => $c['year'],
                'location' => $c['location'],
                'body' => $c['body'],
                'approach' => $c['approach'],
                'credits' => $c['credits'],
                'cover_url' => $c['gallery_urls'][0] ?? null,
                'gallery_urls' => $c['gallery_urls'],
                'status' => 'published',
            ]);
        }

        $this->attachWorkCategories();
    }

    /** Place each seeded case under its real work category (and its industry). */
    protected function attachWorkCategories(): void
    {
        $categoryBySlug = [
            'atlas' => 'brands',
            'udaipur' => 'wedding',
            'aurelia' => 'brands',
            'conf25' => 'corporate',
            'beverage' => 'liquor-industry',
            'reel' => 'motion-graphics',
            'editorial' => 'fashion-show',
            'goa' => 'wedding',
            'tech-keynote' => 'corporate',
        ];
        foreach ($categoryBySlug as $portfolioSlug => $categorySlug) {
            $cat = WorkCategory::where('slug', $categorySlug)->first();
            if ($cat) {
                Portfolio::where('slug', $portfolioSlug)
                    ->update(['work_category_id' => $cat->id, 'industry_id' => $cat->industry_id]);
            }
        }
    }
}
