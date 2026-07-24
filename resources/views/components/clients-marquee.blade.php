@php
    // Admin-managed: each client resolves to an uploaded logo (media disk) or the
    // logo path set alongside it. Wordmarks below are the last resort so the strip
    // is never empty on a fresh install.
    $clients = \App\Models\Client::active()->orderBy('order')->get()
        ->map(fn ($c) => ['name' => $c->name, 'logo' => $c->logoUrl()])
        ->filter(fn ($c) => filled($c['logo']))
        ->values();

    $fallback = [
        'DLF', 'Amazon', 'Mothercare', 'Oberoi', 'Taj', 'Hyatt', 'Ritz-Carlton',
        'Range Rover', 'BMW', 'Rolls-Royce', 'Mercedes', 'Adobe', 'Meta', 'TaskUs',
        'WNS', 'Johnnie Walker', 'Black Dog', 'Singleton', 'Godawan', 'Smirnoff',
        'Radico Khaitan', 'Bacardi', 'Beluga',
    ];
@endphp
<div class="marquee marquee--logos" aria-label="Brands we've worked with">
    <div class="marquee__track">
        @if ($clients->isNotEmpty())
            {{-- Rendered twice so the -50% keyframe scrolls seamlessly --}}
            @foreach ($clients->concat($clients) as $client)
                <span class="marquee__item marquee__item--logo">
                    <img class="marquee__logo" src="{{ $client['logo'] }}" alt="{{ $client['name'] }}" loading="lazy" decoding="async">
                </span>
            @endforeach
        @else
            @foreach (array_merge($fallback, $fallback) as $i => $name)
                <span class="marquee__item {{ $i % 2 ? 'is-outline' : '' }}">{{ $name }}</span>
            @endforeach
        @endif
    </div>
</div>
