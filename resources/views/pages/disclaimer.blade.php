<x-layouts.app
    title="Disclaimer — TheLastClicks"
    description="General disclaimer for this website."
    :canonical="url('/disclaimer')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Disclaimer</span></div>
    <h1 data-split><em>Disclaimer.</em></h1>
    <dl class="page-header__meta"><div><dt>Effective</dt><dd>01 May 2026</dd></div></dl>
  </section>
  <section class="section"><div class="wrap"><div class="legal">
    <p>The information provided on this website is for general informational purposes only. While we strive for accuracy, TheLastClicks makes no warranties about completeness, reliability, or availability.</p>
    <h2>1. External links</h2>
    <p>We may link to external sites. We do not control their content and assume no responsibility for their practices or policies.</p>
    <h2>2. Brand mentions</h2>
    <p>References to brand names are illustrative of categories we have served. All trademarks belong to their respective owners.</p>
    <h2>3. Visual content</h2>
    <p>Imagery on this site may include placeholders for visual reference. Final delivered work is governed by individual project agreements.</p>
    <h2>4. Contact</h2>
    <p>For corrections or questions, write to <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'hello@thelastclicks.com') }}</a>.</p>
  </div></div></section>
</x-layouts.app>
