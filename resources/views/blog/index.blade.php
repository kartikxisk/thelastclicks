<x-layouts.app
    title="Journal — TheLastClicks"
    description="Studio dispatches — film craft, behind-the-scenes, and editorial notes from TheLastClicks."
    :canonical="url('/blog')"
>

    {{-- 01 Page Header --}}
    <section class="page-header" data-screen-label="01 Header">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Journal</span></div>
        <h1 data-split>Notes from <em>set,</em><br>edit &amp; finish.</h1>
        <dl class="page-header__meta">
            <div><dt>Posts</dt><dd>{{ $posts->total() + ($featured ? 1 : 0) }}</dd></div>
            <div><dt>Categories</dt><dd>Craft · Brand · Process</dd></div>
            <div><dt>Updated</dt><dd>Monthly</dd></div>
            <div><dt>Subscribers</dt><dd>2.4k</dd></div>
        </dl>
    </section>

    {{-- 02 Journal — featured article, category chips, post grid --}}
    <section class="section" data-screen-label="02 Journal">
        <div class="wrap">

            {{-- Featured article spotlight (article.feat from design) --}}
            @if ($featured)
                <article class="feat reveal">
                    <div class="feat__media">
                        <span class="feat__tag">Editor's pick</span>
                        @php $coverUrl = $featured->getFirstMediaUrl('cover'); @endphp
                        <img
                            src="{{ $coverUrl ?: 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=1800&q=85' }}"
                            alt=""
                            decoding="async"
                        >
                    </div>
                    <div class="feat__body">
                        <span class="feat__eyebrow">{{ $featured->published_at->format('M j, Y') }} · Featured</span>
                        <h2 class="feat__title" data-split>{{ $featured->title }}</h2>
                        <p class="feat__excerpt">{{ $featured->excerpt }}</p>
                        <div class="feat__meta">
                            @if ($featured->author)
                                <span>{{ $featured->author->name }}</span>
                            @endif
                            <span>· Craft</span>
                        </div>
                        <div class="feat__cta">
                            <a class="btn btn--red" href="{{ url('/blog/' . $featured->slug) }}" data-magnetic data-cursor="READ">
                                Read the essay <span class="arr"></span>
                            </a>
                        </div>
                    </div>
                </article>
            @endif

            {{-- Category chips (dynamic — data-cat key is the category slug, matching x-card-post). --}}
            @php
                // Counts reflect posts shown on this page (the cards the filter can toggle).
                $catCounts = [];
                foreach ($posts as $p) {
                    foreach ($p->categories as $c) {
                        $catCounts[$c->slug] = ($catCounts[$c->slug] ?? 0) + 1;
                    }
                }
                $cats = \App\Models\Category::orderBy('name')->get();
            @endphp
            <div class="cats reveal" data-cats>
                <button class="cats__chip is-on" data-cat="all">All<span class="count">{{ str_pad((string) $posts->count(), 2, '0', STR_PAD_LEFT) }}</span></button>
                @foreach ($cats as $cat)
                    @php($count = $catCounts[$cat->slug] ?? 0)
                    @if ($count > 0)
                        <button class="cats__chip" data-cat="{{ $cat->slug }}">{{ $cat->name }}<span class="count">{{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</span></button>
                    @endif
                @endforeach
            </div>

            {{-- Posts grid --}}
            <div class="blog-grid">
                @foreach ($posts as $post)
                    <x-card-post :post="$post" />
                @endforeach
            </div>

            @if ($posts->hasPages())
                <div class="pagination">{{ $posts->links() }}</div>
            @endif

        </div>
    </section>

    {{-- 03 Newsletter subscribe card --}}
    {{-- TODO: wire newsletter form to a controller action / email service (e.g. Mailcoach, Mailchimp) before launch --}}
    <section class="news" data-screen-label="03 Newsletter">
        <div class="wrap">
            <div class="news__card reveal">
                <div>
                    <span class="section__eyebrow" data-scramble>The journal</span>
                    <h2 class="news__h" data-split>Stay close to the <em>set.</em></h2>
                    <p class="news__p">One craft note a month — what we shot, how we cut it, and the questions still keeping us up. No spam, no growth-hacks.</p>
                </div>
                <div>
                    {{-- Form posts to '#' placeholder — no backend wired yet --}}
                    <form class="news__form" action="#" method="POST">
                        @csrf
                        <input type="email" name="email" placeholder="you@studio.com" required>
                        <button class="btn btn--red" type="submit" data-magnetic>Subscribe <span class="arr"></span></button>
                    </form>
                    <div class="news__hint">
                        <span>2,400 subscribers</span>
                        <span>6 issues this year</span>
                        <span>Unsubscribe in one click</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Strip --}}
    <section class="cta-strip">
        <div class="wrap">
            <h2 class="cta-strip__title" data-split>Get the <em>journal.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">One craft note a month. No spam, no growth-hacks.</p>
                {{-- TODO: wire newsletter form to a controller action / email service before launch --}}
                <form class="row" action="#" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="you@studio.com" required style="background:transparent;border:1px solid var(--line);border-radius:100px;padding:14px 22px;color:var(--paper);font:inherit;min-width:280px">
                    <button class="btn btn--red" type="submit" data-magnetic>Subscribe <span class="arr"></span></button>
                </form>
            </div>
        </div>
    </section>

    {{-- Category filter — ported from design/blog.html. Matches chip data-cat to .post[data-cat] (category slug). --}}
    <script>
    (function () {
        const cats = document.querySelector('[data-cats]');
        if (!cats) return;
        cats.addEventListener('click', e => {
            const chip = e.target.closest('.cats__chip');
            if (!chip) return;
            cats.querySelectorAll('.cats__chip').forEach(c => c.classList.remove('is-on'));
            chip.classList.add('is-on');
            const cat = chip.dataset.cat;
            document.querySelectorAll('.blog-grid .post').forEach(p => {
                const pcat = p.dataset.cat || '';
                const show = cat === 'all' || pcat === cat;
                p.style.transition = 'opacity 0.4s, transform 0.4s';
                if (show) {
                    p.style.display = '';
                    requestAnimationFrame(() => { p.style.opacity = '1'; p.style.transform = 'translateY(0)'; });
                } else {
                    p.style.opacity = '0';
                    p.style.transform = 'translateY(12px)';
                    setTimeout(() => { p.style.display = 'none'; }, 380);
                }
            });
        });
    })();
    </script>

</x-layouts.app>
