@props(['title' => null, 'subtitle' => null])
{{-- Background is one pre-composited reel (4 films in a row, ffmpeg-merged) --}}
<section class="hero" data-screen-label="01 Hero">
    <div class="hero__bg hero__bg--reel">
      {{-- Decorative background reel: silent, conveys no information → hidden from AT, so no captions needed. --}}
      <video aria-hidden="true" tabindex="-1" src="{{ asset('videos/hero-reel.mp4') }}" autoplay muted loop playsinline preload="metadata"
             poster="{{ asset('videos/posters/hero-reel.jpg') }}"></video>
    </div>

    <div class="hero__center">
      <h1 class="hero__title" data-split>
        @if ($title)
            {{ $title }}
        @else
            Not a vendor.<br>
            <span class="stroke">A</span> <em>partner.</em>
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
