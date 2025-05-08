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

$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'modules/lti/lib.php';

// Handle sending a user to a tool provider to initiate a content-item selection.

// request parameters
$id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
$courseid = (isset($_REQUEST['course'])) ? $_REQUEST['course'] : '';
$resourcetype = (isset($_REQUEST['resourcetype'])) ? $_REQUEST['resourcetype'] : '';

if (empty($id) || empty($courseid) || empty($resourcetype)) {
    throw new Exception('LTI Error: invalid contentitem request.');
}

$ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $id);
if (empty($ltiApp)) {
    throw new Exception('LTI Error: invalid contentitem request.');
}

$course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $courseid);
if (empty($course) || empty($course->code)) {
    throw new Exception('LTI Error: course not found during contentitem request.');
}
$_SESSION['dbname'] = $course->code;

if ($ltiApp->lti_version === LTI_VERSION_1_3) {
    if (!isset($_SESSION['lti_initiatelogin_status'])) {
        echo ltiInitiateLogin($course, $ltiApp, 'ContentItemSelectionRequest', $resourcetype);
        exit;
    } else {
        unset($_SESSION['lti_initiatelogin_status']);
    }
}

// Check access and capabilities.
$stat = Database::get()->querySingle("SELECT status, tutor, editor, course_reviewer FROM course_user WHERE user_id = ?d AND course_id = ?d", $uid, $courseid);
if (empty($stat) || empty($stat->status)) {
    throw new Exception('LTI Error: course_user not found during contentitem request.');
}
if ($stat->status != USER_TEACHER) {
    throw new Exception('LTI Error: contentitem action requires course editor access.');
}

// Set the return URL.
$returnurlparams = [
    'course' => $courseid,
    'id' => $id,
    'sesskey' => randomkeys(10)
];
$returnurl = $urlServer . "modules/lti/contentitem_return.php?" . getQueryString($returnurlparams);

// Prepare the request.
$request = ltiBuildContentItemSelectionRequest($ltiApp, $course, $stat, $returnurl);

// Get the launch HTML.
$content = ltiPostLaunchHtml($request->params, $request->url, false);

echo $content;