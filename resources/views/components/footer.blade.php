@php
  $contactPhone = \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842');
  $contactEmail = \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com');
  $whatsappUrl  = \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842');
@endphp
<footer class="foot">
  <div class="container">
    <span class="foot__status"><span class="foot__pulse"></span>Available — booking 2026</span>

    <a href="{{ url('/') }}" class="foot__big" data-parallax="0.04" data-cursor="HOME" aria-label="The Last Clicks — home">The Last <em>Clicks</em></a>

    <div class="foot__grid">
      <div class="foot__col foot__intro">
        <p>Cinematic photography &amp; videography, finished by the in-house post-production that sets us apart.</p>
        <p class="foot__avail">Available for bookings — Limited slots for 2026</p>
      </div>
      <div class="foot__col">
        <h5>Studio</h5>
        <a href="{{ url('/about') }}">About</a>
        <a href="{{ url('/our-process') }}">Our Process</a>
        <a href="{{ url('/industries') }}">Industries</a>
        <a href="{{ url('/blog') }}">Journal</a>
      </div>
      <div class="foot__col">
        <h5>Work</h5>
        <a href="{{ url('/portfolio') }}">Portfolio</a>
        <a href="{{ url('/services/post-production') }}">Post Production</a>
        <a href="{{ url('/services/videography') }}">Videography</a>
        <a href="{{ url('/services/photography') }}">Photography</a>
        <a href="{{ url('/contact') }}">Start a project</a>
      </div>
      <div class="foot__col">
        <h5>Contact</h5>
        <a href="tel:{{ preg_replace('/[^+\d]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" data-noswap>WhatsApp</a>
        <a href="{{ url('/login') }}">Sign In</a>
        <a href="{{ url('/signup') }}">Create your page</a>
      </div>
    </div>

    <div class="foot__copy">
      <span>© {{ date('Y') }} TheLastClicks — All rights reserved</span>
      <span class="foot__legal">
        <a href="{{ url('/privacy-policy') }}">Privacy</a>
        <a href="{{ url('/cookie-policy') }}">Cookies</a>
        <a href="{{ url('/terms-of-service') }}">Terms</a>
        <a href="{{ url('/disclaimer') }}">Disclaimer</a>
      </span>
    </div>
  </div>
</footer>
