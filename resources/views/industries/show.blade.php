<x-layouts.app
    :title="$industry->title.' — Industries — TheLastClicks'"
    :description="$industry->summary"
    :canonical="url('/industries/'.$industry->slug)"
>

    {{-- HERO --}}
    <section class="page-header" data-screen-label="01 Hero">
        <div class="page-header__crumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/industries') }}">Industries</a>
            <span>/</span>
            <span>{{ $industry->title }}</span>
        </div>
        <h1 data-split>{{ $industry->title }}</h1>
        @if ($industry->summary)
            <p class="pp-hero__lead reveal">{{ $industry->summary }}</p>
        @endif
    </section>

    {{-- RICH BODY (populated via admin in later plan) --}}
    @if ($industry->body)
        <section class="industry-body section">
            <div class="wrap">
                {!! $industry->body !!}
            </div>
        </section>
    @endif

    {{-- PORTFOLIO WORK GRID --}}
    @if ($work->isNotEmpty())
        <section class="section" data-screen-label="Selected work">
            <div class="wrap">
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Selected work</span>
                        <h2 class="section__title" data-split>The <em>output.</em></h2>
                    </div>
                </div>
                <div class="work-grid">
                    @foreach ($work as $item)
                        <x-card-portfolio :item="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA STRIP --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Let's <em>create.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">
                    Brief us — treatment, timeline, and budget within 4 working hours.
                </p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>
