import { expect, test } from '@playwright/test'

/**
 * Every public route, asserted for the things that break silently: status,
 * a single h1, a non-empty title, and a canonical.
 *
 * Titles come from admin-managed SeoPage rows, so an empty one here means a
 * row went missing rather than a template bug.
 */
const ROUTES = [
  '/',
  '/about',
  '/portfolio',
  '/industries',
  '/blog',
  '/contact',
  '/thank-you',
  '/privacy-policy',
  '/terms-of-service',
  '/cookie-policy',
  '/disclaimer',
  '/services/photography',
  '/services/videography',
  '/services/editing',
]

for (const path of ROUTES) {
  test(`${path} renders with metadata`, async ({ page }) => {
    const response = await page.goto(path)

    expect(response?.status(), `${path} status`).toBe(200)
    await expect(page.locator('h1'), `${path} h1 count`).toHaveCount(1)
    expect(await page.title(), `${path} title`).not.toBe('')

    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href')
    expect(canonical, `${path} canonical`).toBeTruthy()
  })
}

test('every image carries an alt attribute', async ({ page }) => {
  for (const path of ['/', '/portfolio', '/industries', '/blog']) {
    await page.goto(path)
    const missing = await page.locator('img:not([alt])').count()
    expect(missing, `${path} images without alt`).toBe(0)
  }
})

test('no console errors on the homepage', async ({ page }) => {
  const errors: string[] = []
  page.on('console', (message) => {
    if (message.type() !== 'error') return

    // CDN CORS rejections are an artefact of running from 127.0.0.1, which is
    // not in CloudFront's allowed origins — the production domain is. Media
    // still renders here via the poster fallback, so failing on these would
    // report an environment difference as a defect.
    if (/CORS|ERR_FAILED|cloudfront/i.test(message.text())) return

    errors.push(message.text())
  })

  await page.goto('/')
  await page.waitForLoadState('networkidle')

  expect(errors).toEqual([])
})

test('an unknown route renders the 404 page', async ({ page }) => {
  const response = await page.goto('/definitely-not-a-page')

  expect(response?.status()).toBe(404)
  await expect(page.locator('h1')).toBeVisible()
})

test('unknown detail slugs 404 rather than erroring', async ({ page }) => {
  for (const path of ['/services/not-a-service', '/blog/not-a-post']) {
    const response = await page.goto(path)
    expect(response?.status(), path).toBe(404)
  }
})

test('fully static routes render without JavaScript', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false })
  const page = await context.newPage()

  // Routes that read no searchParams are prerendered whole, so every word is
  // visible with JS off. Motion and WebGL are decoration on top of this.
  for (const path of ['/', '/industries', '/about', '/services/photography']) {
    await page.goto(path)
    await expect(page.locator('h1'), `${path} h1`).toBeVisible()
  }

  await page.goto('/')
  await expect(page.locator('[data-section="services"]')).toBeVisible()
  await expect(page.locator('[data-work-tile]').first()).toBeVisible()

  await context.close()
})

test('suspended routes ship their content in the HTML payload', async ({ request }) => {
  // /portfolio and /blog read searchParams, so their content streams into a
  // Suspense boundary: it is present in the response body but sits in a
  // hidden container until an inline script swaps it in. Crawlers that run JS
  // (Googlebot does) see it; a JS-disabled browser sees the skeleton.
  //
  // Asserting on the payload rather than on visibility records that tradeoff
  // honestly instead of pretending it does not exist.
  const markers = [
    ['/portfolio', 'data-work-tile'],
    ['/blog', 'data-post-card'],
  ] as const

  for (const [path, marker] of markers) {
    const body = await (await request.get(path)).text()

    expect(body, `${path} carries its content`).toContain(marker)
  }
})
