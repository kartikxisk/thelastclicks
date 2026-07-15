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
  .pf-hero h1 { font-family: var(--f-display); font-weight: 700; font-size: clamp(58px, 10vw, 180px); letter-spacing: -0.05em; line-height: 0.88; }
  .pf-hero h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .pf-hero__lead { font-size: 18px; line-height: 1.55; color: var(--paper-dim); max-width: 38ch; }
  .pf-hero__stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; padding: 36px 0 0; }
  .pf-hero__stats dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 12px; }
  .pf-hero__stats dd { font-family: var(--f-display); font-weight: 700; font-size: clamp(28px, 3.6vw, 44px); letter-spacing: -0.03em; line-height: 1; font-variant-numeric: tabular-nums; }
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

  /* Work reel — layout/visuals come from the shared .reel component */
  .pf-work { padding: 72px 0 0; }

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
    .pf-filter { top: 60px; padding: 20px 0; }
    .pf-work { padding: 40px 0 0; }
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
            <p class="pf-hero__lead reveal">Eight years, 547 productions — a public few. Tap any tile for the full case.</p>
        </div>
        <dl class="pf-hero__stats">
            <div class="reveal"><dt>Projects shipped</dt><dd>547<em>+</em></dd></div>
            <div class="reveal" data-delay="1"><dt>Featured here</dt><dd>{{ $itemsByYear->flatten()->count() + ($featured ? 1 : 0) }}<em>·</em></dd></div>
            <div class="reveal" data-delay="2"><dt>Disciplines</dt><dd>03<em>·</em></dd></div>
            <div class="reveal" data-delay="3"><dt>Window</dt><dd>2024–26</dd></div>
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

    {{-- 03 WORK REEL --}}
    <section class="pf-work" data-screen-label="03 Work">
        <div class="wrap">
            <div class="reel">
                @if ($featured)
                    @php $coverUrl = $featured->getFirstMediaUrl('cover') ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1800&q=85'; @endphp
                    <a class="pf-tile reel__frame reel__frame--tagged reveal" href="{{ url('/portfolio/'.$featured->slug) }}"
                       data-ind="{{ $featured->industry?->slug ?? 'other' }}"
                       data-cat="{{ $featured->workCategory?->slug ?? '' }}"
                       data-cursor="VIEW">
                        <img src="{{ $coverUrl }}" alt="{{ $featured->title }}" decoding="async">
                        <span class="reel__tag">Editor's pick · {{ $featured->year }}</span>
                        <span class="reel__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                        <div class="reel__body">
                            <h2 class="reel__title">{{ $featured->title }}</h2>
                            <span class="reel__meta">{{ $featured->workCategory?->title ?? $featured->service?->title ?? 'Film' }}{{ $featured->client ? ' · '.$featured->client : '' }}{{ $featured->year ? ' · '.$featured->year : '' }}</span>
                        </div>
                    </a>
                @endif
                @foreach ($itemsByYear as $year => $items)
                    <div class="reel__year">
                        <span>{{ $year }} archive</span>
                        <em>{{ $items->count() }} {{ Str::plural('production', $items->count()) }} · delivered</em>
                    </div>
                    @foreach ($items as $portfolioItem)
                        @php
                            $tileImg = $portfolioItem->getFirstMediaUrl('cover') ?: 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=85';
                            $isHalf = $loop->index % 3 !== 0 && !($loop->last && $loop->index % 3 === 1);
                        @endphp
                        <a class="pf-tile reel__frame {{ $isHalf ? 'reel__frame--half' : '' }} reveal" href="{{ url('/portfolio/'.$portfolioItem->slug) }}"
                           data-ind="{{ $portfolioItem->industry?->slug ?? 'other' }}"
                           data-cat="{{ $portfolioItem->workCategory?->slug ?? '' }}"
                           data-cursor="VIEW">
                            <img src="{{ $tileImg }}" alt="{{ $portfolioItem->title }}" loading="lazy" decoding="async">
                            <span class="reel__num">{{ str_pad($loop->index + 1, 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="reel__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                            <div class="reel__body">
                                <h3 class="reel__title">{{ $portfolioItem->title }}</h3>
                                <span class="reel__meta">{{ $portfolioItem->workCategory?->title ?? $portfolioItem->service?->title ?? 'Film' }}{{ $portfolioItem->client ? ' · '.$portfolioItem->client : '' }} · {{ $portfolioItem->year }}</span>
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
                    <h2 class="section__title" data-split>What it <em>actually took.</em></h2>
                </div>
                <p class="section__lead reveal">The portfolio is the tip — here's the iceberg.</p>
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
                <p class="section__lead reveal">What we shoot, year over year — emergent from briefs, never picked.</p>
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
                        <p class="pf-disc__d">Treatment-led storytelling — commercials, launches, anthems.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="1" style="--p: 0.24">
                        <div class="pf-disc__h"><div class="pf-disc__t">Weddings</div><div class="pf-disc__c">24%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Where we started, still most demanded — destination and intimate.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="2" style="--p: 0.18">
                        <div class="pf-disc__h"><div class="pf-disc__t">Photography</div><div class="pf-disc__c">18%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Editorial, brand, lifestyle, lookbook — stills as their own discipline.</p>
                    </div>
                    <div class="pf-disc__cell reveal" style="--p: 0.14">
                        <div class="pf-disc__h"><div class="pf-disc__t">Corporate</div><div class="pf-disc__c">14%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Conferences and keynotes — multi-cam, same-day delivery.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="1" style="--p: 0.08">
                        <div class="pf-disc__h"><div class="pf-disc__t">Automotive</div><div class="pf-disc__c">08%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Vehicle reveals — motion-control rigs, high-speed Phantom work.</p>
                    </div>
                    <div class="pf-disc__cell reveal" data-delay="2" style="--p: 0.04">
                        <div class="pf-disc__h"><div class="pf-disc__t">Lifestyle &amp; F&amp;B</div><div class="pf-disc__c">04%</div></div>
                        <div class="pf-disc__bar"><div class="pf-disc__bar-fill"></div></div>
                        <p class="pf-disc__d">Beverages, hospitality, lifestyle — practical light, tabletop, set work.</p>
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

      // Deep link: /portfolio?category=wedding narrows to one work category
      const wanted = new URLSearchParams(location.search).get('category');
      if (wanted) {
        const first = document.querySelector('.pf-tile[data-cat="' + CSS.escape(wanted) + '"]');
        if (first) {
          filterBy('cat', wanted);
          chips.forEach(o => o.classList.toggle('is-on', o.dataset.ind === first.dataset.ind));
        }
      }

      // Animate discipline bars in via IntersectionObserver
      const bars = document.querySelectorAll('.pf-disc__cell');
      const bIO = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); bIO.unobserve(e.target); } });
      }, { threshold: 0.3 });
      bars.forEach(b => bIO.observe(b));
    })();
    </script>

</x-layouts.app>
