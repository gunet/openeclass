<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/*
 * Mass change user's mail verification status
 *
 * @author Kapetanakis Giannis <bilias@edu.physics.uoc.gr>
 *
 * @abstract This component massively changes user's verification status.
 *
 */

$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langMailVerification;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$mr = get_config('email_required')? $m['yes']: $m['no'];
$mv = get_config('email_verification_required')? $m['yes']: $m['no'];
$mm = get_config('dont_mail_unverified_mails')? $m['yes']: $m['no'];

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
	if ($old_mail_ver != $new_mail_ver) {
		$old_mail_ver = intval($old_mail_ver);
		$new_mail_ver = intval($new_mail_ver);
		$qry = "UPDATE `user` set verified_mail=$new_mail_ver WHERE verified_mail=$old_mail_ver AND user_id!=1";
		$result = db_query($qry);
		if ($result) {
			$count = mysql_affected_rows();
			if ($count > 0) {
				$user = ($count == 1)? $langOfUser: $langUsersS;
				$tool_content .= "<p class='success'>$langMailVerificationChanged {$m['from']} «{$mail_ver_data[$old_mail_ver]}» {$m['in']} «{$mail_ver_data[$new_mail_ver]}» {$m['in']} $count $user</p>";
			}
			// user is admin or no user selected
			else {
				$tool_content .= "<p class='caution'>$langMailVerificationChangedNoAdmin</p>";
			}
		}
	}
	// no change selected
	else {
		$tool_content .= "<p class='info'>$langMailVerificationChangedNo</p>";
	}
}

// admin hasn't clicked on edit
if (empty($submit0) && empty($submit1) && empty($submit2)) {
	$tool_content .= "<form name='mail_verification' method='post' action='$_SERVER[PHP_SELF]'>
	<table width='100%' class='tbl_1' style='margin-top: 20px;'>
		<tr><td class='left' colspan='3'><b>$langMailVerificationSettings</b></td></tr>
		<tr><td class='left' colspan='2'>$lang_email_required:</td>
			<td class='center'>$mr</td></tr>
		<tr><td class='left' colspan='2'>$lang_email_verification_required:</td>
			<td class='center'>$mv</td></tr>
		<tr><td class='left' colspan='2'>$lang_dont_mail_unverified_mails:</td>
			<td class='center'>$mm</td></tr>
		<tr><td colspan='3'>&nbsp;</td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=1'>$langMailVerificationYes</a></td>
			<td class='center'><b>" .
			list_1Result("SELECT count(*) FROM user WHERE verified_mail = 1;") .
			"</b></td><td class='right'><input type='submit' name='submit1' value='{$m['edit']}'></td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></td>
			<td class='center'><b>" .
			list_1Result("SELECT count(*) FROM user WHERE verified_mail = 2;") .
			"</b></td><td class='right'><input type='submit' name='submit2' value='{$m['edit']}'></td></tr>
		<tr><td><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></td>
			<td class='center'><b>" .
			list_1Result("SELECT count(*) FROM user WHERE verified_mail = 0;") .
			"</b></td><td class='right'><input type='submit' name='submit0' value='{$m['edit']}'></td></tr>
		<tr><td><a href='listusers.php?search=yes'>$langTotal $langUsersOf</a></td>
			<td class='center'><b>" .
			list_1Result("SELECT count(*) FROM user;") .
			"</b></td><td class='right'>&nbsp;</td></tr>
	</table></form>";
}
// admin wants to change user's mail verirication value. 3 possible
else {
	if (!empty($submit0)) {
		$sub = 0;
		$msg = $langMailVerificationPending;
	}
	elseif (!empty($submit1)) {
		$sub = 1;
		$msg = $langMailVerificationYes;
	}
	elseif (!empty($submit2)) {
		$sub = 2;
		$msg = $langMailVerificationNo;
	}
	else {
		$sub = NULL;
	}
	$c = list_1Result("SELECT count(*) FROM user WHERE verified_mail = $sub;");

	if (isset($sub)) {
		$tool_content .= "<form name='mail_verification_change' method='post' action='$_SERVER[PHP_SELF]'>
		<fieldset>
		<legend>$msg ($langNbUsers: $c)</legend>
		<table width='100%' class='tbl'>
		<tr><th class='left'>$langChangeTo: </th>
			<td>";
		$tool_content .= selection($mail_ver_data, "new_mail_ver", $sub);

		$tool_content .= "</td>
		</tr>
		<tr><th>&nbsp;</th><td class='left'><input type='submit' name='submit' value='{$m['edit']}'></td></tr>
		<tr><th colspan='2'><input type='hidden' name='old_mail_ver' value='$sub' /></th></tr>
		</table>
		</fieldset>
		</form>
		";
	}
}

$tool_content .= "
	<p class='noteit'><b>$langNote</b>:<br />$langMailVerificationNotice</p>";
$tool_content .= "
	<p class='info'>$langMailVerificationNoticeAdmin</p>";

function list_1Result($sql) {
   global $mysqlMainDb;

   $res = db_query($sql, $mysqlMainDb);
   $res = mysql_fetch_array($res);
   return $res[0];
}

draw($tool_content,3);
