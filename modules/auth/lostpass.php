<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * Password reset component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component resets the user's password after verifying 
 * his/hers  information through a challenge/response system.
 *
 */

include '../../include/baseTheme.php';
include 'auth.inc.php';
include 'include/sendMail.inc.php';
$nameTools = $lang_remind_pass;

function check_password_editable($password)
{
        $authmethods = array('pop3', 'imap', 'ldap', 'db', 'shibboleth', 'cas');
        if (in_array($password,$authmethods)) {
                return false; // it is not editable, because it belongs in external auth method
        } else {
                return true; // is editable
        }
}

if (isset($_GET['do']) and $_GET['do'] == 'go') {
	$userUID = isset($_GET['u'])?intval($_GET['u']):'';
	$hash = isset($_GET['h'])?$_GET['h']:'';
	$res = db_query("SELECT `user_id`, `hash`, `password`, `datetime` FROM passwd_reset
			WHERE `user_id` = '" . mysql_escape_string($userUID) . "'
			AND `hash` = '" . mysql_escape_string($hash) . "'
			AND TIME_TO_SEC(TIMEDIFF(`datetime`,NOW())) < 3600
			", $mysqlMainDb);

	if (mysql_num_rows($res) == 1) {
		$myrow = mysql_fetch_array($res);
		//copy pass hash (md5) from reset_pass to user table
		$sql = "UPDATE `user` SET `password` = '".$myrow['hash']."' WHERE `user_id` = ".$myrow['user_id']."";
		if(db_query($sql, $mysqlMainDb)) {
			//send email to the user of his new pass (not hashed)
			$res = db_query("SELECT `email` FROM user WHERE `user_id` = ".$myrow['user_id']."", $mysqlMainDb);
			$myrow2 = mysql_fetch_array($res);
			$text = "$langPassEmail1 <em>$myrow[password]</em><br>$langPassEmail2";
			$tool_content .= "<div class='success'>
                            <p>$langAccountResetSuccess1</p>
			    <p>$text</p>
    			    <p><a href=\"../../index.php\">$langHome</a></p></div>";
			db_query("DELETE FROM `passwd_reset` WHERE `user_id` = '$myrow[user_id]'", $mysqlMainDb);
			// delete passws_reset entries older from 2 days
			db_query("DELETE FROM `passwd_reset` 
				WHERE DATE_SUB(CURDATE(),INTERVAL 2 DAY) > `datetime`", $mysqlMainDb);
		}
	} else {
		$tool_content = "<div class='caution'>$langAccountResetInvalidLink </div><a href=\"../../index.php\">$langHome</a></td>";
	}
} elseif ((!isset($email) || !isset($userName) || empty($userName)) && !isset($_POST['do'])) {
	/***** Email address entry form *****/
	$tool_content .= "<div class='info'><p>$lang_pass_intro</p></div><br />";
	$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
          <legend>$langUserData</legend>
	  <table class='tbl' width='100%'>
	  <tr>
            <th width='100'>$lang_username:</th>
	    <td><input type=\"text\" name=\"userName\" size=\"40\" /></td>
          </tr>
	  <tr>
	    <th>$lang_email: </th>
	    <td><input type=\"text\" name=\"email\" size=\"40\" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td class='right'><input type=\"submit\" name=\"do\" value=\"".$lang_pass_submit."\" /></td>
          </tr>
	  </table>
        </fieldset>
	</form>";

} elseif (isset($_POST['do'])) {
	$email = isset($_POST['email'])?mb_strtolower(trim($_POST['email'])):'';
	$userName = isset($_POST['userName'])?canonicalize_whitespace($_POST['userName']):'';
	/***** If valid e-mail address was entered, find user and send email *****/
	$res = db_query("SELECT user_id, nom, prenom, username, password, statut FROM user
			WHERE email = '" . mysql_escape_string($email) . "'
			AND BINARY username = '" . mysql_escape_string($userName) . "'", $mysqlMainDb);

        $found_editable_password = false;
	if (mysql_num_rows($res) == 1) {
		$text = $langPassResetIntro. $emailhelpdesk;
		$text .= "$langHowToResetTitle";
		while ($s = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$is_editable = check_password_editable($s['password']);
			if($is_editable) {
                                $found_editable_password = true;
				//insert an md5 key to the db
				$new_pass = create_pass();
				//TODO: add a query to check if the newly generated password already exists in the
				//reset-pass table. If yes, attempt to generate another one.
				$sql = "INSERT INTO `passwd_reset` (`user_id`, `hash`, `password`, `datetime`) VALUES ('".$s['user_id']."',  '".md5($new_pass)."', '$new_pass', NOW())";
				db_query($sql, $mysqlMainDb);
				//prepare instruction for password reset
				$text .= $langPassResetGoHere;
				$text .= $urlServer . "modules/auth/lostpass.php?do=go&u=".$s['user_id']."&h=" .md5($new_pass);

			} else { //other type of auth...
				switch($s['password'])  {
					case 'pop3':{
						$auth = 2;
						break;
					}
					case 'imap':{
						$auth = 3;
						break;
					}
					case 'ldap':{
						$auth = 4;
						break;
					}
					case 'db':{
						$auth = 5;
						break;
					}
					case 'shibboleth': {
						$auth = 6;
						break;
					}
					case 'cas': {
						$auth = 7;
						break;
					}
					default:{
						$auth = 1;
						break;
					}
				}
				$tool_content = "<div class='caution'>
				    <p><strong>$langPassCannotChange1</strong></p>
				    <p>$langPassCannotChange2 ".get_auth_info($auth).". $langPassCannotChange3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a> $langPassCannotChange4</p>
				    <p><a href=\"../../index.php\">$langHome</a></p>
</div>";
			}
		}

	/***** Account details found, now send e-mail *****/
        if ($found_editable_password) {
                $emailsubject = $lang_remind_pass;
                if (!send_mail('', '', '', $email, $emailsubject, $text, $charset)) {
                        $tool_content = "<div class='caution'>
                                <p><strong>$langAccountEmailError1</strong></p>
                                <p>$langAccountEmailError2 $email.</p>
                                <p>$langAccountEmailError3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.</p></div>
                                <p><a href=\"../../index.php\">$langHome</a></p>";
                } elseif (!isset($auth)) {
                    $tool_content .= "<div class='success'>$lang_pass_email_ok <strong>$email</strong><br/><br/><a href=\"../../index.php\">$langHome</a></div>";
                        }
                }
       } else {
		$tool_content .= "<div class='caution'>
		    <p><strong>$langAccountNotFound1 ($userName / $email)</strong></p>
		    <p>$langAccountNotFound2 <a href='mailto: $emailhelpdesk'>$emailhelpdesk</a>, $langAccountNotFound3</p></div>
		    <p><a href=\"../../index.php\">$langHome</a></p>";
        }
} else {
	$tool_content = "<div class='caution'>
	    <p><strong>$langAccountEmailError1</strong></p>
	    <p>$langAccountEmailError2 $email.</p>
	    <p>$langAccountEmailError3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.</p></div>
	    <p><a href=\"../../index.php\">$langHome</a></p>";
}
draw($tool_content,0);
?>
