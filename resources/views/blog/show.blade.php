{{-- Long editorial headlines truncate in the SERP, so a post may carry a short
     seo_title; the full title still runs as the on-page H1. --}}
<x-layouts.app
    :title="$post->seo_title ?: $post->title.' — Journal — TheLastClicks'"
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
            'publisher'        => ['@type' => 'Organization', 'name' => 'TheLastClicks', 'logo' => ['@type' => 'ImageObject', 'url' => \App\Models\SiteSetting::brandLogoUrl() ?: asset('apple-touch-icon.png')]],
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
  .art-hero h1 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .art-meta { display: flex; gap: 24px; flex-wrap: wrap; padding: 32px 0; margin-top: 32px; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); }
  .art-meta span { display: inline-flex; align-items: center; gap: 8px; }
  .art-meta span::before { content: ''; width: 6px; height: 6px; background: var(--red); border-radius: 50%; }
  .art-cover { aspect-ratio: 16/9; overflow: hidden; margin-top: 32px; }
  .art-cover img { width: 100%; height: 100%; object-fit: cover; }
  .art-body { padding: 100px var(--pad-x); max-width: 760px; margin: 0 auto; font-size: 19px; line-height: 1.7; color: var(--paper); }
  .art-body p { margin-bottom: 22px; text-wrap: pretty; }
  .art-body h2 { font-family: var(--f-display); font-weight: 600; font-size: clamp(28px, 3.6vw, 40px); letter-spacing: -0.025em; margin: 56px 0 22px; text-wrap: balance; line-height: 1.1; }
  .art-body h2 em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  .art-body blockquote { font-family: 'Instrument Serif', serif; font-style: italic; font-size: 26px; line-height: 1.35; color: var(--paper); padding: 24px 0 24px 32px; border-left: 2px solid var(--red); margin: 36px 0; text-wrap: balance; max-width: 36ch; }
  .art-body img { width: 100%; margin: 32px 0; aspect-ratio: 16/9; object-fit: cover; }
  .art-body ul { padding-left: 22px; margin-bottom: 22px; }
  .art-body ul li { margin-bottom: 8px; color: var(--paper-dim); }
  .art-share { padding: 40px var(--pad-x); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; max-width: 760px; margin: 0 auto; }
  .art-share__label { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); }
  .art-share__btns { display: flex; gap: 8px; }
  .art-share__btn { padding: 9px 14px; border: 1px solid var(--line); border-radius: 100px; font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--paper-dim); background: transparent; cursor: pointer; transition: border-color 0.3s var(--ease-soft), color 0.3s var(--ease-soft); }
  .art-share__btn:hover { border-color: var(--red); color: var(--red); }
  .art-author { padding: 60px var(--pad-x); display: flex; gap: 20px; align-items: center; max-width: 760px; margin: 0 auto; }
  .art-author__avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--red); color: #fff; display: grid; place-items: center; font-family: var(--f-display); font-weight: 700; font-size: 22px; flex-shrink: 0; }
  .art-author__name { font-family: var(--f-display); font-weight: 500; font-size: 18px; }
  .art-author__role { font-family: var(--f-mono); font-size: 10.5px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--paper-dim); margin-top: 4px; }
  .art-next { max-width: var(--maxw); margin-inline: auto; padding: 80px var(--pad-x); border-top: 1px solid var(--line); display: grid; gap: 24px; text-align: center; }
  .art-next-label { font-family: var(--f-mono); font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--paper-dim); }
  .art-next a { font-family: var(--f-display); font-weight: 700; font-size: clamp(32px, 5vw, 64px); letter-spacing: -0.03em; }
  .art-next a em { font-family: 'Instrument Serif', serif; font-style: italic; font-weight: 400; color: var(--red); }
  @media (max-width: 880px) {
    .art-hero { padding-top: 110px; }
    .art-body { padding: 56px 20px; font-size: 16.5px; }
    .art-share, .art-author, .art-next { padding: 40px 20px; }
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
            <span>{{ max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200)) }} min read</span>
            <span>{{ $post->author?->name ?? 'TheLastClicks' }}</span>
        </div>
        @if ($cover = $post->getFirstMediaUrl('cover'))
            <div class="art-cover clip-reveal">
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

    {{-- NEXT POST --}}
    @php
        $nextPost = \App\Models\Post::published()->where('id', '>', $post->id)->orderBy('id')->first()
            ?? \App\Models\Post::published()->orderBy('id')->first();
    @endphp
    @if ($nextPost && $nextPost->id !== $post->id)
        <section class="art-next">
            <span class="art-next-label">Next in the journal</span>
            <a href="{{ url('/blog/'.$nextPost->slug) }}" data-cursor="READ">
                <span>{{ $nextPost->title }}</span> <em>→</em>
            </a>
        </section>
    @endif

    {{-- CTA STRIP --}}
    <section class="cta-strip">
        <x-container>
            <h2 class="cta-strip__title" data-split>Let's <em>create.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">
                    Brief us — treatment, timeline, and budget back within 4 working hours.
                </p>
                <a class="btn btn--red" href="#quote" data-quote-trigger data-magnetic data-cursor="START">
                    Start a brief <span class="arr"></span>
                </a>
            </div>
        </x-container>
    </section>

</x-layouts.app>
