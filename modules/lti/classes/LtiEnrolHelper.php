<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2022  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ========================================================================
 */

/**
 * LTI enrolment plugin helper class.
 */
class LtiEnrolHelper {
    /*
     * The value used when we want to enrol new members and unenrol old ones.
     */
    const MEMBER_SYNC_ENROL_AND_UNENROL = 1;

    /*
     * The value used when we want to enrol new members only.
     */
    const MEMBER_SYNC_ENROL_NEW = 2;

    /*
     * The value used when we want to unenrol missing users.
     */
    const MEMBER_SYNC_UNENROL_MISSING = 3;

    /**
     * Code for when an enrolment was successful.
     */
    const ENROLMENT_SUCCESSFUL = true;

    /**
     * Error code for enrolment when max enrolled reached.
     */
    const ENROLMENT_MAX_ENROLLED = 'maxenrolledreached';

    /**
     * Error code for enrolment has not started.
     */
    const ENROLMENT_NOT_STARTED = 'enrolmentnotstarted';

    /**
     * Error code for enrolment when enrolment has finished.
     */
    const ENROLMENT_FINISHED = 'enrolmentfinished';

    /**
     * Error code for when an image file fails to upload.
     */
    const PROFILE_IMAGE_UPDATE_SUCCESSFUL = true;

    /**
     * Error code for when an image file fails to upload.
     */
    const PROFILE_IMAGE_UPDATE_FAILED = 'profileimagefailed';

    /**
     * Creates a unique username.
     *
     * @param string $localDomain Local domain
     * @param String $remoteDomain Remote domain
     * @param string $ltiUserId External tool user id
     * @return string The new username
     */
    public static function create_username(string $localDomain, string $remoteDomain, string $ltiUserId): string {
        return 'enrol_lti_' . sha1($localDomain . '::' . $remoteDomain . ':' . $ltiUserId);
    }

    /**
     * Compares two users.
     *
     * @param stdClass $newuser The new user
     * @param stdClass $olduser The old user
     * @return bool True if both users are the same
     */
    public static function user_match(stdClass $newuser, stdClass $olduser): bool {
        if ($newuser->givenname != $olduser->givenname) {
            return false;
        }
        if ($newuser->surname != $olduser->surname) {
            return false;
        }
        if ($newuser->email != $olduser->email) {
            return false;
        }
        if ($newuser->username != $olduser->username) {
            return false;
        }
        if ($newuser->password != $olduser->password) {
            return false;
        }

        return true;
    }

    /**
     * Enrol a user in a course.
     *
     * @param stdClass $tool The tool object (retrieved using self::get_lti_tool() or self::get_lti_tools())
     * @param int $userid The user id
     * @param string $sourceid
     * @param string $serviceurl
     * @param string $consumerkey
     * @param string $membershipsurl
     * @param string $membershipsid
     * @return bool returns true if successful, else an error code
     */
    public static function enrol_user(stdClass $tool, int $userid, string $sourceid, string $serviceurl, string $consumerkey, string $membershipsurl, string $membershipsid): bool {
        $now = time();

        // check if the user enrolment exists
        $record_exists = Database::get()->querySingle("SELECT 1 AS record_exists FROM course_lti_publish_user_enrolments WHERE publish_id = ?d AND user_id = ?d",
            $tool->id,
            $userid
        );
        if (!$record_exists) {
            // enroll the user
            Database::get()->query("INSERT INTO course_lti_publish_user_enrolments (publish_id, user_id, created, updated) VALUES (?d, ?d, ?d, ?d)",
                $tool->id,
                $userid,
                $now,
                $now
            );
        }

        // check if the course_user user enrolment exists
        $record_exists = Database::get()->querySingle("SELECT 1 AS record_exists FROM course_user WHERE course_id = ?d AND user_id = ?d",
            $tool->course_id,
            $userid
        );
        if (!$record_exists) {
            // enroll the user
            Database::get()->query("INSERT INTO course_user (course_id, user_id, status, reg_date, document_timestamp) VALUES (?d, ?d, ?d, NOW(), NOW())",
                $tool->course_id,
                $userid,
                USER_STUDENT
            );
        }

        // Check if we have recorded this user before.
        $record_exists = Database::get()->querySingle("SELECT 1 AS record_exists FROM course_lti_enrol_users WHERE publish_id = ?d AND user_id = ?d",
            $tool->id,
            $userid
        );
        if (!$record_exists) {
            Database::get()->query("INSERT INTO course_lti_enrol_users 
                (publish_id, user_id, service_url, source_id, consumer_key, consumer_secret, memberships_url, memberships_id, last_grade, last_access, time_created) 
                VALUES 
                (?d, ?d, ?s, ?s, ?s, ?s, ?s, ?s, ?f, ?d, ?d)",
                $tool->id,
                $userid,
                $serviceurl,
                $sourceid,
                $consumerkey,
                $tool->lti_provider_secret,
                $membershipsurl,
                $membershipsid,
                0,
                $now,
                $now
            );
        } else {
            Database::get()->query("UPDATE course_lti_enrol_users SET service_url = ?s, source_id = ?s, last_access = ?d WHERE publish_id = ?d AND user_id = ?d",
                $serviceurl,
                $sourceid,
                $now,
                $tool->id,
                $userid
            );
        }

        return self::ENROLMENT_SUCCESSFUL;
    }

    /**
     * Returns the LTI tool.
     *
     * @param int $toolid
     * @return array|DBResult|null the tool
     */
    public static function get_lti_tool(int $toolid) {
        return Database::get()->querySingle("SELECT * FROM course_lti_publish WHERE id = ?d", $toolid);
    }

    /**
     * Returns the url to launch the lti tool.
     *
     * @param int $toolid the id of the shared tool
     * @return string the url to launch the tool
     */
    public static function get_launch_url(int $toolid): string {
        global $urlServer;
        return $urlServer . "modules/lti/tool.php?id=" . $toolid;
    }

    /**
     * Returns the icon of the tool.
     *
     * @param stdClass $tool The lti tool
     * @return string A url to the icon of the tool
     */
    public static function get_icon(stdClass $tool): string {
        global $urlServer;
        return $urlServer . "template/favicon/favicon.ico";
    }

    /**
     * Returns a unique hash for this site and this enrolment instance.
     *
     * Used to verify that the link to the proxy has not just been guessed.
     *
     * @param int $toolid The id of the shared tool
     * @return string MD5 hash of combined site ID and enrolment instance ID.
     */
    public static function generate_proxy_token(int $toolid): string {
        global $urlServer;
        return md5($urlServer . '_enrol_lti_proxy_' . $toolid);
    }

    /**
     * Verifies that the given token matches the proxy token of the given shared tool.
     *
     * @param int $toolid The id of the shared tool
     * @param string $token hash for this site and this enrolment instance
     * @return boolean True if the token matches, false if it does not
     */
    public static function verify_proxy_token(int $toolid, string $token): bool {
        return $token == self::generate_proxy_token($toolid);
    }

}
