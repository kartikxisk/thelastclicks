<x-layouts.app
    title="Cookie Policy — Cookies We Use | TheLastClicks"
    description="The cookies and similar technologies TheLastClicks uses on this website, what each one does, and how to block or delete them in your browser."
    :canonical="url('/cookie-policy')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Cookie Policy</span></div>
    <h1 data-split>Cookie <em>policy.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    <p>We use cookies and similar technologies to make our website work, understand how it's used, and improve your experience.</p>
    <h2>1. Strictly necessary</h2>
    <p>Required for the site to function — these cannot be turned off.</p>
    <h2>2. Performance &amp; analytics</h2>
    <p>Help us understand which content resonates. Anonymized and aggregated.</p>
    <h2>3. Functional</h2>
    <p>Remember your preferences (e.g. language, theme).</p>
    <h2>4. Managing cookies</h2>
    <p>Most browsers let you block or delete cookies. Doing so may affect site functionality. For questions, contact <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}</a>.</p>
  </div></x-container></section>
</x-layouts.app>
