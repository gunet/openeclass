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
$require_valid_uid = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/log.class.php';

$toolName = $langMyProfile;
$pageName = $langChangePass;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);
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
        $('#password_form').keyup(function() {
            $('#result').html(checkStrength($('#password_form').val()))
        });
    });

/* ]]> */
</script>
hContent;

$passUrl = $urlServer . 'main/profile/password.php';
$passLocation = 'Location: ' . $passUrl;

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

$tool_content .= action_bar(array(
    array('title' => $langBack,
          'url' => 'display_profile.php',
          'icon' => 'fa-reply',
          'level' => 'primary-label')));

if (!isset($_POST['changePass'])) {
    $old_pass_error = Session::getError('old_pass');
    $old_pass = Session::has('old_pass') ? Session::get('old_pass') : '';
    $password_form_error = Session::getError('password_form');
    $password_form = Session::has('password_form') ? Session::get('password_form') : '';
    $password_form1_error = Session::getError('password_form1');
    $password_form1 = Session::has('password_form1') ? Session::get('password_form1') : '';
    $tool_content .= "<div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='$passUrl'>
    <fieldset>
    <div class='form-group".($old_pass_error ? " has-error" : "")."'>
      <label for='old_pass' class='col-sm-2 control-label'>$langOldPass: </label>
      <div class='col-sm-8'>
	    <input type='password' class='form-control' id='old_pass' name='old_pass' value='$old_pass' autocomplete='off'>
            <span class='help-block'>$old_pass_error</span>
      </div>
    </div>
    <div class='form-group".($password_form_error ? " has-error" : "")."'>
      <label for='password_form' class='col-sm-2 control-label'>$langNewPass1: </label>
      <div class='col-sm-8'>
	    <input type='password' class='form-control' id='password_form' name='password_form' value='$password_form' autocomplete='off'>
            <span class='help-block'>$password_form_error</span>
      </div>
      <div class='col-sm-2 text-center padding-thin'>
        <span id='result'></span>
      </div>
    </div>
    <div class='form-group".($password_form1_error ? " has-error" : "")."'>
      <label for='password_form1' class='col-sm-2 control-label'>$langNewPass2: </label>
      <div class='col-sm-8'>
        <input type='password' class='form-control' id='password_form1' name='password_form1' value='$password_form1' autocomplete='off'>
        <span class='help-block'>$password_form1_error</span>
      </div>
    </div>
    ".showSecondFactorChallenge()."
    <div class='form-group'>
      <div class='col-sm-offset-2 col-sm-8'>
         <input type='submit' class='btn btn-primary' name='submit' value='$langModify'>
         <a href='display_profile.php' class='btn btn-default'>$langCancel</a>
      </div>
    </div>
  </fieldset>
  ". generate_csrf_token_form_field() ."  
</form></div>";
}

draw($tool_content, 1, null, $head_content);
