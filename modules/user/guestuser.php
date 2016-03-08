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
use Hautelook\Phpass\PasswordHash;

$require_current_course = true;
$require_course_admin = true;
$require_help = TRUE;
$helpTopic = 'Guest';

require_once '../../include/baseTheme.php';

if (get_config('course_guest') == 'off') {
    redirect_to_home_page('modules/user/?course=' . $course_code);
}

$toolName = $langUsers;
$pageName = $langAddGuest;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

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

$default_guest_username = $langGuestUserName . $course_code;

$tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $password = $_POST['guestpassword'];
    createguest($default_guest_username, $course_id, $password);
    Session::Messages($langGuestSuccess, 'alert-success');
    if ($password === '') {
        Session::Messages($langGuestWarnEmptyPassword, 'alert-warning');
    }
    redirect_to_home_page('modules/user/');
} else {
    $guest_info = guestinfo($course_id);
    if ($guest_info) {
        if ($guest_info->password === '') {
            $tool_content .= "<div class='alert alert-warning'>$langGuestWarnEmptyPassword</div>";
        } else {
            $tool_content .= "<div class='alert alert-info'>$langGuestExist</div>";
        }
        $submit_label = $langModify;
    } else {
        $guest_info = new stdClass();
        $guest_info->givenname = $langGuestName;
        $guest_info->surname = $langGuestSurname;
        $guest_info->username = $default_guest_username;
        $submit_label = $langAdd;
    }
    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->givenname)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->surname)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langUsername:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->username)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label for='password' class='col-sm-2 control-label'>$langPass:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='guestpassword' value='' id='password' autocomplete='off' placeholder='$langAskGuest'>
                <span id='result'></span>
            </div>
        </div>
        <div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$submit_label'>
          <a href='index.php?course=$course_code' class='btn btn-default'>$langCancel</a>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."  
        </form>
        </div>";
}
draw($tool_content, 2, null, $head_content);

/**
 * @brief create guest account or update password if it already exists
 * @global type $langGuestName
 * @global type $langGuestSurname
 * @param type $username
 * @param type $course_id
 * @param type $password
 * @return none
 */
function createguest($username, $course_id, $password) {
    global $langGuestName, $langGuestSurname, $langGuestFail;

    $hasher = new PasswordHash(8, false);
    if ($password !== '') {
        $password = $hasher->HashPassword($password);
    }

    $q = Database::get()->querySingle("SELECT user_id from course_user WHERE status=" . USER_GUEST . " AND course_id = $course_id");
    if ($q) {
        $guest_id = $q->user_id;
        Database::get()->query("UPDATE user SET password = ?s WHERE id = ?d", $password, $guest_id);
    } else {
        $q = Database::get()->query("INSERT INTO user (surname, givenname, username, password, status, registered_at, expires_at, whitelist, description)
                                        VALUES (?s, ?s, ?s, ?s, " . USER_GUEST . ", ".DBHelper::timeAfter().", ".DBHelper::timeAfter(get_config('account_duration')).", '','')",
                                            $langGuestSurname, $langGuestName, $username, $password);
        $guest_id = $q->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $guest_id);
    }
    Database::get()->query("INSERT IGNORE INTO course_user (course_id, user_id, status, reg_date)
                  VALUES (?d, ?d, " . USER_GUEST . ", ".DBHelper::timeAfter().")", $course_id, $guest_id);
    return;
}

/**
 * @brief check if guest account exists and return account information
 * @param type $course_id
 * @return boolean
 */
function guestinfo($course_id) {

    $q = Database::get()->querySingle("SELECT surname, givenname, username, password FROM user, course_user
                       WHERE user.id = course_user.user_id AND
                             course_user.status = " . USER_GUEST . " AND
                             course_user.course_id = ?d", $course_id);
    if (!$q) {
        return false;
    } else {
        return $q;
    }
}
