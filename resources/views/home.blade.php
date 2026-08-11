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
            // Settings and the LocalBusiness node below starts emitting.
            $addrLocality = \App\Models\SiteSetting::get('address_locality');
            $orgAddress = $addrLocality ? array_filter([
                '@type'           => 'PostalAddress',
                'streetAddress'   => \App\Models\SiteSetting::get('address_street'),
                'addressLocality' => $addrLocality,
                'addressRegion'   => \App\Models\SiteSetting::get('address_region'),
                'postalCode'      => \App\Models\SiteSetting::get('address_postal_code'),
                'addressCountry'  => \App\Models\SiteSetting::get('address_country') ?: 'IN',
            ]) : null;

            // ProfessionalService only once there is an address to anchor it: a
            // LocalBusiness with no location is the one thing Google's own docs
            // single out as invalid. Without it the Organization node below still
            // carries the brand, which is what a service-area studio needs anyway.
            $orgType = $orgAddress ? ['Organization', 'ProfessionalService'] : 'Organization';
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

    <!-- DISCIPLINE -->
    {{-- Scene 02 · Discipline — the studio's standards, so the backdrop is the
         camera language: light sweep, framing guides, focus pull. --}}
    <section class="section disc" data-screen-label="02 Discipline">
        <x-scene-bg type="camera" />
        <x-container>
            {{-- Title left, description right, bottom-aligned so the paragraph sits
                 on the title's last line rather than up beside the eyebrow.

                 .disc__head rather than .disc__grid: the grid rule belongs to the
                 About page, whose story copy is three paragraphs and wants a
                 different measure and a top alignment. --}}
            <div class="disc__head" data-stagger>
                <div class="disc__lead" data-anim="curtain">
                    <span class="section__eyebrow reveal">Who we are</span>
                    <h2 class="section__title" data-split>Beyond the lens: <em>a promise of discipline.</em></h2>
                </div>
                <div class="disc__copy" data-anim="slide-r">
                    <p>We are built on the discipline of premium brands. By showing up prepared and refusing to compromise, our work earns the trust of <strong>national institutions</strong> and <strong>global enterprises</strong> alike.</p>
                </div>
            </div>

            {{-- The same four principles the About page expands on, cut to one line
                 each. Reuses .proc rather than a homepage-only variant, so a change
                 to the card treatment lands on both pages at once. --}}
            <div class="proc" data-stagger>
                <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">01<span>FOCUS</span></div><h3>Narrative first</h3><p>Imagery is empty without a story. We build the blueprint before the cameras roll.</p></div>
                <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">02<span>CRAFT</span></div><h3>Studio-grade finish</h3><p>True quality is forged in post. Our in-house grading ensures uncompromising fidelity.</p></div>
                <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">03<span>SCALE</span></div><h3>Agile production</h3><p>From single-operator to massive multi-camera sets, our aesthetic remains steadfast.</p></div>
                <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">04<span>TRUST</span></div><h3>Absolute alignment</h3><p>We integrate seamlessly, ensuring deliverables are fully compliant with your guidelines.</p></div>
            </div>

            <div class="disc__stats" data-stagger>
                <div class="disc__stat" data-anim="mask-up">
                    <div class="disc__num"><span data-count="5">0</span><em>+</em></div>
                    <span class="disc__lab">Years of experience</span>
                </div>
                <div class="disc__stat" data-anim="mask-up">
                    <div class="disc__num"><span data-count="20">0</span><em>+</em></div>
                    <span class="disc__lab">Cities covered across India</span>
                </div>
                <div class="disc__stat" data-anim="mask-up">
                    <div class="disc__num"><span data-count="1000">0</span><em>+</em></div>
                    <span class="disc__lab">Events &amp; activations over the last decade</span>
                </div>
            </div>
        </x-container>
    </section>

    <!-- INDUSTRIES -->
    @if ($industries->isNotEmpty())
    {{-- Scene 03 · Industries — a 3D deck already, so the backdrop stays the
         neutral grid and the reveal pushes the whole deck in from depth. --}}
    <section class="section" data-screen-label="02 Industries">
        <x-scene-bg type="grid" />
        <x-container>
            <div class="services__head" data-stagger>
                <div data-anim="flip-up">
                    <span class="section__eyebrow">Industries</span>
                    <h2 class="section__title" data-split>What we <em>cover.</em></h2>
                </div>
            </div>
        </x-container>

        {{-- Coverflow: every industry is a real card sitting in 3D space. Advancing
             re-assigns position classes, so cards transition from wherever they are
             instead of restarting. Cards stay focusable — focusing one off to the
             side rotates the deck to it rather than trapping keyboard users. --}}
        <div class="i3d" data-i3d data-anim="depth">
            <div class="i3d__stage"
                 tabindex="0"
                 role="group"
                 aria-roledescription="carousel"
                 aria-label="Industries we cover">
                @foreach ($industries as $industry)
                    {{-- href stays a real contact URL so the card still works without
                         JS; the delegated [data-quote-trigger] handler intercepts it
                         and opens the wizard with this industry pre-selected. --}}
                    <a class="i3d__card"
                       data-i3d-card
                       href="{{ url('/contact') }}"
                       data-quote-trigger
                       data-quote-prefill="{{ $industry->title }}"
                       aria-label="Start a {{ $industry->title }} project">
                        @if ($industry->coverUrl())
                            {{-- draggable=false: without it a mouse press on the image
                                 starts a native HTML5 image drag, which takes the
                                 pointer and the deck's own drag never sees a move.
                                 CSS covers WebKit; Firefox needs the attribute. --}}
                            <img src="{{ $industry->coverUrl() }}" alt="" draggable="false"
                                 loading="lazy" decoding="async">
                        @endif
                        <span class="i3d__scrim" aria-hidden="true"></span>
                        <span class="i3d__title">{{ $industry->title }}</span>
                    </a>
                @endforeach
            </div>

            @if ($industries->count() > 1)
                <div class="i3d__nav">
                    <button type="button" class="i3d__arr" data-i3d-prev aria-label="Previous industry">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                    </button>
                    <div class="i3d__dots">
                        @foreach ($industries as $industry)
                            <button type="button" class="i3d__dot" data-i3d-dot
                                    aria-label="Show {{ $industry->title }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="i3d__arr" data-i3d-next aria-label="Next industry">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            @endif

            <p class="sr-only" aria-live="polite" data-i3d-status></p>
        </div>
    </section>
    @endif

    <!-- SERVICES -->
    {{-- Scene 04 · Services — the disciplines themselves, so the backdrop is the
         edit bay: timeline lanes, keyframes, playhead. Rows wipe in from the
         left one after another, like clips landing on a track. --}}
    <section class="section services" id="services" data-screen-label="03 Services">
        <x-scene-bg type="edit" />
        <x-container>
            <div class="services__head" data-stagger>
                <div data-anim="mask-up">
                    <span class="section__eyebrow" data-scramble>Our Services</span>
                    <h2 class="section__title" data-split>What <em>we do</em></h2>
                </div>
            </div>
            <div class="services__list" data-stagger data-svc-accordion>
                @foreach ($services as $service)
                    {{-- The row's own artwork becomes its hover background, so each
                         service previews itself in place. --}}
                    <a class="svc" href="{{ url('/services/'.$service->slug) }}"
                       @if ($service->heroUrl()) style="--svc-bg:url('{{ $service->heroUrl() }}')" @endif
                       data-anim="curtain" data-sheen data-cursor="EXPLORE">
                        <h3 class="svc__title">{{ $service->title }}</h3>
                        <span class="svc__arr" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                    </a>
                @endforeach
            </div>
        </x-container>
    </section>

    <!-- TESTIMONIALS -->
    @if ($testimonials->isNotEmpty())
    {{-- Heading left, quotes drifting vertically on the right. The two rails run
         in opposite directions so the pair reads as movement rather than as one
         block sliding; the second is a straight repeat of the same quotes, which
         is why it is aria-hidden in full — it exists to fill the right-hand
         column, not to add content. --}}
    <section class="section" data-screen-label="06 Testimonials">
        {{-- Scene 05 · Testimonials — words, not craft, so the backdrop drops to
             the quiet aperture. --}}
        <x-scene-bg type="photo" />
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

    <!-- OUR WORK -->
    @if ($featuredWorks->isNotEmpty())
    {{-- Scene 06 · Work — a looping strip of the archive. Previews play
         muted inline; hover stops the strip, a click opens the work. Backdrop matches. --}}
    <section class="section" data-screen-label="07 Work">
        <x-scene-bg type="photo" />
        <x-container>
            <div class="services__head" data-stagger>
                <div data-anim="curtain">
                    <span class="section__eyebrow" data-scramble>Portfolio</span>
                    <h2 class="section__title" data-split>Our <em>work.</em></h2>
                </div>
            </div>
            <x-work-marquee :items="$featuredWorks" lightbox-label="Selected work" />
            {{-- The onward link closes the scene: you watch the strip first, then
                 you're offered the full archive. --}}
            <div class="work-marquee__cta" data-anim="rise">
                <a class="btn btn--ghost" href="{{ url('/portfolio') }}" data-cursor="VIEW">View portfolio <span class="arr"></span></a>
            </div>
        </x-container>
    </section>
    @endif

    <!-- CTA -->
    {{-- Scene 07 · CTA — the close. Headline rises, buttons follow, aperture
         breathing behind the red floor-glow. --}}
    <section class="cta-strip" data-screen-label="08 CTA">
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
