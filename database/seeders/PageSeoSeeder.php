<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use App\Support\Brand;
use Illuminate\Database\Seeder;

/**
 * One-time content migration: titles and descriptions for the eleven public
 * routes that were carrying them as hardcoded attributes in Blade templates.
 *
 * Now called from DatabaseSeeder, outside the testing environment. Leaving it
 * to a remembered one-off meant a rebuilt site launched with no <title> and no
 * description on its homepage, about, portfolio and contact pages — the eleven
 * routes with the most search value on the site. The testing guard is what the
 * original note was really about: ManageSeoTest creates its own rows for '/'
 * and '/about' and asserts the no-row fallback, so pre-seeded rows break it.
 *
 * Can still be run on its own:
 *
 *     php artisan db:seed --class=PageSeoSeeder
 *
 * Idempotent, so re-running never clobbers an admin edit.
 */
class PageSeoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'page_url' => '/',
                'label' => 'Home',
                'title' => Brand::title('Photography, Videography & Editing Agency'),
                // Names the three services, the city and the region. The previous
                // version led with "Cinematic" and never said where the studio is,
                // so it competed on an adjective instead of on what it sells and
                // where — and Noida/Delhi NCR is the intent that actually converts.
                'meta_description' => 'Photography, videography and post-production agency in Noida, serving brands across Delhi NCR and India — brand films, corporate shoots and in-house editing.',
                'og_image_path' => 'headers/gear-camera-dark.jpg',
            ],
            [
                'page_url' => '/about',
                'label' => 'About',
                'title' => Brand::title('About Our Photography & Film Studio'),
                'meta_description' => 'A photography and film production studio at the intersection of cinema, brand and craft. Five years, 1,000+ events and 20+ cities across India and counting.',
                'og_image_path' => 'headers/about-crew.jpg',
            ],
            [
                'page_url' => '/portfolio',
                'label' => 'Portfolio',
                'title' => Brand::title('Photography & Video Portfolio'),
                'meta_description' => 'Selected films and photography from TheLastClicks — brand campaigns, corporate productions, automotive shoots, launches and weddings across 20+ Indian cities.',
                'og_image_path' => 'headers/portfolio-set.jpg',
            ],
            [
                'page_url' => '/blog',
                'label' => 'Journal',
                'title' => Brand::title('Film & Photography Journal'),
                'meta_description' => 'Studio dispatches on film craft, behind-the-scenes process and editorial notes from the TheLastClicks production team. One new craft note every month.',
                'og_image_path' => 'headers/journal-studio.jpg',
            ],
            [
                'page_url' => '/contact',
                'label' => 'Contact',
                'title' => Brand::title('Contact Our Photography & Video Team'),
                'meta_description' => 'Bring us a brief for photography, videography or post-production and we will reply with next steps and a number. Crews and studios covering 20+ cities across India.',
                'og_image_path' => 'headers/contact-crew.jpg',
            ],
            [
                'page_url' => '/privacy-policy',
                'label' => 'Privacy Policy',
                'title' => Brand::title('Privacy Policy'),
                'meta_description' => 'How TheLastClicks collects, uses, stores and protects personal data submitted through our website, enquiry forms and production work across India.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
            ],
            [
                'page_url' => '/terms-of-service',
                'label' => 'Terms of Service',
                'title' => Brand::title('Terms of Service'),
                'meta_description' => 'The terms governing TheLastClicks photography and film production services, this website, bookings, deliverables, licensing and image usage rights.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
            ],
            [
                'page_url' => '/cookie-policy',
                'label' => 'Cookie Policy',
                'title' => Brand::title('Cookie Policy'),
                'meta_description' => 'The cookies and similar technologies TheLastClicks uses on this website, what each one does, and how to block or delete them in your browser.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
            ],
            [
                'page_url' => '/disclaimer',
                'label' => 'Disclaimer',
                'title' => Brand::title('Disclaimer'),
                'meta_description' => 'General disclaimer covering the accuracy of information, portfolio imagery and third-party links published on the TheLastClicks website.',
                'og_image_path' => 'headers/gear-lens-red.jpg',
            ],
            [
                'page_url' => '/thank-you',
                'label' => 'Thank you',
                'title' => Brand::title('Brief received'),
                'meta_description' => 'Thanks for your brief — we will be in touch with next steps.',
                'og_image_path' => 'headers/contact-celebration.jpg',
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
