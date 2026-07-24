<x-layouts.app
    title="Journal — Film Craft & Production Notes | TheLastClicks"
    description="Studio dispatches on film craft, behind-the-scenes process and editorial notes from the TheLastClicks production team. One new craft note every month."
    :canonical="url('/blog')"
>

    {{-- 01 Page Header --}}
    <section class="page-header page-header--media" data-screen-label="01 Header" style="--ph-bg:url('https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1800&q=80')">
        <div class="page-header__crumb"><a href="{{ url('/') }}">Home</a><span>/</span><span>Journal</span></div>
        <h1 data-split>Notes from <em>set,</em><br>edit &amp; finish.</h1>
    </section>

    {{-- 02 Journal — featured article, category chips, post grid --}}
    <section class="section" data-screen-label="02 Journal" style="background:var(--ink-2);border-radius:var(--stack-r)">
        <x-container>

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

        </x-container>
    </section>


    {{-- CTA Strip --}}
    <section class="cta-strip">
        <x-container>
            <h2 class="cta-strip__title" data-split>Get the <em>journal.</em></h2>
            <div class="cta-strip__row reveal">
                <p style="max-width:42ch;color:var(--paper-dim);font-size:17px">One craft note a month. No spam, no growth-hacks.</p>
                <form class="row" action="{{ route('newsletter.store') }}" method="POST">
                    @csrf
                    {{-- Honeypot: real people never fill this; bots do. Mirrors the contact form. --}}
                    <input type="text" name="website" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px" aria-hidden="true">
                    <input type="hidden" name="source_page" value="{{ request()->path() }}">
                    <input type="email" name="email" aria-label="Email address" placeholder="you@studio.com" required value="{{ old('email') }}" style="background:transparent;border:1px solid var(--line);border-radius:100px;padding:14px 22px;color:var(--paper);font:inherit;flex:1 1 220px;min-width:0">
                    <button class="btn btn--red" type="submit" data-magnetic>Subscribe <span class="arr"></span></button>
                </form>
                @if (session('newsletter_status'))
                    <output style="width:100%;font-family:var(--f-mono);font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--paper-dim)">{{ session('newsletter_status') }}</output>
                @endif
                @error('email')
                    <p role="alert" style="width:100%;font-family:var(--f-mono);font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--red)">{{ $message }}</p>
                @enderror
            </div>
        </x-container>
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
