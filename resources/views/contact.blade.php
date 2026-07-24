<x-layouts.app
    title="Contact TheLastClicks — Start a Film or Photography Project"
    description="Bring us a brief for photography, videography or post-production and we will reply within 4 working hours. Crews and studios covering 20+ cities across India."
    :canonical="url('/contact')"
>
  <x-slot name="head">
    <x-json-ld :data="[
      '@type'       => 'LocalBusiness',
      'name'        => 'The Last Clicks (TLC)',
      'url'         => url('/'),
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
      'openingHours' => 'Mo-Sa 10:00-19:00',
    ]" />
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
          <p>The Last Clicks (TLC)<br>B-7, D-Block, Sector 26<br>Noida · Uttar Pradesh<br>India · 201301</p>
        </div>
      </aside>
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
