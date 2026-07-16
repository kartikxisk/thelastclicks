<x-layouts.app
    title="TheLastClicks — Cinematic photography & film production"
    description="Cinematic photography, brand films and post for premium teams."
    :canonical="url('/')"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'     => 'Organization',
            'name'      => 'TheLastClicks',
            'url'       => url('/'),
            'logo'      => asset('apple-touch-icon.svg'),
            'email'     => \App\Models\SiteSetting::get('contact_email'),
            'telephone' => \App\Models\SiteSetting::get('contact_phone'),
        ]" />
    </x-slot>

    {{-- Hero --}}
    <x-hero />

    {{-- Marquee --}}
    <x-marquee />

    <!-- FILM STRIP CAROUSEL -->
    <section class="strip" data-screen-label="02 Reel">
        <div class="strip__head">
            <div>
                <span class="section__eyebrow" data-scramble>Frame · 01 of 06</span>
                <h2 class="strip__title" data-split>The reel, <em>scrolled.</em></h2>
            </div>
            <div class="strip__ctrl">
                <span class="strip__time" data-strip-time>00:00 / 00:36</span>
                <div class="strip__btns">
                    <button class="strip__btn is-muted" data-strip-sound aria-label="Unmute reel" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M11 5L6.5 9H3v6h3.5L11 19V5z"/>
                            <path class="snd-on" d="M15.5 9a4.5 4.5 0 0 1 0 6M18 6.5a8 8 0 0 1 0 11"/>
                            <path class="snd-off" d="M16 9.5l5 5M21 9.5l-5 5"/>
                        </svg>
                    </button>
                    <button class="strip__btn" data-strip-prev aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18L9 12L15 6"/></svg></button>
                    <button class="strip__btn" data-strip-next aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 6L15 12L9 18"/></svg></button>
                </div>
            </div>
        </div>
        <div class="strip__rail" data-strip>
            <div class="strip__perf strip__perf--top" aria-hidden="true"></div>
            <div class="strip__perf strip__perf--bot" aria-hidden="true"></div>
            <div class="strip__track" data-strip-track>
                @php
                    $stripCards = [
                        ['slug' => 'ins-navy', 'video' => 'ins-navy-blackdog', 'tag' => '001 · Defence · 2026', 'title' => 'Indian <em>Navy.</em>', 'meta' => 'Official event film'],
                        ['slug' => 'salesforce-blr', 'video' => 'salesforce-blr', 'tag' => '002 · Corporate · 2026', 'title' => 'Salesforce · <em>Bengaluru.</em>', 'meta' => 'Multi-cam recap film'],
                        ['slug' => 'rahul-dravid-teaser', 'video' => 'rahul-dravid-teaser', 'tag' => '003 · Campaign · 2026', 'title' => 'Rahul Dravid · <em>teaser.</em>', 'meta' => 'Brand campaign film'],
                        ['slug' => 'range-rover', 'video' => 'range-rover', 'tag' => '004 · Automotive · 2026', 'title' => 'Range <em>Rover.</em>', 'meta' => 'Platform-first reel'],
                        ['slug' => 'black-label', 'video' => 'black-label', 'tag' => '005 · Brands · 2026', 'title' => 'Black <em>Label.</em>', 'meta' => 'Regulated-category reel'],
                        ['slug' => 'pramod-pooja-prewedding', 'video' => 'prewedding-pramod-pooja', 'tag' => '006 · Wedding · 2026', 'title' => 'Pramod &amp; <em>Pooja.</em>', 'meta' => 'Pre-wedding film'],
                    ];
                @endphp
                @foreach ($stripCards as $i => $card)
                    <a class="strip__card {{ $i === 0 ? 'is-on' : '' }}" data-i="{{ $i }}" href="{{ url('/portfolio/'.$card['slug']) }}" data-cursor="WATCH">
                        <span class="strip__tag">{{ $card['tag'] }}</span>
                        <video data-strip-video src="{{ asset('videos/'.$card['video'].'.mp4') }}"
                               poster="{{ asset('videos/posters/'.$card['video'].'.jpg') }}"
                               muted loop playsinline preload="none"></video>
                        <span class="strip__play">Watch full film</span>
                        <div class="strip__body">
                            <h3>{!! $card['title'] !!}</h3>
                            <span>{{ $card['meta'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="strip__progress">
            <button class="strip__dot is-on" data-strip-jump="0"></button>
            <button class="strip__dot" data-strip-jump="1"></button>
            <button class="strip__dot" data-strip-jump="2"></button>
            <button class="strip__dot" data-strip-jump="3"></button>
            <button class="strip__dot" data-strip-jump="4"></button>
            <button class="strip__dot" data-strip-jump="5"></button>
        </div>
    </section>

    <!-- CLIENTS -->
    <section class="section" data-screen-label="02 Clients">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">Our Clients</span>
                    <h2 class="section__title" data-split>Who we <em>work with</em></h2>
                </div>
                <p class="section__lead reveal">Brands choose us because we deliver trust, not just footage — from Fortune 500 companies and automotive houses to the nation's most demanding institutions, including the Indian Navy, Indian Army, and BSF.</p>
            </div>
            <div class="clients">
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Corporate &amp; Enterprise</span>
                </a>
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1556155092-490a1ba16284?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Brands &amp; Agencies</span>
                </a>
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Automobile &amp; Luxury</span>
                </a>
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Lifestyle &amp; Beverage</span>
                </a>
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Weddings &amp; Celebrations</span>
                </a>
                <a href="{{ url('/industries') }}" class="client" data-cursor="EXPLORE">
                    <img src="https://images.unsplash.com/photo-1587474260584-136574528ed5?w=800&q=80" alt="" loading="lazy" decoding="async">
                    <span class="label">Government &amp; Defence</span>
                </a>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section class="section services" id="services" data-screen-label="03 Services">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Our Services</span>
                    <h2 class="section__title" data-split>What <em>we do</em></h2>
                </div>
                <p class="section__lead reveal">Photography and film, finished in-house. Post-production is where we win — post-only briefs welcome.</p>
            </div>
            <div class="services__list">
                @foreach ($services as $service)
                    <a class="svc reveal" href="{{ url('/services/'.$service->slug) }}" data-preview="{{ $service->getFirstMediaUrl('hero') ?: $service->hero_url }}" data-cursor="EXPLORE">
                        <span class="svc__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="svc__title">{{ $service->title }}</h3>
                        <p class="svc__desc">{{ $service->hero_copy }}</p>
                        @if (!empty($service->proof['sectors'] ?? ''))
                            <div class="svc__tags">{{ $service->proof['sectors'] }}</div>
                        @endif
                        <span class="svc__arr"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 19L19 5M19 5H8M19 5V16"/></svg></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="section" style="padding-top:0">
        <div class="wrap">
            <div class="stats">
                <div class="reveal">
                    <div class="stat__num"><span data-scramble-count="64" data-decimals="0">0</span><em>+</em></div>
                    <div class="stat__lab">Crew Members</div>
                </div>
                <div class="reveal" data-delay="1">
                    <div class="stat__num"><span data-scramble-count="186" data-decimals="0">0</span><em>+</em></div>
                    <div class="stat__lab">Events / Year</div>
                </div>
                <div class="reveal" data-delay="2">
                    <div class="stat__num"><span data-scramble-count="26" data-decimals="0">0</span><em>+</em></div>
                    <div class="stat__lab">Cities Covered</div>
                </div>
                <div class="reveal" data-delay="3">
                    <div class="stat__num"><span data-scramble-count="42" data-decimals="0">0</span><em>h</em></div>
                    <div class="stat__lab">Avg. Turnaround</div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    @if ($testimonials->isNotEmpty())
    <section class="car" data-carousel data-screen-label="06 Testimonials">
        <div class="wrap" style="margin-bottom:32px">
            <span class="section__eyebrow" data-scramble>Client Stories</span>
            <h2 class="section__title" data-split>What our <em>clients say</em></h2>
        </div>
        <div class="car__viewport">
            @foreach ($testimonials as $t)
                <div class="car__slide {{ $loop->first ? 'is-on' : '' }}">
                    <div class="car__quote">"{{ $t->quote }}"</div>
                    <div class="who">
                        <span class="av">{{ collect(explode(' ', $t->client_name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                        <span>{{ $t->client_name }}{{ $t->role_company ? ' · '.$t->role_company : '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="car__nav wrap">
            <div class="car__dots">
                @foreach ($testimonials as $t)
                    <button class="car__dot {{ $loop->first ? 'is-on' : '' }}" data-cursor="{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}"></button>
                @endforeach
            </div>
            <div class="car__btns">
                <button class="car__prev" data-cursor="PREV"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18L9 12L15 6"/></svg></button>
                <button class="car__next" data-cursor="NEXT"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 6L15 12L9 18"/></svg></button>
            </div>
        </div>
    </section>
    @endif

    <!-- WHY US -->
    <section class="section" data-screen-label="07 Why us">
        <div class="wrap about-grid">
            <div>
                <span class="section__eyebrow">Why The Last Clicks</span>
                <h2 class="section__title" data-split>Built for <em>partnership.</em></h2>
                <p class="section__lead reveal">Not a vendor — a long-term partner that scales with your story.</p>
                <div class="reveal" data-delay="2" style="margin-top:32px">
                    <a class="btn btn--red" href="{{ url('/about') }}" data-magnetic data-cursor="ABOUT US">
                        About the studio <span class="arr"></span>
                    </a>
                </div>
            </div>
            <ol class="why-list reveal" data-delay="1">
                <li>Post-production in-house — the edit is our edge</li>
                <li>One team from brief to final master</li>
                <li>Same standard across photo and film</li>
                <li>Trusted by Fortune 500 brands, the Indian Navy, Indian Army &amp; BSF</li>
            </ol>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section" data-screen-label="08 FAQ">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">FAQs</span>
                    <h2 class="section__title" data-split>Frequently asked <em>questions</em></h2>
                </div>
                <p class="section__lead reveal">Still have questions? We're just a message away. Reach out anytime.</p>
            </div>
            <div class="acc" data-acc>
                <div class="acc__item is-open">
                    <button class="acc__head"><h3>Do you handle large-scale corporate events?</h3><span class="acc__plus"></span></button>
                    <div class="acc__body"><div class="acc__body-inner">Yes, we regularly work on conferences, brand launches, and multi-day productions — with crews that scale from a single shooter to a full unit.</div></div>
                </div>
                <div class="acc__item">
                    <button class="acc__head"><h3>Do you collaborate with brands and agencies?</h3><span class="acc__plus"></span></button>
                    <div class="acc__body"><div class="acc__body-inner">Yes — we work with internal teams and agencies to align visuals with campaign objectives and brand guidelines.</div></div>
                </div>
                <div class="acc__item">
                    <button class="acc__head"><h3>How involved are you in post-production?</h3><span class="acc__plus"></span></button>
                    <div class="acc__body"><div class="acc__body-inner">Deeply. Color, edit, sound and finishing are all handled in-house — so every deliverable meets the same standard.</div></div>
                </div>
                <div class="acc__item">
                    <button class="acc__head"><h3>Do you travel for projects?</h3><span class="acc__plus"></span></button>
                    <div class="acc__body"><div class="acc__body-inner">Yes — we support events and productions across cities and destinations based on project requirements.</div></div>
                </div>
                <div class="acc__item">
                    <button class="acc__head"><h3>How quickly do you deliver final files?</h3><span class="acc__plus"></span></button>
                    <div class="acc__body"><div class="acc__body-inner">48 hours for highlights, 2–3 weeks for full edits — faster for time-sensitive launches.</div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-strip" data-screen-label="09 CTA">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Let's create<br>something <em>impactful.</em></h2>
            <div class="cta-strip__row reveal" data-delay="2">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Planning an event, campaign, or production? Bring us the brief — we'll bring the craft.</p>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    <a class="btn btn--red" href="{{ url('/contact') }}" data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
                    <a class="btn btn--ghost" href="{{ url('/portfolio') }}" data-cursor="VIEW">View our work <span class="arr"></span></a>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Film-strip cards: play the preview on hover/focus, pause on leave.
    // Sound button toggles muted state for every preview in the strip.
    (function () {
      const videos = [...document.querySelectorAll('.strip__card video[data-strip-video]')];

      document.querySelectorAll('.strip__card').forEach((card) => {
        const v = card.querySelector('video[data-strip-video]');
        if (!v) return;
        const play = () => { v.play().catch(() => {}); };
        const stop = () => { v.pause(); };
        card.addEventListener('mouseenter', play);
        card.addEventListener('mouseleave', stop);
        card.addEventListener('focus', play);
        card.addEventListener('blur', stop);
      });

      const soundBtn = document.querySelector('[data-strip-sound]');
      if (soundBtn) {
        soundBtn.addEventListener('click', () => {
          const unmuting = soundBtn.classList.contains('is-muted');
          videos.forEach((v) => { v.muted = !unmuting; });
          soundBtn.classList.toggle('is-muted', !unmuting);
          soundBtn.setAttribute('aria-pressed', unmuting ? 'true' : 'false');
          soundBtn.setAttribute('aria-label', unmuting ? 'Mute reel' : 'Unmute reel');
        });
      }
    })();
    </script>

</x-layouts.app>
