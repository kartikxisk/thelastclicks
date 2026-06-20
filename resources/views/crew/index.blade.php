<x-layouts.app
    title="Talent — TheLastClicks"
    description="The crew behind the camera. Specialists, not generalists. Photographers, cinematographers, editors, producers."
    :canonical="url('/crew')"
>
    <section class="page-header" data-screen-label="01 Header">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Talent</span></div>
        <h1 data-split>The crew <em>behind</em><br>the camera.</h1>
        <dl class="page-header__meta">
            <div><dt>Core team</dt><dd>18 in-house</dd></div>
            <div><dt>Extended roster</dt><dd>60+ across India</dd></div>
            <div><dt>Disciplines</dt><dd>Photo · Film · Post</dd></div>
            <div><dt>Open roles</dt><dd>3 — view below</dd></div>
        </dl>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="services__head">
                <div><span class="section__eyebrow">The roster</span><h2 class="section__title" data-split>Specialists, <em>not generalists.</em></h2></div>
                <p class="section__lead reveal">Each project is led by a director who's been on the brief since day one — and finished by craftspeople who care about the last 5%.</p>
            </div>
            <div class="talent-grid">
                @foreach ($members as $m)
                    <a data-tilt="5" class="talent-card spotlight reveal" href="{{ url('/crew/'.$m->slug) }}"@if ($loop->index % 3) data-delay="{{ $loop->index % 3 }}"@endif>
                        <div class="talent-card__img"><img src="{{ $m->getFirstMediaUrl('headshot') ?: $m->photo_url }}" alt="" loading="lazy" decoding="async"></div>
                        <div class="talent-card__body"><h3>{{ $m->name }}</h3><div class="role">{{ $m->role }}</div>
                            <div class="talent-card__skills">@foreach (array_slice($m->skills ?? [], 0, 3) as $skill)<span class="tag">{{ $skill }}</span>@endforeach</div></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" style="border-top:1px solid var(--line)">
        <div class="wrap">
            <div class="services__head">
                <div><span class="section__eyebrow">Open positions</span><h2 class="section__title" data-split>Join the <em>roster.</em></h2></div>
                <p class="section__lead reveal">We hire for craft, taste and care. If that's you — we'd love to hear from you.</p>
            </div>
            <div class="services__list">
                <a class="svc reveal" href="{{ url('/contact') }}"><span class="svc__num">JOB-01</span><h3 class="svc__title">Senior <em>Editor</em></h3><p class="svc__desc">DaVinci Resolve / Premiere · 4+ yrs · brand &amp; long-form storytelling.</p><div class="svc__tags">Full-time · Bhopal</div><span class="svc__arr">→</span></a>
                <a class="svc reveal" href="{{ url('/contact') }}"><span class="svc__num">JOB-02</span><h3 class="svc__title">Producer</h3><p class="svc__desc">Multi-day productions · client-facing · scaling crews.</p><div class="svc__tags">Full-time · Mumbai</div><span class="svc__arr">→</span></a>
                <a class="svc reveal" href="{{ url('/contact') }}"><span class="svc__num">JOB-03</span><h3 class="svc__title">Cinematographer</h3><p class="svc__desc">Cinema cameras · brand and event experience.</p><div class="svc__tags">Roster · Pan-India</div><span class="svc__arr">→</span></a>
            </div>
        </div>
    </section>


    {{-- CREW ROLODEX --}}
    <section class="crew-roll" data-screen-label="04 Crew Rolodex">
        <div class="crew-roll__head">
            <div>
                <span class="section__eyebrow" data-scramble>Behind the lens</span>
                <h2>Roster <em>in motion.</em></h2>
            </div>
            <p>Sixty-plus craftspeople, directors, editors and producers. This is who shows up.</p>
        </div>
        <div class="crew-roll__track">
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Aarav Khanna</h3><span>Founder · DOP</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Maya Iyer</h3><span>Creative Director</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Rohan Bose</h3><span>Lead Editor · Colorist</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Nisha Rao</h3><span>Producer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Devansh Patel</h3><span>2nd Unit DOP</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Anaya Singh</h3><span>Photographer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Ravi Menon</h3><span>Sound Designer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1544723795-3fb6469f5b39?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Priya Shah</h3><span>Gaffer</span></div></a>
            {{-- duplicate for seamless loop --}}
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Aarav Khanna</h3><span>Founder · DOP</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Maya Iyer</h3><span>Creative Director</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Rohan Bose</h3><span>Lead Editor · Colorist</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Nisha Rao</h3><span>Producer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Devansh Patel</h3><span>2nd Unit DOP</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Anaya Singh</h3><span>Photographer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Ravi Menon</h3><span>Sound Designer</span></div></a>
            <a class="crew-roll__card" href="#"><img src="https://images.unsplash.com/photo-1544723795-3fb6469f5b39?w=600&q=85" alt="" loading="lazy" decoding="async"><div class="crew-roll__body"><h3>Priya Shah</h3><span>Gaffer</span></div></a>
        </div>
    </section>

    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Pitch us <em>yourself.</em></h2>
            <div class="cta-strip__row reveal"><p style="max-width:42ch;color:var(--paper-dim);font-size:17px">No open role that fits? Send a reel anyway. We hire by craft.</p>
            <a class="btn btn--red" href="{{ url('/contact') }}" data-magnetic data-cursor="APPLY">Send your reel <span class="arr"></span></a></div>
        </div>
    </section>
</x-layouts.app>
