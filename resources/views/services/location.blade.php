@php
    $pageUrl = url('/services/'.$service->slug.'/'.$location->city_slug);
@endphp
<x-layouts.app
    :title="\App\Support\Brand::title($location->meta_title ?: $location->headline)"
    :description="$location->meta_description"
    :canonical="$pageUrl"
    :ogImage="$service->heroUrl()"
>
    <x-slot name="head">
        {{-- No Offer, price or FAQPage: the studio publishes no pricing, and the
             page carries no literal Q&A. --}}
        <x-json-ld :data="[
            '@type'       => 'Service',
            '@id'         => $pageUrl.'#service',
            'name'        => $location->headline,
            'serviceType' => $service->title,
            'description' => $location->meta_description,
            'provider'    => ['@id' => url('/').'#organization'],
            'areaServed'  => ['@type' => 'City', 'name' => $location->city_name],
            'url'         => $pageUrl,
        ]" />
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $service->title, 'item' => url('/services/'.$service->slug)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $location->city_name, 'item' => $pageUrl],
        ]]" />
    </x-slot>

    <section class="page-header" data-screen-label="01 Header">
        <div class="page-header__crumb">
            <a href="{{ url('/') }}">Home</a><span>/</span>
            <a href="{{ url('/services/'.$service->slug) }}">{{ $service->title }}</a><span>/</span>
            <span>{{ $location->city_name }}</span>
        </div>
        <h1>{{ $location->headline }}</h1>
    </section>

    {{-- intro, section and answer bodies are admin/seed-authored HTML — same
         trust as Industry::body. One flat set of blocks: CSS places them in two
         columns on desktop and orders them for one column below, so no link or
         heading is rendered twice. --}}
    <section class="section ind-case" data-screen-label="02 Overview">
        <x-container>
            <div class="loc-body">
                <div class="ind-case__copy loc-body__intro">{!! $location->intro !!}</div>

                <div class="loc-body__side">
                    @if (! empty($location->highlights))
                        <aside class="loc-glance" aria-labelledby="loc-glance-title">
                            <h2 class="loc-side__label" id="loc-glance-title">At a glance</h2>
                            <ul class="loc-glance__list">
                                @foreach ($location->highlights as $fact)
                                    <li>{{ $fact }}</li>
                                @endforeach
                            </ul>
                        </aside>
                    @endif
                    <div class="loc-rail">
                        {{-- data-quote-trigger: chrome.js's delegated handler opens the quote
                             modal and prefills it; href is the no-JS fallback. --}}
                        <a class="btn btn--red loc-rail__cta" href="{{ url('/contact') }}" data-quote-trigger
                           data-quote-prefill="{{ $service->title }} — {{ $location->city_name }}" data-cursor="START">
                            Start a brief <span class="arr"></span>
                        </a>
                        <a class="loc-rail__alt" href="{{ url('/contact') }}">Or write to us</a>

                        <nav class="loc-links" aria-label="{{ $service->title }} in other cities">
                            <h2 class="loc-side__label">{{ $service->title }} in other cities</h2>
                            <ul class="ind-case__services">
                                <li><a href="{{ url('/services/'.$service->slug) }}">All {{ $service->title }} <span class="arr"></span></a></li>
                                @foreach ($otherCities as $c)
                                    {{-- Full "service in city" anchor, not the bare city name: the anchor
                                         is what tells a crawler what the target page is about. --}}
                                    <li><a href="{{ url('/services/'.$service->slug.'/'.$c->city_slug) }}">{{ $service->title }} in {{ $c->city_name }} <span class="arr"></span></a></li>
                                @endforeach
                            </ul>
                        </nav>
                        @if ($otherServices->isNotEmpty())
                            <nav class="loc-links" aria-label="Other services in {{ $location->city_name }}">
                                <h2 class="loc-side__label">Other services in {{ $location->city_name }}</h2>
                                <ul class="ind-case__services">
                                    @foreach ($otherServices as $o)
                                        <li><a href="{{ url('/services/'.$o->service->slug.'/'.$o->city_slug) }}">{{ $o->service->title }} in {{ $location->city_name }} <span class="arr"></span></a></li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>

                <div class="ind-case__copy loc-body__sections">
                    @foreach ($location->sections ?? [] as $section)
                        <h2>{{ $section['heading'] ?? '' }}</h2>
                        {!! $section['body'] ?? '' !!}
                    @endforeach
                </div>

                @if (! empty($location->faqs))
                    {{-- Visible Q&A only: Google retired FAQ rich results, so no FAQPage markup. --}}
                    <div class="ind-case__copy loc-body__faqs loc-faq">
                        {{-- Service + city, not the headline: headlines run long ("… for Offices
                             and Towers") and read badly after "Questions about". --}}
                        <h2>Questions about {{ \Illuminate\Support\Str::lower($service->title) }} in {{ $location->city_name }}</h2>
                        @foreach ($location->faqs as $faq)
                            <h3>{{ $faq['q'] ?? '' }}</h3>
                            {!! $faq['a'] ?? '' !!}
                        @endforeach
                    </div>
                @endif
            </div>
        </x-container>
    </section>

    @if ($works->isNotEmpty())
        <section class="pp-gallery-section" data-screen-label="03 Work">
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow">Selected work</span>
                        <h2 class="section__title">The <em>output.</em></h2>
                    </div>
                </div>
                <x-media-grid
                    :items="$works"
                    layout="bento"
                    :meta="fn ($work) => collect([$work->client, $work->categoryLabel()])->filter()->implode(' · ')"
                    lightboxLabel="{{ $service->title }} work"
                />
            </x-container>
        </section>
    @endif

    <section class="cta-strip">
        <x-scene-bg type="photo" />
        <x-container>
            <h2 class="cta-strip__title">{{ $service->title }} in {{ $location->city_name }}?<br>Let's talk about <em>your brief.</em></h2>
            <div class="cta-strip__row">
                <a class="btn btn--red" href="{{ url('/contact') }}" data-quote-trigger
                   data-quote-prefill="{{ $service->title }} — {{ $location->city_name }}" data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>
</x-layouts.app>
