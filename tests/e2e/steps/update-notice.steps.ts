import { createBdd } from 'playwright-bdd';
import { test } from 'playwright-bdd';
import { expect } from '@playwright/test';
import { readState, putDropin, removeDropin } from '../lib/pantheon';

const { Given, When, Then, After } = createBdd(test);

const FILTER_DROPIN = 'zz-e2e-show-filter.php';
const CONSTANT_DROPIN = 'zz-e2e-hide-constant.php';

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
  putDropin(multidev, FILTER_DROPIN, "<?php\nadd_filter( 'pantheon_show_update_notice', '__return_false' );\n");
});

When('the PANTHEON_HIDE_UPDATE_NOTICE constant is set to true', async () => {
  const { multidev } = readState();
  putDropin(multidev, CONSTANT_DROPIN, "<?php\ndefine( 'PANTHEON_HIDE_UPDATE_NOTICE', true );\n");
});

// Always clear any override drop-ins after each scenario so scenarios stay isolated
// on the shared multidev (tolerant when nothing was added).
After(async () => {
  const { multidev } = readState();
  removeDropin(multidev, FILTER_DROPIN);
  removeDropin(multidev, CONSTANT_DROPIN);
});
