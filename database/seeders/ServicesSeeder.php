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
        // editing (the USP) only.
        //
        // 'post-production' is in the retire list because the service was renamed
        // to Editing and routes/web.php already 301s the old URL. The seeder kept
        // creating the old slug, so every db:seed resurrected it as a fourth
        // service sitting behind a redirect.
        // Hydrate then delete through Eloquent (not a Builder delete) so the
        // `deleting` event fires — Service uses Spatie's InteractsWithMedia
        // directly, which hooks `deleting` to clean up its media/S3 files; a
        // Builder ->delete() bypasses that and leaks the hero/gallery files.
        Service::whereIn('slug', ['talent', 'weddings', 'social-content', 'creative-direction', 'post-production'])->get()->each->delete();

        $services = [
            'videography' => [
                'hero_headline' => 'Motion with <em>a mandate.</em>',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Videography · 02'],
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
                    ['num' => '01', 'title' => 'Treatment', 'desc' => 'Director-written and heavily researched. Visualised with distinct references and shot grammar — the entire film architected before a single camera is packed.', 'time' => 'Day 1–5'],
                    ['num' => '02', 'title' => 'Pre-pro', 'desc' => 'Logistics locked. Casting, location scouting, scheduling, art direction and kit lists are finalised. The call sheet is strictly enforced.', 'time' => 'Day 6–10'],
                    ['num' => '03', 'title' => 'Production', 'desc' => 'Our crews deploy. Absolute set discipline, live on-set monitoring, and daily rushes delivered by 9 PM.', 'time' => 'Shoot days'],
                    ['num' => '04', 'title' => 'Edit', 'desc' => 'A highly structured post pipeline. Paper edit → rough cut → fine cut, managed through three consolidated feedback rounds.', 'time' => '+10 days'],
                    ['num' => '05', 'title' => 'Finish', 'desc' => 'The final polish. ACES colour grading, broadcast-compliant sound design, and masters rendered for every specific platform requirement.', 'time' => '+5 days'],
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
                    ['q' => 'Music licensing & scores', 'a' => 'Full clearance is standard. Every deliverable includes a fully licensed, broadcast-safe track. Custom, original scores from our composer collective are available for premium campaigns.'],
                    ['q' => 'Camera & lens deployment', 'a' => 'We shoot on ARRI, RED and Sony cinema lines, paired with Cooke or Sigma Cine primes. We do not use a standard kit; the gear is rigorously matched to the exact requirements of your brief.'],
                    ['q' => 'Live event & broadcast coverage', 'a' => 'Yes. We deploy dedicated, agile event units featuring 2–6 cameras and an on-site producer. Same-day recap reels and live feeds are executed flawlessly.'],
                ],
                'sections' => [
                    'flow' => ['title' => 'From brief <em>to delivery.</em> No drift.', 'lead' => 'Every phase: an owner, a deliverable, a strict review gate.'],
                    'kit' => ['title' => 'Cinema-grade <em>by default.</em>', 'lead' => 'We do not compromise on our technical pipeline. This is our baseline roster — scaled and adapted whenever a brief demands a highly specific aesthetic.'],
                ],
                'cta' => ['title' => 'Cut us in early.<br>Or cut us in <em>at the edit.</em>', 'copy' => 'Detail your project scope. Our production team will review your requirements and respond within 4 working hours.', 'prefill' => 'Brand commercial'],
            ],
            'photography' => [
                'hero_headline' => 'Narrative stills.<br><em>Precision capture.</em>',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Photography · 03'],
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
                    ['num' => '01', 'title' => 'Creative direction', 'desc' => 'Visual strategy locked. We develop comprehensive mood boards, lighting diagrams and shot lists to align perfectly with your campaign goals.', 'time' => 'Day 1–3'],
                    ['num' => '02', 'title' => 'Pre-pro', 'desc' => 'Logistics secured. Location scouting, casting, art direction and exact technical kit lists are finalised before stepping on set.', 'time' => 'Day 4–7'],
                    ['num' => '03', 'title' => 'The shoot', 'desc' => 'Our units deploy. Absolute set discipline, tethered live-monitoring for instant client feedback, and rigorous adherence to the shot list.', 'time' => 'Shoot days'],
                    ['num' => '04', 'title' => 'Selection', 'desc' => 'Curated contact sheets delivered. We provide a streamlined, secure review process for your team to make final selects without the friction.', 'time' => '+3 days'],
                    ['num' => '05', 'title' => 'Retouching', 'desc' => 'Studio-grade finishing. High-end skin retouching, colour matching to exact brand hex codes, and final delivery in both print and web-ready formats.', 'time' => '+5 days'],
                ],
                // Galleries are uploaded through the admin — nothing is seeded.
                'gallery_urls' => [],
                'kit' => [
                    ['title' => 'Bodies', 'items' => ['Medium format systems', 'Sony A7R V', 'Canon EOS R5', 'Full-frame high-res']],
                    ['title' => 'Lenses', 'items' => ['G-Master primes', 'L-Series primes', 'Specialised tilt-shift for architecture']],
                    ['title' => 'Lighting', 'items' => ['Profoto studio strobes', 'Aputure continuous lighting']],
                ],
                'featured_slug' => 'editorial',
                'faqs' => [
                    ['q' => 'Commercial & industrial capabilities', 'a' => 'Yes. We are equipped and trained to shoot in highly regulated environments, including manufacturing floors, corporate headquarters and multi-day industrial site visits.'],
                    ['q' => 'Do you provide full usage rights?', 'a' => 'Every commercial package comes with clear, comprehensive licensing agreements. You get the rights you need to scale your campaign without unexpected royalties.'],
                    ['q' => 'Can you handle high-volume corporate headshots?', 'a' => 'Yes. We deploy mobile studio setups directly to your corporate offices, ensuring absolute lighting consistency across hundreds of employees.'],
                ],
                'sections' => [
                    'flow' => ['title' => 'From moodboard <em>to master.</em> No drift.', 'lead' => 'Every phase: an owner, a deliverable, a strict review gate.'],
                    'kit' => ['title' => 'High-resolution <em>by default.</em>', 'lead' => 'We deploy premium glass and high-megapixel sensors to ensure your assets hold up everywhere — from digital feeds to national billboards.'],
                ],
                'cta' => ['title' => 'Cut us in early.<br>Or cut us in <em>at the edit.</em>', 'copy' => 'Detail your project scope. Our production team will review your requirements and respond within 4 working hours.', 'prefill' => 'Product shoot'],
            ],
            'editing' => [
                'hero_headline' => 'Studio-grade <em>finishing.</em>',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Post Production · 01'],
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
                    ['num' => '01', 'title' => 'Ingest & sync', 'desc' => 'Data wrangling and security. Footage is backed up to redundant servers, multi-cam angles are synced, and proxies are generated.', 'time' => 'Day 1'],
                    ['num' => '02', 'title' => 'Offline edit', 'desc' => 'The narrative assembly. We cut for pacing, rhythm and story structure. Delivered as a rough cut for your initial structural approval.', 'time' => 'Day 2–7'],
                    ['num' => '03', 'title' => 'Online & VFX', 'desc' => 'The cleanup phase. Picture lock is achieved. Motion graphics, titling and any necessary plate cleanups are integrated seamlessly.', 'time' => 'Day 8–10'],
                    ['num' => '04', 'title' => 'Colour & sound', 'desc' => 'The final polish. ACES colour grading for cinematic depth, paired with broadcast-compliant audio mixing and sound design.', 'time' => 'Day 11–13'],
                    ['num' => '05', 'title' => 'Mastering', 'desc' => 'Rendered for deployment. We deliver exact platform specs (16:9, 9:16, 4:5), ensuring pristine compression for web, broadcast and internal archives.', 'time' => 'Day 14'],
                ],
                // Galleries are uploaded through the admin — nothing is seeded.
                'gallery_urls' => [],
                'kit' => [
                    ['title' => 'The edit', 'items' => ['Adobe Creative Cloud Pro Suite', 'Premiere Pro', 'After Effects']],
                    ['title' => 'The grade', 'items' => ['DaVinci Resolve Studio', 'ACES pipeline', 'Calibrated reference monitors']],
                    ['title' => 'The sound', 'items' => ['Pro Tools', 'Audition', 'Broadcast-compliant metering']],
                ],
                'featured_slug' => 'reel',
                'faqs' => [
                    ['q' => 'Do you edit outside footage?', 'a' => 'Yes. If you have an existing archive or footage shot by another agency, our post-production team can ingest, colour-match and cut it to meet our studio standards.'],
                    ['q' => 'How do revision rounds work?', 'a' => 'No endless back-and-forth. We use frame-accurate, timecoded review links. Every project includes three structured feedback rounds: rough cut, fine cut and final polish.'],
                    ['q' => 'How long do you archive project files?', 'a' => 'We maintain active, redundant backups of all raw footage and project files for a standard minimum window, ensuring you can request alternate cuts or updates months down the line.'],
                ],
                'sections' => [
                    'flow' => ['title' => 'From ingest <em>to export.</em> No drift.', 'lead' => 'A highly structured, secure pipeline built for scale and speed.'],
                    'kit' => ['title' => 'The post-production <em>stack.</em>', 'lead' => 'Our editing bays are powered by industry-standard software and calibrated hardware to guarantee absolute colour and audio accuracy.'],
                ],
                'cta' => ['title' => 'Cut us in early.<br>Or cut us in <em>at the edit.</em>', 'copy' => 'Detail your project scope. Our production team will review your requirements and respond within 4 working hours.', 'prefill' => 'Post-production only'],
            ],
        ];

        $heroCopy = [
            'editing' => 'True cinematic quality is forged behind closed doors. We refuse to outsource our finish. By keeping our entire post-production pipeline in-house, we maintain absolute control over the final aesthetic, ensuring zero friction and uncompromising visual fidelity.',
            'videography' => 'Scalable cinematic production built on absolute set discipline. From agile documentary units to heavily controlled, multi-camera commercial sets, we protect the narrative and deliver footage designed for a studio-grade finish.',
            'photography' => 'We do not just capture images; we architect them. From high-stakes commercial advertising to industrial documentation and corporate archives, every frame is meticulously composed to command attention and align flawlessly with your brand\'s identity.',
        ];
        // The 'editing' KEY is the slug, and the slug is a published address —
        // Service::getSlugOptions() deliberately refuses to regenerate it from a
        // renamed title for exactly this reason. So the service is displayed as
        // "Post Production" while /services/editing keeps serving it, and the
        // /services/post-production 301 in routes/web.php stays pointing here.
        $titles = [
            'editing' => 'Post Production',
            'videography' => 'Videography',
            'photography' => 'Photography',
        ];
        // Post-production is the studio's USP — it leads everywhere services are listed.
        $order = ['editing', 'videography', 'photography'];
        // Discipline "mix of work" percentages — descending, sums to 100. Drives the
        // portfolio bars (.pf-disc__c label + --p fill), mirroring the design.
        $share = [
            'editing' => 40,
            'videography' => 35,
            'photography' => 25,
        ];
        // Placeholder heroes drawn from the industry library already on the CDN, so
        // the services list has a hover preview out of the box. An admin upload to the
        // 'hero' media collection wins over these — see Service::heroUrl().
        $heroUrl = [
            'editing' => 'industries/brands-agencies.jpg',
            'videography' => 'industries/nightlife-entertainment.jpg',
            'photography' => 'industries/fashion-creators.jpg',
        ];

        foreach ($services as $slug => $data) {
            $service = Service::firstWhere('slug', $slug);

            Service::updateOrCreate(['slug' => $slug], array_merge($data, [
                'title' => $titles[$slug],
                'hero_copy' => $heroCopy[$slug],
                // Never clobber a hero someone already set in the admin.
                'hero_url' => filled($service?->hero_url) ? $service->hero_url : ($heroUrl[$slug] ?? null),
                'body' => '',
                'order' => array_search($slug, $order, true),
                'share' => $share[$slug] ?? null,
            ]));
        }
    }
}
