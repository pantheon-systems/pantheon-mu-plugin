import { execSync } from 'child_process';
import * as fs from 'fs';
import * as os from 'os';
import * as path from 'path';

/** Reject anything that isn't a plain Pantheon site/env identifier before it
 *  reaches a shell command (guards against command injection via config). */
function assertSafeName(kind: string, value: string): string {
  if (!/^[a-z0-9][a-z0-9-]*$/i.test(value)) {
    throw new Error(`Unsafe ${kind}: ${JSON.stringify(value)}`);
  }
  return value;
}

const SITE = assertSafeName('TERMINUS_SITE', process.env.TERMINUS_SITE || 'pantheon-mu-plugin');
const SOURCE_ENV = assertSafeName('TERMINUS_SOURCE_ENV', process.env.TERMINUS_SOURCE_ENV || 'dev');

/** Path to the branch mu-plugin source (repo root /inc), relative to tests/e2e. */
const PLUGIN_SRC = path.resolve(__dirname, '..', '..', '..', 'inc');
/** Where the plugin loads from on the Pantheon appserver. */
const REMOTE_INC = 'code/wp-content/mu-plugins/pantheon-mu-plugin/inc';

const TERMINUS_ENV = { ...process.env, TERMINUS_HIDE_UPDATE_MESSAGE: '1' };

export function terminus(args: string, timeoutMs = 180000): string {
  return execSync(`terminus ${args}`, {
    encoding: 'utf8',
    env: TERMINUS_ENV,
    timeout: timeoutMs,
    stdio: ['ignore', 'pipe', 'pipe'],
  });
}

/** Run wp-cli on a Pantheon environment via terminus. */
export function wp(env: string, cmd: string, timeoutMs = 120000): string {
  return terminus(`wp ${SITE}.${env} -- ${cmd}`, timeoutMs);
}

export function siteEnv(env: string): string {
  return `${SITE}.${env}`;
}

export function multidevUrl(env: string): string {
  return `https://${env}-${SITE}.pantheonsite.io`;
}

/** Generate a Pantheon-legal multidev name (<=11 chars, lowercase alnum). */
export function generateMultidevName(): string {
  const stamp = Date.now().toString(36).slice(-8);
  return `e2e${stamp}`.slice(0, 11);
}

/** Switch an env to SFTP mode, retrying (the connection flip can lag after create). */
export function switchToSftp(env: string, attempts = 8): void {
  for (let i = 0; i < attempts; i++) {
    try {
      terminus(`connection:set ${SITE}.${env} sftp`, 60000);
      return;
    } catch {
      if (i === attempts - 1) throw new Error(`Could not switch ${env} to SFTP`);
      execSync('sleep 20');
    }
  }
}

/** Run a batch of sftp commands against an env (non-interactive). */
export function sftpBatch(env: string, commands: string[]): void {
  const sftpCmd = terminus(`connection:info ${SITE}.${env} --field=sftp_command`, 60000).trim();
  // sftpCmd looks like: sftp -o Port=2222 dev.<uuid>@appserver.<env>.<uuid>.drush.in
  const sftpWithOpts = sftpCmd.replace(
    /^sftp /,
    'sftp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -b - '
  );
  execSync(sftpWithOpts, {
    input: [...commands, 'bye'].join('\n'),
    stdio: ['pipe', 'pipe', 'pipe'],
    timeout: 120000,
  });
}

const MU_DIR = 'code/wp-content/mu-plugins';
const SHIM_FILENAME = 'zz-e2e-shim.php';

// E2E-only shim: activates the real hide mechanisms from WP options, so
// scenarios toggle them with a DB write (fast) instead of a per-test code deploy.
const SHIM_PHP = `<?php
// E2E test shim (SITE-5884) - installed only on ephemeral test multidevs.
if ( get_option( 'e2e_hide_via_filter' ) ) {
\tadd_filter( 'pantheon_show_update_notice', '__return_false' );
}
if ( get_option( 'e2e_hide_via_constant' ) && ! defined( 'PANTHEON_HIDE_UPDATE_NOTICE' ) ) {
\tdefine( 'PANTHEON_HIDE_UPDATE_NOTICE', true );
}
`;

/** SFTP the branch plugin files + the option-toggle shim onto the env. */
export function installBranchPlugin(env: string): void {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'e2e-shim-'));
  const shim = path.join(dir, SHIM_FILENAME);
  fs.writeFileSync(shim, SHIM_PHP);
  sftpBatch(env, [
    `put ${PLUGIN_SRC}/functions.php ${REMOTE_INC}/functions.php`,
    `put ${PLUGIN_SRC}/pantheon-updates.php ${REMOTE_INC}/pantheon-updates.php`,
    `put ${shim} ${MU_DIR}/${SHIM_FILENAME}`,
  ]);
}

/** Read the multidev name/url written by global-setup. */
export function readState(): { multidev: string; url: string } {
  const f = path.join(__dirname, '..', '.test-state.json');
  return JSON.parse(fs.readFileSync(f, 'utf8'));
}

/** Set a WP option on the env (a DB write - no deploy, no workflow). */
export function wpOption(env: string, key: string, value: string): void {
  terminus(`wp ${SITE}.${env} -- option update ${key} ${value}`, 120000);
}

/** Wait for the env's code-sync workflow to complete (default max 180s). */
export function workflowWait(env: string): void {
  terminus(`workflow:wait ${SITE}.${env} --max=180`, 210000);
}

export function commitEnv(env: string, message: string): void {
  terminus(`env:commit ${SITE}.${env} --message="${message}"`, 180000);
  // Wait out the deploy workflow so the next commit/clear-cache can't race it.
  workflowWait(env);
}

export function clearCache(env: string): void {
  // Non-fatal: a commit already redeploys code and clears opcache, and wp-admin
  // is not edge-cached, so a failed clear-cache must not abort setup/steps.
  try {
    terminus(`env:clear-cache ${SITE}.${env}`, 120000);
  } catch (e) {
    console.warn(`[warn] clear-cache failed, continuing: ${(e as Error).message.split('\n')[0]}`);
  }
}

export function deleteMultidev(env: string): void {
  terminus(`multidev:delete ${SITE}.${env} --delete-branch --yes`, 180000);
}

export function createMultidev(env: string): void {
  // Multidev creation clones code + DB and runs workflows; can exceed 5 min.
  // terminus waits for the create workflow itself, so no workflow:wait here
  // (there is no code-sync to wait for, and workflow:wait would idle to --max).
  terminus(`multidev:create ${SITE}.${SOURCE_ENV} ${env}`, 600000);
}
