@props(['items', 'lightboxLabel' => 'Work'])

{{-- Work globe. Tiles are laid out as a plain centred grid here; three.js
     re-positions the same DOM nodes onto a rotating sphere once the section
     scrolls into view. Everything below therefore has to stand on its own —
     without JS, or with reduced motion on, this markup IS the final layout. --}}
{{-- Tile shape is admin-configurable (Site Settings → Portfolio display).
     Emitted as a custom property so one value drives both the CSS box and the
     JS sizing, which reads the rendered box back. --}}
<div class="wglobe reveal" data-work-globe
     style="--wglobe-ratio: {{ \App\Models\SiteSetting::workTileRatio() }}">
    {{-- tabindex/role mirror the industries coverflow: the stage is the thing
         arrow keys act on, so it has to be reachable without tabbing through
         every tile first. --}}
    <div class="wglobe__stage"
         data-wglobe-stage
         tabindex="0"
         role="group"
         aria-roledescription="carousel"
         aria-label="Selected work — drag or use the arrow keys to rotate">
        @foreach ($items as $item)
            @php
                $cover = $item->coverUrl();
                $payload = $item->mediaPayload();
            @endphp
            @if ($cover)
                {{-- The cell is what three transforms; the button keeps its own
                     transform free for hover, so the two never overwrite each other. --}}
                <div class="wglobe__cell" data-wglobe-cell>
                    <button type="button"
                            class="wglobe__tile"
                            @if ($payload)
                                data-work-tile
                                data-work-media='@json($payload)'
                            @endif
                            aria-label="View {{ $item->title }}">
                        {{-- Decorative: the tile is labelled by its aria-label. --}}
                        <img src="{{ $cover }}" alt="" loading="lazy" decoding="async">
                        <span class="wglobe__label">{{ $item->title }}</span>
                    </button>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Only meaningful once the sphere exists, so JS unhides it. The rotation
         runs indefinitely, so WCAG 2.2.2 needs a real stop control — pausing on
         hover does nothing for keyboard or touch. --}}
    <div class="wglobe__controls" data-wglobe-controls hidden>
        <button type="button" class="wglobe__toggle" data-wglobe-toggle>Pause rotation</button>
    </div>
</div>

<x-work-lightbox :label="$lightboxLabel" />
