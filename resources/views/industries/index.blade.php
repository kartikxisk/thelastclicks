<x-layouts.app
    title="Industries — TheLastClicks"
    description="Fashion, hospitality, beauty, weddings, automotive — sectors TheLastClicks works with."
    :canonical="url('/industries')"
>

    {{-- 1. PAGE HEADER --}}
    <section class="page-header" data-screen-label="01 Header">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Industries</span></div>
        <h1 data-split>The verticals<br>we <em>know cold.</em></h1>
        <dl class="page-header__meta">
            <div><dt>Active sectors</dt><dd>{{ str_pad($industries->count(), 2, '0', STR_PAD_LEFT) }}</dd></div>
            <div><dt>Brand partners</dt><dd>52+</dd></div>
            <div><dt>Coverage</dt><dd>Pan-India · Intl.</dd></div>
            <div><dt>Compliance</dt><dd>100%</dd></div>
        </dl>
    </section>

    {{-- 2. SPECIALISED HIGHLIGHTS --}}
    <section class="section" style="border-top:1px solid var(--line)" data-screen-label="03 Specialised">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Specialised craft</span>
                    <h2 class="section__title" data-split>Built for <em>each vertical.</em></h2>
                </div>
                <p class="section__lead reveal">Our crews don't generalise. Each vertical gets a team that knows its grammar — brand guidelines, compliance, audience, channel.</p>
            </div>
            <div class="ind-hi">
                <div class="ind-hi__card spotlight reveal">
                    <div class="ind-hi__icon">◐</div>
                    <h3 class="ind-hi__h">Premium &amp; regulated</h3>
                    <p class="ind-hi__p">Beverages, automotive, pharma — disciplined grade pipelines, brand-guideline conform, compliance-aware shoots.</p>
                    <div class="ind-hi__stat"><span class="ind-hi__stat-n">06</span><span class="ind-hi__stat-l">Fortune-500 partners</span></div>
                </div>
                <div class="ind-hi__card spotlight reveal" data-delay="1">
                    <div class="ind-hi__icon">▲</div>
                    <h3 class="ind-hi__h">Corporate &amp; enterprise</h3>
                    <p class="ind-hi__p">Multi-day conferences, executive summits, internal launches — scalable crews, same-day delivery.</p>
                    <div class="ind-hi__stat"><span class="ind-hi__stat-n">160<em style="color:var(--red); font-style:normal">+</em></span><span class="ind-hi__stat-l">Events / year</span></div>
                </div>
                <div class="ind-hi__card spotlight reveal" data-delay="2">
                    <div class="ind-hi__icon">★</div>
                    <h3 class="ind-hi__h">Weddings &amp; celebrations</h3>
                    <p class="ind-hi__p">Cinematic coverage with brand-grade discipline. Same-day reels, treated like a brand film.</p>
                    <div class="ind-hi__stat"><span class="ind-hi__stat-n">120<em style="color:var(--red); font-style:normal">+</em></span><span class="ind-hi__stat-l">Weddings shot</span></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. DYNAMIC INDUSTRIES GRID --}}
    <section class="section" data-screen-label="03 Sectors">
        <div class="wrap">
            <div class="ind-grid">
                @foreach ($industries as $industry)
                    @php
                        $heroUrl = $industry->getFirstMediaUrl('hero') ?: $industry->image_url;
                        $fallback = 'https://images.unsplash.com/photo-1521334884684-d80222895322?w=1200&q=85';
                    @endphp
                    <a class="ind reveal" href="{{ url('/industries/'.$industry->slug) }}" data-cursor="VIEW"@if ($loop->index % 2) data-delay="1"@endif>
                        <img src="{{ $heroUrl ?: $fallback }}" alt="" decoding="async">
                        <div class="ind__body">
                            <h3>{{ $industry->title }}</h3>
                            <p>{{ $industry->summary }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. MARQUEE --}}
    <div class="marquee" aria-hidden="true">
        <div class="marquee__track">
            <span class="marquee__item">Johnnie Walker</span><span class="marquee__item is-outline">The Macallan</span>
            <span class="marquee__item">Hendrick's Gin</span><span class="marquee__item is-outline">Bombay Sapphire</span>
            <span class="marquee__item">Grey Goose</span><span class="marquee__item is-outline">Glenfiddich</span>
            <span class="marquee__item">Tanqueray</span><span class="marquee__item is-outline">Absolut</span>
            <span class="marquee__item">Diageo</span><span class="marquee__item is-outline">Suntory</span>
            <span class="marquee__item">Moët &amp; Chandon</span>
            <span class="marquee__item">Johnnie Walker</span><span class="marquee__item is-outline">The Macallan</span>
            <span class="marquee__item">Hendrick's Gin</span><span class="marquee__item is-outline">Bombay Sapphire</span>
            <span class="marquee__item">Grey Goose</span><span class="marquee__item is-outline">Glenfiddich</span>
            <span class="marquee__item">Tanqueray</span><span class="marquee__item is-outline">Absolut</span>
        </div>
    </div>

    {{-- 5. ABOUT-GRID — Premium & regulated, 2-col text+image --}}
    <section class="section">
        <div class="wrap about-grid">
            <div>
                <span class="section__eyebrow">Premium &amp; regulated</span>
                <h2 data-split>Discipline that <em>scales.</em></h2>
                <p class="reveal">Working with premium and regulated brands requires precision, planning, and a deep understanding of brand guidelines. Our experience in this space has shaped a disciplined approach to lighting, framing, and post-production — ensuring every visual aligns with brand expectations.</p>
                <p class="reveal" data-delay="1">This attention to detail allows us to deliver consistent quality across campaigns, events, and productions of any scale.</p>
            </div>
            <div class="about-img clip-reveal"><img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=85" alt="" loading="lazy" decoding="async"></div>
        </div>
    </section>

    {{-- 6. VACC ACCORDION — Deep-dive verticals --}}
    <section class="section" data-screen-label="04 Deep-dive">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Inside each vertical</span>
                    <h2 class="section__title" data-split>The <em>specifics.</em></h2>
                </div>
                <p class="section__lead reveal">Click open any vertical to see how we approach it — kit, crew, deliverables, and what we've learned shooting it.</p>
            </div>
            <div class="vacc" data-vacc>
                <div class="vacc__row is-open">
                    <div class="vacc__head">
                        <span class="vacc__num">01</span>
                        <h3 class="vacc__name">Premium <em>beverage</em> &amp; lifestyle</h3>
                        <span class="vacc__count">22 brands · 4 yrs</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>We run brand-grade discipline on every shoot — guidelines memorised, compliance frameworks understood, never a flagged frame in delivery.</p>
                            <p>Practical light only. Tabletop on a motion-control rig. Hero shots in-camera, never in post.</p>
                            <div class="vacc__tags"><span>Macro tabletop</span><span>Practical light</span><span>Motion control</span><span>Compliance-aware</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>

                <div class="vacc__row">
                    <div class="vacc__head">
                        <span class="vacc__num">02</span>
                        <h3 class="vacc__name">Corporate &amp; <em>enterprise</em></h3>
                        <span class="vacc__count">186+ events / yr</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>Multi-day conferences, executive summits, internal launches. Scalable crews — one stage or twelve.</p>
                            <p>Live mix on-site for stream, recap reel cut overnight. Selects delivered to marketing within two hours of session end.</p>
                            <div class="vacc__tags"><span>Multi-cam</span><span>Live mix</span><span>Same-day delivery</span><span>Scalable</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>

                <div class="vacc__row">
                    <div class="vacc__head">
                        <span class="vacc__num">03</span>
                        <h3 class="vacc__name">Automotive &amp; <em>luxury</em></h3>
                        <span class="vacc__count">14 reveals · 2024–26</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>Vehicle reveals on closed sets and city locations. Phantom Flex 4K for 1000fps, motion-control for studio hero shots.</p>
                            <p>Signature deep-cyan grade — built into our DaVinci pipeline as a brand LUT, applied identically across every cut.</p>
                            <div class="vacc__tags"><span>Phantom Flex</span><span>Motion control</span><span>VFX-ready</span><span>Closed sets</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>

                <div class="vacc__row">
                    <div class="vacc__head">
                        <span class="vacc__num">04</span>
                        <h3 class="vacc__name">Destination <em>weddings</em></h3>
                        <span class="vacc__count">120+ shot · 4 destinations</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>Treated like brand films — directed, treatment-led, story-first. Two photo, one film crew minimum.</p>
                            <p>Same-day reel cut on-site before guests leave. Full film delivered in 2–4 weeks with original sound design.</p>
                            <div class="vacc__tags"><span>Same-day reel</span><span>Story-led</span><span>Drone arrivals</span><span>Original score</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>

                <div class="vacc__row">
                    <div class="vacc__head">
                        <span class="vacc__num">05</span>
                        <h3 class="vacc__name">Commercial &amp; <em>brand films</em></h3>
                        <span class="vacc__count">32% of slate</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>Treatment-led brand storytelling. Commercials, brand films, launches, anthems. Always with a director on set.</p>
                            <p>In-house grade, in-house sound — never outsourced. Brand-guideline conform on every master.</p>
                            <div class="vacc__tags"><span>Treatment-led</span><span>Director on set</span><span>In-house finish</span><span>Brand-conform</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>

                <div class="vacc__row">
                    <div class="vacc__head">
                        <span class="vacc__num">06</span>
                        <h3 class="vacc__name">Editorial &amp; <em>fashion</em></h3>
                        <span class="vacc__count">18% of slate</span>
                        <span class="vacc__toggle"></span>
                    </div>
                    <div class="vacc__body"><div class="vacc__inner">
                        <div class="vacc__copy">
                            <p>Hasselblad H6D for skin tones. Editorial lookbooks, magazine spreads, brand campaigns.</p>
                            <p>Minimal retouch — we shoot for the final frame, not the post-production. Film-tone stills are our default.</p>
                            <div class="vacc__tags"><span>Hasselblad</span><span>Minimal retouch</span><span>Editorial</span><span>Lookbook</span></div>
                        </div>
                        <div class="vacc__media"><img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1600&q=85" alt="" loading="lazy" decoding="async"></div>
                    </div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. CTA STRIP --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Speak our <em>vertical.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Tell us your industry — we'll send relevant case studies within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Get in touch <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

    <script>
    // Deep-dive vertical accordion — toggle .is-open on .vacc__row heads (ported from design)
    (function () {
      document.querySelectorAll('[data-vacc]').forEach(acc => {
        acc.querySelectorAll('.vacc__row').forEach(row => {
          row.querySelector('.vacc__head').addEventListener('click', () => {
            const isOpen = row.classList.contains('is-open');
            acc.querySelectorAll('.vacc__row').forEach(r => r.classList.remove('is-open'));
            if (!isOpen) row.classList.add('is-open');
          });
        });
      });
    })();
    </script>

</x-layouts.app>
