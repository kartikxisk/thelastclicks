@props(['title' => null, 'subtitle' => null])
@php
    // Admin-managed slides win. With none, fall back to the bundled CDN reel so a
    // fresh install (or an empty admin) still renders a hero rather than a black box.
    // Run `php artisan videos:import` after deploying to a fresh environment.
    $slides = \App\Models\HeroSlide::active()->get()
        ->filter(fn ($s) => filled($s->assetUrl()))
        ->values();

    $fallbackReel = \App\Support\MediaUrl::onMediaDisk('videos/hero-reel.mp4');
    $fallbackPoster = \App\Support\MediaUrl::onMediaDisk('videos/posters/hero-reel.jpg');

    // The frame painted before anything can play is the LCP element, so it gets
    // preloaded rather than waiting to be discovered on the <video> tag.
    $lcpImage = $slides->isNotEmpty() ? $slides->first()->previewUrl() : $fallbackPoster;
@endphp
<section class="hero" data-screen-label="01 Hero">
    @if ($lcpImage)
        <link rel="preload" as="image" href="{{ $lcpImage }}" fetchpriority="high">
    @endif

    <div class="hero__bg hero__bg--reel" @if ($slides->count() > 1) data-hero-slides @endif>
        @if ($slides->isEmpty())
            {{-- Decorative background reel: silent, conveys no information → hidden from AT, so no captions needed. --}}
            <video aria-hidden="true" tabindex="-1" src="{{ $fallbackReel }}" autoplay muted loop playsinline
                   preload="metadata" poster="{{ $fallbackPoster }}"></video>
        @else
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
        @endif
    </div>

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
        <a class="btn btn--red" href="{{ url('/services/photography') }}" data-magnetic data-cursor="VIEW REEL">
          View the reel <span class="arr"></span>
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
