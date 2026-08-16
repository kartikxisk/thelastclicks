@php
  /**
   * Meta Pixel and GA4. Both admin-managed under Site Settings → Tracking.
   *
   * The GA block used to live inline in the layout, reading only
   * config('services.google_analytics.id'). It still falls back to that, so an
   * environment setting GA_MEASUREMENT_ID keeps working; the setting simply wins
   * when present, which is what lets the ID be changed without a deploy.
   *
   * IDs are pattern-checked in SiteSetting rather than printed as typed — they
   * land inside an inline <script>, where a stray apostrophe breaks every script
   * on the page and a deliberate one is stored XSS with an admin as the author.
   *
   * Not loaded in local or testing. Dev and test pageviews land in the same Meta
   * and GA property as real traffic and cannot be separated out afterwards. Give
   * a staging box APP_ENV=staging rather than local to check the tags on it.
   *
   * BOTH tags are gated by the cookie banner, and that is not optional here: the
   * site runs a consent dialog and publishes a cookie policy, so a tag that
   * fires before the visitor answers makes the banner decoration. GA uses
   * Consent Mode v2, Meta its own consent call; both start denied, and chrome.js
   * grants on accept. A returning visitor who already accepted is granted before
   * the first PageView so their visit is measured from first paint.
   *
   * Hosts are allowlisted in App\Http\Middleware\SecurityHeaders — script-src for
   * the loaders, connect-src for the beacons. Adding a tag without adding its
   * hosts publishes a policy that would block it the day the header stops being
   * Report-Only.
   *
   * No integrity="sha384-…" on these loaders, and none is possible: Google and
   * Meta rewrite the bundles continuously and publish no hashes, so a pinned one
   * breaks the tag on their next deploy. The CSP host allowlist is what bounds
   * the trust instead.
   */
  $metaPixelId = \App\Models\SiteSetting::metaPixelId();
  $gaMeasurementId = \App\Models\SiteSetting::gaMeasurementId()
      ?: config('services.google_analytics.id');
  $trackable = ! app()->environment('local', 'testing');
@endphp

@if ($trackable && $gaMeasurementId)
  {{-- Google Analytics 4, gated by the cookie banner through Consent Mode v2.
       analytics_storage starts denied and is only granted once the visitor has
       actually accepted, so "Only essential" is a real choice rather than a
       dialog that dismisses itself. --}}
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
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
      gtag('config', '{{ $gaMeasurementId }}');
  </script>
@endif

@if ($trackable && $metaPixelId)
  {{-- Meta Pixel Code --}}
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $metaPixelId }}');

    // Consent before the first event, never after: revoke has to be in place
    // before track() or the PageView this line is meant to gate has already
    // gone. chrome.js grants (and fires the deferred PageView) on accept.
    var tlcPixelConsent = 'revoke';
    try {
      if (localStorage.getItem('tlc-cookies') === 'accepted') { tlcPixelConsent = 'grant'; }
    } catch (e) { /* storage blocked: stay revoked */ }
    fbq('consent', tlcPixelConsent);

    fbq('track', 'PageView');
  </script>
  {{-- The no-JS pixel is deliberately absent. It cannot read the consent choice,
       so it would report every visitor who declined — the one hole that makes the
       banner a lie. A visitor with JS off is not measured, which is correct. --}}
  {{-- End Meta Pixel Code --}}
@endif
