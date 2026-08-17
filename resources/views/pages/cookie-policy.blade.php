<x-layouts.app
    :title="\App\Support\Brand::title('Cookie Policy')"
    description="The cookies and similar technologies TheLastClicks uses on this website, what each one does, and how to block or delete them in your browser."
    :canonical="url('/cookie-policy')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Cookie Policy</span></div>
    <h1 data-split>Cookie <em>policy.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    @include("pages.legal.cookie-policy")
  </div></x-container></section>
</x-layouts.app>
