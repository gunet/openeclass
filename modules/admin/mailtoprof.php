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

$nameTools = $langSendInfoMail;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// Display link back to index.php
$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));
/* * ***************************************************************************
  MAIN BODY
 * **************************************************************************** */
// Send email after form post
if (isset($_POST['submit']) && ($_POST['body_mail'] != '') && ($_POST['submit'] == $langSend)) {

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
    $emailsubject = $langInfoAboutEclass;
    $emailbody = "" . $_POST['body_mail'] . "

$langManager $siteName
" . get_config('admin_name') . "
$langEmail: " . get_config('email_helpdesk') . "
";
    // Send email to all addresses
    foreach ($sql as $m) {
        $emailTo = $m->email;
        $user_id = $m->id;
        // checks if user is notified by email
        if (get_user_email_notification($user_id)) {
            array_push($recipients, $emailTo);
        }
        $linkhere = "&nbsp;<a href='${urlServer}main/profile/profile.php'>$langHere</a>.";
        $unsubscribe = "<br /><br />" . sprintf($langLinkUnsubscribeFromPlatform, $siteName);
        $emailcontent = $emailbody . $unsubscribe . $linkhere;
        if (count($recipients) >= 50) {
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, $charset);
            $recipients = array();
        }
    }
    if (count($recipients) > 0) {
        send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, $charset);
    }
    // Display result and close table correctly
    $tool_content .= "<div class='alert alert-success'>$emailsuccess</div>";
} else {
    // Display form to administrator
    $tool_content .= "<div class='form-wrapper'>
    <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
    <fieldset>        	
	<div class='form-group'>
	  <label for='body_mail' class='col-sm-2 control-label'>$typeyourmessage</label>
              <div class='col-sm-10'>
	      <textarea class='form-control' name='body_mail' rows='10'></textarea>
              </div/>
	</div>
	<div class='form-group'>
	  <label class='col-sm-2 control-label'>$langSendMessageTo</label>
              <div class='col-sm-10'>
	    <select class='form-control' name='sendTo'>
	      <option value='1'>$langProfOnly</option>
		<option value='2'>$langStudentsOnly</option>
	      <option value='0'>$langToAllUsers</option>
	      </select>	    
            </div>
        </div>
	<div class='col-sm-offset-2 col-sm-10'>	
	  <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSend) . "'>
          <a class='btn btn-default' href='index.php'>$langCancel</a>
        </div>	
    </fieldset>
    </form>
    </div>";
}
draw($tool_content, 3);
