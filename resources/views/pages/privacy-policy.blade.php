<x-layouts.app
    title="Privacy Policy — How We Handle Your Data | TheLastClicks"
    description="How TheLastClicks collects, uses, stores and protects personal data submitted through our website, enquiry forms and production work across India."
    :canonical="url('/privacy-policy')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Privacy Policy</span></div>
    <h1 data-split>Privacy <em>policy.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    @include("pages.legal.privacy-policy")
  </div></x-container></section>
</x-layouts.app>
