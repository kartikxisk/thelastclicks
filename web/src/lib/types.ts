/**
 * Mirrors app/Http/Resources/Api/V1/*.php.
 *
 * The authority is tests/Feature/Api/V1/contract.json, which is asserted on
 * every Laravel test run, and docs/api-v1.md which documents it. Any change to
 * a Resource requires the matching change here in the same pull request.
 *
 * Several inner key names are admin-authored and are not what you would guess
 * — faqs are {q, a}, pillars are {title, desc}. Do not "tidy" them.
 */

export interface Media {
  url: string
  srcset: string | null
  width: number | null
  height: number | null
  mime: string | null
  alt: string | null
}

export interface MediaItem {
  type: 'image' | 'video' | 'youtube'
  url: string
  /** YouTube poster; null for images and uploaded video. */
  poster: string | null
  caption: string | null
  width: number | null
  height: number | null
  mime: string | null
}

export interface Seo {
  title: string | null
  description: string | null
  /** Absolute, and page-aware on paginated routes. */
  canonical: string
  noindex: boolean
  nofollow: boolean
  og: { title: string | null; description: string | null; image: string | null }
  json_ld: Record<string, unknown>[]
}

export interface FilterOption {
  value: string
  label: string
}

export interface PageMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Work {
  id: number
  slug: string
  title: string
  summary: string | null
  client: string | null
  category: string | null
  category_label: string | null
  /** Labels, already filtered to slugs still present in the CRAFTS map. */
  crafts: string[]
  /** Half-filled rows are dropped server-side. */
  credits: { role: string; name: string }[]
  location: string | null
  agency: string | null
  year: string | null
  cover: string | null
  /** Uploaded video only — YouTube is excluded, an iframe per tile is too heavy. */
  preview_video_url: string | null
  media: MediaItem[]
  is_featured: boolean
}

export interface Service {
  id: number
  slug: string
  title: string
  hero_headline: string | null
  hero_copy: string | null
  hero_meta: { label: string; value: string }[]
  hero: Media | null
  proof: { count?: string; label?: string; sectors?: string }
  pillars: { title: string; desc: string }[]
  phases: { num: string; title: string; desc: string; time: string }[]
  kit: { title: string; items: string[] }[]
  faqs: { q: string; a: string }[]
  cta: { title?: string; copy?: string; prefill?: string }
  tags: string[]
  gallery: string[]
  body: string | null
  share: number | null
}

/** No avatar — Testimonial has no media collection. */
export interface Testimonial {
  id: number
  quote: string
  client_name: string | null
  role_company: string | null
}

export interface Industry {
  id: number
  slug: string
  title: string
  summary: string | null
  body: string | null
  cover: string | null
  media: MediaItem[]
  testimonials: Testimonial[]
}

export interface Post {
  id: number
  slug: string
  title: string
  excerpt: string | null
  body: string | null
  /** ISO 8601. */
  published_at: string | null
  reading_minutes: number
  cover: string | null
  /** The first category only; the card design shows one. */
  category: FilterOption | null
  tags: FilterOption[]
}

export interface Client {
  id: number
  name: string
  /** Resolved URL string, not a Media object — logoUrl() can fall back to an
   *  admin-set path that has no Media record behind it. */
  logo: string | null
  url: string | null
}

export interface HeroSlide {
  id: number
  label: string | null
  asset: Media | null
  poster: Media | null
  mime: string | null
  is_video: boolean
}

export interface Settings {
  contact_email: string | null
  contact_phone: string | null
  whatsapp_url: string | null
  /** Fixed key set — unset platforms are null, never absent. */
  socials: {
    instagram: string | null
    youtube: string | null
    facebook: string | null
    linkedin: string | null
    x: string | null
    behance: string | null
    pinterest: string | null
  }
  /** Null means render no logo. Do not substitute a bundled file. */
  brand_logo_url: string | null
  favicon_url: string
  cta_video_url: string
  /** A CSS aspect-ratio value, already validated against the offered set. */
  work_tile_ratio: string
  seo_defaults: {
    title: string | null
    description: string | null
    og_image: string | null
  }
}

export interface HomePage {
  data: {
    hero_slides: HeroSlide[]
    services: Service[]
    featured_works: Work[]
    industries: Industry[]
    testimonials: Testimonial[]
    clients: Client[]
  }
  seo: Seo
}

export interface AboutPage {
  data: {
    testimonials: Testimonial[]
    clients: Client[]
    stats: { works: number; clients: number }
  }
  seo: Seo
}

export interface ContactPage {
  data: {
    services: Service[]
    project_types: FilterOption[]
    /** value equals label — the quote form stores the label itself. */
    budget_ranges: FilterOption[]
  }
  seo: Seo
}

/** `body` is null for thank-you, which the frontend designs itself. */
export interface StaticPage {
  data: { body: string | null }
  seo: Seo
}

export interface WorksPage {
  data: Work[]
  meta: PageMeta
  filters: { categories: FilterOption[] }
  seo: Seo
}

export interface PostsPage {
  data: Post[]
  meta: PageMeta
  filters: { categories: FilterOption[]; tags: FilterOption[] }
  seo: Seo
}

export type ServiceDetail = { data: Service & { related_works: Work[] }; seo: Seo }
export type PostDetail = { data: Post & { related: Post[] }; seo: Seo }
export type ServiceList = { data: Service[]; seo: Seo }
export type IndustryList = { data: Industry[]; seo: Seo }

/** Static page slugs the API accepts. A route constraint, not a lookup. */
export const STATIC_PAGE_SLUGS = [
  'privacy-policy',
  'terms-of-service',
  'cookie-policy',
  'disclaimer',
  'thank-you',
] as const

export type StaticPageSlug = (typeof STATIC_PAGE_SLUGS)[number]
