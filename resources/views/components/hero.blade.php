@props(['title' => null, 'subtitle' => null, 'videos' => []])
<section class="hero" data-screen-label="01 Hero">
    <div class="hero__bg">
      @foreach (array_slice($videos, 0, 1) as $v)
      <div class="tile">
        <video src="{{ $v['video_url'] }}" autoplay muted loop playsinline preload="metadata" @if ($v['poster_url']) poster="{{ $v['poster_url'] }}" @endif></video>
      </div>
      @endforeach
      <div class="tile">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=85" alt="" decoding="async">
      </div>
      @foreach (array_slice($videos, 1) as $v)
      <div class="tile">
        <video src="{{ $v['video_url'] }}" autoplay muted loop playsinline preload="metadata" @if ($v['poster_url']) poster="{{ $v['poster_url'] }}" @endif></video>
      </div>
      @endforeach
    </div>

    <div class="hero__top">
      <div class="row"></div>
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
