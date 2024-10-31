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

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
                                            email_public, phone_public, am_public, pic_public, password
                                        FROM user WHERE id = ?d", $uid);


$password = $myrow->password;
$auth = array_search($password, $auth_ids);
if (!$auth) {
    $auth = 1;
}
$data['auth_text'] = get_auth_info($auth);

if ($auth != 1) {
    $data['allow_username_change'] = $allow_username_change= false;
    $allow_password_change = false;
} else {
    $data['allow_username_change'] = $allow_username_change = !get_config('block_username_change');
    $allow_password_change = true;
}

if (in_array($password, array('shibboleth', 'cas', 'ldap')) or get_config('disable_name_surname_change')) {
    $data['allow_name_change'] = $allow_name_change = false;
} else {
    $data['allow_name_change'] = $allow_name_change = true;
}

if ((get_config('am_prevent_autoset_change') and isset($_SESSION['auth_user_info']['studentid']) and $_SESSION['auth_user_info']['studentid']) or get_config('disable_am_change')) {
    $data['allow_am_change'] = $allow_am_change = false;
} else {
    $data['allow_am_change'] = $allow_am_change = true;
}


if ((get_config('email_prevent_autoset_change') and isset($_SESSION['auth_user_info']['email']) and $_SESSION['auth_user_info']['email']) or get_config('disable_email_change')) {
    $data['allow_email_change'] = $allow_email_change = false;
} else {
    $data['allow_email_change'] = $allow_email_change = true;
}

// Handle AJAX profile image delete
if (isset($_POST['delimage'])) {
    $images = glob($image_path . '_*');
    foreach ($images as $image) {
        unlink($image);
    }
    Database::get()->query("UPDATE user SET has_icon = 0 WHERE id = ?d", $uid);
    Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                         'deleteimage' => 1));
    exit;
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    // First process language changes
    checkSecondFactorChallenge();
    saveSecondFactorUserProfile();
    if (!file_exists($webDir . '/courses/userimg/')) {
        make_dir($webDir . '/courses/userimg/');
        touch($webDir."courses/userimg/index.php");
    }
    $subscribe = (isset($_POST['subscribe']) and $_POST['subscribe'] == 'yes') ? EMAIL_NOTIFICATIONS_ENABLED : EMAIL_NOTIFICATIONS_DISABLED;
    $old_language = $language;
    $langcode = $language = $_SESSION['langswitch'] = $_POST['userLanguage'];
    Database::get()->query("UPDATE user SET lang = ?s WHERE id = ?d", $langcode, $uid);

    $var_arr = array('am_form' => get_config('am_required') and $myrow->status != 1,
                    'desc_form' => false,
                    'phone_form' => false,
                    'email_form' => $allow_email_change && get_config('email_required'),
                    'surname_form' => $allow_name_change && !$is_admin,
                    'givenname_form' => $allow_name_change,
                    'username_form' => $allow_username_change,
                    'email_public' => false,
                    'phone_public' => false,
                    'am_public' => false,
                    'pic_public' => false);

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

    // upload user picture
    if (isset($_FILES['userimage']) && is_uploaded_file($_FILES['userimage']['tmp_name'])) {

        validateUploadedFile($_FILES['userimage']['name'], 1);

        $type = $_FILES['userimage']['type'];
        $image_file = $_FILES['userimage']['tmp_name'];
        $image_base = $image_path . '_' . profile_image_hash($uid) . '_';

        if (!copy_resized_image($image_file, $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE, $image_base . IMAGESIZE_LARGE . '.jpg')) {
            Session::flash('message',$langInvalidPicture);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("main/profile/profile.php");
        }
        if (!copy_resized_image($image_file, $type, IMAGESIZE_SMALL, IMAGESIZE_SMALL, $image_base . IMAGESIZE_SMALL . '.jpg')) {
            Session::flash('message',$langInvalidPicture);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("main/profile/profile.php");
        }
        Database::get()->query("UPDATE user SET has_icon = 1 WHERE id = ?d", $_SESSION['uid']);
        $_SESSION['profile_image_cache_buster'] = time();
        Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                             'addimage' => 1,
                                             'imagetype' => $type));
    }


    // check if email is valid
    if (!empty($email_form) and !valid_email($email_form)) {
        Session::flash('message',$langEmailWrong);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("main/profile/profile.php");
    }

    // check if there are empty fields
    if (!$all_ok) {
        Session::flash('message',$langFieldsMissing);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("main/profile/profile.php");
    }

    if (!$allow_username_change) {
        $username_form = $_SESSION['uname'];
    }

    if (!$allow_name_change) {
        $surname_form = $_SESSION['surname'];
        $givenname_form = $_SESSION['givenname'];
    }

    if (!$allow_am_change) {
        $am_form = $myrow->am;
    }

    if (!$allow_email_change) {
        $email_form = $myrow->email;
    }

    $username_form = canonicalize_whitespace($username_form);
        // check if username exists
    if ($username_form != $_SESSION['uname']) {
        $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $username_form);
        if ($username_check) {
            Session::flash('message',$langUserFree);
            Session::flash('alert-class', 'alert-warning');
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
        Session::flash('message',$cpf_error_str);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("main/profile/profile.php");
    }

    $need_email_verification = false;
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
                             email_public = ?d,
                             phone_public = ?d,
                             receive_mail = ?d,
                             am_public = ?d,
                             pic_public = ?d
                             $verified_mail_sql
                         WHERE id = ?d",
                            $surname_form, $givenname_form, $username_form, $email_form, $am_form, $phone_form, $desc_form, $email_public, $phone_public, $subscribe, $am_public, $pic_public, $uid);

    // fill custom profile fields
    process_profile_fields_data(array('uid' => $uid, 'origin' => 'edit_profile'));

    if ($q->affectedRows > 0 or isset($departments)) {
        $old_username = q($myrow->username);
        $old_email = q($myrow->email);
        $old_am = q($myrow->am);

        $userObj->refresh($uid, $departments);
        Log::record(0, 0, LOG_PROFILE, array('uid' => intval($_SESSION['uid']),
                                             'modifyprofile' => 1,
                                             'old_username' => "$old_username",
                                             'username' => "$username_form",
                                             'old_email' => "$old_email",
                                             'email' => "$email_form",
                                             'old_am' => "$old_am",
                                             'am' => "$am_form"));
        $_SESSION['uname'] = $username_form;
        $_SESSION['surname'] = $surname_form;
        $_SESSION['givenname'] = $givenname_form;
        $_SESSION['email'] = $email_form;
        if ($need_email_verification) { // email has been changed and needs verification
            $redirect_to = "modules/auth/mail_verify_change.php?from_profile=true";
        } else {
            Session::flash('message',$langProfileReg);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("main/profile/display_profile.php");
        }
    }
    Session::flash('message',$langProfileReg);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($redirect_to ?? "main/profile/display_profile.php");
}

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;

// HybridAuth actions
if (isset($_GET['provider'])) {
    // user requests hybridauth provider uid deletion
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'delete') {
            $auth_id = array_search(strtolower($_GET['provider']), $auth_ids);
            if ($auth_id and
                Database::get()->query('DELETE FROM user_ext_uid
                    WHERE user_id = ?d AND auth_id = ?d', $uid, $auth_id)) {
                Session::flash('message',$langProfileReg);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page('main/profile/profile.php');
            }
        } elseif ($_GET['action'] == 'connect') {
            // HybridAuth checks, authentication and user profile info and finally store provider user id in the db
            require_once 'modules/auth/methods/hybridauth/config.php';
            require_once 'vendor/hybridauth/hybridauth/src/Hybridauth.php';

            $config = get_hybridauth_config();
            $user_data = '';
            $provider = @trim(strip_tags(strtolower($_GET['provider'])));
            if($_GET['provider'] == 'Live') {
                $_GET['provider'] = 'WindowsLive';
            }
            $hybridauth = new Hybridauth($config);
        $allProviders = $hybridauth->getProviders();

            if (count($allProviders) && in_array($_GET['provider'], $allProviders)) { //check if the provider is existent and valid - it's checked above
                try {
                    if (in_array($provider, $hybridAuthMethods)) {
                        $providerAuthId = array_search(strtolower($provider), $auth_ids);

                        if(isset($_SESSION['hybridauth_callback']) && $_SESSION['hybridauth_callback'] == 'profile') {
                            unset($_SESSION['hybridauth_callback']);
                            if(isset($_SESSION['hybridauth_provider'])) {
                                unset($_SESSION['hybridauth_provider']);
                            }
                        } else {
                            $_SESSION['hybridauth_callback'] = 'profile';
                            if($provider == 'linkedin') {
                                $_SESSION['hybridauth_provider'] = 'LinkedIn';
                            } else {
                                $_SESSION['hybridauth_provider'] = ucfirst($provider);
                            }
                        }
                        if ($provider == 'live') {
                            $provider = 'WindowsLive';
                        }

                        /**
                            * Feed configuration array to Hybridauth.
                        */
                        $hybridauth = new Hybridauth($config);
                        $hybridauth->authenticate($provider);
                        $adapters = $hybridauth->getConnectedAdapters();
                        foreach ($adapters as $name => $adapter) :
                            $user_data = $adapter->getUserProfile();
                        endforeach;

                        /**
                        * This will erase the current user authentication data from session, and any further
                            * attempt to communicate with provider.
                            */
                        if (isset($_GET['logout'])) {
                            $adapter = $hybridauth->getAdapter($_GET['logout']);
                            $adapter->disconnect();
                        }

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
                                Session::flash('message',$langProviderIdAlreadyExists);
                                Session::flash('alert-class', 'alert-warning');
                            } else {
                                Database::get()->querySingle('INSERT INTO user_ext_uid
                                    SET user_id = ?d, auth_id = ?d, uid = ?s',
                                    $uid, $providerAuthId, $user_data->identifier);
                                    Session::flash('message',$langProfileReg);
                                    Session::flash('alert-class', 'alert-success');
                            }
                    } else {
                        Session::flash('message',$langProviderError);
                        Session::flash('alert-class', 'alert-danger');
                    }
                    redirect_to_home_page('main/profile/profile.php');
                } catch (Exception $e) {
                    // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                    // let hybridauth forget all about the user so we can try to authenticate again.

                    // Display the received error,
                    // to know more please refer to Exceptions handling section on the userguide
                    switch($e->getCode()) {
                        case 0:
                            Session::flash('message',$langProviderError1);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 1:
                            Session::flash('message',$langProviderError2);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 2:
                            Session::flash('message',$langProviderError3);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 3:
                            Session::flash('message',$langProviderError4);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 4:
                            Session::flash('message',$langProviderError5);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 5:
                            Session::flash('message',$langProviderError6);
                            Session::flash('alert-class', 'alert-danger');
                            break;
                        case 6:
                            Session::flash('message',$langProviderError7);
                            Session::flash('alert-class', 'alert-danger');
                            $adapter->disconnect();
                            break;
                        case 7:
                            Session::flash('message',$langProviderError8);
                            Session::flash('alert-class', 'alert-danger');
                            $adapter->disconnect();
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
    Session::flash('message', $message);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('main/profile/profile.php');
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
$data['email_public_selected'] = $data['am_public_selected'] = $data['phone_public_selected'] = $data['pic_public_selected'] = '';

if ($myrow->email_public) {
    $data['email_public_selected'] = 'checked';
}
if ($myrow->am_public) {
    $data['am_public_selected'] = 'checked';
}
if ($myrow->phone_public) {
    $data['phone_public_selected'] = 'checked';
}
if ($myrow->pic_public) {
    $data['pic_public_selected'] = 'checked';
}

$data['sec'] = $urlServer . 'main/profile/profile.php';
$passurl = $urlServer . 'main/profile/password.php';

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
    list($js, $html) = $tree->buildUserNodePicker(array('defaults' => $userObj->getDepartmentIds($uid)));
    $head_content .= $js;
    $data['html'] = $html;
}


if ($data['icon']) {
    $data['message_pic'] = $langReplacePicture;
    $data['picture'] = profile_image($uid, IMAGESIZE_SMALL) . "&nbsp;&nbsp;";
    $data['delete'] = '&nbsp;' . icon('fa-xmark link-delete', $langDelete, '#', 'id="delete"') . '&nbsp;';
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
// the homepage, or no message if no providers are enabled
$config = get_hybridauth_config();

$hybridauth = new Hybridauth( $config );
$allProviders = $hybridauth->getProviders();
$activeAuthMethods = get_auth_active_methods();
foreach ($allProviders as $provider => $settings) {
    if($settings === 'WindowsLive' && array_search(array_search(strtolower('live'), $auth_ids), $activeAuthMethods)) {
        $allProviders[$provider] = 'Live';
        continue;
    }
    $aid = array_search(strtolower($settings), $auth_ids);
    if (array_search($aid, $activeAuthMethods) === false) {
        unset($allProviders[$provider]);
    }
}

$data['SecFactorProfile'] = showSecondFactorUserProfile();

$data['SecFactorChallenge'] = showSecondFactorChallenge();

$data['allProviders'] = $allProviders;

$data['menuTypeID'] = 1;
view('main.profile.edit', $data);
