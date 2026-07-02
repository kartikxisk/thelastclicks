<x-layouts.app
    title="Contact — TheLastClicks"
    description="Bring us a brief — we reply within 4 working hours."
    :canonical="url('/contact')"
>
  <x-slot name="head">
    <x-json-ld :data="[
      '@type'       => 'LocalBusiness',
      'name'        => 'The Last Clicks (TLC)',
      'url'         => url('/'),
      'telephone'   => \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842'),
      'email'       => \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com'),
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

  <section class="page-header" data-screen-label="01 Header">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Contact</span></div>
    <h1 data-split>Let's <em>talk</em><br>about your brief.</h1>
    <dl class="page-header__meta">
      <div><dt>Reply within</dt><dd>4 working hours</dd></div>
      <div><dt>Studio hours</dt><dd>Mon–Sat · 10–7 IST</dd></div>
      <div><dt>HQ</dt><dd>Noida · India</dd></div>
      <div><dt>Bookings</dt><dd>Open · 2026</dd></div>
    </dl>
  </section>

  <section class="section" style="padding-bottom:48px">
    <div class="wrap contact-grid">
      <x-quote-form />

      <aside class="contact-side reveal" data-delay="1">
        <div class="contact-card">
          <h4>Email</h4>
          <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}</a>
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
    </div>
  </section>

  <!-- AVAILABILITY -->
  <section data-screen-label="03 Availability" style="padding:8px 0 clamp(64px,9vh,110px)">
    <div class="wrap">
      <div class="avail reveal">
        <div class="avail__pulse"><span class="avail__dot"></span>Booking now</div>
        <div class="avail__text">
          <strong>Taking briefs for 2026 — limited slots</strong>
          Every new brief reviewed within 4 working hours.
        </div>
        <a class="btn btn--red avail__cta" href="#quote" data-quote-trigger data-magnetic data-cursor="START">Get a quote <span class="arr"></span></a>
      </div>
    </div>
  </section>

  <!-- STUDIO HOURS -->
  <section class="hours" data-screen-label="05 Hours">
    <div class="wrap">
      <div class="hours__row">
        <div class="hours__clock reveal">
          <svg viewBox="0 0 200 200" aria-hidden="true">
            <line class="tick-major" x1="100.00" y1="12.00" x2="100.00" y2="20.00" /><line class="tick" x1="109.20" y1="12.48" x2="108.78" y2="16.46" /><line class="tick" x1="118.30" y1="13.92" x2="117.46" y2="17.84" /><line class="tick" x1="127.19" y1="16.31" x2="125.96" y2="20.11" /><line class="tick" x1="135.79" y1="19.61" x2="134.17" y2="23.26" /><line class="tick-major" x1="144.00" y1="23.79" x2="140.00" y2="30.72" /><line class="tick" x1="151.73" y1="28.81" x2="149.37" y2="32.04" /><line class="tick" x1="158.88" y1="34.60" x2="156.21" y2="37.58" /><line class="tick" x1="165.40" y1="41.12" x2="162.42" y2="43.79" /><line class="tick" x1="171.19" y1="48.27" x2="167.96" y2="50.63" /><line class="tick-major" x1="176.21" y1="56.00" x2="169.28" y2="60.00" /><line class="tick" x1="180.39" y1="64.21" x2="176.74" y2="65.83" /><line class="tick" x1="183.69" y1="72.81" x2="179.89" y2="74.04" /><line class="tick" x1="186.08" y1="81.70" x2="182.16" y2="82.54" /><line class="tick" x1="187.52" y1="90.80" x2="183.54" y2="91.22" /><line class="tick-major" x1="188.00" y1="100.00" x2="180.00" y2="100.00" /><line class="tick" x1="187.52" y1="109.20" x2="183.54" y2="108.78" /><line class="tick" x1="186.08" y1="118.30" x2="182.16" y2="117.46" /><line class="tick" x1="183.69" y1="127.19" x2="179.89" y2="125.96" /><line class="tick" x1="180.39" y1="135.79" x2="176.74" y2="134.17" /><line class="tick-major" x1="176.21" y1="144.00" x2="169.28" y2="140.00" /><line class="tick" x1="171.19" y1="151.73" x2="167.96" y2="149.37" /><line class="tick" x1="165.40" y1="158.88" x2="162.42" y2="156.21" /><line class="tick" x1="158.88" y1="165.40" x2="156.21" y2="162.42" /><line class="tick" x1="151.73" y1="171.19" x2="149.37" y2="167.96" /><line class="tick-major" x1="144.00" y1="176.21" x2="140.00" y2="169.28" /><line class="tick" x1="135.79" y1="180.39" x2="134.17" y2="176.74" /><line class="tick" x1="127.19" y1="183.69" x2="125.96" y2="179.89" /><line class="tick" x1="118.30" y1="186.08" x2="117.46" y2="182.16" /><line class="tick" x1="109.20" y1="187.52" x2="108.78" y2="183.54" /><line class="tick-major" x1="100.00" y1="188.00" x2="100.00" y2="180.00" /><line class="tick" x1="90.80" y1="187.52" x2="91.22" y2="183.54" /><line class="tick" x1="81.70" y1="186.08" x2="82.54" y2="182.16" /><line class="tick" x1="72.81" y1="183.69" x2="74.04" y2="179.89" /><line class="tick" x1="64.21" y1="180.39" x2="65.83" y2="176.74" /><line class="tick-major" x1="56.00" y1="176.21" x2="60.00" y2="169.28" /><line class="tick" x1="48.27" y1="171.19" x2="50.63" y2="167.96" /><line class="tick" x1="41.12" y1="165.40" x2="43.79" y2="162.42" /><line class="tick" x1="34.60" y1="158.88" x2="37.58" y2="156.21" /><line class="tick" x1="28.81" y1="151.73" x2="32.04" y2="149.37" /><line class="tick-major" x1="23.79" y1="144.00" x2="30.72" y2="140.00" /><line class="tick" x1="19.61" y1="135.79" x2="23.26" y2="134.17" /><line class="tick" x1="16.31" y1="127.19" x2="20.11" y2="125.96" /><line class="tick" x1="13.92" y1="118.30" x2="17.84" y2="117.46" /><line class="tick" x1="12.48" y1="109.20" x2="16.46" y2="108.78" /><line class="tick-major" x1="12.00" y1="100.00" x2="20.00" y2="100.00" /><line class="tick" x1="12.48" y1="90.80" x2="16.46" y2="91.22" /><line class="tick" x1="13.92" y1="81.70" x2="17.84" y2="82.54" /><line class="tick" x1="16.31" y1="72.81" x2="20.11" y2="74.04" /><line class="tick" x1="19.61" y1="64.21" x2="23.26" y2="65.83" /><line class="tick-major" x1="23.79" y1="56.00" x2="30.72" y2="60.00" /><line class="tick" x1="28.81" y1="48.27" x2="32.04" y2="50.63" /><line class="tick" x1="34.60" y1="41.12" x2="37.58" y2="43.79" /><line class="tick" x1="41.12" y1="34.60" x2="43.79" y2="37.58" /><line class="tick" x1="48.27" y1="28.81" x2="50.63" y2="32.04" /><line class="tick-major" x1="56.00" y1="23.79" x2="60.00" y2="30.72" /><line class="tick" x1="64.21" y1="19.61" x2="65.83" y2="23.26" /><line class="tick" x1="72.81" y1="16.31" x2="74.04" y2="20.11" /><line class="tick" x1="81.70" y1="13.92" x2="82.54" y2="17.84" /><line class="tick" x1="90.80" y1="12.48" x2="91.22" y2="16.46" />
            <circle class="pip" cx="100" cy="20" r="3" />
            <circle class="pip" cx="180" cy="100" r="3" />
            <circle class="pip" cx="100" cy="180" r="3" />
            <circle class="pip" cx="20" cy="100" r="3" />
            <line class="hand-hour" x1="100" y1="100" x2="100" y2="55" />
            <line class="hand-minute" x1="100" y1="100" x2="100" y2="38" />
            <line class="hand-second" x1="100" y1="100" x2="100" y2="32" />
            <circle class="center" cx="100" cy="100" r="4" />
          </svg>
          <div class="hours__clock-label">
            <div class="tz">UTC +05:30</div>
            <div class="city">Noida · IN</div>
          </div>
        </div>
        <div>
          <span class="hours__h">Studio hours</span>
          <h2 class="hours__title" data-split>When you'll <em>hear back.</em></h2>
          <div class="hours__list">
            <div class="hours__day"><span class="hours__day-name">Mon</span><span class="hours__day-time">10:00 — 19:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day"><span class="hours__day-name">Tue</span><span class="hours__day-time">10:00 — 19:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day"><span class="hours__day-name">Wed</span><span class="hours__day-time">10:00 — 19:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day"><span class="hours__day-name">Thu</span><span class="hours__day-time">10:00 — 19:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day"><span class="hours__day-name">Fri</span><span class="hours__day-time">10:00 — 19:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day"><span class="hours__day-name">Sat</span><span class="hours__day-time">11:00 — 17:00 IST</span><span class="hours__day-status">Open</span></div>
            <div class="hours__day is-closed"><span class="hours__day-name">Sun</span><span class="hours__day-time">By appointment</span><span class="hours__day-status">Closed</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-strip" style="padding-top:40px">
    <div class="wrap">
      <h2 class="cta-strip__title" data-split>Or just <em>say hi.</em></h2>
      <div class="cta-strip__row reveal">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">No brief yet? A DM works too.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn btn--red" href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap data-magnetic data-cursor="DM">WhatsApp us <span class="arr"></span></a>
          <a class="btn btn--ghost" href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}" data-cursor="EMAIL">Email instead <span class="arr"></span></a>
        </div>
      </div>
    </div>
  </section>
</x-layouts.app>
