<x-layouts.app
    title="About TheLastClicks — Cinematic Film & Photography Studio"
    description="A photography and film production studio at the intersection of cinema, brand and craft. Five years, 1,000+ events and 20+ cities across India and counting."
    :canonical="url('/about')"
>
  <x-slot name="head">
    <x-json-ld :data="[
        '@type' => 'AboutPage',
        'name' => 'About TheLastClicks',
        'url' => url('/about'),
        'mainEntity' => [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => \App\Support\Brand::NAME,
            'alternateName' => \App\Support\Brand::ALTERNATE_NAMES,
            'url' => url('/'),
            'description' => 'A photography and film production studio working across brand, corporate, automotive and wedding film in India.',
        ],
    ]" />
    <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => url('/about')],
    ]]" />
  </x-slot>

  @php $pageHeader = \App\Models\SiteSetting::pageImage('about'); @endphp
  <section class="page-header page-header--media" data-screen-label="01 Header" @if ($pageHeader) style="--ph-bg:url('{{ $pageHeader }}')" @endif>
    <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>About</span></div>
    <h1 data-split>A studio of <em>cinema,</em><br>brand &amp; craft.</h1>
  </section>

  {{-- Our story — mirrors the homepage "discipline" block, image kept on the right --}}
  <section class="section disc" data-screen-label="02 Our story">
      <x-scene-bg type="camera" />
    <x-container>
      <div class="disc__grid" data-stagger>
        <div class="disc__lead" data-anim="curtain">
          <span class="section__eyebrow">Our story</span>
          <h2 class="section__title" data-split>Beyond the lens: <em>a promise of discipline.</em></h2>
          <div class="disc__copy">
            <p>We are built on the discipline of premium brands. We treat every shoot as a chance to outdo our last — showing up prepared and delivering work that holds up under the highest scrutiny. That unwavering standard is why our portfolio spans far beyond product launches, earning the trust of <strong>national institutions</strong> and <strong>global enterprises</strong> alike.</p>
          </div>

          <div class="disc__stats" data-stagger>
            <div class="disc__stat" data-anim="mask-up">
              <div class="disc__num"><span data-count="5">0</span><em>+</em></div>
              <span class="disc__lab">Years of experience</span>
            </div>
            <div class="disc__stat" data-anim="mask-up">
              <div class="disc__num"><span data-count="20">0</span><em>+</em></div>
              <span class="disc__lab">Cities covered across India</span>
            </div>
            <div class="disc__stat" data-anim="mask-up">
              <div class="disc__num"><span data-count="1000">0</span><em>+</em></div>
              <span class="disc__lab">Events &amp; activations over the last decade</span>
            </div>
          </div>
        </div>
        @php $aboutPhoto = \App\Models\SiteSetting::pageImage('about_body'); @endphp
        @if ($aboutPhoto)
          <div class="about-img" data-anim="iris" data-zoom><img src="{{ $aboutPhoto }}" alt="TheLastClicks studio at work" decoding="async"></div>
        @endif
      </div>
    </x-container>
  </section>

  <section class="section">
      <x-scene-bg type="edit" />
    <x-container>
      <div class="services__head">
        <div><span class="section__eyebrow">Principles</span><h2 class="section__title" data-split>How we <em>operate</em></h2></div>
      </div>
      <div class="proc" data-stagger>
        <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">01<span>FOCUS</span></div><h3>Narrative first</h3><p>Beautiful imagery is empty without a story. We dive deep into your vision to build the blueprint before the cameras roll.</p></div>
        <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">02<span>CRAFT</span></div><h3>Studio-grade finishing</h3><p>True cinematic quality is forged in post-production. Our in-house editing and colour grading ensure uncompromising visual fidelity.</p></div>
        <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">03<span>SCALE</span></div><h3>Agile production</h3><p>From intimate single-operator shoots to massive multi-camera sets, our aesthetic and operational discipline remain steadfast.</p></div>
        <div class="proc__step" data-anim="curtain" data-sheen><div class="proc__num">04<span>TRUST</span></div><h3>Absolute alignment</h3><p>We integrate seamlessly. Every deliverable is designed to be fully compliant and perfectly aligned with your established brand guidelines.</p></div>
      </div>
    </x-container>
  </section>

  <!-- TIMELINE -->
  <section class="section" data-screen-label="03 Timeline">
      <x-scene-bg type="edit" />
    <x-container>
      <div class="timeline-x">
        <div class="timeline-x__sticky" data-anim="slide-l">
          <span class="label">The journey</span>
          <h3>Five years, <em>one obsession.</em></h3>
          <p>From a single borrowed lens to a 60-person operation spanning 20+ cities. We built this scale not by taking every job, but by refusing to compromise on our standard.</p>
        </div>
        <div class="timeline-x__rail" data-stagger>
          <div class="timeline-x__item scene-stop" data-anim="slide-r">
            <div class="timeline-x__year">2018</div>
            <div class="timeline-x__title">Started with <em>one camera.</em></div>
            <div class="timeline-x__desc">Founded in Noida as a two-person crew. Our first project was an intimate hometown engagement; today, that same dedication to the client remains our foundation.</div>
          </div>
          <div class="timeline-x__item scene-stop" data-anim="slide-r">
            <div class="timeline-x__year">2020</div>
            <div class="timeline-x__title">The <em>corporate pivot.</em></div>
            <div class="timeline-x__desc">A global lockdown forced an evolution. Within 90 days, we delivered our first corporate brand film for a regional FMCG client — a campaign still in active use today.</div>
          </div>
          <div class="timeline-x__item scene-stop" data-anim="slide-r">
            <div class="timeline-x__year">2022</div>
            <div class="timeline-x__title">In-house <em>mastery.</em></div>
            <div class="timeline-x__desc">We stopped outsourcing our finish. By building a complete DaVinci pipeline with ACES colour management, we took absolute control of our cinematic quality.</div>
          </div>
          <div class="timeline-x__item scene-stop" data-anim="slide-r">
            <div class="timeline-x__year">2024</div>
            <div class="timeline-x__title">Enterprise <em>trust.</em></div>
            <div class="timeline-x__desc">Secured our first Fortune 500 partner. Delivering for heavily regulated, premium beverage brands permanently elevated our approach to production and compliance.</div>
          </div>
          <div class="timeline-x__item scene-stop" data-anim="slide-r">
            <div class="timeline-x__year">2026</div>
            <div class="timeline-x__title"><em>1,000+</em> deployments.</div>
            <div class="timeline-x__desc">A crew of 60+ operating nationwide. The footprint has scaled massively, but our rigorous operational standard remains exactly the same.</div>
          </div>
        </div>
      </div>
    </x-container>
  </section>

  <!-- CITIES PULSE MAP -->
  <section class="cities" data-screen-label="06 Cities">
      <x-scene-bg type="photo" />
    <x-container>
      <div class="cities__grid">
      <div class="cities__aside">
        <div class="cities__head">
          <span class="section__eyebrow" data-scramble>Our presence</span>
          <h2 class="section__title" data-split>20+ cities. 1,000+ productions. <em>One unbroken standard.</em></h2>
          <p class="section__lead" data-anim="rise">From metro hubs to remote locations — a live trace of our national footprint. Every red pulse represents a set we've run, a narrative we've captured, and a brand we've delivered for.</p>
        </div>
      </div>
      <div class="cities__map" data-anim="iris">
      <x-india-outline />
      <span class="cities__pin" style="left:32.1%;top:29.2%"><span class="label">Delhi</span></span>
      <span class="cities__pin" style="left:18.2%;top:62.1%"><span class="label">Mumbai</span></span>
      <span class="cities__pin" style="left:68%;top:50.1%"><span class="label">Kolkata</span></span>
      <span class="cities__pin" style="left:21.4%;top:64%"><span class="label">Pune</span></span>
      <span class="cities__pin" style="left:17.2%;top:48.5%"><span class="label">Ahmedabad</span></span>
      <span class="cities__pin" style="left:32.7%;top:29.5%"><span class="label">Noida</span></span>
      <span class="cities__pin" style="left:27.6%;top:35.1%"><span class="label">Jaipur</span></span>
      <span class="cities__pin" style="left:44.2%;top:35.3%"><span class="label">Lucknow</span></span>
      <span class="cities__pin" style="left:22.2%;top:75.1%"><span class="label">Goa</span></span>
      <span class="cities__pin" style="left:20.9%;top:43.1%"><span class="label">Udaipur</span></span>
      <span class="cities__pin" style="left:30.8%;top:21.9%"><span class="label">Chandigarh</span></span>
      <span class="cities__pin" style="left:27.8%;top:49.5%"><span class="label">Indore</span></span>
      <span class="cities__pin" style="left:18.1%;top:54.9%"><span class="label">Surat</span></span>
      <span class="cities__pin" style="left:38.2%;top:54.9%"><span class="label">Nagpur</span></span>
      <span class="cities__pin" style="left:18.7%;top:37.4%"><span class="label">Jodhpur</span></span>
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
        <x-scene-bg type="photo" />
    <x-container data-stagger>
      <h2 class="cta-strip__title" data-split data-anim="mask-up">Bring us a brief.<br>Or bring us <em>a problem.</em></h2>
      <div class="cta-strip__row" data-anim="rise">
        <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Product launches to multi-day brand films — tell us what you're building.</p>
        <a class="btn btn--red" href="{{ url('/contact') }}" data-quote-trigger data-magnetic data-cursor="START">Start a conversation <span class="arr"></span></a>
      </div>
    </x-container>
  </section>
</x-layouts.app>
