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
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/pwgen.inc.php';
$require_valid_uid = TRUE;

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/log.php';

$tree = new Hierarchy();
$userObj = new User();

load_js('jstree');
load_js('tools.js');
$head_content .= "<script type='text/javascript'>
var lang = { 
        addPicture: '" . js_escape($langAddPicture) . "',
        confirmDelete: '" . js_escape($langConfirmDelete) . "'}; 
$(profile_init);</script>";


check_uid();
$nameTools = $langModifyProfile;
check_guest();

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

function redirect_to_message($id) {
    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?msg=' . $id);
    exit();
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
    $image_path = $webDir . '/courses/userimg/' . $_SESSION['uid'];
    $subscribe = (isset($_POST['subscribe']) and $_POST['subscribe'] == 'yes') ? '1' : '0';
    $old_language = $language;
    $langcode = $language = $_SESSION['langswitch'] = $_POST['userLanguage'];
    Database::get()->query("UPDATE user SET lang = ?s WHERE id = ?d", $langcode, $uid);    

    $all_ok = register_posted_variables(array(
        'am_form' => get_config('am_required') and $myrow->status != 1,
        'desc_form' => false,
        'phone_form' => false,
        'email_form' => get_config('email_required'),
        'surname_form' => !$is_admin,
        'givenname_form' => true,
        'username_form' => true,
        'email_public' => false,
        'phone_public' => false,
        'am_public' => false), 'all');

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
            redirect_to_message(7);
        }
        if (!copy_resized_image($image_file, $type, IMAGESIZE_SMALL, IMAGESIZE_SMALL, $image_path . '_' . IMAGESIZE_SMALL . '.jpg')) {
            redirect_to_message(7);
        }
        Database::get()->query("UPDATE user SET has_icon = 1 WHERE id = ?d", $_SESSION['uid']);        
        Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                             'addimage' => 1,
                                             'imagetype' => $type));
    }

    // check if email is valid
    if ((get_config('email_required') | get_config('email_verification_required')) and !email_seems_valid($email_form)) {
        redirect_to_message(6);
    }

    // check if there are empty fields
    if (!$all_ok) {
        redirect_to_message(4);
    }

    if (!$allow_username_change) {
        $username_form = $_SESSION['uname'];
    }

    $username_form = canonicalize_whitespace($username_form);
        // check if username exists        
    if ($username_form != $_SESSION['uname']) {        
        $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $username_form);        
        if ($username_check) {
            redirect_to_message(5);
        }
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
            redirect_to_message(1);
        }
    if ($old_language != $language) {
        redirect_to_message(1);
    }
}

//Show message if exists
if (isset($_GET['msg'])) {
    $urlText = '';
    $type = 'caution';
    switch ($_GET['msg']) {
        case 1: //profile information changed successfully
            $message = $langProfileReg;
            $urlText = "<br /><a href='$urlServer'>$langHome</a>";
            $type = "success";
            break;
        case 3: //pass too easy
            $message = $langPassTooEasy . ": <strong>" . genPass() . "</strong>";
            break;
        case 4: // empty fields check
            $message = $langFieldsMissing;
            break;
        case 5: //username already exists
            $message = $langUserFree;
            break;
        case 6: //email not valid
            $message = $langEmailWrong;
            break;
        case 7: //invalid image
            $message = $langInvalidPicture;
            break;
        case 10: // invalid characters
            $message = $langInvalidCharsUsername;
            break;
        default:
            exit;
    }
    $tool_content .= "<p class='$type'>$message$urlText</p><br/>";
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

$sec = $urlSecure . 'main/profile/profile.php';
$passurl = $urlSecure . 'main/profile/password.php';

$tool_content .= "
  <div id='operations_container'>" .
        action_bar(array(
            array('title' => $langDisplayProfile,
                'url' => "display_profile.php",
                'icon' => 'fa-eye',
                'level' => 'primary-label'),
            array('title' => $langChangePass,
                'url' => "$passurl",
                'icon' => 'fa-key',
                'show' => $allow_password_change,
                'level' => 'primary'),
            array('title' => $langEmailUnsubscribe,
                'url' => "emailunsubscribe.php",
                'icon' => 'fa-envelope',
                'level' => 'primary'),
            array('title' => $langUnregUser,
                'url' => "../unreguser.php",
                'icon' => 'fa-times',
                'button-class'=>'btn-danger',
                'level' => 'primary')
            )) .
        "</div>";
$tool_content .= "
   <form method='post' enctype='multipart/form-data' action='$sec' onsubmit='return validateNodePickerForm();'>
   <fieldset>
     <legend>$langUserData</legend>
        <table class='tbl' width='100%'>
        <tr>
          <th>$langName:</th>";

if ($allow_name_change) {
    $tool_content .= "
          <td><input type='text' size='40' name='givenname_form' value='$givenname_form' /></td>";
} else {
    $tool_content .= "
          <td><b>$givenname_form</b>
            <input type='hidden' name='givenname_form' value='$givenname_form' />
          </td>";
}

$tool_content .= "</tr><tr><th>$langSurname:</th>";
if ($allow_name_change) {
    $tool_content .= "<td><input type='text' size='40' name='surname_form' value='$surname_form' /></td>";
} else {
    $tool_content .= "<td><b>" . $surname_form . "</b><input type='hidden' name='surname_form' value='$surname_form' /></td>";
}
$tool_content .= "</tr>";

if ($allow_username_change) {
    $tool_content .= "
        <tr>
          <th>$langUsername:</th>
          <td><input type='text' size='40' name='username_form' value='$username_form' /></td>
        </tr>";
} else {
    // means that it is external auth method, so the user cannot change this password
    $tool_content .= "
        <tr>
          <th class='left'>$langUsername:</th>
          <td><b>$username_form</b> [$auth_text]
            <input type='hidden' name='username_form' value='$username_form' />
          </td>
        </tr>";
}

$access_options = array(ACCESS_PRIVATE => $langProfileInfoPrivate,
    ACCESS_PROFS => $langProfileInfoProfs,
    ACCESS_USERS => $langProfileInfoUsers);

$tool_content .= "<tr><th>$langEmail:</th>";

//if ($allow_name_change) {
$tool_content .= "<td><input type='text' size='40' name='email_form' value='$email_form' />";
//} else {
//        $tool_content .= "
//           <td><b>$email_form</b> [$auth_text]
//               <input type='hidden' name='email_form' value='$email_form' /> ";
//}
$tool_content .= selection($access_options, 'email_public', $myrow->email_public) . "</td>
        </tr>
        <tr><th>$langAm</th>
            <td><input type='text' size='40' name='am_form' value='$am_form' /> " .
        selection($access_options, 'am_public', $myrow->am_public) . "</td></tr>
        <tr><th>$langPhone</th>
            <td><input type='text' size='40' name='phone_form' value='$phone_form' /> " .
        selection($access_options, 'phone_public', $myrow->phone_public) . "</td></tr>";

if (get_user_email_notification_from_courses($uid)) {
    $selectedyes = 'checked';
    $selectedno = '';
} else {
    $selectedyes = '';
    $selectedno = 'checked';
}
$tool_content .= "<tr><th>$langEmailFromCourses:</th>
                  <td><input type='radio' name='subscribe' value='yes' $selectedyes />$langYes&nbsp;
                  <input type='radio' name='subscribe' value='no' $selectedno />$langNo&nbsp;
                  </td></tr>";
if (get_config('email_verification_required')) {
    $user_email_status = get_mail_ver_status($uid);
    switch ($user_email_status) {
        case EMAIL_VERIFICATION_REQUIRED:
            $link = "<a href = '../auth/mail_verify_change.php?from_profile=TRUE'>$langHere</a>.";
            $message = "<div class='alert1'>$langMailNotVerified $link</div>";
            break;
        case EMAIL_VERIFIED:
            $message = "<img src='$themeimg/tick_1.png' title='$langMailVerificationYesU' />";
            break;
        case EMAIL_UNVERIFIED:
            $link = "<a href = '../auth/mail_verify_change.php?from_profile=TRUE'>$langHere</a>.";
            $message = "<div class='alert1'>$langMailNotVerified $link</div>";
        default:
            break;
    }
    $tool_content .= "<tr><th>$langVerifiedMail</th><td>$message</td>";
}

if (!get_config('restrict_owndep')) {
    $tool_content .= "<tr><th>$langFaculty:</th><td>";
    list($js, $html) = $tree->buildUserNodePicker(array('defaults' => $userObj->getDepartmentIds($uid)));
    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</td></tr>";
}

$tool_content .= "<tr><th>$langLanguage:</th>
          <td>" . lang_select_options('userLanguage') . "</td>
        </tr>";
if ($icon) {
    $message_pic = $langReplacePicture;
    $picture = profile_image($uid, IMAGESIZE_SMALL) . "&nbsp;&nbsp;";
    $delete = '&nbsp;' . icon('fa-times', $langDelete, null, 'id="delete"') . '&nbsp;';
} else {
    $picture = $delete = '';
    $message_pic = $langAddPicture;
}
$tool_content .= "<tr>
        <th>$message_pic</th>
        <td><span>$picture$delete</span><input type='file' name='userimage' size='30'></td>
      </tr>
      <tr>
        <th>$langDescription:</th>
        <td>" . rich_text_editor('desc_form', 5, 20, $desc_form) . "</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td class='right'><input type='submit' name='submit' value='$langModify' /></td>
      </tr>
      </table>
      </fieldset>
      </form>";

draw($tool_content, 1, null, $head_content);

function valid_access($val) {
    $val = intval($val);
    if (in_array($val, array(ACCESS_PRIVATE, ACCESS_PROFS, ACCESS_USERS))) {
        return $val;
    } else {
        return 0;
    }
}
