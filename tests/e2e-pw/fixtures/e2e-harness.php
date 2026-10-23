<?php
/*
 * Open eClass E2E control harness (test-only).
 *
 * Lets the Playwright suite set up and inspect the site directly instead of clicking
 * through the UI: seed data, change config, move dates, read the log, check files.
 * Served at /tests/e2e-pw/fixtures/e2e-harness.php by the e2e stack's repo bind mount.
 *
 * It refuses to run unless the container has ECLASS_E2E=1 (docker-compose.e2e.yaml) AND
 * the request carries the header `X-Eclass-E2E: eclass-e2e`. It must never ship in an image.
 *
 * Bootstraps only main_lib, the config and Database, not include/init.php: no session,
 * no login, no redirects.
 *
 * Request: `?action=<name>`, body JSON for POST. Response: JSON, `{error}` with a 4xx/5xx
 * status on failure. The typed client is utils/harness.ts.
 *
 * DB/file snapshots are not done here (the PHP container has no DB client); utils/stack.ts
 * does them through docker, and `reset` only does the PHP side.
 */

if (getenv('ECLASS_E2E') !== '1') {
    http_response_code(404);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['HTTP_X_ECLASS_E2E'] ?? '') !== 'eclass-e2e') {
    http_response_code(403);
    echo json_encode(['error' => 'missing X-Eclass-E2E header']);
    exit;
}

// Same environment as include/init.php. $webDir must match, since FileCache derives its file names from it.
$webDir = dirname(__DIR__, 3);
chdir($webDir);
date_default_timezone_set('Europe/Athens');
mb_internal_encoding('UTF-8');
require 'vendor/autoload.php';
require_once 'include/main_lib.php';
if (!file_exists('config/config.php')) {
    respond(['error' => 'site is not installed'], 409);
}
include_once 'config/config.php';
require_once 'modules/db/database.php';
require_once 'modules/admin/extconfig/externals.php'; // Course::refresh() reindexes, which reads external app settings
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/create_course/functions.php';

// Library code reads the acting user from the global $uid (e.g. the search indexer's async queue).
// Harness changes are made as the first platform admin, as if done from the admin pages.
$uid = (int) (Database::get()->querySingle('SELECT MIN(user_id) AS id FROM admin WHERE privilege = ?d', ADMIN_USER)->id ?? 0);

// Database reports query errors through Debug and carries on; turn them into exceptions.
Debug::setOutput(function ($message, $level) {
    if ($level >= Debug::ERROR) {
        throw new RuntimeException(html_entity_decode(strip_tags($message)));
    }
});

const CONFIG_BACKUP_KEY = 'e2e_config_backup';
const MAILPIT_API = 'http://mailpit:8025/api/v1';

$actions = [
    'ping'          => ['GET',  'action_ping'],
    'seed'          => ['POST', 'action_seed'],
    'reset'         => ['POST', 'action_reset'],
    'config'        => ['GET|POST', 'action_config'],
    'departments'   => ['POST', 'action_departments'],
    'users'         => ['POST', 'action_users'],
    'courses'       => ['POST', 'action_courses'],
    'enrol'         => ['POST', 'action_enrol'],
    'admin-rights'  => ['POST', 'action_admin_rights'],
    'course-module' => ['POST', 'action_course_module'],
    'time'          => ['POST', 'action_time'],
    'cron'          => ['POST', 'action_cron'],
    'log'           => ['GET',  'action_log'],
    'file'          => ['GET',  'action_file'],
    'api-token'     => ['POST', 'action_api_token'],
    'deactivate'    => ['POST', 'action_deactivate'],
];

$name = $_GET['action'] ?? '';
if (!isset($actions[$name])) {
    respond(['error' => "unknown action '$name'", 'actions' => array_keys($actions)], 404);
}
[$methods, $handler] = $actions[$name];
if (!in_array($_SERVER['REQUEST_METHOD'], explode('|', $methods))) {
    respond(['error' => "$name expects $methods"], 405);
}
$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = $raw === '' ? [] : json_decode($raw, true);
    if (!is_array($body)) {
        respond(['error' => 'body must be a JSON object'], 400);
    }
}

try {
    respond($handler($body));
} catch (HarnessError $e) {
    respond(['error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    respond(['error' => get_class($e) . ': ' . $e->getMessage(), 'at' => $e->getFile() . ':' . $e->getLine()], 500);
}

// ---------------------------------------------------------------------------------------------
// Plumbing
// ---------------------------------------------------------------------------------------------

/** A bad request: reported as 400 with the message. */
class HarnessError extends RuntimeException {}

function respond($data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function db(): Database {
    return Database::get();
}

function now(): string {
    return date('Y-m-d H:i:s');
}

/** `$data[$key]`, or a 400 when it is missing. */
function need(array $data, string $key) {
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        throw new HarnessError("missing '$key'");
    }
    return $data[$key];
}

/** Map a name (`'open'`) or a number to a constant value, or fail with the list of names. */
function pick($value, array $names, string $what): int {
    if (is_int($value) || ctype_digit((string) $value)) {
        return (int) $value;
    }
    if (!isset($names[$value])) {
        throw new HarnessError("unknown $what '$value' (expected one of: " . implode(', ', array_keys($names)) . ')');
    }
    return $names[$value];
}

/**
 * A date for a DATE/DATETIME column: null, an absolute date, or a relative one parsed by strtotime
 * ('-1 day', '+2 hours', 'now'), so specs never depend on the day they run.
 */
function to_date($value, string $format = 'Y-m-d H:i:s'): ?string {
    if ($value === null) {
        return null;
    }
    $ts = strtotime((string) $value);
    if ($ts === false) {
        throw new HarnessError("cannot parse date '$value'");
    }
    return date($format, $ts);
}

/** Drop every FileCache file of this site (config, etc.), like set_config() does for the config one. */
function clear_caches(): void {
    global $webDir;
    $dir = defined('CACHE_DIR') ? CACHE_DIR : sys_get_temp_dir();
    $files = glob($dir . '/' . substr(md5($webDir), 0, 8) . '_*.cache') ?: [];
    foreach ($files as $file) {
        @unlink($file);
    }
}

function http(string $method, string $url, int $timeout = 10, array $headers = []): array {
    $context = stream_context_create(['http' => ['method' => $method, 'timeout' => $timeout, 'ignore_errors' => true,
        'follow_location' => 0, 'header' => $headers]]);
    $body = @file_get_contents($url, false, $context);
    $status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int) $m[1] : 0;
    return [$status, $body === false ? '' : $body];
}

function user_id(string $username): int {
    $row = db()->querySingle('SELECT id FROM user WHERE username = ?s', $username);
    if (!$row) {
        throw new HarnessError("no user '$username'");
    }
    return $row->id;
}

function course_row(string $code) {
    $row = db()->querySingle('SELECT id, code, is_collaborative FROM course WHERE code = ?s', $code);
    if (!$row) {
        throw new HarnessError("no course '$code'");
    }
    return $row;
}

function department_id(string $code): int {
    $row = db()->querySingle('SELECT id FROM hierarchy WHERE code = ?s ORDER BY id LIMIT 1', $code);
    if (!$row) {
        throw new HarnessError("no department '$code'");
    }
    return $row->id;
}

/** The root of the tree (lft = 1): the parent when a department names none. */
function root_department() {
    return db()->querySingle('SELECT id, lft FROM hierarchy WHERE lft = 1');
}

// ---------------------------------------------------------------------------------------------
// Actions
// ---------------------------------------------------------------------------------------------

function action_ping(array $body): array {
    return ['ok' => true, 'version' => get_config('version'), 'time' => now(), 'timezone' => date_default_timezone_get()];
}

/**
 * Create everything a run needs in one call, in dependency order. Every part is idempotent
 * (matched by code / username), so seeding twice gives the same ids.
 *
 * Body: { departments?, users?, courses?, enrolments?, adminRights?, prerequisites?, config? }
 * with the same shapes as the single actions below.
 */
function action_seed(array $body): array {
    $result = [];
    if (!empty($body['config'])) {
        $result['config'] = override_config($body['config']);
    }
    $result['departments'] = action_departments(['departments' => $body['departments'] ?? []])['departments'];
    $result['users'] = action_users(['users' => $body['users'] ?? []])['users'];
    $result['courses'] = action_courses(['courses' => $body['courses'] ?? []])['courses'];
    $result['enrolments'] = action_enrol(['enrolments' => $body['enrolments'] ?? []])['enrolments'];
    $result['adminRights'] = action_admin_rights(['rights' => $body['adminRights'] ?? []])['rights'];
    // Admin membership is authoritative to the seed: a seeded user not granted a privilege above must have
    // no admin row, even if a prior (polluted) snapshot left one. Only touches users this seed creates.
    $granted = array_column($body['adminRights'] ?? [], 'user');
    $strip = array_values(array_diff(array_column($body['users'] ?? [], 'username'), $granted));
    if ($strip) {
        $placeholders = implode(', ', array_fill(0, count($strip), '?s'));
        db()->query("DELETE FROM admin WHERE user_id IN (SELECT id FROM user WHERE username IN ($placeholders))", ...$strip);
    }
    foreach ($body['prerequisites'] ?? [] as $p) {
        $course = course_row(need($p, 'course'))->id;
        $prerequisite = course_row(need($p, 'prerequisite'))->id;
        db()->query('DELETE FROM course_prerequisite WHERE course_id = ?d AND prerequisite_course = ?d', $course, $prerequisite);
        db()->query('INSERT INTO course_prerequisite (course_id, prerequisite_course) VALUES (?d, ?d)', $course, $prerequisite);
    }
    return $result;
}

/**
 * Put the PHP side back to a clean state: undo config overrides, empty mailpit, drop caches.
 * The DB/file snapshot restore happens before this, from utils/stack.ts.
 * Body: { config?: {key: value} } – overrides to apply after the reset.
 */
function action_reset(array $body): array {
    $restored = restore_config();
    clear_caches();
    $result = ['restoredConfig' => $restored, 'mailCleared' => clear_mailpit()];
    if (!empty($body['config'])) {
        $result['config'] = override_config($body['config']);
    }
    return $result;
}

/**
 * GET  ?keys=a,b             → {values: {a: …, b: …}} (null when unset)
 * POST {values: {key: val}}  → override; the first override of a key remembers its original value
 * POST {restore: true}       → put every overridden key back to its original value
 */
function action_config(array $body): array {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $keys = array_filter(explode(',', $_GET['keys'] ?? ''));
        if (!$keys) {
            throw new HarnessError("missing 'keys'");
        }
        clear_caches(); // read the DB, not a cache that may predate a snapshot restore
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = get_config($key);
        }
        return ['values' => $values];
    }
    if (!empty($body['restore'])) {
        return ['restored' => restore_config()];
    }
    return ['values' => override_config(need($body, 'values'))];
}

function config_backup(): array {
    $row = db()->querySingle('SELECT `value` FROM config WHERE `key` = ?s', CONFIG_BACKUP_KEY);
    return $row ? (json_decode($row->value, true) ?: []) : [];
}

function override_config(array $values): array {
    $backup = config_backup();
    foreach ($values as $key => $value) {
        if ($key === CONFIG_BACKUP_KEY) {
            throw new HarnessError("'$key' is reserved");
        }
        if (!array_key_exists($key, $backup)) {
            $row = db()->querySingle('SELECT `value` FROM config WHERE `key` = ?s', $key);
            $backup[$key] = $row ? $row->value : null;
        }
        set_config($key, is_bool($value) ? (int) $value : (string) $value);
    }
    set_config(CONFIG_BACKUP_KEY, json_encode($backup));
    return $values;
}

function restore_config(): array {
    $backup = config_backup();
    foreach ($backup as $key => $value) {
        if ($value === null) {
            db()->query('DELETE FROM config WHERE `key` = ?s', $key);
        } else {
            set_config($key, $value);
        }
    }
    db()->query('DELETE FROM config WHERE `key` = ?s', CONFIG_BACKUP_KEY);
    clear_caches();
    return array_keys($backup);
}

function clear_mailpit(): bool {
    [$status] = http('DELETE', MAILPIT_API . '/messages', 5);
    return $status === 200;
}

/**
 * Body: { departments: [{code, name, parent?: code, allowCourse?, allowUser?}] }
 * Created under `parent` (default: the root), in order, so parents come first.
 */
function action_departments(array $body): array {
    $tree = new Hierarchy();
    $result = [];
    foreach ($body['departments'] ?? [] as $d) {
        $code = need($d, 'code');
        $existing = db()->querySingle('SELECT id FROM hierarchy WHERE code = ?s', $code);
        if ($existing) {
            $result[$code] = $existing->id;
            continue;
        }
        $parentLft = isset($d['parent']) ? $tree->getNodeLft(department_id($d['parent'])) : root_department()->lft;
        $name = serialize(['el' => need($d, 'name'), 'en' => $d['name']]);
        $tree->addNode($name, '', $parentLft, $code, (int) ($d['allowCourse'] ?? 1), (int) ($d['allowUser'] ?? 1), null, 2, '');
        $result[$code] = department_id($code);
    }
    return ['departments' => $result];
}

/**
 * Body: { users: [{username, password, givenname?, surname?, email?, status?: 'student'|'teacher'|'guest'|int,
 *          lang?, expiresAt?, verifiedMail?: 'required'|'verified'|'unverified'|int, departments?: [code],
 *          options?: {name: value}}] }
 * Existing users (same username) are updated, password included.
 */
function action_users(array $body): array {
    $statuses = ['teacher' => USER_TEACHER, 'student' => USER_STUDENT, 'guest' => USER_GUEST];
    $verified = ['required' => EMAIL_VERIFICATION_REQUIRED, 'verified' => EMAIL_VERIFIED, 'unverified' => EMAIL_UNVERIFIED];
    $user = new User();
    $result = [];
    foreach ($body['users'] ?? [] as $u) {
        $username = need($u, 'username');
        $surname = $u['surname'] ?? $username;
        $givenname = $u['givenname'] ?? 'E2E';
        $email = $u['email'] ?? "$username@example.com";
        $status = pick($u['status'] ?? 'student', $statuses, 'status');
        $lang = $u['lang'] ?? 'en';
        $expires = to_date($u['expiresAt'] ?? '+1 year');
        $verifiedMail = pick($u['verifiedMail'] ?? 'verified', $verified, 'verifiedMail');
        $password = password_hash(need($u, 'password'), PASSWORD_DEFAULT);
        $options = empty($u['options']) ? null : json_encode($u['options']);

        $existing = db()->querySingle('SELECT id FROM user WHERE username = ?s', $username);
        if ($existing) {
            $id = $existing->id;
            db()->query('UPDATE user SET surname = ?s, givenname = ?s, password = ?s, email = ?s, status = ?d, lang = ?s,
                    expires_at = ?t, verified_mail = ?d, options = ?s WHERE id = ?d',
                $surname, $givenname, $password, $email, $status, $lang, $expires, $verifiedMail, $options, $id);
        } else {
            // Same columns as modules/admin/newuseradmin.php.
            $id = (int) db()->query('INSERT INTO user (surname, givenname, username, password, email, status, phone, am,
                    registered_at, expires_at, lang, description, verified_mail, whitelist, disable_course_registration, options)
                    VALUES (?s, ?s, ?s, ?s, ?s, ?d, \'\', \'\', ?t, ?t, ?s, \'\', ?d, \'\', 0, ?s)',
                $surname, $givenname, $username, $password, $email, $status, now(), $expires, $lang, $verifiedMail, $options)->lastInsertID;
            db()->query('INSERT IGNORE INTO personal_calendar_settings (user_id) VALUES (?d)', $id);
        }
        $departments = array_map('department_id', $u['departments'] ?? []);
        if ($departments) {
            $user->refresh($id, $departments);
        }
        $result[$username] = $id;
    }
    return ['users' => $result];
}

/**
 * Body: { courses: [{code, title?, visibility?: 'open'|'registration'|'closed'|'inactive'|int, departments?: [code],
 *          lang?, password?, startDate?, endDate?, regStartDate?, regEndDate?, collaborative?, profNames?, description?}] }
 * New courses get their folders, index.php, default modules and forum category, like create_course.php.
 * Existing courses (same code) get their settings updated.
 */
function action_courses(array $body): array {
    $visibilities = ['closed' => COURSE_CLOSED, 'registration' => COURSE_REGISTRATION, 'open' => COURSE_OPEN, 'inactive' => COURSE_INACTIVE];
    $course = new Course();
    $result = [];
    foreach ($body['courses'] ?? [] as $c) {
        $code = need($c, 'code');
        if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $code)) {
            throw new HarnessError("bad course code '$code'");
        }
        $values = [
            $c['lang'] ?? 'en',
            $c['title'] ?? $code,
            pick($c['visibility'] ?? 'open', $visibilities, 'visibility'),
            $c['profNames'] ?? 'E2E Teacher',
            $c['password'] ?? '',
            to_date($c['startDate'] ?? '-1 month', 'Y-m-d'),
            to_date($c['endDate'] ?? null, 'Y-m-d'),
            to_date($c['regStartDate'] ?? null, 'Y-m-d'),
            to_date($c['regEndDate'] ?? null, 'Y-m-d'),
            empty($c['collaborative']) ? 0 : 1,
            $c['description'] ?? '',
        ];
        $set = 'lang = ?s, title = ?s, visible = ?d, prof_names = ?s, password = ?s, start_date = ?t, end_date = ?t,
                reg_start_date = ?t, reg_end_date = ?t, is_collaborative = ?d, description = ?s';

        $existing = db()->querySingle('SELECT id FROM course WHERE code = ?s', $code);
        if ($existing) {
            $id = $existing->id;
            db()->query("UPDATE course SET $set WHERE id = ?d", ...[...$values, $id]);
        } else {
            if (!create_course_dirs($code)) {
                throw new RuntimeException("cannot create the folders of course '$code'");
            }
            $id = (int) db()->query("INSERT INTO course SET code = ?s, public_code = ?s, $set, keywords = '', view_type = 'units',
                    created = ?t, glossary_expand = 0, glossary_index = 1, view_units = 1",
                ...[$code, $code, ...$values, now()])->lastInsertID;
            create_course_modules($id);
            course_index($code);
            // The lang files aren't loaded here, so the default category gets a fixed English name.
            db()->query('INSERT INTO forum_category SET cat_title = ?s, course_id = ?d', 'Forums', $id);
        }
        $departments = array_map('department_id', $c['departments'] ?? []);
        $course->refresh($id, $departments ?: [root_department()->id]);
        $result[$code] = $id;
    }
    return ['courses' => $result];
}

/**
 * create_modules() without its `global $modules` (built in include/init.php): the default modules visible,
 * every other course tool hidden. Keep the lists in sync with $modules in include/init.php.
 */
function create_course_modules(int $courseId): void {
    $collaborative = db()->querySingle('SELECT is_collaborative FROM course WHERE id = ?d', $courseId)->is_collaborative;
    $all = $collaborative
        ? [MODULE_ID_AGENDA, MODULE_ID_LINKS, MODULE_ID_DOCS, MODULE_ID_VIDEO, MODULE_ID_ANNOUNCE, MODULE_ID_FORUM,
           MODULE_ID_GROUPS, MODULE_ID_MESSAGE, MODULE_ID_CHAT, MODULE_ID_QUESTIONNAIRE, MODULE_ID_WALL, MODULE_ID_TC,
           MODULE_ID_REQUEST, MODULE_ID_STICKY_NOTES, MODULE_ID_ASSIGN, MODULE_ID_GRADEBOOK, MODULE_ID_ATTENDANCE, MODULE_ID_SESSION]
        : [MODULE_ID_AGENDA, MODULE_ID_LINKS, MODULE_ID_DOCS, MODULE_ID_VIDEO, MODULE_ID_ASSIGN, MODULE_ID_ANNOUNCE,
           MODULE_ID_FORUM, MODULE_ID_EXERCISE, MODULE_ID_GROUPS, MODULE_ID_MESSAGE, MODULE_ID_GLOSSARY, MODULE_ID_EBOOK,
           MODULE_ID_CHAT, MODULE_ID_QUESTIONNAIRE, MODULE_ID_LP, MODULE_ID_WIKI, MODULE_ID_BLOG, MODULE_ID_WALL,
           MODULE_ID_GRADEBOOK, MODULE_ID_ATTENDANCE, MODULE_ID_TC, MODULE_ID_PROGRESS, MODULE_ID_REQUEST,
           MODULE_ID_STICKY_NOTES, MODULE_ID_H5P];
    $visible = $collaborative ? default_modules_collaboration() : default_modules();
    foreach ($all as $module) {
        db()->query('INSERT IGNORE INTO course_module (module_id, visible, course_id) VALUES (?d, ?d, ?d)',
            $module, in_array($module, $visible) ? 1 : 0, $courseId);
    }
}

/**
 * Body: { enrolments: [{course, user, status?: 'teacher'|'student'|'guest'|int, tutor?, editor?,
 *          courseReviewer?, reviewer?}] }
 * Re-enrolling replaces the flags.
 */
function action_enrol(array $body): array {
    $statuses = ['teacher' => USER_TEACHER, 'student' => USER_STUDENT, 'guest' => USER_GUEST];
    $result = [];
    foreach ($body['enrolments'] ?? [] as $e) {
        $courseId = course_row(need($e, 'course'))->id;
        $userId = user_id(need($e, 'user'));
        db()->query('REPLACE INTO course_user (course_id, user_id, status, tutor, editor, course_reviewer, reviewer,
                reg_date, receive_mail, document_timestamp) VALUES (?d, ?d, ?d, ?d, ?d, ?d, ?d, ?t, 1, ?t)',
            $courseId, $userId, pick($e['status'] ?? 'student', $statuses, 'status'),
            (int) !empty($e['tutor']), (int) !empty($e['editor']), (int) !empty($e['courseReviewer']),
            (int) !empty($e['reviewer']), now(), now());
        $result[] = "{$e['user']}@{$e['course']}";
    }
    return ['enrolments' => $result];
}

/**
 * Body: { rights: [{user, privilege: 'admin'|'poweruser'|'usermanager'|'depadmin'|int, department?: code}] }
 * Replaces the user's previous admin rows. `privilege: 'none'` removes them.
 */
function action_admin_rights(array $body): array {
    $privileges = ['admin' => ADMIN_USER, 'poweruser' => POWER_USER, 'usermanager' => USERMANAGE_USER,
        'depadmin' => DEPARTMENTMANAGE_USER, 'none' => -1];
    $result = [];
    foreach ($body['rights'] ?? [] as $r) {
        $userId = user_id(need($r, 'user'));
        $privilege = pick(need($r, 'privilege'), $privileges, 'privilege');
        db()->query('DELETE FROM admin WHERE user_id = ?d', $userId);
        if ($privilege >= 0) {
            $department = isset($r['department']) ? department_id($r['department']) : null;
            db()->query('INSERT INTO admin (user_id, privilege, department_id) VALUES (?d, ?d, ?d)', $userId, $privilege, $department);
        }
        $result[$r['user']] = $privilege;
    }
    return ['rights' => $result];
}

/** Body: { course, module: 'forum'|'docs'|…|int (MODULE_ID_<NAME>), visible: bool } */
function action_course_module(array $body): array {
    $courseId = course_row(need($body, 'course'))->id;
    $module = need($body, 'module');
    if (!is_int($module) && !ctype_digit((string) $module)) {
        $constant = 'MODULE_ID_' . strtoupper($module);
        if (!defined($constant)) {
            throw new HarnessError("unknown module '$module' (no $constant)");
        }
        $module = constant($constant);
    }
    $visible = empty($body['visible']) ? 0 : 1;
    db()->query('INSERT INTO course_module (module_id, visible, course_id) VALUES (?d, ?d, ?d)
            ON DUPLICATE KEY UPDATE visible = VALUES(visible)', (int) $module, $visible, $courseId);
    return ['course' => $body['course'], 'module' => (int) $module, 'visible' => (bool) $visible];
}

/**
 * Move dates instead of sleeping or faking the clock. Values go through to_date(): absolute or relative ('-1 day').
 * Body: { entity: 'user'|'course'|'assignment'|'exercise', key: username|code|id, set: {field: date|null} }
 */
function action_time(array $body): array {
    $entities = [
        'user'       => ['user', 'username', ['expires_at' => 'Y-m-d H:i:s', 'registered_at' => 'Y-m-d H:i:s', 'last_passreminder' => 'Y-m-d H:i:s']],
        'course'     => ['course', 'code', ['start_date' => 'Y-m-d', 'end_date' => 'Y-m-d', 'reg_start_date' => 'Y-m-d', 'reg_end_date' => 'Y-m-d', 'created' => 'Y-m-d H:i:s']],
        'assignment' => ['assignment', 'id', ['deadline' => 'Y-m-d H:i:s', 'submission_date' => 'Y-m-d H:i:s', 'results_date' => 'Y-m-d H:i:s']],
        'exercise'   => ['exercise', 'id', ['start_date' => 'Y-m-d H:i:s', 'end_date' => 'Y-m-d H:i:s']],
    ];
    $entity = need($body, 'entity');
    if (!isset($entities[$entity])) {
        throw new HarnessError("unknown entity '$entity' (expected one of: " . implode(', ', array_keys($entities)) . ')');
    }
    [$table, $keyColumn, $fields] = $entities[$entity];
    $key = need($body, 'key');
    $set = need($body, 'set');
    $assignments = $args = [];
    $values = [];
    foreach ($set as $field => $value) {
        if (!isset($fields[$field])) {
            throw new HarnessError("cannot set $entity.$field (allowed: " . implode(', ', array_keys($fields)) . ')');
        }
        $values[$field] = to_date($value, $fields[$field]);
        $assignments[] = "`$field` = ?t";
        $args[] = $values[$field];
    }
    $args[] = $key;
    $placeholder = $keyColumn === 'id' ? '?d' : '?s';
    $affected = db()->query("UPDATE `$table` SET " . implode(', ', $assignments) . " WHERE `$keyColumn` = $placeholder", ...$args)->affectedRows;
    $exists = db()->querySingle("SELECT 1 AS found FROM `$table` WHERE `$keyColumn` = $placeholder", $key);
    if (!$exists) {
        throw new HarnessError("no $entity '$key'");
    }
    return ['entity' => $entity, 'key' => $key, 'set' => $values, 'changed' => $affected > 0];
}

/**
 * Run modules/admin/cron.php (the monthly login/action summaries and index optimisation) the way a
 * scheduler would: an HTTP request to the site, inside the container.
 */
function action_cron(array $body): array {
    // init.php redirects requests for another host to base_url, so send the site's own Host header.
    $base = parse_url(get_config('base_url'));
    $host = $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');
    [$status] = http('GET', 'http://127.0.0.1/modules/admin/cron.php', 120, ["Host: $host"]);
    if ($status !== 200) {
        throw new RuntimeException("cron.php answered $status");
    }
    return ['ok' => true];
}

/**
 * GET ?table=log|actions_daily&user=&course=&module=&type=&since=&limit=
 * `user` is a username, `course` a code, `module` a MODULE_ID_ name or number, `type` a LOG_ name or number
 * (log only, the `action_type` column), `since` a date for to_date(). Newest first.
 */
function action_log(array $body): array {
    $table = $_GET['table'] ?? 'log';
    $where = $args = [];
    if (!empty($_GET['user'])) {
        $where[] = 'user_id = ?d';
        $args[] = user_id($_GET['user']);
    }
    if (!empty($_GET['course'])) {
        $where[] = 'course_id = ?d';
        $args[] = course_row($_GET['course'])->id;
    }
    if (!empty($_GET['module'])) {
        $module = $_GET['module'];
        $where[] = 'module_id = ?d';
        $args[] = ctype_digit($module) ? (int) $module : constant_or_fail('MODULE_ID_' . strtoupper($module));
    }
    if ($table === 'log') {
        if (!empty($_GET['type'])) {
            $type = $_GET['type'];
            $where[] = 'action_type = ?d';
            $args[] = ctype_digit($type) ? (int) $type : constant_or_fail('LOG_' . strtoupper($type));
        }
        if (!empty($_GET['since'])) {
            $where[] = 'ts >= ?t';
            $args[] = to_date($_GET['since']);
        }
        $columns = 'id, user_id, course_id, module_id, details, action_type, ts, ip';
        $order = 'id DESC';
    } elseif ($table === 'actions_daily') {
        if (!empty($_GET['since'])) {
            $where[] = 'last_update >= ?t';
            $args[] = to_date($_GET['since']);
        }
        $columns = 'id, user_id, module_id, course_id, hits, duration, day, last_update';
        $order = 'last_update DESC';
    } else {
        throw new HarnessError("unknown table '$table' (expected log or actions_daily)");
    }
    $limit = min(500, max(1, (int) ($_GET['limit'] ?? 50)));
    $sql = "SELECT $columns FROM `$table`" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $order LIMIT $limit";
    $rows = db()->queryArray($sql, ...$args);
    if ($table === 'log') {
        foreach ($rows as $row) {
            $details = @unserialize($row->details);
            $row->details = $details === false ? $row->details : $details;
        }
    }
    return ['rows' => $rows];
}

function constant_or_fail(string $name): int {
    if (!defined($name)) {
        throw new HarnessError("unknown constant $name");
    }
    return constant($name);
}

/** GET ?path=<code>/document/… – relative to courses/ (or `video/…`). Never leaves those folders. */
function action_file(array $body): array {
    global $webDir;
    $path = (string) ($_GET['path'] ?? '');
    if ($path === '' || str_contains($path, "\0")) {
        throw new HarnessError("missing 'path'");
    }
    $base = str_starts_with($path, 'video/') ? "$webDir/video" : "$webDir/courses";
    $relative = str_starts_with($path, 'video/') ? substr($path, 6) : $path;
    $full = realpath("$base/$relative");
    if ($full === false) {
        return ['path' => $path, 'exists' => false];
    }
    if ($full !== $base && !str_starts_with($full, "$base/")) {
        throw new HarnessError("path '$path' leaves " . basename($base) . '/');
    }
    $result = ['path' => $path, 'exists' => true, 'isDir' => is_dir($full)];
    if (is_dir($full)) {
        $result['entries'] = array_values(array_diff(scandir($full), ['.', '..']));
    } else {
        $result['size'] = filesize($full);
        $result['sha1'] = sha1_file($full);
    }
    return $result;
}

/**
 * Body: { name?, courses?: [code] (default: every course), department?: code, ip?, expires? }
 * Returns the token, for an `Authorization: Bearer …` header on /api/v1.
 */
function action_api_token(array $body): array {
    $token = 'eclass_' . bin2hex(random_bytes(32));
    $department = isset($body['department']) ? department_id($body['department']) : root_department()->id;
    $id = (int) db()->query('INSERT INTO api_token SET token = ?s, name = ?s, comments = \'\', department_id = ?d, ip = ?s,
            enabled = 1, created = ?t, expired = ?t',
        $token, $body['name'] ?? 'e2e', $department, $body['ip'] ?? '', now(), to_date($body['expires'] ?? '+1 day'))->lastInsertID;
    $codes = $body['courses'] ?? array_map(fn($row) => $row->code, db()->queryArray('SELECT code FROM course'));
    foreach ($codes as $code) {
        db()->query('INSERT INTO api_token_course (course_id, token_id) VALUES (?d, ?d)', course_row($code)->id, $id);
    }
    return ['id' => $id, 'token' => $token, 'courses' => array_values($codes)];
}

/**
 * Called by the global teardown: undo every config override (mail settings included), so the site
 * behaves normally between runs.
 */
function action_deactivate(array $body): array {
    return ['restoredConfig' => restore_config()];
}
