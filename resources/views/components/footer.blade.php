<footer class="foot">
  <div class="wrap">
    <div class="foot__big" data-parallax="0.04">Let's <em>create</em>.</div>
    <div class="foot__grid">
      <div class="foot__col">
        <h5>The Last Clicks</h5>
        <p>Cinematic photography, videography &amp; production for brands, events and weddings — built to scale with your story.</p>
        <p style="margin-top:18px">Available for bookings — Limited slots for 2026</p>
      </div>
      <div class="foot__col">
        <h5>Studio</h5>
        <a href="{{ url('/about') }}">About</a>
        <a href="{{ url('/our-process') }}">Our Process</a>
        <a href="{{ url('/crew') }}">Talent</a>
        <a href="{{ url('/industries') }}">Industries</a>
        <a href="{{ url('/blog') }}">Journal</a>
      </div>
      <div class="foot__col">
        <h5>Work</h5>
        <a href="{{ url('/portfolio') }}">Portfolio</a>
        <a href="{{ url('/portfolio') }}#weddings">Weddings</a>
        <a href="{{ url('/portfolio') }}#brands">Brand films</a>
        <a href="{{ url('/portfolio') }}#corporate">Corporate</a>
        <a href="{{ url('/contact') }}">Start a project</a>
      </div>
      <div class="foot__col">
        <h5>Contact</h5>
        <a href="tel:{{ preg_replace('/[^+\d]/', '', \App\Models\SiteSetting::get('contact_phone', '+918770155842')) }}">{{ \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842') }}</a>
        <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}</a>
        <a href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap>WhatsApp</a>
        <a href="{{ url('/login') }}">Sign In</a>
        <a href="{{ url('/signup') }}">Create your page</a>
      </div>
    </div>
    <div class="foot__copy">
      <span>© {{ date('Y') }} TheLastClicks · All rights reserved</span>
      <span style="display:flex;gap:18px;flex-wrap:wrap">
        <a href="{{ url('/privacy-policy') }}">Privacy</a>
        <a href="{{ url('/cookie-policy') }}">Cookies</a>
        <a href="{{ url('/terms-of-service') }}">Terms</a>
        <a href="{{ url('/disclaimer') }}">Disclaimer</a>
      </span>
      <span>Made with care · Bhopal · Mumbai · Delhi</span>
    </div>
  </div>
</footer>
