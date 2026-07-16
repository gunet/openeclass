<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

namespace modules\eduapi;

use Database;
use Exception;
use Hierarchy;
use Msg;

/**
 * Edu-API sync engine.
 *
 * Executes the import algorithm (hierarchy nodes, courses, users,
 * enrollments, sessions).
 */
class Sync {
    private $service;
    private $topNode;
    private $tree;
    private $language;
    private $senderUserId;
    private $creatorName;

    /**
     * @param Service $service configured Edu-API service
     * @param object $topNode school hierarchy node (id, code, lft, rgt)
     * @param Hierarchy $tree
     * @param string $language platform language for titles / new users
     * @param int $senderUserId admin user sending the internal notifications
     * @param string $creatorName stored in course.prof_names for new courses
     */
    public function __construct(Service $service, object $topNode, Hierarchy $tree, string $language, int $senderUserId, string $creatorName) {
        $this->service = $service;
        $this->topNode = $topNode;
        $this->tree = $tree;
        $this->language = $language;
        $this->senderUserId = $senderUserId;
        $this->creatorName = $creatorName;
    }

    /**
     * Run the import for one academic session.
     *
     * @param string $sessionId academic session sourcedId
     * @param array $offeringIds selected course offering sourcedIds
     * @param bool $syncStudents true = full sync (users + enrollments), false = courses only
     * @param array $codePrefixes admin-approved course-code prefix per organization
     *        sourcedId ('' key = offerings without organization), missing or
     *        empty entries fall back to the auto-calculated prefix
     * @param array|null $approvedOrgs organization sourcedIds approved as
     *        hierarchy nodes (confirmation step). An array (possibly empty)
     *        mirrors the approved part of each organization ancestor chain,
     *        skipped levels splice their children up
     * @return array summary counters / warnings / errors
     * @throws Exception on fatal errors (config, no offerings, missing top node)
     */
    public function run(string $sessionId, array $offeringIds, bool $syncStudents, array $codePrefixes = [], array $approvedOrgs = null): array {
        $summary = [
            'sessionCode' => '',
            'nodesCreated' => 0,
            'coursesCreated' => 0,
            'coursesReused' => 0,
            'usersCreated' => 0,
            'usersAdopted' => 0,
            'usersRenamed' => 0,
            'usersPromoted' => 0,
            'enrollmentsCreated' => 0,
            'skippedPersons' => [],
            'warnings' => [],
            'errors' => [],
            'teachersNotified' => 0,
        ];

        if (!$this->topNode) {
            throw new Exception($GLOBALS['langEduApiTopNodeNotFound']);
        }

        $now = date('Y-m-d H:i:s');

        // Fetch fresh offerings for the session, keep the ones the admin selected.
        // Non-active offerings are honored (they were explicitly picked from a
        // live list) but counted and reported as a warning.
        $offerings = [];
        $inactiveSelected = 0;
        foreach ($this->service->getCourseOfferings($sessionId) as $offering) {
            if (empty($offering->sourcedId) || !in_array($offering->sourcedId, $offeringIds)) {
                continue;
            }
            if (strcasecmp($offering->recordStatus ?? 'active', 'active') !== 0) {
                $inactiveSelected++;
            }
            $offerings[] = $offering;
        }

        if (empty($offerings)) {
            throw new Exception($GLOBALS['langEduApiNoOfferingsSelected']);
        }

        if ($inactiveSelected > 0) {
            $summary['warnings'][] = sprintf($GLOBALS['langEduApiInactiveSynced'], $inactiveSelected);
        }

        $sessionCode = trim((string)($offerings[0]->academicSessionCode ?? ''));
        if ($sessionCode === '') {
            $sessionCode = $sessionId;
        }
        $summary['sessionCode'] = $sessionCode;

        // ---- Hierarchy: school -> organization node -> academic-session node -> courses ----
        // Nodes carry no hierarchy.code, they are tracked in the eduapi_nodes
        // mapping table so re-syncs find them even after admin renames.
        $offeringsByOrg = [];
        foreach ($offerings as $offering) {
            $organizationId = trim((string)($offering->organization ?? ''));
            $offeringsByOrg[$organizationId][] = $offering;
        }

        $usedFallback = false;
        $semesterTag = self::semesterTag($sessionCode, $usedFallback);
        if ($usedFallback) {
            $summary['warnings'][] = sprintf($GLOBALS['langEduApiCodeFallback'], $sessionCode, $semesterTag);
        }

        $courseIdByOffering = [];
        foreach ($offeringsByOrg as $organizationId => $orgOfferings) {
            try {
                if ($organizationId !== '') {
                    $orgName = $this->resolveOrganizationName($organizationId, $orgOfferings);
                    $codePrefix = self::orgInitials($orgName) . '-' . $semesterTag;
                    $sessionParent = $this->createOrgChainNodes($organizationId, $orgName, $approvedOrgs, $summary);
                } else {
                    // Offerings without an organization attach right under the school
                    $sessionParent = $this->topNode;
                    $codePrefix = 'EA-' . $semesterTag;
                }
                // An admin-approved prefix (confirmation step) overrides the auto one
                if (isset($codePrefixes[$organizationId]) && $codePrefixes[$organizationId] !== '') {
                    $codePrefix = $codePrefixes[$organizationId];
                }
                // The session node carries the course-code prefix as its hierarchy code
                $sessionNode = $this->getOrCreateMappedNode('session:' . $organizationId . ':' . $sessionId, $sessionCode, $sessionParent, $summary, $codePrefix);
            } catch (Exception $e) {
                $summary['errors'][] = "Organization {$organizationId}: " . $e->getMessage();
                continue;
            }

            // ---- Courses: create or reuse. The eduapi_course_offerings mapping
            // is the authoritative offering -> course link, public_code carries
            // the friendly {INITIALS}-{YY}{A|B}-{NNN} code for new courses.
            foreach ($orgOfferings as $offering) {
                try {
                    $sourcedId = $offering->sourcedId;
                    $orgCode = trim((string)($offering->organizationCode ?? ''));
                    $title = Service::pickTitle($offering->title ?? [], $this->language);

                    $existing = null;
                    $mapped = Database::get()->querySingle("SELECT course_id FROM eduapi_course_offerings WHERE sourced_id = ?s", $sourcedId);
                    if ($mapped) {
                        $existing = Database::get()->querySingle("SELECT id FROM course WHERE id = ?d", $mapped->course_id);
                    }
                    if (!$existing) {
                        // courses synced before carry the offering UUID in public_code
                        $existing = Database::get()->querySingle("SELECT id FROM course WHERE public_code = ?s", $sourcedId);
                    }

                    if ($existing) {
                        $courseId = $existing->id;
                        $summary['coursesReused']++;
                    } else {
                        $newCode = self::newCourseCode($codePrefix);
                        $courseTitle = ($title !== '' ? $title : $sourcedId);
                        $result = create_course(
                            $newCode,
                            $this->language,
                            $courseTitle,
                            '',
                            [$sessionNode->id],
                            0, // Locked
                            $this->creatorName,
                            '',
                            $newCode
                        );
                        if (!$result) {
                            throw new Exception('create_course failed');
                        }
                        list($code, $courseId) = $result;
                        course_index($code);
                        create_modules($courseId);
                        $summary['coursesCreated']++;
                    }

                    $courseIdByOffering[$sourcedId] = $courseId;

                    Database::get()->query(
                        "INSERT INTO eduapi_course_offerings
                            (sourced_id, course_id, academic_session_id, academic_session_code, organization_code, title, last_sync)
                         VALUES (?s, ?d, ?s, ?s, ?s, ?s, ?t)
                         ON DUPLICATE KEY UPDATE
                            course_id = VALUES(course_id),
                            academic_session_id = VALUES(academic_session_id),
                            academic_session_code = VALUES(academic_session_code),
                            organization_code = VALUES(organization_code),
                            title = VALUES(title),
                            last_sync = VALUES(last_sync)",
                        $sourcedId,
                        $courseId,
                        $offering->academicSession ?? $sessionId,
                        $sessionCode,
                        $orgCode,
                        $title,
                        $now
                    );
                } catch (Exception $e) {
                    $summary['errors'][] = "Course {$offering->sourcedId}: " . $e->getMessage();
                }
            }
        }

        if (!$syncStudents) {
            return $summary;
        }

        // ---- Collect persons per offering (enrollments carry only person id + role) ----
        $distinctPersons = []; // person sourcedId => ['person' => obj|null, 'isTeacher' => bool]
        $offeringRoster = []; // offering sourcedId => ['students' => [pid...], 'teachers' => [pid...]]

        foreach ($offerings as $offering) {
            $offId = $offering->sourcedId;
            if (!isset($courseIdByOffering[$offId])) {
                continue; // course creation failed - already reported
            }
            try {
                $students = [];
                $teachers = [];
                foreach ($this->service->getEnrollments($offId) as $enrollment) {
                    if (strcasecmp($enrollment->recordStatus ?? 'active', 'active') !== 0) {
                        continue;
                    }
                    $enrollmentStatus = strtolower($enrollment->enrollmentStatus ?? 'accepted');
                    if (!in_array($enrollmentStatus, ['accepted', 'active'])) {
                        continue;
                    }
                    $pid = $enrollment->person ?? null;
                    if (!$pid) {
                        continue;
                    }
                    $isTeacher = Service::isTeacherRole($enrollment->role ?? 'student');
                    if ($isTeacher) {
                        $teachers[$pid] = true;
                    } else {
                        $students[$pid] = true;
                    }
                    if (!isset($distinctPersons[$pid])) {
                        $distinctPersons[$pid] = ['person' => null, 'isTeacher' => $isTeacher];
                    } else {
                        $distinctPersons[$pid]['isTeacher'] = $distinctPersons[$pid]['isTeacher'] || $isTeacher;
                    }
                }

                // Person data comes from the offering's /students and /staff endpoints
                foreach ($this->service->getStudents($offId) as $person) {
                    $this->attachPersonData($distinctPersons, $person, false);
                }
                foreach ($this->service->getStaff($offId) as $person) {
                    $this->attachPersonData($distinctPersons, $person, true);
                }

                $offeringRoster[$offId] = [
                    'students' => array_keys($students),
                    'teachers' => array_keys($teachers),
                ];
            } catch (Exception $e) {
                $summary['errors'][] = "Offering {$offId}: " . $e->getMessage();
            }
        }

        // ---- User upsert ----
        $userIdMap = $this->upsertUsers($distinctPersons, $summary);

        // ---- Enrollments (add-only) + missing-student detection ----
        $registeredAt = $now;
        $missingStudentsByCourse = [];

        foreach ($offeringRoster as $offId => $roster) {
            $courseId = $courseIdByOffering[$offId];

            $teacherUserIds = [];
            foreach ($roster['teachers'] as $pid) {
                if (isset($userIdMap[$pid])) {
                    $teacherUserIds[$userIdMap[$pid]] = true;
                }
            }
            $studentUserIds = [];
            foreach ($roster['students'] as $pid) {
                if (isset($userIdMap[$pid]) && !isset($teacherUserIds[$userIdMap[$pid]])) {
                    $studentUserIds[$userIdMap[$pid]] = true;
                }
            }

            // Students enrolled in eClass but missing from the current API roster
            $enrolledStudents = Database::get()->queryArray(
                "SELECT user_id FROM course_user WHERE course_id = ?d AND status = ?d",
                $courseId, USER_STUDENT);
            foreach ($enrolledStudents as $enrolled) {
                if (!isset($studentUserIds[$enrolled->user_id]) && !isset($teacherUserIds[$enrolled->user_id])) {
                    $missingStudentsByCourse[$courseId][] = $enrolled->user_id;
                }
            }

            foreach (array_keys($teacherUserIds) as $userId) {
                $this->enrollUser($courseId, $userId, USER_TEACHER, $registeredAt, $summary);
            }
            foreach (array_keys($studentUserIds) as $userId) {
                $this->enrollUser($courseId, $userId, USER_STUDENT, $registeredAt, $summary);
            }
        }

        $summary['teachersNotified'] = $this->notifyTeachers($missingStudentsByCourse);

        return $summary;
    }

    /**
     * Fill person data / teacher flag for a person already seen in enrollments.
     * Persons returned by /students or /staff without an active enrollment are ignored.
     */
    private function attachPersonData(array &$distinctPersons, $person, $isStaff): void {
        $pid = $person->sourcedId ?? null;
        if (!$pid || !isset($distinctPersons[$pid])) {
            return;
        }
        if ($distinctPersons[$pid]['person'] === null) {
            $distinctPersons[$pid]['person'] = $person;
        }
        if ($isStaff) {
            $distinctPersons[$pid]['isTeacher'] = true;
        }
    }

    /**
     * Create/adopt eClass users for the collected persons.
     * One INSERT per user, each id is collected via lastInsertID.
     *
     * @return array person sourcedId => user id
     */
    private function upsertUsers(array $distinctPersons, array &$summary): array {
        $userIdMap = [];

        if (empty($distinctPersons)) {
            return $userIdMap;
        }

        $sourcedIds = array_keys($distinctPersons);

        // Existing person -> user mappings
        $placeholders = implode(',', array_fill(0, count($sourcedIds), '?s'));
        $mappings = Database::get()->queryArray(
            "SELECT sourced_id, user_id FROM eduapi_persons WHERE sourced_id IN ($placeholders)",
            ...$sourcedIds);
        $mappedUserBySourcedId = [];
        foreach ($mappings as $mapping) {
            $mappedUserBySourcedId[$mapping->sourced_id] = $mapping->user_id;
        }

        // Resolve usernames for unmapped persons
        $pendingBySourcedId = []; // sourcedId => ['username' =>, 'person' =>, 'isTeacher' =>]
        foreach ($distinctPersons as $pid => $entry) {
            if (isset($mappedUserBySourcedId[$pid])) {
                $userIdMap[$pid] = $mappedUserBySourcedId[$pid];
                continue;
            }

            $person = $entry['person'];
            if ($person === null) {
                $summary['skippedPersons'][] = $pid . ': ' . $GLOBALS['langEduApiSkipNoData'];
                continue;
            }
            if (strcasecmp($person->recordStatus ?? 'active', 'active') !== 0) {
                $summary['skippedPersons'][] = self::personLabel($person) . ': ' . $GLOBALS['langEduApiSkipInactive'];
                continue;
            }
            $username = Service::personUserName($person);
            if (empty($username)) {
                $summary['skippedPersons'][] = self::personLabel($person) . ': ' . $GLOBALS['langEduApiSkipNoUsername'];
                continue;
            }

            $pendingBySourcedId[$pid] = [
                'username' => $username,
                'person' => $person,
                'isTeacher' => $entry['isTeacher'],
            ];
        }

        // Batch-load users owning the wanted usernames and their eduapi mappings
        $existingByUsername = [];
        if (!empty($pendingBySourcedId)) {
            $usernames = array_values(array_unique(array_column($pendingBySourcedId, 'username')));
            $placeholders = implode(',', array_fill(0, count($usernames), '?s'));
            $existingUsers = Database::get()->queryArray(
                "SELECT user.id, user.username, user.surname, user.givenname, user.email, user.status,
                        eduapi_persons.sourced_id AS eduapi_sourced_id
                 FROM user
                 LEFT JOIN eduapi_persons ON eduapi_persons.user_id = user.id
                 WHERE user.username IN ($placeholders)",
                ...$usernames);
            foreach ($existingUsers as $user) {
                $existingByUsername[$user->username] = $user;
            }
        }

        $registeredAt = date('Y-m-d H:i:s');
        $accountDuration = (int)get_config('account_duration');
        if ($accountDuration <= 0) {
            $accountDuration = 4 * 365 * 24 * 3600;
        }
        $expiresAt = date('Y-m-d H:i:s', time() + $accountDuration);

        foreach ($pendingBySourcedId as $pid => $pending) {
            $person = $pending['person'];
            $username = $pending['username'];
            $isTeacher = $pending['isTeacher'];
            list($surname, $givenname) = self::personNames($person);
            $email = Service::personEmail($person);

            try {
                if (isset($existingByUsername[$username])) {
                    $conflictUser = $existingByUsername[$username];

                    if (empty($conflictUser->eduapi_sourced_id)) {
                        // Same username, not managed by eduapi yet: adopt the account
                        Database::get()->query(
                            "UPDATE user SET
                                surname = CASE WHEN surname = '' THEN ?s ELSE surname END,
                                givenname = CASE WHEN givenname = '' THEN ?s ELSE givenname END,
                                email = CASE WHEN email = '' THEN ?s ELSE email END
                             WHERE id = ?d",
                            $surname, $givenname, $email, $conflictUser->id);
                        $this->insertPersonMapping($pid, $conflictUser->id, $username, $email);
                        $userIdMap[$pid] = $conflictUser->id;
                        $summary['usersAdopted']++;
                        continue;
                    }

                    // Username owned by a user mapped to a DIFFERENT person: rename it out of the way
                    $oldUsername = $this->renameToOld($username);
                    Database::get()->query(
                        "UPDATE user SET username = ?s WHERE id = ?d",
                        $oldUsername, $conflictUser->id);
                    $summary['usersRenamed']++;
                    $summary['warnings'][] = sprintf($GLOBALS['langEduApiRenameWarning'], $username, $oldUsername);
                    unset($existingByUsername[$username]);
                }

                // Fresh user: one insert per person, real id via lastInsertID
                $newUserId = Database::get()->query(
                    "INSERT INTO user (surname, givenname, username, password, email, status, am, registered_at, expires_at, lang, verified_mail)
                     VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?t, ?t, ?s, ?d)",
                    $surname,
                    $givenname,
                    $username,
                    'keycloak',
                    $email,
                    $isTeacher ? SAEK_TEACHER : USER_STUDENT,
                    '',
                    $registeredAt,
                    $expiresAt,
                    $this->language,
                    ($email !== '') ? EMAIL_VERIFIED : EMAIL_UNVERIFIED
                )->lastInsertID;

                $this->insertPersonMapping($pid, $newUserId, $username, $email);
                Database::get()->query(
                    "INSERT IGNORE INTO personal_calendar_settings (user_id) VALUES (?d)", $newUserId);
                Database::get()->query(
                    "INSERT IGNORE INTO user_department (user, department) VALUES (?d, ?d)",
                    $newUserId, $this->topNode->id);
                user_hook($newUserId);

                $userIdMap[$pid] = $newUserId;
                $summary['usersCreated']++;
            } catch (Exception $e) {
                $summary['errors'][] = "User {$username}: " . $e->getMessage();
            }
        }

        // Promote existing student accounts that now appear as staff
        $teacherUserIds = [];
        foreach ($distinctPersons as $pid => $entry) {
            if ($entry['isTeacher'] && isset($userIdMap[$pid])) {
                $teacherUserIds[] = $userIdMap[$pid];
            }
        }
        if (!empty($teacherUserIds)) {
            $placeholders = implode(',', array_fill(0, count($teacherUserIds), '?d'));
            $params = array_merge([USER_STUDENT], $teacherUserIds);
            $toPromote = Database::get()->queryArray(
                "SELECT id FROM user WHERE status = ?d AND id IN ($placeholders)",
                ...$params);
            foreach ($toPromote as $user) {
                Database::get()->query(
                    "UPDATE user SET status = ?d WHERE id = ?d", SAEK_TEACHER, $user->id);
                $summary['usersPromoted']++;
            }
        }

        return $userIdMap;
    }

    private function insertPersonMapping($sourcedId, $userId, $username, $email): void {
        Database::get()->query(
            "INSERT INTO eduapi_persons (sourced_id, user_id, username, email, last_sync)
             VALUES (?s, ?d, ?s, ?s, ?t)
             ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email), last_sync = VALUES(last_sync)",
            $sourcedId, $userId, $username, $email, date('Y-m-d H:i:s'));
    }

    /**
     * Find a free "{username}_old" variant for a conflicting account
     */
    private function renameToOld($username): string {
        $candidate = $username . '_old';
        $counter = 1;
        while (Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $candidate)) {
            $counter++;
            $candidate = $username . '_old' . $counter;
        }
        return $candidate;
    }

    /**
     * Add-only enrollment: never deletes course_user rows; promotes
     * student rows to teacher rows when the role changed.
     */
    private function enrollUser($courseId, $userId, $status, $registeredAt, array &$summary): void {
        try {
            $enrolled = Database::get()->querySingle(
                "SELECT status FROM course_user WHERE course_id = ?d AND user_id = ?d",
                $courseId, $userId);

            if (!$enrolled) {
                Database::get()->query(
                    "INSERT INTO course_user (course_id, user_id, status, tutor, editor, course_reviewer, reviewer, reg_date, receive_mail, document_timestamp)
                     VALUES (?d, ?d, ?d, ?d, ?d, ?d, ?d, ?t, ?d, ?t)",
                    $courseId,
                    $userId,
                    $status,
                    ($status == USER_TEACHER) ? 1 : 0,
                    ($status == USER_TEACHER) ? 1 : 0,
                    0,
                    0,
                    $registeredAt,
                    1,
                    $registeredAt
                );
                $summary['enrollmentsCreated']++;
            } elseif ($status == USER_TEACHER && $enrolled->status == USER_STUDENT) {
                Database::get()->query(
                    "UPDATE course_user SET status = ?d, tutor = 1, editor = 1 WHERE course_id = ?d AND user_id = ?d",
                    USER_TEACHER, $courseId, $userId);
            }
        } catch (Exception $e) {
            $summary['errors'][] = "Enrollment (course $courseId, user $userId): " . $e->getMessage();
        }
    }

    /**
     * Internal message to each course's teachers listing students that are
     * enrolled in eClass but absent from the Edu-API roster. Teachers decide,
     * the sync never unenrolls anyone.
     *
     * @return int number of notified teachers
     */
    private function notifyTeachers(array $missingStudentsByCourse): int {
        if (empty($missingStudentsByCourse)) {
            return 0;
        }

        require_once 'modules/message/class.msg.php';
        $teachersNotified = 0;

        foreach ($missingStudentsByCourse as $courseId => $studentIds) {
            $studentIds = array_unique($studentIds);

            $teachers = Database::get()->queryArray(
                "SELECT user_id FROM course_user WHERE course_id = ?d AND status = ?d",
                $courseId, USER_TEACHER);
            if (empty($teachers)) {
                continue;
            }
            $teacherIds = array_column($teachers, 'user_id');

            $course = Database::get()->querySingle("SELECT title, code FROM course WHERE id = ?d", $courseId);
            if (!$course) {
                continue;
            }

            $subject = $GLOBALS['langEduApiMissingSubject'] . ' ' . $course->title;
            $body = '<p>' . $GLOBALS['langEduApiMissingGreeting'] . '</p>';
            $body .= '<p>' . sprintf($GLOBALS['langEduApiMissingIntro'], q($course->title)) . '</p>';
            $body .= '<ul>';
            foreach ($studentIds as $studentId) {
                $student = Database::get()->querySingle(
                    "SELECT username, CONCAT(surname, ' ', givenname) AS fullname FROM user WHERE id = ?d",
                    $studentId);
                if ($student) {
                    $studentName = !empty(trim($student->fullname)) ? q($student->fullname) : q($student->username);
                    $body .= '<li>' . $studentName . ' (' . q($student->username) . ')</li>';
                }
            }
            $body .= '</ul>';
            $body .= '<p>' . $GLOBALS['langEduApiMissingOutro'] . '</p>';

            try {
                new Msg(
                    $this->senderUserId,
                    $courseId,
                    $subject,
                    $body,
                    $teacherIds,
                    '',
                    '',
                    0
                );
                $teachersNotified += count($teacherIds);
            } catch (Exception $e) {
                error_log("Edu-API: failed to send teacher notification for course $courseId: " . $e->getMessage());
            }
        }

        return $teachersNotified;
    }

    /**
     * Create (or reuse) the hierarchy nodes for an offering's organization
     * and return the node the session node should attach to.
     *
     * $approvedOrgs = null: only the direct organization node under the school
     * node. Otherwise the organization ancestor chain is mirrored in parent ->
     * child order, keeping only approved sourcedIds. Skipped levels splice
     * their children up. When nothing is approved, the session node attaches
     * directly to the school node.
     * @throws Exception
     */
    private function createOrgChainNodes($organizationId, $orgName, $approvedOrgs, array &$summary) {
        if ($approvedOrgs === null) {
            return $this->getOrCreateMappedNode('org:' . $organizationId, $orgName, $this->topNode, $summary);
        }

        $chain = $this->service->getOrganizationChain($organizationId);
        if (empty($chain)) {
            // Chain walk failed, honor the direct org if approved
            if (in_array($organizationId, $approvedOrgs)) {
                return $this->getOrCreateMappedNode('org:' . $organizationId, $orgName, $this->topNode, $summary);
            }
            return $this->topNode;
        }

        $parent = $this->topNode;
        foreach ($chain as $chainOrg) {
            $chainId = trim((string)($chainOrg->sourcedId ?? ''));
            if ($chainId === '' || !in_array($chainId, $approvedOrgs)) {
                continue;
            }
            $chainName = ($chainId === $organizationId)
                ? $orgName
                : Service::organizationName($chainOrg, $this->language);
            if ($chainName === '') {
                $chainName = $chainId;
            }
            $parent = $this->getOrCreateMappedNode('org:' . $chainId, $chainName, $parent, $summary);
        }

        return $parent;
    }

    /**
     * Organization display name: live /organizations lookup or the
     * offerings' organizationCode or the raw organization sourcedId
     */
    private function resolveOrganizationName($organizationId, array $orgOfferings) {
        try {
            $organization = $this->service->getOrganization($organizationId);
            $name = Service::organizationName($organization, $this->language);
            if ($name !== '') {
                return $name;
            }
        } catch (Exception $e) {
            error_log('Edu-API: organization lookup failed for ' . $organizationId . ': ' . $e->getMessage());
        }

        foreach ($orgOfferings as $offering) {
            if (!empty($offering->organizationCode)) {
                return $offering->organizationCode;
            }
        }

        return $organizationId;
    }

    /**
     * Find a hierarchy node through the eduapi_nodes mapping table or create
     * it without a hierarchy code under the given parent node. The display
     * name is refreshed when the API name changed. An unmapped node with the
     * same name under the same parent (i.e. left over from a run that failed
     * before registering the mapping) is adopted instead of duplicated.
     * @throws Exception
     */
    private function getOrCreateMappedNode($refKey, $displayName, $parentNode, array &$summary, $nodeCode = '') {
        $now = date('Y-m-d H:i:s');
        $names = serialize([$this->language => $displayName]);

        // Re-fetch the parent: lft/rgt shift as sibling subtrees are created
        $parent = Database::get()->querySingle("SELECT id, lft, rgt FROM hierarchy WHERE id = ?d", $parentNode->id);
        if (!$parent) {
            throw new Exception("Parent hierarchy node not found for '$displayName'");
        }

        // 1. Known node via the mapping table
        $node = Database::get()->querySingle(
            "SELECT h.id, h.lft, h.name, h.code FROM eduapi_nodes e
             JOIN hierarchy h ON h.id = e.hierarchy_id
             WHERE e.ref_key = ?s", $refKey);

        if ($node) {
            if ($node->name !== $names) {
                Database::get()->query("UPDATE hierarchy SET name = ?s WHERE id = ?d", $names, $node->id);
            }
            $this->refreshNodeCode($node, $nodeCode);
            Database::get()->query("UPDATE eduapi_nodes SET last_sync = ?t WHERE ref_key = ?s", $now, $refKey);
            return $node;
        }

        // 2. Same-named unmapped node inside the parent subtree - adopt it
        $node = Database::get()->querySingle(
            "SELECT id, lft, code FROM hierarchy
             WHERE name = ?s AND lft > ?d AND lft < ?d
             ORDER BY id DESC LIMIT 1",
            $names, $parent->lft, $parent->rgt);
        if ($node) {
            $this->refreshNodeCode($node, $nodeCode);
        }

        // 3. Create it. Locate the new node by its fixed insert position:
        //    a new child always lands at parent lft + 1.
        if (!$node) {
            $this->tree->addNode(
                $names,
                serialize([$this->language => '']),
                $parent->lft,
                $nodeCode,
                1,
                0,
                'null',
                2,
                ''
            );

            $node = Database::get()->querySingle(
                "SELECT id, lft FROM hierarchy WHERE lft = ?d AND name = ?s",
                $parent->lft + 1, $names);
            if (!$node) {
                throw new Exception("Failed to create hierarchy node '$displayName'");
            }
            $summary['nodesCreated']++;
        }

        Database::get()->query(
            "INSERT INTO eduapi_nodes (ref_key, hierarchy_id, last_sync)
             VALUES (?s, ?d, ?t)
             ON DUPLICATE KEY UPDATE hierarchy_id = VALUES(hierarchy_id), last_sync = VALUES(last_sync)",
            $refKey, $node->id, $now);

        return $node;
    }

    private function refreshNodeCode($node, $nodeCode): void {
        if ($nodeCode !== '' && (string)($node->code ?? '') !== $nodeCode) {
            Database::get()->query("UPDATE hierarchy SET code = ?s WHERE id = ?d", $nodeCode, $node->id);
        }
    }

    /**
     * Organization initials for course codes. Names are also transliterated first.
     */
    public static function orgInitials($orgName): string {
        $latin = greek_to_latin((string)$orgName);
        $words = preg_split('/[^A-Za-z0-9]+/', $latin, -1, PREG_SPLIT_NO_EMPTY);

        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper($word[0]);
        }
        $initials = substr(preg_replace('/[^A-Z0-9]/', '', $initials), 0, 8);

        return ($initials !== '') ? $initials : 'EA';
    }

    /**
     * Semester tag for course codes: "2022-2023 Spring" -> "22B",
     * "2022-2023 Winter" -> "22A" (first year's last two digits,
     * Winter = A, Spring = B). $usedFallback is set when a part of the
     * session code could not be recognized.
     */
    public static function semesterTag($sessionCode, &$usedFallback = false): string {
        $fallback = false;

        $year = '00';
        if (preg_match('/\d{4}/', $sessionCode, $matches)) {
            $year = substr($matches[0], 2, 2);
        } else {
            $fallback = true;
        }

        if (stripos($sessionCode, 'winter') !== false || mb_stripos($sessionCode, 'χειμ') !== false) {
            $season = 'A';
        } elseif (stripos($sessionCode, 'spring') !== false || mb_stripos($sessionCode, 'εαρ') !== false
                || mb_stripos($sessionCode, 'ανοιξ') !== false) {
            $season = 'B';
        } else {
            // Unknown season: first Latin letter of the last word, else 'X'
            $season = 'X';
            $words = preg_split('/[^A-Za-z0-9]+/', greek_to_latin((string)$sessionCode), -1, PREG_SPLIT_NO_EMPTY);
            foreach (array_reverse($words) as $word) {
                if (ctype_alpha($word[0])) {
                    $season = strtoupper($word[0]);
                    break;
                }
            }
            $fallback = true;
        }

        $usedFallback = $fallback;

        return $year . $season;
    }

    /**
     * Normalize an admin-supplied course-code prefix: uppercase, keep only
     * [A-Z0-9-], collapse dashes, cap at 20 chars (hierarchy.code limit).
     * Returns '' when nothing valid remains - the caller then falls back
     * to the auto-calculated prefix.
     */
    public static function sanitizePrefix($raw): string {
        $prefix = strtoupper(trim((string)$raw));
        $prefix = preg_replace('/[^A-Z0-9-]/', '', $prefix);
        $prefix = preg_replace('/-+/', '-', $prefix);
        $prefix = trim($prefix, '-');

        return trim(substr($prefix, 0, 20), '-');
    }

    /**
     * Next free course code for a prefix, e.g. AUTH-22B -> AUTH-22B-100.
     */
    private static function newCourseCode($prefix): string {
        $base = $prefix . '-';
        $max = Database::get()->querySingle("SELECT MAX(code) AS max_code FROM course WHERE code LIKE ?s", $base . '%');
        if ($max && $max->max_code) {
            $counter = intval(preg_replace('/^' . preg_quote($base, '/') . '/', '', $max->max_code)) + 1;
        } else {
            $counter = 100;
        }
        do {
            $code = $base . $counter;
            $counter++;
        } while (file_exists("courses/$code"));

        return $code;
    }

    /**
     * surname / givenname from legalName, falling back to formattedName
     */
    private static function personNames($person): array {
        $surname = trim((string)($person->legalName->familyName ?? ''));
        $givenname = trim((string)($person->legalName->givenName ?? ''));

        if ($surname === '' && $givenname === '') {
            $formatted = trim((string)($person->formattedName ?? ''));
            if ($formatted !== '') {
                $parts = preg_split('/\s+/', $formatted);
                $surname = array_pop($parts);
                $givenname = implode(' ', $parts);
            }
        }

        return [$surname, $givenname];
    }

    private static function personLabel($person): string {
        $label = trim((string)($person->formattedName ?? ''));
        return $label !== '' ? $label : ($person->sourcedId ?? '?');
    }

}
