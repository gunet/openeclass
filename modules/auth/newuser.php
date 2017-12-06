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
 * @file newuser.php
 * @brief user registration process
 */
use Hautelook\Phpass\PasswordHash;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/auth/auth.inc.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$display_captcha = get_config("display_captcha") && function_exists('imagettfbbox');
$data['display_captcha'] = $display_captcha;
$data['captcha'] = "{$urlAppend}include/securimage/securimage_show.php";

$tree = new Hierarchy();
$userObj = new User();

load_js('jstree3');
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

$pageName = $langUserDetails;
$navigation[] = array("url" => "registration.php", "name" => $langNewUser);

$data['action_bar'] = action_bar(
                                [[
                                    'title' => $langBack,
                                    'url' => 'registration.php',
                                    'icon' => 'fa-reply',
                                    'level' => 'primary-label',
                                    'button-class' => 'btn-default'
                                ]], false);

$data['user_registration'] = get_config('user_registration');
$data['eclass_stud_reg'] = get_config('eclass_stud_reg'); // student registration via eclass

$data['lang_select_options'] = lang_select_options('localize', "class='form-control'");
list($js, $html) = $tree->buildUserNodePickerIndirect();
$head_content .= $js;
$data['buildusernode'] = $html;

$data['render_profile_fields_form'] = render_profile_fields_form(array('origin' => 'student_register'));

if(!empty($_GET['provider_id'])) {
    $provider_id = @q($_GET['provider_id']);
} else {
    $provider_id = '';
}
        
// check if it's valid and the provider enabled in the db
if (isset($_GET['auth']) and is_numeric($_GET['auth']) and $_GET['auth'] > 7 and $_GET['auth'] < 14) {
    $auth = $_GET['auth'];
    $provider_name = $auth_ids[$auth];
    if($provider_name == "linkedin") $provider_name = "linkedIn";
    $result = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_id = ?d", $auth);
    if (!$result->auth_default) {
        $provider_name = $provider_id = '';
    }
} else {
    $provider_name = '';
}

// authenticate user via hybridauth if requested by URL
$user_data = null;
if (!empty($provider_name)) {
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();

    $hybridauth = new Hybrid_Auth( $config );
    $allProviders = $hybridauth->getProviders();
    $warning = '';

    // additional layer of checks to verify that the provider is valid via hybridauth middleware
    if (count($allProviders) && array_key_exists(ucfirst($provider_name), $allProviders)) {
        try {
            // create an instance for Hybridauth with the configuration file path as parameter
            $hybridauth = new Hybrid_Auth($config);

            // try to authenticate the selected $provider
            $adapter = $hybridauth->authenticate(strtolower($provider_name));

            // grab the user profile and check if the provider_uid
            $user_data = $adapter->getUserProfile();
            if ($user_data->identifier) {
                $result = Database::get()->querySingle("SELECT uid FROM user_ext_uid
                    WHERE uid = ?s AND auth_id = ?d", $user_data->identifier, $auth);
                if ($result) {
                    $registration_errors[] = $langProviderError9; //the provider user id already exists the the db. show an error.
                } else {
                    $provider_id = $user_data->identifier;
                }
            }
        } catch (Exception $e) {
            // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
            // let hybridauth forget all about the user so we can try to authenticate again.

            // Display the recived error,
            // to know more please refer to Exceptions handling section on the userguide
            switch($e->getCode()) {
                case 0 : $warning = "<p class='alert alert-info'>$langProviderError1</p>"; break;
                case 1 : $warning = "<p class='alert alert-info'>$langProviderError2</p>"; break;
                case 2 : $warning = "<p class='alert alert-info'>$langProviderError3</p>"; break;
                case 3 : $warning = "<p class='alert alert-info'>$langProviderError4</p>"; break;
                case 4 : $warning = "<p class='alert alert-info'>$langProviderError5</p>"; break;
                case 5 : $warning = "<p class='alert alert-info'>$langProviderError6</p>"; break;
                case 6 : $warning = "<p class='alert alert-info'>$langProviderError7</p>"; $adapter->logout(); break;
                case 7 : $warning = "<p class='alert alert-info'>$langProviderError8</p>"; $adapter->logout(); break;
            }
        }
    }
}

// display form
if (!isset($_POST['submit'])) {
    if (get_config('email_required')) {
        $email_message = $langCompulsory;
    } else {
        $email_message = $langOptional;
    }
    if (get_config('am_required')) {
        $am_message = $langCompulsory;
    } else {
        $am_message = $langOptional;
    }

    $data['user_data_firstname'] = $data['user_data_lastname'] = $data['user_data_displayName'] = $data['user_data_email'] = $data['user_data_am'] = $data['user_data_phone'] = '';
    if (isset($_GET['givenname_form'])) {
        $data['user_data_firstname'] = $_GET['givenname_form'];
    }
    if (isset($_GET['surname_form'])) {
        $data['user_data_lastname'] = $_GET['surname_form'];
    }
    if (isset($_GET['uname'])) {
        $data['user_data_displayName'] = $_GET['uname'];
    }
    if (isset($_GET['email'])) {
        $data['user_data_email'] = $_GET['email'];
    }
    if (isset($_GET['am'])) {
        $data['user_data_am'] = $_GET['am'];
    }
    if (isset($_GET['phone'])) {
        $data['user_data_phone'] = $_GET['phone'];
    }                
    if ($user_data) {
        $data['user_data_firstname'] = $user_data->firstName;
        $data['user_data_lastname'] = $user_data->lastName;
        $data['user_data_displayName'] =  str_replace(' ', '', $user_data->displayName);
        $data['user_data_email'] = $user_data->email;
        $data['user_data_phone'] = $user_data->phone;
    }
    $data['menuTypeID'] = 0;
    view('modules.auth.newuser', $data);

} else {
    if (get_config('email_required')) {
        $email_arr_value = true;
    } else {
        $email_arr_value = false;
    }
    if (get_config('am_required')) {
        $am_arr_value = true;
    } else {
        $am_arr_value = false;
    }
    
    $var_arr = array('uname' => true,
                    'surname_form' => true,
                    'givenname_form' => true,
                    'password' => true,
                    'password1' => true,
                    'email' => $email_arr_value,
                    'phone' => false,
                    'am' => $am_arr_value);
    
    //add custom profile fields required variables
    augment_registered_posted_variables_arr($var_arr);
    
    $missing = register_posted_variables($var_arr);

    if (!isset($_POST['department'])) {
        $departments = array();
        $missing = false;
    } else {
        $departments = arrayValuesDirect($_POST['department']);
    }

    $registration_errors = array();
    // check if there are empty fields
    if (!$missing) {
        $registration_errors[] = $langFieldsMissing;
    } else {
        $uname = canonicalize_whitespace($uname);
        // check if the username is already in use
        $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $uname);
        if ($username_check) {
            $registration_errors[] = $langUserFree;
        }
        if ($display_captcha) {
            // captcha check
            require_once 'include/securimage/securimage.php';
            $securimage = new Securimage($options = array('perturbation' => '0.9'));
            if ($securimage->check($_POST['captcha_code']) == false) {
                $registration_errors[] = $langCaptchaWrong;
            }
        }
    }
    if (!empty($email) and !Swift_Validate::email($email)) {
        $registration_errors[] = $langEmailWrong;
    } else {
        $email = mb_strtolower(trim($email));
    }
    if ($password != $_POST['password1']) { // check if the two passwords match
        $registration_errors[] = $langPassTwice;
    }
    //check for validation errors in custom profile fields
    $cpf_check = cpf_validate_format();
    if ($cpf_check[0] === false) {
        unset($cpf_check[0]);
        foreach ($cpf_check as $cpf_error) {
            $registration_errors[] = $cpf_error;
        }
    }

    // validate HybridAuth provider and user id and check if it's already in the db (shouldn't be
    // because the user would be logged in the system rather than redirected here)
    // check if there are any available alternative providers for authentication
    if (!empty($_POST['provider_id'])) {
        require_once 'modules/auth/methods/hybridauth/config.php';
        require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
        $config = get_hybridauth_config();

        $hybridauth = new Hybrid_Auth( $config );
        $allProviders = $hybridauth->getProviders();
        $provider = '';
        $warning = '';

        // check if $_POST['provider'] is valid and enabled
        if (count($allProviders) && array_key_exists(ucfirst($_POST['provider']), $allProviders)) {
            $provider = strtolower($_POST['provider']);
        }
        if (!empty($_POST['provider_id']) && !empty($provider)) {
            // if !empty($provider), it means the provider is existent and valid - it's checked above
            try {
                // create an instance for Hybridauth with the configuration file path as parameter
                $hybridauth = new Hybrid_Auth($config);

                // try to authenticate the selected $provider
                $adapter = $hybridauth->authenticate($provider);

                // grab the user profile and check if the provider_uid
                $user_data = $adapter->getUserProfile();
                if ($user_data->identifier) {
                    $result = Database::get()->querySingle("SELECT uid FROM user_ext_uid
                        WHERE uid = ?s AND auth_id = ?d", $user_data->identifier, $auth);
                    if($result) $registration_errors[] = $langProviderError; //the provider user id already exists the the db. show an error.
                }

            } catch (Exception $e) {
                // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                // let hybridauth forget all about the user so we can try to authenticate again.

                // Display the recived error,
                // to know more please refer to Exceptions handling section on the userguide
                switch($e->getCode()) {
                    case 0 : $warning = "<p class='alert1'>$langProviderError1</p>"; break;
                    case 1 : $warning = "<p class='alert1'>$langProviderError2</p>"; break;
                    case 2 : $warning = "<p class='alert1'>$langProviderError3</p>"; break;
                    case 3 : $warning = "<p class='alert1'>$langProviderError4</p>"; break;
                    case 4 : $warning = "<p class='alert1'>$langProviderError5</p>"; break;
                    case 5 : $warning = "<p class='alert1'>$langProviderError6</p>"; break;
                    case 6 : $warning = "<p class='alert1'>$langProviderError7</p>"; $adapter->logout(); break;
                    case 7 : $warning = "<p class='alert1'>$langProviderError8</p>"; $adapter->logout(); break;
                }
            }
        } else {
            // error. the provider is not valid or not enabled
            $registration_errors[] = $langProviderError . ': ' . $warning;
        }
    }

    if (count($registration_errors) == 0) {
        if (get_config('email_verification_required') && !empty($email)) {
            $verified_mail = 0;
            $vmail = TRUE;            
        } else {
            $verified_mail = 2;
            $vmail = FALSE;
        }

        $hasher = new PasswordHash(8, false);
        $password_encrypted = $hasher->HashPassword($password);

        // check if hybridauth provider and provider user id is used (the
        // validity of both is checked on a previous step in this script)
        if (empty($provider) && empty($_POST['provider_id'])) {
            $q1 = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email,
                                     status, am, phone, registered_at, expires_at,
                                     lang, verified_mail, whitelist, description)
                          VALUES (?s, ?s, ?s, '$password_encrypted', ?s, " . USER_STUDENT . ", ?s, ?s, " . DBHelper::timeAfter() . ",
                                  " . DBHelper::timeAfter(get_config('account_duration')) . ", ?s, $verified_mail, '', '')",
                                $surname_form, $givenname_form, $uname, $email, $am, $phone, $language);
        } else {
            $q1 = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email,
                    status, am, phone, registered_at, expires_at,
                    lang, verified_mail, whitelist, description)
                    VALUES (?s, ?s, ?s, '$password_encrypted', ?s, " . USER_STUDENT . ", ?s, ?s, " . DBHelper::timeAfter() . ",
                                  " . DBHelper::timeAfter(get_config('account_duration')) . ", ?s, $verified_mail, '', '')",
                    $surname_form, $givenname_form, $uname, $email, $am, $phone, $language);
            if ($q1) {
                Database::get()->query('INSERT INTO user_ext_uid
                    SET user_id = ?d, auth_id = ?d, uid = ?s',
                    $q1->lastInsertID, $auth, $user_data->identifier);
            }
        }

        $last_id = $q1->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $last_id);
        $userObj->refresh($last_id, $departments);
        user_hook($last_id);
        
        //fill custom profile fields
        process_profile_fields_data(array('uid' => $last_id, 'origin' => 'student_register'));
        
        if ($vmail) {
            $hmac = token_generate($uname . $email . $last_id);
        }

        $emailsubject = "$langYourReg $siteName";
        $telephone = get_config('phone');
        $administratorName = get_config('admin_name');
        $emailhelpdesk = get_config('email_helpdesk');


        $header_html_topic_notify = "<!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$langYouAreReg $siteName</div>
            </div>
        </div>";

        $body_html_topic_notify = "<!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div id='mail-body-inner'>
            <p>$langSettings</p>
                <ul id='forum-category'>
                    <li><span><b>$langUsername:</b></span> <span>$uname</span></li>
                    <li><span><b>$langPass:</b></span> <span>$password</span></li>
                    <li><span><b>$langAddress $siteName:</b></span> <span><a href='$urlServer'>$urlServer</a></span></li>
                </ul>
                <p>".($vmail ? "$langMailVerificationSuccess<br>$langMailVerificationClick <a href='{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id'>{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id</a>" : "") .
                "<br><br>"."$langProblem"."<br><br><br>"."$langFormula" .
                "<br>"."$administratorName" ."<br><br>".
                "$langTel: $telephone " ."<br>".
                "$langEmail: $emailhelpdesk"."</p>
            </div>
        </div>";

        $html_topic_notify = $header_html_topic_notify.$body_html_topic_notify;

        $emailPlainBody = html2text($html_topic_notify);

        // send email to user
        if (!empty($email)) {
            send_mail_multipart('', '', '', $email, $emailsubject, $emailPlainBody, $html_topic_notify);
            $user_msg = $langPersonalSettings;
        } else {
            $user_msg = $langPersonalSettingsLess;
        }
        // login user if not verification needed
        if (!$vmail) {            
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname FROM user WHERE id = ?d", $last_id);
            $uid = $myrow->id;
            $surname = $myrow->surname;
            $givenname = $myrow->givenname;

            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                             VALUES (?d, ?s, NOW(), 'LOGIN')", $uid, Log::get_client_ip());
            $_SESSION['uid'] = $uid;
            $_SESSION['status'] = USER_STUDENT;
            $_SESSION['givenname'] = $givenname_form;
            $_SESSION['surname'] = $surname_form;
            $_SESSION['uname'] = $uname;
            $session->setLoginTimestamp();            
        }
        $data['user_msg'] = $user_msg;
        $data['vmail'] = $vmail;        
        $data['menuTypeID'] = 0;
        view('modules.auth.newuser', $data);
    } else { // errors exist
        $provider_name = '';
        $provider_id ='';
        foreach ($registration_errors as $error) {
            Session::Messages("$error", 'alert-danger');
        }
        redirect_to_home_page("modules/auth/newuser.php?givenname_form=" . urlencode($givenname_form) . "&surname_form=" . urlencode($surname_form) . "&uname=" . urlencode($uname) . "&email=" . urlencode($email) . "&am=" . urlencode($am) . "&phone=" . urlencode($phone) . "" . augment_url_refill_custom_profile_fields_registr() . "");        
    }
} // end of registration

unset($uid);