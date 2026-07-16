<x-layouts.app
    title="Portfolio — TheLastClicks"
    description="Cinematic photography and brand films. Selected work from TheLastClicks."
    :canonical="url('/portfolio')"
>
    <x-slot name="head">
    <style>
  /* ============================================================
     PORTFOLIO — editorial, taste-skill applied
     ============================================================ */

  /* Hero */
  .pf-hero { max-width: var(--maxw); margin-inline: auto; padding: 130px var(--pad-x) 0; }
  .pf-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); display: flex; gap: 10px; margin-bottom: 28px; }
  .pf-hero__crumb a { color: var(--paper-dim); }
  .pf-hero__crumb a:hover { color: var(--red); }
  .pf-hero__row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 80px; align-items: end; padding-bottom: 48px; border-bottom: 1px solid var(--line); }
  .pf-hero h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(38px, 5.5vw, 88px); letter-spacing: -0.04em; line-height: 0.95; }
  .pf-hero h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-hero__lead { font-size: 18px; line-height: 1.55; color: var(--paper-dim); max-width: 38ch; }

  /* Work grid — friendly cards, title under image */
  .pf-work { padding: 56px 0 24px; }
  .pf-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px 20px;
  }
  .pf-yearline {
    grid-column: 1 / -1;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 20px;
    padding: 36px 0 12px;
    border-bottom: 1px solid var(--line);
    font-family: var(--f-mono);
  }
  .pf-yearline:first-child { padding-top: 0; }
  .pf-yearline span { font-size: 12.5px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--paper); }
  .pf-yearline em { font-style: normal; font-size: 10.5px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--paper-dim); }
  .pf-card { display: block; }
  .pf-card__media {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: var(--ink-2);
  }
  .pf-card__media img {
    width: 100%; height: 100%;
    object-fit: cover;
    transform: scale(1.02);
    transition: transform 0.8s var(--ease-soft), filter 0.5s var(--ease-soft);
    filter: grayscale(0.1) brightness(0.92);
  }
  .pf-card:hover .pf-card__media img { transform: scale(1.06); filter: none; }
  .pf-card__tag {
    position: absolute;
    top: 12px; left: 12px;
    padding: 6px 11px;
    background: var(--red); color: #fff;
    font-family: var(--f-mono);
    font-size: 9.5px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
  }
  .pf-card__body { padding: 14px 2px 0; }
  .pf-card__body h3 {
    font-family: var(--f-display);
    font-weight: 500;
    font-size: 19px;
    letter-spacing: -0.02em;
    line-height: 1.2;
    transition: color 0.3s var(--ease-soft);
  }
  .pf-card:hover .pf-card__body h3 { color: var(--red); }
  .pf-card__body span {
    display: block;
    margin-top: 5px;
    font-family: var(--f-mono);
    font-size: 10.5px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--paper-dim);
  }

  /* Responsive */
  @media (max-width: 980px) {
    .pf-hero__row { grid-template-columns: 1fr; gap: 24px; padding-bottom: 32px; }
    .pf-work { padding: 36px 0 16px; }
    .pf-grid { grid-template-columns: 1fr 1fr; gap: 22px 14px; }
  }
  @media (max-width: 540px) {
    .pf-grid { grid-template-columns: 1fr; }
  }
</style>
    </x-slot>

    {{-- 01 HERO --}}
    <section class="pf-hero" data-screen-label="01 Header">
        <div class="pf-hero__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Portfolio</span></div>
        <div class="pf-hero__row">
            @php
                $window = $stats['yearMax']
                    ? ($stats['yearMin'] === $stats['yearMax'] ? (string) $stats['yearMax'] : $stats['yearMin'].' — '.$stats['yearMax'])
                    : null;
            @endphp
            <h1 data-split>Selected <em>work{{ $window ? ',' : '.' }}</em>@if ($window)<br><em>{{ $window }}.</em>@endif</h1>
            <p class="pf-hero__lead reveal">Real client work — every case opens with the full film. Tap any card.</p>
        </div>
    </section>

    {{-- 02 WORK GRID --}}
    <section class="pf-work" data-screen-label="03 Work">
        <div class="wrap">
            <div class="pf-grid">
                @if ($featured)
                    @php $coverUrl = $featured->getFirstMediaUrl('cover') ?: ($featured->cover_url ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'); @endphp
                    <a class="pf-card reveal" href="{{ url('/portfolio/'.$featured->slug) }}" data-cursor="VIEW">
                        <div class="pf-card__media">
                            <img src="{{ $coverUrl }}" alt="{{ $featured->title }}" decoding="async">
                            <span class="pf-card__tag">Editor's pick</span>
                        </div>
                        <div class="pf-card__body">
                            <h3>{{ $featured->title }}</h3>
                            <span>{{ $featured->service?->title ?? 'Film' }}{{ $featured->client ? ' · '.$featured->client : '' }}{{ $featured->year ? ' · '.$featured->year : '' }}</span>
                        </div>
                    </a>
                @endif
                @foreach ($itemsByYear as $year => $items)
                    <div class="pf-yearline">
                        <span>{{ $year }} archive</span>
                        <em>{{ $items->count() }} {{ Str::plural('production', $items->count()) }} · delivered</em>
                    </div>
                    @foreach ($items as $portfolioItem)
                        @php $tileImg = $portfolioItem->getFirstMediaUrl('cover') ?: ($portfolioItem->cover_url ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'); @endphp
                        <a class="pf-card reveal" href="{{ url('/portfolio/'.$portfolioItem->slug) }}" data-cursor="VIEW">
                            <div class="pf-card__media">
                                <img src="{{ $tileImg }}" alt="{{ $portfolioItem->title }}" loading="lazy" decoding="async">
                            </div>
                            <div class="pf-card__body">
                                <h3>{{ $portfolioItem->title }}</h3>
                                <span>{{ $portfolioItem->service?->title ?? 'Film' }}{{ $portfolioItem->client ? ' · '.$portfolioItem->client : '' }} · {{ $portfolioItem->year }}</span>
                            </div>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>

    {{-- 03 CTA STRIP --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Want your work<br><em>here next year?</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Bring the brief — treatment, timeline, and a number within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>
