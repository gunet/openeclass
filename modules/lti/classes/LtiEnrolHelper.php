<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
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
     * @param string|null $sourceid
     * @param string $serviceurl
     * @param string $consumerkey
     * @param string $membershipsurl
     * @param string $membershipsid
     * @return bool returns true if successful, else an error code
     */
    public static function enrol_user(stdClass $tool, int $userid, ?string $sourceid, string $serviceurl, string $consumerkey, string $membershipsurl, string $membershipsid): bool {
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
        return $urlServer . "template/modern/favicon/favicon.ico";
    }

    /**
     * Returns a unique hash for this site and this enrolment instance.
     *
     * Used to verify that the link to the cartridge has not just been guessed.
     *
     * @param int $toolid The id of the shared tool
     * @return string MD5 hash of combined site ID and enrolment instance ID.
     */
    public static function generate_cartridge_token(int $toolid): string {
        global $urlServer;
        return md5($urlServer . '_enrol_lti_cartridge_' . $toolid);
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
     * Verifies that the given token matches the cartridge token of the given shared tool.
     *
     * @param int $toolid The id of the shared tool
     * @param string $token hash for this site and this enrolment instance
     * @return boolean True if the token matches, false if it does not
     */
    public static function verify_cartridge_token(int $toolid, string $token): bool {
        return $token == self::generate_cartridge_token($toolid);
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

    /**
     * Returns the parameters of the cartridge as an associative array of partial xpath.
     *
     * @param object $tool The shared tool
     * @return array Recursive associative array with partial xpath to be concatenated into an xpath expression before setting the value.
     */
    protected static function get_cartridge_parameters(object $tool): array {
        global $urlServer, $siteName, $Institution;

        // Work out the tool properties
        $title = $tool->title;
        $launchurl = self::get_launch_url($tool->id);
        $iconurl = self::get_icon($tool);
        $securelaunchurl = null;
        $secureiconurl = null;
        $vendorurl = $urlServer;
        $description = $tool->description;

        // If we are a https site, we can add the launch url and icon urls as secure equivalents.
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $securelaunchurl = $launchurl;
            $secureiconurl = $iconurl;
        }

        return array(
            "/cc:cartridge_basiclti_link" => array(
                "/blti:title" => $title,
                "/blti:description" => $description,
                "/blti:extensions" => array(
                    "/lticm:property[@name='icon_url']" => $iconurl,
                    "/lticm:property[@name='secure_icon_url']" => $secureiconurl
                ),
                "/blti:launch_url" => $launchurl,
                "/blti:secure_launch_url" => $securelaunchurl,
                "/blti:icon" => $iconurl,
                "/blti:secure_icon" => $secureiconurl,
                "/blti:vendor" => array(
                    "/lticp:code" => $siteName,
                    "/lticp:name" => $siteName,
                    "/lticp:description" => $Institution,
                    "/lticp:url" => $vendorurl
                )
            )
        );
    }

    /**
     * Traverses a recursive associative array, setting the properties of the corresponding
     * xpath element.
     *
     * @param DOMXPath $xpath The xpath with the xml to modify
     * @param array $parameters The array of xpaths to search through
     * @param string $prefix The current xpath prefix (gets longer the deeper into the array you go)
     * @return void
     */
    protected static function set_xpath(DOMXPath $xpath, array $parameters, string $prefix = '') {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                self::set_xpath($xpath, $value, $prefix . $key);
            } else {
                $result = @$xpath->query($prefix . $key);
                if ($result) {
                    $node = $result->item(0);
                    if ($node) {
                        if (is_null($value)) {
                            $node->parentNode->removeChild($node);
                        } else {
                            $node->nodeValue = self::xpath_quote($value);
                        }
                    }
                } else {
                    self::draw_popup_error('Please check your XPATH and try again.');
                }
            }
        }
    }

    /**
     * Add quotes to HTML characters.
     *
     * Returns $var with HTML characters (like "<", ">", etc.) properly quoted.
     *
     * @param string $var the string potentially containing HTML characters
     * @return string
     */
    protected static function xpath_quote(string $var): string {
        if ($var === false) {
            return '0';
        }
        return preg_replace('/&amp;#(\d+|x[0-9a-f]+);/i', '&#$1;',
            htmlspecialchars($var, ENT_QUOTES | ENT_HTML401 | ENT_SUBSTITUTE));
    }

    /**
     * Create an IMS cartridge for the tool.
     *
     * @param object $tool The shared tool
     * @return string representing the generated cartridge
     */
    public static function create_cartridge(object $tool): string {
        $cartridge = new DOMDocument();
        $cartridge->load(realpath(__DIR__ . '/../xml/imslticc.xml'));
        $xpath = new DOMXpath($cartridge);
        $xpath->registerNamespace('cc', 'http://www.imsglobal.org/xsd/imslticc_v1p0');
        $parameters = self::get_cartridge_parameters($tool);
        self::set_xpath($xpath, $parameters);
        return $cartridge->saveXML();
    }

    /**
     * Draw the popup screen with an error attached.
     *
     * @param string $msg
     * @param string|null $type
     * @return void
     */
    public static function draw_popup_error(string $msg, string $type = null) {
        global $tool_content;
        if (!empty($type)) {
            $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$type: $msg</span></div>";
        } else {
            $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$msg</span></div>";
        }
        draw_popup($tool_content);
        exit;
    }

}
