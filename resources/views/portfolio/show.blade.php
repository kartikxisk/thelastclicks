<x-layouts.app
    :title="$item->title.' — Portfolio — TheLastClicks'"
    :description="$item->client && $item->year ? $item->client.' · '.$item->year : ($item->client ?? (string) $item->year)"
    :canonical="url('/portfolio/'.$item->slug)"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'       => 'CreativeWork',
            'name'        => $item->title,
            'creator'     => ['@type' => 'Organization', 'name' => 'TheLastClicks'],
            'dateCreated' => (string) $item->year,
        ]" />
<style>
.case-hero { max-width: var(--maxw); margin-inline: auto; padding: 140px var(--pad-x) 0; }
.case-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 28px; display:flex; gap:10px; }
.case-hero__crumb a { color: var(--paper-dim); }
.case-hero__crumb a:hover { color: var(--red); }
.case-hero h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(48px, 8vw, 120px); letter-spacing: -0.04em; line-height: 0.96; margin-bottom: 36px; }
.case-hero h1 em { font-family: var(--f-serif); font-style: italic; font-weight: 400; color: var(--red); }
.case-meta { display: grid; grid-template-columns: repeat(4,1fr); gap: 28px; padding: 32px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); margin-bottom: 48px; }
.case-meta dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 8px; }
.case-meta dd { font-family: var(--f-display); font-weight: 500; font-size: 17px; }
.case-cover { aspect-ratio: 16/9; overflow: hidden; }
.case-cover img { width: 100%; height: 100%; object-fit: cover; }
.case-body { padding: 100px var(--pad-x); display: grid; grid-template-columns: 280px 1fr; gap: 80px; max-width: var(--maxw); margin: 0 auto; }
.case-body h2 { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--red); }
.case-body p { font-size: 19px; line-height: 1.55; color: var(--paper); }
.case-body p + p { margin-top: 22px; }
.gallery { max-width: var(--maxw); margin-inline: auto; display: grid; grid-template-columns: repeat(12, 1fr); gap: 12px; padding: 0 var(--pad-x) 80px; }
.gallery .g { overflow: hidden; }
.gallery .g img { width:100%; height:100%; object-fit: cover; transition: transform 1s var(--ease); }
.gallery .g:hover img { transform: scale(1.04); }
.gallery .g--video { background: #000; }
.gallery .g--video video { display:block; width:100%; height:auto; max-height:82vh; object-fit:contain; margin-inline:auto; }
.g--6 { grid-column: span 6; aspect-ratio: 3/2; }
.g--12 { grid-column: span 12; aspect-ratio: 16/7; }
.g--4 { grid-column: span 4; aspect-ratio: 3/4; }
.g--8 { grid-column: span 8; aspect-ratio: 2/1; }
.case-credits { max-width: var(--maxw); margin-inline: auto; padding: 80px var(--pad-x); border-top: 1px solid var(--line); display:grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
.case-credits dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom:8px; }
.case-credits dd { font-size:15px; }
.case-next { max-width: var(--maxw); margin-inline: auto; padding: 100px var(--pad-x); text-align:center; }
.case-next a { font-family: var(--f-display); font-weight:700; font-size: clamp(40px, 7vw, 96px); letter-spacing: -0.03em; }
.case-next a em { font-family: var(--f-serif); font-style: italic; font-weight:400; color: var(--red); }
@media (max-width: 880px) {
  .case-hero { padding: 110px 20px 0; }
  .case-meta { grid-template-columns: 1fr 1fr; gap: 18px; padding: 22px 0; margin-bottom: 28px; }
  .case-body { grid-template-columns: 1fr; gap: 24px; padding: 56px 20px; }
  .case-body p { font-size: 16.5px; }
  .gallery { padding: 0 20px 56px; gap: 8px; }
  .gallery > .g { grid-column: span 12; aspect-ratio: 3/2; }
  .case-credits { grid-template-columns: 1fr 1fr; padding: 48px 20px; }
}
</style>
    </x-slot>

    @php
        $cover = $item->getFirstMediaUrl('cover') ?: $item->cover_url;
        $next = \App\Models\Portfolio::published()->where('id', '>', $item->id)->orderBy('id')->first()
            ?? \App\Models\Portfolio::published()->where('id', '!=', $item->id)->orderBy('id')->first();
    @endphp

    {{-- HERO --}}
    <section class="case-hero" data-screen-label="01 Hero">
        <div class="case-hero__crumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/portfolio') }}">Portfolio</a>
            <span>/</span>
            <span>{{ $item->title }}</span>
        </div>
        <h1 data-split>{!! $item->hero_html ?: e($item->title) !!}</h1>
        <dl class="case-meta">
            <div><dt>Client</dt><dd>{{ $item->client }}</dd></div>
            <div><dt>Discipline</dt><dd>{{ $item->service?->title }}</dd></div>
            <div><dt>Year</dt><dd>{{ $item->year }}</dd></div>
            <div><dt>Location</dt><dd>{{ $item->location }}</dd></div>
        </dl>
        @if ($cover)
            <div class="case-cover clip-reveal">
                <img src="{{ $cover }}" alt="{{ $item->title }}" decoding="async">
            </div>
        @endif
    </section>

    {{-- BRIEF --}}
    @if ($item->body)
        <section class="case-body">
            <div><h2>The brief</h2></div>
            <div>{!! $item->body !!}</div>
        </section>
    @endif

    {{-- GALLERY --}}
    <section class="gallery">
        @php $spans = ['g--6', 'g--12', 'g--4', 'g--8']; @endphp
        @forelse ($item->gallery_urls ?? [] as $i => $src)
            @if (str_ends_with($src, '.mp4'))
                <div class="g g--12 g--video reveal">
                    <video src="{{ $src }}" controls playsinline preload="metadata"
                           poster="{{ str_replace(['/videos/', '.mp4'], ['/videos/posters/', '.jpg'], $src) }}"></video>
                </div>
            @else
                <div class="g {{ $spans[$i % count($spans)] }} reveal">
                    <img src="{{ $src }}" alt="" loading="lazy" decoding="async">
                </div>
            @endif
        @empty
            @if ($cover)
                <div class="g g--12 reveal">
                    <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
                </div>
            @endif
        @endforelse
    </section>

    {{-- APPROACH --}}
    @if ($item->approach)
        <section class="case-body">
            <div><h2>Approach</h2></div>
            <div>{!! $item->approach !!}</div>
        </section>
    @endif

    {{-- CREDITS --}}
    @if (!empty($item->credits))
        <section class="case-credits">
            @foreach ($item->credits as $role => $name)
                <div><dt>{{ $role }}</dt><dd>{{ $name }}</dd></div>
            @endforeach
        </section>
    @endif

    {{-- NEXT --}}
    @if ($next)
        <section class="case-next">
            <a href="{{ url('/portfolio/'.$next->slug) }}">{{ $next->title }} <em>&rarr;</em></a>
        </section>
    @endif

</x-layouts.app>
