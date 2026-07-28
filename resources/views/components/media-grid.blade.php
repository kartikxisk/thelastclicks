{{-- linkAttrs: optional closure returning an attribute string for linked tiles,
     so a caller can turn them into quote-modal triggers without this component
     having to know what a quote is. --}}
@props(['items', 'meta' => null, 'metaClass' => 'work-tile__meta', 'lightboxLabel' => 'Media', 'layout' => 'masonry', 'link' => null, 'linkAttrs' => null])

@php
    // masonry (default) · grid = equal 16:9 cards · bento = mixed-size feed
    $layoutClass = match ($layout) {
        'grid' => 'work-grid--fixed',
        'bento' => 'work-grid--bento',
        default => '',
    };
@endphp

<div class="work-grid {{ $layoutClass }}" data-work-grid data-stagger>
    @foreach ($items as $item)
        @php
            $cover = $item->coverUrl();
            $href = $link ? $link($item) : null;
            // Link tiles open a detail page (anchor); otherwise fall back to the
            // media lightbox (button), or an inert div when there's nothing to open.
            $payload = $href ? null : $item->mediaPayload();
            $metaText = $meta
                ? $meta($item)
                : collect([$item->client ?? null, $item->year ?? null])->filter()->implode(' · ');
            $tag = $href ? 'a' : ($payload ? 'button' : 'div');

            // Only Work carries these; the component is also handed Industries and
            // MediaItems, so every access is guarded.
            $preview = method_exists($item, 'previewVideoUrl') ? $item->previewVideoUrl() : null;
            $crafts = method_exists($item, 'craftSlugs') ? $item->craftSlugs() : [];
            $category = $item->category ?? null;
        @endphp
        <{{ $tag }}
            class="work-tile scene-stop"
            data-anim="scale-frame"
            data-lift
            data-sheen
            data-zoom
            @if ($category) data-cat="{{ $category }}" @endif
            @if ($crafts) data-crafts="{{ implode(' ', $crafts) }}" @endif
            @if ($href)
                href="{{ $href }}"
                aria-label="{{ $item->title }}"
                @if ($linkAttrs) {!! $linkAttrs($item) !!} @endif
            @elseif ($payload)
                type="button"
                data-work-tile
                data-work-media='@json($payload)'
                aria-label="View {{ $item->title }}"
            @endif
        >
            @if ($cover)
                {{-- Decorative: the tile is labelled by its visible title (and aria-label
                     on interactive tiles), so a matching alt would just double-announce. --}}
                <img src="{{ $cover }}" alt="" loading="lazy" decoding="async">
            @endif
            @if ($preview)
                {{-- Muted loop layered over the still, revealed on hover/focus. preload="none"
                     keeps a grid of these from costing a dozen video fetches on page load;
                     the JS starts the fetch on first hover. --}}
                <video class="work-tile__preview" src="{{ $preview }}" muted loop playsinline
                       preload="none" tabindex="-1" aria-hidden="true"></video>
            @endif
            <span class="work-tile__scrim" aria-hidden="true"></span>
            <span class="work-tile__body">
                <span class="work-tile__title">{{ $item->title }}</span>
                @if ($metaText)
                    <span class="{{ $metaClass }}">{{ $metaText }}</span>
                @endif
            </span>
        </{{ $tag }}>
    @endforeach
</div>

<x-work-lightbox :label="$lightboxLabel" />
