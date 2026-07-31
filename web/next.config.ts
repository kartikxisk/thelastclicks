import { fileURLToPath } from 'node:url'
import { dirname } from 'node:path'
import type { NextConfig } from 'next'
import { REDIRECTS } from './src/redirects'

const config: NextConfig = {
  // The repo root holds its own package-lock.json for the Filament theme build,
  // so Turbopack would otherwise infer the Laravel directory as the workspace
  // root and resolve modules from the wrong tree.
  turbopack: { root: dirname(fileURLToPath(import.meta.url)) },

  // Traces the exact dependency set into .next/standalone so the deploy
  // artifact is a self-contained Node server, not a node_modules tree.
  // server.js does NOT copy public/ or .next/static — the deploy script does,
  // and forgetting that step is what produces an unstyled site.
  output: 'standalone',

  // Next 16's unified caching model: `use cache` + cacheTag + cacheLife, with
  // Partial Prerendering as the default. Data is dynamic unless a function
  // opts in, which is what lets every page ship a static shell while the
  // admin-managed parts stream and revalidate by tag.
  cacheComponents: true,

  images: {
    remotePatterns: [
      // Media lives on S3/CloudFront. The host comes from env so staging and
      // production can differ without a code change.
      {
        protocol: 'https',
        hostname: process.env.NEXT_PUBLIC_MEDIA_HOST ?? 'cdn.thelastclicks.com',
      },
      // YouTube posters for gallery rows of type `youtube`.
      { protocol: 'https', hostname: 'img.youtube.com' },
    ],
  },

  async redirects() {
    return [...REDIRECTS]
  },

  // In production nginx puts Laravel and Next on one origin, so /api/v1 is
  // same-origin. In dev, proxy it so the browser never sees a second port and
  // CORS never enters the picture.
  async rewrites() {
    return process.env.NODE_ENV === 'development'
      ? [
          {
            source: '/api/v1/:path*',
            destination: `${process.env.API_BASE_URL}/api/v1/:path*`,
          },
        ]
      : []
  },
}

export default config
