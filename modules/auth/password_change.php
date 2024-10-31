<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_login = true;
$require_valid_uid = true;
$force_password_excluded = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langChangePass;

if (isset($_POST['submit'])) {

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('password_form', 'password_form1'));
    $v->rule('equals', 'password_form', 'password_form1');
    $v->rule('lengthMin', 'password_form', get_config('min_password_len'));
    $v->labels(array(
        'password_form' => "$langTheField $langNewPass1",
        'password_form1' => "$langTheField $langNewPass2"
    ));

    if ($v->validate()) {
        // all checks ok. Change password!
        $myrow = Database::get()->querySingle("SELECT password FROM user WHERE id= ?d", $_SESSION['uid']);

        $new_pass = password_hash($_REQUEST['password_form'], PASSWORD_DEFAULT);
        $options = json_encode(['force_password_change' => 0]);
        Database::get()->query("UPDATE user SET password = ?s, options = ?s WHERE id = ?d", $new_pass, $options, $_SESSION['uid']);
        Log::record(0, 0, LOG_PROFILE, array('uid' => $_SESSION['uid'], 'pass_change' => 1));
        if (isset($_SESSION['force_password_change'])) {
            unset($_SESSION['force_password_change']);
        }

        Session::flash('message', $langPassChanged);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('main/portfolio.php');
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/auth/password_change.php');
    }
}

if (!isset($_POST['changePass'])) {
    $data['password_form_error'] = Session::getError('password_form');
    $data['password_form'] = Session::has('password_form') ? Session::get('password_form') : '';
    $data['password_form1_error'] = Session::getError('password_form1');
    $data['password_form1'] = Session::has('password_form1') ? Session::get('password_form1') : '';
}

view('modules.auth.password_change', $data);


