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

$require_usermanage_user = TRUE;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'info_e_mail';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

$user = new User();
$tree = new Hierarchy();

$toolName = $langAdmin;
$pageName = $langSendInfoMail;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

load_js('jstree3');

$allowables = [];
if (isDepartmentAdmin()) {
    $userdeps = $user->getAdminDepartmentIds($uid);
    $subs = $tree->buildSubtreesFull($userdeps);
    foreach ($subs as $node) {
        if ($node->allow_user) {
            $allowables[] = $node->id;
        }
    }
}

// Send email after form post
if (isset($_POST['submit']) && ($_POST['body_mail'] != '') && ($_POST['submit'] == $langSend)) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isDepartmentAdmin()) {
        $depwh = ' user_department.department IN (' . implode(', ', $allowables) . ') ';
    }

    // Department search
    $depqryadd = $qry_criteria = '';
    $dep = (isset($_POST['department'])) ? intval($_POST['department']) : 0;
    if ($dep || isDepartmentAdmin()) {
        $depqryadd = ', user_department';

        $subs = array();
        if ($dep) {
            $subs = $tree->buildSubtrees(array($dep));
        } else if (isDepartmentAdmin()) {
            $subs = $user->getAdminDepartmentIds($uid);
        }

        foreach ($subs as $key => $id) {
            validateNode($id, isDepartmentAdmin());
        }
        // remove last ',' from $ids
        $deps = implode(', ', $subs);

        $criteria[] = 'AND user.id = user_department.user';
        $criteria[] = 'department IN (' . $deps . ')';
        $qry_criteria = implode(' AND ', $criteria);
    }

    // where we want to send the email ?
    if (isset($_POST['send_to_prof']) and isset($_POST['send_to_users']) and ($_POST['send_to_prof'] == "1") and ($_POST['send_to_users'] == "1")) { // all users
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user, user_department WHERE user.id = user_department.user AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user $depqryadd $qry_criteria");
        }
    } elseif (isset($_POST['send_to_prof']) and $_POST['send_to_prof'] == "1") { // Only teachers
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user, user_department WHERE user.id = user_department.user
                                                                AND user.status = " . USER_TEACHER . " AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user $depqryadd WHERE status = " . USER_TEACHER . " $qry_criteria");
        }
    } elseif (isset($_POST['send_to_users']) and $_POST['send_to_users'] == "1") { // Only students
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user, user_department WHERE user.id = user_department.user
                                            AND user.status = " . USER_STUDENT . " AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, user.id FROM user $depqryadd WHERE status = " . USER_STUDENT . " $qry_criteria");
        }
    }

    $recipients = array();
    $emailsubject = "$langAdminMessage -  $_POST[email_title]";
    $emailbody = "" . $_POST['body_mail'] . "<br>\n$langManager $siteName
" . get_config('admin_name') . " ($langEmail: " . get_config('email_helpdesk') . ")<br>\n";
    // Send email to all addresses
    foreach ($sql as $m) {
        $emailTo = $m->email;
        $user_id = $m->id;
        // checks if user is notified by email and his email address is valid
        if (valid_email($emailTo) and get_user_email_notification($user_id)) {
            $recipients[] = $emailTo;
        }

        $emailheader = "
        <!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$langAdminMessage.</div>
            </div>
        </div>";

        $emailmain = "
        <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div><b>$langMailSubject</b> <span class='left-space'>$_POST[email_title]</span></div><br>
            <div><b>$langMailBody</b></div>
            <div id='mail-body-inner'>
                $_POST[body_mail]
            </div>
        </div>
        ";

        $emailfooter = "
        <!-- Footer Section -->
        <div id='mail-footer'>
            <br>
            <div>
                <small>" . sprintf($langLinkUnsubscribeFromPlatform, $siteName) ." <a href='{$urlServer}main/profile/emailunsubscribe.php'>$langHere</a></small>
            </div>
        </div>
        ";

        $emailcontent = $emailheader.$emailmain.$emailfooter;

        $emailbody = html2text($emailcontent);
        if (count($recipients) >= 50) {
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent);
            $recipients = array();
        }
    }
    if (count($recipients) > 0) {
        send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent);
    }
    Session::flash('message',$emailsuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/mailtoprof.php');
}

$body_mail = $email_title = $data['email_title'] = '';

$userdeps = $user->getDepartmentIds($uid);
$subs = $tree->buildSubtreesFull($userdeps);
foreach ($subs as $node) {
    if (intval($node->allow_course) === 1) {
        $allowables[] = $node->id;
    }
}

list($js, $html) = $tree->buildUserNodePicker(array('params' => 'name="department"',
    'tree' => isDepartmentAdmin()? null: array('0' => $langAllFacultes),
    'allowables' => $allowables,
    'defaults' => $allowables,
    'skip_preloaded_defaults' => true,
    'multiple' => false));

$head_content .= $js;
$data['buildusernode'] = $html;

$data['body_mail_rich_text'] = rich_text_editor('body_mail', 10, 20, $body_mail);

view('admin.users.mailtoprof', $data);
