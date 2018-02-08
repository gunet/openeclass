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

use Hybrid\Auth;
$require_login = true;
$require_valid_uid = true;
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'profile_change';

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/custom_profile_fields_functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/pwgen.inc.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/log.class.php';

require_once 'modules/auth/methods/hybridauth/config.php';

check_uid();
check_guest();

$toolName = $langMyProfile;
$pageName = $langModifyProfile;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

$tree = new Hierarchy();
$userObj = new User();
$image_path = $webDir . '/courses/userimg/' . $_SESSION['uid'];

load_js('jstree3');
load_js('tools.js');

$myrow = Database::get()->querySingle("SELECT surname, givenname, username, email, am, phone,
                                            lang, status, has_icon, description,
                                            email_public, phone_public, am_public, password
                                        FROM user WHERE id = ?d", $uid);

$password = $myrow->password;
$auth = array_search($password, $auth_ids);
if (!$auth) {
    $auth = 1;
}
$data['auth_text'] = get_auth_info($auth);

if ($auth != 1) {
    $data['allow_username_change'] = false;
    $allow_password_change = false;
} else {
    $data['allow_username_change'] = !get_config('block_username_change');
    $allow_password_change = true;
}

if (in_array($password, array('shibboleth', 'cas', 'ldap'))) {
    $data['allow_name_change'] = false;
} else {
    $data['allow_name_change'] = true;
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
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    // First process language changes
    checkSecondFactorChallenge();
    saveSecondFactorUserProfile();
    if (!file_exists($webDir . '/courses/userimg/')) {
        make_dir($webDir . '/courses/userimg/');
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
                    'username_form' => $allow_username_change,
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
            $departments = arrayValuesDirect($_POST['department']);
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
    if ((get_config('email_required') or get_config('email_verification_required')) and !Swift_Validate::email($email_form)) { 
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
    
    $need_email_verification = false;
    // TODO: Allow admin to configure allowed username format
    if (!empty($email_form) && ($email_form != $_SESSION['email']) && get_config('email_verification_required')) {
        $verified_mail_sql = ", verified_mail = " . EMAIL_UNVERIFIED;
        $need_email_verification = true;
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
        if ($need_email_verification) { // email has been changed and needs verification
            redirect_to_home_page("modules/auth/mail_verify_change.php?from_profile=true");
        } else {
            Session::Messages($langProfileReg, 'alert-success');
            redirect_to_home_page("main/profile/display_profile.php");    
        }
    }
    if ($old_language != $language) {
        Session::Messages($langProfileReg, 'alert-success');
        redirect_to_home_page("main/profile/display_profile.php");
    }
}

// HybridAuth actions
if (isset($_GET['provider'])) {
    // user requests hybridauth provider uid deletion
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'delete') {
            $auth_id = array_search(strtolower($_GET['provider']), $auth_ids);
            if ($auth_id and
                Database::get()->query('DELETE FROM user_ext_uid
                    WHERE user_id = ?d AND auth_id = ?d', $uid, $auth_id)) {
                Session::Messages($langProfileReg, 'alert-success');
                redirect_to_home_page('main/profile/profile.php');
            }
        } elseif ($_GET['action'] == 'connect') {
            // HybridAuth checks, authentication and user profile info and finally store provider user id in the db
            require_once 'modules/auth/methods/hybridauth/config.php';
            require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';

            $config = get_hybridauth_config();
            $user_data = '';
            $provider = q(trim(strtolower($_GET['provider'])));

            $hybridauth = new Hybrid_Auth($config);
            $allProviders = $hybridauth->getProviders();

            if (count($allProviders) && array_key_exists($_GET['provider'], $allProviders)) { //check if the provider is existent and valid - it's checked above
                try {
                    if (in_array($provider, $hybridAuthMethods)) {
                        $hybridauth = new Hybrid_Auth($config);
                        $adapter = $hybridauth->authenticate($provider);
                        $providerAuthId = array_search(strtolower($provider), $auth_ids);

                        // grab the user profile
                        $user_data = $adapter->getUserProfile();

                        // Fetch user profile id and check if there is another
                        // instance in the db (this would happen if a user tried to
                        // authenticate two different eclass accounts with the same
                        // Facebook, etc. account)
                        if ($user_data->identifier)
                            $r = Database::get()->querySingle('SELECT id FROM user_ext_uid
                                WHERE auth_id = ?d AND uid = ?s AND user_id <> ?d',
                                $providerAuthId, $user_data->identifier, $uid);
                            if ($r) {
                                // HybridAuth provider uid is already in the db!
                                // (which means the user tried to authenticate a second
                                // eClass account with the same facebook etc. account)
                                Session::Messages($langProviderIdAlreadyExists, 'alert-warning');
                            } else {
                                Database::get()->querySingle('INSERT INTO user_ext_uid
                                    SET user_id = ?d, auth_id = ?d, uid = ?s',
                                    $uid, $providerAuthId, $user_data->identifier);
                                Session::Messages($langProfileReg, 'alert-success');
                            }
                    } else {
                        Session::Messages($langProviderError, 'alert-danger');
                    } 
                    redirect_to_home_page('main/profile/profile.php');
                } catch (Exception $e) {
                    // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                    // let hybridauth forget all about the user so we can try to authenticate again.

                    // Display the received error,
                    // to know more please refer to Exceptions handling section on the userguide
                    switch($e->getCode()) {
                        case 0:
                            Session::Messages($langProviderError1, 'alert-danger');
                            break;
                        case 1:
                            Session::Messages($langProviderError2, 'alert-danger');
                            break;
                        case 2:
                            Session::Messages($langProviderError3, 'alert-danger');
                            break;
                        case 3:
                            Session::Messages($langProviderError4, 'alert-danger');
                            break;
                        case 4:
                            Session::Messages($langProviderError5, 'alert-danger');
                            break;
                        case 5:
                            Session::Messages($langProviderError6, 'alert-danger');
                            break;
                        case 6:
                            Session::Messages($langProviderError7, 'alert-danger');
                            $adapter->logout(); 
                            break;
                        case 7:
                            Session::Messages($langProviderError8, 'alert-danger');
                            $adapter->logout(); 
                            break;
                    }
                    $_GET['msg'] = 11; // display generic error for now

                    // debug messages for hybridauth errors
                    //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
                    //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
                }
            } // endif(count($allProviders) && array_key_exists($_GET['provider'], $allProviders))
        } // endif(isset($_GET['provider'])) {
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
    $tool_content .= "<table width='100%'><tbody><tr><td class='alert alert-danger'>$message$urlText</td></tr></tbody></table><br /><br />";
}

$data['surname_form'] = q($myrow->surname);
$data['givenname_form'] = q($myrow->givenname);
$data['username_form'] = q($myrow->username);
$data['email_public'] = q($myrow->email_public);
$data['am_public'] = q($myrow->am_public);
$data['email_form'] = q($myrow->email);
$data['am_form'] = q($myrow->am);
$data['phone_form'] = q($myrow->phone);
$data['phone_public'] = q($myrow->phone_public);
$data['desc_form'] = $myrow->description;
$data['userLang'] = $myrow->lang;
$data['icon'] = $myrow->has_icon;

$data['sec'] = $urlServer . 'main/profile/profile.php';
$passurl = $urlServer . 'main/profile/password.php';

$data['action_bar'] =
        action_bar(
        [
            ['title' => $langBack,
                'url' => "display_profile.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);

$data['access_options'] = array(ACCESS_PROFS => $langProfileInfoProfs,
                                ACCESS_USERS => $langProfileInfoUsers);


if (get_user_email_notification_from_courses($uid)) {
    $data['selectedyes'] = 'checked';
    $data['selectedno'] = '';
} else {
    $data['selectedyes'] = '';
    $data['selectedno'] = 'checked';
}

if (get_config('email_verification_required')) {
    $user_email_status = get_mail_ver_status($uid);
    $data['messageClass'] = '';
    switch ($user_email_status) {
        case EMAIL_VERIFICATION_REQUIRED:
        case EMAIL_UNVERIFIED:
            $data['messageClass'] = ' alert alert-warning';
            $link = "<a href = '{$urlAppend}modules/auth/mail_verify_change.php?from_profile=true'>$langHere</a>.";
            $data['message'] = "$langMailNotVerified $link";
            break;
        case EMAIL_VERIFIED:
            $data['message'] = icon('fa-check', $langMailVerificationYesU);
            break;
        default:
            break;
    }
}
if (!get_config('restrict_owndep')) {
    list($js, $html) = $tree->buildUserNodePickerIndirect(array('defaults' => $userObj->getDepartmentIds($uid)));
    $head_content .= $js;
    $data['html'] = $html;
}


if ($data['icon']) {
    $data['message_pic'] = $langReplacePicture;
    $data['picture'] = profile_image($uid, IMAGESIZE_SMALL) . "&nbsp;&nbsp;";
    $data['delete'] = '&nbsp;' . icon('fa-times', $langDelete, '#', 'id="delete"') . '&nbsp;';
} else {
    $data['picture'] = $data['delete'] = '';
    $data['message_pic'] = $langAddPicture;
}
enableCheckFileSize();

$data['info_text_area'] = rich_text_editor('desc_form', 5, 20, $data['desc_form']);


foreach ($hybridAuthMethods as $provider) {
    $data['userProviders'][$provider] = false;
}
Database::get()->queryFunc('SELECT auth_id FROM user_ext_uid WHERE user_id = ?d',
    function ($item) {
        global $data, $auth_ids;
        $data['userProviders'][$auth_ids[$item->auth_id]] = true;
    }, $uid);

// HybridAuth settings and links
// check if there are any available alternative providers for authentication and show the corresponding links on 
// the homepage, or no mesage if no providers are enabled
$config = get_hybridauth_config();

$hybridauth = new Hybrid_Auth($config);
$data['allProviders'] = $hybridauth->getProviders();
$activeAuthMethods = get_auth_active_methods();
foreach ($data['allProviders'] as $provider => $settings) {
    $aid = array_search(strtolower($provider), $auth_ids);
    if (array_search($aid, $data['allProviders']) === false) {
        unset($data['allProviders'][$provider]);
    }
}

$data['SecFactorProfile'] = showSecondFactorUserProfile();

$data['SecFactorChallenge'] = showSecondFactorChallenge();

$data['menuTypeID'] = 1;
view('main.profile.edit', $data);


/**
 *
 * @param type $val
 * @return int
 */
function valid_access($val) {
    $val = intval($val);
    if (in_array($val, array(ACCESS_PROFS, ACCESS_USERS))) {
        return $val;
    } else {
        return 0;
    }
}
