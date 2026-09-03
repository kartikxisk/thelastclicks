@props(['title' => null, 'subtitle' => null])
@php
    // The hero background is entirely admin-managed. With no active slide and
    // no source chosen, the whole background layer is omitted — no bundled
    // reel, no placeholder. Same rule the chrome <meta> values follow: absent
    // means render nothing, never a fallback asset, so an empty admin reads as
    // empty rather than as someone else's footage the editor can't find.
    //
    // Two sources, picked in Site Settings → Homepage hero:
    //
    //   slides — HeroSlide uploads, the original behaviour and the default.
    //   work   — the studio's own featured work, read live from the portfolio.
    //
    // The second is not a silent fallback: it renders only once an editor
    // picks it, which is what keeps the no-fallback rule intact. It also
    // references the covers and previews already on S3 rather than copying
    // files the way HeroSlidesSeeder does, so featuring a new project in the
    // admin is the whole workflow — nothing to re-upload, nothing to re-run.
    //
    // Both sources normalise to the same shape (video, image, label) so the
    // loop below stays one thing rather than two branches.
    $frames = \App\Models\SiteSetting::heroSource() === 'work'
        ? \App\Models\Work::published()
            ->where('is_featured', true)
            ->with(['media', 'mediaItems.media'])
            ->orderBy('order')
            ->orderByDesc('id')
            ->take(\App\Models\Work::HERO_FRAMES)
            ->get()
            ->map(fn ($work) => [
                'video' => $work->previewVideoUrl(),
                'image' => $work->coverUrl(),
                'label' => $work->title,
            ])
            // A work with neither a preview nor a cover has nothing to show.
            ->filter(fn (array $f) => filled($f['video']) || filled($f['image']))
            ->values()
        // with('media'): assetUrl()/posterUrl()/previewUrl() all go through
        // getFirstMediaUrl(), so an unloaded relation costs a query per slide.
        : \App\Models\HeroSlide::active()->with('media')->get()
            ->filter(fn ($s) => filled($s->assetUrl()))
            ->map(fn ($s) => [
                'video' => $s->isVideo() ? $s->assetUrl() : null,
                'image' => $s->isVideo() ? $s->posterUrl() : $s->assetUrl(),
                'label' => $s->label,
            ])
            ->values();

    // The frame painted before anything can play is the LCP element, so it gets
    // preloaded rather than waiting to be discovered on the <video> tag.
    $lcpImage = $frames->first()['image'] ?? null;
@endphp
<section class="hero" data-screen-label="01 Hero">
    @if ($lcpImage)
        <link rel="preload" as="image" href="{{ $lcpImage }}" fetchpriority="high">
    @endif

    {{-- No frames → no background layer at all. Rendering the empty container
         would still paint `.hero__bg::after`, laying the scrim gradient over
         nothing but the page background. --}}
    @if ($frames->isNotEmpty())
        <div class="hero__bg hero__bg--reel" @if ($frames->count() > 1) data-hero-slides @endif>
            @foreach ($frames as $frame)
                {{-- Only the first frame is eager: the rest are behind a cross-fade the
                     visitor may never reach, and preloading every film would cost more
                     than the hero is worth. --}}
                <div class="hero__slide{{ $loop->first ? ' is-on' : '' }}" aria-hidden="true">
                    @if ($frame['video'])
                        <video tabindex="-1" src="{{ $frame['video'] }}" muted loop playsinline
                               @if ($loop->first) autoplay preload="metadata" @else preload="none" @endif
                               @if ($frame['image']) poster="{{ $frame['image'] }}" @endif></video>
                    @else
                        <img src="{{ $frame['image'] }}" alt=""
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
