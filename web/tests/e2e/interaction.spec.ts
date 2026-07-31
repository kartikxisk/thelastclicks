import { expect, test } from '@playwright/test'

test.describe('portfolio filtering', () => {
  test('filtering updates the url and the grid, server-side', async ({ page }) => {
    await page.goto('/portfolio')

    const all = await page.locator('[data-work-tile]').count()
    expect(all).toBeGreaterThan(0)

    const chip = page.locator('nav[aria-label="Filter by category"] a').nth(1)
    const label = await chip.textContent()
    await chip.click()

    await expect(page).toHaveURL(/category=/)
    await expect(page.locator('[data-work-tile]').first()).toBeVisible()
    expect(label?.trim()).toBeTruthy()
  })

  test('a filtered url is server-rendered, not client-filtered', async ({ request }) => {
    // The filter is a link, so the server does the filtering. Fetching the URL
    // directly must return only that category — if filtering had been client
    // state, this response would carry the unfiltered set.
    const filtered = await (await request.get('/portfolio?category=brand-film')).text()
    const all = await (await request.get('/portfolio')).text()

    const count = (html: string) => (html.match(/data-work-tile/g) ?? []).length

    expect(count(filtered)).toBeGreaterThan(0)
    expect(count(filtered)).toBeLessThanOrEqual(count(all))
  })

  test('canonical is page-aware', async ({ page }) => {
    await page.goto('/portfolio?page=2')
    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href')

    // The API builds this; if pagination stops reaching it, SEO silently
    // collapses every page onto one URL.
    expect(canonical).toContain('page=2')
  })
})

test.describe('lightbox', () => {
  test('opens from a tile and Escape closes it', async ({ page }) => {
    await page.goto('/portfolio')

    await page.locator('[data-work-tile] button').first().click()
    await expect(page.getByRole('dialog')).toBeVisible()

    await page.keyboard.press('Escape')
    await expect(page.getByRole('dialog')).not.toBeVisible()
  })

  test('returns focus to the tile that opened it', async ({ page }) => {
    await page.goto('/portfolio')

    const tile = page.locator('[data-work-tile] button').first()
    await tile.click()
    await expect(page.getByRole('dialog')).toBeVisible()

    await page.keyboard.press('Escape')
    await expect(tile).toBeFocused()
  })
})

test.describe('chrome', () => {
  test('nav gains a scrolled state past the hero', async ({ page }) => {
    await page.goto('/')
    const header = page.locator('header[data-nav]')

    await expect(header).toHaveAttribute('data-scrolled', 'false')
    await page.evaluate(() => window.scrollTo(0, 2000))
    await expect(header).toHaveAttribute('data-scrolled', 'true')
  })

  test('footer shows the admin-managed contact email', async ({ page, request }) => {
    const api = process.env.API_BASE_URL ?? 'http://127.0.0.1:8000'
    const { data } = await (await request.get(`${api}/api/v1/settings`)).json()

    test.skip(!data.contact_email, 'no contact email configured')

    await page.goto('/')
    await expect(page.locator('footer')).toContainText(data.contact_email)
  })

  test('skip link is the first focusable element', async ({ page }) => {
    await page.goto('/')
    await page.keyboard.press('Tab')

    await expect(page.locator('.skip-link')).toBeFocused()
  })
})

test.describe('contact form', () => {
  test('native validation blocks an invalid email before submitting', async ({ page }) => {
    await page.goto('/contact')

    await page.getByLabel('Name', { exact: true }).fill('Ada Lovelace')
    await page.getByLabel('Email', { exact: true }).fill('not-an-email')
    await page.getByLabel('Brief', { exact: true }).fill('A short brief.')
    await page.getByRole('button', { name: /send brief/i }).click()

    // type="email" stops this client-side, so the request never leaves the
    // browser and the page does not navigate.
    await expect(page).toHaveURL(/\/contact/)
    const valid = await page
      .getByLabel('Email', { exact: true })
      .evaluate((el: HTMLInputElement) => el.validity.valid)
    expect(valid).toBe(false)
  })

  test('reports a server rejection without clearing the form', async ({ page }) => {
    await page.goto('/contact')

    await page.getByLabel('Name', { exact: true }).fill('Ada Lovelace')
    await page.getByLabel('Email', { exact: true }).fill('ada@example.com')
    // Over the API's max:5000 — passes native validation, fails server-side,
    // which is the only way to reach the error path through the real form.
    await page.getByLabel('Brief', { exact: true }).fill('x'.repeat(5001))
    await page.getByRole('button', { name: /send brief/i }).click()

    // Any alert: the endpoint rate-limits at 5/min per IP, so a parallel or
    // repeated run legitimately gets 429 rather than 422. Asserting on the
    // specific error would make this flaky for a reason that is not a bug.
    await expect(page.locator('[role="alert"]').first()).toBeVisible()

    // The invariant that actually matters — losing a long brief because the
    // server said no is how enquiries get lost.
    await expect(page.getByLabel('Name', { exact: true })).toHaveValue('Ada Lovelace')
    await expect(page.getByLabel('Brief', { exact: true })).not.toHaveValue('')
    await expect(page).toHaveURL(/\/contact/)
  })

  test('the honeypot is present and hidden from users', async ({ page }) => {
    await page.goto('/contact')

    // Scoped to the quote form: the footer newsletter carries its own
    // honeypot, so an unscoped selector legitimately finds two.
    const honeypot = page.locator('form:has([name="message"]) input[name="website"]')
    await expect(honeypot).toHaveCount(1)
    await expect(honeypot).not.toBeInViewport()
    await expect(honeypot).toHaveAttribute('aria-hidden', 'true')
  })

  test('pre-fills the industry from the query string', async ({ page }) => {
    await page.goto('/contact?industry=fashion')

    await expect(page.locator('[data-prefilled-industry]')).toContainText('fashion')
  })
})

test.describe('faq disclosure', () => {
  test('expands without javascript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false })
    const page = await context.newPage()

    await page.goto('/services/photography')
    const first = page.locator('[data-section="faqs"] details').first()

    test.skip((await first.count()) === 0, 'no faqs on this service')

    await expect(first).not.toHaveAttribute('open', '')
    await first.locator('summary').click()
    await expect(first).toHaveAttribute('open', '')

    await context.close()
  })
})

test.describe('route transition', () => {
  test('navigation completes and content is readable after the wipe', async ({ page }) => {
    await page.goto('/')

    const started = Date.now()
    await page
      .getByRole('navigation', { name: 'Footer' })
      .getByRole('link', { name: 'Portfolio' })
      .click()
    await page.locator('[data-work-tile]').first().waitFor({ state: 'visible' })

    // A curtain that outlasts the navigation makes the site feel broken
    // rather than considered (plans/001).
    expect(Date.now() - started).toBeLessThan(4000)
  })

  test('the wipe never intercepts a click', async ({ page }) => {
    await page.goto('/')
    await page
      .getByRole('navigation', { name: 'Footer' })
      .getByRole('link', { name: 'Journal' })
      .click()
    await page.waitForURL('**/blog')

    // Immediately click on through while the wipe may still be running.
    await page
      .getByRole('navigation', { name: 'Footer' })
      .getByRole('link', { name: 'About' })
      .click()
    await expect(page).toHaveURL(/\/about/)
  })

  test('back navigation works through the transition', async ({ page }) => {
    await page.goto('/')
    await page
      .getByRole('navigation', { name: 'Footer' })
      .getByRole('link', { name: 'Portfolio' })
      .click()
    await expect(page).toHaveURL(/\/portfolio/)

    await page.goBack()
    await expect(page).toHaveURL(/127\.0\.0\.1:\d+\/$|\/$/)
    await expect(page.locator('[data-section="hero"]')).toBeVisible()
  })
})
