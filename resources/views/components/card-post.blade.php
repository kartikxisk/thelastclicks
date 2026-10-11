@props(['post'])

@php
    $category = $post->categories->first();
    $catName = $category?->name;
    // data-cat key MUST match the chip key in blog/index.blade.php (category slug).
    $catKey = $category?->slug ?? 'uncategorized';
    // EVERY category the post is filed under, not just the first. The chip
    // counts are built from all of them, so filtering on the first alone made
    // "Corporate 03" show one post: the other two carry Corporate as their
    // second category. The badge above still shows one name — a card has room
    // for one — but the filter now matches any of them.
    $catKeys = $post->categories->pluck('slug')->filter()->values()->all();
    $catKeys = $catKeys !== [] ? $catKeys : ['uncategorized'];
    // S3 cover only — no external placeholder. Empty renders a styled block below.
    $cover = $post->getFirstMediaUrl('cover');
    // Reading time: ~200 wpm over the post body. Mirrors design's "· N min read" span.
    $readMin = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
@endphp

<a class="post scene-stop" href="{{ url('/blog/' . $post->slug) }}" data-cat="{{ $catKey }}"
   data-cat-slugs="{{ implode(' ', $catKeys) }}"
   data-anim="mask-up" data-lift data-sheen>
    <div class="post__img" data-zoom>
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
        @else
            <div class="placeholder" style="position:absolute;inset:0">Journal</div>
        @endif
        @if ($catName)
            <span class="post__cat">{{ $catName }}</span>
        @endif
    </div>
    <div class="meta">
        <span>{{ $post->published_at?->format('d M Y') }}</span>
        <span>· {{ $readMin }} min read</span>
    </div>
    <h3>{{ $post->title }}</h3>
</a>
