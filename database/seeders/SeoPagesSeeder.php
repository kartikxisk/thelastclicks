<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

/**
 * Search-facing title/description overrides for the service pages.
 *
 * Their headings ("Photography") make terrible <title>s on their own; these
 * rows give them intent-bearing titles without touching the page design.
 *
 * Titles for the other public routes live in PageSeoSeeder, which is a
 * one-time content migration and is deliberately not called from
 * DatabaseSeeder.
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
            '/services/post-production',
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
                'title' => 'Brand & Corporate Photography in Delhi NCR | TheLastClicks',
                'meta_description' => 'Editorial, product, portrait and event photography for brands and corporates across India. In-house retouching, licensed usage, and a brief-first process.',
                'og_image_path' => 'headers/gear-lenses-red.jpg',
            ],
            [
                'page_url' => '/services/videography',
                'label' => 'Videography service',
                'title' => 'Brand Film & Video Production in Delhi NCR | TheLastClicks',
                'meta_description' => 'Treatment-led brand films, corporate video and campaign production across India. One integrated crew from director to editor, with in-house finishing.',
                'og_image_path' => 'headers/gear-camera-dark.jpg',
            ],
            [
                // The service is displayed as "Post Production"; the URL stays
                // /services/editing because it is already published there.
                'page_url' => '/services/editing',
                'label' => 'Post Production service',
                'title' => 'Video Post-Production & Colour Grading | TheLastClicks',
                'meta_description' => 'Offline edit, DaVinci colour grading, sound and conform — finished in-house, never outsourced. Post-only projects welcome, footage from any camera.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
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
