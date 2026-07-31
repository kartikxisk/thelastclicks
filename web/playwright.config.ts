import { defineConfig, devices } from '@playwright/test'

/**
 * Runs against a real built server, not `next dev` — the whole point is to
 * catch what production does. Set E2E_BASE_URL to point at staging instead.
 *
 * The Laravel API must be reachable at API_BASE_URL; these tests assert
 * against real admin content rather than fixtures, so a contract change on the
 * Laravel side fails here rather than surfacing as an empty section later.
 */
export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? 'github' : 'list',

  use: {
    baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:3100',
    trace: 'on-first-retry',
  },

  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile', use: { ...devices['Pixel 7'] } },
  ],

  // Reuse an already-running server when one is up; otherwise build and start.
  webServer: process.env.E2E_BASE_URL
    ? undefined
    : {
        command:
          'npm run build && cp -r .next/static .next/standalone/.next/static && cp -r public .next/standalone/public && PORT=3100 node .next/standalone/server.js',
        url: 'http://127.0.0.1:3100',
        reuseExistingServer: !process.env.CI,
        timeout: 300_000,
      },
})
