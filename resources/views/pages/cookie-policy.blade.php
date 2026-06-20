<x-layouts.app
    title="Cookie Policy — TheLastClicks"
    description="Cookies and similar technologies we use."
    :canonical="url('/cookie-policy')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Cookie Policy</span></div>
    <h1 data-split>Cookie <em>policy.</em></h1>
    <dl class="page-header__meta"><div><dt>Effective</dt><dd>01 May 2026</dd></div><div><dt>Version</dt><dd>2.1</dd></div></dl>
  </section>
  <section class="section"><div class="wrap"><div class="legal">
    <p>We use cookies and similar technologies to make our website work, understand how it's used, and improve your experience.</p>
    <h2>1. Strictly necessary</h2>
    <p>Required for the site to function — these cannot be turned off.</p>
    <h2>2. Performance &amp; analytics</h2>
    <p>Help us understand which content resonates. Anonymized and aggregated.</p>
    <h2>3. Functional</h2>
    <p>Remember your preferences (e.g. language, theme).</p>
    <h2>4. Managing cookies</h2>
    <p>Most browsers let you block or delete cookies. Doing so may affect site functionality. For questions, contact <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}</a>.</p>
  </div></div></section>
</x-layouts.app>
