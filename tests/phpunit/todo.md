# Open eClass – coding standards (PHPCS) & PHPUnit – TODO

Branch: `feat/coding-standards-and-tests`. Priorities: **P0 (Critical)** → **P1 (High)** → **P2 (Medium)** → **P3 (Low)**.

The plan is based on a full pass over `include/`, `modules/`, `main/`, `api/`, `install/` and `upgrade/`:
- 1,009 PHP files, 4,757 functions and methods, classified with a tokenizer script by what they touch (DB, globals, superglobals,
  config, output, filesystem, network, time, randomness);
- a full PHPCS run with the branch's `phpcs.xml`;
- the existing PHPUnit suite;
- the lint workflow's command, run locally.

Numbers are from 2026-10-03 on top of `upstream/master` `04ce7b684`.

---

## 0. Where the branch stands

| Item | State |
|---|---|
| `composer.json` require-dev | `squizlabs/php_codesniffer ^4.0` (4.0.1), `phpunit/phpunit ^11.5` (11.5.55, chosen for PHP 8.2) |
| `phpunit.xml` | one `Unit` suite; `Integration` commented out; `<source>` = `modules/` + `include/` |
| Tests | `tests/phpunit/Unit/FormatByteSizeTest.php`, `MakeClickableTest.php` → **21 tests, 21 assertions, all green** |
| `phpcs.xml` | PSR-12, `LineLength` off, excludes `vendor`, `node_modules`, `tests`, `storage`, `*.blade.php` |
| PHPCS baseline | **98,643 errors + 2,009 warnings in 1,216 of 1,259 files**; 96,861 auto-fixable |
| Lint (`php -l`, PHP 8.4) | passes for `include/ modules/ resources/ tests/` |
| Workflows | `lint.yml`, `phpunit.yml`: push on `master`, `pull_request`, `workflow_dispatch`; `phpcs.yml`: `workflow_dispatch` only (2026-10-03) |

### 0.1 Problems in the current setup – P0
- [x] **`composer.lock` didn't install on PHP 8.2** (2026-10-03). Adding the dev packages had updated the whole lock, which pulled
      `maennchen/zipstream-php` 3.2.2 (needs PHP ≥ 8.3) while `composer.json` says `"php": ">8.2"`. Rebuilt from upstream's lock with only
      `squizlabs/php_codesniffer` + `phpunit/phpunit` and their dependencies, resolved on PHP 8.2: runtime packages are identical to upstream,
      27 dev packages added. `phpunit.yml` no longer needs `--ignore-platform-reqs`; it lists the required `ext-*` in `setup-php` instead.
      Checked: clean install + 21 tests green on PHP 8.2 and 8.5.
- [x] **PHPCS workflow is manual** (`workflow_dispatch` only) until §1.3 is in place; on PRs it would fail against the ~100k-violation baseline.
- [ ] **Bootstrap warning:** `tests/phpunit/bootstrap.php` defines `ECLASS_VERSION`, then `include/constants.php` (loaded by
      `main_lib.php`) defines it again → `PHP Warning: Constant ECLASS_VERSION already defined` on every run. Drop it from the bootstrap
      (or `require` `include/constants.php` there instead). Make PHPUnit fail on warnings (`failOnWarning="true"`) so this can't creep back.
- [ ] **Schema version mismatch:** `phpunit.xml` points at `https://schema.phpunit.de/12.5/phpunit.xsd` while PHPUnit 11.5 is installed.
      Use the 11.5 schema (`vendor/bin/phpunit --migrate-configuration` does it).
- [ ] **Tests depend on the working directory:** they `require_once 'include/main_lib.php'` (relative), which only works when PHPUnit runs
      from the repo root. Use `BASE_DIR . '/include/main_lib.php'` (the bootstrap defines `BASE_DIR`), or load it once in the bootstrap.
- [ ] `BASE_DIR` is defined in the bootstrap but nothing uses it; main code uses `$webDir`. Set `$GLOBALS['webDir'] = BASE_DIR` there,
      since `FileCache`, `include/init.php` and several helpers read it.
- [ ] `phpcs.xml` `<config name="testVersion" value="8.2-"/>` does nothing: that setting belongs to **PHPCompatibility**, which isn't installed
      (§1.4).
- [ ] `phpcs.xml` doesn't exclude the bundled third-party code (54,415 errors, more than half of the total) – §1.1.
- [ ] `tests/phpunit/Integration/` is empty and its suite is commented out – §4.
- [ ] Lint workflow checks `include/ modules/ resources/ tests/` only: `main/`, `api/`, `install/`, `upgrade/`, `info/`, `maintenance/`,
      `Widgets/`, `domain_check/`, `index.php`, `rss.php`, `cron_disk_usage.php` are never linted. Lint everything except `vendor/` and `node_modules/`.
- [ ] Lint step runs `php -l` once per file sequentially; use `xargs -0 -n1 -P$(nproc)` (8 s locally for the current scope).
- [ ] `phpcs.yml` runs a 4-version PHP matrix; PHPCS output doesn't depend on the PHP version, so one job is enough (keep the matrix for
      lint and PHPUnit).
- [ ] `actions/checkout@v6` vs `@v4` elsewhere in the repo – pick one.
- [ ] `tests/phpunit/.cache` is ignored, but add coverage output (`tests/phpunit/coverage/`) once §2.4 lands.
- [ ] `tests/dspace_smoke.php` (226 lines, upstream) is a manual smoke script, not a PHPUnit test; leave it out of the suites or turn it
      into an integration test behind a group (§4.9).

---

## 1. Coding standards (PHPCS)

### 1.1 Exclude bundled third-party code – P0
These are upstream copies of external libraries and must not be reformatted (it would make later updates of them impossible):

| Path | Library | PHPCS issues |
|---|---|---|
| `modules/admin/sysinfo/` | phpSysInfo | 39,246 |
| `include/QueryPath/` | QueryPath | 5,311 |
| `include/phpmathpublisher/` | PhpMathPublisher | 4,918 |
| `include/simplehtmldom/` | simple_html_dom | 2,647 |
| `include/Zend/` | Zend Search Lucene | 1,961 |
| `modules/lti/ltiprovider/` | IMS / ceLTIc LTI Tool Provider | 1,039 |
| `modules/wiki/lib/wiki2xhtml/` | wiki2xhtml | 131 |
| `include/lib/PasswordHash.php` | phpass | 15 |

- [ ] Add each as `<exclude-pattern>` in `phpcs.xml`, and as `<exclude>` under `<source>` in `phpunit.xml` (so coverage numbers mean something).
- [ ] Also exclude generated/runtime paths: `config/`, `courses/`, `video/`, `storage/`, `js/` (built assets), `template/*/` compiled CSS
      helpers if any PHP there is generated.
- [ ] After this, own code is **1,014 files / 44,228 errors / 1,156 warnings**, of which **43,408 are auto-fixable** and **1,976 are not**.

### 1.2 Tailor PSR-12 to a legacy procedural codebase – P1
Non-fixable sniffs in own code that are about structure, not style. Each needs a decision (exclude now, revisit later):

| Count | Sniff | Why it fires | Suggestion |
|---|---|---|---|
| 442 | `PSR1.Files.SideEffects.FoundWithSymbols` | page scripts declare functions **and** run code | exclude (inherent to the architecture) |
| 282 | `PSR1.Classes.ClassDeclaration.MissingNamespace` | no namespaces anywhere | exclude for now |
| 317 | `PSR12.Properties.ConstantVisibility.NotFound` | `const X` without `public` | fixable by hand, low risk – keep as warning |
| 258 | `Squiz.Scope.MethodScope.Missing` | methods without `public` | same |
| 249 | `PSR1.Methods.CamelCapsMethodName.NotCamelCaps` | `get_foo()` methods called all over | exclude (renaming = API break) |
| 69+69 | `PSR2.Classes.PropertyDeclaration.VarUsed` / `ScopeMissing` | `var $x;` | fix by hand (safe) |
| 103 | `Squiz.ControlStructures.ControlSignature.SpaceAfterCloseBrace` (non-fixable part) | `} // comment else` patterns | review |
| 66 | `PSR2.ControlStructures.SwitchDeclaration.WrongOpenercase` | `case X;` | fix by hand |
| 19 | `PSR1.Classes.ClassDeclaration.MultipleClasses` | several classes per file | exclude or split |
| 17 | `Squiz.Classes.ValidClassName.NotPascalCase` | | exclude |

- [ ] Decide per sniff, then encode it in `phpcs.xml` (`<rule ref="..."><severity>0</severity></rule>` or `<exclude name=...>`), each with a
      one-line comment saying why.
- [ ] Decide on **tabs vs spaces**: `Generic.WhiteSpace.DisallowTabIndent` alone is 29,218 issues. If the team prefers tabs, override
      instead of reformatting.
- [ ] Decide on `Generic.ControlStructures.InlineControlStructure` (1,213: `if ($x) return;` without braces) – fixable, but touches logic lines.

### 1.3 How to roll it out without a 40k-line diff – P0
A full `phpcbf` run rewrites almost every file and makes merging with `gunet/openeclass` painful. Options, in order of preference:
- [ ] **Check only changed lines in CI.** In `phpcs.yml` on `pull_request`: `git diff --name-only origin/master...HEAD -- '*.php'` → `phpcs` on
      those files, and filter the report to changed lines (e.g. `phpcs --report=diff` per file, or a small script over `--report=json` + `git diff -U0`).
      New code is clean, old code is untouched.
- [ ] **Baseline file**: record current violations and fail only on new ones (PHPCS 4 has no native baseline; use
      `digitalrevolution/php-codesniffer-baseline`, or the json-diff script above).
- [ ] Optional, coordinated with upstream: auto-fix one directory per PR (`phpcbf modules/glossary` …), smallest first, with no logic changes
      in the same PR. Own-code issues by directory: `modules/session` 3,812, `modules/admin` (own part) 3,509, `modules/exercise` 2,695,
      `include/lib` 1,851, `main/eportfolio` 1,729, `modules/progress` 1,657, `modules/group` 1,598, `modules/questionnaire` 1,565 …
      Only 42 own files are clean today.
- [ ] Add `.git-blame-ignore-revs` for any formatting-only commit.
- [ ] Add an `.editorconfig` that matches the chosen ruleset (indent, final newline, trailing whitespace) so editors do it before PHPCS.
- [ ] `composer` scripts: `composer lint`, `composer cs` (`phpcs`), `composer cs:fix` (`phpcbf`), `composer test` (`phpunit`).

### 1.4 Extra static checks – P2
- [ ] **PHPCompatibility** (`phpcompatibility/php-compatibility` + `dealerdirect/phpcodesniffer-composer-installer`) with `testVersion 8.2-`,
      which is what `phpcs.xml` already asks for. Catches syntax/functions removed in newer PHP across the 8.2–8.5 matrix.
- [ ] **PHPStan level 0** with a baseline: finds undefined functions/classes, wrong argument counts, unreachable code. Bootstrap it with
      `include/constants.php` and a list of the global `$lang*` strings to keep noise down. More useful for bug-finding than PSR-12.
- [ ] Security sniffs worth a look: `Generic.PHP.ForbiddenFunctions` for `eval`, `create_function`, `extract`, `unserialize` on input.

---

## 2. PHPUnit infrastructure

### 2.1 Bootstrap – P0
- [ ] Load once: `vendor/autoload.php`, `include/constants.php`, `include/main_lib.php` (which pulls `lib/theme.php`, `log.class.php`,
      `lib/session.class.php`, `lib/file_cache.class.php`, `lib/hierarchy.class.php`, `modules/admin/tenant_functions.php`).
- [ ] Set `$GLOBALS['webDir'] = BASE_DIR`, `$urlServer`, `$urlAppend`, `$language = 'en'`.
- [ ] Load language strings once (`lang/en/common.inc.php`, `lang/en/messages.inc.php`) into `$GLOBALS`, because many otherwise pure
      functions only read `$lang*` globals (`format_time_duration`, `langcode_to_name`, the `Log::*_action_details` formatters, `CertaintyBasedButtons` …).
- [ ] `chdir(BASE_DIR)` in the bootstrap: several includes use paths relative to the web root.
- [ ] A `Tests\Support\` namespace (autoload-dev in `composer.json`) for helpers below.

### 2.2 Isolating globals and config – P0
- [ ] `GlobalsTestCase` base class: snapshot `$GLOBALS`, `$_SESSION`, `$_GET`, `$_POST`, `$_SERVER` in `setUp()` and restore in `tearDown()`
      (or `#[BackupGlobals(true)]`), so tests can set `$_SESSION['uid']` etc. safely.
- [ ] **`get_config()` without a DB:** it reads through `FileCache('config', 300)`, and `FileCache` disables itself under CLI
      (`php_sapi_name() == 'cli'` → no cache file), so it always hits `Database::get()`. For unit tests, provide a fake: a small
      `Database` test double registered before `main_lib.php` loads (the class lives in `modules/db/database.php`), or a
      `TestConfig::set('code_key', '...')` seam. Needed by `token_generate/validate`, `isWhitelistAllowed`, `choose_password_strength`, `profile_image_hash` …
- [ ] Freeze time where needed (`token_generate` with timestamps, `safe_filename`, date helpers): wrap `time()` calls behind a clock only if
      a test really needs it; otherwise assert ranges.

### 2.3 Test layout – P1
```
tests/phpunit/
├── bootstrap.php
├── Support/                 # GlobalsTestCase, DatabaseTestCase, fakes, fixture loaders
├── Unit/
│   ├── MainLib/             # one file per group of main_lib.php helpers
│   ├── Lib/                 # include/lib/*
│   ├── Exercise/  LearnPath/  Progress/  Wiki/  Video/  Tc/  Lti/  Ai/  ExternalRepos/ …
└── Integration/             # needs MariaDB (§4)
```
- [ ] Data providers (`#[DataProvider]`) for the table-like helpers (IP matching, file extensions, sizes, transliteration).
- [ ] Group attributes: `#[Group('db')]`, `#[Group('network')]` so CI can run `--exclude-group network`.

### 2.4 Coverage & CI – P2
- [ ] Coverage with PCOV in one matrix job (`shivammathur/setup-php` `coverage: pcov`), `--coverage-clover`, upload as artifact.
- [ ] Report only on own code (after the §1.1 excludes in `<source>`).
- [ ] Integration job with a `mariadb:10.11` service (same version as `docker-compose.development.yaml`) – §4.

---

## 3. Unit tests (no database)

Legend: **pure** = no DB / globals / superglobals / I/O according to the scan; **globals** = only reads `$lang*` or similar globals.

### 3.1 `include/main_lib.php` – P0
69 of its 226 functions are pure. Grouped:

**Security-relevant helpers**
- [ ] `isIPv4`, `isIPv4cidr`, `isIPv6`, `isIPv6cidr`, `ip_v4_cidr_match`, `ip_v6_cidr_match`, `inet_to_bits`, `match_ip_to_ip_or_cidr` – used by the
      exercise IP lock (`exercise_submit.php:421`), the assignment IP lock (`work/functions.php:422`) and API token IP restrictions
      (`api/v1/access.class.php:172`). Cases: exact IPs, /0, /32, /24, /31, mixed v4/v6 lists, garbage input, whitespace in lists.
  - **Bug found (verified):** `inet_to_bits()` uses `unpack('A16', …)`, and the `A` format strips trailing NUL bytes and spaces. A subnet
    whose packed form ends in zero bytes loses bits, so `ip_v6_cidr_match('2001:db8:0:1::5', '2001:db8::/48')` returns **false** (should be
    true). Legitimate IPv6 users get refused by IP locks. Fix: `unpack('a16', …)` or `inet_pton` + `bin2hex`. Write the failing test first.
- [ ] `generate_csrf_token`, `validate_csrf_token` (strict `!==`), `generate_csrf_token_form_field`, `generate_csrf_token_link_parameter`.
- [ ] `token_generate` / `token_validate` (needs the config seam for `code_key`): round trip, wrong info, tampered token, expired timestamped
      token, token from another key.
  - **Weakness to test/fix:** `token_validate` compares with `==` (`include/main_lib.php:3574`). Two numeric-looking hex strings are
    compared as numbers in PHP, so a "magic hash" (`0e` + digits) HMAC would accept the token `"0"`. Very unlikely with a 40-char
    ripemd160 hex, but the comparison should be `hash_equals()` (constant time too). Same review for `rss_token_valid`, `getIndirectReference`.
- [ ] `is_url_accepted` (rejects `javascript:` even with spaces/case, `http://` alone, protocol filter), `canonicalize_url` (adds `http://`
      only without a scheme; `mailto:` kept), `valid_email` (non-ASCII rejected, RFC validation via egulias).
- [ ] `q()`, `js_escape()`, `q_math` (globals) – escaping of quotes, `<script>`, unicode.
- [ ] `remove_filename_unsafe_chars`, `my_basename` (`../` sequences, Windows separators), `my_dirname`, `get_file_extension` (`.tar.gz`,
      no extension, 9-char extension, upper case), `safe_filename` (time+rand: assert format only).
- [ ] `framebusting_code`.
- [ ] `directHash`, `getIndirectReference`/`getDirectReference`/`getAndUnsetDirectReference` (object-reference indirection; need `$_SESSION`).

**Text & encoding**
- [ ] `html2text`, `make_clickable` (exists: extend with `javascript:` links, existing `<a>` tags, punctuation at URL end), `ellipsize`,
      `ellipsize_html` (doesn't break tags or entities, multibyte), `canonicalize_whitespace` (keeps newlines), `invalid_utf8`, `sanitize_utf8`,
      `utf8_to_cp1253`, `cp737_to_utf8`, `greek_to_latin`, `remove_accents`, `math_unescape`, `dom_save_html`, `varmsg`, `form_popovers`, `icon`.
- [ ] `base64url_encode` / `base64url_decode` round trip (padding, `+/` → `-_`), `imap_literal`, `urlenc`, `mailto`.

**Numbers, sizes, time**
- [ ] `format_bytesize` (exists; note it labels KiB as `Kb`), `formatBytes`, `parseSize` (`1.5M` → 1,572,864; `512`; `2G`; `1k`; garbage),
      `fileUploadMaxSize` (ini-dependent: assert relation with `parseSize`), `fix_float`, `timeToSeconds` (`01:02:03`, `90`, `00:00:00`),
      `datetime_remove_seconds`, `format_time_duration` (globals).
- [ ] `checkPHPVersion`.

**Arrays & misc**
- [ ] `closest`, `array_value_recursive`, `reindex_array_keys_from_one`, `removeGetVar`, `stringStartsWith`, `stringEndsWith`, `append_units`,
      `multiselection`, `selection3`, `randomkeys` (length/charset), `generate_secret_key`.

### 3.2 `include/lib/` – P1
- [ ] `fileUploadLib.inc.php`: `replace_dangerous_char`, `getPureFileExtension`, `enough_size`, `get_max_upload_size`; `isWhitelistAllowed` with the
      config seam: blocks `.php`, `.php5`, `.phtml`, `.phar`, `.PhP`, `x.php.` … even with `*` in the whitelist; student vs teacher vs per-user lists.
- [ ] `fileDisplayLib.inc.php`: `choose_image` (icon per extension), `format_file_size`, `format_url`, `file_url_escape` (spaces, `#`, `?`, unicode).
- [ ] `forcedownload.php`: `get_mime_type` for common and dangerous types (`.html`, `.svg`, `.php`).
- [ ] `multimediahelper.class.php` (21 pure methods): `isSupportedImage/Media/File/ModalFile`, `isEmbeddableMedialink`, `makeEmbeddableMedialink` for
      YouTube/Vimeo/Dailymotion/9slides/Voki URL variants (short links, `?t=`, playlists, http vs https), `getObjectWidth/Height`.
- [ ] `mediaresource.class.php` getters, `mediaresource.factory.php` (globals).
- [ ] `pwgen.inc.php`: `genPassPronouncable`, `create_pass` (length, charset); `choose_password_strength` with the config seam.
- [ ] `session.class.php`: `validate_language_code` (unknown code → default), and the flash/messages API with `$_SESSION` (§2.2).
- [ ] `hierarchy.class.php`: pure parts – `locateSubordinatesAndSubTrees`, `buildRootsWithSubTreesArray`, `buildRootIdsArray`, `shiftRight/Left`
      math; `unserializeLangField`-style localized name parsing (§`hierarchy.class.php:1026`). The nested-set operations go to §4.3.
- [ ] `references.class.php`: `get_module_from_objtype`, `get_general_modules`, `get_module_list`, `get_module_items`.
- [ ] `permissions.class.php`: `get_permissions_names`; the rest is DB (§4).
- [ ] `learnPathLib.inc.php`: `calculate_learnPath_bestAttempt_progress`, `calculate_learnPath_combined_progress`, `calculate_learnPath_progress`,
      `format_lp_progress_display`, `is_num`, `selectImage`, `build_element_list`, `build_display_element_list` – progress math with multiple
      attempts, `scoreMax <= 0`, empty input, rounding.
- [ ] `file_cache.class.php`: store/get/clear/expiry under a temp `CACHE_DIR` (and the CLI no-op path).
- [ ] `curlutil.class.php`, `cronutil.class.php`: argument building only (no network).
- [ ] `include/action.php` (action bar builders), `include/user_settings.php`, `include/course_settings.php` – object API with globals.
- [ ] `include/HTMLPurifier_Filter_MyIframe.php` + `purify()`: allowed iframe hosts pass, others are stripped, `<script>` removed.

### 3.3 Exercises (`modules/exercise`) – P0
- [ ] `Question::blanksSplitAnswer` (`a::b::3` keeps `::` inside the answer), `Question::getBlanks` (math tags `[m]…[/m]` ignored, unclosed `[`,
      nested brackets, empty blanks).
- [ ] `replaceBracketsWithBlanks` (`exercise.lib.php`): `[1]` → span with escaped id; non-numeric brackets untouched.
- [ ] `Exercise::canonicalize_exercise_score` (range > 0 scales, range 0 rounds), `canonicalize_exercise_pass_grade`, `calculate_feedback`
      (sorted thresholds, score exactly on a threshold, no feedback) – construct `Exercise` without DB and set the fields.
- [ ] Getters/setters of `Exercise` (62 pure methods) and `Question` (23): one data-driven test is enough; mostly guards against typos.
- [ ] `AikenParser` + `TestItem`: valid quiz, missing `ANSWER:`, too many distractors, answer letter not in the options, CRLF input, UTF-8
      Greek text, `toArray()` / `toHTML()` escaping. (`TestItem::__construct` uses randomness for ids – assert format.)
- [ ] `evaluateExpression` (`exercise.lib.php:869`) for calculated questions – it touches the DB; split the math part out or test via §4.6.
- [ ] `CertaintyBasedButtons`, `getCertaintyLegend*` (globals) – markup per certainty level.

### 3.4 Progress, badges & certificates (`modules/progress`) – P1
- [ ] `Criterion::buildRule` / `Game::buildRule`: rule strings for each combination of activity type / module / resource / threshold +
      `Operator` (`=`, `<`, `>`, `<=`, `>=`, `!=`).
- [ ] `CriterionSet::addCriterion` / `evaluateCriteria`, `Game::evaluate*` with the ruler and a fake context (`assertedAction` /
      `notAssertedAction` hit the DB → test double or §4.7).
- [ ] `BasicEvent::getContext`, `preDataListeners`.

### 3.5 Other modules with pure logic – P1/P2
- [ ] `modules/wall/ExtVideoUrlParser.class.php`: `validateUrl` and `get_embed_url` for YouTube/Vimeo URL shapes (`youtu.be`, `watch?v=`,
      `embed/`, `shorts/`, extra params), rejecting look-alike hosts (`youtube.com.evil.example`).
- [ ] `modules/tc/bbb-api.php` (20 pure): every `get*Url()` builds the query and the SHA checksum correctly (compare with the BBB API docs),
      `_requiredParam` / `_optionalParam`.
- [ ] `modules/wiki/lib`: `lib.diff.php` (`diff`, `str_split_on_new_line`, `format_line`), `lib.url.php` (`add_request_variable_to_url` with
      existing `?`/`&`/fragments), `class.wikiaccesscontrol.php` (17 pure ACL checks per role/flag), `class.wikipage.php` / `class.wiki.php` getters.
- [ ] `modules/course_info/restorehelper.class.php` (11 pure): backup version detection, file/field/value/type mappings per version.
- [ ] `modules/course_metadata/CourseXML.php` (17 pure): XML ↔ array round trip, required fields, language variants.
- [ ] `modules/oai/xml_creater.php` (10 pure): well-formed OAI-PMH XML, escaping.
- [ ] `modules/document/doc_metadata.php` (10 pure): LOM metadata XML building/parsing.
- [ ] `modules/blog/class.blogpost.php` (10 pure) getters/formatting.
- [ ] `modules/lti/classes/*` (`LtiServiceBase`, `LtiServiceResponse`, `LtiResourceBase`, memberships): response building, JSON shapes, status codes.
- [ ] `modules/lti/lib.php` pure helpers (12): signature base strings, parameter normalisation.
- [ ] `modules/main/services/OpenBadges*` (`ApiResponse` 20, `ApiService` 14, `EndpointRegistry` 12, `ApiClient` 9), `BackpackProviderService`,
      `modules/admin/entities/BackpackProvider.php`: request/response mapping with recorded JSON fixtures.
- [ ] `modules/eduapi/Service.php` (16 pure), `Sync.php` (9): mapping of EduAPI payloads to courses/users with fixtures.
- [ ] `modules/drives/*` (`clouddrive.php`, OneDrive/Google/credential plugins): URL and token-request building only.
- [ ] `modules/tc/Zoom/User/ZoomUser.php`: getters/serialisation.
- [ ] `modules/auth/methods/pop3.php` (14 pure): protocol response parsing.
- [ ] `modules/plagiarism/unplag/unplag.php`: request building.
- [ ] `modules/admin/extconfig/*` (`externals.php` 21 pure, `secondfaapp.php`, `googledriveapp.php` …): config field definitions, validation of
      each external app's settings.
- [ ] `modules/h5p/classes/H5PFramework.php` (40 pure methods of the H5P framework interface): the ones returning constants/paths.

### 3.6 AI & external repositories – P2 (no network: recorded responses)
- [ ] `include/lib/ai/providers/` (`OpenAIProvider` 14, `CustomProvider` 12, `GeminiProvider` 11, `AnthropicProvider` 11, `AbstractAIProvider`):
      request body building and response parsing from fixture JSON, error responses, empty choices.
- [ ] `include/lib/ai/AIProviderFactory.php`: provider selection by config.
- [ ] `include/lib/ai/services/` (`AIQuestionBankService`, `AICourseExtractionService::sanitizeCourseData`): prompt building, parsing of model
      output into questions/course data, invalid JSON from the model.
- [ ] `include/lib/externalrepos/` (`AbstractExternalRepo`, DSpace 15, Islandora 17, Wikipedia 12, Pixabay 11, YouTube 10, ReasonableGraph 8):
      search URL building and result normalisation from fixtures.

### 3.7 Log formatting – P2
- [ ] `Log::*_action_details()` (one per module, globals only): each renders the stored details array to text; unknown keys, missing keys.

---

## 4. Integration tests (MariaDB) – P1

Everything below needs a real database. Most of the business logic (grading, permissions, course access, hierarchy) lives here.

### 4.1 Harness – P0 for this section
- [ ] Test DB: `mariadb:10.11` service in CI; locally the e2e stack's `db` (`docker-compose.e2e.yaml`) or a dedicated container.
- [ ] Schema: run the installer's DB step (`install/install_db.php` via the same functions the wizard uses) into `eclass_test` once per run,
      then wrap each test in a transaction (`Database::get()->transaction(...)` exists) and roll back, or truncate the touched tables.
- [ ] `DatabaseTestCase` with fixture builders: `makeUser(status)`, `makeCourse(visibility)`, `enrol(user, course, flags)`, `makeDepartment()`.
- [ ] Re-enable the `Integration` suite in `phpunit.xml`, with `<env>` for DB host/name/user (already sketched there).

### 4.2 DB layer – P0
- [ ] `Database` placeholders (`modules/db/database.php` `queryImpl`): `?d` (intval), `?f` (floatval), `?s`, `?t`, `?b`, and a bare `?` that
      guesses the type; nested arrays are flattened. Cases: correct binding, SQL-injection strings stored literally, `NULL` for every type,
      more variables than placeholders (logged), fewer (the code calls `die()` – test in a separate process), and a literal `?` inside
      SQL text (the statement is split on every `?`, so it is taken as a placeholder). Return shapes of `queryArray` / `querySingle` /
      `queryFunc`, `lastInsertID` / `affectedRows` on the result object.
- [ ] `transaction()` commits / rolls back on exception.
- [ ] `DBHelper`: `tableExists`, `fieldExists`, `indexExists`, `primaryKeysOf`, `foreignKeyExists`, `createForeignKey`, `intToDate`, `timeAfter`.
- [ ] `get_config` / `set_config` and the FileCache interplay (`set_config` must clear the cache).

### 4.3 Hierarchy (nested sets) – P1
- [ ] `addNode`, `updateNode`, `deleteNode`, `moveNodes`, `shift*`: `lft`/`rgt` stay consistent after every operation (invariant check helper);
      `getParent`, `getRootParent`, `buildSubtrees`, tenant roots/children.
- [ ] Department-manager scope: `User::getAdminDepartmentIds` + `buildSubtrees` → the same course set `include/init.php` grants.

### 4.4 Users, courses, permissions – P1
- [ ] `user_exists`, `user_is_registered_to_course`, `check_guest`, `check_editor`, `check_course_reviewer`, `check_opencourses_reviewer`,
      `is_inactive_user`, `get_admin_rights`, `get_mail_ver_status`.
- [ ] `course_has_expired`, `course_has_started`, `course_reg_date_started/ended` around boundary dates; `course_status`, `course_type`,
      `is_enabled_course_registration`, `check_course_prerequisites`.
- [ ] `Permissions` (`has_course_modules_permission`, `has_course_users_permission`, `has_course_clone_permission`, `has_course_backup_permission`,
      `can_upload_document`, `can_upload_multimedia`, `update_course_permissions`).
- [ ] `visible_module`, `is_module_disable`, `user_groups`, `is_group_visible`, `get_course_users`.
- [ ] `delete_course`, `deleteUser`: rows in dependent tables are gone (FK cascade or manual), files dir handled (temp `courses/`).
- [ ] `set_user_option` / `get_user_option` / `delete_user_option`.
- [ ] `move_order`, `reorder_table` (units, announcements…), `add_unit_resource`, `units_get_maxorder`.

### 4.5 Auth – P1
- [ ] `modules/auth/auth.inc.php`: local password check with `password_hash` and legacy hashes (phpass `PasswordHash`), lockout counters
      (`login_fail_*`), `resetLoginFailure`, alternative-auth dispatch by `auth_ids` (methods mocked).
- [ ] `lostpass.php` logic extracted or tested via the token functions: admins excluded, 1-hour rate limit (`last_passreminder`).

### 4.6 Exercise grading – P0
- [ ] `Exercise::calculate_total_score`: negative per-question totals for `MULTIPLE_ANSWER` clamp to 0, negative overall total clamps to 0,
      mixed question types.
- [ ] `Answer::get_drag_and_drop_*_grade`, `get_correct_calculated_*`, `get_user_answer_grade`, `get_ordering_answer_grade`,
      `get_user_certainty_answer_choice`, `Question::certaintyBasedResults`.
- [ ] Grading per question type through `QuestionResult(…, $regrade=true)` with seeded answers: unique, multiple (with negative weights),
      true/false, fill-in-blanks strict vs tolerant (whitespace/case/alternatives `a|b`), predefined, matching, drag-and-drop text/markers,
      calculated (wildcards, tolerance), ordering, free text (manual), oral/upload (manual).
- [ ] `hasQuestionListWithRandomCriteria`, random question selection respects counts per category/difficulty.

### 4.7 Gradebook, attendance, assignments, progress – P1
- [ ] `modules/work`: `max_grade_from_scale`, `max_grade_from_rubric`, `submission_grade`, `was_graded`, `countUngradedSubmissions`,
      `is_rubric_used_in_*`, `is_scale_used_in_assignment`, `clone_rubric`, `get_grade_review_field`.
- [ ] `modules/gradebook`: activity weights → user total, `clone_gradebook`, delete paths.
- [ ] `modules/attendance`: presence counts vs limit.
- [ ] `modules/progress`: `CriterionAbstract::assertedAction`/`notAssertedAction`, `Criterion::evaluate` (max points per criterion / per period),
      `PointsGame::levelUpdate`, `getNextLevelInfo`, `get_level_number`, certificate issuing when all criteria pass.
- [ ] `get_learnPath_progress*` with seeded SCORM tracking rows.

### 4.8 Logging & tokens – P2
- [ ] `Log::record` writes the row (with the client IP helper), `Log::rotate` / `purge` by config.
- [ ] `rss_token_valid` / `token_generate` with the real `code_key`.

### 4.9 Install & upgrade – P2
- [ ] Fresh schema from `install/install_db.php` has every table/FK the code expects (compare against `DBHelper` checks).
- [ ] `upgrade/functions.php` steps are idempotent: running the upgrade on a just-installed DB changes nothing.
- [ ] Backup → restore round trip (`course_info/archive_functions.php` + `restore_functions.php`, 25 pure helpers among them) on a seeded course.
- [ ] Optional: `tests/dspace_smoke.php` as a `#[Group('network')]` test.

---

## 5. Findings from reading the code (each deserves a regression test)

| # | Where | What | Severity |
|---|---|---|---|
| 1 | `include/main_lib.php` `inet_to_bits()` / `ip_v6_cidr_match()` | `unpack('A16')` strips trailing NULs → IPv6 CIDR ranges whose prefix ends in zero bytes never match (verified: `2001:db8:0:1::5` ∉ `2001:db8::/48` per the code). Affects exercise/assignment IP locks and API token IP limits. | Medium (fails closed) |
| 2 | `include/main_lib.php:3574` `token_validate()` | `==` instead of `hash_equals()`: not constant time, and numeric-string comparison of hex digests. | Low |
| 3 | `tests/phpunit/bootstrap.php` | redefines `ECLASS_VERSION` → warning on every run. | Low (test infra) |
| 4 | `phpcs.xml` | `testVersion` has no effect without PHPCompatibility. | Low (tooling) |
| 5 | `format_bytesize()` | labels binary units as `Kb`/`Mb` (bits); the existing test locks that in. Decide whether that is intended. | Cosmetic |

---

## 6. Suggested order
1. §0.1 fixes (bootstrap, schema, paths, PHPCS on changed lines only) – so the three workflows are green and meaningful.
2. §1.1 excludes + §1.2 ruleset decisions.
3. §2.1–2.2 bootstrap and the `get_config` seam.
4. §3.1 security-relevant helpers (with the IPv6 regression test), §3.3 exercise helpers, §3.2 file/upload helpers.
5. §4.1 integration harness, then §4.2 DB layer and §4.6 grading.
6. The rest by priority.
