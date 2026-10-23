import { test, expect } from '../../utils/fixtures';
import type { Page } from '@playwright/test';
import type { SessionRole } from '../../utils/auth';
import { expectDenied, loginLink } from '../../utils/eclass';

/**
 * Role × URL authorization matrix for the admin area. For each page we know which roles the platform should
 * let in (from the require_* flag it sets and the privilege hierarchy in include/init.php):
 *
 *   admin               → admin only
 *   poweruser           → + power / usermanage / departmentmanage
 *   usermanager         → usermanage only
 *   depadmin            → departmentmanage (+ usermanage), scoped to its department
 *
 * Each page is visited by every role; the test asserts it is reached (allowed) or refused (denied):
 *   - anonymous        → redirected to the login form
 *   - logged-in denied → redirected to the portfolio with the warning "access denied" flash
 *   - allowed          → stays on the page's own URL
 *
 * This is the GET matrix; destructive POST actions and department scoping are covered elsewhere.
 */

type Tier = 'admin' | 'usermanage' | 'departmentmanage';

// Which roles each tier allows. 'anon' is never allowed.
const ALLOWED: Record<Tier, SessionRole[]> = {
  admin: ['admin'],
  usermanage: ['admin', 'poweruser', 'usermanager', 'depadmin'],
  departmentmanage: ['admin', 'poweruser', 'depadmin'],
};

const PAGES: { path: string; tier: Tier }[] = [
  { path: '/modules/admin/eclassconf.php', tier: 'admin' },
  { path: '/modules/admin/apitokenconf.php', tier: 'admin' },
  { path: '/modules/admin/widgets.php', tier: 'admin' },
  { path: '/modules/admin/listusers.php', tier: 'usermanage' },
  { path: '/modules/admin/newuseradmin.php', tier: 'usermanage' },
  { path: '/modules/admin/listcours.php', tier: 'departmentmanage' },
  { path: '/modules/admin/addadmin.php', tier: 'departmentmanage' },
];

// Roles exercised against every page. 'anon' uses a session-less page.
const ROLES: (SessionRole | 'anon')[] = ['anon', 'student', 'teacher', 'usermanager', 'depadmin', 'poweruser', 'admin'];

const pathRegex = (path: string) => new RegExp(path.replace(/[.]/g, '\\$&').replace(/\//g, '\\/'));

test.describe('authorization matrix (admin area, GET)', () => {
  for (const { path, tier } of PAGES) {
    for (const role of ROLES) {
      const allowed = role !== 'anon' && ALLOWED[tier].includes(role);
      test(`${role} ${allowed ? 'may open' : 'is refused'} ${path}`, async ({ page, as }) => {
        const target: Page = role === 'anon' ? page : await as(role);
        await target.goto(path);

        if (allowed) {
          await expect(target, 'should stay on the page').toHaveURL(pathRegex(path));
        } else if (role === 'anon') {
          await expect(target, 'anonymous should be sent to the login form').toHaveURL(/main\/login_form\.php/);
          await expect(loginLink(target)).toBeVisible();
        } else {
          await expect(target, 'denied role should not stay on the page').not.toHaveURL(pathRegex(path));
          await expectDenied(target);
        }
      });
    }
  }
});
