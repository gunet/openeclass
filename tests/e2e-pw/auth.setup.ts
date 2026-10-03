import { test as setup, expect } from '@playwright/test';
import { login, STATE, USERS } from './utils/auth';
import type { Role } from './utils/auth';
import { runWizard } from './utils/install';
import { hasSnapshot, isInstalled, snapshot, useMailpit } from './utils/stack';

/**
 * Gets the e2e stack ready, then logs each account in once and stores the
 * session, so specs never race on the login form.
 */
setup.describe.configure({ mode: 'serial' });

setup('install and configure the site', async ({ page, baseURL }) => {
  if (!isInstalled()) {
    await runWizard(page, baseURL!, USERS.admin);
  }
  expect(isInstalled(), 'config/config.php exists').toBe(true);
  useMailpit();
  // Baseline to restore to; the harness seed (0.4) will add its own snapshot on top.
  if (!hasSnapshot('installed')) {
    snapshot('installed');
  }
});

for (const role of Object.keys(USERS) as Role[]) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    await login(page, USERS[role]);
    await page.context().storageState({ path: STATE[role] });
  });
}
