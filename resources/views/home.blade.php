<x-layouts.app
    title="TheLastClicks — Cinematic photography & film production"
    description="Cinematic photography, brand films and post-production for premium teams across India — trusted by Fortune 500 brands, automotive houses and the armed forces."
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
    <section class="section disc" data-screen-label="02 Discipline">
        <x-container>
            <div class="disc__grid">
                <div class="disc__lead">
                    <span class="section__eyebrow">Why The Last Clicks</span>
                    <h2 class="section__title" data-split>Built on the discipline of <em>premium brands.</em></h2>
                    <p class="disc__kicker reveal">Not a vendor — a long-term partner that scales with your story.</p>
                </div>
                <div class="disc__copy reveal" data-delay="1">
                    <p>Brands choose us because we deliver trust, not just footage. Every shoot — wedding, brand, commercial, or corporate — is run with the same discipline: show up prepared, protect the brief, deliver work that holds up under scrutiny.</p>
                    <p>That discipline is why our client list spans far beyond weddings and product launches — we've delivered for the nation's most demanding institutions, including the <strong>Indian Navy, Indian Army, and BSF</strong>, alongside <strong>Fortune 500 brands</strong> and automotive houses.</p>
                    <p>We don't chase "good enough." Every project is a chance to be better than the last one — sharper frames, tighter edits, stronger stories.</p>
                </div>
            </div>

            <div class="disc__stats">
                <div class="disc__stat reveal">
                    <div class="disc__num"><span data-count="5">0</span><em>+</em></div>
                    <span class="disc__lab">Years of experience</span>
                </div>
                <div class="disc__stat reveal" data-delay="1">
                    <div class="disc__num"><span data-count="20">0</span><em>+</em></div>
                    <span class="disc__lab">Cities covered across India</span>
                </div>
                <div class="disc__stat reveal" data-delay="2">
                    <div class="disc__num"><span data-count="1000">0</span><em>+</em></div>
                    <span class="disc__lab">Events &amp; activations over the last decade</span>
                </div>
            </div>
        </x-container>
    </section>

    <!-- INDUSTRIES -->
    @if ($industries->isNotEmpty())
    <section class="section" data-screen-label="02 Industries">
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">Industries</span>
                    <h2 class="section__title" data-split>What we <em>cover.</em></h2>
                </div>
            </div>
            <x-media-grid :items="$industries" :meta="fn ($industry) => $industry->summary" meta-class="work-tile__desc" layout="grid" :link="fn ($industry) => url('/industries/'.$industry->slug)" />
        </x-container>
    </section>
    @endif

    <!-- SERVICES -->
    <section class="section services" id="services" data-screen-label="03 Services">
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Our Services</span>
                    <h2 class="section__title" data-split>What <em>we do</em></h2>
                </div>
            </div>
            <div class="services__list">
                @foreach ($services as $service)
                    <a class="svc reveal" href="{{ url('/services/'.$service->slug) }}"
                       data-preview="{{ $service->getFirstMediaUrl('hero') ?: $service->hero_url }}"
                       data-delay="{{ $loop->index }}" data-cursor="EXPLORE">
                        <span class="svc__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="svc__title">{{ $service->title }}</h3>
                        @if (!empty($service->proof['sectors'] ?? ''))
                            <div class="svc__tags">{{ $service->proof['sectors'] }}</div>
                        @endif
                        <span class="svc__arr"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                    </a>
                @endforeach
            </div>
        </x-container>
    </section>

    <!-- TESTIMONIALS -->
    @if ($testimonials->isNotEmpty())
    <section class="car" data-carousel data-screen-label="06 Testimonials">
        <x-container style="margin-bottom:32px">
            <span class="section__eyebrow" data-scramble>Client Stories</span>
            <h2 class="section__title" data-split>What our <em>clients say</em></h2>
        </x-container>
        <div class="car__viewport">
            <div class="car__track" data-car-track>
                @foreach ($testimonials as $t)
                    <article class="car__slide {{ $loop->first ? 'is-on' : '' }}">
                        <span class="av">{{ collect(explode(' ', $t->client_name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                        <p class="car__quote">"{{ $t->quote }}"</p>
                        <div class="who">
                            <span class="who__name">{{ $t->client_name }}</span>
                            @if ($t->role_company)
                                <span class="who__role">{{ $t->role_company }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
        <x-container class="car__nav">
            <div class="car__dots">
                @foreach ($testimonials as $t)
                    <button type="button" class="car__dot {{ $loop->first ? 'is-on' : '' }}" aria-label="Go to slide {{ $loop->iteration }}" data-cursor="{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}"></button>
                @endforeach
            </div>
            <div class="car__btns">
                <button type="button" class="car__prev" aria-label="Previous testimonial" data-cursor="PREV"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 18L9 12L15 6"/></svg></button>
                <button type="button" class="car__next" aria-label="Next testimonial" data-cursor="NEXT"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 6L15 12L9 18"/></svg></button>
            </div>
        </x-container>
    </section>
    @endif

    <!-- OUR WORK -->
    @if ($featuredWorks->isNotEmpty())
    <section class="section" data-screen-label="07 Work">
        <x-container>
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Our Work</span>
                    <h2 class="section__title" data-split>Selected <em>work.</em></h2>
                </div>
                <a class="btn btn--ghost" href="{{ url('/our-works') }}" data-cursor="VIEW">View all work <span class="arr"></span></a>
            </div>
            <x-media-grid :items="$featuredWorks" layout="bento" />
        </x-container>
    </section>
    @endif

    <!-- CTA -->
    <section class="cta-strip" data-screen-label="08 CTA">
        <x-container>
            <h2 class="cta-strip__title" data-split>Let's create<br>something <em>impactful.</em></h2>
            <div class="cta-strip__row reveal" data-delay="2">
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    <a class="btn btn--red" href="{{ url('/contact') }}" data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
                    <a class="btn btn--ghost" href="{{ url('/services/photography') }}" data-cursor="VIEW">Explore services <span class="arr"></span></a>
                </div>
            </div>
        </x-container>
    </section>

</x-layouts.app>
