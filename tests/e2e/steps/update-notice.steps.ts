import { createBdd } from 'playwright-bdd';
import { test } from 'playwright-bdd';
import { expect } from '@playwright/test';
import { readState, wpOption, wp } from '../lib/pantheon';

const { Given, When, Then, After } = createBdd(test);

// The installed E2E shim activates the real filter/constant based on these WP
// options, so scenarios toggle them with a fast DB write (no per-test deploy).
const FILTER_OPTION = 'e2e_hide_via_filter';
const CONSTANT_OPTION = 'e2e_hide_via_constant';
// The shim also forces a core update to appear (and at which version) so the
// dismissible update-available notice renders deterministically.
const FORCE_OPTION = 'e2e_force_update_available';
const VERSION_OPTION = 'e2e_forced_version';
const DISMISSED_META = 'pantheon_dismissed_update_notice';

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

When('the PANTHEON_SHOW_UPDATE_NOTICE constant is set to false', async () => {
  const { multidev } = readState();
  wpOption(multidev, CONSTANT_OPTION, '1');
});

Given('a WordPress core update is available', async () => {
  const { multidev } = readState();
  wpOption(multidev, FORCE_OPTION, '1');
  wpOption(multidev, VERSION_OPTION, '99.0.0');
});

When('a newer WordPress core update is released', async () => {
  const { multidev } = readState();
  wpOption(multidev, VERSION_OPTION, '100.0.0');
});

When('I dismiss the update notice', async ({ page }) => {
  // WordPress hides the notice client-side on click; wait for our AJAX POST so
  // the dismissal is persisted server-side before the next page load.
  await Promise.all([
    page.waitForResponse(
      (r) =>
        r.url().includes('admin-ajax.php') &&
        (r.request().postData() || '').includes('pantheon_dismiss_update_notice')
    ),
    page.locator('#pantheon-update-notice .notice-dismiss').click(),
  ]);
});

// Reset toggles + the forced-update state, and clear the per-user dismissal so
// scenarios stay isolated on the shared multidev (DB writes, no deploy).
After(async () => {
  const { multidev } = readState();
  wpOption(multidev, FILTER_OPTION, '0');
  wpOption(multidev, CONSTANT_OPTION, '0');
  wpOption(multidev, FORCE_OPTION, '0');
  try {
    wp(multidev, `user meta delete ${process.env.WP_USER} ${DISMISSED_META}`);
  } catch {
    // Meta may not exist if the scenario never dismissed; ignore.
  }
});
