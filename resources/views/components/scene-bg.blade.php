@props(['type' => 'grid'])

{{-- Ambient scene backdrop. Purely decorative, so it's aria-hidden and never a
     tab stop. Every layer is CSS-animated rather than JS-driven; the scene
     observer only flips a class to park the animations when the scene is off
     screen, so a page of these costs nothing while you're elsewhere.
     Types map to the three crafts: `edit` (timeline), `camera` (lens/light),
     `photo` (aperture/bokeh). --}}
<div class="scenebg scenebg--{{ $type }}" data-scene-bg aria-hidden="true">

    @if ($type === 'edit')
        {{-- Editing timeline: track lanes, clip blocks, keyframes, a playhead
             sweeping the scene, and a waveform that breathes. --}}
        <div class="scenebg__tracks">
            @for ($lane = 0; $lane < 4; $lane++)
                <div class="scenebg__lane" style="--lane:{{ $lane }}">
                    @foreach ([[4, 18], [26, 14], [44, 22], [70, 12], [86, 10]] as $i => [$x, $w])
                        <span class="scenebg__clip" style="--x:{{ $x }}%;--w:{{ $w }}%;--i:{{ $i }}"></span>
                    @endforeach
                </div>
            @endfor
        </div>
        <div class="scenebg__keys">
            @foreach ([12, 31, 49, 63, 81] as $i => $x)
                <span class="scenebg__key" style="--x:{{ $x }}%;--i:{{ $i }}"></span>
            @endforeach
        </div>
        <div class="scenebg__wave">
            @for ($i = 0; $i < 48; $i++)
                <span style="--i:{{ $i }}"></span>
            @endfor
        </div>
        <div class="scenebg__playhead"></div>

    @elseif ($type === 'camera')
        {{-- Lens and light: a slow sweep across the frame, framing guides that
             breathe, and drifting lens flare. --}}
        <div class="scenebg__sweep"></div>
        <svg class="scenebg__guides" viewBox="0 0 160 90" preserveAspectRatio="none" focusable="false">
            <path d="M6 6 H26 M6 6 V22"/><path d="M154 6 H134 M154 6 V22"/>
            <path d="M6 84 H26 M6 84 V68"/><path d="M154 84 H134 M154 84 V68"/>
            <path class="scenebg__thirds" d="M53.3 6 V84 M106.6 6 V84 M6 32 H154 M6 58 H154"/>
        </svg>
        <div class="scenebg__focus"><i></i><i></i><i></i></div>
        <div class="scenebg__flare"></div>

    @elseif ($type === 'photo')
        {{-- Aperture blades opening and closing, bokeh drifting behind. --}}
        <svg class="scenebg__aperture" viewBox="-60 -60 120 120" focusable="false">
            @for ($b = 0; $b < 6; $b++)
                <path d="M0 -46 L40 23 L-40 23 Z" style="--b:{{ $b }};transform:rotate({{ $b * 60 }}deg)"/>
            @endfor
        </svg>
        <div class="scenebg__bokeh">
            @foreach ([[12, 22, 46, 0], [78, 16, 30, 3], [30, 74, 62, 6], [88, 62, 38, 2], [58, 34, 24, 8], [44, 88, 52, 5], [68, 80, 28, 9]] as [$x, $y, $s, $d])
                <span style="--x:{{ $x }}%;--y:{{ $y }}%;--s:{{ $s }}px;--d:{{ $d }}s"></span>
            @endforeach
        </div>

    @else
        {{-- Neutral: a slow drifting measurement grid for scenes with no craft
             of their own, so the whole site still shares one visual language. --}}
        <div class="scenebg__grid"></div>
    @endif

    {{-- Film grain sits over every variant — it's the through-line that ties the
         three crafts to one studio. --}}
    <div class="scenebg__grain"></div>
</div>
