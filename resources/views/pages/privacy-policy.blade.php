<x-layouts.app
    title="Privacy Policy — TheLastClicks"
    description="How we collect, use and protect your data."
    :canonical="url('/privacy-policy')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Privacy Policy</span></div>
    <h1 data-split>Privacy <em>policy.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    <p>This Privacy Policy explains how TheLastClicks ("we", "us", "our") collects, uses, and protects your personal information when you visit our website or engage our services.</p>
    <h2>1. Information we collect</h2>
    <p>We collect information you provide when filling out a brief, signing up, or contacting us — including name, email, phone, company, and project details. We also collect technical information automatically (browser, device, anonymized analytics).</p>
    <h2>2. How we use it</h2>
    <ul><li>To respond to your inquiries and deliver agreed services.</li><li>To send relevant updates, only with consent.</li><li>To improve the quality of our website and offerings.</li><li>To meet legal and contractual obligations.</li></ul>
    <h2>3. Sharing</h2>
    <p>We do not sell your data. We share information only with vendors required to deliver services (hosting, email), under strict confidentiality.</p>
    <h2>4. Your rights</h2>
    <p>You may access, correct, or delete your data at any time. Reach out at <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}">{{ \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com') }}</a>.</p>
    <h2>5. Retention</h2>
    <p>We keep project data for the duration required by contract and applicable law, then delete or anonymize.</p>
    <h2>6. Changes</h2>
    <p>We may update this policy. Material changes will be notified on this page and, where relevant, by email.</p>
  </div></x-container></section>
</x-layouts.app>
