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

/**
 * Index
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Password change component
 *
 */
$require_login = true;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/phpass/PasswordHash.php';
require_once 'include/log.php';

$nameTools = $langChangePass;
$navigation[]= array('url' => 'profile.php', 'name'=> $langModifyProfile);

// javascript
load_js('jquery');
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
    $head_content .= "pwStrengthTooShort: '". js_escape($langPwStrengthTooShort) ."', ";
    $head_content .= "pwStrengthWeak: '". js_escape($langPwStrengthWeak) ."', ";
    $head_content .= "pwStrengthGood: '". js_escape($langPwStrengthGood) ."', ";
    $head_content .= "pwStrengthStrong: '". js_escape($langPwStrengthStrong) ."'";
$head_content .= <<<hContent
    };
                        
    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

check_uid();

$passurl = $urlSecure.'modules/profile/password.php';

if (isset($_POST['submit'])) {
	if (empty($_POST['password_form']) or empty($_POST['password_form1']) or empty($_POST['old_pass'])) {
		header("Location:". $passurl."?msg=2");
		exit();
	}
	if (count($error_messages = acceptable_password($_POST['password_form'], $_POST['password_form1'])) > 0) {
                $_SESSION['password_error_text'] = "<ul><li>" .
                                implode("</li>\n<li>", $error_messages) .
                                "</li></ul>";
		header("Location:". $passurl."?msg=1");
		exit();
	}

	//all checks ok. Change password!
	$sql = "SELECT `password` FROM `user` WHERE `user_id`=".$_SESSION["uid"]." ";
	$result = db_query($sql, $mysqlMainDb);
	$myrow = mysql_fetch_array($result);

	$hasher = new PasswordHash(8, false);
	$new_pass = $hasher->HashPassword($_REQUEST['password_form']);

	if ($hasher->CheckPassword($_REQUEST['old_pass'], $myrow['password'])) {
                db_query("UPDATE `user` SET `password` = '$new_pass'
                                 WHERE `user_id` = ".$_SESSION["uid"]);
                Log::record(0, 0, LOG_PROFILE, array('uid' => $_SESSION['uid'],
                                                     'pass_change' => 1));
		header("Location:". $passurl."?msg=4");
		exit();
	} else {
		header("Location:". $passurl."?msg=3");
		exit();
	}

}

//Show message if exists
if(isset($_GET['msg'])) {
	$msg = $_GET['msg'];
	switch ($msg){

		case 1: { // Passwords not acceptable
                        $message = isset($_SESSION['password_error_text'])?
                                   $_SESSION['password_error_text']: '';
                        unset($_SESSION['password_error_text']);
			$urlText = '';
			$type = 'alert1';
			break;
		}

		case 2: { // admin tools
			$message = $langFieldsMissing;
			$urlText = '';
			$type = 'alert1';
			break;
		}
		case 3: { // wrong old password entered
			$message = $langPassOldWrong;
			$urlText = '';
			$type = 'caution';
			break;
		}

		case 4: { // password successfully changed
			$message = $langPassChanged;
			$urlText = $langHome;
			$type = 'success';
			break;
		}
	}
	$tool_content .= "<div class='$type'>$message<br /><a href='$urlServer'>$urlText</a></div>";
}

if (!isset($_POST['changePass'])) {
	$tool_content .= "
	<form method='post' action='$passurl'>
        <fieldset>
        <legend>$langPassword</legend>
	<table class='tbl'>
	<tr>
	   <th>$langOldPass</th>
	   <td><input type='password' size='40' name='old_pass' value=''></td>
	</tr>
	<tr>
	   <th>$langNewPass1</th>
	   <td><input type='password' size='40' name='password_form' id='password' value=''/>&nbsp;<span id='result'></span></td>
	</tr>
	<tr>
	   <th>$langNewPass2</th>
	   <td><input type='password' size='40' name='password_form1' value=''></td>
	</tr>
	<tr>
	   <th>&nbsp;</th>
	   <td><input type='submit' name='submit' value='$langModify'></td>
	</tr>
	</table>
        </fieldset>
        </form>";
}

draw($tool_content, 1, null, $head_content);
