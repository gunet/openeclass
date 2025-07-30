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

/*
 * Per course settings
 */

require_once 'include/log.class.php';

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
        SETTING_COURSE_USER_REQUESTS_DISABLE => 0,
        SETTING_GROUP_MULTIPLE_REGISTRATION => 0,
        SETTING_GROUP_STUDENT_DESCRIPTION => 0,
        SETTING_COURSE_FORUM_NOTIFICATIONS => 0,
        SETTING_DOCUMENTS_PUBLIC_WRITE => 0,
        SETTING_OFFLINE_COURSE => 0,
        SETTING_USERS_LIST_ACCESS => 1,
        SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION => 1,
        SETTING_FACULTY_USERS_REGISTRATION => 0,
        SETTING_COUSE_IMAGE_STYLE => 0,
        SETTING_COUSE_IMAGE_PRINT_HEADER => 0,
        SETTING_COUSE_IMAGE_PRINT_FOOTER => 0
    );
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

function setting_get_print_image_url($setting_id, $course_id=null) {
    global $urlServer;

    if (!$course_id) {
        $course_id = $GLOBALS['course_id'];
    }

    $document_id = setting_get($setting_id, $course_id);
    if (!$document_id) {
        return null;
    }

    $document = Database::get()->querySingle("SELECT path, filename FROM document 
                                             WHERE id = ?d AND course_id = ?d",
        $document_id, $course_id);
    if (!$document) {
        return null;
    }

    $course_code = course_id_to_code($course_id);
    //return $urlServer . "modules/document/index.php?course=" . $course_code . "&download=" . getInDirectReference($document->path);
    return $urlServer . "modules/document/file.php/" . $course_code . "/" . $document->filename;
}

function setting_get_print_image_disk_path($setting_id, $course_id=null) {
    global $webDir;
    if (!$course_id) {
        $course_id = $GLOBALS['course_id'];
    }
    $document_id = setting_get($setting_id, $course_id);
    if (!$document_id) {
        return null;
    }
    $document = Database::get()->querySingle("SELECT path, filename FROM document 
                                             WHERE id = ?d AND course_id = ?d",
        $document_id, $course_id);
    if (!$document) {
        return null;
    }
    return $webDir . "/courses/" . course_id_to_code($course_id) ."/document". $document->path;
}

function imageToBase64($imagePath) {
    if (!file_exists($imagePath)) {
        return 'not found';
    }

    // Παίρνουμε το mime type σωστά
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $imagePath);
    finfo_close($finfo);

    $data = file_get_contents($imagePath);
    $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($data);

    return $base64;
}
