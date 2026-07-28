@props(['items', 'lightboxLabel' => 'Work'])

{{-- Scattered collage. Layout is plain multi-column masonry; the scatter comes
     entirely from per-tile transforms, which cost no layout and let tiles
     overlap for free. Offsets cycle every 7 tiles against a 5-column grid, so
     the pattern never lines up into visible rows. --}}
<div class="wcol" data-work-collage>
    @foreach ($items as $item)
        @php
            $cover = $item->coverUrl();
            $payload = $item->mediaPayload();
        @endphp
        @if ($cover)
            <button type="button"
                    class="wcol__tile reveal"
                    data-delay="{{ $loop->index % 5 }}"
                    @if ($payload)
                        data-work-tile
                        data-work-media='@json($payload)'
                    @endif
                    aria-label="View {{ $item->title }}">
                {{-- Decorative: the tile is labelled by its aria-label. --}}
                <img src="{{ $cover }}" alt="" loading="lazy" decoding="async">
                <span class="wcol__label">{{ $item->title }}</span>
            </button>
        @endif
    @endforeach
</div>

<x-work-lightbox :label="$lightboxLabel" />
