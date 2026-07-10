import { createBdd } from 'playwright-bdd';
import { test } from 'playwright-bdd';
import { expect } from '@playwright/test';
import { readState, wpOption } from '../lib/pantheon';

const { Given, When, Then, After } = createBdd(test);

// The installed E2E shim activates the real filter/constant based on these WP
// options, so scenarios toggle them with a fast DB write (no per-test deploy).
const FILTER_OPTION = 'e2e_hide_via_filter';
const CONSTANT_OPTION = 'e2e_hide_via_constant';

// The Pantheon sandbox interstitial is bypassed via the Deterrence-Bypass HTTP
// header set in playwright.config.ts (use.extraHTTPHeaders), so no page load
// hits the gate and steps can navigate straight to wp-admin.

Given('I am logged in to WordPress admin', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.WP_USER!);
  await page.fill('#user_pass', process.env.WP_PASSWORD!);
  await page.click('#wp-submit');
  await expect(page.locator('#wpadminbar')).toBeVisible();
});

When('I open the WordPress admin page {string}', async ({ page }, adminPath: string) => {
  await page.goto(adminPath);
});

Then('the element {string} should be visible', async ({ page }, selector: string) => {
  await expect(page.locator(selector)).toBeVisible();
});

Then('the element {string} should be hidden', async ({ page }, selector: string) => {
  await expect(page.locator(selector)).toBeHidden();
});

When('I apply the CSS {string}', async ({ page }, css: string) => {
  await page.addStyleTag({ content: css });
});

When('the pantheon_show_update_notice filter returns false', async () => {
  const { multidev } = readState();
  wpOption(multidev, FILTER_OPTION, '1');
});

When('the PANTHEON_HIDE_UPDATE_NOTICE constant is set to true', async () => {
  const { multidev } = readState();
  wpOption(multidev, CONSTANT_OPTION, '1');
});

// Reset both toggle options after each scenario so scenarios stay isolated on
// the shared multidev (a DB write, no deploy).
After(async () => {
  const { multidev } = readState();
  wpOption(multidev, FILTER_OPTION, '0');
  wpOption(multidev, CONSTANT_OPTION, '0');
});
