import { test, expect } from '../../utils/fixtures';
import { SESSION_ROLES, USERS } from '../../utils/auth';
import { loginLink, submitLogin, userMenu } from '../../utils/eclass';

/**
 * Authentication & account lifecycle. Specs that change platform config restore it in afterEach
 * (restoreConfig is light; the lockout spec needs a full reset to clear the login_failure table).
 */

test.describe('login', () => {

  for (const role of SESSION_ROLES) {
    test(`${role} logs in`, async ({ page }) => {
      await submitLogin(page, USERS[role]);
      // Logged in = the user menu shows and the header login link is gone. (Guests don't get a portfolio,
      // so we don't assert the landing URL here.)
      await expect(userMenu(page)).toBeVisible();
      await expect(loginLink(page)).toBeHidden();
    });
  }

  test('a wrong password is refused and starts no session', async ({ page }) => {
    await submitLogin(page, { username: USERS.student.username, password: 'definitely-wrong' });
    await expect(userMenu(page)).toBeHidden();
    await expect(loginLink(page)).toBeVisible();
  });

  test('an unknown user is refused', async ({ page }) => {
    await submitLogin(page, { username: 'e2e_nobody', password: 'whatever' });
    await expect(userMenu(page)).toBeHidden();
  });

  test('empty credentials start no session', async ({ page }) => {
    await submitLogin(page, { username: '', password: '' });
    await expect(userMenu(page)).toBeHidden();
    await expect(loginLink(page)).toBeVisible();
  });

  test('the admin login page (login_form_admin.php) logs the admin in', async ({ page }) => {
    // Note: admin_login only flags MAINTENANCE_PAGE so this page works during maintenance; it is not an
    // admin-only gate (any valid account would authenticate here), so we only assert the admin case.
    await page.goto('/main/login_form_admin.php');
    await page.fill('#Uname', USERS.admin.username);
    await page.fill('#Pass', USERS.admin.password);
    await page.locator("button[name='submit']").click();
    await expect(userMenu(page)).toBeVisible();
  });

  test('clearing the session cookie logs the user out', async ({ page }) => {
    await submitLogin(page, USERS.student);
    await expect(userMenu(page)).toBeVisible();
    await page.context().clearCookies();
    await page.goto('/main/portfolio.php');
    await expect(userMenu(page)).toBeHidden();
    await expect(loginLink(page)).toBeVisible();
  });

  test('an expired account cannot log in', async ({ page }) => {
    await submitLogin(page, USERS.expired);
    await expect(userMenu(page)).toBeHidden();
  });

  test('a user flagged for a forced password change is sent to password_change.php', async ({ page }) => {
    await submitLogin(page, USERS.force_pw);
    await expect(page).toHaveURL(/modules\/auth\/password_change\.php/);
  });

});

test.describe('email verification required', () => {
  test.afterEach(async ({ harness }) => {
    await harness.restoreConfig();
  });

  test('an unverified user is redirected to mail verification when it is required', async ({ page, harness }) => {
    await harness.setConfig({ email_verification_required: 1 });
    await submitLogin(page, USERS.unverified);
    await expect(page).toHaveURL(/modules\/auth\/mail_verify_change\.php/);
  });
});

test.describe('maintenance mode', () => {
  test.afterEach(async ({ harness }) => {
    await harness.restoreConfig();
  });

  test('non-admins are redirected to the maintenance page, admins are not', async ({ harness, as }) => {
    await harness.setConfig({ maintenance: 1 });

    const student = await as('student');
    await student.goto('/main/portfolio.php');
    await expect(student).toHaveURL(/maintenance/);

    const admin = await as('admin');
    await admin.goto('/main/portfolio.php');
    await expect(admin).not.toHaveURL(/maintenance/);
    await expect(userMenu(admin)).toBeVisible();
  });
});

test.describe('brute-force lockout', () => {
  test.afterEach(async ({ harness }) => {
    await harness.reset(); // clears config overrides and the login_failure table
  });

  test('after the threshold, even the correct password is refused', async ({ page, harness }) => {
    // Quirk: increaseLoginFailure() in modules/auth/auth.inc.php only runs when `login_page` is NOT set,
    // but the normal login form posts to ?login_page=1 (which flashes + redirects without counting). So
    // lockout can't be driven through the standard form and this spec can't pass as-is. Left for when the
    // counting path / a login endpoint that increments is sorted out.
    test.fixme(true, 'login_fail counter is not incremented via the ?login_page=1 form flow');
    await harness.setConfig({ login_fail_check: 1, login_fail_threshold: 2, login_fail_deny_interval: 30 });

    // Exceed the threshold with wrong passwords (count must get past login_fail_threshold).
    for (let i = 0; i < 3; i++) {
      await submitLogin(page, { username: USERS.student.username, password: `wrong-${i}` });
      await expect(userMenu(page)).toBeHidden();
    }
    // Now the correct password must still be refused while the IP is locked out.
    await submitLogin(page, USERS.student);
    await expect(userMenu(page)).toBeHidden();
  });
});

test.describe('logout', () => {
  test('ends the session so protected pages are no longer accessible', async ({ page }) => {
    await submitLogin(page, USERS.student);
    await expect(userMenu(page)).toBeVisible();
    await userMenu(page).click();
    await page.locator('#logoutForm button[type="submit"]').click();
    await expect(loginLink(page)).toBeVisible();
    await page.goto('/main/portfolio.php');
    await expect(userMenu(page)).toBeHidden();
  });
});
