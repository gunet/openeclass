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

$require_usermanage_user = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$user = new User();

/* * ***************************************************************************
  MAIN BODY
 * **************************************************************************** */
// Send email after form post
if (isset($_POST['submit']) && ($_POST['body_mail'] != '') && ($_POST['submit'] == $langSend)) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (isDepartmentAdmin()) {
        $depwh = ' user_department.department IN (' . implode(', ', $user->getDepartmentIds($uid)) . ') ';
    }

    // where we want to send the email ?
    if ($_POST['sendTo'] == '0') { // All users
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, id FROM user, user_department WHERE user.id = user_department.user AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, id FROM user");
        }
    } elseif ($_POST['sendTo'] == "1") { // Only professors
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, id FROM user, user_department WHERE user.id = user_department.user 
                                                                AND user.status = " . USER_TEACHER . " AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, id FROM user where status = " . USER_TEACHER . "");
        }
    } elseif ($_POST['sendTo'] == "2") { // Only students
        if (isDepartmentAdmin()) {
            $sql = Database::get()->queryArray("SELECT email, id FROM user, user_department WHERE user.id = user_department.user
                                            AND user.status = " . USER_STUDENT . " AND " . $depwh);
        } else {
            $sql = Database::get()->queryArray("SELECT email, id FROM user where status = " . USER_STUDENT . "");
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
        // checks if user is notified by email
        if (get_user_email_notification($user_id)) {
            array_push($recipients, $emailTo);
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
            <div><b>$langSubject:</b> <span class='left-space'>$_POST[email_title]</span></div><br>
            <div><b>$langMailBody:</b></div>
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
                <small>" . sprintf($langLinkUnsubscribeFromPlatform, $siteName) ." <a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
            </div>
        </div>
        ";

        $emailcontent = $emailheader.$emailmain.$emailfooter;

        $emailbody = html2text($emailcontent);
        if (count($recipients) >= 50) {
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, $charset);
            $recipients = array();
        }
    }
    if (count($recipients) > 0) {
        send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, $charset);
    }
    Session::Messages($emailsuccess, 'alert-success');
    redirect_to_home_page('modules/admin/mailtoprof.php');
}
$toolName = $langSendInfoMail;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// Display link back to index.php
$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

$data['body_mail_rich_text'] = rich_text_editor('body_mail', 10, 20, '');
$data['menuTypeID'] = 3;

view('admin.users.mailtoprof', $data);
