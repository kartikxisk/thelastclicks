<x-layouts.app
    title="About TheLastClicks — Cinematic Film & Photography Studio"
    description="A photography and film production studio at the intersection of cinema, brand and craft. Five years, 547 productions and 26 cities across India and counting."
    :canonical="url('/about')"
>
  <x-slot name="head">
    <x-json-ld :data="[
        '@type' => 'AboutPage',
        'name' => 'About TheLastClicks',
        'url' => url('/about'),
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'TheLastClicks',
            'url' => url('/'),
            'description' => 'A photography and film production studio working across brand, corporate, automotive and wedding film in India.',
        ],
    ]" />
    <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => url('/about')],
    ]]" />
  </x-slot>

  <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1800&q=80')">
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>About</span></div>
    <h1 data-split>A studio of <em>cinema,</em><br>brand &amp; craft.</h1>
  </section>

  {{-- Our story — mirrors the homepage "discipline" block, image kept on the right --}}
  <section class="section disc" data-screen-label="02 Our story">
    <x-container>
      <div class="disc__grid">
        <div class="disc__lead">
          <span class="section__eyebrow">Our story</span>
          <h2 class="section__title" data-split>Built on the <em>discipline</em> of premium brands.</h2>
          <div class="disc__copy reveal" data-delay="1">
            <p>Brands choose us because we deliver trust, not just footage. Every shoot — wedding, commercial, or corporate — is run with the same discipline: show up prepared, protect the brief, deliver work that holds up under scrutiny.</p>
            <p>That discipline is why our client list spans far beyond weddings and product launches — we've delivered for the nation's most demanding institutions, including the <strong>Indian Navy, Indian Army, and BSF</strong>, alongside <strong>Fortune 500 brands</strong> and automotive houses.</p>
            <p>We don't chase &ldquo;good enough.&rdquo; Every project is a chance to be better than the last one — sharper frames, tighter edits, stronger stories.</p>
          </div>
          <p class="disc__kicker reveal" data-delay="2">Not a vendor — a long-term partner that scales with your story.</p>

          <div class="disc__stats">
            <div class="disc__stat reveal">
              <div class="disc__num"><span data-count="5">0</span><em>+</em></div>
              <span class="disc__lab">Years of experience</span>
            </div>
            <div class="disc__stat reveal" data-delay="1">
              <div class="disc__num"><span data-count="26">0</span><em>+</em></div>
              <span class="disc__lab">Cities covered</span>
            </div>
            <div class="disc__stat reveal" data-delay="2">
              <div class="disc__num"><span data-count="547">0</span><em>+</em></div>
              <span class="disc__lab">Productions</span>
            </div>
          </div>
        </div>
        <div class="about-img clip-reveal"><img src="https://images.unsplash.com/photo-1554048612-b6a482bc67e5?w=1200&q=85" alt="Photographer at work" decoding="async"></div>
      </div>
    </x-container>
  </section>

  <section class="section">
    <x-container>
      <div class="services__head">
        <div><span class="section__eyebrow">Principles</span><h2 class="section__title" data-split>How we <em>operate</em></h2></div>
        <div class="section__lead reveal">
          <p>We start by listening — understanding the brand, the brief, and the gap between what exists and what's possible. Then we build the story first, frame by frame, before a single camera rolls.</p>
          <p style="margin-top:14px">Four non-negotiables that shape every brief we accept.</p>
        </div>
      </div>
      <div class="proc">
        <div class="proc__step reveal"><div class="proc__num">01<span>BRIEF</span></div><h3>Story before spectacle</h3><p>We listen first — understanding your brand and where craft can push it further — then build the story before we shoot a single frame.</p></div>
        <div class="proc__step reveal" data-delay="1"><div class="proc__num">02<span>CRAFT</span></div><h3>Brand-grade post</h3><p>In-house grading and finishing — never outsourced.</p></div>
        <div class="proc__step reveal" data-delay="2"><div class="proc__num">03<span>SCALE</span></div><h3>Crews that flex</h3><p>One operator or thirty — same standard, same lead.</p></div>
        <div class="proc__step reveal" data-delay="3"><div class="proc__num">04<span>TRUST</span></div><h3>Compliance by default</h3><p>Premium &amp; regulated brand guidelines understood deeply.</p></div>
      </div>
    </x-container>
  </section>

  <!-- TIMELINE -->
  <section class="section" data-screen-label="03 Timeline">
    <x-container>
      <div class="timeline-x">
        <div class="timeline-x__sticky reveal">
          <span class="label">The journey</span>
          <h3>Five years, <em>one obsession.</em></h3>
          <p>One borrowed lens to a 60-person studio across 26 cities — earned by saying no to briefs that didn't fit.</p>
        </div>
        <div class="timeline-x__rail">
          <div class="timeline-x__item reveal">
            <div class="timeline-x__year">2018</div>
            <div class="timeline-x__title">Started with <em>one camera.</em></div>
            <div class="timeline-x__desc">Founded in Noida as a 2-person wedding-film crew — first gig a 100-guest hometown engagement, and we still know the family.</div>
          </div>
          <div class="timeline-x__item reveal">
            <div class="timeline-x__year">2020</div>
            <div class="timeline-x__title">First brand film.</div>
            <div class="timeline-x__desc">Lockdown forced a pivot — within 90 days, our first corporate brand film: a regional FMCG launch still in their library.</div>
          </div>
          <div class="timeline-x__item reveal">
            <div class="timeline-x__year">2022</div>
            <div class="timeline-x__title">In-house <em>post pipeline.</em></div>
            <div class="timeline-x__desc">Stopped outsourcing grade — built a full DaVinci pipeline with ACES color management. Quality jumped overnight.</div>
          </div>
          <div class="timeline-x__item reveal">
            <div class="timeline-x__year">2024</div>
            <div class="timeline-x__title">Premium beverage partner.</div>
            <div class="timeline-x__desc">First Fortune-500 partner — working with regulated brands reshaped how we approach every brief.</div>
          </div>
          <div class="timeline-x__item reveal">
            <div class="timeline-x__year">2026</div>
            <div class="timeline-x__title"><em>547+</em> productions in.</div>
            <div class="timeline-x__desc">Crew of 60+ across cities, booked into 2026 — same standard, scaled.</div>
          </div>
        </div>
      </div>
    </x-container>
  </section>

  <!-- CITIES PULSE MAP -->
  <section class="cities" data-screen-label="06 Cities">
    <x-container>
      <div class="cities__grid">
      <div class="cities__aside">
        <div class="cities__head">
          <span class="section__eyebrow" data-scramble>Where we shoot</span>
          <h2 class="section__title" data-split>26 cities, <em>one team.</em></h2>
          <p class="section__lead reveal">A live trace of where our crews have worked — every red pulse a shoot, a screening, a brand we've shipped for.</p>
        </div>
      </div>
      <div class="cities__map reveal">
      <x-india-outline />
      <span class="cities__pin" style="left:32.1%;top:29.2%"><span class="label">Delhi</span></span>
      <span class="cities__pin" style="left:18.2%;top:62.1%"><span class="label">Mumbai</span></span>
      <span class="cities__pin" style="left:33.4%;top:83.1%"><span class="label">Bengaluru</span></span>
      <span class="cities__pin" style="left:36.3%;top:67.9%"><span class="label">Hyderabad</span></span>
      <span class="cities__pin" style="left:42%;top:82.7%"><span class="label">Chennai</span></span>
      <span class="cities__pin" style="left:68%;top:50.1%"><span class="label">Kolkata</span></span>
      <span class="cities__pin" style="left:21.4%;top:64%"><span class="label">Pune</span></span>
      <span class="cities__pin" style="left:17.2%;top:48.5%"><span class="label">Ahmedabad</span></span>
      <span class="cities__pin" style="left:32.7%;top:29.5%"><span class="label">Noida</span></span>
      <span class="cities__pin" style="left:27.6%;top:35.1%"><span class="label">Jaipur</span></span>
      <span class="cities__pin" style="left:44.2%;top:35.3%"><span class="label">Lucknow</span></span>
      <span class="cities__pin" style="left:22.2%;top:75.1%"><span class="label">Goa</span></span>
      <span class="cities__pin" style="left:20.9%;top:43.1%"><span class="label">Udaipur</span></span>
      <span class="cities__pin" style="left:30.8%;top:21.9%"><span class="label">Chandigarh</span></span>
      <span class="cities__pin" style="left:29.1%;top:93.6%"><span class="label">Kochi</span></span>
      <span class="cities__pin" style="left:27.8%;top:49.5%"><span class="label">Indore</span></span>
      <span class="cities__pin" style="left:18.1%;top:54.9%"><span class="label">Surat</span></span>
      <span class="cities__pin" style="left:38.2%;top:54.9%"><span class="label">Nagpur</span></span>
      <span class="cities__pin" style="left:31.4%;top:89.8%"><span class="label">Coimbatore</span></span>
      <span class="cities__pin" style="left:18.7%;top:37.4%"><span class="label">Jodhpur</span></span>
      <span class="cities__pin" style="left:51.5%;top:66.9%"><span class="label">Visakhapatnam</span></span>
      <span class="cities__pin" style="left:59.8%;top:57.9%"><span class="label">Bhubaneswar</span></span>
      <span class="cities__pin" style="left:57.6%;top:39.6%"><span class="label">Patna</span></span>
      <span class="cities__pin" style="left:78.9%;top:37.8%"><span class="label">Guwahati</span></span>
      <span class="cities__pin" style="left:34.8%;top:23.4%"><span class="label">Dehradun</span></span>
      <span class="cities__pin" style="left:24.6%;top:18.8%"><span class="label">Amritsar</span></span>
      </div>
      </div>
    </x-container>
  </section>

  <section class="cta-strip">
    <x-container>
      <h2 class="cta-strip__title" data-split>Bring us a <em>brief.</em></h2>
      <div class="cta-strip__row reveal" data-delay="2">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Product launches to multi-day brand films — tell us what you're building.</p>
        <a class="btn btn--red" href="{{ url('/contact') }}" data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
      </div>
    </x-container>
  </section>
</x-layouts.app>
