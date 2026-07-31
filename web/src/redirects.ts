/**
 * Permanent redirects ported from routes/web.php. Every entry preserves an
 * indexed URL or an inbound link — dropping one silently 404s real traffic.
 *
 * tests/Feature/Api/V1/RedirectParityTest.php asserts this list stays in sync
 * with the Laravel route file. Add to both or neither.
 *
 * Next matches static segments before dynamic ones, so `/portfolio` resolves to
 * the page and only `/portfolio/<something>` hits the wildcard entry.
 */
export const REDIRECTS = [
  { source: '/our-process', destination: '/about', permanent: true },

  // Retired services — the studio now offers photography, videography and
  // editing only. Each points at the closest survivor.
  { source: '/services/weddings', destination: '/services/videography', permanent: true },
  { source: '/services/post-production', destination: '/services/editing', permanent: true },
  { source: '/services/social-content', destination: '/services/editing', permanent: true },
  { source: '/services/creative-direction', destination: '/services/editing', permanent: true },

  // Industry detail pages retired; the index carries the vertical story now.
  { source: '/industries/:slug', destination: '/industries', permanent: true },

  { source: '/our-works', destination: '/portfolio', permanent: true },
  { source: '/portfolio/:slug', destination: '/', permanent: true },

  // Earlier posts were published under auto-generated slugs built from the full
  // headline. These consolidate onto the short canonical slugs.
  {
    source:
      '/blog/how-to-brief-a-video-production-team-so-the-film-you-get-is-the-film-you-imagined',
    destination: '/blog/how-to-brief-a-video-production-team',
    permanent: true,
  },
  {
    source: '/blog/planning-your-wedding-photography-timeline-a-working-template',
    destination: '/blog/wedding-photography-timeline-planning',
    permanent: true,
  },
  {
    source: '/blog/what-post-production-actually-includes-and-why-it-is-half-the-film',
    destination: '/blog/what-post-production-actually-includes',
    permanent: true,
  },
  {
    source: '/blog/photo-video-or-both-choosing-coverage-for-your-corporate-event',
    destination: '/blog/photo-vs-video-corporate-event-coverage',
    permanent: true,
  },
  {
    source: '/blog/how-to-prepare-your-team-for-a-corporate-shoot',
    destination: '/blog/preparing-your-team-for-a-corporate-shoot',
    permanent: true,
  },

  // Talent/crew pages retired.
  { source: '/crew', destination: '/about', permanent: true },
  { source: '/crew/:slug', destination: '/about', permanent: true },
] as const
