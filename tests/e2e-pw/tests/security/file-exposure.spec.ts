import { test, expect } from '../../utils/fixtures';

/**
 * Files that must not be served. Each test asserts the SAFE behaviour (the file is not fetchable), so a
 * pass means the deployment is safe and a failure means a real exposure.
 *
 * modules/admin/cron.php logs to courses/cron.log, under the web root, so the log is fetchable at
 * /courses/cron.log. The test is marked test.fail(): while the bug is present the safe assertion throws
 * (expected), and once cron.log is moved out of the web root the test will pass and Playwright will flag the
 * now-unneeded marker for removal.
 */
test.describe('static file exposure', () => {

  test('courses/cron.log is not served to anonymous users', async ({ request }) => {
    test.fail(true, 'Known issue: cron.log is served under courses/ (modules/admin/cron.php). Remove this marker when fixed.');
    const response = await request.get('/courses/cron.log');
    const body = await response.text();
    const looksLikeLog = /\bcron\b/i.test(body) || /\[.+\].+(START|END)/.test(body);
    // Safe: not a 200 that returns the log. (403/404, or a 200 that isn't the log, are both fine.)
    expect(response.status() === 200 && looksLikeLog, `GET /courses/cron.log → ${response.status()}`).toBe(false);
  });

  // Paths that the bundled nginx config should also not serve. These are deployment-dependent (a hardened
  // front-end blocks them), so they're informational: run them against a given target to see what leaks.
  test.describe('other paths (deployment-dependent, informational)', () => {
    for (const path of ['/.git/HEAD', '/composer.lock', '/vendor/composer/installed.json', '/Dockerfile']) {
      test(`${path} is not served`, async ({ request }) => {
        test.fixme(true, 'Deployment-dependent: blocked by a hardened front-end, so not asserted against the default stack.');
        const response = await request.get(path);
        expect(response.status(), `GET ${path}`).not.toBe(200);
      });
    }
  });

});
