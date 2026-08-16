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
    // An admin-set canonical is honoured EXACTLY as typed — cross-domain canonicals
    // are a legitimate thing to want (syndicated copy pointing home), and rewriting
    // one onto our own host would silently defeat the reason someone set it.
    //
    // Everything else goes through AppUrl::canonical(), because everything else is
    // derived from the request: both url() and url()->current() take their host
    // from the incoming Host header, which is how www and apex each ended up
    // emitting a canonical pointing at themselves.
    $seoCanonical = $seo?->canonical_url ?: \App\Support\AppUrl::canonical($canonical);
    // Admin-managed brand mark (Site Settings → Branding). Null when nothing is uploaded —
    // in that case no logo is rendered anywhere, by design.
    $brandLogo = \App\Models\SiteSetting::brandLogoUrl();
    // Final fallback keeps every share card imaged — admin can override in Site Settings → SEO.
    // defaultOgImageUrl(), NOT get(): the stored value is an upload key, and
    // emitting it raw gave every share card a relative path that no crawler or
    // social platform can resolve from its own servers.
    $seoImage = $seo?->ogImage() ?: ($ogImage ?: (\App\Models\SiteSetting::defaultOgImageUrl() ?: $brandLogo));
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
    <link rel="canonical" href="{{ $seoCanonical }}">
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
    {{-- Names the brand on every shared link rather than leaving each platform to
         infer it from the domain. One declaration, read by everything that renders
         a link preview. --}}
    <meta property="og:site_name" content="{{ \App\Support\Brand::NAME }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if ($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- One typeface, site-wide: Jost. Chosen to sit with the brand mark, which is
         a monoline geometric wordmark with circular bowls — Archivo Black (wide,
         black, grotesque) and JetBrains Mono fought it on every axis.

         Weights are exactly the six core.css and pages.css declare. 900 is not
         among them; requesting it only adds a file nothing renders.

         The italic axis is NOT optional decoration: .hero__title em,
         .belief__title em, .svc__title em and .section__title em all set
         `font-style: italic`. Without ital here the browser fakes the slant by
         shearing the roman, which on a light geometric face reads as a
         rendering fault rather than an accent.

         brutalist.css is still on disk but no longer built. Re-adding its @vite
         entry means re-adding Archivo Black and JetBrains Mono here too, or its
         headlines silently fall back to a system face. --}}
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    {{-- chrome.js MUST load before core.js: chrome injects the shared HTML (nav/preloader/quote/cursor), then core.js wires behaviour onto it. --}}
    @vite(['resources/css/core.css','resources/css/pages.css','resources/js/chrome.js','resources/js/core.js'])

    {{-- Analytics and ad tags, both cookie-banner gated. Moved out of this file so
         GA and the Meta Pixel share one consent story instead of two; chrome.js
         calls the matching grant for each when the banner is answered. --}}
    <x-tracking />

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
