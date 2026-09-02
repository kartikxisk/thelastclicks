<x-layouts.app
    :title="\App\Support\Brand::title($service->title)"
    :description="$service->hero_copy"
    :canonical="url('/services/'.$service->slug)"
    :ogImage="$service->heroUrl()"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'       => 'Service',
            'name'        => $service->title,
            'description' => $service->hero_copy,
            'provider'    => ['@type' => 'Organization', 'name' => \App\Support\Brand::NAME, 'url' => url('/')],
            'areaServed'  => 'IN',
            'url'         => url('/services/'.$service->slug),
        ]" />
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $service->title, 'item' => url('/services/'.$service->slug)],
        ]]" />

        {{-- The FAQ accordion below is the one block of literal question-and-answer
             copy on the site, and without this node it was invisible to answer
             engines — an assistant extracting Q&A reads the schema, not a JS
             accordion. Built from the same $service->faqs the section renders,
             so the two cannot drift apart. --}}
        @if (!empty($service->faqs))
            <x-json-ld :data="[
                '@type' => 'FAQPage',
                'mainEntity' => collect($service->faqs)->map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f['q'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? ''],
                ])->all(),
            ]" />
        @endif
    </x-slot>

    @php
        $heroImg = $service->heroUrl();
        $galleryUrls = $service->galleryUrls();
        $gallerySpans = ['g--7', 'g--5', 'g--4', 'g--8'];
        $cta = $service->cta ?? [];
        // Per-service headings for the blocks below. Every read falls back to the
        // string that used to be hardcoded there, so a service with no `sections`
        // row renders exactly as this page did before the column existed.
        $sections = $service->sections ?? [];
        // Eager-loaded here rather than in the loop: the grid reads cover media
        // per tile, and lazily resolving that is the N+1 assertQueryCount() in
        // tests/Pest.php exists to catch.
        $serviceWorks = $service->publishedWorks()->with('media')->get();
        $serviceIndustries = $service->industries;
    @endphp


    {{-- 01 HEADER — the same full-bleed media header every other top-level page
         uses, so a service no longer opens on a different shape to /about or
         /portfolio. The photograph fills the header and the crumb, title,
         standfirst and spec strip sit on top of it; the old layout stacked a
         heading block above a 2.6:1 letterbox crop, which read as a page with a
         picture under it rather than a page that opens on one.

         No hero_meta spec strip: "Typical scope — Per project" and "Timeline —
         1–3 weeks" are not things a buyer can act on, and Phases below states the
         real timeline per stage. The data is still on the model and still editable
         in the admin, so restoring the strip is just re-adding the <dl>. --}}
    <section class="page-header page-header--media" data-screen-label="01 Header"
             @if ($heroImg) style="--ph-bg:url('{{ $heroImg }}')" @endif>
        <div class="page-header__crumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/#services') }}">Services</a>
            <span>/</span>
            <span>{{ $service->title }}</span>
        </div>
        <h1 data-split>{!! $service->hero_headline ?: e($service->title) !!}</h1>
    </section>

    {{-- 02 STANDFIRST — the positioning statement, on the page rather than over
         the photograph. hero_copy used to reach only the <meta> description and the
         JSON-LD; putting it in the header made it visible but left it competing
         with the image for contrast and capped at the header's measure. Out here
         it gets its own band, its own measure and no scrim to fight. --}}
    @if ($service->hero_copy)
        <section class="pp-intro" data-screen-label="02 Standfirst">
            <x-container>
                <p class="pp-intro__copy" data-anim="rise">{{ $service->hero_copy }}</p>
            </x-container>
        </section>
    @endif

    {{-- BODY (rich content from admin) --}}
    @if ($service->body)
        <section class="service-body section">
            <x-container>
                {!! $service->body !!}
            </x-container>
        </section>
    @endif

    {{-- 02 PROOF STRIP — retired. An unverifiable counter ("286 cuts finished") next to
         a sector list the visitor cannot act on, and the number is seeded rather than
         derived from real work. The `proof` data is still on the model. --}}

    {{-- 03 PILLARS — retired. Phases below already answers "how do you work", concretely
         and with a timeline; Pillars restated it as four generic promises, and two
         how-we-work blocks on one page dilute both. The `pillars` data is still seeded
         and stored on the model, so restoring this section is just un-commenting it. --}}

    {{-- 04 PHASES --}}
    @if (!empty($service->phases))
        <section class="pp-phases-section" data-screen-label="03 Phases">
            <x-scene-bg type="edit" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>The flow</span>
                        <h2 class="section__title" data-split>{!! $sections['flow']['title'] ?? 'From brief <em>to delivery.</em>' !!}</h2>
                    </div>
                    <p class="section__lead" data-anim="rise">{{ $sections['flow']['lead'] ?? 'Every phase: an owner, a deliverable, a review gate. No drift.' }}</p>
                </div>
                <div class="pp-phases">
                    @foreach ($service->phases as $ph)
                        {{-- Index, stage, description. No duration.
                             The stage used to state one — "Day 1–5", "+10 days" —
                             and the studio cannot commit to a figure before it knows
                             the scope: the same five stages run over a single-day
                             product shoot and a multi-unit campaign. The sequence is
                             the promise; the dates are set in the quote. --}}
                        {{-- skew-in, not curtain: the phases are a sequence, and a
                             slight shear settling out as each row lands reads as
                             steps arriving rather than five identical wipes. --}}
                        <article class="pp-phase scene-stop" data-anim="skew-in" data-sheen>
                            <div class="pp-phase__num">{{ $ph['num'] ?? '' }}</div>
                            <div class="pp-phase__head">
                                <h3>{{ $ph['title'] ?? '' }}</h3>
                            </div>
                            <p class="pp-phase__desc">{{ $ph['desc'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>

                {{-- Says where the timeline actually lives, now that no stage states
                     one. Without it the section reads as though the studio simply
                     declines to answer "how long". --}}
                <p class="pp-phases__note" data-anim="rise">
                    {{ $sections['flow']['note'] ?? 'Your timeline is fixed in the quote, once we know the scope.' }}
                </p>
            </x-container>
        </section>
    @endif

    {{-- 05 SELECTED WORK — real projects when the service has any attached.

         The frames gallery below is the fallback, not a second section: two media
         grids back to back compete, and a titled project with a client on it
         makes the case an anonymous frame cannot. A service nobody has curated
         yet keeps rendering its frames exactly as before.

         Tiles are not linked. /portfolio/{slug} is a retired route that 301s to
         home, so linking them would walk a visitor off the page they were
         reading — the grid opens its own lightbox instead, which is what
         /portfolio does with the same component. --}}
    @if ($serviceWorks->isNotEmpty())
        <section class="pp-gallery-section" data-screen-label="04 Work">
            <x-scene-bg type="photo" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>{{ $sections['work']['lead'] ?? 'Selected work' }}</span>
                        <h2 class="section__title" data-split>{!! $sections['work']['title'] ?? 'The <em>output.</em>' !!}</h2>
                    </div>
                </div>
                <x-media-grid
                    :items="$serviceWorks"
                    layout="bento"
                    :meta="fn ($work) => collect([$work->client, $work->categoryLabel()])->filter()->implode(' · ')"
                    lightboxLabel="{{ $service->title }} work"
                />
            </x-container>
        </section>
    @elseif (!empty($galleryUrls))
        <section class="pp-gallery-section" data-screen-label="04 Gallery">
            <x-scene-bg type="photo" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Selected frames</span>
                        <h2 class="section__title" data-split>The <em>output.</em></h2>
                    </div>
                </div>
                <div class="pp-gallery" data-stagger>
                    @foreach ($galleryUrls as $i => $url)
                        {{-- The one genuinely unlabelled image on the site: unlike the
                             work tiles and industry cards, these frames sit in a bare
                             div with no aria-label and no visible caption, so an empty
                             alt left them invisible to both screen readers and image
                             search. For a studio that sells photographs, image search is
                             not a side channel. Indexed per frame so the four are not
                             four identical strings. --}}
                        <div class="pp-g {{ $gallerySpans[$i % count($gallerySpans)] }} scene-stop" data-anim="iris" data-zoom><img src="{{ $url }}" alt="{{ $service->title }} work by TheLastClicks — frame {{ $i + 1 }}" {{ $i > 1 ? 'loading=lazy' : '' }} decoding="async"></div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 06 KIT --}}
    @if (!empty($service->kit))
        <section class="pp-kit" data-screen-label="05 Kit">
            <x-scene-bg type="camera" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Our arsenal</span>
                        <h2 class="section__title" data-split>{!! $sections['kit']['title'] ?? 'Cinema-grade <em>by default.</em>' !!}</h2>
                    </div>
                    <p class="section__lead" data-anim="slide-l">{{ $sections['kit']['lead'] ?? 'Our shortlist — extended per-brief when a project needs a specific look.' }}</p>
                </div>
                <div class="pp-kit__grid" data-stagger>
                    @foreach ($service->kit as $i => $k)
                        {{-- A real list, not a middot-joined run. Kit is scanned for
                             one name at a time ("do they shoot ARRI?"), and a single
                             wrapped paragraph makes the reader parse the whole card
                             to answer that. --}}
                        {{-- Cards get the overshoot spring; the old rotate-in tipped
                             them off-axis, which fought the hard-edged grid. --}}
                        <div class="pp-kit__card" data-anim="pop" data-lift>
                            <span class="pp-kit__cat">{{ $k['title'] ?? '' }}</span>
                            <ul class="pp-kit__list">
                                @foreach ($k['items'] ?? [] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 08 FAQ --}}
    @if ($serviceIndustries->isNotEmpty())
        {{-- Who this service is bought by. Editorial, not derived from the
             attached work: a service with nothing filed under it yet still
             covers its verticals. Also the route from a service page into the
             industry pages, which otherwise had one way in. --}}
        {{-- Same compact header as the industry cross-links, and for the same
             reason: .services__head is a two-column grid that leaves a 400px
             hole when only the heading is filled. --}}
        <section class="section ind-more" data-screen-label="Industries" data-service-industries>
            <x-container>
                <div class="ind-more__head">
                    <span class="section__eyebrow">Who we shoot for</span>
                    <h2 class="ind-more__title">{{ $service->title }} for <em>your sector.</em></h2>
                </div>
                <nav class="ind-cross" aria-label="Industries we cover for {{ $service->title }}">
                    @foreach ($serviceIndustries as $ind)
                        <a class="ind-cross__link" href="{{ url('/industries/'.$ind->slug) }}">
                            {{ $ind->title }} <span class="arr"></span>
                        </a>
                    @endforeach
                </nav>
            </x-container>
        </section>
    @endif
    @if (!empty($service->faqs))
        <section class="pp-faq" data-screen-label="07 FAQ">
            <x-scene-bg type="grid" />
            <x-container>
                <div class="services__head">
                    <div>
                        <span class="section__eyebrow" data-scramble>Operational protocols</span>
                        <h2 class="section__title" data-split>Capabilities <em>&amp; compliance.</em></h2>
                    </div>
                </div>
                <div class="acc" data-acc data-stagger>
                    @foreach ($service->faqs as $i => $f)
                        <div class="acc__item{{ $i === 0 ? ' is-open' : '' }}" data-anim="rise"><button class="acc__head"><h3>{{ $f['q'] ?? '' }}</h3><span class="acc__plus"></span></button><div class="acc__body"><div class="acc__body-inner">{{ $f['a'] ?? '' }}</div></div></div>
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- 09 CTA STRIP — the page's single close. The next-discipline link rides along
         inside it rather than as its own band, so the page doesn't end on two
         competing calls to action. --}}
    @php
        $nextService ??= \App\Models\Service::where('order', '>', $service->order)->orderBy('order')->first()
            ?? \App\Models\Service::orderBy('order')->first();
    @endphp
    <section class="cta-strip">
        <x-scene-bg type="photo" />
        <x-container data-stagger>
            {{-- One wrapper, because .cta-strip > .container is a two-column flex
                 row: title on the left, action on the right. Adding the eyebrow and
                 the copy as further children of it would have made four columns and
                 stranded the button. --}}
            <div class="cta-strip__lead">
                <span class="section__eyebrow" data-scramble>Commissions</span>
                <h2 class="cta-strip__title" data-split data-anim="mask-up">{!! $cta['title'] ?? 'Start with a brief.<br>Or start with <em>a question.</em>' !!}</h2>
                {{-- cta.copy has been seeded and admin-editable all along but nothing
                     ever printed it, so the response-time promise the studio makes on
                     every other page was missing from the one that asks for the brief. --}}
                @if (!empty($cta['copy']))
                    <p class="cta-strip__copy" data-anim="rise">{{ $cta['copy'] }}</p>
                @endif
            </div>
            <div class="cta-strip__row" data-anim="rise">
                <a class="btn btn--red" href="#quote" data-quote-trigger data-quote-prefill="{{ $cta['prefill'] ?? $service->title }}" data-magnetic data-cursor="START">Start a brief <span class="arr"></span></a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
