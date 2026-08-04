@props(['items', 'lightboxLabel' => 'Work'])

@php
    // Only works that resolve a cover are renderable; the preview video is
    // optional and falls back to the still.
    $renderable = $items->filter(fn ($w) => filled($w->coverUrl()))->values();

    // Tile shape comes from the admin (Site Settings → Portfolio display), which
    // is already allowlisted to a fixed set of ratios. Emitted twice: the ratio
    // itself for `aspect-ratio`, and its decimal so the tile's width can be
    // derived from one strip height — that keeps the strip the same height
    // whichever ratio is chosen, instead of a 9:16 pick making it twice as tall.
    $ratio = \App\Models\SiteSetting::workTileRatio();
    [$rw, $rh] = array_map('trim', explode('/', $ratio));
    $decimal = ((float) $rh) > 0 ? round(((float) $rw) / ((float) $rh), 4) : 0.5625;

    // One flat payload for the whole strip, plus the offset at which each work
    // starts in it. Per-tile payloads held only that one work's media — usually
    // a single video — so the lightbox's next/prev wrapped straight back to the
    // item already on screen and read as broken. Walking the strip is what a
    // viewer expects from a strip.
    $strip = [];
    $offsets = [];

    foreach ($renderable as $i => $work) {
        $offsets[$i] = count($strip);
        $media = $work->mediaPayload();

        if (empty($media)) {
            // No media rows: fall back to whatever the tile itself is showing,
            // so a work can never open an empty stage.
            $preview = $work->previewVideoUrl();
            $media = [[
                'type' => $preview ? 'video' : 'image',
                'url' => $preview ?: $work->coverUrl(),
                'caption' => $work->title,
            ]];
        }

        foreach ($media as $entry) {
            $strip[] = $entry;
        }
    }

    // The set is rendered twice so the track can translate -50% and loop
    // seamlessly. The second pass is the same works flagged as duplicates:
    // hidden from assistive tech and out of the tab order, so no work is
    // announced or focused twice.
    $passes = [false, true];
@endphp

{{-- Work marquee. One track holding the set twice, translated -50% — the same
     idiom as the existing .marquee in core.css, so the loop needs no JS to
     measure anything.

     Hover and :focus-within pause the scroll in pure CSS. The stop control is
     visually hidden until keyboard focus reaches it, so it stops both the
     scroll and the video for a keyboard user without putting a button on the
     page.

     Previews autoplay muted inline; clicking a tile hands off to the existing
     lightbox. The payload for the whole strip lives here on the root rather than
     on each tile — twelve copies of the same JSON in the HTML is pure weight —
     and each tile carries only its offset into it. --}}
<div class="wmq" data-work-marquee
     data-work-media='@json($strip)'
     style="--wmq-ratio: {{ $ratio }}; --wmq-ar: {{ $decimal }}">
    <div class="wmq__viewport">
        <ul class="wmq__track" data-wmq-track>
            @foreach ($passes as $isDuplicate)
                @foreach ($renderable as $i => $item)
                    @php
                        $video = $item->previewVideoUrl();
                    @endphp
                    <li class="wmq__item" @if ($isDuplicate) aria-hidden="true" @endif>
                        <{{ $isDuplicate ? 'span' : 'button' }}
                            class="wmq__tile"
                            @if ($isDuplicate)
                                tabindex="-1"
                            @else
                                type="button"
                                data-work-tile
                                data-work-index="{{ $offsets[$i] }}"
                                aria-label="Play {{ $item->title }}"
                            @endif>
                            <span class="wmq__frame">
                                @if ($video)
                                    {{-- Decorative preview: silent, looping, and says
                                         nothing the tile's label doesn't already say.
                                         preload="none" — JS starts these only once the
                                         marquee is actually on screen. --}}
                                    <video data-wmq-video
                                           src="{{ $video }}"
                                           poster="{{ $item->coverUrl() }}"
                                           muted loop playsinline preload="none"
                                           aria-hidden="true" tabindex="-1"></video>
                                @else
                                    <img src="{{ $item->coverUrl() }}" alt="" loading="lazy" decoding="async">
                                @endif
                            </span>
                        </{{ $isDuplicate ? 'span' : 'button' }}>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>

    {{-- Off-screen until it receives keyboard focus, like .skip-link. Hover
         already stops the strip for a mouse, but WCAG 2.2.2 still wants a real
         mechanism to stop scrolling content and auto-playing video, and hover
         gives a keyboard user nothing. --}}
    <button type="button" class="wmq__toggle" data-wmq-toggle aria-pressed="false">Pause</button>
</div>

<x-work-lightbox :label="$lightboxLabel" />
