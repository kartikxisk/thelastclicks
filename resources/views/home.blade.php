<x-layouts.app
    title="Photography, Videography &amp; Editing Agency | The Last Clicks (TLC)"
    description="Photography, videography and post-production agency in Noida, serving brands across Delhi NCR and India — brand films, corporate shoots and in-house editing."
    :canonical="url('/')"
>
    <x-slot name="head">
        @php
            // sameAs is the strongest entity signal Google reads for a brand — feed it every
            // social profile the admin has configured. array_filter drops anything unset.
            $orgSameAs = array_values(array_filter((array) (\App\Models\SiteSetting::get('socials') ?? [])));
            $orgEmail = \App\Models\SiteSetting::get('contact_email');
            $orgPhone = \App\Models\SiteSetting::get('contact_phone');

            // Postal address, entirely admin-supplied. NOTHING is hardcoded here on
            // purpose: a guessed street or locality becomes an inconsistent citation
            // the moment it meets a real directory listing, and inconsistent NAP is
            // worse for local ranking than no NAP at all. Set these under Site
            // Settings → Location and the LocalBusiness node below starts emitting.
            //
            // The build moved into App\Support\Nap so the contact page's node reads
            // the same values; both used to keep their own copy, and this one — the
            // canonical #organization node — was the copy that came out empty
            // because nothing populated the settings it reads.
            $orgAddress = \App\Support\Nap::address();

            // Plain Organization — the brand entity, and only that.
            //
            // This used to become ['Organization', 'ProfessionalService'] as soon as
            // an address existed, on the reasoning that a LocalBusiness needs a
            // location to be valid. The branch had never actually run: no address
            // was ever set, so the type was always the bare string. Now that one is
            // set it would run, and it should not — /contact carries a dedicated
            // PhotographStudio node (an exact subtype, pointed back here via
            // parentOrganization), so upgrading this node too would describe one
            // studio as two different LocalBusinesses that happen to share a name.
            // One precise place entity beats two competing ones.
            $orgType = 'Organization';
        @endphp
        {{-- WebSite node, separate from the brand. It is what makes the site itself
             an entity Google can attach a name to in results, and it is the correct
             home for site-level identity rather than bolting it onto Organization. --}}
        <x-json-ld :data="[
            '@type' => 'WebSite',
            'name'  => \App\Support\Brand::NAME,
            'alternateName' => \App\Support\Brand::ALTERNATE_NAMES,
            'url'   => url('/'),
            'publisher' => ['@id' => url('/').'#organization'],
            'inLanguage' => 'en-IN',
        ]" />
        <x-json-ld :data="array_filter([
            '@type'        => $orgType,
            '@id'          => url('/').'#organization',
            'address'      => $orgAddress,
            'name'         => \App\Support\Brand::NAME,
            {{-- The canonical #organization node is the one Google resolves the
                 brand against, so this is where the name variants have to live —
                 they were only on the contact page's LocalBusiness before. --}}
            'alternateName' => \App\Support\Brand::ALTERNATE_NAMES,
            'url'          => url('/'),
            {{-- Schema needs a logo to be eligible for rich results, so this one keeps an
                 icon fallback even when no brand logo is uploaded. Not rendered on screen. --}}
            'logo'         => \App\Models\SiteSetting::brandLogoUrl() ?: asset('apple-touch-icon.png'),
            'description'  => 'Cinematic photography, brand films and post-production for premium teams across India.',
            'email'        => $orgEmail,
            'telephone'    => $orgPhone,
            'areaServed'   => ['@type' => 'Country', 'name' => 'India'],
            'sameAs'       => $orgSameAs,
            'contactPoint' => [array_filter([
                '@type'             => 'ContactPoint',
                'contactType'       => 'sales',
                'email'             => $orgEmail,
                'telephone'         => $orgPhone,
                'areaServed'        => 'IN',
                'availableLanguage' => ['en', 'hi'],
            ])],
        ])" />
    </x-slot>

    {{-- Hero --}}
    <x-hero />

    {{-- Client-logo marquee (replaces the text marquee) --}}
    <x-clients-marquee />

    <!-- OUR WORK -->
    @if ($featuredWorks->isNotEmpty())
    {{-- Scene 02 · Work — a looping strip of the archive. Previews play
         muted inline; hover stops the strip, a click opens the work.

         Leads the page on purpose: the studio is judged on the footage, so the
         reel comes before the copy about how it is made. Everything below —
         discipline, industries, services — reads as an explanation of what the
         visitor has already seen. --}}
    {{-- id="work" is the stable hook: HomeWorkSectionTest used to isolate this
         block by its data-screen-label, which is a running scene number and so
         broke the moment the section moved up the page. --}}
    <section class="section" id="work" data-screen-label="02 Work">
        <x-scene-bg type="photo" />
        <x-container>
            <div class="services__head" data-stagger>
                <div data-anim="curtain">
                    <span class="section__eyebrow" data-scramble>Portfolio</span>
                    <h2 class="section__title" data-split>Our <em>work.</em></h2>
                </div>
                {{-- The archive link rides the head, right of the title — the
                     same head geometry as the artist band. --}}
                <a class="btn btn--ghost work-head__cta" href="{{ url('/portfolio') }}" data-cursor="VIEW">View portfolio <span class="arr"></span></a>
            </div>
        </x-container>
        {{-- The strip bleeds edge to edge — the head keeps the page grid, the
             footage does not. Same full-bleed language as the artist reel. --}}
        <x-work-marquee :items="$featuredWorks" lightbox-label="Selected work" />
    </section>
    @endif

    <!-- INDUSTRIES -->
    @if ($industries->isNotEmpty())
    {{-- Scene 04 · Industries — a full-bleed bento of the verticals, the same
         edge-to-edge language as the work strip and the services bands. The
         first industry (admin order) takes the feature cell; every tile is
         the strongest internal link its detail page gets. No backdrop: the
         covers are the backdrop. Nothing enters on scroll. --}}
    <section class="section" data-screen-label="04 Industries" data-ind-bento>
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">Industries</span>
                    <h2 class="section__title" data-split>What we <em>cover.</em></h2>
                </div>
            </div>
        </x-container>
        <div class="indb">
            @foreach ($industries as $industry)
                <a class="indb__tile @if ($loop->first) indb__tile--feature @endif"
                   href="{{ url('/industries/'.$industry->slug) }}"
                   aria-label="{{ $industry->title }} work">
                    @if ($industry->coverUrl())
                        <img src="{{ $industry->coverUrl() }}" alt="" loading="lazy" decoding="async">
                    @endif
                    <span class="indb__scrim" aria-hidden="true"></span>
                    <span class="indb__meta">
                        <span class="indb__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="indb__title">{{ $industry->title }}</span>
                    </span>
                    <span class="indb__arr" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 04b ARTIST SPOTLIGHT — the live-music work shown as work: a thin head
         of copy, then a full-bleed reel of watermarked frames from real shows
         (public/images/artist/wm), each strip expanding on hover, the whole
         reel one link into /industries/cover-artist. No names, no captions —
         the frames are the argument. Renders only while the cover-artist
         industry exists and has published work; the roster gates it without
         being printed. --}}
    @php
        $spotlight = $industries->firstWhere('slug', 'cover-artist');
        $spotlightArtists = $spotlight?->publishedWorks()->orderBy('order')->pluck('title') ?? collect();
        // The frames are the industry's own uploaded image rows, resolved on
        // the media disk. They were seven filenames committed under
        // public/images/artist — nothing an editor could change, and 37MB of
        // photography in the repo. Uploading under Content → Industries →
        // Cover Artist → Media is now the whole workflow.
        $spotlightFrames = $spotlight
            ? $spotlight->mediaItems->where('type', 'image')
                ->map(fn ($item) => $item->resolvedUrl())
                ->filter()
                ->values()
            : collect();
    @endphp
    @if ($spotlight && $spotlightArtists->isNotEmpty() && $spotlightFrames->isNotEmpty())
    <section class="artist-band" data-artist-band data-screen-label="04b Artists">
        <x-container>
            <div class="artist-band__head">
                <div>
                    <span class="section__eyebrow">{{ $spotlight->title }}</span>
                    <h2 class="artist-band__title">Every performance is <em>its own film.</em></h2>
                </div>
                <a class="artist-band__cta" href="{{ url('/industries/'.$spotlight->slug) }}">
                    See the artist work <span class="arr"></span>
                </a>
            </div>
        </x-container>

        <a class="artist-band__reel" href="{{ url('/industries/'.$spotlight->slug) }}"
           aria-label="See the artist work">
            @foreach ($spotlightFrames as $frame)
                <span class="artist-band__frame"
                      style="background-image:url('{{ $frame }}')"></span>
            @endforeach
        </a>
    </section>
    @endif

    <!-- SERVICES -->
    {{-- Scene 05 · Services — full-bleed cover strips, the vertical counterpart
         to the artist reel: each service is an edge-to-edge band of its own
         artwork, and the hovered band opens to show its pillar line — a
         grid-template-rows transition, the motion-spec's sanctioned layout
         motion. No scene backdrop here: the artwork is the backdrop. Nothing
         enters on scroll. --}}
    <section class="section services" id="services" data-screen-label="05 Services" data-svc-index>
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Our Services</span>
                    <h2 class="section__title" data-split>What <em>we do</em></h2>
                </div>
            </div>
        </x-container>
        <div class="svcx">
            @foreach ($services as $service)
                <a class="svcx__row" href="{{ url('/services/'.$service->slug) }}" data-cursor="EXPLORE"
                   @if ($service->heroUrl()) style="--svcx-bg:url('{{ $service->heroUrl() }}')" @endif>
                    <span class="svcx__inner">
                        <span class="svcx__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="svcx__title">{{ $service->title }}</h3>
                        <span class="svcx__arr" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                    </span>
                    {{-- Empty grid track that opens on hover — the strip grows,
                         the photograph gets the room, no copy appears. --}}
                    <span class="svcx__grow" aria-hidden="true"><span></span></span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- TESTIMONIALS -->
    @if ($testimonials->isNotEmpty())
    {{-- Heading left, quotes drifting vertically on the right. The two rails run
         in opposite directions so the pair reads as movement rather than as one
         block sliding; the second is a straight repeat of the same quotes, which
         is why it is aria-hidden in full — it exists to fill the right-hand
         column, not to add content. --}}
    @php $storiesBg = \App\Models\SiteSetting::pageImage('testimonials'); @endphp
    <section class="section @if ($storiesBg) section--photo @endif"
             data-screen-label="06 Testimonials"
             @if ($storiesBg) style="--section-bg:url('{{ $storiesBg }}')" @endif>
        {{-- Scene 06 · Testimonials — words, not craft, so the backdrop drops to
             the quiet aperture.

             The photo replaces the animated backdrop rather than layering over
             it: two backdrops in one section read as noise, and a still costs
             one static surface where .scenebg costs a masked animated one.
             Nothing uploaded means nothing changes here — the aperture stays. --}}
        @unless ($storiesBg)
            <x-scene-bg type="photo" />
        @endunless
        <x-container>
            <div class="tmq" data-anim="rise">
                {{-- Sticky, so the heading holds while the quotes travel past it. --}}
                <div class="tmq__aside">
                    <span class="section__eyebrow" data-scramble>Client stories</span>
                    <h2 class="section__title" data-split>What <em>people say</em></h2>
                    <p class="tmq__lead">Work we were trusted with, described by the people who trusted us with it.</p>
                </div>

                <div class="tmq__rails">
                    @foreach ([false, true] as $isSecondRail)
                        {{-- Each rail renders the set twice and travels -50%, so the
                             seam lands exactly where it started — the same trick
                             .marquee uses, turned on its side. Rendering the FULL set
                             per rail rather than splitting it between them guarantees
                             the track is always taller than the viewport it scrolls
                             in, whatever number of testimonials the admin publishes. --}}
                        <ul class="tmq__rail @if ($isSecondRail) tmq__rail--down @endif"
                            aria-hidden="{{ $isSecondRail ? 'true' : 'false' }}">
                            @foreach ([false, true] as $isDuplicate)
                                @foreach ($testimonials as $t)
                                    {{-- Attribute always emitted: the @if form that only
                                         added it on the duplicate pass silently rendered
                                         nothing, announcing every quote twice. --}}
                                    <li class="tmq__item" aria-hidden="{{ $isDuplicate ? 'true' : 'false' }}">
                                        <figure class="tmq__card">
                                            <blockquote class="tmq__quote">{{ $t->quote }}</blockquote>
                                            <figcaption class="tmq__who">
                                                <span class="tmq__name">{{ $t->client_name }}</span>
                                                @if ($t->role_company)
                                                    <span class="tmq__role">{{ $t->role_company }}</span>
                                                @endif
                                            </figcaption>
                                        </figure>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </x-container>
    </section>
    @endif

    <!-- CTA -->
    {{-- Scene 07 · CTA — the close. Headline rises, buttons follow, aperture
         breathing behind the red floor-glow. --}}
    <section class="cta-strip" data-screen-label="07 CTA">
        <x-scene-bg type="photo" />
        <x-container data-stagger>
            <h2 class="cta-strip__title" data-split data-anim="mask-up">Work with us.<br>Or work <em>among us.</em></h2>
            <div class="cta-strip__row" data-anim="rise">
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    {{-- href stays a real contact URL so it still works without JS; the
                         delegated [data-quote-trigger] handler opens the modal instead. --}}
                    <a class="btn btn--red" href="{{ url('/contact') }}" data-quote-trigger data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
                    <a class="btn btn--ghost" href="{{ url('/services/photography') }}" data-cursor="VIEW">Explore services <span class="arr"></span></a>
                </div>
            </div>
        </x-container>
    </section>

</x-layouts.app>
