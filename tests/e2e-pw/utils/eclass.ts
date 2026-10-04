import { expect } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import type { Credentials } from './auth';

/**
 * UI helpers shared by the specs. Sessions are switched to English (`?localize=en`), so text
 * assertions use the English strings from lang/en/.
 */

/** The user menu, shown only to logged-in users. */
export const userMenu = (page: Page) => page.locator('#btnGroupDrop1');

/** The header "Login" link, shown only to visitors. */
export const loginLink = (page: Page) => page.locator('a.header-login-text[href$="main/login_form.php"]').first();

/** Switch the session's interface language (stored in `$_SESSION['langswitch']` by include/lib/session.class.php). */
export async function useLanguage(page: Page, lang: 'en' | 'el' = 'en'): Promise<void> {
  await page.goto(`/?localize=${lang}`);
}

/** Log in through the login form, then switch the session to English. Expects to land logged in. */
export async function login(page: Page, { username, password }: Credentials, options: { english?: boolean } = {}): Promise<void> {
  await submitLogin(page, { username, password });
  await expect(userMenu(page)).toBeVisible();
  if (options.english ?? true) {
    await useLanguage(page, 'en');
  }
}

/** Fill in and submit the login form without checking the outcome (for refused or redirected logins). */
export async function submitLogin(page: Page, { username, password }: Credentials): Promise<void> {
  await page.goto('/main/login_form.php');
  await page.fill('#username_id', username);
  await page.fill('#password_id', password);
  await page.locator('input[name="submit"]').click();
}

export async function logout(page: Page): Promise<void> {
  await userMenu(page).click();
  await page.locator('#logoutForm button[type="submit"]').click();
  await expect(loginLink(page)).toBeVisible();
}

/** The course home page (`/courses/<code>/`), or a course module (`/modules/<module>/index.php?course=<code>`). */
export async function gotoCourse(page: Page, code: string, module?: string): Promise<void> {
  await page.goto(module ? `/modules/${module}/index.php?course=${code}` : `/courses/${code}/`);
}

/** The CSRF token of the first form on the page (`generate_csrf_token_form_field()` → `input[name=token]`). */
export async function csrfToken(page: Page): Promise<string> {
  const token = await page.locator('input[name="token"]').first().getAttribute('value');
  if (!token) {
    throw new Error(`no CSRF token field on ${page.url()}`);
  }
  return token;
}

export type AlertKind = 'success' | 'info' | 'warning' | 'danger';

/** The session flash message (layouts/partials/show_alert.blade.php), optionally of one kind. */
export const flash = (page: Page, kind?: AlertKind): Locator =>
  page.locator(kind ? `.alert.alert-${kind}[role="alert"]` : '.alert[role="alert"]').first();

/**
 * The English texts of the "you may not do this" messages set by include/init.php and include/baseTheme.php
 * ($langCheckAdmin, $langCheckPowerUser, $langCheckUserManageUser, $langCheckDepartmentManageUser,
 * $langCheckCourseAdmin, $langCheckProf, $langCheckGuest, $langNoAdminAccess, $langSessionIsLost, $langLoginRequired).
 */
const DENIED = new RegExp([
  'requires administrator privileges',
  'requires course and user administration rights',
  'requires user administration rights',
  'requires department manager access',
  'requires course administrator rights',
  'requires .* privileges',
  'not possible with guest user rights',
  'requires a valid username and password',
  'Your session has timed-out',
  'You are not registered to the course',
].join('|'), 'i');

/** Expect the page to have been refused: the warning flash with one of the denial messages. */
export async function expectDenied(page: Page): Promise<void> {
  await expect(flash(page, 'warning')).toContainText(DENIED);
}

/** Answer the open bootbox confirm dialog (deletes, cancels). Both bootbox versions in js/bootbox/ are handled. */
export async function confirmModal(page: Page, answer: 'accept' | 'cancel' = 'accept'): Promise<void> {
  const dialog = page.locator('.bootbox.modal.show');
  await expect(dialog).toBeVisible();
  const button = answer === 'accept'
    ? dialog.locator('.bootbox-accept, [data-bb-handler="confirm"], [data-bb-handler="ok"]')
    : dialog.locator('.bootbox-cancel, [data-bb-handler="cancel"]');
  await button.first().click();
  await expect(dialog).toBeHidden();
}

/** DataTables 2 controls (user, course and log lists). `table` narrows to one table when a page has several. */
export function dataTable(page: Page, table?: string) {
  const container = table ? page.locator('div.dt-container').filter({ has: page.locator(table) }) : page.locator('div.dt-container').first();
  return {
    container,
    rows: container.locator('table tbody tr'),
    /** Type into the search box and wait for the table to redraw. */
    async search(text: string): Promise<void> {
      await container.locator('.dt-search input').fill(text);
      await expect(container.locator('.dt-processing')).toBeHidden();
    },
    async nextPage(): Promise<void> {
      await container.locator('.dt-paging button.next, .dt-paging-button.next').first().click();
    },
    info: container.locator('.dt-info'),
  };
}
