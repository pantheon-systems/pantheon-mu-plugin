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

async function globalSetup(_config: FullConfig): Promise<void> {
  const env = generateMultidevName();
  const url = multidevUrl(env);

  console.log(`[setup] creating multidev ${env}`);
  createMultidev(env); // terminus blocks until the environment is created

  console.log('[setup] installing branch mu-plugin over SFTP');
  switchToSftp(env);
  installBranchPlugin(env);
  commitEnv(env, 'e2e: install SITE-5884 branch mu-plugin under test');
  clearCache(env);

  fs.writeFileSync(STATE_FILE, JSON.stringify({ multidev: env, url }, null, 2));
  writeWpUrl(url);
  console.log(`[setup] ready: ${url}`);
}

export default globalSetup;
