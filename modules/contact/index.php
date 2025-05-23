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

/** @file index.php
 *  @brief display form to contact with course prof if course is closed
 */

$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/course_settings.php';

$toolName = $langLabelCourseUserRequest;

$disable_course_user_requests = setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course_id);

if ($disable_course_user_requests) {
    redirect_to_home_page();
}

if (isset($_REQUEST['course_id']) and (!empty($_REQUEST['course_id']))) {
    $course_id = $_REQUEST['course_id'];
    $is_collaborative_course = Database::get()->querySingle("SELECT is_collaborative FROM course WHERE id = ?d",$course_id)->is_collaborative;
} else {
    redirect_to_home_page();
}

$userdata = Database::get()->querySingle("SELECT username, givenname, surname, email FROM user WHERE id = ?d", $uid);

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
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEmptyMessage</span></div></div>";
        $tool_content .= form("$userdata->surname $userdata->givenname");
    } else {

        $tool_content .= action_bar(array(
        array('title' => "$langBack",
            'url' => "../auth/courses.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
        ));

        $tool_content .= email_profs($course_id, $content, "$userdata->givenname $userdata->surname", $userdata->username, $userdata->email);
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
 * @global type $langOfCourse
 * @global type $langRequestReasons
 * @return type
 */
function form($user) {
    global $course_id, $langInfoAboutRegistration, $langFrom, $langSendTo,
            $langSubmitNew, $course_code, $langRequest, $langOfCourse, $langRequestReasons, $urlAppend,
            $is_collaborative_course, $langInfoAboutCollabRegistration, $langLabelCollabUserRequest,
            $langRequestReasonsCollab, $langTypeCollaboration, $langImgFormsDes, $langForm;

    if($is_collaborative_course){
        $langInfoAboutRegistration = $langInfoAboutCollabRegistration;
        $langLabelCollabUserRequest = $langLabelCollabUserRequest;
        $langRequestReasons = $langRequestReasonsCollab;
        $langOfCourse = $langTypeCollaboration;
    }

    $userprof = '';
    $profdata = Database::get()->queryArray("SELECT user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);
    foreach ($profdata as $prof) {
        $userprof .= "$prof->surname $prof->givenname &nbsp;&nbsp;";
    }

    $ret = "<div class='col-sm-12 mt-3'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langInfoAboutRegistration</span></div></div>";
    $ret .= "<div class='row m-auto'><div class='col-lg-6 col-12 px-0'><div class='form-wrapper form-edit p-0 border-0 mt-2 mb-3 rounded'>";
    $ret .= "<form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	<fieldset>
        <legend class='mb-0' aria-label='$langForm'></legend>
        <div class='col-sm-12'><div class='control-label-notes'>$langRequest $langOfCourse:</div><small>" . course_id_to_title($course_id) . "</small></div>
        <div class='col-sm-12'><div class='control-label-notes mt-4'>$langFrom:&nbsp;</div><small>$user</small></div>
        <div class='col-sm-12'><div class='control-label-notes mt-4'>$langSendTo:&nbsp;</div><small>$userprof</small></div>
        <div class='form-group mt-4'>
            <div class='col-sm-12'>
              <textarea aria-label='$langRequestReasons' name='content' rows='10' cols='80' placeholder='$langRequestReasons'></textarea>
            </div>
	    </div>
        <div class='form-group mt-4'>
            <div class='col-sm-offset-1 col-sm-11'>
                <input class='btn submitAdminBtn' type='submit' name='submit' value='" . q($langSubmitNew) . "' />
            </div>
        </div>		
        ". generate_csrf_token_form_field() ."
        <input type='hidden' name='course_id' value='$course_id'>
	</fieldset></form></div></div>
    <div class='col-lg-6 col-12 d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div></div>";

    return $ret;
}

/**
 * @brief send emails to course prof
 * @global type $langSendingMessage
 * @global type $langLabelCourseUserRequest
 * @global type $langContactIntro
 * @global type $urlServer
 * @global type $langHere
 * @param type $course_id
 * @param type $content
 * @param type $from_name
 * @param type $from_username
 * @param type $from_address
 * @return type
 */
function email_profs($course_id, $content, $from_name, $from_username, $from_address) {
    global $langSendingMessage, $langLabelCourseUserRequest, $langContactIntro,
            $langHere, $urlServer, $langNote, $langMessage, $langContactIntroFooter;

    $c_code = course_id_to_code($course_id);
    $title = course_id_to_title($course_id);
    $public_code = course_id_to_public_code($course_id);
    $ret = "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langSendingMessage $title</span></div></div>";
    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);

    $subject = "$langLabelCourseUserRequest $title ($public_code)";

    $mailHeader = "
    <!-- Header Section -->
	<div id='mail-header'>
		<div>
			<br>
			<div id='header-title'>".q(sprintf($langContactIntro, $from_name, $from_username, $from_address))."</div>
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
		<div id='alert'><small><b class='notice'>$langNote: </b>" . q($langContactIntroFooter) . "
                <a href='{$urlServer}modules/user/course_user_requests.php?course=$c_code'>$langHere</a>.</small></div>
	</div>";

    $message = $mailHeader.$mailMain.$mailFooter;
    $plainMessage = html2text($message);
    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {
            continue;
        } else {
            $to_name = $prof->givenname . ' ' . $prof->surname;
            $ret .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>" . "" . "&nbsp;" . q($to_name) . "</span></div></div>";
            if (!send_mail_multipart($from_name, $from_address, $to_name, $prof->email, $subject, $plainMessage, $message)) {
                $ret .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$GLOBALS[langErrorSendingMessage]</span></div></div>";
            }
        }
    }
    return $ret;
}
