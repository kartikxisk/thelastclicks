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
  .pf-hero__stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; padding: 36px 0 0; }
  .pf-hero__stats dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 12px; }
  .pf-hero__stats dd { font-family: var(--f-display); font-weight: 700; font-size: clamp(22px, 2.6vw, 32px); letter-spacing: -0.03em; line-height: 1; font-variant-numeric: tabular-nums; }
  .pf-hero__stats dd em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }

  /* Filter chips */
  .pf-filter {
    padding: 24px 0;
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
  .pf-filter__inner {
    max-width: var(--maxw);
    margin-inline: auto;
    padding: 0 var(--pad-x);
    display: flex; flex-wrap: wrap;
    gap: 8px;
    align-items: center;
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

  /* Responsive */
  @media (max-width: 980px) {
    .pf-hero__row { grid-template-columns: 1fr; gap: 24px; padding-bottom: 32px; }
    .pf-hero__stats { grid-template-columns: 1fr 1fr; gap: 22px; }
    .pf-filter { top: 60px; padding: 20px 0; }
    .pf-work { padding: 36px 0 16px; }
    .pf-grid { grid-template-columns: 1fr 1fr; gap: 22px 14px; }
    .pf-numbers__grid { grid-template-columns: 1fr 1fr; }
    .pf-num { border-right: 0; border-bottom: 1px solid var(--line); padding: 28px 20px; }
    .pf-num:nth-child(2n) { border-right: 0; }
    .pf-num:nth-child(odd) { border-right: 1px solid var(--line); }
  }
  @media (max-width: 540px) {
    .pf-hero__stats { grid-template-columns: 1fr 1fr; }
    .pf-grid { grid-template-columns: 1fr; }
    .pf-numbers__grid { grid-template-columns: 1fr; }
    .pf-num { border-right: 0 !important; }
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
        <dl class="pf-hero__stats">
            <div class="reveal"><dt>Films featured</dt><dd>{{ str_pad((string) $stats['films'], 2, '0', STR_PAD_LEFT) }}<em>·</em></dd></div>
            <div class="reveal" data-delay="1"><dt>Client brands</dt><dd>{{ str_pad((string) $stats['clients'], 2, '0', STR_PAD_LEFT) }}<em>·</em></dd></div>
            <div class="reveal" data-delay="2"><dt>Industries</dt><dd>{{ str_pad((string) $stats['industries'], 2, '0', STR_PAD_LEFT) }}<em>·</em></dd></div>
            <div class="reveal" data-delay="3"><dt>Newest work</dt><dd>{{ $stats['yearMax'] ?? '—' }}</dd></div>
        </dl>
    </section>

    {{-- 02 FILTER --}}
    @php
        $allTiles = collect($itemsByYear)->flatten(1);
        if ($featured) {
            $allTiles = $allTiles->prepend($featured);
        }
        $indGroups = $allTiles->groupBy(fn ($i) => $i->industry?->slug ?? 'other');
        $indLabels = $allTiles->mapWithKeys(fn ($i) => [($i->industry?->slug ?? 'other') => ($i->industry?->title ?? 'Other')]);
    @endphp
    <section class="pf-filter" data-screen-label="02 Filter">
        <div class="pf-filter__inner">
            <span class="pf-filter__label">Filter</span>
            <button class="pf-chip is-on" data-ind="all">All<span class="count">{{ str_pad((string) $allTiles->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
            @foreach ($indGroups as $ind => $items)
                <button class="pf-chip" data-ind="{{ $ind }}">{{ $indLabels[$ind] }}<span class="count">{{ str_pad((string) $items->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
            @endforeach
        </div>
    </section>

    {{-- 03 WORK GRID --}}
    <section class="pf-work" data-screen-label="03 Work">
        <div class="wrap">
            <div class="pf-grid">
                @if ($featured)
                    @php $coverUrl = $featured->getFirstMediaUrl('cover') ?: ($featured->cover_url ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&q=85'); @endphp
                    <a class="pf-tile pf-card reveal" href="{{ url('/portfolio/'.$featured->slug) }}"
                       data-ind="{{ $featured->industry?->slug ?? 'other' }}"
                       data-cursor="VIEW">
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
                        <a class="pf-tile pf-card reveal" href="{{ url('/portfolio/'.$portfolioItem->slug) }}"
                           data-ind="{{ $portfolioItem->industry?->slug ?? 'other' }}"
                           data-cursor="VIEW">
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

    {{-- 07 BY THE NUMBERS --}}
    <section class="pf-numbers" data-screen-label="07 Numbers">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Behind the work</span>
                    <h2 class="section__title" data-split>The work, <em>in numbers.</em></h2>
                </div>
                <p class="section__lead reveal">Every figure below comes from the cases on this page — nothing padded.</p>
            </div>
            <div class="pf-numbers__grid">
                <div class="pf-num reveal">
                    <div class="pf-num__n"><span data-count="{{ $stats['films'] }}" data-decimals="0">0</span><em>·</em></div>
                    <div class="pf-num__l">Films on this page</div>
                </div>
                <div class="pf-num reveal" data-delay="1">
                    <div class="pf-num__n"><span data-count="{{ $stats['clients'] }}" data-decimals="0">0</span><em>·</em></div>
                    <div class="pf-num__l">Client brands featured</div>
                </div>
                <div class="pf-num reveal" data-delay="2">
                    <div class="pf-num__n"><span data-count="{{ $stats['industries'] }}" data-decimals="0">0</span><em>·</em></div>
                    <div class="pf-num__l">Industries covered</div>
                </div>
                <div class="pf-num reveal" data-delay="3">
                    <div class="pf-num__n">{{ $stats['yearMax'] ?? '—' }}</div>
                    <div class="pf-num__l">Newest delivery</div>
                </div>
            </div>
        </div>
    </section>

    {{-- 09 CTA STRIP --}}
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

    <script>
    // Filter chips behavior — animated show/hide of tiles
    (function(){
      const chips = document.querySelectorAll('.pf-chip');
      const tiles = document.querySelectorAll('.pf-tile[data-ind]');

      function showTile(t, match) {
        t.style.transition = 'opacity 0.4s, transform 0.4s';
        if (match) {
          t.classList.remove('is-hidden');
          requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'scale(1)'; });
        } else {
          t.style.opacity = '0'; t.style.transform = 'scale(0.97)';
          setTimeout(() => t.classList.add('is-hidden'), 380);
        }
      }

      function filterBy(attr, value) {
        tiles.forEach(t => showTile(t, value === 'all' || t.dataset[attr] === value));
      }

      chips.forEach(c => c.addEventListener('click', () => {
        chips.forEach(o => o.classList.remove('is-on'));
        c.classList.add('is-on');
        filterBy('ind', c.dataset.ind);
      }));

    })();
    </script>

</x-layouts.app>
