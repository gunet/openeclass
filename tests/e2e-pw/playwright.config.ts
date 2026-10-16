import { readdirSync } from 'node:fs';
import { join } from 'node:path';
import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright runs against the e2e docker stack (docker-compose.development.yaml +
 * docker-compose.e2e.yaml, port 8080). Specs share one site and its data, so they
 * run serially on one worker.
 *
 *   setup    – installs the site through the web wizard if needed, seeds the accounts and courses
 *              through the PHP harness (once, then restores a snapshot), points mail at mailpit,
 *              logs each account in once and stores the session in .auth/
 *   <suite>  – one project per spec folder in `suites` below (basic, auth, security, …), reusing the stored
 *              sessions. Run one with `--project=<suite>`; CI runs each as its own job (.github/workflows/e2e.yml)
 *   install  – the wizard's own specs, only with ECLASS_E2E_INSTALL=1 (`bun run test:e2e:install`,
 *              which starts from an empty stack)
 *
 * The global teardown undoes the harness's config overrides.
 *
 * Override the target with ECLASS_BASE_URL. Reports and traces land under
 * tests/e2e-pw/ (gitignored).
 */
const baseURL = process.env.ECLASS_BASE_URL || 'http://localhost:8080';
const installMode = process.env.ECLASS_E2E_INSTALL === '1';

// Spec folders under tests/, one project each. A new folder goes here and gets a job in e2e.yml.
const suites = ['basic', 'auth', 'security'];

// A spec folder missing from `suites` would never run, so refuse to load instead.
const unlisted = readdirSync(join(__dirname, 'tests'), { withFileTypes: true })
  .filter((d) => d.isDirectory() && d.name !== 'install' && !suites.includes(d.name))
  .filter((d) => readdirSync(join(__dirname, 'tests', d.name), { recursive: true }).some((f) => String(f).endsWith('.spec.ts')))
  .map((d) => d.name);
if (unlisted.length) {
  throw new Error(`Spec folders without a project: ${unlisted.join(', ')}. Add them to \`suites\` in playwright.config.ts and to e2e.yml.`);
}

export default defineConfig({
  testDir: './tests',
  outputDir: './test-results',
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  globalTeardown: './global-teardown.ts',
  retries: process.env.CI ? 1 : 0,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: [
    ['list'],
    ['html', { outputFolder: './playwright-report', open: 'never' }],
    // On CI, each job also writes a blob report that the workflow's `report` job merges into one HTML report.
    // ...(process.env.CI ? [['blob', { outputDir: './blob-report' }] as const] : []),
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
        ...suites.map((suite) => ({
          name: suite,
          testDir: `./tests/${suite}`,
          use: { ...devices['Desktop Chrome'] },
          dependencies: ['setup'],
        })),
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
