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

/**
  /*
 * Mass change user's mail verification status
 * @author Kapetanakis Giannis <bilias@edu.physics.uoc.gr>
 * @abstract This component massively changes user's verification status.
 *
 */
$require_admin = TRUE;
require_once '../../include/baseTheme.php';
$toolName = $langMailVerification;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ));

$mr = get_config('email_required') ? $langYes : $langNo;
$mv = get_config('email_verification_required') ? $langYes : $langNo;
$mm = get_config('dont_mail_unverified_mails') ? $langYes : $langNo;

register_posted_variables(array(
    'submit' => true,
    'submit0' => true,
    'submit1' => true,
    'submit2' => true,
    'old_mail_ver' => true,
    'new_mail_ver' => true
));

$mail_ver_data[0] = $langMailVerificationPendingU;
$mail_ver_data[1] = $langMailVerificationYesU;
$mail_ver_data[2] = $langMailVerificationNoU;

if (!empty($submit) && (isset($old_mail_ver) && isset($new_mail_ver))) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($old_mail_ver != $new_mail_ver) {
        $old_mail_ver = intval($old_mail_ver);
        $new_mail_ver = intval($new_mail_ver);
        $count = Database::get()->query("UPDATE `user` set verified_mail=?s WHERE verified_mail=?s AND id != 1", $new_mail_ver, $old_mail_ver)->affectedRows;
        if ($count > 0) {
            $user = ($count == 1) ? $langOfUser : $langUsersS;
            $tool_content .= "<div class='alert alert-success'>$langMailVerificationChanged {$m['from']} «{$mail_ver_data[$old_mail_ver]}» {$m['in']} «{$mail_ver_data[$new_mail_ver]}» {$m['in']} $count $user</div>";
        }
        // user is admin or no user selected
        else {
            $tool_content .= "<div class='alert alert-danger'>$langMailVerificationChangedNoAdmin</div>";
        }
    }
    // no change selected
    else {
        $tool_content .= "<div class='alert alert-info'>$langMailVerificationChangedNo</div>";
    }
}

// admin hasn't clicked on edit
if (empty($submit0) && empty($submit1) && empty($submit2)) {
    $tool_content .= "<form name='mail_verification' method='post' action='$_SERVER[SCRIPT_NAME]'>
        <div class='table-responsive'>
	<table class='table-default'>
		<tr><td class='text-left' colspan='3'><b>$langMailVerificationSettings</b></td></tr>
		<tr><td class='text-left' colspan='2'>$lang_email_required:</td>
			<td class='text-center'>$mr</td></tr>
		<tr><td class='text-left' colspan='2'>$lang_email_verification_required:</td>
			<td class='text-center'>$mv</td></tr>
		<tr><td class='text-left' colspan='2'>$lang_dont_mail_unverified_mails:</td>
			<td class='text-center'>$mm</td></tr>
		<tr><td colspan='3'>&nbsp;</td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=1'>$langMailVerificationYes</a></td>
			<td class='text-center'><b>" .
            Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt .
            "</b></td><td class='text-right'><input class='btn btn-primary' type='submit' name='submit1' value='{$m['edit']}'></td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></td>
			<td class='text-center'><b>" .
            Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt .
            "</b></td><td class='text-right'><input class='btn btn-primary' type='submit' name='submit2' value='{$m['edit']}'></td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></td>
			<td class='text-center'><b>" .
            Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt .
            "</b></td><td class='text-right'><input class='btn btn-primary' type='submit' name='submit0' value='{$m['edit']}'></td></tr>";
    if (!get_config('email_required')) {
        $tool_content .= "<tr><td><a href='listusers.php?search=yes&verified_mail=0'>$langUsersWithNoMail</a></td>
                                <td class='text-center'><b>" .
                Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE email = '';")->cnt .
                "</b></td><td class='text-right'>&nbsp;</td></tr>";
    }
    $tool_content .= "<tr><td><a href='listusers.php?search=yes'>$langTotal $langUsersOf</a></td>
			<td class='text-center'><b>" .
            Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt .
            "</b></td><td class='text-right'>&nbsp;</td></tr>
	</table></div>". generate_csrf_token_form_field() ."</form>";
}
// admin wants to change user's mail verification value. 3 possible
else { 
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (!empty($submit0)) {
        $sub = 0;
        $msg = $langMailVerificationPending;
    } elseif (!empty($submit1)) {
        $sub = 1;
        $msg = $langMailVerificationYes;
    } elseif (!empty($submit2)) {
        $sub = 2;
        $msg = $langMailVerificationNo;
    } else {
        $sub = NULL;
    }
    $c = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = $sub;")->cnt;

    if (isset($sub)) {
        $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' role='form' name='mail_verification_change' method='post' action='$_SERVER[SCRIPT_NAME]'>
		<fieldset>		
                <div class='form-group'>
		<label class='col-sm-2 control-label'>$langChangeTo:</label>
                <div class='col-sm-10'>";
        $tool_content .= selection($mail_ver_data, "new_mail_ver", $sub, "class='form-control'");
        $tool_content .= "</div>
		</div>
		<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit' value='$langEdit'></div>
		<input type='hidden' name='old_mail_ver' value='$sub'>		
		</fieldset>
        ". generate_csrf_token_form_field() ."
		</form></div>";
    }
}

$tool_content .= "<div class='alert alert-warning'><b>$langNote</b>:<br>$langMailVerificationNotice</div>";
$tool_content .= "<div class='alert alert-info'>$langMailVerificationNoticeAdmin</div>";

draw($tool_content, 3);
