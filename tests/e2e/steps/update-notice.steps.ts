import { createBdd } from 'playwright-bdd';
import { test } from 'playwright-bdd';
import { expect } from '@playwright/test';
import { readState, putDropin, removeDropin } from '../lib/pantheon';

const { Given, When, Then, After } = createBdd(test);

const FILTER_DROPIN = 'zz-e2e-show-filter.php';
const CONSTANT_DROPIN = 'zz-e2e-hide-constant.php';

/**
 * Sandbox-plan Pantheon sites gate every pantheonsite.io page behind a
 * "hosted in a sandbox environment" interstitial with a Continue button.
 * Click through it if present (sets a cookie, so later loads pass).
 */
async function dismissSandboxGate(page: import('@playwright/test').Page): Promise<void> {
  const cont = page.getByRole('button', { name: 'Continue' });
  if (await cont.isVisible().catch(() => false)) {
    await cont.click();
    await page.waitForLoadState('load');
  }
}

Given('I am logged in to WordPress admin', async ({ page }) => {
  await page.goto('/wp-login.php');
  await dismissSandboxGate(page);
  await page.fill('#user_login', process.env.WP_USER!);
  await page.fill('#user_pass', process.env.WP_PASSWORD!);
  await page.click('#wp-submit');
  await dismissSandboxGate(page);
  await expect(page.locator('#wpadminbar')).toBeVisible();
});

When('I open the WordPress admin page {string}', async ({ page }, adminPath: string) => {
  await page.goto(adminPath);
  await dismissSandboxGate(page);
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
