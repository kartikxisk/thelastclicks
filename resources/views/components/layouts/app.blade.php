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
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- chrome.js reads this to render the preloader / quote-modal marks. Absent = render no logo. --}}
    @if ($brandLogo) <meta name="brand-logo" content="{{ $brandLogo }}"> @endif
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
    {{-- Only what actually renders: Outfit supersedes Sora/Inter in core.css, and no rule uses
         weight 300 or 900. Dropping them removes 12 unused font files from the critical path. --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    {{-- chrome.js MUST load before core.js: chrome injects the shared HTML (nav/preloader/quote/cursor), then core.js wires behaviour onto it. --}}
    @vite(['resources/css/core.css','resources/css/pages.css','resources/js/chrome.js','resources/js/core.js'])
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
