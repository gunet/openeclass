import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright runs against the e2e docker stack (docker-compose.development.yaml +
 * docker-compose.e2e.yaml, port 8080). Specs share one site and its data, so they
 * run serially on one worker.
 *
 *   setup    – installs the site through the web wizard if needed, points mail at mailpit,
 *              logs each account in once and stores the session in .auth/
 *   chromium – the specs, reusing the stored sessions
 *   install  – the wizard's own specs, only with ECLASS_E2E_INSTALL=1 (`bun run test:e2e:install`,
 *              which starts from an empty stack)
 *
 * Override the target with ECLASS_BASE_URL. Reports and traces land under
 * tests/e2e-pw/ (gitignored).
 */
const baseURL = process.env.ECLASS_BASE_URL || 'http://localhost:8080';
const installMode = process.env.ECLASS_E2E_INSTALL === '1';

export default defineConfig({
  testDir: './tests',
  outputDir: './test-results',
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: [
    ['list'],
    ['html', { outputFolder: './playwright-report', open: 'never' }],
  ],
  use: {
    baseURL,
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15_000,
  },
  projects: installMode
    ? [{ name: 'install', testMatch: /install\/.*\.spec\.ts/, use: { ...devices['Desktop Chrome'] } }]
    : [
        { name: 'setup', testMatch: /auth\.setup\.ts/, testDir: '.' },
        {
          name: 'chromium',
          testIgnore: /install\//,
          use: { ...devices['Desktop Chrome'] },
          dependencies: ['setup'],
        },
      ],
  webServer: {
    // `e2e:up` (`up -d --wait`) exits once the containers are healthy, and Playwright treats an exited
    // command as a failure, so keep a process alive. The containers stay up after the run.
    command: 'bun run e2e:up && tail -f /dev/null',
    url: baseURL,
    reuseExistingServer: true,
    timeout: 600_000,
  },
});
