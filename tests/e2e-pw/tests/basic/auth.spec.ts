import { test, expect } from '@playwright/test';
import { STATE, USERS } from '../../utils/auth';
import { login, loginLink, logout, userMenu } from '../../utils/eclass';

test.describe('authentication', () => {

  test('logs in with valid credentials', async ({ page }) => {
    await login(page, USERS.admin);
    await expect(loginLink(page)).toBeHidden();
  });

  test('rejects a wrong password', async ({ page }) => {
    await page.goto('/main/login_form.php');
    await page.fill('#username_id', USERS.admin.username);
    await page.fill('#password_id', `${USERS.admin.password}-wrong`);
    await page.locator('input[name="submit"]').click();
    await expect(userMenu(page)).toBeHidden();
    await expect(loginLink(page)).toBeVisible();
  });

  test.describe('with a stored session', () => {
    test.use({ storageState: STATE.admin });

    test('opens the portfolio without logging in', async ({ page }) => {
      await page.goto('/main/portfolio.php');
      await expect(userMenu(page)).toBeVisible();
    });
  });

  // Logs in on its own: logging out of the stored session would end it for every other spec.
  test('logs out and ends the session', async ({ page }) => {
    await login(page, USERS.admin);
    await logout(page);
    await page.goto('/main/portfolio.php');
    await expect(userMenu(page)).toBeHidden();
  });

});
