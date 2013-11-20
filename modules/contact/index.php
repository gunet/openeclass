<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

if (isset($_REQUEST['from_reg'])) {
	$from_reg = $_REQUEST['from_reg'];
	$course_id = $_REQUEST['course_id'];
}

if (isset($from_reg)) {        
	$require_login = TRUE;
} else {        
	$require_current_course = TRUE;
	$require_help = TRUE;
	$helpTopic = 'Contact';
}

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

if (isset($from_reg)) {
	$title = course_id_to_title($course_id);
}
$nameTools = $langContactProf;

$userdata = mysql_fetch_array(db_query("SELECT surname, givenname, email FROM user WHERE user_id=$uid"));

if (empty($userdata['email'])) {
	if ($uid) {
		$tool_content .= sprintf('<p>'.$langEmailEmpty.'</p>', $urlServer.'modules/profile/profile.php');
	} else {
		$tool_content .= sprintf('<p>'.$langNonUserContact.'</p>', $urlServer);
	}
} elseif (isset($_POST['content'])) {
	$content = trim($_POST['content']);
	if (empty($content)) {
		$tool_content .= "<p>$langEmptyMessage</p>";
		$tool_content .= form();
	} else {
		$tool_content .= email_profs($course_id, $content,
			"$userdata[givenname] $userdata[surname]",
			$userdata['email']);
	}
} else {
	$tool_content .= form();
}

if (isset($from_reg)) {
	draw($tool_content, 1);
} else {
	draw($tool_content, 2);
}



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
function form()
{
	global $from_reg, $course_id, $langInfoAboutRegistration, $langContactMessage, $langIntroMessage, $langSendMessage, $course_code;

	if (isset($from_reg)) {
		$message = $langInfoAboutRegistration;
		$hidden = "<input type='hidden' name='from_reg' value='$from_reg'>
			<input type='hidden' name='course_id' value='$course_id'>";
	} else {
		$message = $langContactMessage;
		$hidden = '';
	}

	$ret = "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	<fieldset>
	<legend>$langIntroMessage</legend>
	$hidden
	<table class='tbl' width='100%'>
	<tbody>
	<tr>
	  <td class='smaller'>$message</td>
	</tr>
	<tr>
	  <td><textarea class=auth_input name='content' rows='10' cols='80'></textarea></td>
	</tr>
	<tr>
	  <td class='right'><input type='submit' name='submit' value='$langSendMessage' /></td>
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
function email_profs($course_id, $content, $from_name, $from_address)
{
        global $themeimg, $langSendingMessage, $langHeaderMessage, $langContactIntro;

        $q = db_query("SELECT public_code FROM course WHERE id = $course_id");
        list($public_code) = mysql_fetch_row($q);

	$ret = "<p>$langSendingMessage</p><br />";

        $profs = db_query("SELECT user.id AS prof_uid, user.email AS email,
                                  user.surname, user.givenname
                               FROM course_user JOIN user ON user.id = course_user.user_id
                               WHERE course_id = $course_id AND course_user.statut = ".USER_TEACHER);

	$message = sprintf($langContactIntro,
		$from_name, $from_address, $content);
	$subject = "$langHeaderMessage ($public_code - $GLOBALS[title])";

	while ($prof = mysql_fetch_array($profs)) {		
                if (!get_user_email_notification_from_courses($prof['prof_uid']) 
                        or (!get_user_email_notification($prof['prof_uid'], $course_id))) {
                        continue;
                } else {
                        $to_name = $prof['givenname'].' '.$prof['surname'];
                        $ret .= "<p><img src='$themeimg/teacher.png'> $to_name</p><br>\n";
                        if (!send_mail($from_name,
                               $from_address,
                               $to_name,
                               $prof['email'],
                               $subject,
                               $message,
                               $GLOBALS['charset'])) {
                        $ret .= "<p class='alert1'>$GLOBALS[langErrorSendingMessage]</p>\n";
                        }
                }
	}
        return $ret;
}
