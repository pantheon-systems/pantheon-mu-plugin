import { FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { deleteMultidev } from './lib/pantheon';

const STATE_FILE = path.join(__dirname, '.test-state.json');

async function globalTeardown(_config: FullConfig): Promise<void> {
  if (!fs.existsSync(STATE_FILE)) return;
  const { multidev } = JSON.parse(fs.readFileSync(STATE_FILE, 'utf8')) as { multidev: string };
  if (!multidev) return;

  if (process.env.KEEP_MULTIDEV === 'true') {
    console.log(`[teardown] KEEP_MULTIDEV set, leaving ${multidev} up`);
    return;
  }

  console.log(`[teardown] deleting multidev ${multidev}`);
  try {
    deleteMultidev(multidev);
  } finally {
    fs.rmSync(STATE_FILE, { force: true });
  }
}

export default globalTeardown;
