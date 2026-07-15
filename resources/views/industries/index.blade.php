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
                <p class="section__lead reveal">No generalists. Each vertical gets a team fluent in its brand guidelines, compliance, audience, and channel.</p>
            </div>
            <div class="ind-hi">
                <div class="ind-hi__card spotlight reveal">
                    <div class="ind-hi__icon">◐</div>
                    <h3 class="ind-hi__h">Premium &amp; regulated</h3>
                    <p class="ind-hi__p">Beverages, automotive, pharma — compliance-aware shoots, brand-guideline conform.</p>
                    <div class="ind-hi__stat"><span class="ind-hi__stat-n">06</span><span class="ind-hi__stat-l">Fortune-500 partners</span></div>
                </div>
                <div class="ind-hi__card spotlight reveal" data-delay="1">
                    <div class="ind-hi__icon">▲</div>
                    <h3 class="ind-hi__h">Corporate &amp; enterprise</h3>
                    <p class="ind-hi__p">Conferences, summits, launches — scalable crews, same-day delivery.</p>
                    <div class="ind-hi__stat"><span class="ind-hi__stat-n">160<em style="color:var(--red); font-style:normal">+</em></span><span class="ind-hi__stat-l">Events / year</span></div>
                </div>
                <div class="ind-hi__card spotlight reveal" data-delay="2">
                    <div class="ind-hi__icon">★</div>
                    <h3 class="ind-hi__h">Weddings &amp; celebrations</h3>
                    <p class="ind-hi__p">Brand-grade cinematic coverage, same-day reels.</p>
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
                    <div class="ind reveal"@if ($loop->index % 2) data-delay="1"@endif>
                        <img src="{{ $heroUrl ?: $fallback }}" alt="" decoding="async">
                        <div class="ind__body">
                            <h3>{{ $industry->title }}</h3>
                            <p>{{ $industry->summary }}</p>
                        </div>
                    </div>
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

    {{-- 7. CTA STRIP --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Speak our <em>vertical.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Tell us your industry — relevant case studies within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Get in touch <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>
