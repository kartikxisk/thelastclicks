<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use App\Support\Brand;
use Illuminate\Database\Seeder;

/**
 * Search-facing title/description overrides for the model-driven pages: the
 * three services, the six industries, and the deck that lists the industries.
 *
 * Their headings ("Photography", "Alcobev") make terrible <title>s on their own;
 * these rows give them intent-bearing titles without touching the page design.
 *
 * Titles for the static routes live in PageSeoSeeder. The split is by what
 * generates the page, not by importance — everything here has a row in the
 * database behind it, so a new service or vertical needs a row here too or it
 * ships with a bare name as its title.
 *
 * Editable afterwards under Site -> Manage SEO; the row wins over whatever a
 * page passes.
 */
class SeoPagesSeeder extends Seeder
{
    public function run(): void
    {
        // URLs that now 301 elsewhere. A row here would carry a title and a
        // description for a page nobody can land on, and it shows up in the
        // admin's SEO list as if it were live. routes/web.php is the source of
        // truth for what redirects; this keeps the SEO table agreeing with it.
        SeoPage::whereIn('page_url', [
            '/services/editing',
            '/services/social-content',
            '/services/creative-direction',
            '/services/weddings',
            '/services/talent',
            '/our-works',
        ])->delete();

        $rows = [
            [
                'page_url' => '/services/photography',
                'label' => 'Photography service',
                'title' => Brand::title('Brand & Corporate Photography, Delhi NCR'),
                'meta_description' => 'Editorial, product, portrait and event photography for brands and corporates across India. In-house retouching, licensed usage, and a brief-first process.',
                'og_image_path' => 'headers/gear-lenses-red.jpg',
            ],
            [
                'page_url' => '/services/videography',
                'label' => 'Videography service',
                'title' => Brand::title('Brand Film & Video Production, Delhi NCR'),
                'meta_description' => 'Treatment-led brand films, corporate video and campaign production across India. One integrated crew from director to editor, with in-house finishing.',
                'og_image_path' => 'headers/gear-camera-dark.jpg',
            ],
            [
                // The address matches the name now; /services/editing 301s here.
                'page_url' => '/services/post-production',
                'label' => 'Post Production service',
                // The only service page that carried no location while the other two
                // did, and "editing" is the higher-volume term people actually type.
                'title' => Brand::title('Video Editing & Post-Production, Delhi NCR'),
                'meta_description' => 'Offline edit, DaVinci colour grading, sound and conform — finished in-house, never outsourced. Post-only projects welcome, footage from any camera.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
            ],

            // The deck that lists them. Its row lived in PageSeoSeeder, which is
            // skipped under testing — so the one description most likely to drift
            // out of step with the taxonomy was also the one nothing could assert
            // on. It sits with the pages it describes now.
            //
            // Both lines named the retired eight-vertical taxonomy — fashion,
            // hospitality, beauty, automotive, nightlife — long after the deck was
            // rebuilt around six that share none of those words. A description
            // that matches nothing on the page is worse than no description.
            [
                'page_url' => '/industries',
                'label' => 'Industries',
                'title' => Brand::title('Corporate, Alcobev & Wedding Film, Delhi NCR'),
                'meta_description' => 'Corporate shoots, alcobev brand films, wedding cinematography, real estate walkthroughs, podcast production and live music — the six verticals TheLastClicks shoots across Delhi NCR.',
                'og_image_path' => 'headers/industries-conference.jpg',
            ],

            // The six industry pages. They shipped live and in the sitemap with
            // no row at all, so each fell back to the Blade default and rendered
            // "<vertical> | The Last Clicks (TLC)" — no keyword, no location.
            //
            // The titles deliberately do NOT use the vertical's own name. Those
            // are the studio's filing vocabulary, not the client's: searching
            // "alcobev brand film" returns regulatory journalism about surrogate
            // advertising rather than a single studio, and nobody hiring a crew
            // types "cover artist". The page keeps the studio's language; the
            // title speaks the buyer's.
            //
            // og images reuse each industry's own cover on the media disk, which
            // is the same key its image_url already points at.
            [
                'page_url' => '/industries/alcobev',
                'label' => 'Alcobev industry',
                'title' => Brand::title('Liquor & Beverage Brand Films, Delhi NCR'),
                'meta_description' => 'Bottle films, spirit launches and bar activations shot for compliance from the brief — macro, liquid and provenance work for alcobev brands across India.',
                'og_image_path' => 'industries/lifestyle-beverage.jpg',
            ],
            [
                'page_url' => '/industries/cover-artist',
                'label' => 'Cover Artist industry',
                'title' => Brand::title('Live Music & Concert Videography, Delhi NCR'),
                'meta_description' => 'Multi-camera performance films for playback singers and touring artists — desk audio with a room backup, cut to the released track, vertical cuts included.',
                'og_image_path' => 'industries/nightlife-entertainment.jpg',
            ],
            [
                'page_url' => '/industries/corporate-shoots',
                'label' => 'Corporate Shoots industry',
                'title' => Brand::title('Corporate Video & Event Coverage, Noida'),
                'meta_description' => 'Summits, leadership films, town halls and product launches across Delhi NCR. Multi-camera coverage run to your run-of-show, with a highlight cut in days.',
                'og_image_path' => 'industries/corporate-enterprise.jpg',
            ],
            [
                'page_url' => '/industries/real-estate',
                'label' => 'Real Estate industry',
                'title' => Brand::title('Property & Office Walkthrough Video, Noida'),
                'meta_description' => 'Office fit-outs, campuses and residential launches filmed as space rather than as a slideshow — walkthroughs, site progress and matched interior stills.',
                'og_image_path' => 'industries/spaces-interiors.jpg',
            ],
            [
                'page_url' => '/industries/podcast',
                'label' => 'Podcast industry',
                'title' => Brand::title('Video Podcast Production Studio, Delhi NCR'),
                'meta_description' => 'Multi-camera podcast and long-form conversation in Noida — isolated audio per speaker, a repeatable set, and every frame shot to crop to vertical clips.',
                'og_image_path' => 'industries/brands-agencies.jpg',
            ],
            [
                'page_url' => '/industries/wedding-pre-wedding',
                'label' => 'Wedding industry',
                'title' => Brand::title('Wedding Cinematography, Noida & Delhi NCR'),
                'meta_description' => 'Wedding films, same-day trailers and pre-wedding shoots across India. Haldi, mehandi, sangeet and roka covered by a second unit so nothing is missed.',
                'og_image_path' => 'industries/weddings-celebrations.jpg',
            ],
        ];

        foreach ($rows as $row) {
            SeoPage::updateOrCreate(
                ['page_url' => $row['page_url']],
                $row + ['is_active' => true],
            );
        }
    }
}
