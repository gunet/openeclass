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
use Hautelook\Phpass\PasswordHash;

$require_login = true;
$require_admin = TRUE;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

include '../../include/baseTheme.php';

check_uid();

if (isset($_POST['changePass'])) {
    $userid = intval(getDirectReference($_POST['userid']));

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    
    if (empty($_POST['password_form']) || empty($_POST['password_form1'])) {
        Session::Messages($langFieldsMissing);
        redirect_to_home_page("modules/admin/password.php?userid=" . urlencode(getIndirectReference($userid)));
    }
    if ($_POST['password_form1'] !== $_POST['password_form']) {
        Session::Messages($langPassTwo);
        redirect_to_home_page("modules/admin/password.php?userid=" . urlencode(getIndirectReference($userid)));        
    }
    // All checks ok. Change password!
    $hasher = new PasswordHash(8, false);
    $new_pass = $hasher->HashPassword($_POST['password_form']);
    Database::get()->query("UPDATE `user` SET `password` = ?s WHERE `id` = ?d", $new_pass, $userid);
    Session::Messages($langPassChanged, 'alert-sucess');
    redirect_to_home_page("modules/admin/edituser.php?u=" . urlencode($userid));    
}

if (!isset($_GET['userid'])) {
    header("Location: {$urlServer}modules/admin/");
    exit;
}

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

$data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "{$urlServer}modules/admin/edituser.php?u=" . urlencode(getDirectReference($_REQUEST['userid'])),
                'icon' => 'fa-reply',
                'level' => 'primary-label')
                ));
$data['menuTypeID'] = 3;
view('admin.users.password', $data);                