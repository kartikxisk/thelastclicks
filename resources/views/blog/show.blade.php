{{-- Long editorial headlines truncate in the SERP, so a post may carry a short
     seo_title; the full title still runs as the on-page H1. --}}
<x-layouts.app
    :title="$post->seo_title ?: \App\Support\Brand::title($post->title)"
    :description="$post->seo_description ?: $post->excerpt"
    :canonical="url('/blog/'.$post->slug)"
    :ogImage="$post->getFirstMediaUrl('cover') ?: null"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'            => 'Article',
            'headline'         => $post->title,
            'description'      => $post->excerpt,
            'image'            => $post->getFirstMediaUrl('cover') ?: null,
            'datePublished'    => optional($post->published_at)->toIso8601String(),
            'dateModified'     => optional($post->updated_at)->toIso8601String(),
            'author'           => ['@type' => 'Person', 'name' => $post->author?->name ?? 'TheLastClicks'],
            'publisher'        => ['@type' => 'Organization', 'name' => \App\Support\Brand::NAME, 'logo' => ['@type' => 'ImageObject', 'url' => \App\Models\SiteSetting::brandLogoUrl() ?: asset('apple-touch-icon.png')]],
            'mainEntityOfPage' => url('/blog/'.$post->slug),
        ]" />
        <x-json-ld :data="['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => url('/blog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => url('/blog/'.$post->slug)],
        ]]" />
<style>
  .art-hero { max-width: var(--maxw); margin-inline: auto; padding: 130px var(--pad-x) 0; }
  .art-hero__crumb { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); display: flex; gap: 10px; margin-bottom: 32px; }
  .art-hero__crumb a { color: var(--paper-dim); }
  .art-hero__crumb a:hover { color: var(--red); }
  .art-hero__cat { display: inline-flex; align-items: center; gap: 12px; padding: 8px 14px; background: var(--red); color: #fff; font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 28px; }
  .art-hero h1 { font-family: var(--f-display); font-weight: 600; font-size: clamp(40px, 6.5vw, 96px); letter-spacing: -0.04em; line-height: 0.98; max-width: 18ch; text-wrap: balance; }
  .art-hero h1 em { font-family: var(--f-display); font-style: italic; font-weight: 400; color: var(--red); }
  .art-meta { display: flex; gap: 24px; flex-wrap: wrap; padding: 32px 0; margin-top: 32px; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); }
  .art-meta span { display: inline-flex; align-items: center; gap: 8px; }
  .art-meta span::before { content: ''; width: 6px; height: 6px; background: var(--red); border-radius: 0; }
  .art-cover { aspect-ratio: 16/9; overflow: hidden; margin-top: 32px; }
  .art-cover img { width: 100%; height: 100%; object-fit: cover; }
  /* Reading column left-aligned to the hero's content edge (not centered), so the
     body lines up under the title. --art-inset = the container's left gutter. */
  :root { --art-inset: max(var(--pad-x), calc((100% - var(--maxw)) / 2 + var(--pad-x))); }
  /* Typography lives in pages.css (.art-body) and is shared with the industry
     pages. Only the article-specific layout and scale is overridden here. */
  .art-body { padding: 100px 0; max-width: 760px; margin-inline: var(--art-inset) var(--pad-x); font-size: 19px; }
  .art-body h2 { font-size: clamp(28px, 3.6vw, 40px); margin: 56px 0 22px; }
  .art-body blockquote { padding: 24px 0 24px 32px; max-width: 36ch; }
  .art-body img { aspect-ratio: 16/9; object-fit: cover; }
  .art-share { padding: 40px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; max-width: 760px; margin-inline: var(--art-inset) var(--pad-x); }
  .art-share__label { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); }
  .art-share__btns { display: flex; gap: 8px; }
  .art-share__btn { padding: 9px 14px; border: 1px solid var(--line); border-radius: 0; font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--paper-dim); background: transparent; cursor: pointer; transition: border-color 0.3s var(--ease-soft), color 0.3s var(--ease-soft); }
  .art-share__btn:hover { border-color: var(--red); color: var(--red); }
  .art-author { padding: 60px 0; display: flex; gap: 20px; align-items: center; max-width: 760px; margin-inline: var(--art-inset) var(--pad-x); }
  .art-author__avatar { width: 64px; height: 64px; border-radius: 0; background: var(--red); color: #fff; display: grid; place-items: center; font-family: var(--f-display); font-weight: 700; font-size: 22px; flex-shrink: 0; }
  .art-author__name { font-family: var(--f-display); font-weight: 500; font-size: 18px; }
  .art-author__role { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-top: 4px; }
  @media (max-width: 880px) {
    .art-hero { padding-top: 110px; }
    /* Horizontal offset comes from --art-inset margins now, so padding is block-only. */
    .art-body { padding: 56px 0; font-size: 16.5px; }
    .art-share, .art-author { padding: 40px 0; }
    .art-body blockquote { font-size: 22px; padding-left: 20px; }
  }
</style>
    </x-slot>

    {{-- HERO --}}
    <section class="art-hero" data-screen-label="01 Header">
        <div class="art-hero__crumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/blog') }}">Journal</a>
            <span>/</span>
            <span>{{ $post->title }}</span>
        </div>
        @if ($post->categories->isNotEmpty())
            <span class="art-hero__cat">{{ $post->categories->first()->name }}</span>
        @endif
        <h1 data-split>{{ $post->title }}</h1>
        <div class="art-meta">
            @if ($post->published_at)
                <span>{{ $post->published_at->format('d M Y') }}</span>
            @endif
            {{-- Only once a revision has actually happened — a day's grace, or
                 every post would say "Updated" on the day it was published and
                 the signal would mean nothing. The schema has carried
                 dateModified all along; this makes the same fact visible, which
                 is the form answer engines and readers actually weight. --}}
            @if ($post->published_at && $post->updated_at && $post->updated_at->gt($post->published_at->addDay()))
                <span>Updated {{ $post->updated_at->format('d M Y') }}</span>
            @endif
            <span>{{ max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200)) }} min read</span>
            <span>{{ $post->author?->name ?? 'TheLastClicks' }}</span>
        </div>
        @if ($cover = $post->getFirstMediaUrl('cover'))
            <div class="art-cover" data-anim="iris" data-zoom>
                <img src="{{ $cover }}" alt="{{ $post->title }}" decoding="async">
            </div>
        @endif
    </section>

    {{-- BODY --}}
    <article class="art-body">
        {!! $post->body !!}
    </article>

    {{-- SHARE --}}
    <section class="art-share">
        <span class="art-share__label">Share this piece</span>
        <div class="art-share__btns">
            <button type="button" class="art-share__btn" onclick="navigator.clipboard?.writeText(location.href); this.textContent='Copied ✓'">Copy link</button>
            <a class="art-share__btn" target="_blank" rel="noopener"
               href="https://twitter.com/intent/tweet?url={{ urlencode(url('/blog/'.$post->slug)) }}&text={{ urlencode($post->title) }}">Twitter</a>
            <a class="art-share__btn" target="_blank" rel="noopener"
               href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url('/blog/'.$post->slug)) }}">LinkedIn</a>
        </div>
    </section>

    {{-- AUTHOR --}}
    <section class="art-author">
        <div class="art-author__avatar">{{ strtoupper(substr($post->author?->name ?? 'TheLastClicks', 0, 2)) }}</div>
        <div>
            <div class="art-author__name">{{ $post->author?->name ?? 'TheLastClicks' }}</div>
            <div class="art-author__role">Editor at TheLastClicks</div>
        </div>
    </section>

    {{-- TAXONOMY --}}
    @if ($post->categories->isNotEmpty() || $post->tags->isNotEmpty())
        <footer class="post-meta" style="padding: 24px var(--pad-x); border-top: 1px solid var(--line); display: flex; gap: 8px; flex-wrap: wrap; max-width: 760px; margin: 0 auto;">
            @foreach ($post->categories as $c)
                <span class="cat" style="padding: 4px 12px; background: var(--red); color: #fff; font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase;">{{ $c->name }}</span>
            @endforeach
            @foreach ($post->tags as $t)
                <span class="tag" style="padding: 4px 12px; border: 1px solid var(--line); color: var(--paper-dim); font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase;">{{ $t->name }}</span>
            @endforeach
        </footer>
    @endif

    {{-- CTA STRIP --}}
    <section class="cta-strip">
        <x-scene-bg type="photo" />
        <x-container data-stagger>
            <h2 class="cta-strip__title" data-split data-anim="mask-up">Read how we work.<br>Or put it <em>to work.</em></h2>
            <div class="cta-strip__row" data-anim="rise">
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
