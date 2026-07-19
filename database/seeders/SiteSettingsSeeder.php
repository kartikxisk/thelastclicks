<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::set('contact_email', 'hello@thelastclicks.com');
        SiteSetting::set('contact_phone', '+91-87701-55842');
        SiteSetting::set('whatsapp_url', 'https://wa.me/918770155842');
        SiteSetting::set('socials', [
            'instagram' => 'https://instagram.com/thelastclicks',
            'youtube' => 'https://youtube.com/@thelastclicks',
        ]);
        SiteSetting::set('seo_default_title', 'TheLastClicks — Cinematic photography & film production');
        SiteSetting::set('seo_default_description', 'Cinematic photography, brand films and post-production for premium teams.');

        SiteSetting::set('home_strip', [
            ['portfolio_slug' => 'ins-navy', 'tag' => '001 · Defence · 2026', 'title' => 'Indian <em>Navy.</em>', 'meta' => 'Official event film'],
            ['portfolio_slug' => 'salesforce-blr', 'tag' => '002 · Corporate · 2026', 'title' => 'Salesforce · <em>Bengaluru.</em>', 'meta' => 'Multi-cam recap film'],
            ['portfolio_slug' => 'rahul-dravid-teaser', 'tag' => '003 · Campaign · 2026', 'title' => 'Rahul Dravid · <em>teaser.</em>', 'meta' => 'Brand campaign film'],
            ['portfolio_slug' => 'range-rover', 'tag' => '004 · Automotive · 2026', 'title' => 'Range <em>Rover.</em>', 'meta' => 'Platform-first reel'],
            ['portfolio_slug' => 'black-label', 'tag' => '005 · Brands · 2026', 'title' => 'Black <em>Label.</em>', 'meta' => 'Regulated-category reel'],
            ['portfolio_slug' => 'pramod-pooja-prewedding', 'tag' => '006 · Wedding · 2026', 'title' => 'Pramod &amp; <em>Pooja.</em>', 'meta' => 'Pre-wedding film'],
        ]);

        SiteSetting::set('hero_videos', ['ins-navy', 'salesforce-blr', 'rahul-dravid-teaser']);
    }
}
