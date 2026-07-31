import { cacheLife, cacheTag } from 'next/cache'
import { apiBaseUrl } from './env'
import type {
  AboutPage,
  ContactPage,
  HomePage,
  IndustryList,
  PostDetail,
  PostsPage,
  ServiceDetail,
  ServiceList,
  Settings,
  StaticPage,
  StaticPageSlug,
  WorksPage,
} from './types'

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly path: string
  ) {
    super(`API ${status} on ${path}`)
    this.name = 'ApiError'
  }
}

/**
 * True when the API reported the record missing.
 *
 * Checks the shape rather than using `instanceof ApiError`, deliberately: an
 * error thrown inside a `use cache` scope is serialised across the cache
 * boundary and rebuilt on the other side, so it arrives as a plain Error with
 * the properties intact but the prototype chain gone. Production minification
 * makes the class name unreliable too. `status` is the one thing that survives.
 */
export function isNotFound(error: unknown): boolean {
  return (
    typeof error === 'object' &&
    error !== null &&
    'status' in error &&
    (error as { status: unknown }).status === 404
  )
}

/**
 * Raw transport. Not cached itself — the exported getters below own caching, so
 * that each one can declare its own tags and lifetime.
 */
async function request<T>(
  path: string,
  searchParams: Record<string, string | number | undefined> = {}
): Promise<T> {
  const url = new URL(`/api/v1${path}`, apiBaseUrl())

  for (const [key, value] of Object.entries(searchParams)) {
    if (value !== undefined && value !== '') url.searchParams.set(key, String(value))
  }

  let response: Response

  try {
    response = await fetch(url, { headers: { Accept: 'application/json' } })
  } catch (cause) {
    // A raw ECONNREFUSED stack trace says nothing about what to do. Every page
    // reads from the Laravel API, so the API not running is the single most
    // likely reason a developer sees this — say so.
    throw new Error(
      `Cannot reach the Laravel API at ${apiBaseUrl()} (requesting ${path}).\n\n` +
        `Start it alongside Next:\n` +
        `  npm run dev          # starts both\n` +
        `  ./bin/php artisan serve --port=8000   # or just the API\n\n` +
        `If it is running on another port, set API_BASE_URL in web/.env.`,
      { cause }
    )
  }

  if (!response.ok) {
    throw new ApiError(response.status, path)
  }

  return response.json() as Promise<T>
}

/**
 * Like request(), but resolves to null when the record is missing.
 *
 * Detail getters use this rather than throwing. An exception raised inside a
 * `use cache` scope is intercepted by the cache layer and surfaces as a 500
 * before a page-level catch can turn it into notFound() — so a missing slug
 * has to be a value, not a throw.
 */
async function requestOrNull<T>(path: string): Promise<T | null> {
  try {
    return await request<T>(path)
  } catch (error) {
    if (isNotFound(error)) return null
    throw error
  }
}

/*
|------------------------------------------------------------------------------
| Cached reads
|------------------------------------------------------------------------------
|
| Each getter is a `use cache` scope carrying the tags Laravel's
| RevalidateFrontend job drops (see docs/api-v1.md for the vocabulary) and a
| lifetime. Arguments become part of the cache key automatically, so a filtered
| or paginated call caches separately without any extra wiring.
|
| `cacheLife('hours')` rather than something longer: on-demand revalidation is
| the primary freshness mechanism, and the time window is only the safety net
| for a webhook that never arrived.
|
*/

export async function getSettings(): Promise<Settings> {
  'use cache'
  cacheTag('settings')
  cacheLife('hours')

  return (await request<{ data: Settings }>('/settings')).data
}

export async function getHome(): Promise<HomePage> {
  'use cache'
  cacheTag('pages:home')
  cacheLife('hours')

  return request<HomePage>('/pages/home')
}

export async function getAbout(): Promise<AboutPage> {
  'use cache'
  cacheTag('pages:about')
  cacheLife('hours')

  return request<AboutPage>('/pages/about')
}

export async function getContact(): Promise<ContactPage> {
  'use cache'
  cacheTag('pages:contact')
  cacheLife('hours')

  return request<ContactPage>('/pages/contact')
}

export async function getStaticPage(slug: StaticPageSlug): Promise<StaticPage> {
  'use cache'
  // Also tagged `settings`: this copy moves into the admin in a later phase,
  // and without it an edit would never invalidate these routes.
  cacheTag(`pages:${slug}`, 'settings')
  cacheLife('days')

  return request<StaticPage>(`/pages/${slug}`)
}

export async function getWorks(
  params: { category?: string; page?: number } = {}
): Promise<WorksPage> {
  'use cache'
  cacheTag('works')
  cacheLife('hours')

  return request<WorksPage>('/works', params)
}

export async function getServices(): Promise<ServiceList> {
  'use cache'
  cacheTag('services')
  cacheLife('hours')

  return request<ServiceList>('/services')
}

export async function getService(slug: string): Promise<ServiceDetail | null> {
  'use cache'
  cacheTag('services', `services:${slug}`)
  cacheLife('hours')

  return requestOrNull<ServiceDetail>(`/services/${slug}`)
}

export async function getIndustries(): Promise<IndustryList> {
  'use cache'
  cacheTag('industries')
  cacheLife('hours')

  return request<IndustryList>('/industries')
}

export async function getPosts(
  params: { category?: string; tag?: string; page?: number } = {}
): Promise<PostsPage> {
  'use cache'
  cacheTag('posts')
  cacheLife('hours')

  return request<PostsPage>('/posts', params)
}

export async function getPost(slug: string): Promise<PostDetail | null> {
  'use cache'
  cacheTag('posts', `posts:${slug}`)
  cacheLife('hours')

  return requestOrNull<PostDetail>(`/posts/${slug}`)
}

/*
|------------------------------------------------------------------------------
| Writes
|------------------------------------------------------------------------------
|
| Write endpoints live in ./api-client.ts, not here. Next rejects any module
| reachable from a Client Component that defines "use cache" functions, and the
| forms that submit them are client components. Keeping the split structural
| means a cached server read can never be imported into the browser bundle.
|
*/
