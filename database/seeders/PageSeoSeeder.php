<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

/**
 * One-time content migration: titles and descriptions for the eleven public
 * routes that were carrying them as hardcoded attributes in Blade templates.
 *
 * Deliberately NOT called from DatabaseSeeder. It is a migration of existing
 * production copy, not fixture data — and the SEO test suite asserts on routes
 * having no row until a test creates one, which seeding these would break.
 *
 * Run once per environment:
 *
 *     php artisan db:seed --class=PageSeoSeeder
 *
 * Required before the Next.js frontend serves those routes: it reads metadata
 * from this table only, so without these rows every non-service page ships
 * with no title. Idempotent, so re-running never clobbers an admin edit.
 */
class PageSeoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'page_url' => '/',
                'label' => 'Home',
                'title' => 'TheLastClicks — Cinematic photography & film production',
                'meta_description' => 'Cinematic photography, brand films and post-production for premium teams across India — trusted by global enterprise brands, automotive names and national institutions.',
            ],
            [
                'page_url' => '/about',
                'label' => 'About',
                'title' => 'About TheLastClicks — Cinematic Film & Photography Studio',
                'meta_description' => 'A photography and film production studio at the intersection of cinema, brand and craft. Five years, 1,000+ events and 20+ cities across India and counting.',
            ],
            [
                'page_url' => '/portfolio',
                'label' => 'Portfolio',
                'title' => 'Portfolio — Film & Photography | TheLastClicks',
                'meta_description' => 'Selected films and photography from TheLastClicks — brand campaigns, corporate productions, automotive shoots, launches and weddings across 20+ Indian cities.',
            ],
            [
                'page_url' => '/industries',
                'label' => 'Industries',
                'title' => 'Industries — Brand, Auto & Wedding Film | TheLastClicks',
                'meta_description' => 'Fashion, hospitality, beauty, weddings, automotive, corporate and nightlife — the sectors TheLastClicks produces photography and film for across India.',
            ],
            [
                'page_url' => '/blog',
                'label' => 'Journal',
                'title' => 'Journal — Film Craft & Production Notes | TheLastClicks',
                'meta_description' => 'Studio dispatches on film craft, behind-the-scenes process and editorial notes from the TheLastClicks production team. One new craft note every month.',
            ],
            [
                'page_url' => '/contact',
                'label' => 'Contact',
                'title' => 'Contact TheLastClicks — Start a Film or Photography Project',
                'meta_description' => 'Bring us a brief for photography, videography or post-production and we will reply within 4 working hours. Crews and studios covering 20+ cities across India.',
            ],
            [
                'page_url' => '/privacy-policy',
                'label' => 'Privacy Policy',
                'title' => 'Privacy Policy — How We Handle Your Data | TheLastClicks',
                'meta_description' => 'How TheLastClicks collects, uses, stores and protects personal data submitted through our website, enquiry forms and production work across India.',
            ],
            [
                'page_url' => '/terms-of-service',
                'label' => 'Terms of Service',
                'title' => 'Terms of Service — Booking & Usage Terms | TheLastClicks',
                'meta_description' => 'The terms governing TheLastClicks photography and film production services, this website, bookings, deliverables, licensing and image usage rights.',
            ],
            [
                'page_url' => '/cookie-policy',
                'label' => 'Cookie Policy',
                'title' => 'Cookie Policy — Cookies We Use | TheLastClicks',
                'meta_description' => 'The cookies and similar technologies TheLastClicks uses on this website, what each one does, and how to block or delete them in your browser.',
            ],
            [
                'page_url' => '/disclaimer',
                'label' => 'Disclaimer',
                'title' => 'Disclaimer — Site & Portfolio Notice | TheLastClicks',
                'meta_description' => 'General disclaimer covering the accuracy of information, portfolio imagery and third-party links published on the TheLastClicks website.',
            ],
            [
                'page_url' => '/thank-you',
                'label' => 'Thank you',
                'title' => 'Brief received — TheLastClicks',
                'meta_description' => 'Thanks for your brief — we will be in touch within 4 working hours.',
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
