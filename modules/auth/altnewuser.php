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
 * @brief display form for authenticating user via alternate methods
 * @file altnewuser.php
 */


include '../../include/baseTheme.php';
include 'auth.inc.php';

$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

$user_registration = get_config('user_registration');
$alt_auth_prof_reg = get_config('alt_auth_prof_reg');
$alt_auth_stud_reg = get_config('alt_auth_stud_reg'); //user registration via alternative auth methods

if (!$user_registration) {
    $tool_content .= "<div class='alert alert-info'>$langCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

if (isset($_REQUEST['auth'])) {
    $auth = intval($_REQUEST['auth']);
    $_SESSION['u_tmp'] = $auth;
}
if (!isset($_REQUEST['auth'])) {
    $auth = 0;
    $auth = $_SESSION['u_tmp'];
}

unset($_SESSION['was_validated']);

$authmethods = get_auth_active_methods();
$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);

if (!empty($msg)) {
    $pageName = "$langConfirmUser ($msg)";
}

if (isset($_GET['p']) and $_GET['p']) {
    $_SESSION['u_prof'] = 1;
} else {
    $_SESSION['u_prof'] = 0;
}

if (!in_array($auth, $authmethods)) {
    $tool_content .= "<div class='alert alert-danger'>$langCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

if (!$_SESSION['u_prof'] and !$alt_auth_stud_reg) {
    $tool_content .= "<div class='alert alert-danger'>$langCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

if ($_SESSION['u_prof'] and !$alt_auth_prof_reg) {
    $tool_content .= "<div class='alert alert-danger'>$langCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}
$tool_content .= "<div class='form-wrapper'>";
$tool_content .= "<form class='form-horizontal' role='form' method='post' action='altsearch.php'>";
$tool_content .= "<fieldset>" . q($settings['auth_instructions']);

if (isset($_SESSION['prof']) and $_SESSION['prof']) {
    $tool_content .= "<input type='hidden' name='p' value='1'>";
}

if (($auth != 7) and ($auth != 6)) {
    $set_uname = isset($_GET['uname']) ? (" value='" . q(canonicalize_whitespace($_GET['uname'])) . "'") : '';
    $tool_content .= "<div class='form-group'>
                        <label for='UserName' class='col-sm-2 control-label'>$langUsername</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' size='30' maxlength='30' name='uname' autocomplete='off' $set_uname placeholder='$langUserNotice'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='Pass' class='col-sm-2 control-label'>$langPass</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='password' size='30' maxlength='30' name='passwd' autocomplete='off' placeholder='$langPass'>
                        </div>
                    </div>";
}

$tool_content .= "<input type='hidden' name='auth' value='$auth'>";

$tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'>";

if (($auth != 7) and ($auth != 6)) {
    $tool_content .= form_buttons(array(
                                array(
                                    'text' => q($langSubmit),
                                    'name' => 'is_submit',
                                    'value'=> q($langSubmit)
                                )
                            ))
                            ;
} else {
    $tool_content .= form_buttons(array(
        array(
            'text' => q($langCheck),
            'name' => 'is_submit',
            'value'=> q($langCheck)
        )
    ))
    ;
}

$tool_content .= "</div></div>";
$tool_content .= "</fieldset></form></div>";

unset($uid);
draw($tool_content, 0);
