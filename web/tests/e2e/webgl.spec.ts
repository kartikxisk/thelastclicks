import { expect, test } from '@playwright/test'

/*
 * Serial, and marked slow.
 *
 * The canvas mounts only after first paint plus a dynamic import of three,
 * fiber and drei — hundreds of kilobytes to fetch, parse and compile. Under
 * full parallelism these compete with the rest of the suite for one Node
 * server and time out for reasons that are not defects. One worker and a
 * longer budget make them measure what they are meant to.
 */
test.describe.configure({ mode: 'serial' })
test.slow()

type TierWindow = { __webglTier?: string }

test('no webgl bytes on the initial load', async ({ page }) => {
  const scripts: string[] = []
  page.on('request', (request) => {
    if (request.resourceType() === 'script') scripts.push(request.url())
  })

  await page.goto('/', { waitUntil: 'domcontentloaded' })

  // three alone is ~600KB. Shipping it in the initial chunk is the fastest way
  // to blow the LCP budget on mobile, so this is the promise the whole
  // dynamic-import arrangement exists to keep.
  const eager = scripts.filter((url) => /three|fiber|drei|postprocessing/i.test(url))
  expect(eager, `WebGL loaded eagerly: ${eager.join(', ')}`).toEqual([])
})

test('a single canvas mounts after first paint', async ({ page }) => {
  await page.goto('/')

  // It must eventually arrive, or this suite would pass on a build where the
  // canvas silently never loads.
  await expect(page.locator('canvas')).toHaveCount(1, { timeout: 15_000 })
})

test('the canvas survives client-side navigation', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('canvas')).toHaveCount(1, { timeout: 15_000 })

  // Mark the element, navigate, and check the same node is still there. The
  // canvas lives in the layout, above the <Activity> boundary Cache Components
  // wraps routes in, so it should never be torn down.
  await page.evaluate(() => {
    ;(document.querySelector('canvas') as HTMLCanvasElement & { __id?: string }).__id = 'marked'
  })

  // Footer nav, not the header: the primary nav is hidden below md, so a
  // header selector would only work on the desktop project.
  await page
    .getByRole('navigation', { name: 'Footer' })
    .getByRole('link', { name: 'Portfolio' })
    .click()
  await page.waitForURL('**/portfolio')

  const survived = await page.evaluate(
    () => (document.querySelector('canvas') as HTMLCanvasElement & { __id?: string })?.__id
  )

  expect(survived).toBe('marked')
  await expect(page.locator('canvas')).toHaveCount(1)
})

test('resolves the full tier on desktop', async ({ page }) => {
  test.skip(test.info().project.name !== 'desktop', 'desktop only')

  await page.goto('/')
  await page.waitForFunction(() => (window as TierWindow).__webglTier !== undefined)

  expect(await page.evaluate(() => (window as TierWindow).__webglTier)).toBe('full')
})

test('resolves the reduced tier on a coarse pointer', async ({ page }) => {
  test.skip(test.info().project.name !== 'mobile', 'mobile only')

  await page.goto('/')
  await page.waitForFunction(() => (window as TierWindow).__webglTier !== undefined)

  expect(await page.evaluate(() => (window as TierWindow).__webglTier)).toBe('reduced')
})

test('mounts nothing under reduced motion', async ({ browser }) => {
  const context = await browser.newContext({ reducedMotion: 'reduce' })
  const page = await context.newPage()

  await page.goto('/')
  await page.waitForFunction(() => (window as TierWindow).__webglTier !== undefined)

  expect(await page.evaluate(() => (window as TierWindow).__webglTier)).toBe('off')
  await expect(page.locator('canvas')).toHaveCount(0)

  await context.close()
})

test('page content is server-rendered without the canvas', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false })
  const page = await context.newPage()

  await page.goto('/')

  // The canvas is decoration on top of content that already exists.
  await expect(page.locator('h1')).toBeVisible()
  await expect(page.locator('[data-section="services"]')).toBeVisible()
  await expect(page.locator('canvas')).toHaveCount(0)

  await context.close()
})

test('transient state does not survive navigation', async ({ page }) => {
  // Arrive at /portfolio through a client-side navigation, so that going back
  // and forward stays client-side and Activity is actually exercised.
  await page.goto('/blog')
  await page
    .getByRole('navigation', { name: 'Footer' })
    .getByRole('link', { name: 'Portfolio' })
    .click()
  await page.waitForURL('**/portfolio')

  await page.locator('[data-work-tile] button').first().click()
  await expect(page.getByRole('dialog')).toBeVisible()

  // Browser back/forward rather than a nav click: the open dialog covers the
  // footer, so no link is reachable — which is also exactly how a real user
  // leaves a modal they did not mean to open.
  await page.goBack()
  await page.waitForURL('**/blog')
  await page.goForward()
  await page.waitForURL('**/portfolio')

  // Cache Components keeps the hidden route mounted, so without the explicit
  // reset in WorkGallery the lightbox would still be open over the grid.
  await expect(page.getByRole('dialog')).not.toBeVisible()
})
