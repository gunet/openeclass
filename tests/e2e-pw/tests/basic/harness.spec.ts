import { test, expect } from '../../utils/fixtures';
import { USERS } from '../../utils/auth';
import { expectDenied, gotoCourse, submitLogin, userMenu } from '../../utils/eclass';
import { COURSE } from '../../utils/seed';

/**
 * Smoke tests for the suite's own plumbing: the seeded accounts and courses, the stored sessions,
 * the harness actions and the mailpit client. If these fail, every other spec would too.
 */
test.describe('e2e plumbing', () => {

  test('stored sessions are logged in and in English', async ({ as }) => {
    for (const role of ['teacher', 'student', 'guest'] as const) {
      const page = await as(role);
      await page.goto('/main/portfolio.php');
      await expect(userMenu(page)).toBeVisible();
      await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    }
  });

  test('a student is refused the admin pages', async ({ as }) => {
    const page = await as('student');
    await page.goto('/modules/admin/index.php');
    await expectDenied(page);
  });

  test('the teacher reaches a seeded course', async ({ as }) => {
    const page = await as('teacher');
    await gotoCourse(page, COURSE.OPEN);
    await expect(page.getByText('E2E Open course').first()).toBeVisible();
  });

  test('an expired account cannot log in', async ({ page }) => {
    await submitLogin(page, USERS.expired);
    await expect(userMenu(page)).toBeHidden();
  });

  test('config overrides are tracked and restored', async ({ harness }) => {
    const before = (await harness.getConfig('course_guest')).course_guest;
    await harness.setConfig({ course_guest: 'off' });
    expect((await harness.getConfig('course_guest')).course_guest).toBe('off');
    await harness.restoreConfig();
    expect((await harness.getConfig('course_guest')).course_guest).toBe(before);
  });

  test('mail goes to mailpit: lost-password link', async ({ page, mail }) => {
    const email = `${USERS.student.username}@example.com`;
    await mail.clear();
    await page.goto('/modules/auth/lostpass.php');
    await page.fill('#userName', USERS.student.username);
    await page.fill('#email', email);
    await page.locator('input[name="send_link"]').click();
    const message = await mail.latest(email);
    expect(mail.extractLink(message, /lostpass\.php\?.*h=/)).toContain('/modules/auth/lostpass.php');
  });

  test('harness reads course files and the log', async ({ harness }) => {
    const index = await harness.file(`${COURSE.OPEN}/index.php`);
    expect(index.exists && !index.isDir).toBe(true);
    expect((await harness.file(`${COURSE.OPEN}/nothing-here`)).exists).toBe(false);
    await expect(harness.file('../config/config.php')).rejects.toThrow(/leaves courses/);
    expect(Array.isArray(await harness.log({ limit: 5 }))).toBe(true);
  });

});
