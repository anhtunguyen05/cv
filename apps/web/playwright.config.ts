import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: './tests/e2e',
  use: {
    baseURL: `http://127.0.0.1:${process.env.E1_WEB_PORT || '4173'}`,
    trace: 'on-first-retry',
  },
  webServer: {
    command: `npm run dev -- --host 127.0.0.1 --port ${process.env.E1_WEB_PORT || '4173'}`,
    url: `http://127.0.0.1:${process.env.E1_WEB_PORT || '4173'}`,
    reuseExistingServer: true,
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
})
