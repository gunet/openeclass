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

/** @file index.php
 *  @brief display form to contact with course prof if course is closed
 */

$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/course_settings.php';

$toolName = $langLabelCourseUserRequest;

if (isset($_REQUEST['course_id'])) {
    $course_id = $_REQUEST['course_id'];
}
$log_course_user_requests = setting_get(SETTING_COURSE_USER_REQUESTS, $course_id);

if (!$log_course_user_requests) {
    redirect_to_home_page();
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, surname, email FROM user WHERE id = ?d", $uid);

if (empty($userdata->email)) {
    if ($uid) {
        $tool_content .= sprintf('<div class = "alert alert-warning">' . $langEmailEmpty . '</div>', $urlServer . 'main/profile/profile.php');
    } else {
        $tool_content .= sprintf('<p>' . $langNonUserContact . '</p>', $urlServer);
    }
} elseif (isset($_POST['content'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $content = trim($_POST['content']);
    if (empty($content)) {
        $tool_content .= "<div class='alert alert-warning'>$langEmptyMessage</div>";
        $tool_content .= form("$userdata->surname $userdata->givenname");
    } else {
        $tool_content .= email_profs($course_id, $content, "$userdata->givenname $userdata->surname", $userdata->email);        
        Database::get()->query("INSERT INTO course_user_request SET uid = ?d, course_id = ?d, 
                                                        status = 1, comments = ?s, 
                                                        ts = " . DBHelper::timeAfter() . "",
                                                    $uid, $course_id, $content);
        
    }
} else {
    $tool_content .= form("$userdata->surname $userdata->givenname");
}

draw($tool_content, 1);

/**
 * @brief display form
 * @param type $user
 * @global type $course_id
 * @global type $course_code
 * @global type $langInfoAboutRegistration 
 * @global type $langSendTo
 * @global type $course_code
 * @global type $langFrom 
 * @return type
 */
function form($user) {
    global $course_id, $langInfoAboutRegistration, $langFrom, $langSendTo, $langSubmitNew, $course_code, $langRequest;
           
    $userprof = '';
    $profdata = Database::get()->queryArray("SELECT user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);
    foreach ($profdata as $prof) {
        $userprof .= "$prof->surname $prof->givenname &nbsp;&nbsp;";
    }
    
    $ret = "<div class='alert alert-info'>$langInfoAboutRegistration</div>";
    $ret .= "<div class='form-wrapper'>";
    $ret .= "<form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	<fieldset>
        <div class='col-sm-12'><label>$langRequest</label></div>
        <div class='col-sm-12'><label>$langFrom:&nbsp;</label><small>$user</small></div>
        <div class='col-sm-12'><label>$langSendTo:&nbsp;</label><small>$userprof</small></div>
        <div class='form-group'>
            <div class='col-sm-12'>
              <textarea name='content' rows='10' cols='80'></textarea>
            </div>
	</div>
        <div class='form-group'>
            <div class='col-sm-offset-1 col-sm-11'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmitNew) . "' />
            </div>
        </div>		
        ". generate_csrf_token_form_field() ."
        <input type='hidden' name='course_id' value='$course_id'>
	</fieldset></form></div>";

    return $ret;
}

/**
 * @brief send emails to course prof 
 * @global type $langSendingMessage
 * @global type $langHeaderMessage
 * @global type $langContactIntro
 * @param type $course_id
 * @param type $content
 * @param type $from_name
 * @param type $from_address
 * @return type
 */
function email_profs($course_id, $content, $from_name, $from_address) {
    global $langSendingMessage, $langHeaderMessage, $langContactIntro, $langNote, $langMessage, $langContactIntroFooter;

    $title = course_id_to_title($course_id);
    $ret = "<div class='alert alert-info'>$langSendingMessage $title</div>";
    $public_code = course_id_to_public_code($course_id);
    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);

    $subject = "$langHeaderMessage ($public_code - $title)";


    $mailHeader = "
    <!-- Header Section -->
	<div id='mail-header'>
		<div>
			<br>
			<div id='header-title'>".q(sprintf($langContactIntro, $from_name, $from_address))."</div>
		</div>
	</div>";
    
    $mailMain = "
    <!-- Body Section -->
	<div id='mail-body'>
		<br>
		<div><b>$langMessage:</b></div>
		<div id='mail-body-inner'>
			$content
        </div>
	</div>";
    
    $mailFooter = "
    <!-- Footer Section -->
	<div id='mail-footer'>
		<br>
		<div id='alert'><small><b class='notice'>$langNote:</b> $langContactIntroFooter.</small></div>
	</div>";

    $message = $mailHeader.$mailMain.$mailFooter;
    $plainMessage = html2text($message);

    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {            
            continue;
        } else {
            $to_name = $prof->givenname . ' ' . $prof->surname;
            $ret .= "<div class='alert alert-success'>" . icon('fa-university') . "&nbsp;" . q($to_name) . "</div>";
            if (!send_mail_multipart($from_name, $from_address, $to_name, $prof->email, $subject, $plainMessage, $message, $GLOBALS['charset'])) {
                $ret .= "<div class='alert alert-warning'>$GLOBALS[langErrorSendingMessage]</div>";
            }
        }
    }    
    return $ret;
}
