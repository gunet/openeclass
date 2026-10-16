import path from 'node:path';

/**
 * Accounts and the stored-session paths written by `auth.setup.ts`.
 *
 * Lives here rather than in the setup file because Playwright refuses a test
 * file importing another test file. The seed data that creates these accounts is
 * in `seed.ts`.
 */

/** Accounts with a stored session: every role that lands on its portfolio after logging in. */
export const SESSION_ROLES = [
  'admin',
  'poweruser',
  'usermanager',
  'depadmin',
  'teacher',
  'teacher_other',
  'editor',
  'course_reviewer',
  'oc_reviewer',
  'tutor',
  'student',
  'student2',
  'student_unenrolled',
  'guest',
] as const;

/** Accounts that can't get a normal session (refused or redirected at login); specs log them in themselves. */
export const SPECIAL_ROLES = ['expired', 'unverified', 'force_pw'] as const;

export type SessionRole = (typeof SESSION_ROLES)[number];
export type Role = SessionRole | (typeof SPECIAL_ROLES)[number];

export type Credentials = { username: string; password: string };

/** Password of every seeded account (the install admin keeps its own). */
export const SEED_PASSWORD = process.env.ECLASS_E2E_PASSWORD || 'E2e-Pass-1!';

const seeded = (role: Role): Credentials => ({ username: `e2e_${role}`, password: SEED_PASSWORD });

export const USERS: Record<Role, Credentials> = {
  admin: {
    username: process.env.ECLASS_ADMIN_USERNAME || 'admin',
    password: process.env.ECLASS_ADMIN_PASSWORD || 'secret',
  },
  ...(Object.fromEntries(
    [...SESSION_ROLES, ...SPECIAL_ROLES].filter((role) => role !== 'admin').map((role) => [role, seeded(role)]),
  ) as Record<Exclude<Role, 'admin'>, Credentials>),
};

const AUTH_DIR = path.join(__dirname, '..', '.auth');

/** `tests/e2e-pw/.auth/<role>.json`, for `test.use({ storageState: STATE.teacher })`. */
export const STATE = Object.fromEntries(
  SESSION_ROLES.map((role) => [role, path.join(AUTH_DIR, `${role}.json`)]),
) as Record<SessionRole, string>;
