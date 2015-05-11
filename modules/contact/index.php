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

if (isset($_REQUEST['course_id'])) {    
    $course_id = $_REQUEST['course_id'];
}

$title = course_id_to_title($course_id);
$pageName = $langContactProf;

$userdata = Database::get()->querySingle("SELECT givenname, surname, email FROM user WHERE id = ?d", $uid);

if (empty($userdata->email)) {
    if ($uid) {
        $tool_content .= sprintf('<p>' . $langEmailEmpty . '</p>', $urlServer . 'main/profile/profile.php');
    } else {
        $tool_content .= sprintf('<p>' . $langNonUserContact . '</p>', $urlServer);
    }
} elseif (isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if (empty($content)) {
        $tool_content .= "<p>$langEmptyMessage</p>";
        $tool_content .= form();
    } else {
        $tool_content .= email_profs($course_id, $content, "$userdata->givenname $userdata->surname", $userdata->email);
    }
} else {
    $tool_content .= form();
}

draw($tool_content, 1);

/**
 * @brief display form
 * @global type $from_reg
 * @global type $course_id
 * @global type $langInfoAboutRegistration
 * @global type $langContactMessage
 * @global type $langIntroMessage
 * @global type $langSendMessage
 * @global type $course_code
 * @return type
 */
function form() {
    global $course_id, $langInfoAboutRegistration, $langIntroMessage, $langSendMessage, $course_code;
    
    $message = $langInfoAboutRegistration;
    $hidden = "<input type='hidden' name='course_id' value='$course_id'>";
     
    $ret = "<form method='post' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	<fieldset>
	<legend>$langIntroMessage</legend>
	$hidden
	<table class='table-default'>
	<tbody>
	<tr>
	  <td class='smaller'>$message</td>
	</tr>
	<tr>
	  <td><textarea class=auth_input name='content' rows='10' cols='80'></textarea></td>
	</tr>
	<tr>
	  <td class='right'><input class='btn btn-primary' type='submit' name='submit' value='$langSendMessage' /></td>
	</tr>
	</tbody>
	</table>
	</fieldset>
	</form>";

    return $ret;
}

/**
 * @brief send emails to course prof
 * @global type $themeimg
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
    global $themeimg, $langSendingMessage, $langHeaderMessage, $langContactIntro;

    $q = Database::get()->querySingle("SELECT public_code FROM course WHERE id = ?d", $course_id);
    $public_code = $q->public_code;

    $ret = "<p>$langSendingMessage</p><br />";

    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                                  user.surname, user.givenname
                               FROM course_user JOIN user ON user.id = course_user.user_id
                               WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);

    $message = sprintf($langContactIntro, $from_name, $from_address, $content);
    $subject = "$langHeaderMessage ($public_code - $GLOBALS[title])";    
    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {            
            continue;
        } else {
            $to_name = $prof->givenname . ' ' . $prof->surname;
            $ret .= "<p><img src='$themeimg/teacher.png'> $to_name</p><br>\n";
            if (!send_mail($from_name, $from_address, $to_name, $prof->email, $subject, $message, $GLOBALS['charset'])) {
                $ret .= "<div class='alert alert-warning'>$GLOBALS[langErrorSendingMessage]</div>\n";               
            }
        }
    }
    return $ret;
}
