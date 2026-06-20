@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'canonical' => null,
])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? \App\Models\SiteSetting::get('seo_default_title', config('app.name')) }}</title>
    <meta name="description" content="{{ $description ?? \App\Models\SiteSetting::get('seo_default_description', '') }}">
    @if ($canonical) <link rel="canonical" href="{{ $canonical }}"> @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if ($ogImage) <meta property="og:image" content="{{ $ogImage }}"> @endif
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    {{-- chrome.js MUST load before core.js: chrome injects the shared HTML (nav/preloader/quote/cursor), then core.js wires behaviour onto it. --}}
    @vite(['resources/css/core.css','resources/css/pages.css','resources/js/chrome.js','resources/js/core.js'])
    {{ $head ?? '' }}
</head>
<body>
    <x-nav />
    <main>{{ $slot }}</main>
    <x-footer />
    {{-- Shared chrome (nav/footer/quote modal/cursor) self-mounts from resources/js/chrome.js --}}
</body>
</html>
