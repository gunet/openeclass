import { test, expect } from '../../utils/fixtures';
import { USERS } from '../../utils/auth';
import { flash, loginLink, submitLogin, userMenu } from '../../utils/eclass';

/**
 * Account-lifecycle flows that change platform config or user data. Each describe resets to the seeded
 * snapshot afterwards (clears config overrides, re-created users, password changes and login_lock rows).
 */

test.describe('lost password', () => {
  test.afterEach(async ({ harness }) => {
    await harness.reset();
  });

  test('reset through the emailed link: the new password works and the old one stops', async ({ page, harness, mail }) => {
    const email = 'e2e_lp@example.com';
    const oldPass = 'E2e-LP-Old-1!';
    const newPass = 'E2e-LP-New-2!';
    await harness.users([{ username: 'e2e_lp', password: oldPass, email }]);
    await mail.clear();

    await page.goto('/modules/auth/lostpass.php');
    await page.fill('#userName', 'e2e_lp');
    await page.fill('#email', email);
    await page.locator("input[name='send_link']").click();

    const message = await mail.latest(email);
    const link = mail.extractLink(message, /lostpass\.php\?.*\bu=/);
    await page.goto(link);
    await page.fill("input[name='newpass']", newPass);
    await page.fill("input[name='newpass1']", newPass);
    await page.locator("input[name='submit']").click();

    await submitLogin(page, { username: 'e2e_lp', password: oldPass });
    await expect(userMenu(page), 'old password must no longer work').toBeHidden();

    await submitLogin(page, { username: 'e2e_lp', password: newPass });
    await expect(userMenu(page), 'the new password must work').toBeVisible();
  });

  test('an invalid reset link is refused', async ({ page, harness }) => {
    const { users } = await harness.users([{ username: 'e2e_lp2', password: 'E2e-LP2-1!', email: 'e2e_lp2@example.com' }]);
    await page.goto(`/modules/auth/lostpass.php?u=${users.e2e_lp2}&h=not-a-valid-token`);
    await expect(flash(page, 'danger')).toBeVisible();
    await expect(page.locator("input[name='newpass']")).toHaveCount(0);
  });

  test('a password that is too short is rejected on reset', async ({ page, harness, mail }) => {
    const email = 'e2e_lp3@example.com';
    await harness.users([{ username: 'e2e_lp3', password: 'E2e-LP3-1!', email }]);
    await harness.setConfig({ min_password_len: 12 });
    await mail.clear();

    await page.goto('/modules/auth/lostpass.php');
    await page.fill('#userName', 'e2e_lp3');
    await page.fill('#email', email);
    await page.locator("input[name='send_link']").click();

    const link = mail.extractLink(await mail.latest(email), /lostpass\.php\?.*\bu=/);
    await page.goto(link);
    await page.fill("input[name='newpass']", 'short');
    await page.fill("input[name='newpass1']", 'short');
    await page.locator("input[name='submit']").click();
    // Rejected: a warning is shown and the password was not changed (the short one can't log in).
    await expect(flash(page, 'warning')).toBeVisible();
    await submitLogin(page, { username: 'e2e_lp3', password: 'short' });
    await expect(userMenu(page)).toBeHidden();
  });
});

test.describe('password change from the profile', () => {
  test.afterEach(async ({ harness }) => {
    await harness.reset();
  });

  test('a wrong current password is rejected; a correct change works', async ({ page, harness }) => {
    const oldPass = 'E2e-PC-Old-1!';
    const newPass = 'E2e-PC-New-2!';
    await harness.users([{ username: 'e2e_pc', password: oldPass }]);
    await submitLogin(page, { username: 'e2e_pc', password: oldPass });
    await expect(userMenu(page)).toBeVisible();

    // Wrong current password → not changed (the old password still logs in afterwards).
    await page.goto('/main/profile/password.php');
    await page.fill("input[name='old_pass']", 'the-wrong-current-password');
    await page.fill("input[name='password_form']", newPass);
    await page.fill("input[name='password_form1']", newPass);
    await page.locator("input[name='submit']").click();
    await page.goto('/main/profile/password.php'); // reload to settle
    await relogin(page, { username: 'e2e_pc', password: oldPass });
    await expect(userMenu(page), 'old password should still work after a rejected change').toBeVisible();

    // Correct change → the new password works and the old one stops.
    await page.goto('/main/profile/password.php');
    await page.fill("input[name='old_pass']", oldPass);
    await page.fill("input[name='password_form']", newPass);
    await page.fill("input[name='password_form1']", newPass);
    await page.locator("input[name='submit']").click();
    await relogin(page, { username: 'e2e_pc', password: newPass });
    await expect(userMenu(page), 'the new password should work').toBeVisible();
    await relogin(page, { username: 'e2e_pc', password: oldPass });
    await expect(userMenu(page), 'the old password should stop working').toBeHidden();
  });
});

test.describe('double login lock', () => {
  test.afterEach(async ({ harness }) => {
    await harness.reset();
  });

  test('a second login for the same user invalidates the first session', async ({ browser, baseURL, harness }) => {
    await harness.setConfig({ double_login_lock: 1 });

    const first = await browser.newContext({ baseURL: baseURL! });
    const firstPage = await first.newPage();
    await submitLogin(firstPage, USERS.student);
    await expect(userMenu(firstPage)).toBeVisible();

    const second = await browser.newContext({ baseURL: baseURL! });
    const secondPage = await second.newPage();
    await submitLogin(secondPage, USERS.student);
    await expect(userMenu(secondPage)).toBeVisible();

    // The first session is now stale: its next request is destroyed and sent home.
    await firstPage.goto('/main/portfolio.php');
    await expect(userMenu(firstPage)).toBeHidden();

    await first.close();
    await second.close();
  });
});

test.describe('upgrade in progress', () => {
  test.afterEach(async ({ harness }) => {
    await harness.reset();
  });

  test('non-admins are warned and sent home, the admin is not blocked', async ({ harness, as }) => {
    await harness.setConfig({ upgrade_begin: Math.floor(Date.now() / 1000) });

    const student = await as('student');
    await student.goto('/main/portfolio.php');
    await expect(flash(student, 'warning')).toBeVisible();

    const admin = await as('admin');
    await admin.goto('/main/portfolio.php');
    await expect(userMenu(admin)).toBeVisible();
  });
});

/**
 * Re-login helper: logs out if a session is present, then logs in fresh. (Used by the password-change spec
 * to check a password without relying on stored state.)
 */
async function relogin(page: import('@playwright/test').Page, creds: { username: string; password: string }): Promise<void> {
  await page.context().clearCookies();
  await submitLogin(page, creds);
}

/**
 * Not automated here — each needs a flow or environment this harness doesn't drive yet. Left as explicit
 * fixmes so they aren't silently missing:
 *  - student self-registration (newuser.php) + "request account" mode: the form requires picking a
 *    department through the hierarchy node-picker widget.
 *  - email-verification full cycle (register → mail_verify.php link → verified).
 *  - teacher account request (formuser.php) → admin approval in listreq.php → login works; and the reject path.
 *  - user consent / privacy-policy acceptance on first login.
 *  - block_username_change, and account self-unregister (main/unreguser.php).
 *  - LDAP (auth=4) / CAS (auth=7) login — needs the `sso` compose profile.
 *  - 2FA challenge, and OAuth2/Keycloak/Shibboleth buttons (only shown when configured).
 */
test.describe('account lifecycle — not yet automated', () => {
  test('registration, verification, teacher-request, consent, unregister, SSO, 2FA', () => {
    test.fixme(true, 'See the comment above this describe for why each is deferred.');
  });
});
