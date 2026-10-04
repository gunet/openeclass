import { request } from '@playwright/test';
import type { APIRequestContext } from '@playwright/test';
import { SEEDED_SNAPSHOT } from './seed';
import { restore } from './stack';

/**
 * Typed client for the PHP control harness (`fixtures/e2e-harness.php`). It only answers on the e2e
 * stack (`ECLASS_E2E=1`) and only with the header below.
 *
 * Dates (`expiresAt`, `startDate`, `time()` values, …) are absolute (`2026-01-31 12:00`) or relative
 * to now in strtotime syntax (`-1 day`, `+2 hours`), so specs don't depend on the day they run.
 */

export const HARNESS_PATH = '/tests/e2e-pw/fixtures/e2e-harness.php';
export const HARNESS_HEADERS = { 'X-Eclass-E2E': 'eclass-e2e' };

export type UserStatus = 'teacher' | 'student' | 'guest';
export type Visibility = 'open' | 'registration' | 'closed' | 'inactive';
export type Privilege = 'admin' | 'poweruser' | 'usermanager' | 'depadmin' | 'none';
export type DateInput = string | null;
export type ConfigValue = string | number | boolean;

export type DepartmentSpec = { code: string; name: string; parent?: string; allowCourse?: boolean; allowUser?: boolean };

export type UserSpec = {
  username: string;
  password: string;
  givenname?: string;
  surname?: string;
  email?: string;
  status?: UserStatus;
  lang?: string;
  expiresAt?: DateInput;
  verifiedMail?: 'required' | 'verified' | 'unverified';
  departments?: string[];
  options?: Record<string, string>;
};

export type CourseSpec = {
  code: string;
  title?: string;
  visibility?: Visibility;
  departments?: string[];
  lang?: string;
  password?: string;
  startDate?: DateInput;
  endDate?: DateInput;
  regStartDate?: DateInput;
  regEndDate?: DateInput;
  collaborative?: boolean;
  profNames?: string;
  description?: string;
};

export type EnrolmentSpec = {
  course: string;
  user: string;
  status?: UserStatus;
  tutor?: boolean;
  editor?: boolean;
  courseReviewer?: boolean;
  reviewer?: boolean;
};

export type AdminRightSpec = { user: string; privilege: Privilege; department?: string };

export type SeedSpec = {
  departments: DepartmentSpec[];
  users: UserSpec[];
  courses: CourseSpec[];
  enrolments: EnrolmentSpec[];
  adminRights: AdminRightSpec[];
  prerequisites: { course: string; prerequisite: string }[];
  config?: Record<string, ConfigValue>;
};

export type SeedResult = {
  departments: Record<string, number>;
  users: Record<string, number>;
  courses: Record<string, number>;
  enrolments: string[];
  adminRights: Record<string, number>;
};

export type TimeChange =
  | { entity: 'user'; key: string; set: Partial<Record<'expires_at' | 'registered_at' | 'last_passreminder', DateInput>> }
  | { entity: 'course'; key: string; set: Partial<Record<'start_date' | 'end_date' | 'reg_start_date' | 'reg_end_date' | 'created', DateInput>> }
  | { entity: 'assignment'; key: number; set: Partial<Record<'deadline' | 'submission_date' | 'results_date', DateInput>> }
  | { entity: 'exercise'; key: number; set: Partial<Record<'start_date' | 'end_date', DateInput>> };

export type LogRow = {
  id: number;
  user_id: number;
  course_id: number;
  module_id: number;
  details: unknown;
  action_type: number;
  ts: string;
  ip: string;
};

export type ActionsDailyRow = {
  id: number;
  user_id: number;
  module_id: number;
  course_id: number;
  hits: number;
  duration: number;
  day: string;
  last_update: string;
};

export type LogQuery = {
  /** Username. */
  user?: string;
  /** Course code. */
  course?: string;
  /** `MODULE_ID_<NAME>` suffix (`'forum'`) or number. */
  module?: string | number;
  /** `log` only: `LOG_<NAME>` suffix (`'insert'`, `'create_course'`) or number. */
  type?: string | number;
  since?: string;
  limit?: number;
};

export type FileInfo =
  | { path: string; exists: false }
  | { path: string; exists: true; isDir: true; entries: string[] }
  | { path: string; exists: true; isDir: false; size: number; sha1: string };

export class HarnessError extends Error {}

export class Harness {
  constructor(private readonly api: APIRequestContext) {}

  /** A harness with its own request context, for code outside a test (setup, global teardown). */
  static async create(baseURL: string): Promise<{ harness: Harness; dispose: () => Promise<void> }> {
    const api = await request.newContext({ baseURL, extraHTTPHeaders: HARNESS_HEADERS });
    return { harness: new Harness(api), dispose: () => api.dispose() };
  }

  /** Call an action; throws with the harness's error message unless the response is 2xx. */
  async call<T>(action: string, options: { body?: object; query?: Record<string, string | number | undefined> } = {}): Promise<T> {
    const params = new URLSearchParams({ action });
    for (const [key, value] of Object.entries(options.query ?? {})) {
      if (value !== undefined) params.set(key, String(value));
    }
    const url = `${HARNESS_PATH}?${params}`;
    const response = options.body === undefined
      ? await this.api.get(url, { headers: HARNESS_HEADERS })
      : await this.api.post(url, { headers: HARNESS_HEADERS, data: options.body });
    const text = await response.text();
    let json: unknown;
    try {
      json = JSON.parse(text);
    } catch {
      throw new HarnessError(`harness ${action}: HTTP ${response.status()}, not JSON: ${text.slice(0, 300)}`);
    }
    if (!response.ok()) {
      const error = (json as { error?: string }).error ?? text;
      throw new HarnessError(`harness ${action}: HTTP ${response.status()}: ${error}`);
    }
    return json as T;
  }

  ping() {
    return this.call<{ ok: true; version: string; time: string; timezone: string }>('ping');
  }

  seed(spec: SeedSpec) {
    return this.call<SeedResult>('seed', { body: spec });
  }

  /**
   * Back to a snapshot (default: the seeded one): restores the DB and course files through docker
   * (`utils/stack.ts`), undoes config overrides, empties mailpit, drops caches and points mail at mailpit
   * again. `config` overrides are applied afterwards.
   */
  async reset(options: { snapshot?: string; config?: Record<string, ConfigValue> } = {}) {
    restore(options.snapshot ?? SEEDED_SNAPSHOT);
    const result = await this.call<{ restoredConfig: string[]; mailCleared: boolean }>('reset', { body: {} });
    await this.useMailpit();
    if (options.config) {
      await this.setConfig(options.config);
    }
    return result;
  }

  async getConfig<K extends string>(...keys: K[]): Promise<Record<K, string | null>> {
    return (await this.call<{ values: Record<K, string | null> }>('config', { query: { keys: keys.join(',') } })).values;
  }

  /**
   * Override config values; `restoreConfig()`, `reset()` and the global teardown put the originals back.
   * The first two keep mail going to mailpit; only the global teardown (`deactivate()`) undoes that too.
   */
  setConfig(values: Record<string, ConfigValue>) {
    return this.call<{ values: Record<string, ConfigValue> }>('config', { body: { values } });
  }

  /** Put every overridden key back to its original value, then point mail at mailpit again. */
  async restoreConfig() {
    const result = await this.call<{ restored: string[] }>('config', { body: { restore: true } });
    await this.useMailpit();
    return result;
  }

  /** Send all site mail to the stack's mailpit (an override, undone by the global teardown). */
  useMailpit() {
    return this.setConfig({
      email_transport: 'smtp',
      smtp_server: 'mailpit',
      smtp_port: 1025,
      smtp_encryption: '',
      smtp_username: '',
      smtp_password: '',
    });
  }

  departments(departments: DepartmentSpec[]) {
    return this.call<{ departments: Record<string, number> }>('departments', { body: { departments } });
  }

  users(users: UserSpec[]) {
    return this.call<{ users: Record<string, number> }>('users', { body: { users } });
  }

  courses(courses: CourseSpec[]) {
    return this.call<{ courses: Record<string, number> }>('courses', { body: { courses } });
  }

  enrol(enrolments: EnrolmentSpec[]) {
    return this.call<{ enrolments: string[] }>('enrol', { body: { enrolments } });
  }

  adminRights(rights: AdminRightSpec[]) {
    return this.call<{ rights: Record<string, number> }>('admin-rights', { body: { rights } });
  }

  /** `module`: `MODULE_ID_<NAME>` suffix (`'forum'`, `'docs'`, `'assign'`) or number. */
  courseModule(course: string, module: string | number, visible: boolean) {
    return this.call<{ course: string; module: number; visible: boolean }>('course-module', { body: { course, module, visible } });
  }

  time(change: TimeChange) {
    return this.call<{ entity: string; key: string | number; set: Record<string, string | null>; changed: boolean }>('time', { body: change });
  }

  cron() {
    return this.call<{ ok: true }>('cron', { body: {} });
  }

  async log(query: LogQuery = {}): Promise<LogRow[]> {
    return (await this.call<{ rows: LogRow[] }>('log', { query: { table: 'log', ...query } })).rows;
  }

  async actionsDaily(query: Omit<LogQuery, 'type'> = {}): Promise<ActionsDailyRow[]> {
    return (await this.call<{ rows: ActionsDailyRow[] }>('log', { query: { table: 'actions_daily', ...query } })).rows;
  }

  /** `path` relative to `courses/` (`E2EOPEN/document/…`), or `video/…`. */
  file(path: string) {
    return this.call<FileInfo>('file', { query: { path } });
  }

  /** A REST API token; send it as `Authorization: Bearer <token>`. Defaults to every course. */
  apiToken(options: { name?: string; courses?: string[]; department?: string; ip?: string; expires?: string } = {}) {
    return this.call<{ id: number; token: string; courses: string[] }>('api-token', { body: options });
  }

  /** Undo every config override (mail included). Called by the global teardown. */
  deactivate() {
    return this.call<{ restoredConfig: string[] }>('deactivate', { body: {} });
  }
}
