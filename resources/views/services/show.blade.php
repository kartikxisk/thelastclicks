<x-layouts.app
    :title="$service->title.' — TheLastClicks'"
    :description="$service->hero_copy"
    :canonical="url('/services/'.$service->slug)"
    :ogImage="$service->getFirstMediaUrl('hero') ?: $service->hero_url"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'       => 'Service',
            'name'        => $service->title,
            'description' => $service->hero_copy,
            'provider'    => ['@type' => 'Organization', 'name' => 'TheLastClicks', 'url' => url('/')],
            'areaServed'  => 'IN',
            'url'         => url('/services/'.$service->slug),
        ]" />
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $service->title, 'item' => url('/services/'.$service->slug)],
        ]]" />
    </x-slot>

    @php
        $heroImg = $service->getFirstMediaUrl('hero') ?: $service->hero_url;
        $galleryMedia = $service->getMedia('gallery');
        $galleryUrls = $galleryMedia->isNotEmpty()
            ? $galleryMedia->map(fn ($m) => $m->getUrl())->all()
            : ($service->gallery_urls ?? []);
        $gallerySpans = ['g--7', 'g--5', 'g--4', 'g--8'];
        $cta = $service->cta ?? [];
    @endphp

    {{-- 01 HERO --}}
    <section class="pp-hero" data-screen-label="01 Hero">
        <div class="pp-hero__crumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/#services') }}">Services</a>
            <span>/</span>
            <span>{{ $service->title }}</span>
        </div>
        <div class="pp-hero__row">
            <h1 data-split>{!! $service->hero_headline ?: e($service->title) !!}</h1>
            @if ($service->hero_copy)
                <p class="pp-hero__lead reveal">{{ $service->hero_copy }}</p>
            @endif
        </div>
        @if (!empty($service->hero_meta))
            <dl class="pp-hero__meta">
                @foreach ($service->hero_meta as $m)
                    <div><dt>{{ $m['label'] ?? '' }}</dt><dd>{{ $m['value'] ?? '' }}</dd></div>
                @endforeach
            </dl>
        @endif
        @if ($heroImg)
            <div class="pp-hero__cover clip-reveal">
                <img src="{{ $heroImg }}" alt="{{ $service->title }}" decoding="async">
            </div>
        @endif
    </section>

    {{-- BODY (rich content from admin) --}}
    @if ($service->body)
        <section class="service-body section">
            <x-container>
                {!! $service->body !!}
            </x-container>
        </section>
    @endif

    {{-- 02 PROOF STRIP --}}
    @if (!empty($service->proof))
        <section class="pp-proof reveal">
            <x-container>
                <div class="pp-proof__inner">
                    <div class="pp-proof__n"><span data-count="{{ $service->proof['count'] ?? 0 }}" data-decimals="0">0</span><em>·</em></div>
                    <div class="pp-proof__l">{{ $service->proof['label'] ?? '' }}</div>
                    <div class="pp-proof__sectors"><span>Sectors covered:</span> {{ $service->proof['sectors'] ?? '' }}</div>
                </div>
            </x-container>
        </section>
    @endif

    {{-- 03 PILLARS --}}
    @if (!empty($service->pillars))
        <section class="section" data-screen-label="02 Pillars">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Pillars</span>
                        <h2 class="section__title" data-split>Four things we <em>never bend.</em></h2>
                    </div>
                    <p class="section__lead reveal">Break one and we re-scope or walk. The standard is the standard.</p>
                </div>
                <div class="pp-pillars">
                    @foreach ($service->pillars as $p)
                        <article class="pp-pillar spotlight reveal"><span class="pp-pillar__dot"></span><h3>{{ $p['title'] ?? '' }}</h3><p>{{ $p['desc'] ?? '' }}</p></article>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 04 PHASES --}}
    @if (!empty($service->phases))
        <section class="pp-phases-section" data-screen-label="03 Phases">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>The flow</span>
                        <h2 class="section__title" data-split>From brief <em>to delivery.</em></h2>
                    </div>
                    <p class="section__lead reveal">Every phase: an owner, a deliverable, a review gate. No drift.</p>
                </div>
                <div class="pp-phases">
                    @foreach ($service->phases as $ph)
                        <article class="pp-phase reveal"><div class="pp-phase__num">{{ $ph['num'] ?? '' }}</div><div class="pp-phase__body"><h3>{{ $ph['title'] ?? '' }}</h3><p>{{ $ph['desc'] ?? '' }}</p></div><div class="pp-phase__time">{{ $ph['time'] ?? '' }}</div></article>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 05 GALLERY --}}
    @if (!empty($galleryUrls))
        <section class="pp-gallery-section" data-screen-label="04 Gallery">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Selected frames</span>
                        <h2 class="section__title" data-split>The <em>output.</em></h2>
                    </div>
                </div>
                <div class="pp-gallery">
                    @foreach ($galleryUrls as $i => $url)
                        <div class="pp-g {{ $gallerySpans[$i % count($gallerySpans)] }} reveal"><img src="{{ $url }}" alt="" {{ $i > 1 ? 'loading=lazy' : '' }} decoding="async"></div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 06 KIT --}}
    @if (!empty($service->kit))
        <section class="pp-kit" data-screen-label="05 Kit">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Tools we trust</span>
                        <h2 class="section__title" data-split>Cinema-grade <em>by default.</em></h2>
                    </div>
                    <p class="section__lead reveal">Our shortlist — extended per-brief when a project needs a specific look.</p>
                </div>
                <div class="pp-kit__grid">
                    @foreach ($service->kit as $i => $k)
                        <div class="pp-kit__card reveal"@if ($i > 0) data-delay="{{ $i }}"@endif><span class="pp-kit__cat">{{ $k['title'] ?? '' }}</span><p>{{ implode(' · ', $k['items'] ?? []) }}</p></div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 08 FAQ --}}
    @if (!empty($service->faqs))
        <section class="pp-faq" data-screen-label="07 FAQ">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Quick answers</span>
                        <h2 class="section__title" data-split>Things people <em>ask.</em></h2>
                    </div>
                </div>
                <div class="acc" data-acc>
                    @foreach ($service->faqs as $i => $f)
                        <div class="acc__item{{ $i === 0 ? ' is-open' : '' }}"><button class="acc__head"><h3>{{ $f['q'] ?? '' }}</h3><span class="acc__plus"></span></button><div class="acc__body"><div class="acc__body-inner">{{ $f['a'] ?? '' }}</div></div></div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 09 NEXT SERVICE --}}
    @php
        $nextService ??= \App\Models\Service::where('order', '>', $service->order)->orderBy('order')->first()
            ?? \App\Models\Service::orderBy('order')->first();
    @endphp
    @if ($nextService)
        <section class="pp-next" data-screen-label="08 Next">
            <x-container>
                <span class="kicker" style="justify-self:center">Next discipline</span>
                <a href="{{ url('/services/'.$nextService->slug) }}"><span>{{ $nextService->title }}</span> <em>→</em></a>
            </x-container>
        </section>
    @endif

    {{-- 10 CTA STRIP --}}
    <section class="cta-strip">
        <x-container>
            <h2 class="cta-strip__title" data-split>{!! $cta['title'] ?? 'Start the <em>brief.</em>' !!}</h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">{{ $cta['copy'] ?? "Brief us — treatment, timeline, and budget back within 4 working hours." }}</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-quote-prefill="{{ $cta['prefill'] ?? $service->title }}" data-magnetic data-cursor="START">Start a brief <span class="arr"></span></a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
