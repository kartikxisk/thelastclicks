<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['weddings-celebrations', 'Weddings & Celebrations', 'Weddings, preweddings, anniversaries and birthdays — cinematic coverage for every celebration.', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&q=85'],
            ['corporate-events', 'Corporate & Events', 'Conferences, corporate films, naval ceremonies, anchors and podcast productions.', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=85'],
            ['brands-products', 'Brands & Products', 'Brand campaigns, ecommerce, product shoots, liquor industry and store launches.', 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'],
            ['fashion-creators', 'Fashion & Creators', 'Fashion shows, designer portfolios and influencer content.', 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&q=85'],
            ['nightlife-entertainment', 'Nightlife & Entertainment', 'Clubbing, concerts, artists and festivals — high-energy live coverage.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1200&q=85'],
            ['spaces-interiors', 'Spaces & Interiors', 'Interior and decor shoots for hospitality, retail and residential spaces.', 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85'],
            ['motion-post-production', 'Motion & Post-Production', 'Motion graphics and post-production — where every frame gets finished.', 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1200&q=85'],
        ];
        foreach ($rows as $i => [$slug, $title, $summary, $image]) {
            Industry::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'summary' => $summary, 'image_url' => $image, 'body' => '', 'order' => $i,
            ]);
        }

        // Retire placeholder industries from earlier seed versions.
        Industry::whereNotIn('slug', array_column($rows, 0))->delete();
    }
}
