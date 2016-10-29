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
use Hautelook\Phpass\PasswordHash;
$require_login = true;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/log.class.php';

$toolName = $langMyProfile;
$pageName = $langChangePass;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);
$navigation[] = array('url' => 'profile.php', 'name' => $langModifyProfile);

check_uid();


$data['passUrl'] = $urlServer . 'main/profile/password.php';
$data['passLocation'] = 'Location: ' . $data['passUrl'];

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('password_form', 'password_form1', 'old_pass'));
    $v->rule('equals', 'password_form', 'password_form1');
    $v->rule('lengthMin', 'password_form', get_config('min_password_len'));
    $v->labels(array(
        'old_pass' => "$langTheField $langOldPass",
        'password_form' => "$langTheField $langNewPass1",
        'password_form1' => "$langTheField $langNewPass2"
    ));
    if($v->validate()) { 
        // all checks ok. Change password!    
       $myrow = Database::get()->querySingle("SELECT password FROM user WHERE id= ?d", $_SESSION['uid']);

       $hasher = new PasswordHash(8, false);
       $new_pass = $hasher->HashPassword($_REQUEST['password_form']);

       if ($hasher->CheckPassword($_REQUEST['old_pass'], $myrow->password)) {
           Database::get()->query("UPDATE user SET password = ?s
                                    WHERE id = ?d", $new_pass, $_SESSION['uid']);
           Log::record(0, 0, LOG_PROFILE,
               array('uid' => $_SESSION['uid'], 'pass_change' => 1));
           Session::Messages($langPassChanged, 'alert-success');
           redirect_to_home_page('main/profile/display_profile.php');
           exit;
       } else {
           Session::Messages($langPassOldWrong);
           redirect_to_home_page('main/profile/password.php');
       }       
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('main/profile/password.php');
    } 

}

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
          'url' => 'display_profile.php',
          'icon' => 'fa-reply',
          'level' => 'primary-label')));

if (!isset($_POST['changePass'])) {
    $data['old_pass_error'] = Session::getError('old_pass');
    $data['old_pass'] = Session::has('old_pass') ? Session::get('old_pass') : '';
    $data['password_form_error'] = Session::getError('password_form');
    $data['password_form'] = Session::has('password_form') ? Session::get('password_form') : '';
    $data['password_form1_error'] = Session::getError('password_form1');
    $data['password_form1'] = Session::has('password_form1') ? Session::get('password_form1') : '';
    $tool_content .= "";
}

$data['menuTypeID'] = 1;
view('main.profile.password', $data);
