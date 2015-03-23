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
 * @file password.php
 * @brief change user password
 */

$require_login = true;
$require_admin = TRUE;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

include '../../include/baseTheme.php';
require_once 'include/phpass/PasswordHash.php';

$toolName = $langChangePass;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'edituser.php', 'name' => $langEditUser);

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

check_uid();

$passurl = $urlServer . 'modules/admin/password.php';
$tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?userid=$_REQUEST[userid]",
                'icon' => 'fa-reply',
                'level' => 'primary')
                ));

if (!isset($_POST['changePass'])) {       
    if (!isset($_GET['userid'])) {
        header("Location: {$urlServer}modules/admin/");
        exit;
    }
    $tool_content .= "<div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='$passurl'>
    <fieldset>      
      <input type='hidden' name='userid' value='$_GET[userid]' />
      <div class='form-group'>
      <label class='col-sm-3 control-label'>$langNewPass1</label>
        <div class='col-sm-9'>
            <input type='password' size='40' name='password_form' value='' id='password' autocomplete='off' />&nbsp;<span id='result'></span>
        </div>
      </div>
      <div class='form-group'>
        <label class='col-sm-3 control-label'>$langNewPass2</label>
        <div class='col-sm-9'>
            <input type='password' size='40' name='password_form1' value='' autocomplete='off' />
        </div>
      </div>
      <div class='col-sm-offset-3 col-sm-9'>
        <input class='btn btn-primary' type='submit' name='changePass' value='$langModify' />
      </div>      
    </fieldset>
    </form>
    </div>";
} else {
    $userid = intval($_POST['userid']);
    if (empty($_POST['password_form']) || empty($_POST['password_form1'])) {
        $tool_content .= mes($langFieldsMissing, '', 'warning');
        draw($tool_content, 3);
        exit();
    }
    if ($_POST['password_form1'] !== $_POST['password_form']) {
        $tool_content .= mes($langPassTwo, '', 'warning');
        draw($tool_content, 3);
        exit();
    }
    // All checks ok. Change password!
    $hasher = new PasswordHash(8, false);
    $new_pass = $hasher->HashPassword($_POST['password_form']);
    Database::get()->query("UPDATE `user` SET `password` = ?s WHERE `id` = ?d", $new_pass, $userid);
    $tool_content .= mes($langPassChanged, $langHome, 'info');
    draw($tool_content, 3);
    exit();
}

draw($tool_content, 3, null, $head_content);


/**
 * @brief display message
 * @param type $message
 * @param type $urlText
 * @param type $type
 * @return type
 */
function mes($message, $urlText, $type) {    
    $str = "<div class='alert alert-$type'>$message</div>";
    return $str;
}
