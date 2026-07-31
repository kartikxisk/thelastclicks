<x-layouts.app
    title="Disclaimer — Site & Portfolio Notice | TheLastClicks"
    description="General disclaimer covering the accuracy of information, portfolio imagery and third-party links published on the TheLastClicks website."
    :canonical="url('/disclaimer')"
>
  <section class="page-header" data-screen-label="01 Header" style="min-height:40vh">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Disclaimer</span></div>
    <h1 data-split><em>Disclaimer.</em></h1>
  </section>
  <section class="section"><x-container><div class="legal">
    @include("pages.legal.disclaimer")
  </div></x-container></section>
</x-layouts.app>
