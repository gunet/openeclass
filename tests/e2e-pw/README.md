# Open eClass E2E tests (Playwright, TypeScript)

End-to-end tests that run against a dedicated docker stack. The plan and progress are in `todo.md`.

## How it works

- **Own stack.** `docker-compose.e2e.yaml` is an overlay on `docker-compose.development.yaml`. It runs as the compose project
  `openeclass-e2e` on port **8080**, with its own volumes, so resetting it never touches the development stack. It adds:
  - **Mailpit**, which catches every mail the site sends. UI and API at http://localhost:8025.
  - A `vendor/` volume filled from the image. The base file bind-mounts the repo, so without it the stack would use the
    host's `vendor/`, which may be out of date.
  - An `e2e_snapshots` volume for DB dumps and course-file tarballs.
  - LDAP/CAS services, only with `--profile sso`.
- **Setup** (`auth.setup.ts`):
  - installs the site through the web wizard when it isn't installed yet, and takes an `installed` snapshot;
  - seeds the accounts and courses of `utils/seed.ts` through the harness, once per version of the seed data, and snapshots that
    (`seeded-<hash>`); every run starts from that snapshot;
  - points mail at Mailpit;
  - logs every account in once, switches it to English and stores the session in `.auth/<role>.json`.
- **Control harness** (`fixtures/e2e-harness.php`, client `utils/harness.ts`): a test-only PHP endpoint for seeding, config
  overrides, moving dates, running cron, reading the log and checking course files. It only answers when the container has
  `ECLASS_E2E=1` and the request has `X-Eclass-E2E: eclass-e2e`, and `.dockerignore` keeps it out of the image. The global teardown
  undoes its config overrides.
- **Serial.** Specs share one site, so they run on a single worker.
- **`utils/stack.ts`** drives the stack from Node: `sql()`, `clearCache()`, `snapshot(name)` and `restore(name)`.

## Writing specs

```ts
import { test, expect } from '../../utils/fixtures';
import { STATE } from '../../utils/auth';
import { expectDenied, gotoCourse } from '../../utils/eclass';
import { COURSE } from '../../utils/seed';

test.use({ storageState: STATE.teacher });            // run as one role…

test('a student cannot open the admin pages', async ({ as }) => {
  const page = await as('student');                    // …or open pages as several
  await page.goto('/modules/admin/index.php');
  await expectDenied(page);
});

test('an expired course', async ({ page, harness }) => {
  await harness.time({ entity: 'course', key: COURSE.OPEN, set: { end_date: '-1 day' } });
  await gotoCourse(page, COURSE.OPEN);
  // …
  await harness.reset();                               // back to the seeded snapshot
});
```

| Module | What it gives you |
|---|---|
| `utils/fixtures.ts` | `test` with `harness`, `mail`, `as(role)` |
| `utils/auth.ts` | `USERS[role]`, `STATE[role]`, `SESSION_ROLES`, `SPECIAL_ROLES` |
| `utils/seed.ts` | `COURSE`, `DEPT`, `COURSE_PASSWORD`, the seed data |
| `utils/harness.ts` | `seed`, `reset`, `getConfig`/`setConfig`/`restoreConfig`, `users`, `courses`, `enrol`, `adminRights`, `courseModule`, `time`, `cron`, `log`, `actionsDaily`, `file`, `apiToken` |
| `utils/mail.ts` | `messages(to)`, `latest(to)`, `extractLink(mail, re)`, `clear()` |
| `utils/eclass.ts` | `login`, `submitLogin`, `logout`, `gotoCourse`, `csrfToken`, `flash`, `expectDenied`, `confirmModal`, `dataTable` |
| `utils/files.ts` | `testFile('pdf' \| 'png' \| 'docx' \| 'scorm' \| 'qti' \| 'gift' \| 'aiken' \| 'usersCsv' \| 'h5p' \| …)` |

Specs share one site and run serially: a spec that changes data should call `harness.reset()` when it's done (or in `afterAll`).

## Running

From `tests/e2e-pw/`:

```bash
bun install
bunx playwright install chromium   # first run only

bun run test:e2e                   # whole suite (starts the stack if needed)
bun run test:e2e tests/basic       # one folder
bun run test:e2e:headed            # watch the browser
bun run test:e2e:ui                # Playwright UI mode
bun run test:e2e:report            # open the last HTML report
bun run test:e2e:typecheck         # tsc over the suite
bun run test:e2e:install           # wipe the stack and run only the install-wizard specs

bun run e2e:up | e2e:down          # start / stop the e2e stack
bun run e2e:reset                  # rebuild the image and start from empty volumes
```

Environment overrides:

| Variable | Default |
|---|---|
| `ECLASS_BASE_URL` | `http://localhost:8080` |
| `ECLASS_E2E_PORT` / `ECLASS_E2E_MAILPIT_PORT` | `8080` / `8025` |
| `ECLASS_ADMIN_USERNAME` / `ECLASS_ADMIN_PASSWORD` | `admin` / `secret` |
| `ECLASS_E2E_PASSWORD` (every seeded account) | `E2e-Pass-1!` |
| `ECLASS_E2E_MAILPIT_URL` | `http://localhost:8025` |
| `ECLASS_DB_HOST` / `_USER` / `_PASSWORD` / `_NAME` | `db` / `root` / `secret` / `eclass` |

## Layout

```
tests/e2e-pw/
├── playwright.config.ts   auth.setup.ts   global-teardown.ts   tsconfig.json   package.json
├── fixtures/              # e2e-harness.php, the test-only control harness (served by the repo bind mount)
├── sample-data/           # upload files; utils/files.ts generates them into .generated/ (gitignored)
├── utils/                 # auth, eclass, fixtures, files, harness, install, mail, seed, stack
└── tests/                 # specs, one folder per area (see todo.md §0.6)
```
