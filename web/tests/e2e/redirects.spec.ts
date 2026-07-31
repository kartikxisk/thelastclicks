import { expect, test } from '@playwright/test'
import { REDIRECTS } from '../../src/redirects'

/**
 * Every entry in the redirect map, exercised against the running server.
 *
 * Driven off REDIRECTS itself rather than a hand-written list, so adding a
 * redirect without a test is impossible.
 */
for (const redirect of REDIRECTS) {
  // Dynamic sources need a concrete value to request.
  const source = redirect.source.replace(/:(\w+)/, 'sample-slug')
  const destination = redirect.destination.replace(/:(\w+)/, 'sample-slug')

  test(`${source} redirects to ${destination}`, async ({ page }) => {
    const response = await page.goto(source)

    expect(new URL(page.url()).pathname, `${source} destination`).toBe(destination)
    expect(response?.status(), `${source} final status`).toBe(200)
  })
}

test('redirects use 301, matching the Blade site', async ({ request }) => {
  const response = await request.get('/our-works', { maxRedirects: 0 })

  // Next's `permanent: true` emits 308; the Blade site emits 301. Matching
  // exactly keeps the pre-cutover parity crawl clean.
  expect(response.status()).toBe(301)
})

test('/portfolio is not swallowed by the /portfolio/:slug wildcard', async ({ page }) => {
  await page.goto('/portfolio')

  expect(new URL(page.url()).pathname).toBe('/portfolio')
  await expect(page.locator('[data-work-tile]').first()).toBeVisible()
})

test('revalidate rejects a wrong secret', async ({ request }) => {
  const response = await request.post('/api/revalidate', {
    data: { tags: ['works'], secret: 'wrong' },
  })

  expect(response.status()).toBe(401)
})

test('revalidate rejects malformed json', async ({ request }) => {
  const response = await request.post('/api/revalidate', {
    headers: { 'Content-Type': 'application/json' },
    // Raw bytes, not `data:` — Playwright would JSON-encode a string argument
    // into a valid document and the body would parse fine.
    data: Buffer.from('{ this is not json'),
  })

  expect(response.status()).toBe(400)
})

test('revalidate accepts the right secret and reports the tags', async ({ request }) => {
  test.skip(!process.env.REVALIDATE_SECRET, 'REVALIDATE_SECRET not set')

  const response = await request.post('/api/revalidate', {
    data: { tags: ['works', 'pages:home'], secret: process.env.REVALIDATE_SECRET },
  })

  expect(response.status()).toBe(200)
  expect((await response.json()).revalidated).toEqual(['works', 'pages:home'])
})
