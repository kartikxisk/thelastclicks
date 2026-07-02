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
                    <button class="strip__btn" data-strip-prev aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18L9 12L15 6"/></svg></button>
                    <button class="strip__btn" data-strip-next aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 6L15 12L9 18"/></svg></button>
                </div>
            </div>
        </div>
        <div class="strip__rail" data-strip>
            <div class="strip__perf strip__perf--top" aria-hidden="true"></div>
            <div class="strip__perf strip__perf--bot" aria-hidden="true"></div>
            <div class="strip__track" data-strip-track>
                <article class="strip__card is-on" data-i="0">
                    <span class="strip__tag">001 · Brand film · 2026</span>
                    <img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1600&q=88" alt="" decoding="async">
                    <div class="strip__body">
                        <h3>Atlas, in <em>motion.</em></h3>
                        <span>Noida · 90s hero · 14 cuts</span>
                    </div>
                </article>
                <article class="strip__card" data-i="1">
                    <span class="strip__tag">002 · Wedding · 2026</span>
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=88" alt="" decoding="async">
                    <div class="strip__body">
                        <h3>Udaipur · <em>S &amp; R.</em></h3>
                        <span>Lake palace · 4 events</span>
                    </div>
                </article>
                <article class="strip__card" data-i="2">
                    <span class="strip__tag">003 · Auto · 2025</span>
                    <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1600&q=88" alt="" loading="lazy" decoding="async">
                    <div class="strip__body">
                        <h3>Aurelia GT <em>reveal.</em></h3>
                        <span>Phantom Flex · 1000fps</span>
                    </div>
                </article>
                <article class="strip__card" data-i="3">
                    <span class="strip__tag">004 · Lifestyle · 2026</span>
                    <img src="https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1600&q=88" alt="" loading="lazy" decoding="async">
                    <div class="strip__body">
                        <h3>Premium <em>beverage.</em></h3>
                        <span>National launch · Goa</span>
                    </div>
                </article>
                <article class="strip__card" data-i="4">
                    <span class="strip__tag">005 · Corporate · 2025</span>
                    <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600&q=88" alt="" loading="lazy" decoding="async">
                    <div class="strip__body">
                        <h3>Annual <em>Conference.</em></h3>
                        <span>40 sessions · same-day</span>
                    </div>
                </article>
                <article class="strip__card" data-i="5">
                    <span class="strip__tag">006 · Editorial · 2025</span>
                    <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1600&q=88" alt="" loading="lazy" decoding="async">
                    <div class="strip__body">
                        <h3>Indé <em>Magazine.</em></h3>
                        <span>8-page autumn spread</span>
                    </div>
                </article>
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
                <p class="section__lead reveal">Brands and teams who demand clarity, consistency, and craft — across corporate, lifestyle, automotive, and beverage.</p>
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

    <!-- PORTFOLIO -->
    <section class="section portfolio" data-screen-label="04 Portfolio">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow" data-scramble>Selected Projects</span>
                    <h2 class="section__title" data-split>Selected <em>work</em></h2>
                </div>
                <p class="section__lead reveal">A curated showcase across events, brands, and productions. Hover to preview.</p>
            </div>
            <div class="portfolio__list">
                @foreach ($portfolio as $item)
                    <a href="{{ url('/portfolio/'.$item->slug) }}" data-preview="{{ $item->getFirstMediaUrl('cover') ?: $item->cover_url }}" data-cursor="VIEW">
                        <span class="portfolio__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="portfolio__title">{{ $item->title }}</span>
                        <span class="portfolio__cat">{{ $item->service?->title ?? 'Film' }}</span>
                        <span class="portfolio__year">{{ $item->year }}</span>
                    </a>
                @endforeach
            </div>
            <div style="margin-top:48px;display:flex;justify-content:center">
                <a class="btn btn--ghost" href="{{ url('/portfolio') }}" data-magnetic data-cursor="ALL WORK">
                    View full portfolio <span class="arr"></span>
                </a>
            </div>
        </div>
    </section>

    <!-- BELIEFS -->
    <section class="section beliefs" data-screen-label="05 Beliefs">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">What we believe</span>
                </div>
                <p class="section__lead reveal">Non-negotiable. They show up in every frame.</p>
            </div>
            <ol class="beliefs__list">
                <li class="belief spotlight reveal">
                    <span class="belief__num">01</span>
                    <h3 class="belief__title">We don't pitch in <em>PowerPoint.</em></h3>
                    <p class="belief__note">A treatment is a film in miniature. If we can't show it, we don't deserve to make it.</p>
                </li>
                <li class="belief spotlight reveal" data-delay="1">
                    <span class="belief__num">02</span>
                    <h3 class="belief__title">Every frame <em>earns its place.</em></h3>
                    <p class="belief__note">If a shot isn't doing a job — emotional, narrative, structural — it gets cut. Length is not craft.</p>
                </li>
                <li class="belief spotlight reveal" data-delay="2">
                    <span class="belief__num">03</span>
                    <h3 class="belief__title">No one <em>outsources</em> our color.</h3>
                    <p class="belief__note">Grade is authorship. The same hand that lit it grades it. Always.</p>
                </li>
                <li class="belief spotlight reveal" data-delay="3">
                    <span class="belief__num">04</span>
                    <h3 class="belief__title">Story <em>before</em> spectacle.</h3>
                    <p class="belief__note">Drones, crash zooms and grade tricks are tools — never the brief. Pretty without purpose is noise.</p>
                </li>
                <li class="belief spotlight reveal" data-delay="4">
                    <span class="belief__num">05</span>
                    <h3 class="belief__title">We shoot for <em>the cut.</em></h3>
                    <p class="belief__note">Every setup is planned with the edit in mind. Coverage is a budget — we spend it on purpose.</p>
                </li>
                <li class="belief spotlight reveal" data-delay="5">
                    <span class="belief__num">06</span>
                    <h3 class="belief__title">The brief is a <em>thesis,</em> not a checklist.</h3>
                    <p class="belief__note">We interrogate it, push back when needed, and protect the idea from death-by-revision.</p>
                </li>
            </ol>
        </div>
    </section>

    <!-- PROCESS -->
    <section class="section sproc" data-screen-label="05 Process">
        <div class="wrap">
            <div class="services__head">
                <div>
                    <span class="section__eyebrow">Our Process</span>
                    <h2 class="section__title" data-split>How <em>we work</em></h2>
                </div>
                <p class="section__lead reveal">A clear process means fewer surprises — and visuals that do their job.</p>
            </div>
            <div class="sproc__stage" data-sproc>
                <div class="sproc__sticky">
                    <div class="sproc__counter">
                        <span class="sproc__now">01</span><span class="sproc__total"> / 04</span>
                    </div>
                    <div class="sproc__panels">
                        <article class="sproc__panel is-on" data-panel="0">
                            <span class="sproc__phase">Phase one · Brief</span>
                            <h3>Understanding the <em>brief.</em></h3>
                            <svg class="sproc__glyph" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <circle class="g" cx="32" cy="32" r="18" pathLength="1"/>
                                <path class="g" d="M32 6v8M32 50v8M6 32h8M50 32h8" pathLength="1" style="--d:.5s"/>
                                <circle class="f" cx="32" cy="32" r="3.5" fill="var(--red)" stroke="none"/>
                            </svg>
                            <ul class="sproc__keys">
                                <li>Discovery call</li>
                                <li>Creative thesis</li>
                                <li>KPIs locked</li>
                            </ul>
                        </article>
                        <article class="sproc__panel" data-panel="1">
                            <span class="sproc__phase">Phase two · Plan</span>
                            <h3>Pre-production <em>planning.</em></h3>
                            <svg class="sproc__glyph" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect class="g" x="6" y="20" width="14" height="24" pathLength="1"/>
                                <rect class="g" x="25" y="20" width="14" height="24" pathLength="1" style="--d:.35s"/>
                                <rect class="g" x="44" y="20" width="14" height="24" pathLength="1" style="--d:.55s"/>
                                <path class="f" d="M30 28.5l5.5 3.5-5.5 3.5z" fill="var(--red)" stroke="none"/>
                            </svg>
                            <ul class="sproc__keys">
                                <li>Treatment &amp; shot list</li>
                                <li>Casting &amp; locations</li>
                                <li>Crew &amp; kit locked</li>
                            </ul>
                        </article>
                        <article class="sproc__panel" data-panel="2">
                            <span class="sproc__phase">Phase three · Shoot</span>
                            <h3>On-ground <em>execution.</em></h3>
                            <svg class="sproc__glyph" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path class="g" d="M10 22V12a2 2 0 0 1 2-2h10" pathLength="1"/>
                                <path class="g" d="M42 10h10a2 2 0 0 1 2 2v10" pathLength="1" style="--d:.2s"/>
                                <path class="g" d="M54 42v10a2 2 0 0 1-2 2H42" pathLength="1" style="--d:.4s"/>
                                <path class="g" d="M22 54H12a2 2 0 0 1-2-2V42" pathLength="1" style="--d:.6s"/>
                                <circle class="f pulse" cx="32" cy="32" r="4" fill="var(--red)" stroke="none"/>
                            </svg>
                            <ul class="sproc__keys">
                                <li>Full crew, cinema kit</li>
                                <li>On-set look dev</li>
                                <li>Daily rushes</li>
                            </ul>
                        </article>
                        <article class="sproc__panel" data-panel="3">
                            <span class="sproc__phase">Phase four · Finish</span>
                            <h3>Post-production &amp; <em>delivery.</em></h3>
                            <svg class="sproc__glyph" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path class="g" d="M12 27v10" pathLength="1"/>
                                <path class="g" d="M22 20v24" pathLength="1" style="--d:.15s"/>
                                <path class="g" d="M32 13v38" pathLength="1" style="--d:.3s" stroke="var(--red)"/>
                                <path class="g" d="M42 22v20" pathLength="1" style="--d:.45s"/>
                                <path class="g" d="M52 28v8" pathLength="1" style="--d:.6s"/>
                            </svg>
                            <ul class="sproc__keys">
                                <li>Edit &amp; ACES grade</li>
                                <li>Sound design</li>
                                <li>Platform masters</li>
                            </ul>
                        </article>
                    </div>
                    <div class="sproc__progress" aria-hidden="true">
                        <div class="sproc__progress-fill"></div>
                    </div>
                    <div class="sproc__dots">
                        <button class="sproc__dot is-on" data-jump="0" aria-label="Go to phase 1"></button>
                        <button class="sproc__dot" data-jump="1" aria-label="Go to phase 2"></button>
                        <button class="sproc__dot" data-jump="2" aria-label="Go to phase 3"></button>
                        <button class="sproc__dot" data-jump="3" aria-label="Go to phase 4"></button>
                    </div>
                </div>
                <div class="sproc__media">
                    <figure class="sproc__scene" data-scene="0">
                        <span class="sproc__tag"><span class="dot"></span>01 · Brief</span>
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1600&q=85" alt="Discovery call" loading="lazy" decoding="async">
                    </figure>
                    <figure class="sproc__scene" data-scene="1">
                        <span class="sproc__tag"><span class="dot"></span>02 · Plan</span>
                        <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=1600&q=85" alt="Planning a shoot" loading="lazy" decoding="async">
                    </figure>
                    <figure class="sproc__scene" data-scene="2">
                        <span class="sproc__tag"><span class="dot"></span>03 · Shoot</span>
                        <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1600&q=85" alt="On set" loading="lazy" decoding="async">
                    </figure>
                    <figure class="sproc__scene" data-scene="3">
                        <span class="sproc__tag"><span class="dot"></span>04 · Finish</span>
                        <img src="https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1600&q=85" alt="Post production" loading="lazy" decoding="async">
                    </figure>
                </div>
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
    <section class="car" data-carousel data-screen-label="06 Testimonials">
        <div class="wrap" style="margin-bottom:32px">
            <span class="section__eyebrow" data-scramble>Client Stories</span>
            <h2 class="section__title" data-split>What our <em>clients say</em></h2>
        </div>
        <div class="car__viewport">
            <div class="car__slide is-on">
                <div class="car__quote">"The Last Clicks delivered exceptional coverage for our annual conference. Their professionalism and attention to detail made all the difference."</div>
                <div class="who">
                    <span class="av">PM</span>
                    <span>Priya Mehta · Marketing Head, Fortune 500 FMCG</span>
                </div>
            </div>
            <div class="car__slide">
                <div class="car__quote">"From pre-production to final delivery, their team was seamless. The brand films exceeded our expectations."</div>
                <div class="who">
                    <span class="av">AK</span>
                    <span>Arjun Kapoor · Creative Director, Leading Ad Agency</span>
                </div>
            </div>
            <div class="car__slide">
                <div class="car__quote">"Incredible wedding coverage. Every moment was captured beautifully — cinematic, emotional, and authentic."</div>
                <div class="who">
                    <span class="av">SR</span>
                    <span>Sneha &amp; Rohit · Destination Wedding, Udaipur</span>
                </div>
            </div>
            <div class="car__slide">
                <div class="car__quote">"Consistent quality every single time. They truly understand the luxury and automotive space."</div>
                <div class="who">
                    <span class="av">VS</span>
                    <span>Vikram Singh · Brand Manager, Premium Automobile</span>
                </div>
            </div>
        </div>
        <div class="car__nav wrap">
            <div class="car__dots">
                <button class="car__dot is-on" data-cursor="01"></button>
                <button class="car__dot" data-cursor="02"></button>
                <button class="car__dot" data-cursor="03"></button>
                <button class="car__dot" data-cursor="04"></button>
            </div>
            <div class="car__btns">
                <button class="car__prev" data-cursor="PREV"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18L9 12L15 6"/></svg></button>
                <button class="car__next" data-cursor="NEXT"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 6L15 12L9 18"/></svg></button>
            </div>
        </div>
    </section>

    <!-- WHY US -->
    <section class="section" data-screen-label="07 Why us">
        <div class="wrap about-grid">
            <div>
                <span class="section__eyebrow">Why The Last Clicks</span>
                <h2 class="section__title" data-split>Built for <em>partnership.</em></h2>
                <p class="section__lead reveal">Long-term partnerships built on reliability and craft — not one-off transactions.</p>
                <div class="reveal" data-delay="2" style="margin-top:32px">
                    <a class="btn btn--red" href="{{ url('/about') }}" data-magnetic data-cursor="ABOUT US">
                        About the studio <span class="arr"></span>
                    </a>
                </div>
            </div>
            <ol class="why-list reveal" data-delay="1">
                <li>Industry-focused approach, not generic services</li>
                <li>Experience across professional and brand-led environments</li>
                <li>End-to-end execution from shoot to final delivery</li>
                <li>Consistent quality across photography and video</li>
                <li>Trusted by brands, corporates, and event teams</li>
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

</x-layouts.app>
