@props(['post'])

@php
    $category = $post->categories->first();
    $catName = $category?->name;
    // data-cat key MUST match the chip key in blog/index.blade.php (category slug).
    $catKey = $category?->slug ?? 'uncategorized';
    $cover = $post->getFirstMediaUrl('cover')
        ?: 'https://images.unsplash.com/photo-1606800052052-a08af7148866?w=1200&q=85';
    // Reading time: ~200 wpm over the post body. Mirrors design's "· N min read" span.
    $readMin = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
@endphp

<a class="post reveal" href="{{ url('/blog/' . $post->slug) }}" data-cat="{{ $catKey }}">
    <div class="post__img">
        <img src="{{ $cover }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
        @if ($catName)
            <span class="post__cat">{{ $catName }}</span>
        @endif
    </div>
    <div class="meta">
        <span>{{ $post->published_at?->format('d M Y') }}</span>
        <span>· {{ $readMin }} min read</span>
    </div>
    <h3>{{ $post->title }}</h3>
    @if ($post->excerpt)
        <p style="color:var(--paper-dim);font-size:14.5px;line-height:1.55">{{ $post->excerpt }}</p>
    @endif
</a>
