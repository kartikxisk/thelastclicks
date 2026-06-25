<x-layouts.app
    :title="$member->name.' — Crew — TheLastClicks'"
    :description="$member->role"
    :canonical="url('/crew/'.$member->slug)"
>
    <x-slot name="head">
    <style>
  .cw-hero { max-width: var(--maxw); margin-inline: auto; padding: 130px var(--pad-x) 0; display: grid; grid-template-columns: 1fr 1.1fr; gap: 60px; align-items: end; }
  .cw-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); display: flex; gap: 10px; margin-bottom: 24px; }
  .cw-hero__crumb a { color: var(--paper-dim); }
  .cw-hero__crumb a:hover { color: var(--red); }
  .cw-portrait { aspect-ratio: 4/5; overflow: hidden; background: var(--ink-2); position: relative; }
  .cw-portrait img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.15) brightness(0.9); transform: scale(1.04); transition: filter 0.6s var(--ease-soft), transform 1s var(--ease-soft); }
  .cw-portrait:hover img { filter: none; transform: scale(1.08); }
  .cw-hero__body { padding-bottom: 24px; }
  .cw-role { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--red); display: inline-flex; align-items: center; gap: 10px; margin-bottom: 22px; }
  .cw-role::before { content: ''; width: 24px; height: 1px; background: var(--red); }
  .cw-name { font-family: var(--f-display); font-weight: 600; font-size: clamp(48px, 8vw, 120px); letter-spacing: -0.045em; line-height: 0.92; }
  .cw-name em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .cw-tagline { margin-top: 22px; font-family: 'Instrument Serif', serif; font-style: italic; font-size: clamp(22px, 2.8vw, 30px); line-height: 1.25; color: var(--paper); text-wrap: balance; max-width: 28ch; }
  .cw-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; padding: 36px 0; margin: 56px 0 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
  .cw-meta dt { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-bottom: 10px; }
  .cw-meta dd { font-family: var(--f-display); font-weight: 500; font-size: clamp(20px, 2.4vw, 28px); letter-spacing: -0.025em; line-height: 1.05; }
  .cw-meta dd em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .cw-body { padding: 100px var(--pad-x); display: grid; grid-template-columns: 260px 1fr; gap: 60px; max-width: var(--maxw); margin: 0 auto; }
  .cw-body h2 { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--red); padding-top: 6px; }
  .cw-body p { font-size: 18px; line-height: 1.6; color: var(--paper); text-wrap: pretty; }
  .cw-body p + p { margin-top: 18px; }
  .cw-skills { padding: 0 var(--pad-x) 100px; max-width: var(--maxw); margin: 0 auto; }
  .cw-skills__h { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--red); margin-bottom: 28px; }
  .cw-skills__grid { display: flex; flex-wrap: wrap; gap: 10px; }
  .cw-skills__chip { padding: 11px 18px; border: 1px solid var(--line); border-radius: 100px; font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--paper); transition: border-color 0.3s var(--ease-soft), color 0.3s var(--ease-soft); }
  .cw-skills__chip:hover { border-color: var(--red); color: var(--red); }
  .cw-cred { padding: 80px var(--pad-x); border-top: 1px solid var(--line); }
  .cw-cred__h { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--red); margin-bottom: 28px; }
  .cw-cred__list { display: grid; gap: 0; }
  .cw-cred__row { display: grid; grid-template-columns: 80px 1fr auto; gap: 28px; padding: 22px 0; border-bottom: 1px solid var(--line); align-items: baseline; transition: padding-left 0.4s var(--ease-soft); }
  .cw-cred__row:hover { padding-left: 12px; }
  .cw-cred__year { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.18em; color: var(--paper-dim); }
  .cw-cred__title { font-family: var(--f-display); font-weight: 500; font-size: clamp(18px, 2.4vw, 24px); letter-spacing: -0.02em; line-height: 1.1; }
  .cw-cred__title em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .cw-cred__role { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--paper-dim); }
  .cw-next { padding: 80px var(--pad-x); border-top: 1px solid var(--line); }
  .cw-next .wrap { display: grid; gap: 20px; text-align: center; }
  .cw-next a { font-family: var(--f-display); font-weight: 700; font-size: clamp(32px, 5.5vw, 72px); letter-spacing: -0.03em; }
  .cw-next a em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .cw-socials { padding: 80px var(--pad-x); border-top: 1px solid var(--line); }
  .cw-socials h3 { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--red); margin-bottom: 22px; }
  .cw-socials ul { display: flex; flex-wrap: wrap; gap: 12px; list-style: none; }
  .cw-socials a { font-family: var(--f-display); font-weight: 500; font-size: 18px; color: var(--paper); border-bottom: 1px solid var(--line); padding-bottom: 3px; transition: color 0.3s var(--ease-soft), border-color 0.3s var(--ease-soft); }
  .cw-socials a:hover { color: var(--red); border-color: var(--red); }
  @media (max-width: 880px) {
    .cw-hero { grid-template-columns: 1fr; padding-top: 110px; gap: 28px; }
    .cw-meta { grid-template-columns: 1fr 1fr; gap: 18px; }
    .cw-body { grid-template-columns: 1fr; gap: 18px; padding: 56px 20px; }
    .cw-skills { padding: 0 20px 56px; }
    .cw-cred { padding: 48px 20px; }
    .cw-cred__row { grid-template-columns: 60px 1fr; gap: 16px; }
    .cw-cred__role { grid-column: 2; }
  }
</style>
    </x-slot>

    {{-- Section 1: cw-hero --}}
    <section class="cw-hero" data-screen-label="01 Hero">
        <div class="cw-portrait clip-reveal">
            <img
                src="{{ $member->getFirstMediaUrl('headshot') ?: $member->photo_url }}"
                alt="{{ $member->name }}"
            >
        </div>
        <div class="cw-hero__body">
            <div class="cw-hero__crumb">
                <a href="{{ url('/') }}">Home</a>
                <span>/</span>
                <a href="{{ url('/crew') }}">Talent</a>
                <span>/</span>
                <span>{{ $member->name }}</span>
            </div>
            <span class="cw-role">{{ $member->role }}</span>
            @php
                $nameWords = preg_split('/\s+/', trim($member->name));
                $lastWord = array_pop($nameWords);
                $heroLead = implode(' ', $nameWords);
            @endphp
            <h1 class="cw-name" data-split>{{ $heroLead !== '' ? $heroLead.' ' : '' }}<em>{{ $lastWord }}.</em></h1>
            <p class="cw-tagline">{{ $member->tagline }}</p>
            <dl class="cw-meta">
                <div><dt>Joined</dt><dd>{{ $member->joined }}</dd></div>
                <div><dt>Discipline</dt><dd>{{ $member->discipline }}</dd></div>
                <div><dt>Based</dt><dd>{{ $member->city }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- Section 2: cw-body (story / bio) --}}
    <section class="cw-body">
        <h2>The story</h2>
        <div>
            @if ($member->bio)
                {!! $member->bio !!}
            @else
                <p>This crew member's story is coming soon.</p>
            @endif
        </div>
    </section>

    {{-- Section 3: cw-skills (tools & craft) --}}
    <section class="cw-skills">
        <div class="cw-skills__h">Tools &amp; craft</div>
        <div class="cw-skills__grid">
            @foreach ($member->skills ?? [] as $skill)
                <span class="cw-skills__chip">{{ $skill }}</span>
            @endforeach
        </div>
    </section>

    {{-- Section 4: cw-cred (selected credits) --}}
    <section class="cw-cred">
        <div class="wrap">
            <div class="cw-cred__h">Selected credits</div>
            <div class="cw-cred__list">
                @foreach ($member->credits ?? [] as [$year, $project, $role])
                    <div class="cw-cred__row">
                        <span class="cw-cred__year">{{ $year }}</span>
                        <span class="cw-cred__title">{{ $project }}</span>
                        <span class="cw-cred__role">{{ $role }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 5: cw-next (next roster member) --}}
    @php
        $nextMember = \App\Models\Crew::where('order', '>', $member->order)->orderBy('order')->first()
            ?? \App\Models\Crew::orderBy('order')->first();
    @endphp
    <section class="cw-next">
        <div class="wrap">
            <span class="kicker">Next on the roster</span>
            @if ($nextMember && $nextMember->id !== $member->id)
                <a href="{{ url('/crew/'.$nextMember->slug) }}" data-cursor="VIEW">
                    <span>{{ $nextMember->name }}</span> <em>→</em>
                </a>
            @else
                <a href="{{ url('/crew') }}" data-cursor="VIEW">
                    <span>Back to the roster</span> <em>→</em>
                </a>
            @endif
        </div>
    </section>

    {{-- Section 6: cta-strip --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Work with <em>this team.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">Bring us the brief — we'll come back with the right shape and the right hands within 4 working hours.</p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">Start a brief <span class="arr"></span></a>
            </div>
        </div>
    </section>

    {{-- Social links from $member->social_json --}}
    @if (! empty($member->social_json))
        <section class="cw-socials">
            <div class="wrap">
                <h3>Find me</h3>
                <ul>
                    @foreach ($member->social_json as $platform => $url)
                        <li><a href="{{ $url }}" rel="noopener" target="_blank">{{ ucfirst($platform) }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.app>
