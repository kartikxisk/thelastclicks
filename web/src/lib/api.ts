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

  const response = await fetch(url, { headers: { Accept: 'application/json' } })

  if (!response.ok) {
    throw new ApiError(response.status, path)
  }

  return response.json() as Promise<T>
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

export async function getService(slug: string): Promise<ServiceDetail> {
  'use cache'
  cacheTag('services', `services:${slug}`)
  cacheLife('hours')

  return request<ServiceDetail>(`/services/${slug}`)
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

export async function getPost(slug: string): Promise<PostDetail> {
  'use cache'
  cacheTag('posts', `posts:${slug}`)
  cacheLife('hours')

  return request<PostDetail>(`/posts/${slug}`)
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
