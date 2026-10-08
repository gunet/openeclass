import { test as setup, expect } from '@playwright/test';
import { SESSION_ROLES, STATE, USERS } from './utils/auth';
import { login, userMenu } from './utils/eclass';
import { Harness } from './utils/harness';
import { runWizard } from './utils/install';
import { SEED, SEEDED_SNAPSHOT } from './utils/seed';
import { hasSnapshot, isInstalled, restore, snapshot } from './utils/stack';

/**
 * Gets the e2e stack ready, then logs each account in once and stores the session,
 * so specs never race on the login form:
 *
 * 1. install through the web wizard if needed, and keep an `installed` snapshot;
 * 2. seed the accounts and courses (utils/seed.ts) on top of `installed`, once per version of the seed
 *    data, and keep that as the seeded snapshot;
 * 3. start every run from the seeded snapshot, with mail going to mailpit;
 * 4. store a session per role in `.auth/` (in English).
 */
setup.describe.configure({ mode: 'serial' });

setup('install, seed and reset the site', async ({ page, baseURL }) => {
  if (!isInstalled()) {
    await runWizard(page, baseURL!, USERS.admin);
  }
  expect(isInstalled(), 'config/config.php exists').toBe(true);
  if (!hasSnapshot('installed')) {
    snapshot('installed');
  }

  const { harness, dispose } = await Harness.create(baseURL!);
  try {
    if (!hasSnapshot(SEEDED_SNAPSHOT)) {
      restore('installed');
      await harness.seed(SEED);
      snapshot(SEEDED_SNAPSHOT);
    }
    await harness.reset({ snapshot: SEEDED_SNAPSHOT });
  } finally {
    await dispose();
  }
});

for (const role of SESSION_ROLES) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    await login(page, USERS[role]);
    await expect(userMenu(page)).toBeVisible();
    await page.context().storageState({ path: STATE[role] });
  });
}
