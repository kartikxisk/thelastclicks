<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\WorkCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'weddings-celebrations' => ['Wedding', 'Prewedding', 'Anniversary', 'Birthday'],
            'corporate-events' => ['Corporate', 'INS Navy', 'Anchor', 'Podcast'],
            'brands-products' => ['Brands', 'Ecommerce', 'Product Shoots', 'Liquor Industry', 'Store & Brand Launch'],
            'fashion-creators' => ['Fashion Show', 'Designer', 'Influencer'],
            'nightlife-entertainment' => ['Clubbing', 'Concert & Artist', 'Festival'],
            'spaces-interiors' => ['Interior Shoots', 'Decor Shoots'],
            'motion-post-production' => ['Motion Graphics'],
        ];

        foreach ($map as $industrySlug => $titles) {
            $industry = Industry::where('slug', $industrySlug)->firstOrFail();
            foreach ($titles as $i => $title) {
                WorkCategory::updateOrCreate(
                    ['slug' => Str::slug($title)],
                    ['industry_id' => $industry->id, 'title' => $title, 'order' => $i],
                );
            }
        }
    }
}
