/**
 * Permanent redirects ported from routes/web.php. Every entry preserves an
 * indexed URL or an inbound link — dropping one silently 404s real traffic.
 *
 * tests/Feature/Api/V1/RedirectParityTest.php asserts this list stays in sync
 * with the Laravel route file. Add to both or neither.
 *
 * Next matches static segments before dynamic ones, so `/portfolio` resolves to
 * the page and only `/portfolio/<something>` hits the wildcard entry.
 *
 * statusCode: 301 rather than permanent: true, which emits 308. Google treats
 * the two the same, but the Blade site returns 301 and matching it exactly
 * means the pre-cutover parity crawl has one less difference to explain.
 */
export const REDIRECTS = [
  { source: '/our-process', destination: '/about', statusCode: 301 },

  // Retired services — the studio now offers photography, videography and
  // editing only. Each points at the closest survivor.
  { source: '/services/weddings', destination: '/services/videography', statusCode: 301 },
  { source: '/services/post-production', destination: '/services/editing', statusCode: 301 },
  { source: '/services/social-content', destination: '/services/editing', statusCode: 301 },
  { source: '/services/creative-direction', destination: '/services/editing', statusCode: 301 },

  // Industry detail pages retired; the index carries the vertical story now.
  { source: '/industries/:slug', destination: '/industries', statusCode: 301 },

  { source: '/our-works', destination: '/portfolio', statusCode: 301 },
  { source: '/portfolio/:slug', destination: '/', statusCode: 301 },

  // Earlier posts were published under auto-generated slugs built from the full
  // headline. These consolidate onto the short canonical slugs.
  {
    source:
      '/blog/how-to-brief-a-video-production-team-so-the-film-you-get-is-the-film-you-imagined',
    destination: '/blog/how-to-brief-a-video-production-team',
    statusCode: 301,
  },
  {
    source: '/blog/planning-your-wedding-photography-timeline-a-working-template',
    destination: '/blog/wedding-photography-timeline-planning',
    statusCode: 301,
  },
  {
    source: '/blog/what-post-production-actually-includes-and-why-it-is-half-the-film',
    destination: '/blog/what-post-production-actually-includes',
    statusCode: 301,
  },
  {
    source: '/blog/photo-video-or-both-choosing-coverage-for-your-corporate-event',
    destination: '/blog/photo-vs-video-corporate-event-coverage',
    statusCode: 301,
  },
  {
    source: '/blog/how-to-prepare-your-team-for-a-corporate-shoot',
    destination: '/blog/preparing-your-team-for-a-corporate-shoot',
    statusCode: 301,
  },

  // Talent/crew pages retired.
  { source: '/crew', destination: '/about', statusCode: 301 },
  { source: '/crew/:slug', destination: '/about', statusCode: 301 },
] as const
