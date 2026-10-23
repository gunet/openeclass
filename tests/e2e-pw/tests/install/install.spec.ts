import { test, expect } from '@playwright/test';
import type { Page } from '@playwright/test';
import { USERS } from '../../utils/auth';
import { runWizard } from '../../utils/install';

async function isInstalled(page: Page): Promise<boolean> {
  await page.goto('/');
  // If installed, / serves the homepage (200), otherwise redirects to not_installed.php
  return !page.url().includes('not_installed.php');
}

test.describe('installation', () => {
  test('redirects to not_installed when config is missing', async ({ page }) => {
    test.skip(await isInstalled(page), 'already installed');
    await page.goto('/');
    await expect(page).toHaveURL(/not_installed\.php\?err=config/);
    await expect(page.locator('text=Οδηγό Εγκατάστασης')).toBeVisible();
  });

  test('navigates to install wizard and shows welcome page', async ({ page }) => {
    test.skip(await isInstalled(page), 'already installed');
    await page.goto('/install/');
    await expect(page.getByRole('heading', { name: /οδηγό εγκατάστασης/i })).toBeVisible();
    await expect(page.locator('input[name="install1"]')).toBeVisible();
  });

  test('completes full installation through the wizard', async ({ page, baseURL }) => {
    test.skip(await isInstalled(page), 'already installed');
    await runWizard(page, baseURL!, USERS.admin);
    await expect(page.locator('text=εγκατάσταση ολοκληρώθηκε')).toBeVisible();
  });
});
