<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortfoliosSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first() ?? User::factory()->create();
        $serviceIds = Service::pluck('id', 'slug');

        $cases = [
            [
                'slug' => 'ins-navy', 'service' => 'videography',
                'title' => 'Indian Navy — event film', 'hero_html' => 'For the <em>Navy.</em>',
                'client' => 'Indian Navy', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Official event film delivered for the Indian Navy — full coverage, edit and finish handled in-house.</p>',
                'video' => 'ins-navy-blackdog',
            ],
            [
                'slug' => 'salesforce-blr', 'service' => 'videography',
                'title' => 'Salesforce — Bengaluru', 'hero_html' => 'Enterprise, <em>on film.</em>',
                'client' => 'Salesforce', 'year' => '2026', 'location' => 'Bengaluru',
                'body' => '<p>Corporate event film for Salesforce in Bengaluru — multi-camera coverage cut into a single recap.</p>',
                'video' => 'salesforce-blr',
            ],
            [
                'slug' => 'rahul-dravid-teaser', 'service' => 'videography',
                'title' => 'Rahul Dravid — teaser', 'hero_html' => 'The Wall, <em>rolling.</em>',
                'client' => 'Brand campaign', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Campaign teaser featuring Rahul Dravid — shot, edited and graded by TheLastClicks.</p>',
                'video' => 'rahul-dravid-teaser',
            ],
            [
                'slug' => 'range-rover', 'service' => 'videography',
                'title' => 'Range Rover — reel', 'hero_html' => 'Presence, <em>in motion.</em>',
                'client' => 'Range Rover', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Automotive social reel for Range Rover — shot vertical for platform-first delivery.</p>',
                'video' => 'range-rover',
            ],
            [
                'slug' => 'black-label', 'service' => 'videography',
                'title' => 'Black Label — brand reel', 'hero_html' => 'Poured, <em>not staged.</em>',
                'client' => 'Johnnie Walker Black Label', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Brand-event reel for Black Label — compliance-aware coverage for a regulated category.</p>',
                'video' => 'black-label',
            ],
            [
                'slug' => 'pramod-pooja-prewedding', 'service' => 'videography',
                'title' => 'Pramod & Pooja — pre-wedding', 'hero_html' => 'Before the <em>vows.</em>',
                'client' => 'Pramod & Pooja', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Pre-wedding film for Pramod & Pooja — story-led, shot and finished by TheLastClicks.</p>',
                'video' => 'prewedding-pramod-pooja',
            ],
            [
                'slug' => 'birthday-reel', 'service' => 'videography',
                'title' => 'Birthday — celebration reel', 'hero_html' => 'One night, <em>one reel.</em>',
                'client' => 'Private client', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Celebration reel cut for same-week delivery — vertical, platform-ready.</p>',
                'video' => 'birthday-reel',
            ],
            [
                'slug' => 'jw-fashion-show', 'service' => 'videography',
                'title' => 'Johnnie Walker — fashion show', 'hero_html' => 'Runway, <em>after dark.</em>',
                'client' => 'Johnnie Walker', 'year' => '2026', 'location' => 'India',
                'body' => '<p>Fashion-show coverage for a Johnnie Walker event — cut as a vertical highlight reel.</p>',
                'video' => 'jw-fashion-show',
            ],
            [
                'slug' => 'diwali-motion', 'service' => 'post-production',
                'title' => 'Diwali offer — motion graphics', 'hero_html' => 'Lit, <em>frame by frame.</em>',
                'client' => 'Brand campaign', 'year' => '2026', 'location' => 'Studio',
                'body' => '<p>Motion-graphics campaign film for a Diwali offer — designed and animated in-house.</p>',
                'video' => 'diwali-motion',
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
                'approach' => null,
                'credits' => ['Production' => 'TheLastClicks'],
                'cover_url' => '/videos/posters/'.$c['video'].'.jpg',
                'gallery_urls' => ['/videos/'.$c['video'].'.mp4'],
                'status' => 'published',
            ]);
        }

        $this->attachIndustries();
    }

    /** Place each seeded case under its industry. */
    protected function attachIndustries(): void
    {
        $industryBySlug = [
            'ins-navy' => 'corporate-events',
            'salesforce-blr' => 'corporate-events',
            'rahul-dravid-teaser' => 'brands-products',
            'range-rover' => 'brands-products',
            'black-label' => 'brands-products',
            'pramod-pooja-prewedding' => 'weddings-celebrations',
            'birthday-reel' => 'weddings-celebrations',
            'jw-fashion-show' => 'fashion-creators',
            'diwali-motion' => 'motion-post-production',
        ];
        foreach ($industryBySlug as $portfolioSlug => $industrySlug) {
            $industry = Industry::where('slug', $industrySlug)->first();
            if ($industry) {
                Portfolio::where('slug', $portfolioSlug)->update(['industry_id' => $industry->id]);
            }
        }
    }
}
