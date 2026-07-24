<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    /**
     * Slugs retired from earlier seed versions. A targeted list rather than
     * "delete everything not in $rows" so industries added through the admin
     * panel survive every deploy.
     */
    protected array $retiredSlugs = [
        'corporate-events',
        'brands-products',
        'motion-post-production',
        'motion-graphics',
    ];

    public function run(): void
    {
        $rows = [
            ['corporate-enterprise', 'Corporate & Enterprise', 'Conferences, corporate films, town halls and internal comms — coverage that holds up to brand scrutiny.', 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=85'],
            ['brands-agencies', 'Brands & Agencies', 'Campaign films, product shoots and ecommerce content, delivered to agency spec.', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1200&q=85'],
            ['automobile-luxury', 'Automobile & Luxury', 'Automotive films and luxury launches — controlled light, controlled motion.', 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&q=85'],
            ['lifestyle-beverage', 'Lifestyle & Beverage', 'Food, beverage and lifestyle production, including regulated categories.', 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=1200&q=85'],
            ['weddings-celebrations', 'Weddings & Celebrations', 'Weddings, preweddings, anniversaries and birthdays — cinematic coverage for every celebration.', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1200&q=85'],
            ['fashion-creators', 'Fashion & Creators', 'Fashion shows, designer portfolios and influencer content.', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&q=85'],
            ['nightlife-entertainment', 'Nightlife & Entertainment', 'Clubbing, concerts, artists and festivals — high-energy live coverage.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1200&q=85'],
            ['spaces-interiors', 'Spaces & Interiors', 'Interior and decor shoots for hospitality, retail and residential spaces.', 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85'],
        ];

        foreach ($rows as $i => [$slug, $title, $summary, $image]) {
            Industry::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'summary' => $summary,
                'image_url' => $image,
                'body' => $this->body($title, $summary),
                'order' => $i,
            ]);
        }

        // Hydrate then delete through Eloquent (not a Builder delete) so `deleting`
        // fires and HasMediaItems' cascade cleans up media_items + medialibrary
        // files — a Builder ->delete() bypasses model events and leaks them.
        Industry::whereIn('slug', $this->retiredSlugs)->get()->each->delete();
    }

    /** Rich-text overview shown on the industry detail page. */
    protected function body(string $title, string $summary): string
    {
        return <<<HTML
        <p>{$summary}</p>
        <p>Every {$title} engagement runs on the same discipline: understand the brief, protect the brand, and deliver footage that holds up under scrutiny. We plan the shoot around the story, not the other way around.</p>
        <h3>What you get</h3>
        <ul>
            <li>A dedicated lead who owns the brief end to end</li>
            <li>In-house grading and finishing — never outsourced</li>
            <li>Crews that flex from a single operator to a full unit</li>
            <li>Delivery formats cut for every channel you run</li>
        </ul>
        <p>Bring us the objective and the constraints. We'll come back with a treatment, a timeline, and a number.</p>
        HTML;
    }
}
