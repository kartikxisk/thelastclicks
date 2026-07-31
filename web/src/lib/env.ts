/**
 * Environment access, validated at the point of use.
 *
 * These throw rather than defaulting: a missing API base URL must fail loudly
 * instead of producing a site that renders empty sections in production.
 *
 * Read through the accessor functions, not at module scope — `next build`
 * evaluates modules while collecting page data, and a module-scope throw would
 * fail the build on a machine that legitimately has no runtime env yet.
 */
function required(name: string): string {
  const value = process.env[name]

  if (!value) {
    throw new Error(`Missing required environment variable: ${name}. See web/.env.example.`)
  }

  return value
}

/** Laravel API origin. Localhost in production — nginx and Node share a box. */
export function apiBaseUrl(): string {
  return required('API_BASE_URL')
}

/** Shared secret the Laravel revalidation webhook presents. */
export function revalidateSecret(): string {
  return required('REVALIDATE_SECRET')
}

/** Public site origin, used for canonicals and OG URLs. */
export function siteUrl(): string {
  return required('NEXT_PUBLIC_SITE_URL')
}
