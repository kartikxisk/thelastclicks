<x-layouts.app
    :title="$industry->title.' — Industries — TheLastClicks'"
    :description="$industry->summary"
    :canonical="url('/industries/'.$industry->slug)"
    :ogImage="$industry->coverUrl()"
>
    <x-slot name="head">
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Industries', 'item' => url('/industries')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $industry->title, 'item' => url('/industries/'.$industry->slug)],
        ]]" />
    </x-slot>

    @php
        $cover = $industry->coverUrl() ?: 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=1800&q=80';
        $tiles = $industry->mediaTiles();
        // Index-aligned lightbox payload (thumb stripped) shared by every tile.
        $payload = array_map(fn ($t) => ['type' => $t['type'], 'url' => $t['url'], 'caption' => $t['caption']], $tiles);
    @endphp

    {{-- 01 HEADER --}}
    <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('{{ $cover }}')">
        <div class="page-header__crumb">
            <a href="{{ url('/') }}">Home</a><span>/</span>
            <a href="{{ url('/industries') }}">Industries</a><span>/</span>
            <span>{{ $industry->title }}</span>
        </div>
        <h1 data-split>{{ $industry->title }}</h1>
        @if ($industry->summary)
            <p class="reveal" style="max-width:60ch;margin-top:18px;color:var(--paper-dim);font-size:18px;line-height:1.6">{{ $industry->summary }}</p>
        @endif
    </section>

    {{-- 02 BODY --}}
    @if (filled($industry->body))
        <section class="section" data-screen-label="02 Overview">
            <x-container>
                <article class="art-body">
                    {!! $industry->body !!}
                </article>
            </x-container>
        </section>
    @endif

    {{-- 03 MEDIA GALLERY --}}
    @if (count($tiles))
        <section class="section" data-screen-label="03 Work">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Selected work</span>
                        <h2 class="section__title" data-split>The <em>output.</em></h2>
                    </div>
                </div>
                <div class="work-grid work-grid--fixed" data-work-grid>
                    @foreach ($tiles as $i => $t)
                        <button
                            type="button"
                            class="work-tile reveal"
                            data-delay="{{ $i % 4 }}"
                            data-work-tile
                            data-work-media='@json($payload)'
                            data-work-index="{{ $i }}"
                            aria-label="View media {{ $i + 1 }}"
                        >
                            @if ($t['type'] === 'video')
                                <video src="{{ $t['url'] }}" muted playsinline preload="metadata"></video>
                                <span class="work-tile__play" aria-hidden="true"></span>
                            @else
                                <img src="{{ $t['thumb'] }}" alt="{{ $t['caption'] ?: $industry->title }}" loading="lazy" decoding="async">
                                @if ($t['type'] === 'youtube')
                                    <span class="work-tile__play" aria-hidden="true"></span>
                                @endif
                            @endif
                            <span class="work-tile__scrim" aria-hidden="true"></span>
                            @if ($t['caption'])
                                <span class="work-tile__body">
                                    <span class="work-tile__meta">{{ $t['caption'] }}</span>
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-container>
        </section>

        @once
        <div class="wlb" data-work-lightbox hidden role="dialog" aria-modal="true" aria-label="Industry media">
            <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
            <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
            <div class="wlb__stage" data-wlb-stage></div>
            <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
            <p class="wlb__caption" data-wlb-caption></p>
        </div>
        @endonce
    @endif

    {{-- 04 CTA --}}
    <section class="cta-strip">
        <x-container>
            <h2 class="cta-strip__title" data-split>Speak our <em>vertical.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Tell us about your {{ Str::lower($industry->title) }} brief — relevant case studies within 4 working hours.</p>
                <a class="btn btn--red" href="{{ url('/contact') }}" data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
            </div>
        </x-container>
    </section>
</x-layouts.app>
