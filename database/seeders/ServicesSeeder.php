<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        // "talent" is the crew/team page (/crew), not a service — removed here.
        Service::where('slug', 'talent')->delete();

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
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=2000&q=85',
                    'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1600&q=85',
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1600&q=85',
                ],
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
                    ['label' => 'Discipline', 'value' => 'Discipline · 01'],
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
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1800&q=85',
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=85',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&q=85',
                    'https://images.unsplash.com/photo-1551434678-e076c223a692?w=1600&q=85',
                ],
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
            'weddings' => [
                'hero_headline' => 'Cinematic,<br><em>never</em> staged.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 05'],
                    ['label' => 'Format', 'value' => 'Photo + film'],
                    ['label' => 'Typical scope', 'value' => '1–5 day event'],
                    ['label' => 'Timeline', 'value' => '2–4 weeks'],
                ],
                'proof' => ['count' => '124', 'label' => 'Weddings shot · 2024–26', 'sectors' => 'Destination · Intimate · Multi-event'],
                'pillars' => [
                    ['title' => 'Story before staging', 'desc' => 'Treatment-led, never posed. Real moments captured cinematically.'],
                    ['title' => 'Same-day reel', 'desc' => 'Cut on-site. Delivered before guests leave — guaranteed.'],
                    ['title' => 'Director on every shoot', 'desc' => 'A treatment, a shot list, and a director. Just like a brand film.'],
                    ['title' => 'Two-year archive', 'desc' => 'Searchable cloud archive of selects. Re-cuts, re-purposes, never a re-fee.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Pre-event', 'desc' => 'Story call, location recce, family briefing, shot list aligned with planner.', 'time' => 'T-30 days'],
                    ['num' => '02', 'title' => 'Coverage', 'desc' => 'Multi-day photo + film, 2–6 shooters depending on scale.', 'time' => 'Event days'],
                    ['num' => '03', 'title' => 'Same-day', 'desc' => '3–5 min cinematic reel cut on-site and delivered before guests leave.', 'time' => 'Event night'],
                    ['num' => '04', 'title' => 'Photo edit', 'desc' => '400–800 retouched stills with story-led sequencing.', 'time' => '+10 days'],
                    ['num' => '05', 'title' => 'Film', 'desc' => '12–15 min cinematic cut with original sound design and grade.', 'time' => '+3 weeks'],
                ],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?w=2000&q=85',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1600&q=85',
                    'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=1600&q=85',
                    'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=1200&q=85',
                    'https://images.unsplash.com/photo-1606800052052-a08af7148866?w=1600&q=85',
                ],
                'kit' => [
                    ['title' => 'Cameras', 'items' => ['Sony A1', 'Sony FX6', 'Sony A7R V', 'DJI Inspire 3 (drone)']],
                    ['title' => 'Lenses', 'items' => ['Sigma 35mm Art', 'Sigma 85mm Art', '70mm macro', 'Cine zooms']],
                    ['title' => 'Reel rig', 'items' => ['On-site edit station', 'Pro Tools mix', 'same-day color', 'portable IO']],
                ],
                'featured_slug' => 'udaipur',
                'faqs' => [
                    ['q' => 'Do you travel for destination weddings?', 'a' => 'Yes — pan-India and select international destinations. Travel and lodging are billed at cost with no markup.'],
                    ['q' => 'How many hours are covered per day?', 'a' => 'Standard is 10 hours per event-day with breaks. Multi-event days can be extended on request.'],
                    ['q' => 'When do we get the full film?', 'a' => 'Same-day reel before guests leave. Full edit lands in 2–4 weeks.'],
                ],
                'cta' => ['title' => 'Tell us your <em>story.</em>', 'copy' => 'Pencil us in or just say hi. We\'ll respond with availability and a tailored quote within 4 working hours.', 'prefill' => 'Wedding film'],
            ],
            'post-production' => [
                'hero_headline' => 'Post that <em>carries</em><br>the brand.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 03'],
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
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1600&q=85',
                    'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=1600&q=85',
                    'https://images.unsplash.com/photo-1604871000636-074fa5117945?w=2000&q=85',
                ],
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
                'cta' => ['title' => 'Finish like <em>you mean it.</em>', 'copy' => 'Send your footage or your brief. We\'ll review and quote a finish plan within 4 working hours.', 'prefill' => 'Something else'],
            ],
            'social-content' => [
                'hero_headline' => 'Always-on,<br><em>never</em> thin.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 06'],
                    ['label' => 'Format', 'value' => 'Monthly + ad-hoc'],
                    ['label' => 'Typical scope', 'value' => 'Per month / per shoot'],
                    ['label' => 'Timeline', 'value' => '1–2 weeks'],
                ],
                'proof' => ['count' => '24', 'label' => 'Brands on retainer · 2026', 'sectors' => 'Social · LinkedIn · Internal'],
                'pillars' => [
                    ['title' => 'Same standard, scaled down', 'desc' => 'Same crew, kit and post pipeline as our brand films — sized for monthly cadence.'],
                    ['title' => 'Multi-format capture', 'desc' => '9:16, 1:1, 16:9 — shot once, cut for every platform.'],
                    ['title' => 'Cadence over scarcity', 'desc' => 'Monthly batch model: one shoot day → 8–12 platform cuts.'],
                    ['title' => 'Asset library', 'desc' => 'Searchable cloud library so the brand can self-serve future cuts.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Plan', 'desc' => 'Content pillars, formats, schedule, channel mix — locked monthly.', 'time' => 'Day 1'],
                    ['num' => '02', 'title' => 'Shoot', 'desc' => 'One batch shoot day per month. Multi-format capture from a single rig.', 'time' => 'Day 5'],
                    ['num' => '03', 'title' => 'Cut', 'desc' => '9:16, 1:1, 16:9 cuts. Reels, shorts, LinkedIn-tuned versions.', 'time' => 'Day 7–10'],
                    ['num' => '04', 'title' => 'Caption', 'desc' => 'Burned-in captions + platform-native styling. Subtitle-ready.', 'time' => 'Day 10'],
                    ['num' => '05', 'title' => 'Review', 'desc' => 'Quarterly performance review. What worked, what to do next.', 'time' => 'Quarterly'],
                ],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1600&q=85',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=2000&q=85',
                ],
                'kit' => [
                    ['title' => 'Capture', 'items' => ['Sony FX6', 'Sony A7R V', 'DJI Ronin 4D', 'vertical-native rigs']],
                    ['title' => 'Post', 'items' => ['Adobe Premiere', 'DaVinci grade', 'platform-spec presets', 'caption tooling']],
                    ['title' => 'Delivery', 'items' => ['Cloud asset library', 'monthly batches', 'analytics-ready files']],
                ],
                'featured_slug' => 'conf25',
                'faqs' => [
                    ['q' => 'Do you provide a content strategist?', 'a' => 'For growth and custom plans, yes — a producer co-owns the calendar with your team.'],
                    ['q' => 'Can we mix platforms — Instagram, LinkedIn, YouTube Shorts?', 'a' => 'Yes — we shoot once, cut for every major platform spec.'],
                    ['q' => 'Is paid-media-ready creative included?', 'a' => 'Per-platform paid cuts (clean frames, hook variants) are an add-on we recommend for performance briefs.'],
                ],
                'cta' => ['title' => 'Plan the <em>cadence.</em>', 'copy' => 'Share your channels and goals. We\'ll come back with a monthly plan and quote within 4 working hours.', 'prefill' => 'Event coverage'],
            ],
            'creative-direction' => [
                'hero_headline' => 'The idea,<br><em>before</em> the camera.',
                'hero_meta' => [
                    ['label' => 'Discipline', 'value' => 'Discipline · 04'],
                    ['label' => 'Format', 'value' => 'Strategy + craft'],
                    ['label' => 'Typical scope', 'value' => 'Per campaign'],
                    ['label' => 'Timeline', 'value' => '2–8 weeks'],
                ],
                'proof' => ['count' => '38', 'label' => 'Campaigns directed · 2024–26', 'sectors' => 'Brand · Campaign · Long-form'],
                'pillars' => [
                    ['title' => 'Treatment as film', 'desc' => 'Our treatments are the closest thing to seeing the final cut before it exists.'],
                    ['title' => 'Single thesis', 'desc' => 'Every campaign serves one creative thesis. Drift requires a decision, never a slip.'],
                    ['title' => 'Visual systems', 'desc' => 'We don\'t deliver one-shot creative. We hand off a system the brand can reuse.'],
                    ['title' => 'On-set authorship', 'desc' => 'Direction continues onto the floor. Same brain, brief to finish.'],
                ],
                'phases' => [
                    ['num' => '01', 'title' => 'Discovery', 'desc' => 'Audience, channel, guardrails, success metrics — locked in a 2-hour kickoff.', 'time' => 'Day 1'],
                    ['num' => '02', 'title' => 'Thesis', 'desc' => 'A one-sentence creative point of view the whole campaign serves.', 'time' => 'Day 2–3'],
                    ['num' => '03', 'title' => 'Treatment', 'desc' => 'Visualised deck — palette, type, references, motion language.', 'time' => 'Day 4–10'],
                    ['num' => '04', 'title' => 'System', 'desc' => 'Palette, type, photo direction, motion principles — documented to hand off.', 'time' => 'Week 3–4'],
                    ['num' => '05', 'title' => 'Direction', 'desc' => 'On-set art direction protects the idea through execution.', 'time' => 'Shoot days'],
                ],
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1600&q=85',
                    'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1600&q=85',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85',
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=85',
                ],
                'kit' => [
                    ['title' => 'Concepts', 'items' => ['Treatment writing', 'mood-boarding', 'script-doctoring', 'reference grading']],
                    ['title' => 'Systems', 'items' => ['Brand-aligned palette', 'type', 'motion principles', 'documentation']],
                    ['title' => 'On-set', 'items' => ['Art direction', 'casting', 'production design', 'continuity protection']],
                ],
                'featured_slug' => 'beverage',
                'faqs' => [
                    ['q' => 'Do you provide direction only — without production?', 'a' => 'Yes. We often deliver treatments and visual systems that the brand executes with their own crew or another studio.'],
                    ['q' => 'How do you keep the idea intact through revisions?', 'a' => 'By writing a clear creative thesis up front and treating every revision as a check against it. No drift without a decision.'],
                    ['q' => 'Do you do brand identity work?', 'a' => 'Photographic and motion identity — yes. For logo systems we collaborate with brand-identity partners.'],
                ],
                'cta' => ['title' => 'Start with the <em>idea.</em>', 'copy' => 'Drop the strategic brief. We\'ll come back with a creative thesis and engagement scope within 4 working hours.', 'prefill' => 'Brand commercial'],
            ],
        ];

        $heroCopy = [
            'videography' => 'Brand films, commercials, documentaries.',
            'photography' => 'Editorial, lifestyle, product, portrait.',
            'weddings' => 'Cinematic wedding films and stills.',
            'post-production' => 'Edit, colour, sound, finishing.',
            'social-content' => 'Short-form, vertical, campaign-ready.',
            'creative-direction' => 'Concept, treatment, art direction.',
        ];
        $titles = [
            'videography' => 'Videography',
            'photography' => 'Photography',
            'weddings' => 'Weddings',
            'post-production' => 'Post Production',
            'social-content' => 'Social Content',
            'creative-direction' => 'Creative Direction',
        ];
        $order = array_keys($services);
        // Discipline "mix of work" percentages — descending, sums to 100. Drives the
        // portfolio bars (.pf-disc__c label + --p fill), mirroring the design.
        $share = [
            'videography' => 32,
            'weddings' => 24,
            'photography' => 18,
            'post-production' => 12,
            'social-content' => 8,
            'creative-direction' => 6,
        ];

        foreach ($services as $slug => $data) {
            Service::updateOrCreate(['slug' => $slug], array_merge($data, [
                'title' => $titles[$slug],
                'hero_copy' => $heroCopy[$slug],
                'hero_url' => $data['gallery_urls'][0] ?? null,
                'body' => '',
                'order' => array_search($slug, $order, true),
                'share' => $share[$slug] ?? null,
            ]));
        }
    }
}
