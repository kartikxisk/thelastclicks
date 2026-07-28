<x-layouts.app
    title="Portfolio — Film & Photography | TheLastClicks"
    description="Selected films and photography from TheLastClicks — brand campaigns, corporate productions, automotive shoots, launches and weddings across 20+ Indian cities."
    :canonical="url('/portfolio')"
>
    <x-slot name="head">
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Portfolio', 'item' => url('/portfolio')],
        ]]" />
        @if ($works->isNotEmpty())
            <x-json-ld :data="[
                '@type' => 'CollectionPage',
                'name' => 'Portfolio',
                'url' => url('/portfolio'),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'itemListElement' => $works->values()->map(fn ($work, $i) => array_filter([
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $work->title,
                        'image' => $work->coverUrl(),
                    ]))->all(),
                ],
            ]" />
        @endif
    </x-slot>

    {{-- HEADER --}}
    @php $pageHeader = \App\Models\SiteSetting::pageImage('works'); @endphp
    <section class="page-header page-header--media" data-screen-label="01 Header" @if ($pageHeader) style="--ph-bg:url('{{ $pageHeader }}')" @endif>
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Portfolio</span></div>
        <h1 data-split>Our <em>portfolio.</em></h1>
    </section>

    {{-- GRID --}}
    @if ($works->isNotEmpty())
        @php
            // Only offer a chip when something is actually filed under it — an empty
            // filter that always yields nothing is worse than no filter.
            $usedCategories = collect(\App\Models\Work::CATEGORIES)
                ->filter(fn ($label, $slug) => $works->contains(fn ($w) => $w->category === $slug));

            $usedCrafts = collect(\App\Models\Work::CRAFTS)
                ->filter(fn ($label, $slug) => $works->contains(fn ($w) => in_array($slug, $w->craftSlugs(), true)));
        @endphp

        <section class="section" data-screen-label="02 Work">
            <x-scene-bg type="photo" />
            <x-container>
                @if ($usedCategories->isNotEmpty() || $usedCrafts->isNotEmpty())
                    <div class="work-filters" data-anim="rise" data-work-filters role="group" aria-label="Filter work">
                        <button type="button" class="work-filters__chip is-on" data-filter="all" aria-pressed="true">All</button>

                        @foreach ($usedCategories as $slug => $label)
                            <button type="button" class="work-filters__chip" data-filter="cat:{{ $slug }}" aria-pressed="false">{{ $label }}</button>
                        @endforeach

                        @if ($usedCrafts->isNotEmpty())
                            <span class="work-filters__sep" aria-hidden="true"></span>
                            <span class="work-filters__label">In-house</span>
                            @foreach ($usedCrafts as $slug => $label)
                                <button type="button" class="work-filters__chip work-filters__chip--craft" data-filter="craft:{{ $slug }}" aria-pressed="false">{{ $label }}</button>
                            @endforeach
                        @endif
                    </div>
                @endif

                <x-media-grid :items="$works" layout="bento" />

                <p class="work-filters__empty" data-work-empty hidden>Nothing filed under that yet.</p>
            </x-container>
        </section>
    @endif

    {{-- CLIENT MARQUEE — closes the page: the work makes the case first, the
         client list is the proof that follows it. --}}
    <x-clients-marquee />
</x-layouts.app>
