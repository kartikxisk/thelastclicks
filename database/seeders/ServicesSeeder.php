<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        // "talent" is the crew/team page (/crew), not a service — removed here.
        // Weddings, social content and creative direction are no longer offered as
        // standalone services; the studio sells photography, videography and
        // post-production (the USP) only.
        // Hydrate then delete through Eloquent (not a Builder delete) so the
        // `deleting` event fires — Service uses Spatie's InteractsWithMedia
        // directly, which hooks `deleting` to clean up its media/S3 files; a
        // Builder ->delete() bypasses that and leaks the hero/gallery files.
        Service::whereIn('slug', ['talent', 'weddings', 'social-content', 'creative-direction'])->get()->each->delete();

        $services = [
            'videography' => [
                'hero_headline' => 'Films that <em>move</em><br>things.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 02'],
                    ['label' => 'Format', 'value' => 'Film + edit'],
                    ['label' => 'Typical scope', 'value' => '1–10 day shoot'],
                    ['label' => 'Timeline', 'value' => '2–6 weeks'],
                ],
                'proof' => ['count' => '142', 'label' => 'Films delivered · 2024–26', 'sectors' => 'Brand · Auto · Lifestyle'],
                'pillars' => [
                    ['title' => 'Treatment-first', 'desc' => 'Every film starts with a written treatment and a single emotional beat it has to land.'],
                    ['title' => 'One integrated team', 'desc' => 'Director, DOP, editor all work the same brief. Edit starts the same day as the shoot.'],
                    ['title' => 'In-house finish', 'desc' => 'Grade, sound, conform — never outsourced. Authorship survives all the way to master.'],
                    ['title' => 'Platform-tuned', 'desc' => 'Hero film + 9:16, 1:1, 16:9 cuts; subtitles + localisation passes on request.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Treatment', 'desc' => 'Director-written, visualised with references and shot grammar — the film in miniature.', 'time' => 'Day 1–5'],
                    ['num' => '02', 'title' => 'Pre-pro', 'desc' => 'Casting, locations, schedule, kit list, art direction. Call sheet locked.', 'time' => 'Day 6–10'],
                    ['num' => '03', 'title' => 'Production', 'desc' => 'Full-crew shoot, on-set monitoring, daily rushes by 9 PM.', 'time' => 'Shoot days'],
                    ['num' => '04', 'title' => 'Edit', 'desc' => 'Paper edit → rough cut → fine cut, three structured feedback rounds.', 'time' => '+10 days'],
                    ['num' => '05', 'title' => 'Finish', 'desc' => 'ACES grade, sound design, masters in every platform spec.', 'time' => '+5 days'],
                ],
                // Galleries are uploaded through the admin — nothing is seeded.
                'gallery_urls' => [],
                'kit' => [
                    ['title' => 'Cinema bodies', 'items' => ['ARRI Alexa Mini', 'RED Komodo X', 'Sony FX6', 'Phantom Flex 4K']],
                    ['title' => 'Lenses', 'items' => ['Cooke S4 Mini', 'Zeiss Supreme', 'Sigma Cine Primes', 'Atlas Orion']],
                    ['title' => 'Movement', 'items' => ['DJI Ronin 4D', 'MoVI Pro', 'DJI Inspire 3', 'MotionMC rig']],
                ],
                'featured_slug' => 'atlas',
                'faqs' => [
                    ['q' => 'Do you handle music licensing?', 'a' => 'Yes — every package includes a fully-cleared music track. Original scores from our composer collective are available as an add-on.'],
                    ['q' => 'What cameras and lenses do you shoot on?', 'a' => 'ARRI Alexa Mini, RED Komodo X, Sony FX6 — paired with Cooke S4 or Sigma Cine primes. The kit is matched to the brief, not the other way around.'],
                    ['q' => 'Can you handle live event coverage as well?', 'a' => 'Yes — we run dedicated event units with 2–6 cameras and a producer. Same-day recap reels included.'],
                ],
                'cta' => ['title' => 'Cut the <em>film.</em>', 'copy' => 'Tell us what the film has to do. We\'ll share a treatment and quote within 4 working hours.', 'prefill' => 'Brand commercial'],
            ],
            'photography' => [
                'hero_headline' => 'Photography,<br><em>brand-grade.</em>',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 03'],
                    ['label' => 'Format', 'value' => 'Stills + retouch'],
                    ['label' => 'Typical scope', 'value' => '1–5 day shoot'],
                    ['label' => 'Timeline', 'value' => '3–10 days'],
                ],
                'proof' => ['count' => '186', 'label' => 'Shoots delivered · 2024–26', 'sectors' => 'Brand · Editorial · Events'],
                'pillars' => [
                    ['title' => 'Lit, never lazy', 'desc' => 'Every frame is composed and exposed for the final deliverable — not for "fix it in post."'],
                    ['title' => 'Curated selects', 'desc' => 'Editor-tight short-list of finals. Never a folder dump for you to sift through.'],
                    ['title' => 'In-house retouch', 'desc' => 'Color, frequency separation, brand-conform retouch — never outsourced.'],
                    ['title' => 'Two-year archive', 'desc' => 'Searchable cloud archive, RAWs on request, re-export any spec at no re-fee.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Look', 'desc' => 'Pre-shoot creative — treatment, mood-board, shot list, props/styling brief.', 'time' => 'Day 0–2'],
                    ['num' => '02', 'title' => 'Capture', 'desc' => 'Director-led shoot with crew, lighting, AD, BTS. Reviewed on set, never in post.', 'time' => 'Shoot days'],
                    ['num' => '03', 'title' => 'Edit', 'desc' => 'Editor-tight selects. Reviewed with you before retouch begins.', 'time' => '+2 days'],
                    ['num' => '04', 'title' => 'Retouch', 'desc' => 'Brand-grade color + skin + product retouch. Two structured review cycles.', 'time' => '+5 days'],
                    ['num' => '05', 'title' => 'Deliver', 'desc' => 'Print masters, web masters, social cuts. Archive activated. Project closed.', 'time' => '+1 day'],
                ],
                // Galleries are uploaded through the admin — nothing is seeded.
                'gallery_urls' => [],
                'kit' => [
                    ['title' => 'Camera bodies', 'items' => ['Hasselblad H6D-100c', 'Sony A7R V', 'Sony A1', 'Capture One']],
                    ['title' => 'Lenses', 'items' => ['Sigma Art primes', 'Sony GM', 'Tilt-shift 50mm', 'Macro 90mm']],
                    ['title' => 'Lighting', 'items' => ['Profoto strobes', 'Aputure constant', 'Bowens softboxes']],
                ],
                'featured_slug' => 'editorial',
                'faqs' => [
                    ['q' => 'Do you license the photos to the brand?', 'a' => 'Yes — default 3-year worldwide usage license on all delivered stills. Extensions and buyouts are easy to negotiate per project.'],
                    ['q' => 'Can we re-use a previous shoot\'s style?', 'a' => 'Absolutely — we save your project look profile and reapply it on new shoots so consecutive campaigns stay visually consistent.'],
                    ['q' => 'Do you shoot tabletop / product?', 'a' => 'Yes. Macro setups, motion-controlled tabletop and product stills are all handled in-house.'],
                ],
                'cta' => ['title' => 'Light the <em>frame.</em>', 'copy' => 'Brief us on the shoot. We\'ll come back with a treatment, timeline, and budget within 4 working hours.', 'prefill' => 'Product shoot'],
            ],
            'post-production' => [
                'hero_headline' => 'Post that <em>carries</em><br>the brand.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 01'],
                    ['label' => 'Format', 'value' => 'Post-only or full'],
                    ['label' => 'Typical scope', 'value' => 'Per project'],
                    ['label' => 'Timeline', 'value' => '1–3 weeks'],
                ],
                'proof' => ['count' => '286', 'label' => 'Cuts finished · 2024–26', 'sectors' => 'Brand · Commercial · Long-form'],
                'pillars' => [
                    ['title' => 'Never outsourced', 'desc' => 'The hand that lit the film is the hand that grades it. Authorship doesn\'t survive outsourcing.'],
                    ['title' => 'ACES managed', 'desc' => 'Full color-managed pipeline. Brand LUTs locked, scene-balanced, version-controlled.'],
                    ['title' => 'Brand-conform masters', 'desc' => 'Re-export to any platform spec, any time, without re-grading.'],
                    ['title' => 'Sound in-house', 'desc' => 'Spot effects, ambience, music mix — finished under the same roof as the picture.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Look', 'desc' => 'Reference grading on stills before the shoot. Brand LUT locked.', 'time' => 'Pre-shoot'],
                    ['num' => '02', 'title' => 'Conform', 'desc' => 'Multi-cam sync, scene cuts, prep for grade.', 'time' => 'Day 1–2'],
                    ['num' => '03', 'title' => 'Grade', 'desc' => 'Primary + secondary in DaVinci, scene-balance, ACES managed.', 'time' => 'Day 3–7'],
                    ['num' => '04', 'title' => 'Sound', 'desc' => 'Spot effects, ambience, mix. Original score on request.', 'time' => 'Day 5–9'],
                    ['num' => '05', 'title' => 'Master', 'desc' => 'Per-platform exports, IMF if required, archive activated.', 'time' => 'Day 10+'],
                ],
                // Galleries are uploaded through the admin — nothing is seeded.
                'gallery_urls' => [],
                'kit' => [
                    ['title' => 'Grade', 'items' => ['DaVinci Resolve Studio', 'ACES', 'brand LUT library', 'calibrated reference monitors']],
                    ['title' => 'Edit & sound', 'items' => ['Avid Media Composer', 'Adobe Premiere', 'Pro Tools', 'Sound Devices']],
                    ['title' => 'Mastering', 'items' => ['Per-platform IMF', 'Rec. 709', 'DCI-P3', 'HDR-ready masters']],
                ],
                'featured_slug' => 'reel',
                'faqs' => [
                    ['q' => 'Do you take on post for someone else\'s footage?', 'a' => 'Yes — post-only briefs are common. We just need clean rushes and a brief.'],
                    ['q' => 'What\'s your turnaround on a 60-second cut?', 'a' => 'Typically 5–7 working days from picture-lock to mastered delivery.'],
                    ['q' => 'Do you handle subtitle and localisation?', 'a' => 'Yes — multilingual subtitles and full localisation passes are an add-on.'],
                ],
                'cta' => ['title' => 'Finish like <em>you mean it.</em>', 'copy' => 'Send your footage or your brief. We\'ll review and quote a finish plan within 4 working hours.', 'prefill' => 'Post-production only'],
            ],
        ];

        $heroCopy = [
            'post-production' => 'Our signature — edit, colour, sound, finishing. Post-only briefs welcome.',
            'videography' => 'Brand films, commercials, documentaries.',
            'photography' => 'Editorial, lifestyle, product, portrait.',
        ];
        $titles = [
            'post-production' => 'Post Production',
            'videography' => 'Videography',
            'photography' => 'Photography',
        ];
        // Post-production is the studio's USP — it leads everywhere services are listed.
        $order = ['post-production', 'videography', 'photography'];
        // Discipline "mix of work" percentages — descending, sums to 100. Drives the
        // portfolio bars (.pf-disc__c label + --p fill), mirroring the design.
        $share = [
            'post-production' => 40,
            'videography' => 35,
            'photography' => 25,
        ];

        foreach ($services as $slug => $data) {
            Service::updateOrCreate(['slug' => $slug], array_merge($data, [
                'title' => $titles[$slug],
                'hero_copy' => $heroCopy[$slug],
                // Hero image comes from the admin upload (media collection 'hero').
                'hero_url' => null,
                'body' => '',
                'order' => array_search($slug, $order, true),
                'share' => $share[$slug] ?? null,
            ]));
        }
    }
}
