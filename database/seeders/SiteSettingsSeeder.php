<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::set('contact_email', 'info@thelastclicks.com');
        SiteSetting::set('contact_phone', '+91 87701 55842');
        SiteSetting::set('whatsapp_url', 'https://wa.me/918770155842');
        SiteSetting::set('socials', [
            'instagram' => 'https://instagram.com/thelastclicks',
            'youtube' => 'https://youtube.com/@thelastclicks',
        ]);
        SiteSetting::set('seo_default_title', 'TheLastClicks — Cinematic photography & film production');
        SiteSetting::set('seo_default_description', 'Cinematic photography, brand films and post-production for premium teams.');
        // The backstop share image. It was seeded empty, so any page without its
        // own row shared with no preview at all — a bare link on every social
        // platform and in every chat app.
        $this->setIfMissing('seo_default_og_image', 'headers/gear-camera-dark.jpg');
        // Matches the "within 4 working hours" promise on the public pages.
        SiteSetting::set('lead_sla_hours', Quote::DEFAULT_SLA_HOURS);

        // Branding and page headers. These are storage keys on the media disk,
        // not uploads owned by a model, so nothing else carries them: a rebuilt
        // database without them loses the logo and every page header while the
        // files sit untouched on S3.
        //
        // setIfMissing, not set: these are the studio's current choices rather
        // than immutable config, and an editor who swaps a header in the admin
        // should not have it reverted by the next deploy.
        $this->setIfMissing('brand_logo', 'branding/logo-be3963b5c6.png');
        $this->setIfMissing('page_image_about', 'headers/gear-lenses-red.jpg');
        $this->setIfMissing('page_image_about_body', 'headers/gear-lenses-red.jpg');
        $this->setIfMissing('page_image_blog', 'headers/gear-camera-dark.jpg');
        $this->setIfMissing('page_image_contact', 'headers/gear-lens-red.jpg');
        $this->setIfMissing('page_image_industries', 'headers/gear-camera-dark.jpg');
        $this->setIfMissing('page_image_works', 'headers/gear-lens-red.jpg');
    }

    /**
     * Seed a value only when the key holds nothing.
     *
     * Blank counts as missing, not as a choice. Every caller is a storage path
     * or URL, and seo_default_og_image in particular already existed as an empty
     * string — an existence check alone would have left it that way forever.
     */
    protected function setIfMissing(string $key, mixed $value): void
    {
        if (filled(SiteSetting::get($key))) {
            return;
        }

        SiteSetting::set($key, $value);
    }
}
