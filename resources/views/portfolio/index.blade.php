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
  .pf-hero { padding: 130px var(--pad-x) 0; }
  .pf-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); display: flex; gap: 10px; margin-bottom: 28px; }
  .pf-hero__crumb a { color: var(--paper-dim); }
  .pf-hero__crumb a:hover { color: var(--red); }
  .pf-hero__row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 80px; align-items: end; padding-bottom: 48px; border-bottom: 1px solid var(--line); }
  .pf-hero h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(58px, 10vw, 180px); letter-spacing: -0.05em; line-height: 0.88; }
  .pf-hero h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-hero__lead { font-size: 18px; line-height: 1.55; color: var(--paper-dim); max-width: 38ch; }
  .pf-hero__stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; padding: 36px 0 0; }
  .pf-hero__stats dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 12px; }
  .pf-hero__stats dd { font-family: var(--f-display); font-weight: 700; font-size: clamp(28px, 3.6vw, 44px); letter-spacing: -0.03em; line-height: 1; font-variant-numeric: tabular-nums; }
  .pf-hero__stats dd em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }

  /* Filter chips */
  .pf-filter {
    padding: 24px var(--pad-x);
    display: flex; flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    background: var(--ink);
    position: sticky;
    top: 70px;
    z-index: 10;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    background: rgba(10,10,10,0.86);
    margin-top: 0;
  }
  .pf-filter__label {
    font-family: var(--f-mono); font-size: 10.5px;
    letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--paper-dim); margin-right: 6px;
  }
  .pf-chip {
    padding: 9px 16px;
    border: 1px solid var(--line);
    border-radius: 100px;
    background: transparent;
    font-family: var(--f-mono); font-size: 11px;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--paper-dim);
    cursor: pointer;
    transition: border-color 0.3s var(--ease-soft), color 0.3s var(--ease-soft), background 0.3s var(--ease-soft);
  }
  .pf-chip:hover { border-color: var(--paper-dim); color: var(--paper); }
  .pf-chip.is-on { background: var(--red); border-color: var(--red); color: #fff; }
  .pf-chip .count { margin-left: 6px; padding-left: 8px; border-left: 1px solid currentColor; opacity: 0.7; }

  /* Featured spotlight */
  .pf-feat {
    padding: 80px 0;
  }
  .pf-feat__card {
    position: relative;
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 0;
    background: var(--ink-2);
    border: 1px solid var(--line);
    overflow: hidden;
    transition: border-color 0.4s var(--ease-soft);
  }
  .pf-feat__card:hover { border-color: rgba(232,15,3,0.4); }
  .pf-feat__media {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
  }
  .pf-feat__media img {
    width: 100%; height: 100%; object-fit: cover;
    transform: scale(1.04);
    transition: transform 1s var(--ease-soft);
    filter: grayscale(0.15) brightness(0.9);
  }
  .pf-feat__card:hover .pf-feat__media img { transform: scale(1.08); filter: none; }
  .pf-feat__media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, transparent 50%, rgba(10,10,10,0.4) 100%);
  }
  .pf-feat__tag {
    position: absolute; top: 24px; left: 24px;
    padding: 8px 14px;
    background: var(--red); color: #fff;
    font-family: var(--f-mono); font-size: 10.5px;
    letter-spacing: 0.18em; text-transform: uppercase;
    z-index: 2;
  }
  .pf-feat__body {
    padding: 56px 48px;
    display: grid;
    gap: 22px;
    align-content: center;
  }
  .pf-feat__eyebrow {
    font-family: var(--f-mono); font-size: 11px;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--red);
    display: inline-flex; align-items: center; gap: 10px;
  }
  .pf-feat__eyebrow::before { content: ''; width: 24px; height: 1px; background: var(--red); }
  .pf-feat__title {
    font-family: var(--f-display); font-weight: 600;
    font-size: clamp(36px, 4.5vw, 64px);
    letter-spacing: -0.04em; line-height: 0.96;
    text-wrap: balance;
  }
  .pf-feat__title em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-feat__desc { color: var(--paper-dim); font-size: 16px; line-height: 1.6; text-wrap: pretty; max-width: 44ch; }
  .pf-feat__meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-top: 4px;
  }
  .pf-feat__meta span {
    padding: 5px 11px;
    border: 1px solid var(--line);
    border-radius: 100px;
    font-family: var(--f-mono); font-size: 10px;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--paper-dim);
  }
  .pf-feat__cta {
    display: inline-flex; align-items: center; gap: 14px;
    padding: 14px 24px;
    border: 1px solid var(--paper-dim);
    border-radius: 100px;
    font-size: 14px;
    transition: background 0.4s var(--ease-soft), color 0.4s var(--ease-soft), border-color 0.4s var(--ease-soft);
    margin-top: 8px;
    justify-self: start;
  }
  .pf-feat__cta:hover { background: var(--paper); color: var(--ink); border-color: var(--paper); }
  .pf-feat__cta .arr svg { width: 14px; height: 14px; transition: transform 0.4s var(--ease-soft); }
  .pf-feat__cta:hover .arr svg { transform: translateX(4px); }

  /* Year header */
  .pf-year {
    padding: 60px var(--pad-x) 24px;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 24px;
  }
  .pf-year__num {
    font-family: var(--f-display); font-weight: 800;
    font-size: clamp(64px, 8vw, 124px);
    letter-spacing: -0.04em; line-height: 1;
    font-variant-numeric: tabular-nums;
    text-wrap: nowrap;
  }
  .pf-year__num em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); margin-left: 12px; font-size: 0.55em; vertical-align: 0.45em; }
  .pf-year__meta {
    font-family: var(--f-mono); font-size: 11px;
    letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--paper-dim);
    text-align: right;
  }
  .pf-year__meta strong { color: var(--paper); font-family: var(--f-display); font-weight: 600; font-size: 18px; letter-spacing: -0.01em; text-transform: none; display: block; margin-bottom: 4px; }

  /* Bento grid */
  .pf-bento {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: clamp(220px, 24vw, 340px);
    gap: 16px;
    padding: 0 var(--pad-x);
  }
  .pf-tile {
    position: relative;
    overflow: hidden;
    background: var(--ink-2);
    border: 1px solid transparent;
    height: 100%;
    transition: border-color 0.4s var(--ease-soft);
    isolation: isolate;
    cursor: pointer;
  }
  .pf-tile:hover { border-color: rgba(232,15,3,0.35); }
  .pf-tile img {
    width: 100%; height: 100%; object-fit: cover;
    transform: scale(1.04);
    transition: transform 1s var(--ease-soft), filter 0.5s var(--ease-soft);
    filter: grayscale(0.15) brightness(0.85);
  }
  .pf-tile:hover img { transform: scale(1.1); filter: grayscale(0) brightness(1); }
  .pf-tile::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10,10,10,0.92) 0%, rgba(10,10,10,0.4) 35%, transparent 60%);
    z-index: 1;
    transition: opacity 0.4s var(--ease-soft);
  }
  .pf-tile__tag {
    position: absolute; top: 16px; left: 16px;
    padding: 6px 11px;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 100px;
    font-family: var(--f-mono); font-size: 9.5px;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: #fff;
    z-index: 2;
  }
  .pf-tile__year {
    position: absolute; top: 16px; right: 16px;
    font-family: var(--f-mono); font-size: 10px;
    letter-spacing: 0.18em;
    color: rgba(255,255,255,0.7);
    z-index: 2;
  }
  .pf-tile__body {
    position: absolute;
    inset: auto 0 0 0;
    padding: 22px 24px;
    z-index: 2;
    color: #fff;
    transform: translateY(8px);
    opacity: 0;
    transition: transform 0.5s var(--ease-spring), opacity 0.4s var(--ease-soft);
  }
  .pf-tile:hover .pf-tile__body { transform: translateY(0); opacity: 1; }
  .pf-tile__body h3 {
    font-family: var(--f-display); font-weight: 500;
    font-size: clamp(18px, 2vw, 26px);
    letter-spacing: -0.025em; line-height: 1.1;
    text-wrap: balance;
  }
  .pf-tile__body h3 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-tile__body p {
    margin-top: 8px;
    font-family: var(--f-mono); font-size: 10px;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: rgba(255,255,255,0.7);
  }
  .pf-tile__visible {
    position: absolute;
    inset: auto 0 0 0;
    padding: 22px 24px;
    z-index: 2;
    color: #fff;
    transition: opacity 0.4s var(--ease-soft), transform 0.4s var(--ease-soft);
  }
  .pf-tile:hover .pf-tile__visible { opacity: 0; transform: translateY(-8px); }
  .pf-tile__visible h3 {
    font-family: var(--f-display); font-weight: 500;
    font-size: clamp(18px, 2vw, 26px);
    letter-spacing: -0.025em; line-height: 1.1;
    text-wrap: balance;
  }
  .pf-tile__visible h3 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-tile__visible span {
    margin-top: 6px;
    display: block;
    font-family: var(--f-mono); font-size: 10px;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: rgba(255,255,255,0.7);
  }
  .pf-tile__arrow {
    position: absolute;
    top: 16px; right: 16px;
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--red);
    display: grid; place-items: center;
    z-index: 3;
    opacity: 0;
    transform: scale(0.85);
    transition: opacity 0.4s var(--ease-soft), transform 0.4s var(--ease-spring);
  }
  .pf-tile:hover .pf-tile__arrow { opacity: 1; transform: scale(1); }
  .pf-tile:hover .pf-tile__year { opacity: 0; }
  .pf-tile__arrow svg { width: 14px; height: 14px; color: #fff; }

  /* Size variants */
  .pf-7 { grid-column: span 7; }
  .pf-5 { grid-column: span 5; }
  .pf-6 { grid-column: span 6; }
  .pf-4 { grid-column: span 4; }
  .pf-8 { grid-column: span 8; }
  .pf-12 { grid-column: span 12; grid-row: span 2; }
  .pf-tall { grid-row: span 2; }

  /* Hidden via filter */
  .pf-tile.is-hidden { display: none; }

  /* By the numbers */
  .pf-numbers {
    padding: 100px 0;
    border-top: 1px solid var(--line);
    margin-top: 80px;
  }
  .pf-numbers__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
  }
  .pf-num {
    padding: 44px 32px;
    border-right: 1px solid var(--line);
    text-align: left;
    transition: background 0.4s var(--ease-soft);
    position: relative;
    overflow: hidden;
  }
  .pf-num:last-child { border-right: 0; }
  .pf-num::before {
    content: '';
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 2px;
    background: var(--red);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.6s var(--ease-soft);
  }
  .pf-num:hover::before { transform: scaleX(1); }
  .pf-num__n {
    font-family: var(--f-display); font-weight: 700;
    font-size: clamp(48px, 7vw, 96px);
    letter-spacing: -0.04em; line-height: 0.9;
    font-variant-numeric: tabular-nums;
    margin-bottom: 16px;
  }
  .pf-num__n em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-num__l {
    font-family: var(--f-mono); font-size: 11px;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--paper-dim);
  }

  /* Disciplines breakdown */
  .pf-disc {
    padding: 100px 0;
    border-top: 1px solid var(--line);
  }
  .pf-disc__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border-top: 1px solid var(--line);
  }
  .pf-disc__cell {
    padding: 36px 32px;
    border-right: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    transition: background 0.4s var(--ease-soft);
  }
  .pf-disc__cell:nth-child(3n) { border-right: 0; }
  .pf-disc__cell:hover { background: rgba(232,15,3,0.03); }
  .pf-disc__h {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: 16px; margin-bottom: 22px;
  }
  .pf-disc__t {
    font-family: var(--f-display); font-weight: 500;
    font-size: 22px; letter-spacing: -0.02em;
  }
  .pf-disc__c {
    font-family: var(--f-mono); font-size: 11px;
    letter-spacing: 0.18em; color: var(--paper-dim);
    font-variant-numeric: tabular-nums;
  }
  .pf-disc__bar {
    height: 4px;
    background: var(--line);
    margin-bottom: 14px;
    position: relative;
    overflow: hidden;
  }
  .pf-disc__bar-fill {
    position: absolute;
    inset: 0;
    background: var(--red);
    transform-origin: left;
    transform: scaleX(0);
    transition: transform 1.2s var(--ease-soft);
  }
  .pf-disc__cell.is-in .pf-disc__bar-fill { transform: scaleX(var(--p, 0.5)); }
  .pf-disc__d {
    font-size: 13.5px;
    color: var(--paper-dim);
    line-height: 1.55;
  }

  /* Responsive */
  @media (max-width: 980px) {
    .pf-hero__row { grid-template-columns: 1fr; gap: 24px; padding-bottom: 32px; }
    .pf-hero__stats { grid-template-columns: 1fr 1fr; gap: 22px; }
    .pf-filter { top: 60px; padding: 20px var(--pad-x); }
    .pf-feat { padding: 48px 0; }
    .pf-feat__card { grid-template-columns: 1fr; }
    .pf-feat__body { padding: 32px 24px; }
    .pf-year { padding: 40px var(--pad-x) 18px; flex-wrap: wrap; }
    .pf-year__num { font-size: clamp(52px, 14vw, 80px); }
    .pf-year__meta { text-align: left; }
    .pf-bento { grid-auto-rows: auto; }
    .pf-bento > .pf-tile { grid-column: span 12 !important; grid-row: auto !important; aspect-ratio: 4/3; }
    .pf-tile__body, .pf-tile__visible { opacity: 1 !important; transform: none !important; }
    .pf-tile__body { display: none; }
    .pf-numbers__grid { grid-template-columns: 1fr 1fr; }
    .pf-num { border-right: 0; border-bottom: 1px solid var(--line); padding: 28px 20px; }
    .pf-num:nth-child(2n) { border-right: 0; }
    .pf-num:nth-child(odd) { border-right: 1px solid var(--line); }
    .pf-disc__grid { grid-template-columns: 1fr; }
    .pf-disc__cell { border-right: 0; padding: 28px 20px; }
  }
  @media (max-width: 540px) {
    .pf-hero__stats { grid-template-columns: 1fr 1fr; }
    .pf-numbers__grid { grid-template-columns: 1fr; }
    .pf-num { border-right: 0 !important; }
  }
</style>
    </x-slot>

    {{-- 01 HERO --}}
    <section class="pf-hero" data-screen-label="01 Header">
        <div class="pf-hero__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Portfolio</span></div>
        <div class="pf-hero__row">
            <h1 data-split>Selected <em>work,</em><br>2024 — <em>2026.</em></h1>
            <p class="pf-hero__lead reveal">Eight years and 547 productions in. Here's the small set we share publicly — case studies, brand films, weddings and editorial. Click any tile for the full breakdown.</p>
        </div>
        <dl class="pf-hero__stats">
            <div class="reveal"><dt>Projects shipped</dt><dd>547<em>+</em></dd></div>
            <div class="reveal" data-delay="1"><dt>Featured here</dt><dd>{{ $itemsByYear->flatten()->count() + ($featured ? 1 : 0) }}<em>·</em></dd></div>
            <div class="reveal" data-delay="2"><dt>Disciplines</dt><dd>06<em>·</em></dd></div>
            <div class="reveal" data-delay="3"><dt>Window</dt><dd>2024–26</dd></div>
        </dl>
    </section>

    {{-- 02 FILTER --}}
    @php
        $allTiles = collect($itemsByYear)->flatten(1);
        $catGroups = $allTiles->groupBy(fn ($i) => $i->service?->slug ?? 'film');
        $catLabels = $allTiles->mapWithKeys(fn ($i) => [($i->service?->slug ?? 'film') => ($i->service?->title ?? 'Brand films')]);
    @endphp
    <section class="pf-filter" data-screen-label="02 Filter">
        <span class="pf-filter__label">Filter</span>
        <button class="pf-chip is-on" data-cat="all">All<span class="count">{{ str_pad((string) $allTiles->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
        @foreach ($catGroups as $cat => $items)
            <button class="pf-chip" data-cat="{{ $cat }}">{{ $catLabels[$cat] }}<span class="count">{{ str_pad((string) $items->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
        @endforeach
    </section>

    {{-- 03 FEATURED CASE SPOTLIGHT --}}
    @if ($featured)
        <section class="pf-feat" data-screen-label="03 Featured">
            <div class="wrap">
                <a class="pf-feat__card spotlight" href="{{ url('/portfolio/'.$featured->slug) }}" data-cursor="VIEW">
                    <div class="pf-feat__media">
                        <span class="pf-feat__tag">Editor's pick · {{ $featured->year }}</span>
                        @php $coverUrl = $featured->getFirstMediaUrl('cover') ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1800&q=85'; @endphp
                        <img src="{{ $coverUrl }}" alt="{{ $featured->title }}" decoding="async">
                    </div>
                    <div class="pf-feat__body">
                        <span class="pf-feat__eyebrow">Featured case</span>
                        <h2 class="pf-feat__title" data-split>{{ $featured->title }}</h2>
                        @if ($featured->body)
                            <p class="pf-feat__desc">{{ Str::limit(strip_tags($featured->body), 160) }}</p>
                        @endif
                        <div class="pf-feat__meta">
                            @if ($featured->service)
                                <span>{{ $featured->service->title }}</span>
                            @endif
                            @if ($featured->client)
                                <span>{{ $featured->client }}</span>
                            @endif
                            @if ($featured->year)
                                <span>{{ $featured->year }}</span>
                            @endif
                        </div>
                        <span class="pf-feat__cta" data-magnetic>Read the case <span class="arr"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span></span>
                    </div>
                </a>
            </div>
        </section>
    @endif

    {{-- 04-N YEAR-GROUPED PORTFOLIO SECTIONS --}}
    @php $sectionIndex = 4; @endphp
    @foreach ($itemsByYear as $year => $items)
        <section data-screen-label="{{ str_pad($sectionIndex++, 2, '0', STR_PAD_LEFT) }} {{ $year }}">
            <div class="pf-year">
                <div class="pf-year__num">20<em>{{ substr((string) $year, 2) }}</em></div>
                <div class="pf-year__meta">
                    <strong>{{ $year }} archive</strong>{{ $items->count() }} {{ Str::plural('production', $items->count()) }} · delivered
                </div>
            </div>
            <div class="pf-bento">
                @foreach ($items as $portfolioItem)
                    @php
                        $tileImg = $portfolioItem->getFirstMediaUrl('cover') ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85';
                        $tileSizes = ['pf-7', 'pf-5', 'pf-6', 'pf-6', 'pf-4', 'pf-8'];
                        $tileSize = $tileSizes[$loop->index % count($tileSizes)];
                    @endphp
                    <a class="pf-tile {{ $tileSize }}" href="{{ url('/portfolio/'.$portfolioItem->slug) }}"
                       data-cat="{{ $portfolioItem->service?->slug ?? 'film' }}"
                       data-cursor="VIEW">
                        <span class="pf-tile__tag">{{ $portfolioItem->service?->title ?? 'Film' }} · {{ $portfolioItem->year }}</span>
                        <span class="pf-tile__year">{{ str_pad($loop->index + 1, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="pf-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                        <img src="{{ $tileImg }}" alt="{{ $portfolioItem->title }}" loading="lazy" decoding="async">
                        <div class="pf-tile__visible">
                            <h3>{{ $portfolioItem->title }}</h3>
                            <span>{{ $portfolioItem->client }}{{ $portfolioItem->client && $portfolioItem->year ? ' · ' : '' }}{{ $portfolioItem->year }}</span>
                        </div>
                        <div class="pf-tile__body">
                            <h3>{{ $portfolioItem->title }}</h3>
                            @if ($portfolioItem->body)
                                <p>{{ Str::limit(strip_tags($portfolioItem->body), 60) }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- 07 BY THE NUMBERS --}}
    <section class="pf-numbers" data-screen-label="07 Numbers">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Behind the work</span>
                    <h2 class="section__title" data-split>What it <em>actually took.</em></h2>
                </div>
                <p class="section__lead reveal">The portfolio is the tip. Here's the iceberg underneath.</p>
            </div>
            <div class="pf-numbers__grid">
                <div class="pf-num reveal">
                    <div class="pf-num__n"><span data-count="547" data-decimals="0">0</span><em>+</em></div>
                    <div class="pf-num__l">Productions shipped</div>
                </div>
                <div class="pf-num reveal" data-delay="1">
                    <div class="pf-num__n"><span data-count="186" data-decimals="0">0</span><em>+</em></div>
                    <div class="pf-num__l">Events per year</div>
                </div>
                <div class="pf-num reveal" data-delay="2">
                    <div class="pf-num__n"><span data-count="26" data-decimals="0">0</span><em>·</em></div>
                    <div class="pf-num__l">Cities covered</div>
                </div>
                <div class="pf-num reveal" data-delay="3">
                    <div class="pf-num__n"><span data-count="98.4" data-decimals="1">0</span><em>%</em></div>
                    <div class="pf-num__l">On-time delivery</div>
                </div>
            </div>
        </div>
    </section>

    {{-- 08 DISCIPLINES BREAKDOWN --}}
    <section class="pf-disc" data-screen-label="08 Disciplines">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>How we split our time</span>
                    <h2 class="section__title" data-split>The <em>mix</em> of work.</h2>
                </div>
                <p class="section__lead reveal">Roughly what we shoot, year over year — never picked, always emergent from briefs.</p>
            </div>
            <div class="pf-disc__grid">
                @forelse ($services as $svc)
                    @php $share = (int) ($svc->share ?? 0); @endphp
                    <div class="pf-disc__cell reveal" data-delay="{{ $loop->index % 3 }}"@if ($share) style="--p: {{ rtrim(rtrim(number_format($share / 100, 2), '0'), '.') }}"@endif>
                        <div class="pf-disc__h">
                            <div class="pf-disc__t">{{ $svc->title }}</div>
                            @if ($share)<div class="pf-disc__c">{{ sprintf('%02d', $share) }}%</div>@endif
                        </div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        @if ($svc->hero_copy)
                            <p class="pf-disc__d">{{ $svc->hero_copy }}</p>
                        @endif
                    </div>
                @empty
                    {{-- Fallback static disciplines if no services seeded --}}
                    <div class="pf-disc__cell reveal" style="--p: 0.32">
                        <div class="pf-disc__h"><div class="pf-disc__t">Brand films</div><div class="pf-disc__c">32%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Treatment-led brand storytelling. Commercials, brand films, launches, anthems.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="1" style="--p: 0.24">
                        <div class="pf-disc__h"><div class="pf-disc__t">Weddings</div><div class="pf-disc__c">24%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Where we started. Still our most demanded discipline — destination + intimate.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="2" style="--p: 0.18">
                        <div class="pf-disc__h"><div class="pf-disc__t">Photography</div><div class="pf-disc__c">18%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Editorial, brand, lifestyle, lookbook. Stills as their own discipline — never an afterthought.</p>
                    </div>
                    <div class="pf-disc__cell reveal" style="--p: 0.14">
                        <div class="pf-disc__h"><div class="pf-disc__t">Corporate</div><div class="pf-disc__c">14%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Conferences, keynotes, internal events — multi-cam coverage with same-day delivery.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="1" style="--p: 0.08">
                        <div class="pf-disc__h"><div class="pf-disc__t">Automotive</div><div class="pf-disc__c">08%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Vehicle reveals, motion-control rigs, high-speed Phantom work for premium auto brands.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="2" style="--p: 0.04">
                        <div class="pf-disc__h"><div class="pf-disc__t">Lifestyle &amp; F&amp;B</div><div class="pf-disc__c">04%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Premium beverages, hospitality, lifestyle campaigns. Practical light, tabletop, set work.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- 09 CTA STRIP --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Want your work<br><em>here next year?</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Bring the brief. We'll come back with a treatment, a timeline, and a number — within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

    <script>
    // Filter chips behavior — animated show/hide of tiles
    (function(){
      const chips = document.querySelectorAll('.pf-chip');
      const tiles = document.querySelectorAll('.pf-tile[data-cat]');
      chips.forEach(c => c.addEventListener('click', () => {
        chips.forEach(o => o.classList.remove('is-on'));
        c.classList.add('is-on');
        const cat = c.dataset.cat;
        tiles.forEach(t => {
          const match = cat === 'all' || t.dataset.cat === cat;
          t.style.transition = 'opacity 0.4s, transform 0.4s';
          if (match) {
            t.classList.remove('is-hidden');
            requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'scale(1)'; });
          } else {
            t.style.opacity = '0'; t.style.transform = 'scale(0.97)';
            setTimeout(() => t.classList.add('is-hidden'), 380);
          }
        });
      }));

      // Animate discipline bars in via IntersectionObserver
      const bars = document.querySelectorAll('.pf-disc__cell');
      const bIO = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); bIO.unobserve(e.target); } });
      }, { threshold: 0.3 });
      bars.forEach(b => bIO.observe(b));
    })();
    </script>

</x-layouts.app>
