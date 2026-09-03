<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /** The studio's Meta pixel and GA4 property. */
    private const PIXEL_ID = '1412855920724528';

    private const GA_MEASUREMENT_ID = 'G-LHT5WBY4MR';

    /** Seeded before PIXEL_ID; kept only so an old row can be recognised. */
    private const RETIRED_PIXEL_ID = '2292935938118631';

    public function run(): void
    {
        SiteSetting::set('contact_email', 'info@thelastclicks.com');
        SiteSetting::set('contact_phone', '+91 87701 55842');
        SiteSetting::set('whatsapp_url', 'https://wa.me/918770155842');
        // Every profile here was confirmed to be this studio, not merely to
        // resolve. Status codes are useless on most of these platforms — a made-up
        // control handle returns 200 from Facebook, Pinterest, TikTok and Threads
        // alike — so LinkedIn and Behance were checked by reading the page, and
        // Pinterest by its og: tags, which name the studio outright.
        //
        // Facebook is absent because it could not be checked at all: the page
        // serves a login wall to every crawler, and a domain-restricted search
        // found nothing. x.com/thelastclicks is registered (a control handle
        // 404s there) but no public endpoint would serve the profile, and a
        // registered handle is not proof of ownership. Both are one paste away
        // from being added; the footer renders nothing while they are unset,
        // which beats pointing a sitewide link at a stranger.
        SiteSetting::set('socials', [
            'instagram' => 'https://instagram.com/thelastclicks',
            'youtube' => 'https://youtube.com/@thelastclicks',
            'linkedin' => 'https://www.linkedin.com/company/thelastclicks',
            'behance' => 'https://www.behance.net/thelastclicks',
            'pinterest' => 'https://www.pinterest.com/thelastclicks/',
        ]);
        SiteSetting::set('seo_default_title', 'TheLastClicks — Cinematic photography & film production');
        SiteSetting::set('seo_default_description', 'Cinematic photography, brand films and post-production for premium teams.');
        $this->setIfMissing('seo_default_og_image', 'headers/gear-camera-dark.jpg');
        SiteSetting::set('lead_sla_hours', Quote::DEFAULT_SLA_HOURS);

        // The pixel seeded before this one belongs to a retired property. Plain
        // setIfMissing would strand it on every environment that has already been
        // seeded — which is all of them — so the superseded value is recognised
        // and moved on. Any OTHER value is someone's deliberate choice and is
        // left alone.
        if (SiteSetting::get('meta_pixel_id') === self::RETIRED_PIXEL_ID) {
            SiteSetting::set('meta_pixel_id', self::PIXEL_ID);
        } else {
            $this->setIfMissing('meta_pixel_id', self::PIXEL_ID);
        }

        $this->setIfMissing('ga_measurement_id', self::GA_MEASUREMENT_ID);

        $this->setIfMissing('address_street', 'B-7, D-Block, Sector 26');
        $this->setIfMissing('address_locality', 'Noida');
        $this->setIfMissing('address_region', 'Uttar Pradesh');
        $this->setIfMissing('address_postal_code', '201301');
        $this->setIfMissing('address_country', 'IN');
        $this->setIfMissing('geo_latitude', '28.5808331');
        $this->setIfMissing('geo_longitude', '77.3328251');
        // The listing's own share link, supplied by the owner. The share.google
        // URL seeded before it bounced through a Google interstitial rather than
        // resolving to the place; recognised by value and replaced, the same way
        // the retired pixel is — any other value is a deliberate choice.
        if (SiteSetting::get('map_url') === 'https://share.google/QlMQkefJfn2iRnma3') {
            SiteSetting::set('map_url', 'https://maps.app.goo.gl/gu7Jr8BPX5PyhupCA');
        } else {
            $this->setIfMissing('map_url', 'https://maps.app.goo.gl/gu7Jr8BPX5PyhupCA');
        }
        $this->setIfMissing('hours_days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $this->setIfMissing('hours_opens', '10:00');
        $this->setIfMissing('hours_closes', '19:00');
        // Recognised-and-replaced rather than setIfMissing: the key was already
        // filled with the NCR-only list, so a plain setIfMissing would skip it
        // forever. Only that exact list is swapped — anything an editor curated
        // is theirs and survives every deploy.
        if (SiteSetting::get('service_areas') === self::RETIRED_SERVICE_AREAS) {
            SiteSetting::set('service_areas', self::SERVICE_AREAS);
        } else {
            $this->setIfMissing('service_areas', self::SERVICE_AREAS);
        }
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
    /**
     * Where the studio actually shoots.
     *
     * areaServed is read at the granularity of the query (see Nap::areaServed),
     * so these are named cities rather than "India" — a national node matches
     * nothing a local searcher types. NCR leads because that is where the
     * studio is based and where the strongest local intent sits; the rest are
     * the metros and destination cities the work travels to, several of which
     * the portfolio already evidences (Jaipur, Udaipur, Bengaluru, Gurgaon).
     *
     * @var list<string>
     */
    public const SERVICE_AREAS = [
        'Noida', 'Delhi', 'Gurgaon', 'Ghaziabad', 'Faridabad',
        'Mumbai', 'Bengaluru', 'Hyderabad', 'Chennai', 'Kolkata',
        'Pune', 'Ahmedabad', 'Jaipur', 'Chandigarh', 'Lucknow',
        'Udaipur', 'Goa', 'Kochi', 'Indore', 'Surat',
        'Nagpur', 'Bhopal', 'Dehradun', 'Amritsar', 'Varanasi', 'Jodhpur',
    ];

    /**
     * The NCR-only list this replaces. Kept so the swap above can recognise
     * exactly what it is allowed to overwrite, and nothing else.
     *
     * @var list<string>
     */
    public const RETIRED_SERVICE_AREAS = ['Noida', 'Delhi', 'Gurgaon', 'Ghaziabad'];

    protected function setIfMissing(string $key, mixed $value): void
    {
        if (filled(SiteSetting::get($key))) {
            return;
        }

        SiteSetting::set($key, $value);
    }
}
