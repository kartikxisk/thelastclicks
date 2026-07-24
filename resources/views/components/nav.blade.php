@php
    $navServices = \App\Models\Service::orderBy('order')->get(['title', 'slug']);
    $navIndustries = \App\Models\Industry::orderBy('order')->get(['title', 'slug']);
@endphp
<header class="nav">
  <div class="nav__inner container">
  <a class="nav__brand" href="{{ url('/') }}" data-cursor="HOME" aria-label="The Last Clicks — home">
    {{-- No width/height: an admin-uploaded logo has its own ratio. CSS pins the height, so there is no vertical CLS.
         Nothing uploaded → nothing rendered (the link keeps its aria-label). --}}
    @if ($navBrandLogo = \App\Models\SiteSetting::brandLogoUrl())
      <img class="nav__brand-img" src="{{ $navBrandLogo }}" alt="The Last Clicks">
    @endif
  </a>
  <nav>
    <ul class="nav__links">
      <li class="nav__has-drop">
        <a href="{{ url('/#services') }}" data-cursor="VIEW"><span class="a">Services</span><span class="b">Services</span></a>
        <div class="nav__drop">
          <div class="nav__drop-inner">
            @foreach ($navServices as $s)
              <a href="{{ url('/services/'.$s->slug) }}" class="nav__drop-link">{{ $s->title }}<span class="nav__drop-arr">↗</span></a>
            @endforeach
          </div>
        </div>
      </li>
      <li class="nav__has-drop">
        <a href="{{ url('/industries') }}" data-cursor="VIEW"><span class="a">Industries</span><span class="b">Industries</span></a>
        <div class="nav__drop">
          <div class="nav__drop-inner">
            @foreach ($navIndustries as $i)
              <a href="{{ url('/industries#ind-'.$i->slug) }}" class="nav__drop-link">{{ $i->title }}<span class="nav__drop-arr">↗</span></a>
            @endforeach
          </div>
        </div>
      </li>
      <li><a href="{{ url('/our-works') }}" data-cursor="VIEW"><span class="a">Our Work</span><span class="b">Our Work</span></a></li>
      <li><a href="{{ url('/blog') }}" data-cursor="VIEW"><span class="a">Blog</span><span class="b">Blog</span></a></li>
      <li><a href="{{ url('/about') }}" data-cursor="VIEW"><span class="a">About</span><span class="b">About</span></a></li>
      <li><a href="{{ url('/contact') }}" data-cursor="VIEW"><span class="a">Contact</span><span class="b">Contact</span></a></li>
    </ul>
  </nav>
  <a class="nav__cta" href="#quote" data-quote-trigger data-magnetic data-cursor="LET'S TALK">
    <span class="dot"></span>
    <span>Get a Quote</span>
  </a>
  <button class="nav__burger" aria-label="Menu"><span></span><span></span></button>
  </div>
</header>
<div class="menu">
  <ul class="menu__list">
    <li><a href="{{ url('/#services') }}"><span>Services</span></a>
      <div class="menu__sub">
        @foreach ($navServices as $s)
          <a href="{{ url('/services/'.$s->slug) }}">{{ $s->title }}</a>
        @endforeach
      </div>
    </li>
    <li><a href="{{ url('/industries') }}"><span>Industries</span></a>
      <div class="menu__sub">
        @foreach ($navIndustries as $i)
          <a href="{{ url('/industries#ind-'.$i->slug) }}">{{ $i->title }}</a>
        @endforeach
      </div>
    </li>
    <li><a href="{{ url('/our-works') }}"><span>Our Work</span></a></li>
    <li><a href="{{ url('/blog') }}"><span>Blog</span></a></li>
    <li><a href="{{ url('/about') }}"><span>About</span></a></li>
    <li><a href="{{ url('/contact') }}"><span>Contact</span></a></li>
    <li><a href="#quote" data-quote-trigger><span>Get a Quote →</span></a></li>
  </ul>
  <div class="menu__foot">
    <p style="font-family:var(--f-mono);font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)">{{ \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842') }} · {{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}</p>
  </div>
</div>
