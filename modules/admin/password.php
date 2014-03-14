<?php
/* ========================================================================
 * Open eClass 2.6
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
 * @file password.php
 * @brief change user password from admin tool
 */

$require_usermanage_user = TRUE;
require_once '../../include/phpass/PasswordHash.php';
include '../../include/baseTheme.php';

if (isset($_REQUEST['userid'])) {
        $userid = intval($_REQUEST['userid']);        
        if (check_admin($userid) and (!(isset($_SESSION['is_admin'])))) {
                header('Location: ' . $urlServer);
        }
} else {   
        header('Location: ' . $urlServer);
}

$nameTools = $langChangePass;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array ('url' => 'edituser.php', 'name'=> $langEditUser);

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

if (!isset($urlSecure)) {
	$passurl = $urlServer.'modules/admin/password.php';
} else {
	$passurl = $urlSecure.'modules/admin/password.php';
}

if (!isset($_POST['changePass'])) {        
	$tool_content .= "
        <form method='post' action='$passurl'>
        <fieldset>
          <legend>$lang_remind_pass</legend>
          <input type='hidden' name='userid' value='$_GET[userid]' />
          <table class='tbl' width='100%'>
          <tr>
            <th class='left' width='160'>$langNewPass1:</th>
            <td><input type='password' size='40' name='password_form' value='' id='password' autocomplete='off' />&nbsp;<span id='result'></span></td>
          </tr>
          <tr>
            <th class='left'>$langNewPass2:</th>
            <td><input type='password' size='40' name='password_form1' value='' autocomplete='off' /></td>
          </tr>
          <tr>
            <th class='left'>&nbsp;</th>
            <td class='right'><input type='submit' name='changePass' value='".q($langModify)."' /></td>
          </tr>
          </table>
        </fieldset>
        </form>";
} else {
	if (empty($_POST['password_form']) || empty($_POST['password_form1'])) {
		$tool_content .= mes($langFieldsMissing, 'caution');
		draw($tool_content, 3);
		exit();
	}
	if ($_POST['password_form1'] !== $_POST['password_form']) {
		$tool_content .= mes($langPassTwo, 'caution');
		draw($tool_content, 3);
		exit();
	}
	// All checks ok. Change password!
	$hasher = new PasswordHash(8, false);
	$new_pass = $hasher->HashPassword($_POST['password_form']);
	$sql = "UPDATE `user` SET `password` = '$new_pass' WHERE `user_id` = $userid";
	db_query($sql, $mysqlMainDb);
        
	$tool_content .= mes($langPassChanged, 'success');
	draw($tool_content, 3);
	exit();
}

draw($tool_content, 3, null, $head_content);


/**
 * display message
 * @global type $urlServer
 * @global type $langBack
 * @global type $userid
 * @param type $message
 * @param type $type
 * @return type
 */
function mes($message, $type) {
    
	global $urlServer, $langBack, $userid;

        if ($type == 'success') {
            $str = "<p class='$type'>$message</p><br /><p><a href='${urlServer}modules/admin/edituser.php?u=$userid'>$langBack</a></p>";
        } else {
            $str = "<p class='$type'>$message</p><br /><p><a href='$_SERVER[SCRIPT_NAME]?userid=$userid'>$langBack</a></p>";
        }
	return $str;
}
