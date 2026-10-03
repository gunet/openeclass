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
  - installs the site through the web wizard when it isn't installed yet;
  - points mail at Mailpit;
  - takes an `installed` snapshot;
  - logs every account in once and stores the session in `.auth/`.
- **Serial.** Specs share one site, so they run on a single worker.
- **`utils/stack.ts`** drives the stack from Node: `sql()`, `setConfig()` (clears the app's config cache like `set_config()` does),
  `snapshot(name)` and `restore(name)`.

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
| `ECLASS_DB_HOST` / `_USER` / `_PASSWORD` / `_NAME` | `db` / `root` / `secret` / `eclass` |

## Layout

```
tests/e2e-pw/
├── playwright.config.ts   auth.setup.ts   tsconfig.json   package.json
├── fixtures/              # test-only PHP harness (served at /tests/e2e-pw/fixtures/ by the repo bind mount)
├── test-data/             # files used for uploads
├── utils/                 # auth.ts, install.ts, stack.ts
└── tests/                 # specs, one folder per area (see todo.md §0.6)
```
