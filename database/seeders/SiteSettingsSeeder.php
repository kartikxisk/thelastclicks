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

        // Tracking IDs. setIfMissing, not set: an environment pointed at a test
        // pixel or a client's own GA property must not be dragged back to the
        // studio's on the next deploy.
        //
        // Both tags are held behind the cookie banner and neither loads in local
        // or testing, so seeding a real ID here does not start measuring dev
        // traffic. Blank either value in Site Settings → Tracking to stop loading
        // that tag entirely.
        $this->setIfMissing('meta_pixel_id', '2292935938118631');
        // ga_measurement_id is deliberately NOT seeded. No GA4 property was
        // supplied, an invented ID reports to nothing, and setIfMissing treats ''
        // as missing — so seeding a blank would rewrite the row on every deploy
        // for no gain. Unset, the tag falls back to GA_MEASUREMENT_ID via
        // config('services.google_analytics.id'), which is how GA worked before it
        // became a setting. Fill it in Site Settings → Tracking to override.

        // Studio location, hours and service area — the NAP block.
        //
        // home.blade.php has always built the Organization address from these keys
        // and refused to hardcode a fallback, on the sound reasoning that a guessed
        // address becomes an inconsistent citation. But nothing ever set them: they
        // were absent from this seeder AND from the settings form, so the homepage
        // emitted no address at all while the contact page hardcoded the same values
        // in its own LocalBusiness node. One studio, two sources of truth, and the
        // stronger of the two nodes was the empty one. These are those values,
        // lifted from the contact page — not guesses.
        //
        // setIfMissing throughout: a studio that moves updates its address in the
        // admin, and the next deploy must not drag it back.
        $this->setIfMissing('address_street', 'B-7, D-Block, Sector 26');
        $this->setIfMissing('address_locality', 'Noida');
        $this->setIfMissing('address_region', 'Uttar Pradesh');
        $this->setIfMissing('address_postal_code', '201301');
        $this->setIfMissing('address_country', 'IN');
        // Exact pin from the Google Business Profile listing. Five decimals is the
        // documented minimum; these carry seven.
        $this->setIfMissing('geo_latitude', '28.5808331');
        $this->setIfMissing('geo_longitude', '77.3328251');
        $this->setIfMissing('map_url', 'https://share.google/QlMQkefJfn2iRnma3');
        $this->setIfMissing('hours_days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $this->setIfMissing('hours_opens', '10:00');
        $this->setIfMissing('hours_closes', '19:00');
        // Named cities, not a Country node. "India" matches nothing a local searcher
        // types; "Noida" and "Gurgaon" are what a local query actually resolves to.
        $this->setIfMissing('service_areas', ['Noida', 'Delhi', 'Gurgaon', 'Ghaziabad']);

        // Branding and page headers. These are storage keys on the media disk,
        // not uploads owned by a model, so nothing else carries them: a rebuilt
        // database without them loses the logo and every page header while the
        // files sit untouched on S3.
        //
        // setIfMissing, not set: these are the studio's current choices rather
        // than immutable config, and an editor who swaps a header in the admin
        // should not have it reverted by the next deploy.
        // The white wordmark (1200x494), not the circular emblem. This pointed at
        // branding/logo-be3963b5c6.png, which is the 320x320 dark mark meant for
        // the favicon — on the site's dark nav it rendered as a near-invisible
        // grey circle. Nobody noticed until a rebuilt database fell back to the
        // seeded default instead of the value an editor had set by hand.
        $this->setIfMissing('brand_logo', 'branding/logo-a6a2cd4afe.png');
        $this->setIfMissing('brand_logo_dark', 'branding/logo-dark-f65ca5e2f7.png');
        $this->setIfMissing('favicon', 'branding/favicon-34d5039f93.png');
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
