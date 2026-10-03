import path from 'node:path';
import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';

/**
 * Accounts and the stored-session paths written by `auth.setup.ts`.
 *
 * Lives here rather than in the setup file because Playwright refuses a test
 * file importing another test file.
 */

export type Role = 'admin';

export type Credentials = { username: string; password: string };

/** Only the install admin exists until the harness seeds the other roles. */
export const USERS: Record<Role, Credentials> = {
  admin: {
    username: process.env.ECLASS_ADMIN_USERNAME || 'admin',
    password: process.env.ECLASS_ADMIN_PASSWORD || 'admin123',
  },
};

const AUTH_DIR = path.join(__dirname, '..', '.auth');

export const STATE: Record<Role, string> = {
  admin: path.join(AUTH_DIR, 'admin.json'),
};

/** The user menu, shown only to logged-in users. */
export const userMenu = (page: Page) => page.locator('#btnGroupDrop1');

/** The header "Login" link, shown only to visitors. */
export const loginLink = (page: Page) => page.locator('a.header-login-text[href$="main/login_form.php"]').first();

export async function login(page: Page, { username, password }: Credentials): Promise<void> {
  await page.goto('/main/login_form.php');
  await page.fill('#username_id', username);
  await page.fill('#password_id', password);
  await page.locator('input[name="submit"]').click();
  await expect(userMenu(page)).toBeVisible();
}

export async function logout(page: Page): Promise<void> {
  await userMenu(page).click();
  await page.locator('#logoutForm button[type="submit"]').click();
  await expect(loginLink(page)).toBeVisible();
}
