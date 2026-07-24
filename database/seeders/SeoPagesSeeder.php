<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

/**
 * Search-facing title/description overrides for pages whose on-page copy is
 * deliberately short. The service pages are the commercial money pages, but
 * their headings ("Photography") make terrible <title>s on their own — these
 * rows give them intent-bearing titles without touching the page design.
 *
 * Editable afterwards under Site → Manage SEO; the row wins over whatever the
 * Blade page passes.
 */
class SeoPagesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'page_url' => '/services/photography',
                'label' => 'Photography service',
                'title' => 'Brand & Corporate Photography in Delhi NCR | TheLastClicks',
                'meta_description' => 'Editorial, product, portrait and event photography for brands and corporates across India. In-house retouching, licensed usage, and a brief-first process.',
            ],
            [
                'page_url' => '/services/videography',
                'label' => 'Videography service',
                'title' => 'Brand Film & Video Production in Delhi NCR | TheLastClicks',
                'meta_description' => 'Treatment-led brand films, corporate video and campaign production across India. One integrated crew from director to editor, with in-house finishing.',
            ],
            [
                'page_url' => '/services/post-production',
                'label' => 'Post-production service',
                'title' => 'Video Post-Production & Colour Grading | TheLastClicks',
                'meta_description' => 'Offline edit, DaVinci colour grading, sound and conform — finished in-house, never outsourced. Post-only projects welcome, footage from any camera.',
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
