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
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @file password.php 
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
$navigation[] = array('url' => 'profile.php', 'name' => $langModifyProfile);

check_uid();

// javascript
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
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

$passurl = $urlSecure . 'main/profile/password.php';

if (isset($_POST['submit'])) {
    if (empty($_POST['password_form']) or empty($_POST['password_form1']) or empty($_POST['old_pass'])) {
        header("Location:" . $passurl . "?msg=2");
        exit();
    }
    if (count($error_messages = acceptable_password($_POST['password_form'], $_POST['password_form1'])) > 0) {        
        header("Location:" . $passurl . "?msg=1");
        exit();
    }

    //all checks ok. Change password!    
    $myrow = Database::get()->querySingle("SELECT password FROM user WHERE id= ?d", $_SESSION['uid']);        
    
    $hasher = new PasswordHash(8, false);
    $new_pass = $hasher->HashPassword($_REQUEST['password_form']);

    if ($hasher->CheckPassword($_REQUEST['old_pass'], $myrow->password)) {
        Database::get()->query("UPDATE user SET password = ?s
                                 WHERE id = ?d", $new_pass, $_SESSION['uid']);        
        Log::record(0, 0, LOG_PROFILE, array('uid' => $_SESSION['uid'],
                                             'pass_change' => 1));
        header("Location:" . $passurl . "?msg=4");
        exit();
    } else {
        header("Location:" . $passurl . "?msg=3");
        exit();
    }
}

//Show message if exists
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    switch ($msg) {

        case 1: { // Passwords not acceptable
                $message = $langPassTwo;               
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
	   <td><input type='password' size='40' name='old_pass' value='' autocomplete='off'></td>
	</tr>
	<tr>
	   <th>$langNewPass1</th>
	   <td><input type='password' size='40' name='password_form' id='password' value='' autocomplete='off'/>&nbsp;<span id='result'></span></td>
	</tr>
	<tr>
	   <th>$langNewPass2</th>
	   <td><input type='password' size='40' name='password_form1' value='' autocomplete='off'></td>
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
