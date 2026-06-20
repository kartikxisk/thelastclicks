<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['corporate-conferences', 'Corporate Conferences', 'Multi-day conferences, executive summits, internal events.', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=85'],
            ['brand-launches', 'Brand Launches', 'Press events, product reveals, brand activations.', 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'],
            ['automobile-showcases', 'Automobile Showcases', 'Luxury car unveils, auto-expos, lifestyle shoots.', 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=85'],
            ['lifestyle-beverage', 'Lifestyle & Beverage', 'Premium beverage campaigns and lifestyle brand content.', 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1200&q=85'],
            ['destination-weddings', 'Destination Weddings', 'Cinematic coverage for intimate and grand celebrations.', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&q=85'],
            ['commercial-productions', 'Commercial Productions', 'Ad films, social media content, brand storytelling.', 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1200&q=85'],
        ];
        foreach ($rows as $i => [$slug, $title, $summary, $image]) {
            Industry::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'summary' => $summary, 'image_url' => $image, 'body' => '', 'order' => $i,
            ]);
        }
    }
}
