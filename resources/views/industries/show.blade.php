<x-layouts.app
    :title="\App\Support\Brand::title($industry->title)"
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

        @if ($works->isNotEmpty())
            <x-json-ld :data="[
                '@type'      => 'CollectionPage',
                'name'       => $industry->title,
                'url'        => url('/industries/'.$industry->slug),
                'mainEntity' => [
                    '@type'           => 'ItemList',
                    'itemListElement' => $works->values()->map(fn ($work, $i) => array_filter([
                        '@type'    => 'ListItem',
                        'position' => $i + 1,
                        'name'     => $work->title,
                        'image'    => $work->coverUrl(),
                    ]))->all(),
                ],
            ]" />
        @endif
    </x-slot>

    {{-- 01 HEADER — the same full-bleed media header /about, /portfolio and each
         service page use, so an industry does not open on a different shape to
         the rest of the site. --}}
    @php $cover = $industry->coverUrl(); @endphp
    <section class="page-header page-header--media" data-screen-label="01 Header"
             @if ($cover) style="--ph-bg:url('{{ $cover }}')" @endif>
        <div class="page-header__crumb">
            <a href="{{ url('/') }}">Home</a><span>/</span>
            <a href="{{ url('/industries') }}">Industries</a><span>/</span>
            <span>{{ $industry->title }}</span>
        </div>
        <h1>{{ $industry->title }}</h1>
        @if ($industry->summary)
            <p class="page-header__lead">{{ $industry->summary }}</p>
        @endif
    </section>

    {{-- 02 THE CASE — the industry's own copy. Rich text from the admin, so it
         is echoed unescaped; Filament's RichEditor is the only writer. --}}
    @if ($industry->body)
        <section class="section" data-screen-label="02 Overview">
            <x-scene-bg type="camera" />
            <x-container>
                <div class="disc__grid">
                    <div class="disc__head">
                        <span class="section__eyebrow">What we cover</span>
                        <h2 class="section__title">Shooting for <em>{{ $industry->title }}.</em></h2>
                    </div>
                    <div class="disc__copy">{!! $industry->body !!}</div>
                </div>
            </x-container>
        </section>
    @endif

    {{-- 03 WORK — the projects filed under this industry. Tiles are not linked:
         /portfolio/{slug} is a retired route that 301s to home, so linking them
         would walk a visitor off the page they are reading. The grid opens its
         own lightbox instead, exactly as /portfolio and the service pages do. --}}
    @if ($works->isNotEmpty())
        <section class="pp-gallery-section" data-screen-label="03 Work">
            <x-scene-bg type="photo" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Selected work</span>
                        <h2 class="section__title">The <em>output.</em></h2>
                    </div>
                </div>
                <x-media-grid
                    :items="$works"
                    layout="bento"
                    :meta="fn ($work) => collect([$work->client, $work->categoryLabel()])->filter()->implode(' · ')"
                    lightboxLabel="{{ $industry->title }} work"
                />
            </x-container>
        </section>
    @endif

    {{-- 04 PROOF — only rendered when this industry actually has testimonials.
         An empty band reads as a broken section rather than a quiet one. --}}
    @php $testimonials = $industry->testimonials; @endphp
    @if ($testimonials->isNotEmpty())
        <section class="section" data-screen-label="04 Proof">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Proof</span>
                        <h2 class="section__title">What they <em>said.</em></h2>
                    </div>
                </div>
                <div class="tstm__grid">
                    @foreach ($testimonials as $t)
                        <figure class="tstm">
                            <blockquote>{{ $t->quote }}</blockquote>
                            <figcaption>{{ $t->author }}@if ($t->role) <span>{{ $t->role }}</span>@endif</figcaption>
                        </figure>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 05 OTHER INDUSTRIES — a shoot rarely sits in one vertical, and without
         this each detail page was a dead end with one route back. Excludes the
         page it is on, and renders nothing when there is nowhere else to go. --}}
    @php
        $otherIndustries = \App\Models\Industry::whereKeyNot($industry->id)
            ->orderBy('order')->orderBy('id')->get(['slug', 'title']);
    @endphp
    @if ($otherIndustries->isNotEmpty())
        <section class="section" data-screen-label="05 More">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Also</span>
                        <h2 class="section__title">Other <em>industries.</em></h2>
                    </div>
                </div>
                <nav class="ind-cross" aria-label="Other industries">
                    @foreach ($otherIndustries as $other)
                        <a class="ind-cross__link" href="{{ url('/industries/'.$other->slug) }}">
                            {{ $other->title }} <span class="arr"></span>
                        </a>
                    @endforeach
                </nav>
            </x-container>
        </section>
    @endif

    <x-clients-marquee />

    <section class="cta-strip">
        <x-scene-bg type="photo" />
        <x-container>
            <h2 class="cta-strip__title">Shooting for {{ $industry->title }}?<br>Let's talk about <em>your brief.</em></h2>
            <div class="cta-strip__row">
                <a class="btn btn--red" href="#quote" data-quote-trigger
                   data-quote-prefill="{{ $industry->title }}" data-magnetic data-cursor="START">
                    Get in touch <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>
</x-layouts.app>
