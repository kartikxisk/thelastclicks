<x-layouts.app
    :title="\App\Support\Brand::title('Brand, Auto & Wedding Film')"
    description="Fashion, hospitality, beauty, weddings, automotive, corporate and nightlife — the sectors TheLastClicks produces photography and film for across India."
    :canonical="url('/industries')"
>
    <x-slot name="head">
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Industries', 'item' => url('/industries')],
        ]]" />
        @if ($industries->isNotEmpty())
            <x-json-ld :data="[
                '@type' => 'CollectionPage',
                'name' => 'Industries we produce for',
                'url' => url('/industries'),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'itemListElement' => $industries->values()->map(fn ($industry, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $industry->title,
                    ])->all(),
                ],
            ]" />
        @endif
    </x-slot>

    {{-- 1. PAGE HEADER --}}
    @php $pageHeader = \App\Models\SiteSetting::pageImage('industries'); @endphp
    <section class="page-header page-header--media" data-screen-label="01 Header" @if ($pageHeader) style="--ph-bg:url('{{ $pageHeader }}')" @endif>
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Industries</span></div>
        <h1 data-split>The verticals<br>we <em>know cold.</em></h1>
    </section>

    {{-- 3. INDUSTRIES --}}
    @if ($industries->isNotEmpty())
        <section class="section" data-screen-label="03 Industries">
            <x-scene-bg type="grid" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Industries</span>
                        <h2 class="section__title" data-split>What we <em>cover.</em></h2>
                    </div>
                </div>
                {{-- Detail pages are live again, so a tile opens the vertical's own
                     page rather than the quote wizard. The wizard is still one
                     click away from the CTA at the bottom of that page, and this
                     way the deck leads somewhere that argues the case. --}}
                <x-media-grid
                    :items="$industries"
                    layout="grid"
                    :link="fn ($industry) => url('/industries/'.$industry->slug)" />
            </x-container>
        </section>
    @endif

    {{-- 4. MARQUEE --}}
    <x-clients-marquee />

    {{-- 7. CTA STRIP --}}
    <section class="cta-strip">
        <x-scene-bg type="photo" />
        <x-container data-stagger>
            <h2 class="cta-strip__title" data-split data-anim="mask-up">Tell us your industry.<br>Or see how well we <em>already speak it.</em></h2>
            <div class="cta-strip__row" data-anim="rise">
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Get in touch <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
