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

include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
include 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/auth/auth.inc.php';

$tree = new Hierarchy();
$user = new User();
load_js('jstree3');

$pageName = $langReqRegProf;

$data['eclass_prof_reg'] = get_config('eclass_prof_reg');
$data['email_errors'] = $data['email_invalid'] = $email_errors = false;

if (get_config('email_verification_required')) {
    $user_email_status = get_mail_ver_status($uid);
    if ($user_email_status == EMAIL_UNVERIFIED || $user_email_status == EMAIL_VERIFICATION_REQUIRED) {
        $data['email_invalid'] = true;
    }
}

if (!uid_to_email($uid)) {
    $data['email_invalid'] = true;
}

if (isset($_POST['submit']))  {

    if (count($_POST['department']) < 1 || empty($_POST['department'][0])) {
        Session::flash('message', $langEmptyAddNode);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/auth/formuser.php');
    }

    if (trim($_POST['usercomment']) == '' || trim($_POST['userphone']) == '') {
        Session::flash('message', $langFieldsMissing);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/auth/formuser.php');
    }

    // register user request
    $res = Database::get()->query("INSERT INTO user_request SET
            givenname = '" . uid_to_name($uid, 'givenname') . "', 
            surname = '" .uid_to_name($uid, 'surname') . "', 
            username = '" . uid_to_name($uid, 'username') . "', 
            email = '" . uid_to_email($uid) . "',
            faculty_id = ?d, 
            phone = ?s,
            state = 1, 
            status = " . USER_TEACHER . ",
            verified_mail = " . EMAIL_VERIFIED . ", 
            date_open = " . DBHelper::timeAfter() . ",
            comment = ?s, 
            lang = ?s, 
            request_ip = '" . Log::get_client_ip() . "'",
        $_POST['department'], $_POST['userphone'], $_POST['usercomment'], $language);

    $request_id = $res?->lastInsertID;

    //----------------------------- Email Request Message --------------------------
    $emailhelpdesk = get_config('email_helpdesk');
    $dep_body = $tree->getFullPath($_POST['department']);
    $subject = $langRequestForCourseCreationRights;

    $header_html_topic_notify = "<!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$mailbody1</div>
            </div>
        </div>";

    $body_html_topic_notify = "<!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div id='mail-body-inner'>
            $mailbody2 " . uid_to_name($uid) . " $langRequestForTeacherRights
                <ul id='forum-category'>
                    <li><span><b>$langFaculty:</b></span> <span>$dep_body</span></li>
                    <li><span><b>$langComments:</b></span> <span>" . $_POST['usercomment'] ."</a></span></li>
                    <li><span><b>$langUsername:</b></span> <span> " . uid_to_name($uid, 'username') . "</span></li>
                    <li><span><b>$langEmail:</b></span> <span> " . uid_to_email($uid) . "</span></li>
                    <li><span><b>$contactphone:</b></span> <span> " . $_POST['userphone'] . "</span></li>
                </ul><br><br>$logo
            </div>
        </div>";

    $MailMessage = $header_html_topic_notify.$body_html_topic_notify;

    $plainMailMessage = html2text($MailMessage);

    $emailAdministrator = get_config('email_sender');
    if (!send_mail_multipart($siteName, $emailAdministrator, '', $emailhelpdesk, $subject, $plainMailMessage, $MailMessage)) {
        $data['email_errors'] = $email_errors = true;
    }

} else { // display the form
    $allow_only_defaults = get_config('restrict_teacher_owndep') && !$is_admin; // departments and validation
    $allowables = array();
    if ($allow_only_defaults) {
        // Method: getDepartmentIdsAllowedForCourseCreation
        // fetches only specific tree nodes, not their sub-children
        //$user->getDepartmentIdsAllowedForCourseCreation($uid);
        // the code below searches for the allow_course flag in the user's department subtrees
        $userdeps = $user->getDepartmentIds($uid);
        $subs = $tree->buildSubtreesFull($userdeps);
        foreach ($subs as $node) {
            if (intval($node->allow_course) === 1) {
                $allowables[] = $node->id;
            }
        }
    }
    $departments = $_POST['department'] ?? array();
    $deps_valid = true;

    foreach ($departments as $dep) {
        if ($allow_only_defaults && !in_array($dep, $allowables)) {
            $deps_valid = false;
            break;
        }
    }
    $data['deps_valid'] = $deps_valid;

    list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $allowables, 'allow_only_defaults' => $allow_only_defaults, 'skip_preloaded_defaults' => true));
    $head_content .= $js;
    $data['buildusernode'] = $html;
    $data['usercomment'] = '';
}
view('modules.auth.formuser', $data);
