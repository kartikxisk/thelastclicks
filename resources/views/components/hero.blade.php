@props(['title' => null, 'subtitle' => null])
<section class="hero" data-screen-label="01 Hero">
    <div class="hero__bg">
      <div class="tile">
        <video src="https://videos.pexels.com/video-files/3209828/3209828-uhd_2560_1440_25fps.mp4" autoplay muted loop playsinline preload="metadata" poster="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&q=85"></video>
      </div>
      <div class="tile">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=85" alt="" decoding="async">
      </div>
      <div class="tile">
        <video src="https://videos.pexels.com/video-files/2103099/2103099-uhd_2560_1440_30fps.mp4" autoplay muted loop playsinline preload="metadata" poster="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=85"></video>
      </div>
      <div class="tile">
        <video src="https://videos.pexels.com/video-files/2022395/2022395-hd_1920_1080_30fps.mp4" autoplay muted loop playsinline preload="metadata" poster="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=85"></video>
      </div>
    </div>

    <div class="hero__top">
      <div class="row">
        <span class="tag"><span class="dot"></span>Available · 2026 slots</span>
      </div>
      <div class="row" style="gap:20px">
        <button class="hero__audio" aria-label="Toggle audio">
          <span class="bars"><span class="bar"></span><span class="bar"></span><span class="bar"></span></span>
        </button>
        <span class="kicker">Reel · 02:14</span>
      </div>
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
        <a class="btn btn--red" href="{{ url('/portfolio') }}" data-magnetic data-cursor="VIEW REEL">
          View the reel <span class="arr"></span>
        </a>
        <a class="btn btn--ghost" href="#quote" data-quote-trigger data-cursor="LET'S TALK">
          Start a project <span class="arr"></span>
        </a>
      </div>
    </div>

    <div class="hero__meta">
      <p class="reveal">{{ $subtitle ?? 'A photography &amp; production studio working at the intersection of cinema, brand, and craft — for teams that demand clarity, consistency, and creative excellence.' }}</p>
      <div class="reveal" data-delay="1">
        <div class="meta-stat"><span data-count="547" data-decimals="0">0</span>+</div>
        <div class="meta-label">Productions delivered</div>
      </div>
      <div class="reveal" data-delay="2">
        <div class="meta-stat"><span data-count="52" data-decimals="0">0</span>+</div>
        <div class="meta-label">Premium brand partners</div>
      </div>
    </div>

    <div class="hero__scroll">
      <span>Scroll</span>
      <span class="line"></span>
    </div>
</section>
