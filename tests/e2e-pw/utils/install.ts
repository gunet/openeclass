import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';
import type { Credentials } from './auth';
import { isInstalled } from './stack';

/** Same defaults as docker-compose.development.yaml. */
export const DB = {
  host: process.env.ECLASS_DB_HOST || 'db',
  user: process.env.ECLASS_DB_USER || 'root',
  password: process.env.ECLASS_DB_PASSWORD || 'secret',
  name: process.env.ECLASS_DB_NAME || 'eclass',
};

/**
 * Installs the site through the web wizard (install/index.php), keeping the defaults of
 * every step except the DB, the site URL and the admin account. Uses only field names,
 * so it works in any interface language.
 */
export async function runWizard(page: Page, baseURL: string, admin: Credentials): Promise<void> {
  const step = async (name: string, next: string) => {
    await page.locator(`input[name="${name}"]`).click();
    await expect(page.locator(`input[name="${next}"]`)).toBeVisible();
  };

  await page.goto('/install/');
  await step('install1', 'install2'); // requirements
  await step('install2', 'install3'); // license
  await step('install3', 'install4'); // accept license → DB settings

  await page.fill('#dbHostForm', DB.host);
  await page.fill('#dbUsernameForm', DB.user);
  await page.fill('#dbPassForm', DB.password);
  await page.fill('#dbNameForm', DB.name);
  await step('install4', 'install5'); // → site settings

  await page.fill('#urlForm', new URL('/', baseURL).href);
  await page.fill('#nameForm', 'Admin User');
  await page.fill('#emailForm', 'admin@example.com');
  await page.fill('#loginForm', admin.username);
  await page.fill('#passForm', admin.password);
  await step('install5', 'install6'); // → theme
  await step('install6', 'install7'); // → email settings
  await step('install7', 'install8'); // → last check

  await page.locator('input[name="install8"]').click(); // creates the DB and config/config.php
  await expect.poll(isInstalled, { message: 'config/config.php exists', timeout: 60_000 }).toBe(true);
}
