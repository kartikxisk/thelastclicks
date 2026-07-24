@php
  $contactPhone = \App\Models\SiteSetting::get('contact_phone', '+91 87701 55842');
  $contactEmail = \App\Models\SiteSetting::get('contact_email', 'info@thelastclicks.com');
  $whatsappUrl  = \App\Models\SiteSetting::get('whatsapp_url', 'https://wa.me/918770155842');
  $s = \App\Models\SiteSetting::get('socials', []);
  $socials = array_filter([
    'instagram' => $s['instagram'] ?? null,
    'youtube'   => $s['youtube'] ?? null,
    'facebook'  => $s['facebook'] ?? null,
    'linkedin'  => $s['linkedin'] ?? null,
    'x'         => $s['x'] ?? null,
    'behance'   => $s['behance'] ?? null,
    'pinterest' => $s['pinterest'] ?? null,
  ]);
  $icons = [
    'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>',
    'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 7.5a3 3 0 0 0-2.1-2.1C19 5 12 5 12 5s-7 0-8.9.4A3 3 0 0 0 1 7.5 31 31 0 0 0 .6 12 31 31 0 0 0 1 16.5a3 3 0 0 0 2.1 2.1C5 19 12 19 12 19s7 0 8.9-.4a3 3 0 0 0 2.1-2.1A31 31 0 0 0 23.4 12 31 31 0 0 0 23 7.5zM9.8 15.3V8.7l5.7 3.3z"/></svg>',
    'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12a12 12 0 1 0-13.9 11.9v-8.4H7v-3.5h3.1V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.2h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12z"/></svg>',
    'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2zM8 19H5v-9h3zM6.5 8.3a1.7 1.7 0 1 1 0-3.5 1.7 1.7 0 0 1 0 3.5zM19 19h-3v-4.7c0-1.1 0-2.6-1.6-2.6S12.6 13 12.6 14.2V19h-3v-9h2.9v1.2h.04a3.2 3.2 0 0 1 2.9-1.6c3.1 0 3.7 2 3.7 4.7z"/></svg>',
    'x'         => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2h3.3l-7.2 8.3L23.5 22h-6.6l-5.2-6.8L5.8 22H2.5l7.7-8.8L1 2h6.8l4.7 6.2zm-1.2 18h1.8L7.3 3.9H5.4z"/></svg>',
    'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.8.7.7-2.7-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.7 1-.3.2-.5.1a6.5 6.5 0 0 1-1.9-1.2 7.3 7.3 0 0 1-1.4-1.7c-.1-.2 0-.4.1-.5l.4-.4.2-.4v-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A2.9 2.9 0 0 0 5 9.6a5 5 0 0 0 1.1 2.7 11.5 11.5 0 0 0 4.4 3.9c2.1.8 2.1.6 2.5.5a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .1-1.2z"/></svg>',
    'behance'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.2 6.8c.6 0 1.2.05 1.7.2.5.1.9.3 1.3.6.3.2.6.6.8 1 .2.4.3.9.3 1.5 0 .6-.15 1.2-.4 1.6-.3.4-.7.8-1.3 1 .8.2 1.4.6 1.8 1.2.4.5.6 1.2.6 2 0 .6-.1 1.2-.4 1.6-.2.5-.6.9-1 1.2-.4.3-.9.5-1.5.7-.5.1-1.1.2-1.6.2H1V6.8h7.2zM7.8 11c.5 0 .9-.1 1.2-.4.3-.2.4-.6.4-1.1 0-.3-.05-.5-.15-.7-.1-.2-.2-.3-.4-.4-.15-.1-.35-.2-.55-.2-.2 0-.5-.1-.7-.1H4v2.9h3.8zm.2 4.5c.3 0 .55 0 .8-.1.2 0 .45-.1.6-.3.2-.1.3-.3.4-.5.1-.2.15-.5.15-.8 0-.6-.2-1.1-.5-1.3-.4-.3-.85-.4-1.4-.4H4v3.4h4zM16.5 15.3c.4.4.9.5 1.6.5.5 0 1-.15 1.3-.4.4-.2.6-.5.7-.8h2c-.3 1-.8 1.7-1.5 2.1-.7.5-1.5.7-2.5.7-.7 0-1.3-.1-1.9-.3-.5-.2-1-.5-1.4-1-.4-.4-.7-.9-.9-1.5-.2-.6-.3-1.2-.3-1.9 0-.7.1-1.3.3-1.8.2-.6.5-1.1.9-1.5.4-.4.9-.8 1.4-1 .5-.2 1.2-.4 1.8-.4.8 0 1.4.2 2 .5.6.3 1 .7 1.4 1.2.4.5.6 1.1.8 1.7.1.6.15 1.3.1 2h-6.4c0 .7.2 1.3.6 1.7zm2.8-4.6c-.3-.3-.8-.5-1.4-.5-.4 0-.7.1-1 .2-.3.1-.5.3-.6.5-.2.2-.3.4-.3.6-.05.2-.1.4-.1.6h4c-.1-.7-.3-1.2-.6-1.5zM16 6.7h5v1.2h-5z"/></svg>',
    'pinterest' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 .05-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.4 1.8-2.4.9 0 1.3.6 1.3 1.4 0 .9-.55 2.1-.85 3.3-.2.9.5 1.7 1.4 1.7 1.7 0 2.9-2.2 2.9-4.7 0-2-1.3-3.4-3.7-3.4a4.2 4.2 0 0 0-4.4 4.2c0 .8.25 1.4.6 1.8.15.2.2.3.15.5l-.2.8c-.1.3-.3.4-.55.25-1-.45-1.5-1.6-1.5-2.95 0-2.4 2-5.2 5.9-5.2 3.2 0 5.3 2.3 5.3 4.8 0 3.2-1.8 5.6-4.4 5.6-.9 0-1.7-.5-2-1l-.5 2.1c-.2.7-.6 1.4-1 2A10 10 0 1 0 12 2z"/></svg>',
  ];
  $labels = ['instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'x' => 'X', 'behance' => 'Behance', 'pinterest' => 'Pinterest', 'whatsapp' => 'WhatsApp'];
@endphp
<footer class="foot">
  <x-container>

    {{-- Oversized email — the footer's headline call to action --}}
    <div class="foot__cta">
      <span class="foot__kicker">Get in touch</span>
      <a class="foot__hello" href="mailto:{{ $contactEmail }}" data-cursor="EMAIL">{{ $contactEmail }} <span class="arr">↗</span></a>
    </div>

    <div class="foot__meta">
      <div class="foot__intro">
        <span class="foot__status"><span class="foot__pulse"></span> Available for new projects</span>
        <p>Cinematic photography &amp; videography, finished by the in-house post-production that sets us apart.</p>
        @if (count($socials))
        <div class="foot__socials">
          @foreach ($socials as $key => $href)
            <a href="{{ $href }}" target="_blank" rel="noopener" data-noswap aria-label="{{ $labels[$key] }}" data-cursor="{{ strtoupper($labels[$key]) }}">{!! $icons[$key] !!}</a>
          @endforeach
        </div>
        @endif
      </div>

      <nav class="foot__nav" aria-label="Footer">
        <div class="foot__col">
          <h5><span class="foot__idx">01</span> Studio</h5>
          <a href="{{ url('/about') }}">About</a>
          <a href="{{ url('/industries') }}">Industries</a>
          <a href="{{ url('/our-works') }}">Our Work</a>
          <a href="{{ url('/blog') }}">Journal</a>
        </div>
        <div class="foot__col">
          <h5><span class="foot__idx">02</span> Work</h5>
          <a href="{{ url('/services/post-production') }}">Post Production</a>
          <a href="{{ url('/services/videography') }}">Videography</a>
          <a href="{{ url('/services/photography') }}">Photography</a>
        </div>
        <div class="foot__col">
          <h5><span class="foot__idx">03</span> Contact</h5>
          <a href="tel:{{ preg_replace('/[^+\d]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
          <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" data-noswap>WhatsApp</a>
          <a href="{{ url('/contact') }}">Start a project</a>
        </div>
      </nav>
    </div>

    <div class="foot__copy">
      <span>© {{ date('Y') }} TheLastClicks — All rights reserved</span>
      <span class="foot__legal">
        <a href="{{ url('/privacy-policy') }}">Privacy</a>
        <a href="{{ url('/cookie-policy') }}">Cookies</a>
        <a href="{{ url('/terms-of-service') }}">Terms</a>
        <a href="{{ url('/disclaimer') }}">Disclaimer</a>
      </span>
      <button class="foot__top-btn" data-scroll-top data-cursor="TOP">Back to top <span class="arr">↑</span></button>
    </div>
  </x-container>
</footer>
