@props(['item'])
@php $cover = $item->getFirstMediaUrl('cover') ?: $item->cover_url; @endphp
<a class="work work--4 reveal" href="{{ url('/portfolio/'.$item->slug) }}" data-cursor="VIEW">
    @if ($cover)
        <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy" decoding="async">
    @endif
    <div class="meta">
        <h3>{{ $item->title }}</h3>
        <span class="cat">{{ $item->service?->title ?? $item->year }}</span>
    </div>
</a>
