import { test as base } from '@playwright/test';
import type { Page } from '@playwright/test';
import { STATE } from './auth';
import type { SessionRole } from './auth';
import { Harness, HARNESS_HEADERS } from './harness';
import { Mailbox } from './mail';

/**
 * `test` with the suite's fixtures. Import `test` and `expect` from here in specs:
 *
 *   import { test, expect } from '../../utils/fixtures';
 *
 *   test('a student cannot see the admin page', async ({ as }) => {
 *     const page = await as('student');
 *     await page.goto('/modules/admin/');
 *     await expectDenied(page);
 *   });
 *
 * - `harness` – the control harness (seed data, config overrides, dates, log, files)
 * - `mail`    – the mailpit inbox
 * - `as(role)` – a page in a new browser context logged in as `role` (its stored session),
 *                for specs that need several roles at once. Closed after the test.
 */
type Fixtures = {
  harness: Harness;
  mail: Mailbox;
  as: (role: SessionRole) => Promise<Page>;
};

export const test = base.extend<Fixtures>({
  harness: async ({ playwright, baseURL }, use) => {
    const api = await playwright.request.newContext({ baseURL, extraHTTPHeaders: HARNESS_HEADERS });
    await use(new Harness(api));
    await api.dispose();
  },

  mail: async ({}, use) => {
    const { mail, dispose } = await Mailbox.create();
    await use(mail);
    await dispose();
  },

  as: async ({ browser, baseURL }, use) => {
    const contexts: Awaited<ReturnType<typeof browser.newContext>>[] = [];
    await use(async (role) => {
      const context = await browser.newContext({ baseURL, storageState: STATE[role] });
      contexts.push(context);
      return context.newPage();
    });
    await Promise.all(contexts.map((context) => context.close()));
  },
});

export { expect } from '@playwright/test';
