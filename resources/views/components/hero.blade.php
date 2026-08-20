@props(['title' => null, 'subtitle' => null])
@php
    // The hero background is entirely admin-managed. With no active slide, the
    // whole background layer is omitted — no bundled reel, no placeholder. Same
    // rule the chrome <meta> values follow: absent means render nothing, never a
    // fallback asset, so an empty admin reads as empty rather than as someone
    // else's footage the editor can't find or replace.
    // with('media'): assetUrl()/posterUrl()/previewUrl() all go through
    // getFirstMediaUrl(), so an unloaded relation costs a query per slide.
    $slides = \App\Models\HeroSlide::active()->with('media')->get()
        ->filter(fn ($s) => filled($s->assetUrl()))
        ->values();

    // The frame painted before anything can play is the LCP element, so it gets
    // preloaded rather than waiting to be discovered on the <video> tag.
    $lcpImage = $slides->first()?->previewUrl();
@endphp
<section class="hero" data-screen-label="01 Hero">
    @if ($lcpImage)
        <link rel="preload" as="image" href="{{ $lcpImage }}" fetchpriority="high">
    @endif

    {{-- No slides → no background layer at all. Rendering the empty container
         would still paint `.hero__bg::after`, laying the scrim gradient over
         nothing but the page background. --}}
    @if ($slides->isNotEmpty())
        <div class="hero__bg hero__bg--reel" @if ($slides->count() > 1) data-hero-slides @endif>
            @foreach ($slides as $slide)
                {{-- Only the first slide is eager: the rest are behind a cross-fade the
                     visitor may never reach, and preloading every film would cost more
                     than the hero is worth. --}}
                <div class="hero__slide{{ $loop->first ? ' is-on' : '' }}" aria-hidden="true">
                    @if ($slide->isVideo())
                        <video tabindex="-1" src="{{ $slide->assetUrl() }}" muted loop playsinline
                               @if ($loop->first) autoplay preload="metadata" @else preload="none" @endif
                               @if ($slide->posterUrl()) poster="{{ $slide->posterUrl() }}" @endif></video>
                    @else
                        <img src="{{ $slide->assetUrl() }}" alt=""
                             @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="hero__center">
      <h1 class="hero__title" data-split>
        @if ($title)
            {{ $title }}
        @else
            Capturing <em>moments,</em><br>
            <span class="stroke">creating</span> memories.
        @endif
      </h1>
      <div class="reveal" data-delay="3" style="display:flex;gap:16px;flex-wrap:wrap">
        <a class="btn btn--red" href="{{ url('/portfolio') }}" data-magnetic data-cursor="VIEW PORTFOLIO">
          View the portfolio <span class="arr"></span>
        </a>
        <a class="btn btn--ghost" href="#quote" data-quote-trigger data-cursor="LET'S TALK">
          Start a project <span class="arr"></span>
        </a>
      </div>
    </div>

    <div class="hero__scroll">
      <span>Scroll</span>
      <span class="line"></span>
    </div>
</section>
