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


$require_help = true;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/custom_profile_fields_functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/pwgen.inc.php';
$require_valid_uid = TRUE;

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/log.php';

check_uid();
check_guest();

$toolName = $langMyProfile;
$pageName = $langModifyProfile;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

$tree = new Hierarchy();
$userObj = new User();
$image_path = $webDir . '/courses/userimg/' . $_SESSION['uid'];

load_js('jstree');
load_js('tools.js');
$head_content .= "<script type='text/javascript'>
var lang = {
        addPicture: '" . js_escape($langAddPicture) . "',
        confirmDelete: '" . js_escape($langConfirmDelete) . "'};
$(profile_init);</script>";

$myrow = Database::get()->querySingle("SELECT surname, givenname, username, email, am, phone,
                                            lang, status, has_icon, description,
                                            email_public, phone_public, am_public, password
                                        FROM user WHERE id = ?d", $uid);

$password = $myrow->password;
$auth = array_search($password, $auth_ids);
if (!$auth) {
    $auth = 1;
}
$auth_text = get_auth_info($auth);

if ($auth != 1) {
    $allow_username_change = false;
    $allow_password_change = false;
} else {
    $allow_username_change = !get_config('block_username_change');
    $allow_password_change = true;
}

if (in_array($password, array('shibboleth', 'cas', 'ldap'))) {
    $allow_name_change = false;
} else {
    $allow_name_change = true;
}


// Handle AJAX profile image delete
if (isset($_POST['delimage'])) {
    @unlink($image_path . '_' . IMAGESIZE_LARGE . '.jpg');
    @unlink($image_path . '_' . IMAGESIZE_SMALL . '.jpg');
    Database::get()->query("UPDATE user SET has_icon = 0 WHERE id = ?d", $uid);
    Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                         'deleteimage' => 1));
    exit;
}

if (isset($_POST['submit'])) {
    // First process language changes
    if (!file_exists($webDir . '/courses/userimg/')) {
        mkdir($webDir . '/courses/userimg/', 0775);
        touch($webDir."courses/userimg/index.php");
    }
    $subscribe = (isset($_POST['subscribe']) and $_POST['subscribe'] == 'yes') ? '1' : '0';
    $old_language = $language;
    $langcode = $language = $_SESSION['langswitch'] = $_POST['userLanguage'];
    Database::get()->query("UPDATE user SET lang = ?s WHERE id = ?d", $langcode, $uid);
    
    $var_arr = array('am_form' => get_config('am_required') and $myrow->status != 1,
                    'desc_form' => false,
                    'phone_form' => false,
                    'email_form' => get_config('email_required'),
                    'surname_form' => !$is_admin,
                    'givenname_form' => true,
                    'username_form' => true,
                    'email_public' => false,
                    'phone_public' => false,
                    'am_public' => false);
    
    //add custom profile fields required variables
    augment_registered_posted_variables_arr($var_arr);
    
    $all_ok = register_posted_variables($var_arr, 'all');

    $departments = null;
    if (!get_config('restrict_owndep')) {
        if (!isset($_POST['department']) and !$is_admin) {
            $all_ok = false;
        } else {
            $departments = $_POST['department'];
        }
    }
    $email_public = valid_access($email_public);
    $phone_public = valid_access($phone_public);
    $am_public = valid_access($am_public);

    // upload user picture
    if (isset($_FILES['userimage']) && is_uploaded_file($_FILES['userimage']['tmp_name'])) {

        validateUploadedFile($_FILES['userimage']['name'], 1);

        $type = $_FILES['userimage']['type'];
        $image_file = $_FILES['userimage']['tmp_name'];

        if (!copy_resized_image($image_file, $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE, $image_path . '_' . IMAGESIZE_LARGE . '.jpg')) {
            Session::Messages($langInvalidPicture);
            redirect_to_home_page("main/profile/profile.php");
        }
        if (!copy_resized_image($image_file, $type, IMAGESIZE_SMALL, IMAGESIZE_SMALL, $image_path . '_' . IMAGESIZE_SMALL . '.jpg')) {
            Session::Messages($langInvalidPicture);
            redirect_to_home_page("main/profile/profile.php");
        }
        Database::get()->query("UPDATE user SET has_icon = 1 WHERE id = ?d", $_SESSION['uid']);
        Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                             'addimage' => 1,
                                             'imagetype' => $type));
    }

    // check if email is valid
    if ((get_config('email_required') or get_config('email_verification_required')) and !email_seems_valid($email_form)) {

        Session::Messages($langEmailWrong);
        redirect_to_home_page("main/profile/profile.php");
    }

    // check if there are empty fields
    if (!$all_ok) {
        Session::Messages($langFieldsMissing);
        redirect_to_home_page("main/profile/profile.php");
    }

    if (!$allow_username_change) {
        $username_form = $_SESSION['uname'];
    }

    $username_form = canonicalize_whitespace($username_form);
        // check if username exists
    if ($username_form != $_SESSION['uname']) {
        $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $username_form);
        if ($username_check) {
            Session::Messages($langUserFree);
            redirect_to_home_page("main/profile/profile.php");
        }
    }
    
    //check for validation errors in custom profile fields
    $cpf_check = cpf_validate_format();
    if ($cpf_check[0] === false) {
        $cpf_error_str = '';
        unset($cpf_check[0]);
        foreach ($cpf_check as $cpf_error) {
            $cpf_error_str .= $cpf_error;
        }
        Session::Messages($cpf_error_str);
        redirect_to_home_page("main/profile/profile.php");
    }

    // TODO: Allow admin to configure allowed username format
    if (!empty($email_form) && ($email_form != $_SESSION['email']) && get_config('email_verification_required')) {
        $verified_mail_sql = ", verified_mail = " . EMAIL_UNVERIFIED;
    } else {
        $verified_mail_sql = '';
    }
    // everything is ok
    $email_form = mb_strtolower(trim($email_form));

    $q = Database::get()->query("UPDATE user SET surname = ?s,
                             givenname = ?s,
                             username = ?s,
                             email = ?s,
                             am = ?s,
                             phone = ?s,
                             description = ?s,
                             email_public = ?s,
                             phone_public = ?s,
                             receive_mail = ?d,
                             am_public = ?d
                             $verified_mail_sql
                         WHERE id = ?d",
                            $surname_form, $givenname_form, $username_form, $email_form, $am_form, $phone_form, $desc_form, $email_public, $phone_public, $subscribe, $am_public, $uid);
        
    //fill custom profile fields
    process_profile_fields_data(array('uid' => $uid, 'origin' => 'edit_profile'));
    
        if ($q->affectedRows > 0 or isset($departments)) {
            $userObj->refresh($uid, $departments);
            Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                                 'modifyprofile' => 1,
                                                 'username' => $username_form,
                                                 'email' => $email_form,
                                                 'am' => $am_form));
            $_SESSION['uname'] = $username_form;
            $_SESSION['surname'] = $surname_form;
            $_SESSION['givenname'] = $givenname_form;
            $_SESSION['email'] = $email_form;
            Session::Messages($langProfileReg, 'alert-success');
            redirect_to_home_page("main/profile/display_profile.php");
        }
    if ($old_language != $language) {
        Session::Messages($langProfileReg, 'alert-success');
        redirect_to_home_page("main/profile/display_profile.php");
    }
}

$surname_form = q($myrow->surname);
$givenname_form = q($myrow->givenname);
$username_form = q($myrow->username);
$email_form = q($myrow->email);
$am_form = q($myrow->am);
$phone_form = q($myrow->phone);
$desc_form = $myrow->description;
$userLang = $myrow->lang;
$icon = $myrow->has_icon;

$sec = $urlServer . 'main/profile/profile.php';
$passurl = $urlServer . 'main/profile/password.php';

$tool_content .=
        action_bar(array(
            array('title' => $langBack,
                'url' => "display_profile.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        $tool_content .=
            "<div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data' action='$sec' onsubmit='return validateNodePickerForm();'>
                <fieldset>
                    <div class='form-group'>
                    <label for='givenname_form' class='col-sm-2 control-label'>$langName:</label>
                        <div class='col-sm-10'>";

if ($allow_name_change) {
    $tool_content .= "<input type='text' class='form-control' name='givenname_form' id='givenname_form' value='$givenname_form'>";
} else {
    $tool_content .= "<label>$givenname_form</label>
            <input type='hidden' name='givenname_form' value='$givenname_form' />";
}

$tool_content .= "</div></div>";
$tool_content .= "<div class='form-group'><label for='surname_form' class='col-sm-2 control-label'>$langSurname:</label>";
$tool_content .= "<div class='col-sm-10'>";
if ($allow_name_change) {
    $tool_content .= "<input type='text' class='form-control' name='surname_form' id='surname_form' value='$surname_form'>";
} else {
    $tool_content .= "<label>" . $surname_form . "</label><input type='hidden' name='surname_form' value='$surname_form' />";
}
$tool_content .= "</div></div>";
$tool_content .= "<div class='form-group'><label for='username_form' class='col-sm-2 control-label'>$langUsername:</label>";
$tool_content .= "<div class='col-sm-10'>";
if ($allow_username_change) {
    $tool_content .= "<input class='form-control' class='form-control' type='text' name='username_form' id='username_form' value='$username_form' />";
} else {
    // means that it is external auth method, so the user cannot change this password
    $tool_content .= "<label>$username_form</label> [$auth_text]
            <input type='hidden' name='username_form' value='$username_form' />";
}
$tool_content .= "</div></div>";

$access_options = array(ACCESS_PRIVATE => $langProfileInfoPrivate,
                        ACCESS_PROFS => $langProfileInfoProfs,
                        ACCESS_USERS => $langProfileInfoUsers);

$tool_content .= "<div class='form-group'>
                    <label for='email_form' class='col-sm-2 control-label'>$langEmail:</label>
                    <div class='col-sm-5'>
                        <input class='form-control' type='text' name='email_form' id='email_form' value='$email_form'>
                    </div>
                    <div class='col-sm-5'>
                        " . selection($access_options, 'email_public', $myrow->email_public, "class='form-control'") . "
                    </div>
                </div>
                <div class='form-group'>
                    <label for='am_form' class='col-sm-2 control-label'>$langAm:</label>
                    <div class='col-sm-5'>
                        <input type='text' class='form-control' name='am_form' id='am_form' value='$am_form'>
                    </div>
                    <div class='col-sm-5'>
                        " . selection($access_options, 'am_public', $myrow->am_public, "class='form-control'") . "
                    </div>
                </div>
                <div class='form-group'>
                    <label for='phone_form' class='col-sm-2 control-label'>$langPhone</label>
                    <div class='col-sm-5'>
                        <input type='text' class='form-control' name='phone_form' id='phone_form' value='$phone_form'>
                    </div>
                    <div class='col-sm-5'>
                        " . selection($access_options, 'phone_public', $myrow->phone_public, "class='form-control'") . "
                    </div>
                </div>";

if (get_user_email_notification_from_courses($uid)) {
    $selectedyes = 'checked';
    $selectedno = '';
} else {
    $selectedyes = '';
    $selectedno = 'checked';
}
$tool_content .= "<div class='form-group'>
                <label for='emailfromcourses' class='col-sm-2 control-label'>$langEmailFromCourses:</label>
                  <div class='col-sm-10'>
                  <div class='radio'>
                    <label>
                        <input type='radio' name='subscribe' value='yes' $selectedyes />$langYes
                    </label>
                   </div>
                   <div class='radio'>
                    <label>
                        <input type='radio' name='subscribe' value='no' $selectedno />$langNo
                    </label>
                   </div>
                  </div>
                </div>";

if (get_config('email_verification_required')) {
    $user_email_status = get_mail_ver_status($uid);
    $messageClass = '';
    switch ($user_email_status) {
        case EMAIL_VERIFICATION_REQUIRED:
        case EMAIL_UNVERIFIED:
            $messageClass = ' alert alert-warning';
            $link = "<a href = '{$urlAppend}modules/auth/mail_verify_change.php?from_profile=true'>$langHere</a>.";
            $message = "$langMailNotVerified $link";
            break;
        case EMAIL_VERIFIED:
            $message = icon('fa-check', $langMailVerificationYesU);
            break;
        default:
            break;
    }
    $tool_content .= "<div class='form-group$messageClass'>
        <label class='col-sm-2 control-label'>$langVerifiedMail</label>
        <div class='col-sm-10 form-control-static'>$message</div>
      </div>";
}

if (!get_config('restrict_owndep')) {
    $tool_content .= "<div class='form-group'><label for='faculty' class='col-sm-2 control-label'>$langFaculty:</label>";
    $tool_content .= "<div class='col-sm-10'>";
    list($js, $html) = $tree->buildUserNodePicker(array('defaults' => $userObj->getDepartmentIds($uid)));
    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</div></div>";
}

$tool_content .= "<div class='form-group'><label for='language' class='col-sm-2 control-label'>$langLanguage:</label>
                      <div class='col-sm-10'>" . lang_select_options('userLanguage', "class='form-control'") . "</div>
                  </div>";

if ($icon) {
    $message_pic = $langReplacePicture;
    $picture = profile_image($uid, IMAGESIZE_SMALL) . "&nbsp;&nbsp;";
    $delete = '&nbsp;' . icon('fa-times', $langDelete, '#', 'id="delete"') . '&nbsp;';
} else {
    $picture = $delete = '';
    $message_pic = $langAddPicture;
}
enableCheckFileSize();
$tool_content .= "<div class='form-group'>
        <label for='picture' class='col-sm-2 control-label'>$message_pic</label>
            <div class='col-sm-10'><span>$picture$delete</span>" . fileSizeHidenInput() . "
            <input type='file' name='userimage' size='30'></div>
        </div>
        <div class='form-group'>
          <label for='desription' class='col-sm-2 control-label'>$langDescription:</label>
          <div class='col-sm-10'>" . rich_text_editor('desc_form', 5, 20, $desc_form) . "</div>
        </div>";
//add custom profile fields
$tool_content .= render_profile_fields_form(array('origin' => 'edit_profile'));
$tool_content .= "
        <div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
          <a href='display_profile.php' class='btn btn-default'>$langCancel</a>
        </div>
      </fieldset>
      </form>
      </div>";

draw($tool_content, 1, null, $head_content);


/**
 *
 * @param type $val
 * @return int
 */
function valid_access($val) {
    $val = intval($val);
    if (in_array($val, array(ACCESS_PRIVATE, ACCESS_PROFS, ACCESS_USERS))) {
        return $val;
    } else {
        return 0;
    }
}
