import { createHash } from 'node:crypto';
import { SEED_PASSWORD, USERS } from './auth';
import type { Role } from './auth';
import type { SeedSpec } from './harness';

/**
 * The data every run starts from, sent to the harness `seed` action by
 * `auth.setup.ts` and snapshotted as `seeded`. Idempotent: seeding again changes nothing.
 *
 * Not seeded yet: `teacher_limited` (course-admin sub-rights), group tutors (`group_members.is_tutor`),
 * LDAP/CAS accounts and the collaboration roles. They come with the specs that need them.
 */

export const DEPT = { A: 'E2EDEPA', A1: 'E2EDEPA1', B: 'E2EDEPB' } as const;

export const COURSE = {
  OPEN: 'E2EOPEN',
  REG: 'E2EREG',
  REGPW: 'E2EREGPW',
  REGWIN: 'E2EREGWIN',
  CLOSED: 'E2ECLOSED',
  INACTIVE: 'E2EINACTIVE',
  EXPIRED: 'E2EEXPIRED',
  FUTURE: 'E2EFUTURE',
  PREREQ: 'E2EPREREQ',
  DEPB: 'E2EDEPB',
  COLLAB: 'E2ECOLLAB',
} as const;

export type CourseCode = (typeof COURSE)[keyof typeof COURSE];

/** Registration password of `E2EREGPW`. */
export const COURSE_PASSWORD = 'e2e-course-pw';

/** The courses where the "normal" roles are enrolled: everything except the department-B and collaboration ones. */
const MAIN_COURSES: CourseCode[] = [COURSE.OPEN, COURSE.REG, COURSE.REGPW, COURSE.REGWIN, COURSE.CLOSED,
  COURSE.INACTIVE, COURSE.EXPIRED, COURSE.FUTURE, COURSE.PREREQ];

const user = (role: Role, extra: Partial<SeedSpec['users'][number]> = {}) => ({
  username: USERS[role].username,
  password: SEED_PASSWORD,
  givenname: 'E2E',
  surname: role,
  departments: [DEPT.A],
  ...extra,
});

const enrol = (role: Role, courses: CourseCode[], flags: Omit<SeedSpec['enrolments'][number], 'course' | 'user'> = {}) =>
  courses.map((course) => ({ course, user: USERS[role].username, ...flags }));

export const SEED: SeedSpec = {
  departments: [
    { code: DEPT.A, name: 'E2E Department A' },
    { code: DEPT.A1, name: 'E2E Department A1', parent: DEPT.A },
    { code: DEPT.B, name: 'E2E Department B' },
  ],

  users: [
    user('poweruser', { status: 'teacher' }),
    user('usermanager', { status: 'teacher' }),
    user('depadmin', { status: 'teacher' }),
    user('teacher', { status: 'teacher' }),
    user('teacher_other', { status: 'teacher' }),
    user('editor'),
    user('course_reviewer'),
    user('oc_reviewer', { status: 'teacher' }),
    user('tutor'),
    user('student'),
    user('student2'),
    user('student_unenrolled'),
    user('guest', { status: 'guest' }),
    user('expired', { expiresAt: '-1 day' }),
    // Only refused at login while `email_verification_required` is on; specs that need that turn it on.
    user('unverified', { verifiedMail: 'required' }),
    user('force_pw', { options: { force_password_change: '1' } }),
  ],

  courses: [
    { code: COURSE.OPEN, title: 'E2E Open course', visibility: 'open', departments: [DEPT.A] },
    { code: COURSE.REG, title: 'E2E Registration course', visibility: 'registration', departments: [DEPT.A] },
    { code: COURSE.REGPW, title: 'E2E Registration with password', visibility: 'registration', password: COURSE_PASSWORD, departments: [DEPT.A] },
    { code: COURSE.REGWIN, title: 'E2E Registration window', visibility: 'registration', regStartDate: '-1 day', regEndDate: '+7 days', departments: [DEPT.A] },
    { code: COURSE.CLOSED, title: 'E2E Closed course', visibility: 'closed', departments: [DEPT.A] },
    { code: COURSE.INACTIVE, title: 'E2E Inactive course', visibility: 'inactive', departments: [DEPT.A] },
    { code: COURSE.EXPIRED, title: 'E2E Expired course', visibility: 'open', startDate: '-2 months', endDate: '-1 month', departments: [DEPT.A] },
    { code: COURSE.FUTURE, title: 'E2E Future course', visibility: 'open', startDate: '+1 month', departments: [DEPT.A] },
    { code: COURSE.PREREQ, title: 'E2E Course with prerequisite', visibility: 'registration', departments: [DEPT.A] },
    { code: COURSE.DEPB, title: 'E2E Department B course', visibility: 'open', departments: [DEPT.B] },
    { code: COURSE.COLLAB, title: 'E2E Collaboration', visibility: 'open', collaborative: true, departments: [DEPT.A] },
  ],

  enrolments: [
    ...enrol('teacher', [...MAIN_COURSES, COURSE.DEPB, COURSE.COLLAB], { status: 'teacher', tutor: true }),
    ...enrol('editor', [COURSE.OPEN, COURSE.INACTIVE], { editor: true }),
    ...enrol('course_reviewer', [COURSE.OPEN, COURSE.INACTIVE], { courseReviewer: true }),
    ...enrol('oc_reviewer', [COURSE.OPEN], { status: 'teacher', reviewer: true }),
    ...enrol('tutor', [COURSE.OPEN], { tutor: true }),
    ...enrol('student', [...MAIN_COURSES.filter((c) => c !== COURSE.PREREQ), COURSE.DEPB]),
    ...enrol('student2', [COURSE.OPEN, COURSE.REG]),
    ...enrol('guest', [COURSE.OPEN], { status: 'guest' }),
  ],

  adminRights: [
    { user: USERS.poweruser.username, privilege: 'poweruser' },
    { user: USERS.usermanager.username, privilege: 'usermanager' },
    { user: USERS.depadmin.username, privilege: 'depadmin', department: DEPT.A },
  ],

  prerequisites: [{ course: COURSE.PREREQ, prerequisite: COURSE.OPEN }],
};

/**
 * Name of the DB + files snapshot taken right after seeding. It includes a hash of `SEED`, so changing the
 * seed data makes the next run seed again instead of restoring a stale snapshot.
 */
export const SEEDED_SNAPSHOT = `seeded-${createHash('sha1').update(JSON.stringify(SEED)).digest('hex').slice(0, 8)}`;
