# Open eClass E2E (Playwright) – TODO

Goal: end-to-end coverage of the platform **for every type of account**, built on a typed control harness, stored sessions per role,
serial specs on one shared site, a global teardown, a README, and a CI workflow.

Priorities: **P0 (Critical)** → **P1 (High)** → **P2 (Medium)** → **P3 (Low)**.
Each item says which roles it should be run as. Role keys are defined in [§1](#1-accounts--roles-to-seed).

---

## 0. Infrastructure (do first)

### 0.1 Fix what is already there – P0 (done 2026-10-03)
- [x] Removed the dead `.gitignore` entry `e2e-pw/tests/playwright.config.ts` (anchored to the repo root, it matched nothing).
- [x] Moved `playwright.config.ts` to `tests/e2e-pw/`: `testDir: './tests'`, `outputDir: './test-results'`, html reporter at `./playwright-report`.
- [x] Config: `workers: 1`, `fullyParallel: false`, `forbidOnly: !!process.env.CI`, `retries: process.env.CI ? 1 : 0`, `timeout: 60_000`,
      `expect.timeout: 10_000`, `actionTimeout: 15_000`, `trace/video: 'retain-on-failure'`, projects `install → setup → chromium`.
      `globalTeardown` is left for 0.4: it only has something to do once the harness exists.
- [x] Base URL from env: `ECLASS_BASE_URL` (default `http://localhost`).
- [x] `webServer` starts the e2e overlay (0.3). It runs `up -d --wait && tail -f /dev/null`, because Playwright fails when the
      command exits ("Process from config.webServer exited early").
- [x] `.gitignore`: `/tests/e2e-pw/{.auth,test-results,playwright-report}/` and `/test-results/`; deleted the empty root `test-results/`.
- [x] `tests/basic/auth.spec.ts` uses `utils/auth.ts` (`USERS`, `STATE`, `login()`, `logout()`, `userMenu()`, `loginLink()`) and the stored admin
      session from `auth.setup.ts`. No Greek text: logged-out state is checked with the header login link. Credentials from
      `ECLASS_ADMIN_USERNAME` / `ECLASS_ADMIN_PASSWORD` (default `admin` / `secret`). Added a wrong-password test; logout logs in on
      its own so it doesn't end the stored session.
- [x] `tests/install/install.spec.ts` is its own `install` project, run only by `bun run test:e2e:install` (wipes the stack, sets
      `ECLASS_E2E_INSTALL=1`). The wizard steps live in `utils/install.ts` `runWizard()`, shared with `setup`: admin from `USERS`,
      DB from `ECLASS_DB_*`, site URL from the base URL. Its Greek assertions are still there (see §16).
- [x] Deleted the empty `tests/main/`, `tests/modules/` and `test-data/` folders. The 0.6 folders exist locally but aren't tracked (no
      `.gitkeep`); each one gets into git with its first file.

Verified (before 0.3, on the dev stack): fresh stack → 8 passed; stopped stack → webServer starts it, 5 passed, 3 skipped. Current results under 0.3.

### 0.2 Tooling – P0 (done 2026-10-03)
- [x] Separate `tests/e2e-pw/package.json` (devDependencies `@playwright/test`, `typescript`, `@types/node`). The app's `package.json` is untouched.
      `tests/e2e-pw/bun.lock` is tracked (`!/tests/e2e-pw/bun.lock` in `.gitignore`); `tests/e2e-pw/node_modules/` is ignored.
- [x] Scripts: `test:e2e`, `test:e2e:headed`, `test:e2e:ui`, `test:e2e:report`, `test:e2e:typecheck` (`tsc -p .`), `test:e2e:install`,
      `e2e:compose`, `e2e:up`, `e2e:down`, `e2e:reset` (`down -v` + `up --build`).
- [x] `tests/e2e-pw/tsconfig.json` (ES2022, Bundler resolution, strict, noEmit). `tsc` passes.
- [x] First-run note (`bunx playwright install chromium`) and usage in `tests/e2e-pw/README.md`.

### 0.3 Test stack (docker) – P0 (done 2026-10-03, except what moved to 0.4)
- [x] `docker-compose.e2e.yaml` overlay. Its own compose project `openeclass-e2e` on port 8080 (`ECLASS_E2E_PORT`), so `e2e:reset` never
      touches the dev stack's volumes. `playwright.config.ts` `webServer` and the default base URL use it.
  - [x] `ECLASS_E2E=1` on `eclass`.
  - [x] No separate mount for `tests/e2e-pw/fixtures`: the base file already bind-mounts the repo, so the harness is served at
        `/tests/e2e-pw/fixtures/…`. (A nested mount made docker create a root-owned `e2e/` folder in the working tree.)
  - [x] Mailpit (SMTP `mailpit:1025`, UI/API on `ECLASS_E2E_MAILPIT_PORT`, default 8025), with a health check the stack waits for.
  - [x] Profile `sso` with `ldap` + `sso` (CAS) from `docker-compose.test.yaml`.
  - [x] `TZ=Europe/Athens` on `eclass`, `db`, `mailpit`.
  - [x] Volumes over the generated folders, filled from the image (built from `composer.lock` / `package.json`): `vendor/`,
        `js/{mathjax,h5p-standalone,recordrtc,video.js}`, `resources/fonts/mathjax-newcm-font`, and a writable `storage/`.
        Needed because the base file bind-mounts the repo: here the host `vendor/` has no `symfony/mailer`/`symfony/mime`, so every
        mail send failed with a 500 (`Class "Symfony\Component\Mime\Email" not found`). In a fresh checkout (CI) the generated JS
        doesn't exist at all. The dev stack still has the `vendor/` problem until `composer install` is run on the host.
        `bun run e2e:up` (also used by the Playwright `webServer`) creates the mount points first (`e2e:mountpoints`), so docker
        doesn't create them as root in the working tree.
- [x] ~~Unattended CLI install~~ → **the web wizard is used instead** (decision 2026-10-03). `setup` runs `utils/install.ts` `runWizard()`
      when `config/config.php` is missing; the wizard specs use the same helper (`bun run test:e2e:install`).
      Background: the CLI path in `install/index.php` is broken upstream. It jumps to `$_POST['install7']`, but commit `f71101976`
      (2024-06-20) renumbered the install step to `install8`. Even with that changed it never writes the `version` config row, so
      every page redirects to `upgrade/`. Not fixed here; worth an upstream issue.
- [x] Mail goes to Mailpit: `setup` calls `useMailpit()` (`email_transport=smtp`, `smtp_server=mailpit`, `smtp_port=1025`). The harness
      can take this over in 0.4. Checked with a password-reset mail for a non-admin user (admins are excluded by `lostpass.php`).
- [x] Snapshots: `utils/stack.ts` `snapshot(name)` / `restore(name)` / `hasSnapshot(name)`. `mariadb-dump --add-drop-database` plus a tar
      of `courses/` and `video/`, stored in the `e2e_snapshots` volume. `restore()` puts back ownership (`nginx`) and clears the app's FileCache.
      `setup` takes an `installed` snapshot; the `seeded` one comes with 0.4.
- [x] Found while doing this: `get_config()` reads through a 300 s `FileCache` (`/tmp/<hash>_config.cache` in the container), so any
      DB write behind the app's back must clear it. `setConfig()` and `restore()` do.
- [ ] P3: php-fpm drops worker stderr (no `catch_workers_output`), so fatal errors leave no log in the container. Mount an fpm conf
      snippet in the overlay so failures in specs can be debugged from `docker compose logs`.

Verified: empty stack → 6 passed (wizard install in setup); installed site → 6 passed; `test:e2e:install` → 3 passed, then a normal run → 6 passed;
password-reset mail captured by Mailpit; `restore('installed')` removes a user added after the snapshot.

### 0.4 PHP control harness `fixtures/e2e-harness.php` – P0
- [ ] Keep the harness out of the production image: the `Dockerfile` copies the whole repo, `tests/` included. Add `tests` to
      `.dockerignore` (the e2e stack doesn't need it in the image, since it bind-mounts the repo). Until then the only guard is `ECLASS_E2E=1`.
A single PHP entry point that bootstraps only the config and `Database` (without `include/init.php`).
It refuses to run unless `getenv('ECLASS_E2E') === '1'`, and also needs the header `X-Eclass-E2E: eclass-e2e`. It is never part of the image.
Endpoints (JSON):
- [ ] `POST /seed` – departments, users for every role, courses for every visibility, enrolments with role flags (idempotent). Returns ids/codes.
- [ ] `POST /reset` – restore the DB + `courses/` snapshot, clear mailpit, reset config overrides. Accepts `{config: {...}}` overrides.
- [ ] `GET/POST /config` – `get_config` / `set_config` (registration toggles, `course_guest`, `double_login_lock`, `maintenance`,
      `enable_strong_passwords`, `login_fail_*`, `email_verification_required`, `enable_mobileapi`, `show_collaboration`, `enable_tenant`, …).
- [ ] `POST /users`, `POST /courses`, `POST /enrol` (`status`, `tutor`, `editor`, `course_reviewer`, `reviewer`), `POST /admin-rights`
      (`privilege`, `department_id`).
- [ ] `POST /course-module` – enable/disable a module in a course (`course_module.visible`).
- [ ] `POST /time` – move dates for expiry/deadline specs (`user.expires_at`, `course.start_date/end_date`, assignment deadlines,
      exercise start/end). This avoids sleeping or faking the system clock.
- [ ] `POST /cron` – run `modules/admin/cron.php` jobs (notifications digest, `updatetheinactive`).
- [ ] `GET /log?module=&action=` – read the `log` / `actions_daily` tables, so specs can check that logging and statistics happened.
- [ ] `GET /file?path=` – check that a file exists under `courses/<code>/…`, for upload/delete specs.
- [ ] `POST /api-token` – create an `api_token` row (for the REST API specs).
- [ ] `POST /deactivate` – called by the global teardown. It restores the SMTP settings and clears overrides, so the dev site goes back to normal.

### 0.5 TypeScript utils – P0
- [ ] `utils/harness.ts` – typed client for the endpoints above (`Harness` class, `call<T>()` that checks `response.ok()`).
- [ ] `utils/fixtures.ts` – `test` extended with `harness`, `mail` (mailpit client) and `as(role)` (a new context with that role's storage state).
- [ ] `utils/auth.ts` – `STATE[role]` paths (`tests/e2e-pw/.auth/<role>.json`) and `USERS[role]` credentials.
- [ ] `utils/mail.ts` – mailpit API: `messages(to)`, `latest(to)`, `extractLink(msg, /lostpass|mail_verify/)`, `clear()`.
- [ ] `utils/eclass.ts` – common UI helpers:
  - `login(page, user)` (`#username_id`, `#password_id`, `input[name=submit]`), `logout(page)` (`#btnGroupDrop1` → `#logoutForm`).
  - `gotoCourse(page, code, module?)` → `/courses/<code>/` or `/modules/<module>/index.php?course=<code>`.
  - `csrfToken(page)` – read `input[name=token]` (forms use `generate_csrf_token_form_field()`).
  - `flash(page)` – the `alert-*` message locator; `expectDenied(page)` – matches the `$langCheckAdmin` / `$langCheckPowerUser` /
    `$langCheckUserManageUser` / `$langCheckDepartmentManageUser` / `$langCheckGuest` / `$langNoAdminAccess` / `$langSessionIsLost` error box.
  - `confirmModal(page)` – the bootbox/`modalconfirmation` dialogs used for deletes.
  - `dataTable(page)` – the DataTables search/paging used by user, course and log lists.
- [ ] **Language:** force English in every stored session (`?localize=en`, which sets `$_SESSION['langswitch']` via
      `include/lib/session.class.php`), so assertions don't depend on Greek strings. Keep one Greek smoke spec (§17).
- [ ] `utils/files.ts` – generated fixtures in `test-data/`: a small pdf/docx/png/zip, a SCORM package, an IMS QTI xml, a GIFT/Aiken txt,
      a users CSV for bulk registration, an H5P file, a course backup zip.

### 0.6 Layout (target)
```
tests/e2e-pw/
├── playwright.config.ts
├── auth.setup.ts            # CLI install (if needed) + harness.seed() + log every role in once
├── global-teardown.ts       # harness.deactivate()
├── tsconfig.json  README.md
├── fixtures/e2e-harness.php
├── test-data/               # upload fixtures
├── utils/{harness,fixtures,auth,mail,eclass,files}.ts
└── tests/
    ├── install/             # wizard on an empty stack (separate project)
    ├── auth/                # login, logout, registration, lost password, verification, lockouts, SSO
    ├── security/            # role × URL access matrix, CSRF, IDOR, file access
    ├── portal/              # anonymous homepage, info pages, course catalog, search
    ├── user/                # portfolio, profile, my courses, calendar, notes, mydocs, messages, certificates
    ├── admin/               # one file per admin area
    ├── course/              # create, settings, users, home, units, tools, backup/restore
    ├── modules/             # one folder per course module
    ├── collaboration/       # sessions platform (coordinator/consultant/user)
    ├── api/                 # REST v1, mobile, LTI, OAI-PMH, RSS/iCal feeds
    └── i18n/                # el/en smoke
```

### 0.7 CI – P1 (done 2026-10-03)
- [x] `.github/workflows/e2e.yml`: on push to `master`, on pull requests and manually; one run per ref (older ones cancelled).
      checkout → setup-bun → `bun install --frozen-lockfile` → `playwright install --with-deps chromium` → `test:e2e:typecheck` →
      `e2e:up` (image build) → `test:e2e` with `CI=true` → on failure: stack logs + `playwright-report`/`test-results` artifact (7 days)
      → always `down -v`.
- [x] Checked locally on a copy holding only what git would commit (no host `vendor/`, generated JS or `node_modules`): 6 passed, and
      the generated JS is served from the image volumes. Not run on GitHub yet.
- [ ] Optional matrix: `sso` profile on/off, collaboration platform on/off.
- [ ] Docker layer cache for the image build (e.g. buildx + `type=gha`) if the build step gets slow.

---

## 1. Accounts / roles to seed

From `include/constants.php`, `include/init.php` (platform flags and per-course flags) and `modules/user/index.php` (give/remove rights).

| Key | How it is created | What it can do (expected) |
|---|---|---|
| `anon` | no session | public homepage, open courses, catalog, info pages, registration |
| `admin` | `admin.privilege = ADMIN_USER (0)` | everything; acts as teacher in every course |
| `poweruser` | `admin.privilege = POWER_USER (1)` | users + courses admin; acts as teacher in every course; no platform config |
| `usermanager` | `admin.privilege = USERMANAGE_USER (2)` | user admin only (search/edit/create/delete users, requests) |
| `depadmin` | `admin.privilege = DEPARTMENTMANAGE_USER (3)` + `department_id = DeptA` | courses/users of DeptA subtree only; acts as teacher in DeptA courses |
| `teacher` | `user.status = USER_TEACHER (1)`, course admin (`course_user.status=1`) of the E2E courses | course creation, full course admin |
| `teacher_other` | teacher, **not** enrolled in the E2E courses | negative cases (cannot administer someone else's course) |
| `editor` | `course_user.editor = 1` | course editing without being the owner |
| `course_reviewer` | `course_user.course_reviewer = 1` | read-only reviewer of course content/results |
| `oc_reviewer` | `course_user.reviewer = 1` | open-courses certification reviewer (`course_metadata`) |
| `tutor` | group tutor (`group_members.is_tutor`) / `course_user.tutor = 1` | group tutor; consultant in collaborative courses |
| `student` | `user.status = USER_STUDENT (5)`, enrolled | learner |
| `student2` | second enrolled student | peer review, groups, messages, forum replies |
| `student_unenrolled` | student, not enrolled | registration / access negatives |
| `guest` | `USER_GUEST (10)`, created from `modules/user/guestuser.php` | read-only course guest (`$langCheckGuest` on most writes) |
| `expired` | `user.expires_at` in the past | login refused |
| `unverified` | `verified_mail = EMAIL_VERIFICATION_REQUIRED (0)` + `email_verification_required` on | redirected to `mail_verify_change.php` |
| `force_pw` | flagged for forced password change | redirected to `auth/password_change.php` |
| `ldap_user` / `cas_user` | from the `docker-compose.test.yaml` LDAP/CAS images | alternative auth |
| collaboration: `coordinator`, `consultant`, `simple_user` | collaborative course (`course.is_collaborative = 1`, `show_collaboration`) | sessions module roles (`is_coordinator`, `is_consultant`, `is_simple_user`) |

Course-admin sub-rights (`userRights_CourseAdminTools`, `userRights_AdminUsers`, `userRights_ArchiveCourse`, `userRights_CloneCourse`
in `modules/user/index.php`): seed a second course admin `teacher_limited` with only some of them.

Courses to seed:

| Code | Purpose |
|---|---|
| `E2EOPEN` | `COURSE_OPEN` – anyone may view; `anon` sees public content |
| `E2EREG` | `COURSE_REGISTRATION` – free self-registration |
| `E2EREGPW` | `COURSE_REGISTRATION` + registration password |
| `E2EREGWIN` | registration with a start/end window (`course_enableRegStartDate/EndDate`) |
| `E2ECLOSED` | `COURSE_CLOSED` – registration only by request/teacher |
| `E2EINACTIVE` | `COURSE_INACTIVE` – only teacher/editor/reviewer can enter |
| `E2EEXPIRED` / `E2EFUTURE` | end date in the past / start date in the future (`$langCourseHasExpired`) |
| `E2EPREREQ` | has `E2EOPEN` as a prerequisite (`course_prerequisites`) |
| `E2EDEPB` | belongs to DeptB, outside `depadmin`'s subtree |
| `E2ECOLLAB` | collaborative course (sessions platform) |

Departments: tree `Root → DeptA → DeptA1`, `Root → DeptB` (to test department-manager subtrees and the `user_multidep` option).

`auth.setup.ts`: seed, then for every role run `login()` once and `context.storageState({ path: STATE[role] })`, then check that the role
sees its expected landing page (portfolio for users, `modules/admin/` link for admins).

---

## 2. Authentication & account lifecycle (`modules/auth`, `main/login_form.php`) – P0

- [ ] Login with valid credentials — **every seeded role**; land on `main/portfolio.php`; the user menu `#btnGroupDrop1` is visible.
- [ ] Login: wrong password, unknown user, empty fields → error, no session cookie.
- [ ] Login: `expired` account refused; `unverified` → `mail_verify_change.php`; `force_pw` → `password_change.php` and cannot browse elsewhere.
- [ ] Brute-force lockout (`login_fail_check`, `login_fail_threshold`, `login_fail_deny_interval`): N failures → blocked; unblocked after the interval (harness `/time`).
- [ ] `double_login_lock`: a second session for the same user logs out the first (`LOG_LOGIN_DOUBLE`); power users are exempt.
- [ ] Admin-only login page `main/login_form_admin.php` (`#Uname`, `#Pass`, `admin_login`) works for admin and is refused/ignored for others.
- [ ] Logout from the user menu (POST `#logoutForm`); then protected pages show `$langSessionIsLost`.
- [ ] Session expiry: delete the cookie → a `require_login` page shows "session lost"; the mobile UA (`eClassMobileApp`) → redirect to `msession_expired.php`.
- [ ] Student self-registration `auth/registration.php` → `newuser.php` (`eclass_stud_reg` on/off; `email_required`, `am_required`; captcha off in e2e).
- [ ] Strong passwords (`enable_strong_passwords`, `min_password_len`) rejected/accepted on registration and password change.
- [ ] Teacher account request `auth/formuser.php` (`eclass_prof_reg`) → appears in `admin/listreq.php` → admin approves → mail sent → login as a teacher works;
      reject path sends a rejection mail.
- [ ] Student "request account" mode (`account_request`) when direct student registration is off.
- [ ] Email verification: register with `email_verification_required` → mail with a `mail_verify.php` link → follow it → verified.
- [ ] Lost password `auth/lostpass.php`: request link → mailpit → set a new password → old password no longer works; an expired/invalid token is refused.
- [ ] Change password from the profile (`main/profile/password.php`): wrong current password, mismatch, success.
- [ ] `block_username_change` on/off on the profile.
- [ ] Unregister account `main/unreguser.php` (student) – refused while still enrolled where the policy requires it; the account is removed.
- [ ] User consent / privacy policy (`enable_user_consent`, `activate_privacy_policy_consent`): first login after enabling forces acceptance.
- [ ] Maintenance mode (`maintenance=1`): non-admins are redirected to `maintenance/`; admin keeps access.
- [ ] Upgrade in progress (`upgrade_begin`): non-admins are sent home with a warning.
- [ ] P2 – LDAP login (`auth=4`) and CAS login (`auth=7`) with the `sso` compose profile; first login auto-creates the account (`alt_auth_stud_reg`).
- [ ] P2 – `admin/auth.php` enable/disable methods; `auth_test.php` "test connection" for LDAP; `auth_change.php` moves users between methods.
- [ ] P3 – 2FA module (`secondfamoduleconf.php`) when enabled: enrol and challenge.
- [ ] P3 – OAuth2/Keycloak/Shibboleth/HybridAuth buttons are only shown when configured (no real provider; check markup + redirect URL).

---

## 3. Security (`tests/security`) – P0

These specs run only against the local docker stack, as regression checks. Each one asserts the **safe** behaviour, so a failure means a
real finding. Items marked **(found while scanning – verify first)** come from reading the code and haven't been confirmed against a
running site yet.

### 3.1 Authorization – role × URL matrix
- [ ] **Data-driven matrix spec**. For each page, assert allowed or denied for **every** role
      in §1, with both GET and POST. Grouped by the `require_*` flag the page sets:
  - `$require_admin` (admin only): `eclassconf`, `extapp`, `auth*`, `modules.php`, `modules_default`, `widgets`, `manage_home`, `manage_footer`,
    `faq_create`, `privacy_policy_conf`, `accessibility_conf`, `contact_info`, `cleanup`, `phpInfo`, `check_server`, `apitokenconf`, `tenants`,
    `tenant_edit`, `coursecategory*`, `custom_profile_fields`, `eportfolio_fields`, `autoenroll`, `suppressed_words`, `mail_ver_settings`,
    `maintenance_config`, `collaboration_enable`, `updatetheinactive`, `adminannouncements_single`, every `*conf.php` integration page.
  - `$require_usermanage_user` (admin, poweruser, usermanager, depadmin): `index.php`, `search_user`, `listusers`, `edituser`, `newuseradmin`,
    `listreq`, `deluser`, `unreguser`, `mergeuser`, `mailtoprof`, `multireguser`, `multicourseuser`, `multiedituser`, `multieditcourse`,
    `userlogs`, `user_last_logins`.
  - `$require_departmentmanage_user` (admin, depadmin; poweruser via `init.php`): `searchcours`, `listcours`, `editcours`, `infocours`,
    `statuscours`, `quotacours`, `delcours`, `multicourse*`, `hierarchy`, `addadmin`, `adminannouncements`, `certbadge`, `login_stats`,
    `monthlyReport`, `otheractions`, `tenant_options`, `theme_options`.
  - Pages **without** a `require_*` flag that check rights in their own code: `change_user.php`, `commondocs.php`, `cron.php`, `debug.php`,
    `new.php`, `upload.php`, `password.php`, `statsForm.php`, `modalconfirmation.php`, `get_minedu_departments.php`, `hierarchy_validations.php`.
    Each one must refuse `anon`, `student` and `teacher`.
  - AJAX/helper endpoints that are easy to forget: `aigetmodels.php`, `aitestconnection.php`, `auth_test.php`, `main/ajax_suppressed_words.php`,
    `main/module_toggle.php`, `main/course_favorite.php`, `main/calendar_data.php`, `main/references_data.php`, `modules/hierarchy/nodes.php`,
    `modules/message/load_recipients.php`, `modules/message/ajax_handler.php`, `modules/sticky_notes/ajax-sticky.php`, `modules/h5p/ajax.php`,
    `modules/search/idx*.php`, `modules/course_info/ajax_load_images.php`, `modules/exercise/save_dropZones.php`, `modules/learnPath/record_action.php`,
    `modules/progress/ajax_certificate.php`, `modules/session/update_percentage.php`.
- [ ] `phpSysInfo` (`modules/admin/sysinfo/index.php`, `xml.php`) only works with `$_SESSION['is_admin']` (`read_config.php`); poweruser/depadmin/student get nothing.
- [ ] Privilege boundaries:
  - `poweruser` can't open platform config; `usermanager` can't open course admin pages; nobody below admin can open `addadmin.php` grants of `ADMIN_USER`.
  - `depadmin`: only DeptA(+DeptA1) courses/users. Direct URLs `editcours.php?c=E2EDEPB`, `edituser.php?u=<DeptB user>`, `delcours.php?c=E2EDEPB`,
    `change_user.php?username=<DeptB user>` are refused. `depadmin` can't promote anyone to admin/poweruser, and can't edit their own privilege.
  - `usermanager` can't edit/delete/switch to an admin or poweruser account (privilege escalation through password reset or `change_user`).
  - A teacher can't make themselves admin through `edituser.php`/`addadmin.php` POSTs with a valid CSRF token.
- [ ] Course-level vertical checks, for every module: `?add`, `?edit`, `?delete`, `?vis`/`mkVisibl`, reorder and settings actions are refused
      (GET and POST, valid token) for `student`, `guest`, `teacher_other`, `course_reviewer` (read-only), `student_unenrolled`.
- [ ] Course-admin sub-rights: `teacher_limited` without `userRights_AdminUsers` can't give editor/admin rights; without `ArchiveCourse` can't call
      `archive_course.php`; without `CloneCourse` can't call `clone_course.php`; without `CourseAdminTools` can't open `course_info`/`course_tools`.
- [ ] A course admin can't change another course by swapping `?course=` (e.g. delete a document id that belongs to `E2EREG` while in `E2EOPEN`).
- [ ] Inactive/disabled modules: the module URL is refused for students even when called directly.
- [ ] Guest (`USER_GUEST`): `$langCheckGuest` on every page without `$guest_allowed` (forum post, work submit, messages, profile edit, poll answer).
- [ ] Student view (`main/student_view.php`): while on, admin actions are hidden **and** refused server-side.

### 3.2 Course visibility & enrolment bypasses
- [ ] Matrix: each course in §1 × {anon, enrolled student, unenrolled student, teacher, editor, course_reviewer, guest} → view home / redirect to
      `course_home/register.php` / `$langCheckProf` / `$langCourseHasExpired` / `$langNoAdminAccess`.
- [ ] Direct module URLs into `E2ECLOSED`/`E2EINACTIVE` (documents, forum topic, exercise, `document/file.php`) are refused for non-members, not just the home page.
- [ ] Registration bypasses: POST to `course_home/register.php` for `E2ECLOSED`; `E2EREGPW` without/with a wrong password; `E2EREGWIN` outside the
      window; a student with `disable_course_registration`.
- [ ] Prerequisites: `E2EPREREQ` can't be entered by direct module URL either.
- [ ] Unregister a different user from a course via `main/unregcours.php` parameters (IDOR).

### 3.3 Horizontal access (IDOR) – one student reading another's data
- [ ] `student2` can't open/download `student`'s:
      work submission (`work/index.php?get=`, `download`), exercise attempt (`exercise_result.php?eurId=`), poll answers
      (`pollresults_per_user.php`), uploaded-file poll/exercise answers, dropbox attachment (`message_download.php`), private notes (`main/notes`),
      my documents (`main/mydocs`), private ePortfolio (`EPF_VISIBLE_PRIVATE`), progress/certificate details, request (`request/` without being a watcher),
      sticky-note edit/delete when `allow_edit`/`allow_delete` are off.
- [ ] Editing/deleting someone else's forum post, blog comment, wall post, wiki page outside the ACL, chat message → refused.
- [ ] Private group documents/forum/wiki aren't reachable by a non-member (`group/document.php?group_id=`).
- [ ] Profile edit can't target another uid (`profile.php` with a hidden `id`/`uid` field changed).
- [ ] Certificate PDF / badge of another user (`mycertificates.php`, `progress/` ids) can't be downloaded.
- [ ] Bookings: a student can't cancel another student's booking (`book_create_delete.php`).

### 3.4 Authentication & session
- [ ] Session fixation: the session id before login ≠ the id after login (`session_regenerate_id` in `auth.inc.php`); the old id is invalid after logout.
- [ ] Logout really kills the session server-side (replay the old cookie → `$langSessionIsLost`).
- [ ] Cookie flags on `PHPSESSID`: `HttpOnly`, `SameSite`, `Secure` when served over https (no `session_set_cookie_params` was found in `include/`
      **(found while scanning – verify first)**).
- [ ] Brute force: `login_fail_*` blocks after N failures **per user and per IP**, also through `login_form_admin.php` and the mobile `mlogin.php`.
- [ ] User enumeration: login error and `lostpass.php` reply the same for an existing and a non-existing user/email.
- [ ] Lost-password token: single use, expires, bound to the user, can't be reused after a password change; requesting reset doesn't change the password by itself.
- [ ] Rate limit on `lostpass.php` and registration mails (`last_passreminder`).
- [ ] Email verification link can't verify a different user (tampered `u`/`h` params in `mail_verify.php`).
- [ ] Forced password change / mail verification can't be skipped by browsing directly to other pages or APIs.
- [ ] Expired account (`expires_at`) can't log in through LDAP/CAS/mobile/API either.
- [ ] `double_login_lock`: the older session is really dropped.
- [ ] Password storage: the DB never has plaintext (harness reads `user.password` and checks it is a bcrypt/argon hash).
- [ ] `change_user.php`: switching back restores the admin session; the switched session can't escalate beyond the target's rights.

### 3.5 CSRF
- [ ] Every state-changing form refuses a missing or wrong `token` (`validate_csrf_token`). Sample at least: create/delete announcement, delete
      document, course settings, give editor/admin, edit user (admin), add admin, platform config save, delete course, change password, profile email change,
      submit/delete assignment, forum delete, poll delete results.
- [ ] State-changing **GET** links (`?delete=`, `?giveAdmin=`, `?vis=`, `?topicdel=`, `?unzip=`…) need the token too; craft the URL without it from another origin page.
- [ ] Logout is POST-only (`#logoutForm`); `?logout` by GET from a third-party page doesn't log out (or at least is not usable for CSRF chaining).
- [ ] A token from user A's session is refused in user B's session.

### 3.6 Open redirect & header injection
- [ ] **`main/student_view.php` does `header('Location: ' . $_POST['next'])` with no check** → `next=https://evil.example` must not redirect off-site
      **(found while scanning – verify first)**.
- [ ] Login `next` (`auth.inc.php` → `redirect_to_home_page($next)`): try `//evil.example`, `/\evil.example`, `https:evil.example`,
      `@evil.example`, `%0d%0aSet-Cookie:` → always lands on the same host, no injected headers.
- [ ] `cas.php`, `oauth2.php`, `keycloak.php`, `redirect.php` `next` parameters behave the same.
- [ ] `link/` redirects only to stored links (not an arbitrary `url` param); `main/out.php` likewise.

### 3.7 XSS (stored and reflected)
- [ ] Stored payloads (`<img src=x onerror=…>`, `"><svg onload=…>`, `javascript:` URLs) in: course title/description, unit title, announcement
      title/body, forum subject/post, blog post/comment, wall post, wiki page, glossary term, link title+URL, document title/comment/metadata,
      folder name, file name, exercise/question text, answer feedback, poll question/answer, assignment title/comments/grade feedback, agenda title,
      chat line, sticky note, request title/comment, profile name/description/custom fields, group name, hierarchy node name, admin announcement,
      homepage texts, FAQ, theme options. Check each where it is shown **to other roles** (student → teacher, teacher → admin).
- [ ] The test listens for `page.on('dialog')` and a `window.__xss` marker, and fails if either fires.
- [ ] Reflected: search pages (`search.php`, `search_incourse.php`, admin `search_user`, `searchcours`, DataTables `search` params), `next`,
      error messages that echo parameters, `help/help.php?topic=`.
- [ ] Rich-text editor (TinyMCE) content is sanitised server-side (POST the payload directly, bypassing the editor).
- [ ] Uploaded active content: `.html`, `.svg`, `.xml` documents served by `document/file.php` / `forcedownload.php` must be downloaded
      (`Content-Disposition: attachment`) or served with a safe `Content-Type`/CSP, never rendered inline on the eClass origin.
      Same for ebook HTML (`ebook/show.php`), SCORM content and H5P.
- [ ] Avatar upload with SVG/HTML disguised as an image.

### 3.8 File upload & file system
- [ ] Extension blacklist/whitelist (`isWhitelistAllowed`): `.php`, `.php5`, `.phtml`, `.phar`, `.PhP`, `shell.php.jpg`, `shell.php.`, `shell.php%00.jpg`,
      `.htaccess`, `.user.ini`, `.pht`, `.shtml` are refused in documents, work, dropbox, forum attachments, mydocs, exercise/poll file answers,
      ebook, video, avatar, theme logo, certificate templates, request files.
- [ ] Student vs teacher whitelists (`student_upload_whitelist` / `teacher_upload_whitelist`) and per-user `whitelist` are applied correctly.
- [ ] Uploaded files can't be executed: request the stored file URL directly and check nothing under `/courses/`, `/video/`, `/storage/`
      is run by PHP-FPM (nginx `location ~ \.php` would run any `.php` there).
- [ ] **Zip extraction** (documents "unzip", ebook create, LP/SCORM import, course restore, H5P upload, theme import, certificate templates):
  - zip-slip entries (`../../x.txt`, absolute paths) stay inside the target folder;
  - blocked extensions inside the zip are refused (`validateUploadedFile` on every entry);
  - zip bomb / huge declared size is refused against the quota.
- [ ] **`document/index.php?unzip=` takes a path relative to `$basedir`** – `unzip=../../E2EREG/document/<file>.zip` must be refused, so a teacher
      can't read archives of another course **(found while scanning – verify first)**.
- [ ] Path traversal in document params: `openDir`, `movePath`, `moveTo`, `renameTo`, `replacePath`, `commentPath`, `metadataPath`, `newDirPath`,
      `download`, `file` with `../`, URL-encoded `%2e%2e%2f` and doubled encoding → refused (`doc_init.php`/`index.php` check `/../`; cover every param).
- [ ] Same for other file params: `wiki` attachments, `ebook/show.php` paths, `learnPath` module paths, `mindmap` `jmpath`, `drives/` `dir`.
- [ ] Quotas can't be bypassed by parallel uploads or by uploading through another module (group docs vs course docs).
- [ ] Deleting a course/user removes its files from disk (harness `/file`).

### 3.9 Server/web configuration (docker image)
- [ ] With the nginx config in `docker/nginx/default.conf` (no deny rules besides `.ht*`) **(found while scanning – verify first)**, request:
      `/config/config.php` (must not leak source), `/config/` listing, `/courses/<code>/document/<stored name>` direct fetch of an invisible
      document, `/courses/<code>/work/…`, `/video/…`, `/storage/views/…`, `/storage/logs`, `/modules/admin/sysinfo/phpsysinfo.ini`,
      `/vendor/composer/installed.json`, `/composer.lock`, `/.git/HEAD`, `/tests/`, `/install/` after install, `/docker/`, `.env`, backup zips in
      `courses/garbage/`, `courses/archive/`, temp exports. Apache `.htaccess` rules don't apply under nginx, so each of these needs its own check.
- [ ] Response headers on every page: `X-Frame-Options` (`add_framebusting_headers`), `X-XSS-Protection`, `X-Content-Type-Options: nosniff`;
      note that `add_hsts_headers()` is commented out in `init.php`.
- [ ] Error pages and PHP warnings don't leak paths, SQL or stack traces (`debug.php` off; `display_errors` off in the image).
- [ ] `upgrade/` and `install/` can't be run by a non-admin on an installed site.

### 3.10 Injection
- [ ] SQL injection smoke on the parameters that are concatenated or used in `ORDER BY`/`LIMIT`: DataTables `order`, `columns`, `start`, `length`
      (`modules/user/index.php`, admin `listusers`/`listcours`, `message/`, `announcements/`, `request/`), search filters, `search_sql` builders
      in `modules/user/index.php`. Payloads `'`, `1 OR 1=1`, `SLEEP(3)`; assert no SQL error text and no timing difference.
- [ ] XML External Entities: IMS QTI import (`imsqtilib.php`), SCORM/IMS manifest import, course restore XML, BBB config XML (`bbbmoduleconf.php`),
      OAI requests → an `<!ENTITY xxe SYSTEM "file:///etc/passwd">` payload is not expanded.
- [ ] CSV/formula injection in exports (`dumpuser`, `dumpgradebook`, `dumppollresults*`, `dump_results*`, `dumpattendancebook`, usage dumps):
      values starting with `=`, `+`, `-`, `@` are escaped.
- [ ] Mail header injection: CR/LF in subject/recipient fields of contact form, `mailtoprof`, course mail, announcements by mail.
- [ ] LDAP injection in the login username (`*)(uid=*`) with the `sso` profile.
- [ ] Server-side template injection: Blade `{!! !!}` usage with user data (grep and test the views that use it).

### 3.11 SSRF & outbound requests
- [ ] Admin/teacher URL fields that the server fetches: AI test connection (`aitestconnection.php`), BBB/TC server URLs, external repositories
      (`extrepo_search.php`), course restore from URL, widgets, theme/logo URLs, QTI import with remote images, LTI tool URLs, OpenBadge backpack,
      video links (`ExtVideoUrlParser`) → `http://127.0.0.1:…`, `http://db:3306`, `http://169.254.169.254/`, `file://` are refused or at least
      return no internal data. Mock an internal HTTP service in the compose overlay to detect hits.

### 3.12 APIs
- [ ] REST v1: no token / expired / wrong IP / revoked → 401-style error 100 for every endpoint, GET and POST.
- [ ] Department-scoped tokens can't list or modify users/courses outside their departments (`Access::checkUserDepartmentAccess`,
      `checkCourseDepartmentAccess`).
- [ ] Tokens in `?token=` end up in logs; check that responses don't echo them and that `Authorization: Bearer` works without the query param.
- [ ] Mobile API (`mlogin`, `mtoken`, `mcourses`, `mtools`, `munits`): disabled when `enable_mobileapi` is off; a student token can't reach other courses.
- [ ] LTI: unsigned or badly signed launches are refused (`accept_unsigned` off); JWT with `alg: none` or a wrong `kid` is refused; nonce replay is refused.
- [ ] OAI-PMH/RSS/iCal of closed or inactive courses don't expose content; private feed tokens can't be guessed or reused across users.
- [ ] `user_sso/` token: single use, expires, bound to the username.

### 3.13 Business-logic abuse
- [ ] Exercises: resubmitting after the attempt limit, submitting after the end date/time limit by replaying the POST, changing the score via
      hidden fields, reading correct answers from the page source/AJAX before submitting, reopening a paused attempt of another user.
- [ ] Assignments: submitting after the deadline by replaying the POST; group submission for a group the student isn't in; viewing peer-review
      targets' names when reviews are anonymous.
- [ ] Polls: answering an anonymous poll twice; anonymous results don't reveal identities (`pollresults_per_user.php`); `behalf_of_user_mode` only for teachers.
- [ ] Gradebook/attendance: students can't post grades or mark their own presence outside the QR flow; QR presence links expire.
- [ ] Certificates: can't be generated by calling `ajax_certificate.php` without meeting the criteria.
- [ ] Self-registration can't set `status=1` (teacher) or extra fields by adding POST params to `newuser.php`.
- [ ] Profile update can't change `status`, `expires_at`, `whitelist`, `verified_mail` by adding POST params (mass assignment).
- [ ] Course creation by a student by posting directly to `create_course.php`.
- [ ] Suppressed words can't be bypassed with case/Unicode variants (low priority).

---

## 4. Public portal (anonymous) – P1

- [ ] Homepage `/index.php`: login box, announcements (`system_announcements.php`), homepage texts/widgets, FAQ, footer links.
- [ ] Info pages `info/{about,contact,copyright,faq,manual,privacy_policy,terms,accessibility}.php` render; privacy/accessibility only when enabled.
- [ ] Course catalog `modules/auth/courses.php` / `listfaculties.php`: browse the hierarchy, open/registration/closed icons, `E2EOPEN` link opens the course.
- [ ] Open courses (`course_metadata/opencourses.php`, `openfaculties.php`) when the open-courses feature is enabled.
- [ ] Global search `modules/search/search.php` (`enable_search`) as anon vs logged-in (private course content is not leaked).
- [ ] Language switch `?localize=en|el` on the homepage keeps the choice across pages.
- [ ] RSS: `rss.php`, `modules/announcements/rss.php?c=E2EOPEN`, `modules/blog/rss.php` – valid XML; closed courses are not exposed.
- [ ] Contact form (`admin/contact_form.php`, `info/contact.php`) → mail to admin in mailpit.
- [ ] Domain-check / not-installed page is not reachable on an installed site.

---

## 5. Logged-in user area (`main/`) – P1 (run as student, teacher, admin unless noted)

- [ ] Portfolio `main/portfolio.php`: course cards, widgets (`my_widgets.php`), announcements, upcoming events.
- [ ] My courses `main/my_courses.php`: list/grid, favourite toggle (`course_favorite.php`), unregister from a course (`unregcours.php`; refused when
      `disable_student_unregister_cours` is on).
- [ ] Course registration from the catalog: `E2EREG` instantly; `E2EREGPW` wrong/right password; `E2EREGWIN` outside the window refused;
      `E2ECLOSED` → request (`course_user_requests.php`) → teacher approves/rejects → mail.
- [ ] Profile `main/profile/profile.php`: edit name/email/phone/AM (respecting `dont_display_profile_*`), upload avatar (sizes LARGE/MEDIUM/SMALL),
      public/private flags, custom profile fields (§7), language preference.
- [ ] Public profile `display_profile.php?id=` – visibility rules (`ACCESS_PROFS` / `ACCESS_USERS`).
- [ ] Theme settings `main/profile/theme_settings.php` (user-level theme if allowed).
- [ ] Email unsubscribe `emailunsubscribe.php` per course and globally; afterwards course mails are not sent (mailpit).
- [ ] Notifications `main/notifications.php` / `main/notifications/` (unread counter, mark as read).
- [ ] Personal calendar `main/personal_calendar/`: create/edit/delete personal events, recurring events, course events shown, iCal export (`icalendar.php`).
- [ ] Notes `main/notes/` (CRUD, attach to a course/resource).
- [ ] My documents `main/mydocs/` (quota `mydocs_student_quota` / `mydocs_teacher_quota`; upload, folder, delete, quota exceeded).
- [ ] Messages (platform-level dropbox) – see §11.10.
- [ ] Gradebook totals `main/gradebookUserTotal/` (student sees own grades across courses).
- [ ] Certificates/badges `main/mycertificates.php`, `main/mybackpacks.php` (OpenBadge backpack connect is stubbed / markup only).
- [ ] ePortfolio `main/eportfolio/`: enable, edit fields, upload bio, add resources from courses (`resources.php`), public link with the token
      (`eportfolio_token`), visibility levels `EPF_VISIBLE_PUBLIC/USERS/PRIVATE` checked as anon/other user.
- [ ] Booking with tutors (`main/profile/add_available_dates.php`, `available_booking.php`, `book_create_delete.php`) – tutor sets slots, student books, both get mail.
- [ ] Invitations `main/invite.php` (accept a course invite sent by a teacher, §8.3).
- [ ] Idle detection (`enable_idle_detection`) shows the warning (P3).

---

## 6. Course creation & course administration (teacher; repeat key cases for admin/poweruser/depadmin) – P0

### 6.1 Create course (`modules/create_course/create_course.php`)
- [ ] Teacher creates a course: title, public code, department, language, visibility, password, start/end dates, license, description → lands on the course home.
- [ ] Student/guest cannot open the creation page.
- [ ] Validation: missing title/department; dates end < start.
- [ ] Flipped-classroom wizard (`flipped_classroom.php`, `edit_flipped_classroom.php`, `course_units_activities.php`).
- [ ] Collaborative course (`is_type_collaborative`) only when the collaboration platform is enabled.
- [ ] P3 – AI syllabus/course generation (`ai_generate_course.php`, `ai_extract_syllabus.php`) is hidden unless AI is configured.

### 6.2 Course settings (`modules/course_info/index.php`)
- [ ] Change title, code, language (the interface switches to the course language), department, visibility (all 4), password, dates, license, keywords.
- [ ] Toggles: offline course, users list access, log of course user requests, public docs write, agenda/announcement widget.
- [ ] Course home layout / description editing (`course_home/editdesc.php`, image upload `ajax_load_images.php`).
- [ ] Activate/deactivate modules (`course_tools`) → the module disappears from the side menu, and its URL is refused for students.
- [ ] External links / LTI apps in the course menu (`course_tools`: add link, add LTI app) appear in the side menu.
- [ ] Course widgets (`course_widgets/`): add/reorder/remove in COURSE_HOME_PAGE_MAIN/SIDEBAR.
- [ ] Course category values (`course_category/`) when categories are defined by admin.
- [ ] Course prerequisites (`course_prerequisites/`): add/remove prerequisite course.
- [ ] Course metadata / open-courses levels (`course_metadata/index.php`, `control.php`) as teacher and `oc_reviewer`.

### 6.3 Backup / restore / clone / refresh / delete
- [ ] Archive (`archive_course.php`) → zip downloaded and not empty.
- [ ] Restore (`restore_course.php`) as admin/poweruser from that zip into a new code, with users/without users.
- [ ] Clone (`clone_course.php`) – allowed only when `allow_teacher_clone_course` or `userRights_CloneCourse`.
- [ ] Refresh (`refresh_course.php`): delete users by date/dept/inactive, delete announcements/agenda/works/blog/wall → content gone, course kept.
- [ ] Delete course (`delete_course.php`) with confirmation → code no longer resolves (redirect home with `$langLessonDoesNotExist`).
- [ ] Import course (`import_course.php`) / Cadmos import (P3).
- [ ] `teacher_limited` without `userRights_ArchiveCourse` cannot archive; without `CourseAdminTools` cannot open course settings.

### 6.4 Course users (`modules/user/`)
- [ ] User list with filters (editor, course_reviewer, reviewer, students, guests), search, paging, export (`dumpuser.php` CSV/XLS).
- [ ] Add user by search (`adduser.php`), bulk add by username list (`muladduser.php`).
- [ ] Give/remove: course admin (`giveAdmin`), editor, course reviewer, open-courses reviewer, consultant; then log in as that user and check the new rights.
- [ ] A teacher cannot remove their own admin right when they are the last admin.
- [ ] Unregister a student from the course.
- [ ] Guest account (`guestuser.php`): create with password, log in as the guest, read-only access, delete the guest; page disabled when `course_guest=off`.
- [ ] Invitations (`invite.php`, `invite_one.php`, `invite_many.php`) → mail with a link → the invitee registers/enrols (`main/invite.php`).
- [ ] Registration requests for closed courses: list, approve, reject with a comment (`course_user_requests_appr/rej.php`) → mails.
- [ ] Mail to course users (with/without attachment) → mailpit receives one mail per recipient who has `receive_mail`.
- [ ] Group tutors / consultant flag displayed correctly.

---

## 7. Platform administration (`modules/admin/`) – P0/P1

Run each as `admin`. Re-run the user/course items as `poweruser`, `usermanager` and `depadmin` with their scope (see §3).

### 7.1 Dashboard & users – P0
- [ ] Admin index: online users, open requests counter, versions, latest course/teacher/student, cron table.
- [ ] Search users (`search_user.php` → `listusers.php`) by name, username, email, AM, status, department, auth method, inactive, date range.
- [ ] Create account (`newuseradmin.php`) as student and as teacher; welcome mail; duplicate username refused.
- [ ] Edit user (`edituser.php`): status, expiry date, department(s) (`user_multidep`), auth method, password reset, verify mail, enable/disable.
- [ ] Delete user (`deluser.php`) – refuses to delete the last admin / yourself; removes enrolments.
- [ ] Unregister from course (`unreguser.php`).
- [ ] Merge users (`mergeuser.php`): courses and submissions move to the target account.
- [ ] Bulk register (`multireguser.php`) from a CSV/text with generated passwords → users exist, optional enrolment, mails.
- [ ] Bulk enrol (`multicourseuser.php`), bulk edit/delete (`multiedituser.php`).
- [ ] Teacher account requests (`listreq.php`): approve, reject, mail.
- [ ] Mail to all teachers/students (`mailtoprof.php`) → mailpit count.
- [ ] Login as user (`change_user.php`) and back.
- [ ] User logs (`userlogs.php`), last logins (`user_last_logins.php`), login stats (`login_stats.php`), monthly report (`monthlyReport.php`).
- [ ] Update inactive users (`updatetheinactive.php`).
- [ ] Admins management (`addadmin.php`): grant/revoke each privilege level; depadmin with department; check the granted user's access right after.
- [ ] Mail verification settings (`mail_ver_settings.php`): change verification status in bulk.
- [ ] Custom profile fields (`custom_profile_fields.php`): create text/menu/date fields, required and registration-visible → shown on registration and profile.
- [ ] ePortfolio fields (`eportfolio_fields.php`).

### 7.2 Courses – P0
- [ ] Search courses (`searchcours.php` → `listcours.php`) by code, title, department, visibility.
- [ ] Edit course (`editcours.php`, `infocours.php`, `statuscours.php`, `quotacours.php`): change department, visibility, quotas (docs/video/group/dropbox).
- [ ] Delete course (`delcours.php`), bulk delete (`multicoursedel.php`), bulk create (`multicourse.php`), bulk edit (`multicoursedit.php`, `multieditcourse.php`).
- [ ] Restore course (`course_info/restore_course.php`) from the admin menu.
- [ ] Course feed (`coursefeed.php`), activity per course (`activity.php`).
- [ ] Auto-enroll rules (`autoenroll.php`): rule by department/status → a new student is enrolled automatically on registration.
- [ ] Default/disabled modules (`modules.php`, `modules_default.php`): a disabled module disappears from every course.
- [ ] Course categories (`coursecategory.php`, `coursecategoryvalues.php`).
- [ ] Certificate/badge templates (`certbadge.php`).

### 7.3 Hierarchy – P1
- [ ] `hierarchy.php`: add/edit/move/delete nodes, node status (`NODE_OPEN/SUBSCRIBED/CLOSED`), localized names; cannot delete a node with courses.
- [ ] depadmin manages only their subtree.

### 7.4 Platform configuration – P1
- [ ] `eclassconf.php`: change and persist each group (registration, profile display, quotas, login-fail, double login lock, search/indexing,
      social sharing, quick note, mobile API, strong passwords, notifications interval). One spec per group that checks the effect on the site, not only the saved value.
- [ ] Homepage management (`manage_home.php`, `homepageTexts_create.php`), footer (`manage_footer.php`), FAQ (`faq_create.php`),
      privacy policy & accessibility (`privacy_policy_conf.php`, `accessibility_conf.php`), contact info (`contact_info.php`) → visible to `anon`.
- [ ] Theme options (`theme_options.php`): create/clone/activate a theme, logo upload, colours → homepage uses it; white-label for depadmin (`enable_white_label`).
- [ ] Admin announcements (`adminannouncements.php`): create/order/visibility/date window/target role → shown on homepage/portfolio; depadmin's announcements scoped.
- [ ] Widgets (`widgets.php`): place on HOME_PAGE_MAIN/SIDEBAR, PORTFOLIO_PAGE_MAIN/SIDEBAR.
- [ ] Suppressed words (`suppressed_words.php`) → forum/comment containing them is filtered/refused.
- [ ] Maintenance (`maintenance_config.php`) on/off with a custom message.
- [ ] Collaboration platform (`collaboration_enable.php`) on/off → collaborative courses and sessions appear.
- [ ] Tenants (`tenants.php`, `tenant_edit.php`, `tenant_options.php`) with `enable_tenant`.
- [ ] API tokens (`apitokenconf.php`): create token with IP restriction and expiry (used by §13).
- [ ] Common documents (`commondocs.php`, `enable_common_docs`) → teacher can insert them into a course.
- [ ] Clean-up (`cleanup.php`), server check (`check_server.php`), phpinfo (`phpInfo.php`) render for admin.
- [ ] Usage statistics (`modules/usage/index.php?t=a`, `general_admin_stats.php`, `faculty_stats.php`, `dump_faculty_stats.php`).
- [ ] Upgrade page (`upgrade/index.php`) on an up-to-date DB says nothing to do (P3).

### 7.5 External integrations config – P2 (markup + save only, no real services)
- [ ] `extapp.php` lists apps; each config page saves and re-renders: BigBlueButton (`bbbmoduleconf`), Jitsi, Zoom, Webex, Google Meet, MS Teams,
      OpenDelos, UniFlix, Panopto, LimeSurvey, Turnitin, AutoJudge, Antivirus, Solr, H5P, OpenBadge, LTI publish, SEB, Coby, external repos,
      EduAPI, AI (`aimoduleconf`, `aitestconnection` with a stub endpoint), 2FA.
- [ ] Enabling an app shows its option in the course (e.g. TC type in `modules/tc`, Turnitin assignment type, AI question generation).

---

## 8. Course home, units & course navigation – P1

- [ ] Course home `/courses/<code>/`: description, units list, side menu with active modules only, announcements/agenda widgets, course info modal.
- [ ] Side menu differs by role: student sees active tools; teacher/editor/course_reviewer also see inactive tools and the admin section (`lessonToolsMenu`).
- [ ] Units (`modules/units/`): create/edit/delete/reorder units and sections; visibility; date-limited units; `units/info.php`.
- [ ] Insert every resource type into a unit (`insert_*.php`): doc, text, link, video, exercise, work, forum/topic, wiki, poll, ebook, LP, chat,
      blog, H5P, TC, external repository → the student opens each from the unit view.
- [ ] Unit prerequisites / completion criteria (`units/manage.php`, `prereq`) block the next unit for a student.
- [ ] Course completion / progress per unit (ties into §11.20).
- [ ] Course syllabus / description sections (`modules/course_description/`): add/edit/reorder/visibility.
- [ ] Course register page (`course_home/register.php`) for each visibility (open, registration, password, closed, inactive, expired).

---

## 9. Exercises (`modules/exercise`) – P0 (teacher creates, student takes, reviewer reviews)

- [ ] Create an exercise with the settings: description, start/end dates, time limit, attempts allowed, single/multiple/one-way page
      (`SINGLE_PAGE_TYPE`, `MULTIPLE_PAGE_TYPE`, `ONE_WAY_TYPE`), random questions, show results/answers policy, password, IP lock, assign to specific users/groups,
      certainty-based grading (`CALC_GRADE_METHOD_CERTAINTY_BASED`), SEB (`launch_seb.php`, markup only).
- [ ] Create one question of **every type** and answer it as a student, checking the score:
      `UNIQUE_ANSWER`, `MULTIPLE_ANSWER`, `TRUE_FALSE`, `FILL_IN_BLANKS`, `FILL_IN_BLANKS_TOLERANT` (whitespace/case), `FILL_IN_FROM_PREDEFINED_ANSWERS`,
      `MATCHING`, `DRAG_AND_DROP_TEXT`, `DRAG_AND_DROP_MARKERS` (`save_dropZones.php`), `CALCULATED` (wildcards), `ORDERING`, `FREE_TEXT` (manual grading),
      `ORAL` (recording markup), `UPLOAD_FILE` (upload, teacher downloads, delete answer — recent fixes in git log).
- [ ] Fill-in-blanks alternatives `[a|b]` both accepted; strict option (`fill_in_blank_strict`).
- [ ] Question pool (`question_pool.php`): reuse in another exercise, clone to another course, categories (`question_categories.php`), difficulty filters.
- [ ] Import Aiken/GIFT (`import_aiken.php`), IMS QTI import/export (`imsqti*.php`, `export.php`).
- [ ] Student attempt flows: start, save & continue later (`ATTEMPT_PAUSED`), time limit auto-submit, attempts exhausted, outside the date window,
      wrong password, cancel attempt.
- [ ] Teacher results (`results.php`, `exercise_result.php`, `results_by_question.php`, `exercise_stats.php`, `analytics.php`): manual grading of free text → the student sees the final score;
      accept/cancel attempts; delete attempts; export (`dump_results.php`, `dump_results_full.php` XLS).
- [ ] Visibility/results policy as student: results hidden until the end date, answers shown only after the end date, etc.
- [ ] `course_reviewer` can view results but not edit the exercise.
- [ ] Question preview (`question_preview.php`), game mode (`game.php`), duplicate exercise, clone to another course.
- [ ] P3 – AI question generation/evaluation hidden without AI config.

## 10. Assignments (`modules/work`) – P0

- [ ] Create assignment: description, deadline, late submission, max grade, file types, file count, group assignment, assign to specific users/groups,
      password lock / IP lock (`assignmentPasswordLock`, `assignmentIPLock`), notify on submission.
- [ ] Grading types: standard, scale (`grading_scales.php`), rubric (`rubrics.php`), peer review (`ASSIGNMENT_PEER_REVIEW_GRADE`, `reviews_per_user`, `grade_edit_review.php`).
- [ ] Student submits file(s)/text, resubmits, deletes before deadline; late submission marked late; after the deadline without late → refused.
- [ ] Group submission by a group member visible to the other members.
- [ ] Teacher grades with comments + file feedback → the student sees the grade/comment; notification mail.
- [ ] Download all submissions zip; non-submitted list (`disp_non_submitted`); results report (`work_result_rpt.php`); import grades (`import.php`).
- [ ] Submission on behalf of a student (`on_behalf_of`).
- [ ] Clone assignment to another course.
- [ ] Peer review: students review each other → teacher sees reviews.
- [ ] P3 – Turnitin/AutoJudge types only when enabled (markup).
- [ ] Security: student2 cannot fetch student's submission (`get=`), covered in §3.

## 11. Other course modules – P1 unless noted

### 11.1 Announcements (`announcements/`) – P0
- [ ] CRUD, rich text, visibility, start/end date window, pin, tags, copy to another course, bulk actions, search/paging.
- [ ] Send by mail to all/selected recipients → mailpit; `myannouncements.php` lists across courses; RSS shows only visible ones.
- [ ] Student sees only visible + in-window; anon sees them in `E2EOPEN` only when public.

### 11.2 Documents (`document/`) – P0
- [ ] Upload file(s) (Uppy/dropzone), create folder, rename, move, replace, comment, metadata (LOM fields), visibility, public flag, copyright flag.
- [ ] Create a text/HTML document (`new.php`), external link (`external_url`), cloud drive picker (`drives/`, markup).
- [ ] Download a single file, bulk download as zip, bulk delete/visibility.
- [ ] Quota exceeded (`doc_quota` from the admin course quota) → error.
- [ ] Student: sees only visible files; `enable_docs_public_write` lets students upload.
- [ ] Media playback (`play.php`), audio/video recording pages render (`rec_audio.php`, `rec_video.php`).
- [ ] Group documents (subsystem GROUP), ebook documents (EBOOK), common documents (COMMON), my documents (MYDOCS) use the same UI with the right scoping.

### 11.3 Agenda (`agenda/`)
- [ ] CRUD events, recurring (`frequencynumber/period`), visibility, duration; iCal export (`icalendar.php`); shown in the personal calendar of enrolled users.

### 11.4 Links (`link/`)
- [ ] CRUD links and categories, reorder, social view settings; student opens a link (redirect is logged).

### 11.5 Video (`video/`)
- [ ] Upload video, add video link (YouTube embed), categories, visibility, public flag, `video_quota`; student plays; P3 Delos/UniFlix with stubs.

### 11.6 Forums (`forum/`) – P0
- [ ] Teacher: categories/forums CRUD, forum linked to a group (private forum), settings (ratings/abuse).
- [ ] Student: new topic, reply, edit own post, attachment, quote; cannot edit others' posts.
- [ ] Teacher: lock/pin/move/delete topic, delete post.
- [ ] Notifications: subscribe to forum/topic/category (`forumnotify`, `topicnotify`) → mail on reply.
- [ ] Export (`export_ans`). Abuse report on a post (§11.22).

### 11.7 Groups (`group/`)
- [ ] Create groups (bulk quantity, max members), categories, settings (self-registration, self-unregistration, allow unreg, private forum, documents, wiki).
- [ ] Assign tutor; tutor sees "my groups"; tutor manages group space.
- [ ] Student self-registers/unregisters; full group refused.
- [ ] Group space (`group_space.php`): forum, documents (`document.php`), description, email to group.
- [ ] Bulk add users (`muladduser.php`), dump groups (`dumpgroup.php`).
- [ ] Bookings with tutor (`booking.php`, `datesTutor.php`, `date_available.php`) – P2.

### 11.8 Glossary (`glossary/`)
- [ ] Terms CRUD, categories, config (index, expand), URL, CSV dump; student view filtering by letter/category.

### 11.9 E-books (`ebook/`)
- [ ] Create from an uploaded zip/HTML, sections/subsections, visibility, reorder; student reads (`show.php`, `play.php`).

### 11.10 Messages / dropbox (`message/`) – P0
- [ ] Course dropbox: student→teacher, teacher→all students, to a group, with attachment; inbox/outbox, delete, reply.
- [ ] Recipient autocomplete (`load_recipients.php`) only offers allowed recipients (students can't message all users if restricted).
- [ ] Platform-level messages (no course) from the portfolio.
- [ ] Attachment download is restricted to sender/recipients (`message_download.php`, §3).
- [ ] `dropbox_quota`; mail notification copy (mailpit).

### 11.11 Chat (`chat/`)
- [ ] Create/edit/delete conference, visibility, activate; two users exchange messages (two browser contexts, polling `messageList.php`); reset, store log as a document.
- [ ] P3 – chat agent (`agentcb.php`, `create_agent`) only with AI.

### 11.12 Questionnaires / polls (`questionnaire/`) – P0
- [ ] Create a poll with every question type: `QTYPE_SINGLE`, `QTYPE_MULTIPLE`, `QTYPE_FILL`, `QTYPE_SHORT`, `QTYPE_LABEL`, `QTYPE_SCALE`, `QTYPE_TABLE`,
      `QTYPE_DATETIME`, `QTYPE_DATE`, `QTYPE_FILE`.
- [ ] Settings: anonymous, start/end date, multiple submissions, assign to specific users/groups, show results to students, QR code.
- [ ] Poll types: normal, COLLES (`colles.php`), ATTLS (`attls.php`), quick poll on the course home (`QPOLL_HOME`), course evaluation.
- [ ] Student participates (`pollparticipate.php`), multi-page, required questions; cannot participate twice unless allowed.
- [ ] Results (`pollresults.php`, per user, multiple submissions), charts, export (`dumppollresults*.php`), delete results, clone to another course.
- [ ] Answer on behalf of a user (`behalf_of_user_mode`).
- [ ] P3 – LimeSurvey type only when enabled.

### 11.13 Learning paths (`learnPath/`) – P1
- [ ] Create LP, add modules from documents/exercises/links/media/description (`insertMy*.php`), reorder, visibility, prerequisites/blocking.
- [ ] Import a SCORM 1.2/2004 package (`importLearningPath.php`) from `test-data/`.
- [ ] Student runs the LP in the viewer (`viewer.php`, `navigation/`), progress is tracked (`record_action.php`), exercise inside the LP is scored.
- [ ] Teacher progress reports (`details*.php`), export xls/pdf, clean attempts.
- [ ] Modules pool (`modules_pool.php`).

### 11.14 Wiki (`wiki/`)
- [ ] Create wiki with ACL (course/group, read/edit/create per role), create/edit pages, history/diff, restore version, search, printable.
- [ ] Student edit respects ACL; group wiki only for members.

### 11.15 Blog (`blog/`) and comments/rating (`comments/`, `rating/`)
- [ ] Teacher posts; settings let students post (`submitSettings`); comment on posts; edit/delete own comments; rating widget (like/fivestar/thumbs).
- [ ] Personal blog on the portfolio (`comments_perso_blog.php`, `rate_perso_blog.php`).

### 11.16 Wall (`wall/`)
- [ ] Post text/link/video and resources (`insert_*.php`), pin, edit/delete own, load more; student posting allowed/blocked by settings.

### 11.17 Gradebook (`gradebook/`) – P0
- [ ] Create gradebook, activities (manual, linked to assignment/exercise/LP/TC), weights, range, users (all/specific/date range).
- [ ] Grade manually + automatic grades from a graded exercise/assignment (`refreshgrades.php`), import grades, dump (`dumpgradebook.php`).
- [ ] Student sees their own grades only; totals in `main/gradebookUserTotal/`.

### 11.18 Attendance (`attendance/`)
- [ ] Create attendance book, activities (manual/linked to TC), mark presence, limit, QR presence (`qrCode_presence`, `download_qrcode`), import, dump.
- [ ] Student sees own attendance; student marks presence via QR link.

### 11.19 Teleconference (`tc/`) – P2
- [ ] With a stub BBB server (or markup only): create/edit/delete session, dates, notify users (mailpit), external users, lock settings, record option.
- [ ] Join link shown only inside the date window; student join refused before start.
- [ ] Zoom/Jitsi/Google Meet/Teams/Webex types: create with a link → student sees the link.
- [ ] TC attendance report (`tc_attendance.php`, `tcuserduration.php`).

### 11.20 Progress / certificates / badges (`progress/`) – P1
- [ ] Create a certificate and a badge with criteria from every activity type (assignment, exercise, forum, LP, poll, document view, wiki, blog,
      attendance, gradebook, ebook, multimedia, course participation, course completion grade).
- [ ] A student completes the criteria → the certificate is issued (`mycertificates.php`), PDF download, threshold/operator logic, deadline.
- [ ] Points game / leaderboard (`PointsGame`, `enable_leaderboard`, `anonymize_leaderboard`).
- [ ] Teacher results dump (`dumpcertificateresults.php`). Badge export to a backpack is allowed only when `allow_badge_export`.

### 11.21 Requests (`request/`)
- [ ] Teacher enables request types; student creates a request with a file, watchers; teacher assigns, changes state, comments; mails to watchers.

### 11.22 Abuse reports (`abuse_report/`)
- [ ] Student reports a forum post/comment/link → teacher sees it in the course and resolves it; mail to the teacher.

### 11.23 Sticky notes (`sticky_notes/`)
- [ ] Topics/categories, posts with colours, edit/delete permissions (`allow_edit`, `allow_delete`), paging.

### 11.24 H5P (`h5p/`)
- [ ] Upload an `.h5p` file, view (`view.php`, `show.php`), reuse, delete; create from a library (markup) – P2.

### 11.25 Course analytics (`analytics/`)
- [ ] Create an analytics set with elements (thresholds, weights), view per user, download; student cannot open it.

### 11.26 Course usage/statistics (`usage/`)
- [ ] Teacher: course stats, user duration, logs (`displaylog.php`) for actions done earlier in the run; export.
- [ ] Student: cannot open them.

### 11.27 Tags (`tags/`)
- [ ] Tag an announcement/document/exercise → tag page lists them; feed.

### 11.28 Offline course (`offline/`)
- [ ] Enable offline → download the offline zip (IMSCP); it contains course content.

### 11.29 Mindmap (`mindmap/`) – P3 (deprecated)
- [ ] Renders and saves a map into documents.

### 11.30 Course search (`search/search_incourse.php`)
- [ ] Finds announcements/documents/forum posts after indexing (`idxpopup.php?reindex` as admin).

---

## 12. Collaboration platform & sessions (`modules/session`) – P2

Run with `show_collaboration` on and `E2ECOLLAB`.
- [ ] Coordinator creates sessions (one-to-one/group), dates, resources (doc/link/poll/TC/work), prerequisites between sessions.
- [ ] Consultant sees `index_consultant.php`, marks completion (`consulting_completion_consultant.php`), uploads reference docs.
- [ ] Simple user sees `index_user.php`, accepts the session (`session_acceptance.php`), uploads deliverables, completes polls.
- [ ] Completion/badges per session, user reports (`user_report.php`, dumps).
- [ ] `show_always_collaboration` mode: the whole platform behaves as collaboration (language strings switch).

---

## 13. APIs & machine endpoints (`tests/api`, using Playwright `request`) – P1

- [ ] REST v1 (`api/v1/*`) with a token from §7.4: as Bearer header, `?token=` and POST `token`; missing/expired/wrong-IP token → error 100.
- [ ] `courses` (list/create), `users` (list/create), `registeruser`, `enroll_user` / `unenroll_user`, `groups`, `sections`, `documents`,
      `categories`, `clone_course`, `registration`, `sso`, `scorms`, `scormtracking*` – happy path + validation errors; the effect is checked in the UI.
- [ ] Department-restricted tokens only see their departments (`Access::checkUserDepartmentAccess`).
- [ ] Mobile API (`modules/mobile/m*.php`, `enable_mobileapi`): login → token → courses → tools → units → logout; disabled → refused.
- [ ] LTI provider (`modules/lti/`): `certs.php` JWKS is valid JSON; cartridge XML; an invalid launch is refused. LTI consumer (`lti_consumer/`):
      add an app, launch form posts the right fields (P3).
- [ ] OAI-PMH (`modules/oai/oai2.php`): Identify, ListMetadataFormats, ListSets, ListRecords, GetRecord return valid XML.
- [ ] iCal feeds (agenda, personal calendar) and RSS (announcements, blog, `rss.php`) are valid; private feeds need the token.
- [ ] `user_sso/` token login (P3).

---

## 14. Notifications & mail (mailpit) – P1

Cross-cutting checks (assert recipients, subject and the link inside):
- [ ] Registration welcome, teacher request approved/rejected, password reset, email verification.
- [ ] Course registration request, approval/rejection, invitation.
- [ ] Announcement by mail, forum/topic subscription, dropbox message copy, assignment submission notify, grade released, poll/TC notifications, request watchers.
- [ ] `receive_mail = 0` / `EMAIL_UNVERIFIED` / course unsubscribe → nothing is sent.
- [ ] Digest mode (`user_notifications`, `user_notifications_interval`) via harness `/cron`.

---

## 15. Logging & statistics side effects – P2
- [ ] Login/logout rows in `loginout`; `LOG_LOGIN_DOUBLE`; course actions logged (`Log::record`) for insert/modify/delete in announcements, documents, exercises.
- [ ] `admin/userlogs.php` and course `usage/displaylog.php` show those actions.

---

## 16. Installer & upgrade – P2
- [ ] Wizard on an empty stack (separate Playwright project): requirements → license → DB → site config → theme → email → review → installed.
      Assert in English (`?localize=en` / `$_SESSION['lang']`) instead of Greek strings.
- [ ] Wrong DB credentials → error at step 3, no config written.
- [ ] Re-running `/install/` on an installed site is refused.
- [ ] CLI install (`php install/index.php` with env) produces a site where admin can log in (this also covers the setup path).
- [ ] `upgrade/` with an admin login: nothing to do on the current version.

---

## 17. i18n & accessibility & UI – P2/P3
- [ ] Greek smoke: homepage, login, portfolio, one course module render with Greek strings and no PHP notices.
- [ ] Course language forces the interface language (`course.lang`) for every user inside the course.
- [ ] No PHP warnings/notices/`Fatal error` in any page visited by the suite: add an `afterEach` hook that fails on page text matching
      `/(Warning|Notice|Deprecated|Fatal error):/` and on uncaught console errors.
- [ ] Mobile viewport project (`devices['Pixel 7']`) for login, portfolio, course home, side menu toggle (P3).
- [ ] P3 – `@axe-core/playwright` scan on homepage, login, portfolio, course home, exercise attempt.

---

## 18. Suggested order of work
1. §0 infrastructure + §1 seed + `auth.setup.ts` storing sessions for every role.
2. §2 auth and §3 security: first verify the four "found while scanning" items, then §3.1 matrix, §3.3 IDOR, §3.5 CSRF, §3.8 uploads.
3. §6 course lifecycle, §9 exercises, §10 assignments, §11.1/11.2/11.6/11.10/11.12/11.17 (P0 modules).
4. §7 admin users/courses, then config.
5. The remaining modules, §13 APIs, §14 mail, then P2/P3.

*Generated from a scan of `include/init.php` (roles and course access), `include/constants.php`, `modules/*`, `main/*`, `api/v1` and
`resources/views/layouts/partials/sidebarAdmin.blade.php` (admin menu by privilege).*
