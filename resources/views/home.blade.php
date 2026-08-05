<x-layouts.app
    title="TheLastClicks — Cinematic photography & film production"
    description="Cinematic photography, brand films and post-production for premium teams across India — trusted by global enterprise brands, automotive names and national institutions."
    :canonical="url('/')"
>
    <x-slot name="head">
        @php
            // sameAs is the strongest entity signal Google reads for a brand — feed it every
            // social profile the admin has configured. array_filter drops anything unset.
            $orgSameAs = array_values(array_filter((array) (\App\Models\SiteSetting::get('socials') ?? [])));
            $orgEmail = \App\Models\SiteSetting::get('contact_email');
            $orgPhone = \App\Models\SiteSetting::get('contact_phone');
        @endphp
        <x-json-ld :data="array_filter([
            '@type'        => 'Organization',
            'name'         => 'TheLastClicks',
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
            <div class="disc__grid" data-stagger>
                <div class="disc__lead" data-anim="curtain">
                    <span class="section__eyebrow reveal">Why TheLastClicks</span>
                    <h2 class="section__title" data-split>Built on the discipline of <em>premium brands.</em></h2>
                    <p class="disc__kicker reveal">Not a vendor — a long-term partner that scales with your story.</p>
                </div>
                <div class="disc__copy" data-anim="slide-r">
                    <p>Brands choose us because we deliver trust, not just footage. Every shoot — wedding, brand, commercial, or corporate — is run with the same discipline: show up prepared, protect the brief, deliver work that holds up under scrutiny.</p>
                    <p>That discipline is why our client list spans far beyond weddings and product launches — we've delivered for some of the country's most demanding organisations, from <strong>national institutions and defence forces</strong> to <strong>global enterprise brands</strong> and leading automotive names.</p>
                    <p>We don't chase "good enough." Every project is a chance to be better than the last one — sharper frames, tighter edits, stronger stories.</p>
                </div>
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
                <div data-anim="mask-up">
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
            <div class="services__list" data-stagger>
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
    {{-- Stacked deck: every testimonial is a real card in the pile, so advancing
         retargets each card's transition from wherever it currently sits rather
         than restarting an animation. The two cards behind the active one are
         the next two testimonials, drawn as outlines. --}}
    <section class="tdeck" data-screen-label="06 Testimonials" data-tdeck>
        {{-- Scene 05 · Testimonials — words, not craft, so the backdrop drops to
             the quiet aperture and the deck resolves out of blur, like a lens
             pulling focus onto the speaker. --}}
        <x-scene-bg type="photo" />
        <x-container style="margin-bottom:48px" data-stagger>
            <span class="section__eyebrow" data-scramble data-anim="rise">Client Stories</span>
            <h2 class="section__title" data-split data-anim="mask-up">What our <em>clients say</em></h2>
        </x-container>

        <div class="tdeck__stage">
            @if ($testimonials->count() > 1)
                <button type="button" class="tdeck__arr tdeck__arr--prev" data-tdeck-prev aria-label="Previous testimonial">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                </button>
            @endif

            <div class="tdeck__deck" data-anim="blur-focus"
                 tabindex="0"
                 role="group"
                 aria-roledescription="carousel"
                 aria-label="Client testimonials">
                @foreach ($testimonials as $t)
                    <article class="tdeck__card" data-tdeck-card
                             role="group"
                             aria-roledescription="slide"
                             aria-label="Testimonial {{ $loop->iteration }} of {{ $testimonials->count() }}">
                        <p class="tdeck__quote">{{ $t->quote }}</p>
                        <p class="tdeck__who">
                            {{ $t->client_name }}@if ($t->role_company), {{ $t->role_company }}@endif
                        </p>
                    </article>
                @endforeach
            </div>

            @if ($testimonials->count() > 1)
                <button type="button" class="tdeck__arr tdeck__arr--next" data-tdeck-next aria-label="Next testimonial">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif
        </div>

        {{-- Announces the swap to screen readers; the visual change alone is silent. --}}
        <p class="sr-only" aria-live="polite" data-tdeck-status></p>
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
                <div data-anim="mask-up">
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
