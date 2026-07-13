import { defineConfig, devices } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';
import * as dotenv from 'dotenv';

dotenv.config();

const testDir = defineBddConfig({
  features: 'features/**/*.feature',
  steps: 'steps/**/*.ts',
});

const headless = process.env.HEADLESS !== 'false';

export default defineConfig({
  globalSetup: './global-setup.ts',
  globalTeardown: './global-teardown.ts',
  testDir,
  // Scenarios share one multidev and mutate site state, so run serially.
  fullyParallel: false,
  workers: 1,
  retries: process.env.CI ? 2 : 0,
  reporter: [['html', { open: 'never' }], ['list']],
  timeout: 300000,
  use: {
    baseURL: process.env.WP_URL,
    headless,
    // Bypass the Pantheon sandbox interstitial on every request (official method).
    // https://docs.pantheon.io/guides/account-mgmt/plans/site-plans#bypassing-the-interstitial-page-with-an-http-header-during-automated-testing
    extraHTTPHeaders: {
      'Deterrence-Bypass': '1',
    },
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15000,
    navigationTimeout: 30000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1920, height: 1080 } },
    },
  ],
});
