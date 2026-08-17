<x-layouts.app
    :title="\App\Support\Brand::title('Terms of Service')"
    description="The terms governing TheLastClicks photography and film production services, this website, bookings, deliverables, licensing and image usage rights."
    :canonical="url('/terms-of-service')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Terms</span></div>
    <h1 data-split>Terms of <em>service.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    @include("pages.legal.terms-of-service")
  </div></x-container></section>
</x-layouts.app>
