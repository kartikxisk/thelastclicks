@props(['items', 'meta' => null, 'metaClass' => 'work-tile__meta', 'lightboxLabel' => 'Media', 'layout' => 'masonry', 'link' => null])

@php
    // masonry (default) · grid = equal 16:9 cards · bento = mixed-size feed
    $layoutClass = match ($layout) {
        'grid' => 'work-grid--fixed',
        'bento' => 'work-grid--bento',
        default => '',
    };
@endphp

<div class="work-grid {{ $layoutClass }}" data-work-grid>
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
            class="work-tile reveal"
            data-delay="{{ $loop->index % 4 }}"
            @if ($category) data-cat="{{ $category }}" @endif
            @if ($crafts) data-crafts="{{ implode(' ', $crafts) }}" @endif
            @if ($href)
                href="{{ $href }}"
                aria-label="{{ $item->title }}"
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

@once
<div class="wlb" data-work-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ $lightboxLabel }}">
    <button class="wlb__close" data-wlb-close aria-label="Close">&times;</button>
    <button class="wlb__nav wlb__nav--prev" data-wlb-prev aria-label="Previous">&#8249;</button>
    <div class="wlb__stage" data-wlb-stage></div>
    <button class="wlb__nav wlb__nav--next" data-wlb-next aria-label="Next">&#8250;</button>
    <p class="wlb__caption" data-wlb-caption></p>
</div>
@endonce
