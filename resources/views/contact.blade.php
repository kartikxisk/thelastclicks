<x-layouts.app
    :title="\App\Support\Brand::title('Contact Our Photography & Video Team')"
    description="Bring us a brief for photography, videography or post-production and we will reply within 4 working hours. Crews and studios covering 20+ cities across India."
    :canonical="url('/contact')"
>
  <x-slot name="head">
    {{-- PhotographStudio, not the generic LocalBusiness it used to be: schema.org
         ships an exact subtype for this business, and the more specific type is
         the one a parser can do something with.

         Every value below now comes from Site Settings via App\Support\Nap. It was
         all hardcoded here, which meant this page and the homepage each carried
         their own copy of the studio's address — and the homepage's copy, being
         admin-fed and unpopulated, was empty. One source now feeds both. --}}
    <x-json-ld :data="array_filter([
      '@type'       => 'PhotographStudio',
      // Anchors this node as an entity the rest of the graph can point at, and
      // ties it to the canonical brand node on the homepage. Without the link the
      // two nodes are, to a parser, two unrelated businesses that share a name.
      '@id'         => url('/').'#localbusiness',
      'parentOrganization' => ['@id' => url('/').'#organization'],
      // Must match the Organization name on the homepage exactly — a mismatched
      // NAP weakens entity matching and local pack eligibility.
      'name'        => \App\Support\Brand::NAME,
      'alternateName' => \App\Support\Brand::ALTERNATE_NAMES,
      'url'         => url('/'),
      'image'       => \App\Models\SiteSetting::brandLogoUrl(),
      'priceRange'  => '₹₹₹',
      'telephone'   => \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842'),
      'email'       => \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com'),
      'address'     => \App\Support\Nap::address(),
      'geo'         => \App\Support\Nap::geo(),
      'areaServed'  => \App\Support\Nap::areaServed(),
      'hasMap'      => \App\Support\Nap::mapUrl(),
      // openingHoursSpecification, not the old 'Mo-Sa 10:00-19:00' string: the
      // structured form is parseable per-day and survives a studio that closes
      // midweek, which the string could only express by being rewritten.
      'openingHoursSpecification' => \App\Support\Nap::hours(),
      // Linking the Business Profile here is what actually ties this page to the
      // listing — a stronger signal than matching the name string exactly.
      'sameAs' => array_values(array_filter([
        \App\Support\Nap::mapUrl(),
        \App\Models\SiteSetting::get('socials')['instagram'] ?? null,
        \App\Models\SiteSetting::get('socials')['youtube'] ?? null,
      ])),
    ], fn ($v) => $v !== null && $v !== [])" />
    <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact', 'item' => url('/contact')],
    ]]" />
  </x-slot>

  @php $pageHeader = \App\Models\SiteSetting::pageImage('contact'); @endphp
  <section class="page-header page-header--media" data-screen-label="01 Header" @if ($pageHeader) style="--ph-bg:url('{{ $pageHeader }}')" @endif>
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Contact</span></div>
    <h1 data-split>Let's <em>talk</em><br>about your brief.</h1>
  </section>

  <section class="section">
    <x-container class="contact-grid" data-stagger>
      <x-quote-form />

      <aside class="contact-side" data-anim="slide-r" aria-labelledby="contact-details-heading">
        {{-- Structures the page for assistive tech (h1 -> h2 -> h3) without a
             visible section title; the cards read as small mono labels. --}}
        <h2 id="contact-details-heading" class="sr-only">Contact details</h2>
        <div class="contact-card">
          <h3>Email</h3>
          <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}</a>
        </div>
        <div class="contact-card">
          <h3>Phone</h3>
          <a href="tel:{{ preg_replace('/[^+\d]/', '', \App\Models\SiteSetting::get('contact_phone', '+918770155842')) }}">{{ \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842') }}</a>
        </div>
        <div class="contact-card">
          <h3>WhatsApp</h3>
          <a href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap>{{ \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842') }} · DM us</a>
        </div>
        <div class="contact-card">
          <h3>Studio</h3>
          {{-- Kept identical to the schema address and the Business Profile listing. --}}
          <p>TheLastClicks<br>B-7, D-Block, Sector 26<br>Noida · Uttar Pradesh<br>India · 201301</p>
          <a href="https://share.google/QlMQkefJfn2iRnma3" target="_blank" rel="noopener" data-noswap>Open in Google Maps ↗</a>
        </div>
      </aside>
    </x-container>
  </section>

  {{-- Studio location. Lazy-loaded so the map never competes with the form for
       bandwidth, and the iframe is titled for screen readers. --}}
  <section class="section" data-screen-label="03 Location">
      <x-scene-bg type="grid" />
    <x-container>
      <div class="contact-map" data-anim="blur-focus">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3503.6312724114205!2d77.3328251!3d28.580833099999992!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce56b4a8e1c41%3A0x2f95fac3ad6f578a!2sThe%20Last%20Clicks%20(TLC)!5e0!3m2!1sen!2sin!4v1784897931408!5m2!1sen!2sin"
          title="TheLastClicks studio location — B-7, D-Block, Sector 26, Noida"
          loading="lazy"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen></iframe>
      </div>
    </x-container>
  </section>

  <section class="cta-strip">
        <x-scene-bg type="photo" />
    <x-container data-stagger>
      <h2 class="cta-strip__title" data-split data-anim="mask-up">Send us a brief.<br>Or just <em>say hi.</em></h2>
      <div class="cta-strip__row" data-anim="rise">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">No brief yet? A DM works too.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn btn--red" href="{{ \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842') }}" target="_blank" rel="noopener" data-noswap data-magnetic data-cursor="DM">WhatsApp us <span class="arr"></span></a>
          <a class="btn btn--ghost" href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}" data-cursor="EMAIL">Email instead <span class="arr"></span></a>
        </div>
      </div>
    </x-container>
  </section>
</x-layouts.app>
