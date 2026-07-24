<x-layouts.app
    title="Contact TheLastClicks — Start a Film or Photography Project"
    description="Bring us a brief for photography, videography or post-production and we will reply within 4 working hours. Crews and studios covering 20+ cities across India."
    :canonical="url('/contact')"
>
  <x-slot name="head">
    <x-json-ld :data="[
      '@type'       => 'LocalBusiness',
      // Must match the Organization name on the homepage exactly — a mismatched
      // NAP weakens entity matching and local pack eligibility.
      'name'        => 'TheLastClicks',
      'alternateName' => 'The Last Clicks (TLC)',
      'url'         => url('/'),
      'image'       => \App\Models\SiteSetting::brandLogoUrl(),
      'priceRange'  => '₹₹₹',
      'telephone'   => \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842'),
      'email'       => \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com'),
      'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'B-7, D-Block, Sector 26',
        'addressLocality' => 'Noida',
        'addressRegion'   => 'Uttar Pradesh',
        'postalCode'      => '201301',
        'addressCountry'  => 'IN',
      ],
      // Exact pin from the studio's Google Business Profile listing.
      'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => 28.5808331,
        'longitude' => 77.3328251,
      ],
      'hasMap' => 'https://share.google/QlMQkefJfn2iRnma3',
      'openingHours' => 'Mo-Sa 10:00-19:00',
      // Linking the Business Profile here is what actually ties this page to the
      // listing — a stronger signal than matching the name string exactly.
      'sameAs' => array_values(array_filter([
        'https://share.google/QlMQkefJfn2iRnma3',
        \App\Models\SiteSetting::get('socials')['instagram'] ?? null,
        \App\Models\SiteSetting::get('socials')['youtube'] ?? null,
      ])),
    ]" />
    <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact', 'item' => url('/contact')],
    ]]" />
  </x-slot>

  <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1800&q=80')">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Contact</span></div>
    <h1 data-split>Let's <em>talk</em><br>about your brief.</h1>
  </section>

  <section class="section" style="padding-bottom:48px">
    <x-container class="contact-grid">
      <x-quote-form />

      <aside class="contact-side reveal" data-delay="1">
        <div class="contact-card">
          <h4>Email</h4>
          <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}</a>
        </div>
        <div class="contact-card">
          <h4>Phone</h4>
          <a href="tel:{{ preg_replace('/[^+\d]/', '', \App\Models\SiteSetting::get('contact_phone', '+918770155842')) }}">{{ \App\Models\SiteSetting::get('contact_phone', '+91-87701-55842') }}</a>
        </div>
        <div class="contact-card">
          <h4>WhatsApp</h4>
          <a href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap>{{ \App\Models\SiteSetting::get('contact_phone', '+91-87701-55842') }} · DM us</a>
        </div>
        <div class="contact-card">
          <h4>Studio</h4>
          {{-- Kept identical to the schema address and the Business Profile listing. --}}
          <p>TheLastClicks<br>B-7, D-Block, Sector 26<br>Noida · Uttar Pradesh<br>India · 201301</p>
          <a href="https://share.google/QlMQkefJfn2iRnma3" target="_blank" rel="noopener" data-noswap>Open in Google Maps ↗</a>
        </div>
      </aside>
    </x-container>
  </section>

  {{-- Studio location. Lazy-loaded so the map never competes with the form for
       bandwidth, and the iframe is titled for screen readers. --}}
  <section class="section" style="padding-top:0" data-screen-label="03 Location">
    <x-container>
      <div class="contact-map reveal">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3503.6312724114205!2d77.3328251!3d28.580833099999992!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce56b4a8e1c41%3A0x2f95fac3ad6f578a!2sThe%20Last%20Clicks%20(TLC)!5e0!3m2!1sen!2sin!4v1784897931408!5m2!1sen!2sin"
          title="TheLastClicks studio location — B-7, D-Block, Sector 26, Noida"
          loading="lazy"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen></iframe>
      </div>
    </x-container>
  </section>

  <section class="cta-strip" style="padding-top:40px">
    <x-container>
      <h2 class="cta-strip__title" data-split>Or just <em>say hi.</em></h2>
      <div class="cta-strip__row reveal">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">No brief yet? A DM works too.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn btn--red" href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap data-magnetic data-cursor="DM">WhatsApp us <span class="arr"></span></a>
          <a class="btn btn--ghost" href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}" data-cursor="EMAIL">Email instead <span class="arr"></span></a>
        </div>
      </div>
    </x-container>
  </section>
</x-layouts.app>
