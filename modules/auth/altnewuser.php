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

$data['action_bar'] = action_bar(
                                [[
                                    'title' => $langBack,
                                    'url' => 'registration.php',
                                    'icon' => 'fa-reply',
                                    'level' => 'primary-label',
                                    'button-class' => 'btn-default'
                                ]], false);

$data['user_registration'] = get_config('user_registration');
$data['alt_auth_prof_reg'] = get_config('alt_auth_prof_reg');
$data['alt_auth_stud_reg'] = get_config('alt_auth_stud_reg'); //user registration via alternative auth methods

if (isset($_REQUEST['auth'])) {
    $auth = intval($_REQUEST['auth']);
    $_SESSION['u_tmp'] = $auth;
} else  {
    $auth = 0;
    $auth = $_SESSION['u_tmp'];
}

$data['auth'] = $auth;
unset($_SESSION['was_validated']);

$data['authmethods'] = get_auth_active_methods();
$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);
$data['settings'] = get_auth_settings($auth);
$data['auth_instructions'] = q($settings['auth_instructions']);

if (($auth != 7) and ($auth != 6)) {
    $data['set_uname'] = isset($_GET['uname']) ? (" value='" . q(canonicalize_whitespace($_GET['uname'])) . "'") : '';
    $data['form_buttons'] = form_buttons(array(
                                array(
                                    'text' => q($langSubmit),
                                    'name' => 'is_submit',
                                    'value'=> q($langSubmit)
                                )
                            ));
} else {
    $data['form_buttons'] = form_buttons(array(
        array(
            'text' => q($langCheck),
            'name' => 'is_submit',
            'value'=> q($langCheck)
        )
    ));
}

if (!empty($msg)) {
    $pageName = "$langConfirmUser ($msg)";
}

if (isset($_GET['p']) and $_GET['p']) {
    $_SESSION['u_prof'] = 1;
} else {
    $_SESSION['u_prof'] = 0;
}
   
unset($uid);

$data['menuTypeID'] = 0;
view('modules.auth.altnewuser', $data);