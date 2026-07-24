<x-layouts.app
    title="Industries — Brand, Auto & Wedding Film | TheLastClicks"
    description="Fashion, hospitality, beauty, weddings, automotive, corporate and nightlife — the sectors TheLastClicks produces photography and film for across India."
    :canonical="url('/industries')"
>

    {{-- 1. PAGE HEADER --}}
    <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=1800&q=80')">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Industries</span></div>
        <h1 data-split>The verticals<br>we <em>know cold.</em></h1>
    </section>

    {{-- 3. INDUSTRIES --}}
    @if ($industries->isNotEmpty())
        <section class="section" data-screen-label="03 Industries">
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

    {{-- 4. MARQUEE --}}
    <x-clients-marquee />

    {{-- 7. CTA STRIP --}}
    <section class="cta-strip">
        <x-container>
            <h2 class="cta-strip__title" data-split>Speak our <em>vertical.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Tell us your industry — relevant case studies within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Get in touch <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
