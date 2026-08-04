@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'canonical' => null,
])
@php
    // Admin-managed per-URL SEO ("Manage SEO") wins over whatever the page passed.
    // Each field falls back independently, so a row that only sets a title still
    // keeps the page's own description.
    $seo = \App\Models\SeoPage::forPath();
    $seoTitle = $seo?->title ?: ($title ?? \App\Models\SiteSetting::get('seo_default_title', config('app.name')));
    $seoDescription = $seo?->meta_description ?: ($description ?? \App\Models\SiteSetting::get('seo_default_description', ''));
    $seoCanonical = $seo?->canonical_url ?: $canonical;
    // Admin-managed brand mark (Site Settings → Branding). Null when nothing is uploaded —
    // in that case no logo is rendered anywhere, by design.
    $brandLogo = \App\Models\SiteSetting::brandLogoUrl();
    // Final fallback keeps every share card imaged — admin can override in Site Settings → SEO.
    $seoImage = $seo?->ogImage() ?: ($ogImage ?: (\App\Models\SiteSetting::get('seo_default_og_image') ?: $brandLogo));
    $seoRobots = $seo?->robotsContent();
    $seoOgTitle = $seo?->og_title ?: $seoTitle;
    $seoOgDescription = $seo?->og_description ?: $seoDescription;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if ($seo?->meta_keywords) <meta name="keywords" content="{{ $seo->meta_keywords }}"> @endif
    @if ($seoRobots) <meta name="robots" content="{{ $seoRobots }}"> @endif
    <link rel="canonical" href="{{ $seoCanonical ?: url()->current() }}">
    {{-- Admin-managed (Site Settings → Branding); falls back to the bundled favicon.
         No type attribute: an uploaded icon may be PNG, SVG or ICO. --}}
    @php $favicon = \App\Models\SiteSetting::faviconUrl(); @endphp
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- chrome.js reads this to render the preloader / quote-modal marks. Absent = render no logo. --}}
    @if ($brandLogo) <meta name="brand-logo" content="{{ $brandLogo }}"> @endif
    {{-- Background clip for the closing CTA. Admin-managed; core.js reads this
         and falls back to the bundled file when nothing is uploaded. --}}
    <meta name="cta-video" content="{{ \App\Models\SiteSetting::ctaVideoUrl() }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical ?: url()->current() }}">
    @if ($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Three registers, per DESIGN-BRUTALIST.md §3: Archivo Black carries every
         headline, JetBrains Mono carries the whole micro register and body copy.
         Outfit stays loaded at two weights only — long-form article prose is the
         one place brutalist.css stands the monospace body down, and dropping the
         family entirely would leave that text on a system fallback. --}}
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=JetBrains+Mono:wght@400;700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    {{-- chrome.js MUST load before core.js: chrome injects the shared HTML (nav/preloader/quote/cursor), then core.js wires behaviour onto it. --}}
    {{-- brutalist.css last: it overrides tokens that core.css re-declares at its own tail. --}}
    @vite(['resources/css/core.css','resources/css/pages.css','resources/css/brutalist.css','resources/js/chrome.js','resources/js/core.js'])

    @php $gaId = config('services.google_analytics.id'); @endphp
    @if ($gaId && ! app()->environment('local', 'testing'))
        {{-- Google Analytics, gated by the cookie banner through Consent Mode v2.
             analytics_storage starts denied and is only granted once the visitor
             has actually accepted, so "Only essential" is a real choice rather
             than a dialog that dismisses itself. chrome.js calls the matching
             consent update when the banner is answered. --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}

            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
            });

            // A returning visitor who already accepted should not be asked again,
            // and should be measured from the first paint of this page.
            try {
                if (localStorage.getItem('tlc-cookies') === 'accepted') {
                    gtag('consent', 'update', { analytics_storage: 'granted' });
                }
            } catch (e) { /* storage blocked: stay denied */ }

            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif

    {{ $head ?? '' }}
</head>
<body>
    <a href="#main" class="skip-link">Skip to content</a>
    <x-nav />
    <main id="main">{{ $slot }}</main>
    <x-footer />
    {{-- Shared chrome (nav/footer/quote modal/cursor) self-mounts from resources/js/chrome.js --}}
</body>
</html>
