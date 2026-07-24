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
        @endphp
        <{{ $tag }}
            class="work-tile reveal"
            data-delay="{{ $loop->index % 4 }}"
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
                <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
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
