<x-layouts.app
    title="Our Work — Film & Photography Portfolio | TheLastClicks"
    description="Selected films and photography from TheLastClicks — brand campaigns, corporate productions, automotive shoots, launches and weddings across 20+ Indian cities."
    :canonical="url('/our-works')"
>
    {{-- HEADER --}}
    <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1800&q=80')">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Our Work</span></div>
        <h1 data-split>Our <em>work.</em></h1>
    </section>

    {{-- CLIENT MARQUEE --}}
    <x-clients-marquee />

    {{-- GRID --}}
    @if ($works->isNotEmpty())
        <section class="section" data-screen-label="02 Work">
            <x-container>
                <x-media-grid :items="$works" />
            </x-container>
        </section>
    @endif
</x-layouts.app>
