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

if (isset($_REQUEST['u']) and
    isset($_REQUEST['h']) and
    isset($_REQUEST['ts'])) {
        $change_ok = false;
        $ts = intval($_REQUEST['ts']);
	$userUID = intval($_REQUEST['u']);
        $valid = token_validate('password', $userUID, $_REQUEST['h'], $ts);
	$res = db_query("SELECT user_id FROM user
                                WHERE user_id = $userUID AND
                                      password NOT IN ('" .
                                      implode("', '", $auth_ids) . "')");
	if ($valid and mysql_num_rows($res) == 1) {
                if (isset($_POST['newpass']) and isset($_POST['newpass1']) and
                    $_POST['newpass'] == $_POST['newpass1']) {
                        if (db_query("UPDATE user SET `password` = ".quote(md5($_POST['newpass']))."
                                         WHERE user_id = $userUID")) {
                                $tool_content = "<div class='success'>
                                                 <p>$langAccountResetSuccess1</p>
                                                 <p><a href='$urlAppend'>$langHome</a></p></div>";
                                $change_ok = true;
                        }
                } else {
                        $tool_content = "<p class='alert1'>$langPassTwo</p>";
                }
		if (!$change_ok) {
                        $tool_content = "
        <form method='post' action='$_SERVER[SCRIPT_NAME]'>
        <input type='hidden' name='ts' value='$ts'>
        <input type='hidden' name='u' value='$userUID'>
        <input type='hidden' name='h' value='".q($_REQUEST['h']).">
        <fieldset>
        <legend>$langPassword</legend>
        <table class='tbl'>
        <tr>
           <th>$langNewPass1</th>
           <td><input type='password' size='40' name='newpass' value=''></td>
        </tr>
        <tr>
           <th>$langNewPass2</th>
           <td><input type='password' size='40' name='newpass1' value=''></td>
        </tr>
        <tr>
           <th>&nbsp;</th>
           <td><input type='submit' name='submit' value='$langModify'></td>
        </tr>
        </table>
        </fieldset>
        </form>";
		}
	} else {
		$tool_content = "<div class='caution'><p>$langAccountResetInvalidLink</p>
                                 <p><a href='$urlAppend'>$langHome</a></p></td>";
	}
} elseif (isset($_POST['send_link'])) {

	$email = isset($_POST['email'])? mb_strtolower(trim($_POST['email'])): '';
	$userName = isset($_POST['userName'])? canonicalize_whitespace($_POST['userName']): '';
	/***** If valid e-mail address was entered, find user and send email *****/
	$res = db_query("SELECT user_id, nom, prenom, username, password, statut FROM user
                                WHERE email = " . quote($email) . " AND
                                      BINARY username = " . quote($userName));

        $found_editable_password = false;
	if (mysql_num_rows($res) == 1) {
		$text = $langPassResetIntro. $emailhelpdesk;
		$text .= "$langHowToResetTitle";
		while ($s = mysql_fetch_array($res, MYSQL_ASSOC)) {
			if (check_password_editable($s['password'])) {
                                $found_editable_password = true;
				//insert an md5 key to the db
				$new_pass = create_pass();
				//prepare instruction for password reset
				$text .= $langPassResetGoHere;
				$text .= $urlServer . "modules/auth/lostpass.php?do=go&u=$s[user_id]&h=".md5($new_pass);

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

} else {
	/***** Email address entry form *****/
	$tool_content .= "<div class='info'><p>$lang_pass_intro</p></div><br />";
	$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
          <legend>$langUserData</legend>
	  <table class='tbl' width='100%'>
	  <tr>
            <th width='100'>$lang_username:</th>
	    <td><input type='text' name='userName' size='40'></td>
          </tr>
	  <tr>
	    <th>$lang_email: </th>
	    <td><input type='text' name='email' size='40'></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td class='right'><input type='submit' name='send_link' value='$lang_pass_submit'></td>
          </tr>
	  </table>
        </fieldset>
	</form>";
}

draw($tool_content, 0);
