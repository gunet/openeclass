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

check_uid();
check_guest();

$toolName = $langMyProfile;
$pageName = $langModifyProfile;
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

$tree = new Hierarchy();
$userObj = new User();
$image_path = $webDir . '/courses/userimg/' . $_SESSION['uid'];

load_js('jstree3d');
load_js('tools.js');
$head_content .= "<script type='text/javascript'>
var lang = {
        addPicture: '" . js_escape($langAddPicture) . "',
        confirmDelete: '" . js_escape($langConfirmDelete) . "'};
$(profile_init);</script>";

$myrow = Database::get()->querySingle("SELECT surname, givenname, username, email, am, phone,
                                            lang, status, has_icon, description,
                                            email_public, phone_public, am_public, password,
                        facebook_uid, twitter_uid, google_uid, live_uid, yahoo_uid, linkedin_uid
                                        FROM user WHERE id = ?d", $uid);
$facebook_uid = $myrow->facebook_uid;
$twitter_uid = $myrow->twitter_uid;
$google_uid = $myrow->google_uid;
$live_uid = $myrow->live_uid;
$yahoo_uid = $myrow->yahoo_uid;
$linkedin_uid = $myrow->linkedin_uid;

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
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    // First process language changes
    if (!file_exists($webDir . '/courses/userimg/')) {
        mkdir($webDir . '/courses/userimg/', 0775);
        touch($webDir."courses/userimg/index.php");
    }
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
            Session::Messages($langProfileReg, 'alert-success');
            redirect_to_home_page("main/profile/display_profile.php");
        }
    if ($old_language != $language) {
        Session::Messages($langProfileReg, 'alert-success');
        redirect_to_home_page("main/profile/display_profile.php");
    }
}

//HybridAuth actions
if(isset($_GET['provider'])) {
    //user requests hybridauth provider_uid deletion
    if(@$_GET['action'] == 'delete') {
        if($_GET['provider'] == 'Facebook') { $q = Database::get()->query("UPDATE user SET facebook_uid = '' WHERE id = ?d", $uid); $facebook_uid = '';}
        if($_GET['provider'] == 'Twitter') { $q = Database::get()->query("UPDATE user SET twitter_uid = '' WHERE id = ?d", $uid); $twitter_uid = '';}
        if($_GET['provider'] == 'Google') { $q = Database::get()->query("UPDATE user SET google_uid = '' WHERE id = ?d", $uid); $google_uid = '';}
        if($_GET['provider'] == 'Live') { $q = Database::get()->query("UPDATE user SET live_uid = '' WHERE id = ?d", $uid); $live_uid = '';}
        if($_GET['provider'] == 'Yahoo') { $q = Database::get()->query("UPDATE user SET yahoo_uid = '' WHERE id = ?d", $uid); $yahoo_uid = '';}
        if($_GET['provider'] == 'LinkedIn') { $q = Database::get()->query("UPDATE user SET linkedin_uid = '' WHERE id = ?d", $uid); $linkedin_uid = '';}
        $_GET['msg'] = 1; //show success message
    } elseif(@$_GET['action'] == 'connect') {
    //HybridAuth checks, authentication and user profile info and finally store provider user id in the db
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();
    $user_data = '';
    $provider = q(strtolower($_GET["provider"]));
    
    $hybridauth = new Hybrid_Auth($config);
    $allProviders = $hybridauth->getProviders();

    if(count($allProviders) && array_key_exists($_GET["provider"], $allProviders)) { //check if the provider is existent and valid - it's checked above
        try {
            $hybridauth = new Hybrid_Auth($config);
            $adapter = $hybridauth->authenticate( @ trim( strip_tags($provider)) );
                
            // grab the user profile
            $user_data = $adapter->getUserProfile();
                
            //fetch user profile id and check if there is another instance in the db (this would happen if a user tried to authenticate
            //two different eclass accounts with the same facebook, etc. account)
            if($user_data->identifier)
                switch($provider) {
                    case 'facebook':
                        $facebook_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE facebook_uid = ?s AND id <> ?d", q($facebook_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET facebook_uid = ?s WHERE id = ?d", q($facebook_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $facebook_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    case 'twitter':
                        $twitter_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE twitter_uid = ?s AND id <> ?d", q($twitter_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET twitter_uid = ?s WHERE id = ?d", q($twitter_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $twitter_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    case 'google':
                        $google_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE google_uid = ?s AND id <> ?d", q($google_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET google_uid = ?s WHERE id = ?d", q($google_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $google_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    case 'live':
                        $live_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE live_uid = ?s AND id <> ?d", q($live_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET live_uid = ?s WHERE id = ?d", q($live_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $live_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    case 'yahoo':
                        $yahoo_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE yahoo_uid = ?s AND id <> ?d", q($yahoo_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET yahoo_uid = ?s WHERE id = ?d", q($yahoo_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $yahoo_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    case 'linkedin':
                        $linkedin_uid = $user_data->identifier;
                        if(!$r = Database::get()->querySingle("SELECT id FROM user WHERE linkedin_uid = ?s AND id <> ?d", q($linkedin_uid), $uid)) {
                            $q = Database::get()->querySingle("UPDATE user SET linkedin_uid = ?s WHERE id = ?d", q($linkedin_uid), $uid);
                            $_GET['msg'] = 1;
                        } else {
                            $linkedin_uid = '';
                            $_GET['msg'] = 12;
                        }
                        break;
                    default:
                        $_GET['msg'] = 11;
                        break;
                } 
                
        } catch(Exception $e) {
            // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
            // let hybridauth forget all about the user so we can try to authenticate again.

            // Display the recived error,
            // to know more please refer to Exceptions handling section on the userguide
            switch($e->getCode()) {
                case 0 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError1</td></tr></tbody></table><br /><br />"; break;
                case 1 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError2</td></tr></tbody></table><br /><br />"; break;
                case 2 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError3</td></tr></tbody></table><br /><br />"; break;
                case 3 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError4</td></tr></tbody></table><br /><br />"; break;
                case 4 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError5</td></tr></tbody></table><br /><br />"; break;
                case 5 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError6</td></tr></tbody></table><br /><br />"; break;
                case 6 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError7</td></tr></tbody></table><br /><br />"; $adapter->logout(); break;
                case 7 : $warning = "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langProviderError8</td></tr></tbody></table><br /><br />"; $adapter->logout(); break;
            }
            $_GET['msg'] = 11; //display generic error for now
            
            // debug messages for hybridauth errors
            //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
            //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
        }
    } //endif(count($allProviders) && array_key_exists($_GET['provider'], $allProviders))
    } //endif(isset($_GET['provider'])) {
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
        case 11: // invalid HybridAuth settings or authentication
            $message = $langProviderError;
            break;
        case 12: //hybrid auth provider is already in the db! (which means the user tried to authenticate a second eclass account with the same facebook etc. account)
            $message = $langProviderIdAlreadyExists;
            break;
        default:
            exit;
    }
    $tool_content .= "<table width='100%'><tbody><tr><td class='alert alert-danger'>$message$urlText</td></tr></tbody></table><br /><br />";
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
    $tool_content .= "<div class='col-sm-10 form-control-static'>";
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

//HybridAuth settings and links
//check if there are any available alternative providers for authentication and show the corresponding links on 
//the homepage, or no mesage if no providers are enabled
require_once 'modules/auth/methods/hybridauth/config.php';
require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
$config = get_hybridauth_config();

$hybridauth = new Hybrid_Auth( $config );
$allProviders = $hybridauth->getProviders();
if(count($allProviders)) {
    $facebook_status = $twitter_status = $google_status = $live_status = $yahoo_status = $linkedin_status = '';
    //checks if a provider is enabled by the admin
    if(array_key_exists('Facebook', $allProviders)) {
        if($facebook_uid) {
            $facebook_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith Facebook' title='$langProviderConnectWith Facebook' />
                                <input type='hidden' name='facebook_provider_id' value='" . q($facebook_uid) . "' /><br />
                                <a href='$sec?action=delete&provider=Facebook'>$langProviderDeleteConnection</a>";
        } else  $facebook_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=Facebook' />$langProviderConnect</a></div>";
    }
    if(array_key_exists('Twitter', $allProviders)) {
        if($twitter_uid) {
            $twitter_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith Twitter' title='$langProviderConnectWith Twitter' />
            <input type='hidden' name='twitter_provider_id' value='" . q($twitter_uid) . "' /><br />
            <a href='$sec?action=delete&provider=Twitter'>$langProviderDeleteConnection</a></div>";
        } else $twitter_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=Twitter' />$langProviderConnect</a></div>";
    }
    if(array_key_exists('Google', $allProviders)) {
        if($google_uid) {
            $google_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith Google' title='$langProviderConnectWith Google' />
            <input type='hidden' name='google_provider_id' value='" . q($google_uid) . "' /><br />
            <a href='$sec?action=delete&provider=Google'>$langProviderDeleteConnection</a></div>";
        } else $google_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=Google' />$langProviderConnect</a></div>";
    }
    if(array_key_exists('Live', $allProviders)) {
        if($live_uid) {
            $live_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith Live' title='$langProviderConnectWith Live' />
            <input type='hidden' name='live_provider_id' value='" . q($live_uid) . "' /><br />
            <a href='$sec?action=delete&provider=Live'>$langProviderDeleteConnection</a>";
        } else $live_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=Live' />$langProviderConnect</a>";
    }
    if(array_key_exists('Yahoo', $allProviders)) {
        if($yahoo_uid) {
            $yahoo_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith Yahoo' title='$langProviderConnectWith Yahoo' />
            <input type='hidden' name='yahoo_provider_id' value='" . q($yahoo_uid) . "' /><br />
            <a href='$sec?action=delete&provider=Yahoo'>$langProviderDeleteConnection</a></div>";
        } else $yahoo_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=Yahoo' />$langProviderConnect</a></div>";
    }
    if(array_key_exists('LinkedIn', $allProviders)) {
        if($linkedin_uid) {
            $linkedin_status = "<div class='col-sm-10'><img src='$themeimg/tick.png' alt='$langProviderConnectWith LinkedIn' title='$langProviderConnectWith LinkedIn' />
            <input type='hidden' name='linkedin_provider_id' value='" . q($linkedin_uid) . "' /><br />
            <a href='$sec?action=delete&provider=LinkedIn'>$langProviderDeleteConnection</a></div>";
        } else $linkedin_status = "<div class='col-sm-10'><a href='$sec?action=connect&provider=LinkedIn' />$langProviderConnect</a></div>";
    }

$tool_content .= "
    <div class='form-group'>
        <th>$langProviderConnectWith:</th>
    <td>
    <table>
        <tr style='text-align: center;'>";
if($facebook_status) $tool_content .= "<td><img src='$themeimg/facebook.png' alt='Sign-in with Facebook' title='Sign-in with Facebook' /><br />Facebook<br />$facebook_status</td>";
if($twitter_status) $tool_content .= "<td><img src='$themeimg/twitter.png' alt='Sign-in with Twitter' title='Sign-in with Twitter' /><br />Twitter<br />$twitter_status</td>";
if($google_status) $tool_content .= "<td><img src='$themeimg/google.png' alt='Sign-in with Google' title='Sign-in with Google' /><br />Google<br />$google_status</td>";
if($live_status) $tool_content .= "<td><img src='$themeimg/live.png' alt='Sign-in with Microsoft Live' title='Sign-in with Microsoft Live' /><br />Live<br />$live_status</td>";
if($yahoo_status) $tool_content .= "<td><img src='$themeimg/yahoo.png' alt='Sign-in with Yahoo!' title='Sign-in with Yahoo!' /><br />Yahoo<br />$yahoo_status</td>";
if($linkedin_status) $tool_content .= "<td><img src='$themeimg/linkedin.png' alt='Sign-in with LinkedIn' title='Sign-in with LinkedIn' /><br />LinkedIn<br />$linkedin_status</td>";
$tool_content .= "</tr>
    </table>
        </td>
    </tr>
    </div>";

} //endif(count($allProviders)) - in case no providers are enabled, do not show anything

$tool_content .= "<div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
          <a href='display_profile.php' class='btn btn-default'>$langCancel</a>
        </div>
      </fieldset>
      ". generate_csrf_token_form_field() ."  
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
