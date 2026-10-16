import { test, expect } from '../../utils/fixtures';
import { STATE } from '../../utils/auth';
import { COURSE } from '../../utils/seed';

/**
 * Open redirect. Asserts the SAFE behaviour (an off-site `next` is not honoured), so a pass means safe
 * and a failure means the redirect is open.
 *
 * main/student_view.php puts $_POST['next'] straight into a Location header with no on-site check.
 * Marked test.fail(): while the bug is present the safe assertion throws (expected); once student_view.php
 * validates `next`, the test passes and Playwright flags the now-unneeded marker.
 */
const OFF_SITE = 'https://evil.example/phish';

test.describe('open redirect', () => {

  test('main/student_view.php does not redirect `next` off-site', async ({ playwright, baseURL }) => {
    test.fail(true, 'Known issue: student_view.php redirects `next` unchecked. Remove this marker when fixed.');

    // An authenticated request (student session) that does not follow redirects, so we can read Location.
    const api = await playwright.request.newContext({ baseURL, storageState: STATE.student });
    try {
      const response = await api.post(`/main/student_view.php?course=${COURSE.OPEN}`, {
        form: { next: OFF_SITE },
        maxRedirects: 0,
      });
      const location = response.headers()['location'] ?? '';
      // Safe: the redirect (if any) must stay on-site, never the attacker URL.
      expect(location.startsWith(OFF_SITE), `Location: ${location}`).toBe(false);
    } finally {
      await api.dispose();
    }
  });

});
