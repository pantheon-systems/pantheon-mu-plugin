import { FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import {
  createMultidev,
  switchToSftp,
  installBranchPlugin,
  commitEnv,
  clearCache,
  generateMultidevName,
  multidevUrl,
} from './lib/pantheon';

const STATE_FILE = path.join(__dirname, '.test-state.json');
const ENV_FILE = path.join(__dirname, '.env');

/** Set (or replace) WP_URL in .env so each Playwright worker resolves baseURL. */
function writeWpUrl(url: string): void {
  let body = '';
  try {
    body = fs.readFileSync(ENV_FILE, 'utf8');
  } catch {
    // no .env yet; start empty
  }
  body = body.replace(/^WP_URL=.*$/m, '').replace(/\n{2,}$/, '\n').trimEnd();
  fs.writeFileSync(ENV_FILE, `${body}\nWP_URL=${url}\n`);
}

function log(msg: string): void {
  console.log(`[e2e setup] ${msg}`);
}

async function globalSetup(_config: FullConfig): Promise<void> {
  const env = generateMultidevName();
  const url = multidevUrl(env);
  const site = process.env.TERMINUS_SITE || 'pantheon-mu-plugin';

  log(`Provisioning ephemeral multidev "${env}" from ${site}.dev`);

  log('Step 1/5: creating multidev (clones code + DB, runs platform workflows; ~2-5 min)...');
  createMultidev(env);
  log(`Step 1/5 done: multidev is up -> ${url}`);

  log('Step 2/5: switching connection mode to SFTP...');
  switchToSftp(env);
  log('Step 2/5 done: SFTP enabled');

  log('Step 3/5: uploading branch plugin (functions.php, pantheon-updates.php) + E2E option shim over SFTP...');
  installBranchPlugin(env);
  log('Step 3/5 done: files uploaded');

  log('Step 4/5: committing code and waiting for the deploy workflow...');
  commitEnv(env, 'e2e: install SITE-5884 branch mu-plugin under test');
  log('Step 4/5 done: code deployed');

  log('Step 5/5: clearing cache (best-effort)...');
  clearCache(env);
  log('Step 5/5 done');

  fs.writeFileSync(STATE_FILE, JSON.stringify({ multidev: env, url }, null, 2));
  writeWpUrl(url);
  log(`Environment ready: ${url} - running scenarios next`);
}

export default globalSetup;
