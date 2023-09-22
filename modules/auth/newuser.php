<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/auth/auth.inc.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$display_captcha = get_config("display_captcha") && function_exists('imagettfbbox');

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

$pageName = "$langRegistration $langOfUserS";
$navigation[] = array("url" => "registration.php", "name" => $langRegistration);

$user_registration = get_config('user_registration');
$eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass

if (!$user_registration or $eclass_stud_reg != 2) {
    $tool_content .= "<div class='alert alert-info'>$langStudentCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

if(!empty($_GET['provider_id'])) $provider_id = @q($_GET['provider_id']); else $provider_id = '';

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
                case 0 : $warning = "<p class='alert alert-info'>$langProviderError1</p>"; break;
                case 1 : $warning = "<p class='alert alert-info'>$langProviderError2</p>"; break;
                case 2 : $warning = "<p class='alert alert-info'>$langProviderError3</p>"; break;
                case 3 : $warning = "<p class='alert alert-info'>$langProviderError4</p>"; break;
                case 4 : $warning = "<p class='alert alert-info'>$langProviderError5</p>"; break;
                case 5 : $warning = "<p class='alert alert-info'>$langProviderError6</p>"; break;
                case 6 : $warning = "<p class='alert alert-info'>$langProviderError7</p>"; $adapter->disconnect(); break;
                case 7 : $warning = "<p class='alert alert-info'>$langProviderError8</p>"; $adapter->disconnect(); break;
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

    if (count($registration_errors) != 0) {
        // errors exist (from hybridauth) - show message
        $tool_content .= "<div class='alert alert-danger'>";
        foreach ($registration_errors as $error) {
            $tool_content .= " $error";
        }
        $tool_content .= "</div>";
        $provider_name = '';
        $provider_id ='';
    }

    $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "{$urlAppend}modules/auth/registration.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')), false);

    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='$_SERVER[REQUEST_URI]' method='post' onsubmit='return validateNodePickerForm();'>
                <div class='form-group'>
                    <label for='Name' class='col-sm-2 control-label'>$langName:</label>
                    <div class='col-sm-10'>";
                        if ($user_data) {
                            $user_data_first_name = explode(' ', $user_data->firstName);
                            $tool_content .= "<p class='form-control-static'>" . q($user_data_first_name[0]) . "</p>";
                            $tool_content .= "<input type='hidden' name='givenname_form' value = '" . $user_data_first_name[0] . "'>";
                        } elseif (isset($_GET['givenname_form'])) {
                            $tool_content .= "<input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '" . q($_GET['givenname_form']) . "' placeholder='$langName'>";
                        } else {
                            $tool_content .= "<input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '' placeholder='$langName'>";
                        }
                    $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <label for='SurName' class='col-sm-2 control-label'>$langSurname:</label>
                    <div class='col-sm-10'>";
                        if ($user_data) {
                            $user_data_surname = explode(' ', $user_data->firstName);
                            $tool_content .= "<p class='form-control-static'>" . q($user_data_surname[1]) . "</p>";
                            $tool_content .= "<input type='hidden' name='surname_form' value = '" . $user_data_surname[1] . "'>";
                        }  elseif (isset($_GET['surname_form'])) {
                            $tool_content .= "<input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '" . q($_GET['surname_form']) . "' placeholder='$langSurname'>";
                        } else {
                            $tool_content .= "<input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '' placeholder='$langSurname'>";
                        }
                    $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <label for='UserName' class='col-sm-2 control-label'>$langUsername:</label>
                    <div class='col-sm-10'>";
                        if ($user_data) {
                            $value_username = q(str_replace(' ', '', $user_data->displayName));
                            $tool_content .= "<p class='form-control-static'>" . $value_username . "</p>";
                            $tool_content .= "<input type='hidden' name='uname' value='$value_username'>";
                        } elseif (isset($_GET['uname'])) {
                            $tool_content .= "<input class='form-control' type='text' name='uname' value='" . q($_GET['uname']) . "' size='30' maxlength='100' autocomplete='off' placeholder='$langUserNotice'>";
                        } else {
                            $tool_content .= "<input class='form-control' type='text' name='uname' size='30' maxlength='100' autocomplete='off' placeholder='$langUserNotice'>";
                        }
                $tool_content .= "</div>
                </div>";

                if (empty($provider_name) && empty($provider_id)) {
                    $tool_content .= "<div class='form-group' >
                            <label for='UserPass' class='col-sm-2 control-label'> $langPass:</label >
                            <div class='col-sm-10' >
                                <input class='form-control' type = 'password' name = 'password1' size = '30' maxlength = '30' autocomplete = 'off' id = 'password' placeholder = '$langUserNotice' ><span id = 'result' ></span >
                            </div >
                        </div >
                        <div class='form-group' >
                          <label for='UserPass2' class='col-sm-2 control-label'> $langConfirmation:</label >
                            <div class='col-sm-10' >
                                <input class='form-control' type = 'password' name = 'password' size = '30' maxlength = '30' autocomplete = 'off' />
                            </div >
                        </div >";
                    }
                $tool_content .= "<div class='form-group'>
                    <label for='UserEmail' class='col-sm-2 control-label'>$langEmail:</label>
                    <div class='col-sm-10'>";
                    if ($user_data) {
                        $value_email = q($user_data->email);
                    } elseif (isset($_GET['email'])) {
                        $value_email = q($_GET['email']);
                    } else {
                        $value_email = '';
                    }
                    $tool_content .= "<input class='form-control' type='text' name='email' size='30' maxlength='100' value='$value_email' placeholder='$email_message'>";
                    $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <label for='UserAm' class='col-sm-2 control-label'>$langAm:</label>
                    <div class='col-sm-10'>";
                    if (isset($_GET['am'])) {
                        $value_am = q($_GET['am']);
                    } else {
                        $value_am = '';
                    }
                    $tool_content .= "<input class='form-control' type='text' name='am' size='20' maxlength='20' value='$value_am' placeholder='$am_message'>";
                    $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <label for='UserPhone' class='col-sm-2 control-label'>$langPhone:</label>
                    <div class='col-sm-10'>";
                    if ($user_data) {
                        $value_phone = q($user_data->phone);
                    } elseif (isset($_GET['phone'])) {
                        $value_phone = q($_GET['phone']);
                    } else {
                        $value_phone = '';
                    }
                    $tool_content .= "<input class='form-control' type='text' name='phone' size='20' maxlength='20' value='$value_phone' placeholder='$langOptional'>";
                    $tool_content .= "</div>
                </div>
                <div class='form-group'>
                  <label for='UserFac' class='col-sm-2 control-label'>$langFaculty:</label>
                    <div class='col-sm-10'>";
                list($js, $html) = $tree->buildUserNodePicker();
                $head_content .= $js;
                $tool_content .= $html;
                $tool_content .= "</div>
                </div>
                <div class='form-group'>
                  <label for='UserLang' class='col-sm-2 control-label'>$langLanguage:</label>
                  <div class='col-sm-10'>";
                $tool_content .= lang_select_options('localize', "class='form-control'");
                $tool_content .= "</div>
                </div>";
                if ($display_captcha) {
                    $securimage = new Securimage();
                    $captchaHtml = $securimage->getCaptchaHtml([
                        'securimage_path' => $urlAppend . 'modules/auth/securimage',
                        'input_text' => '',
                    ]);
                    $tool_content .= "<div class='form-group'>
                          <label for='captcha_code' class='col-sm-2 control-label'>$langCaptcha:</label>
                          <div class='col-sm-10'>$captchaHtml</div>
                        </div>";
                }
            //add custom profile fields
            $tool_content .= render_profile_fields_form(array('origin' => 'student_register'));

            //check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form
            if(!empty($provider_name) && !empty($provider_id)) {
                $tool_content .= "<div class='form-group'>
                  <label for='UserLang' class='col-sm-2 control-label'>$langProviderConnectWith:</label>
                  <div class='col-sm-10'><p class='form-control-static'>
                    <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "'>&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>$langProviderConnectWithTooltip</small></p>
                  </div>
                  <div class='col-sm-offset-2 col-sm-10'>
                    <input type='hidden' name='provider' value='" . $provider_name . "' />
                    <input type='hidden' name='provider_id' value='" . $provider_id . "' />
                  </div>
                  </div>";
            }
            $tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'>
                        <input class='btn btn-primary' type='submit' name='submit' value='" . q($langRegistration) . "' />
                  </div>
                </div>";

          $tool_content .= "
          </form>
      </div>";

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
        if ($display_captcha) {
            // captcha check
            $securimage = new Securimage();
            if (!$securimage->check($_POST['captcha_code'])) {
                $registration_errors[] = $langCaptchaWrong;
            }
        }
    }
    if (!empty($email) and !valid_email($email)) {
        $registration_errors[] = $langEmailWrong;
    } else {
        $email = mb_strtolower(trim($email));
    }

    if (empty($provider) && empty($_POST['provider_id'])) {
        if ($password != $_POST['password1']) { // check if the two passwords match
            $registration_errors[] = $langPassTwice;
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
                    if($result) $registration_errors[] = $langProviderError; //the provider user id already exists the the db. show an error.
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
        // verification needed
        if ($vmail) {
                $tool_content .= "<div class='alert alert-info'>$langMailVerificationSuccess
                                $langMailVerificationSuccess2 <br><br><small>$langMailVerificationNote</small>
                                 <br><br>$langClick <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</div>";
        } else { // login user
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname FROM user WHERE id = ?d", $last_id);
            $uid = $myrow->id;
            $surname = $myrow->surname;
            $givenname = $myrow->givenname;

            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                             VALUES (?d, ?s, " . DBHelper::timeAfter() .", 'LOGIN')", $uid, Log::get_client_ip());
            $_SESSION['uid'] = $uid;
            $_SESSION['status'] = USER_STUDENT;
            $_SESSION['givenname'] = $givenname_form;
            $_SESSION['surname'] = $surname_form;
            $_SESSION['uname'] = $uname;
            $session->setLoginTimestamp();

            $tool_content .= "<div class='alert alert-success'>$user_msg <br><br>";
            $tool_content .= "$langClick
                                <a href='$urlServer' class='mainpage'>$langHere</a> $langPersonalSettingsMore
                              <ul>
                                <li>$langPersonalSettingsMore1</li>
                                <li>$langPersonalSettingsMore2</li>
                              </ul>
                            </div>";
        }
    } else {
        if (isset($_POST['toolbox'])) {
            $login_details = array();
            foreach ($var_arr as $var => $req) {
                $login_details[$var] = $GLOBALS[$var];
            }
            Session::flash('login-details', $login_details);
            Session::flash('registration-errors', $registration_errors);
            redirect_to_home_page('main/toolbox.php');
        }
        // errors exist - registration failed
        $tool_content .= "<div class='alert alert-danger text-center'>";
        foreach ($registration_errors as $error) {
            $tool_content .= " $error";
        }
        $tool_content .= "</div>";

        $url = "$_SERVER[SCRIPT_NAME]?givenname_form=" . urlencode($givenname_form) .
                "&amp;surname_form=" . urlencode($surname_form) .
                "&amp;uname=" . urlencode($uname) .
                "&amp;email=" . urlencode($email) .
                "&amp;am=" . urlencode($am) .
                "&amp;phone=" . urlencode($phone) .
                augment_url_refill_custom_profile_fields_registr() . "";

        $tool_content .= "<div class='text-center'><a class='btn btn-default' href='$url'>$langBack</a></div>";

    }
} // end of registration

unset($uid);
draw($tool_content, 0, null, $head_content);
