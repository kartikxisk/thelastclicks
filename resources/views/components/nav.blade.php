<header class="nav">
  <div class="nav__inner container">
  <a class="nav__brand" href="{{ url('/') }}" data-cursor="HOME">
    <span class="nav__brand-mark">
      <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
        <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="1.5"/>
        <circle cx="16" cy="16" r="6" fill="#e80f03"/>
        <circle cx="22" cy="10" r="1.5" fill="currentColor"/>
      </svg>
    </span>
    <span>TheLastClicks</span>
  </a>
  <nav>
    <ul class="nav__links">
      <li><a href="{{ url('/#services') }}" data-cursor="VIEW"><span class="a">Services</span><span class="b">Services</span></a></li>
      <li><a href="{{ url('/industries') }}" data-cursor="VIEW"><span class="a">Industries</span><span class="b">Industries</span></a></li>
      <li><a href="{{ url('/our-process') }}" data-cursor="VIEW"><span class="a">Our Process</span><span class="b">Our Process</span></a></li>
      <li><a href="{{ url('/portfolio') }}" data-cursor="VIEW"><span class="a">Portfolio</span><span class="b">Portfolio</span></a></li>
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
    <li><a href="{{ url('/#services') }}"><span>Services</span></a></li>
    <li><a href="{{ url('/industries') }}"><span>Industries</span></a></li>
    <li><a href="{{ url('/our-process') }}"><span>Our Process</span></a></li>
    <li><a href="{{ url('/portfolio') }}"><span>Portfolio</span></a></li>
    <li><a href="{{ url('/blog') }}"><span>Blog</span></a></li>
    <li><a href="{{ url('/about') }}"><span>About</span></a></li>
    <li><a href="{{ url('/contact') }}"><span>Contact</span></a></li>
    <li><a href="#quote" data-quote-trigger><span>Get a Quote →</span></a></li>
  </ul>
  <div class="menu__foot">
    <p style="font-family:var(--f-mono);font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)">{{ \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842') }} · {{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}</p>
  </div>
</div>
