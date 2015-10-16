<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== */

/*
 * Per course settings
 */

require_once 'include/log.php';

/** Get the default value of a course setting.
 * 
 * @param int $setting_id   One of the SETTING_... constants
 * @return int Setting value
 */
function setting_default($setting_id) {
    global $langUnknownSetting;
    
    $defaults = array(
        SETTING_BLOG_COMMENT_ENABLE => 1,
        SETTING_BLOG_STUDENT_POST => 1,
        SETTING_BLOG_RATING_ENABLE => 1,
        SETTING_BLOG_SHARING_ENABLE => 0,
        SETTING_COURSE_SHARING_ENABLE => 0,
        SETTING_COURSE_RATING_ENABLE => 0,
        SETTING_COURSE_COMMENT_ENABLE => 0,
        SETTING_COURSE_ANONYMOUS_RATING_ENABLE => 0,
        SETTING_FORUM_RATING_ENABLE => 0,
        SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE => 0,
        SETTING_COURSE_ABUSE_REPORT_ENABLE => 0,
        SETTING_COURSE_USER_REQUESTS => 0,
        SETTING_GROUP_MULTIPLE_REGISTRATION => 0);
    if (isset($defaults[$setting_id])) {
        return $defaults[$setting_id];
    } else {
        die("$langUnknownSetting $setting_id\n");
    }
}

/** Get the value of a course setting.
 * 
 * @param int $setting_id   One of the SETTING_... constants
 * @param int $course_id    The course id (default: the current course id)
 * @return int Setting value
 */
function setting_get($setting_id, $course_id=null) {
    if (!$course_id) {
        $course_id = $GLOBALS['course_id'];
    }
    $result = Database::get()->querySingle("SELECT value FROM course_settings
                                                WHERE setting_id = ?d AND course_id = ?d",
                                           $setting_id, $course_id);
    if ($result) {
        return $result->value;
    } else {
        return setting_default($setting_id);
    }
}

/** Set the value of a course setting.
 * 
 * @param int $setting_id   One of the SETTING_... constants
 * @param int $value        New value of the setting
 * @param int $course_id    The course id (default: the current course id)
 */
function setting_set($setting_id, $value, $course_id=null) {
    global $course_code;
    
    if (!$course_id) {
        $course_id = $GLOBALS['course_id'];
    }
    $result = Database::get()->query("REPLACE INTO course_settings
                                          (setting_id, course_id, value)
                                          VALUES (?d, ?d, ?d)",
                                     $setting_id, $course_id, $value);
    if ($result) {
        Log::record($course_id, MODULE_ID_COURSEINFO, LOG_MODIFY,
            array('id' => $setting_id, 'value' => $value));
    }
}
