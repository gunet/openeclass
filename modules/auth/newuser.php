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

/**
 * @file newuser.php
 * @brief user registration process
 */

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$display_captcha = get_config("display_captcha") && function_exists('imagettfbbox');
$data['display_captcha'] = $display_captcha;
if ($display_captcha) {
    $securimage = new Securimage();
    $data['captcha'] = $securimage->getCaptchaHtml([
        'securimage_path' => $urlAppend . 'modules/auth/securimage',
        'input_text' => '',
    ]);
}

$tree = new Hierarchy();
$userObj = new User();

$data['action_bar'] = action_bar(
                                [[
                                    'title' => $langBack,
                                    'url' => 'registration.php',
                                    'icon' => 'fa-reply',
                                    'level' => 'primary',
                                    'button-class' => 'btn-secondary'
                                ]], false);

$data['user_registration'] = get_config('user_registration');
$data['eclass_stud_reg'] = $eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass

if ($eclass_stud_reg == 1) {
    $pageName = "$langUserRequest";
} else {
    $pageName = "$langRegistration $langOfUserS";
}

$data['lang_select_options'] = lang_select_options('localize', "class='form-control' id='UserLang'");
list($js, $html) = $tree->buildUserNodePickerIndirect();
$head_content .= $js;
$data['buildusernode'] = $html;

$data['render_profile_fields_form'] = render_profile_fields_form(array('origin' => 'student_register'));

if (!empty($_GET['provider_id'])) {
    $provider_id = @q($_GET['provider_id']);
} else {
    $provider_id = '';
}

// check if it's valid and the provider enabled in the db
if (isset($_GET['auth']) and is_numeric($_GET['auth']) and $_GET['auth'] > 7 and $_GET['auth'] < 14) {
    $auth = $_GET['auth'];
    $provider_name = $auth_ids[$auth];
    if ($provider_name == "linkedin") {
        $provider_name = "linkedIn";
    }
    $result = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_id = ?d", $auth);
    if (!$result->auth_default) {
        $provider_name = $provider_id = '';
    }
} else {
    $provider_name = '';
}


// authenticate user via hybridauth if requested by URL
$user_data = null;
$registration_errors = [];
if (!empty($provider_name)) {
    require_once 'modules/auth/methods/hybridauth/config.php';
    $config = get_hybridauth_config();

    $hybridauth = new Hybridauth\Hybridauth( $config );
    $allProviders = $hybridauth->getProviders();
    $warning = '';

    // additional layer of checks to verify that the provider is valid via hybridauth middleware
    if (count($allProviders) && in_array(ucfirst($provider_name), $allProviders)) {
        try {
            // create an instance for Hybridauth with the configuration file path as parameter
            $hybridauth = new Hybridauth\Hybridauth( $config );
            // try to authenticate the selected $provider
            $adapter = $hybridauth->authenticate(strtolower($provider_name));
            // grab the user profile and check if the provider_uid
            $user_data = $adapter->getUserProfile();
            $provider_id = $user_data->identifier;
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

            // Display the received error,
            // to know more please refer to Exceptions handling section on the userguide
            switch($e->getCode()) {
                case 0 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError1</span></p>"; break;
                case 1 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError2</span></p>"; break;
                case 2 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError3</span></p>"; break;
                case 3 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError4</span></p>"; break;
                case 4 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError5</span></p>"; break;
                case 5 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError6</span></p>"; break;
                case 6 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError7</span></p>"; $adapter->disconnect(); break;
                case 7 : $warning = "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langProviderError8</span></p>"; $adapter->disconnect(); break;
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
        $user_data_first_name = explode(' ', $user_data->firstName);
        $data['user_data_firstname'] = q($user_data_first_name[0]);
        $data['user_data_lastname'] = q($user_data_first_name[1]);
        $data['user_data_displayName'] =  str_replace(' ', '', $user_data->displayName);
        $data['user_data_email'] = $user_data->email;
        $data['user_data_phone'] = $user_data->phone;
    }
    $data['menuTypeID'] = 0;
    view('modules.auth.newuser', $data);

} else { // submit
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

    if (isset($_POST['account_request'])) {
        $var_arr = array('uname' => true,
            'surname_form' => true,
            'givenname_form' => true,
            'email' => $email_arr_value,
            'phone' => false,
            'am' => $am_arr_value);
    } else {
        if (empty($provider) && empty($_POST['provider_id'])) {
            $var_arr = array('uname' => true,
                'surname_form' => true,
                'givenname_form' => true,
                'password' => true,
                'password1' => true,
                'email' => $email_arr_value,
                'phone' => false,
                'am' => $am_arr_value);
        } else {
            $var_arr = array(
                'uname' => true,
                'surname_form' => true,
                'givenname_form' => true,
                'email' => $email_arr_value,
                'phone' => false,
                'am' => $am_arr_value);
        }
    }

    //add custom profile fields required variables
    augment_registered_posted_variables_arr($var_arr);

    $missing = register_posted_variables($var_arr);

    if (!isset($_POST['department'])) {
        $departments = array();
        if (!isset($_POST['toolbox'])) {
            $missing = false;
        }
    } else {
        $departments = $_POST['department'];
    }

    // check if there are empty fields
    if (!$missing) {
        $registration_errors[] = $langFieldsMissing;
    } else {
        $uname = canonicalize_whitespace($uname);
        if (isset($_POST['account_request'])) {
            // check if exists user request with the same username
            if (user_app_exists($uname)) {
                Session::flash('message', $langUserFree3);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/auth/newuser.php?givenname_form=" . urlencode($givenname_form) .
                    "&surname_form=" . urlencode($surname_form) . "&uname=" . urlencode($uname) .
                    "&email=" . urlencode($email) . "&am=" . urlencode($am) .
                    "&phone=" . urlencode($phone) . "" . augment_url_refill_custom_profile_fields_registr());
            }
        } else {
            if (empty($provider) && empty($_POST['provider_id'])) {
                if ($password != $_POST['password1']) { // check if the two passwords match
                    $registration_errors[] = $langPassTwice;
                }
            }
            // check if the username is already in use
            $username_check = Database::get()->querySingle("SELECT username, email FROM user WHERE username = ?s", $uname);
            if ($username_check) {
                if (isset($_POST['toolbox'])) {
                    $login_details = array();
                    foreach ($var_arr as $var => $req) {
                        $login_details[$var] = $GLOBALS[$var];
                    }
                    Session::flash('login-details', $login_details);
                    Session::flash('username-exists', true);
                    if ($username_check->email === $email) {
                        Session::flash('email-correct', true);
                    }
                    redirect_to_home_page('main/toolbox.php');
                }
                $registration_errors[] = $langUserFree;
            }
        }
        // email validity
        if (!empty($email) and !valid_email($email)) {
            $registration_errors[] = $langEmailWrong;
        } else {
            $email = mb_strtolower(trim($email));
        }

        // captcha check
        if ($display_captcha) {
            $securimage = new Securimage();
            if (!$securimage->check($_POST['captcha_code'])) {
                $registration_errors[] = $langCaptchaWrong;
            }
        }
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
        $config = get_hybridauth_config();

        $hybridauth = new Hybridauth\Hybridauth( $config );
        $allProviders = $hybridauth->getProviders();
        $provider = '';
        $warning = '';

        // check if $_POST['provider'] is valid and enabled
        if (count($allProviders) && in_array(ucfirst($_POST['provider']), $allProviders)) {
            $provider = strtolower($_POST['provider']);
        }
        if (!empty($_POST['provider_id']) && !empty($provider)) {
            // if !empty($provider), it means the provider is existent and valid - it's checked above
            try {
                // create an instance for Hybridauth with the configuration file path as parameter
                $hybridauth = new Hybridauth\Hybridauth( $config );

                // try to authenticate the selected $provider
                $adapter = $hybridauth->authenticate($provider);

                // grab the user profile and check if the provider_uid
                $user_data = $adapter->getUserProfile();
                if ($user_data->identifier) {
                    $result = Database::get()->querySingle("SELECT uid FROM user_ext_uid
                        WHERE uid = ?s AND auth_id = ?d", $user_data->identifier, $auth);
                    if($result) {
                        $registration_errors[] = $langProviderError;
                    } //the provider user id already exists the the db. show an error.
                }

            } catch (Exception $e) {
                // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                // let hybridauth forget all about the user so we can try to authenticate again.

                // Display the received error,
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

        // user account request
        if (isset($_POST['account_request'])) {
            $res = Database::get()->query("INSERT INTO user_request SET
                    givenname = ?s, 
                    surname = ?s, 
                    username = ?s, 
                    email = ?s,
                    faculty_id = ?d, 
                    phone = ?s,
                    state = 1, 
                    status = " . USER_STUDENT . ",
                    verified_mail = $verified_mail, 
                    date_open = " . DBHelper::timeAfter() . ",
                    comment = ?s, 
                    lang = ?s, 
                    request_ip = '" . Log::get_client_ip() . "'",
                $givenname_form, $surname_form, $uname, $email,
                $_POST['department'], $phone, $_POST['usercomment'], $language);

            $request_id = $res?->lastInsertID;

            if (!$vmail) { // email verification not needed
                //----------------------------- Email Request Message --------------------------
                $dep_body = $tree->getFullPath($_POST['department']);
                $header_html_topic_notify = "<!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$mailbody1</div>
                            </div>
                        </div>";

                $body_html_topic_notify = "<!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div id='mail-body-inner'>
                        $mailbody2 $givenname $surname $mailbody3 $mailbody4 $mailbody5 $mailbody8
                            <ul id='forum-category'>
                                <li><span><b>$langFaculty:</b></span> <span>$dep_body</span></li>
                                <li><span><b>$langComments:</b></span> <span>$usercomment</a></span></li>
                                <li><span><b>$langAm :</b></span> <span>$am</span></li>
                                <li><span><b>$langProfUname:</b></span> <span> $username </span></li>
                                <li><span><b>$langProfEmail:</b></span> <span> $usermail </span></li>
                                <li><span><b>$contactphone:</b></span> <span> $userphone </span></li>
                            </ul><br><br>$logo
                        </div>
                    </div>";

                $MailMessage = $header_html_topic_notify.$body_html_topic_notify;
                $plainMailMessage = html2text($MailMessage);
                $emailAdministrator = get_config('email_sender');
                if (!send_mail_multipart($siteName, $emailAdministrator, '', $emailhelpdesk, $mailsubject2, $plainMailMessage, $MailMessage)) {
                    $data['email_errors'] = $email_errors = true;
                }
            } else { // email needs verification -> mail user
                $data['email_errors'] = $email_errors = false;
                $hmac = token_generate($uname . $email . $request_id);
                $subject = $langMailVerificationSubject;
                $emailhelpdesk = get_config('email_helpdesk');
                $emailAdministrator = get_config('email_sender');

                $activateLink = "<a href='{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;rid=$request_id'>{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$request_id</a>";;

                $header_html_topic_notify = "<!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'>$mailbody1</div>
                        </div>
                    </div>";

                    $body_html_topic_notify = "<!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div id='mail-body-inner'>".
                                sprintf($langMailVerificationBody1, $activateLink)."
                        </div>
                    </div>";

                $MailMessage = $header_html_topic_notify . $body_html_topic_notify;
                $plainMailMessage = html2text($MailMessage);

                if (!send_mail_multipart($siteName, $emailAdministrator, '', $email, $subject, $plainMailMessage, $MailMessage)) {
                    $data['email_errors'] = $email_errors = true;
                }
            }
            $data['vmail'] = $vmail;

        } else { // new user account
            if (empty($provider) && empty($_POST['provider_id'])) {
                $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $password_encrypted = $provider;
            }
            // check if hybridauth provider and provider user id is used (the
            // validity of both is checked on a previous step in this script)
            if (empty($provider) && empty($_POST['provider_id'])) {
                $q1 = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email,
                                     status, am, phone, registered_at, expires_at,
                                     lang, verified_mail, whitelist, description)
                          VALUES (?s, ?s, ?s, '$password_encrypted', ?s, " . USER_STUDENT . ", ?s, ?s, " . DBHelper::timeAfter() . ",
                                  DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND), ?s, $verified_mail, '', '')",
                    $surname_form, $givenname_form, $uname, $email, $am, $phone, $language);
            } else {
                $q1 = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email,
                    status, am, phone, registered_at, expires_at,
                    lang, verified_mail, whitelist, description)
                    VALUES (?s, ?s, ?s, '$password_encrypted', ?s, " . USER_STUDENT . ", ?s, ?s, " . DBHelper::timeAfter() . ",
                                  DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND), ?s, $verified_mail, '', '')",
                    $surname_form, $givenname_form, $uname, $email, $am, $phone, $language);
                if ($q1) {
                    Database::get()->query('INSERT INTO user_ext_uid
                    SET user_id = ?d, auth_id = ?d, uid = ?s',
                        $q1->lastInsertID, $auth, $user_data->identifier);
                }
            }
            $data['email_errors'] = $email_errors = false;
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
                            <li><span><b>$langAddress $siteName:</b></span> <span><a href='$urlServer'>$urlServer</a></span></li>
                        </ul>
                        <p>" . ($vmail ? "$langMailVerificationSuccess<br>$langMailVerificationClick <a href='{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id'>{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id</a>" : "") .
                        "<br><br>" . "$langProblem" . "<br><br><br>" . "$langFormula" .
                        "<br>" . "$administratorName" . "<br><br>" .
                        "$langTel: $telephone " . "<br>" .
                        "$langEmail: $emailhelpdesk" . "</p>
                    </div>
                </div>";

            $html_topic_notify = $header_html_topic_notify . $body_html_topic_notify;

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
                             VALUES (?d, ?s, " . DBHelper::timeAfter() . ", 'LOGIN')", $uid, Log::get_client_ip());
                $_SESSION['uid'] = $uid;
                $_SESSION['status'] = USER_STUDENT;
                $_SESSION['givenname'] = $givenname_form;
                $_SESSION['surname'] = $surname_form;
                $_SESSION['uname'] = $uname;
                $session->setLoginTimestamp();
            }
            $data['user_msg'] = $user_msg;
            $data['vmail'] = $vmail;
        }
        $data['menuTypeID'] = 0;
        view('modules.auth.newuser', $data);
    } else { // errors exist
        $provider_name = '';
        $provider_id ='';
        foreach ($registration_errors as $error) {
            Session::flash('message',"$error");
            Session::flash('alert-class', 'alert-danger');
        }
        redirect_to_home_page("modules/auth/newuser.php?givenname_form=" . urlencode($givenname_form) .
            "&surname_form=" . urlencode($surname_form) .
            "&uname=" . urlencode($uname) .
            "&email=" . urlencode($email) .
            "&am=" . urlencode($am) .
            "&phone=" . urlencode($phone) . augment_url_refill_custom_profile_fields_registr());
    }
} // end of registration

unset($uid);
