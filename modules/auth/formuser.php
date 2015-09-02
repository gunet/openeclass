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

include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
include 'include/lib/hierarchy.class.php';
require_once 'modules/auth/auth.inc.php';

$tree = new Hierarchy();

load_js('jstree3');

$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

$prof = isset($_REQUEST['p']) ? intval($_REQUEST['p']) : 0;
$am = !empty($_REQUEST['am']) ? intval($_REQUEST['am']) : '';
$pageName = $prof ? $langReqRegProf : $langUserRequest;

$user_registration = get_config('user_registration');
$eclass_prof_reg = get_config('eclass_prof_reg');
$eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass

$display_captcha = get_config("display_captcha") && function_exists('imagettfbbox');

// security check
if (!$user_registration) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}
if ($prof and !$eclass_prof_reg) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

if (!$prof and $eclass_stud_reg != 1) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

$am_required = !$prof && get_config('am_required');
$errors = array();

$all_set = register_posted_variables(array(
    'usercomment' => true,
    'givenname' => true,
    'surname' => true,
    'username' => true,
    'userphone' => $prof,
    'usermail' => true,
    'am' => $am_required,
    'department' => true,
    'captcha_code' => false,
    'provider' => false,
    'provider_name' => false,
    'provider_id' => false));

if (!$all_set) {
    $errors[] = $langFieldsMissing;
}

if (!email_seems_valid($usermail)) {
    $errors[] = $langEmailWrong;
    $all_set = false;
} else {
    $usermail = mb_strtolower(trim($usermail));
}

// check if the username is already in use
$username = canonicalize_whitespace($username);
if (user_exists($username)) {
    $errors[] = $langUserFree;
    $all_set = false;
}

// check if exists user request with the same username
if (user_app_exists($username)) {
    $errors[] = $langUserFree3;
    $all_set = false;
}

if ($display_captcha) {
    // captcha check
    include 'include/securimage/securimage.php';
    $securimage = new Securimage();
    if ($securimage->check($captcha_code) == false) {
        $errors[] = $langCaptchaWrong;
        $all_set = false;
    }
}

if(!empty($_GET['provider_id'])) $provider_id = @q($_GET['provider_id']); else $provider_id = '';

//check if it's valid and the provider enabled in the db
if(is_numeric(@$_GET['auth']) && @$_GET['auth'] > 7 && @$_GET['auth'] < 14) {
    $provider_name = $auth_ids[$_GET['auth']];
    $result = Database::get()->querySingle("SELECT auth_enabled FROM auth WHERE auth_name = ?s", $provider_name);
    if($result->auth_enabled != 1) {
        $provider_name = "";
        $provider_id = "";
    }
} else $provider_name = "";

//authenticate user via hybridauth if requested by URL
$user_data = '';
if(!empty($provider_name) || (!empty($_POST['provider']) && !empty($_POST['provider_id']))) {
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();
    
    $hybridauth = new Hybrid_Auth( $config );
    $allProviders = $hybridauth->getProviders();
    $warning = '';
    
    //additional layer of checks to verify that the provider is valid via hybridauth middleware
    if(count($allProviders) && array_key_exists(ucfirst($provider_name), $allProviders)) { 
        try {
            // create an instance for Hybridauth with the configuration file path as parameter
            $hybridauth = new Hybrid_Auth($config);
    
            // try to authenticate the selected $provider
            $adapter = $hybridauth->authenticate(strtolower($provider_name));
    
            // grab the user profile and check if the provider_uid
            $user_data = $adapter->getUserProfile();
            if($user_data->identifier) {
                $result = Database::get()->querySingle("SELECT " . strtolower($provider_name) . "_uid FROM user_request WHERE " . strtolower($provider_name) . "_uid = ?s", $user_data->identifier);
                if($result) $registration_errors[] = $langProviderError9; //the provider user id already exists the the db. show an error.
                    else {
                        $provider_id = $user_data->identifier; 
                        if(empty($givenname)) $givenname = $user_data->firstName;
                        if(empty($surname)) $surname = $user_data->lastName;
                        if(empty($username)) $username = q(str_replace(' ', '', $user_data->displayName));
                        if(empty($usermail)) $usermail = $user_data->email;
                        if(empty($userphone)) $userphone = $user_data->phone;
                    }
            }
        } catch(Exception $e) {
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

if (@count($registration_errors) != 0) {
        // errors exist (from hybridauth) - show message
        $tool_content .= "<div class='alert alert-danger'>";
        foreach ($registration_errors as $error) {
            $tool_content .= " $error";
        }
        $tool_content .= "</div>";
        $provider_name = '';
        $provider_id ='';
        $_GET['auth'] = '';
}

if (isset($_POST['submit'])) {
    foreach ($errors as $message) {
        $tool_content .= "<div class='alert alert-warning'>$message</div>";
    }
}

if ($all_set) {
    $email_verification_required = get_config('email_verification_required');
    $emailhelpdesk = get_config('email_helpdesk');
    if (!$email_verification_required) {
        $verified_mail = 2;
    } else {
        $verified_mail = 0;
    }

    // register user request
    $status = $prof ? USER_TEACHER : USER_STUDENT;
    if(!empty($provider) && !empty($user_data->identifier)) {
        $res = Database::get()->query("INSERT INTO user_request SET
            givenname = ?s, surname = ?s, username = ?s, email = ?s,
            am = ?s, faculty_id = ?d, phone = ?s,
            state = 1, status = $status,
            verified_mail = ?d, date_open = " . DBHelper::timeAfter() . ",
			comment = ?s, lang = ?s, request_ip = ?s, " . $provider . "_uid = ?s",
            $givenname, $surname, $username, $usermail, $am, $department, $userphone, $verified_mail, $usercomment, $language, $_SERVER['REMOTE_ADDR'], $user_data->identifier);
    } else {
        $res = Database::get()->query("INSERT INTO user_request SET
    			givenname = ?s, surname = ?s, username = ?s, email = ?s,
    			am = ?s, faculty_id = ?d, phone = ?s,
    			state = 1, status = $status,
    			verified_mail = ?d, date_open = " . DBHelper::timeAfter() . ",
    			comment = ?s, lang = ?s, request_ip = ?s",
                $givenname, $surname, $username, $usermail, $am, $department, $userphone, $verified_mail, $usercomment, $language, $_SERVER['REMOTE_ADDR']);
    }
    $request_id = $res->lastInsertID;

    // email does not need verification -> mail helpdesk
    if (!$email_verification_required) {
        //----------------------------- Email Request Message --------------------------
        $dep_body = $tree->getFullPath($department);
        $subject = $prof ? $mailsubject : $mailsubject2;
        $MailMessage = $mailbody1 . $mailbody2 . "$givenname $surname\n\n" .
                $mailbody3 . $mailbody4 . $mailbody5 .
                ($prof ? $mailbody6 : $mailbody8) .
                "\n\n$langFaculty: $dep_body\n$langComments: $usercomment\n" .
                "$langAm: $am\n" .
                "$langProfUname: $username\n$langProfEmail : $usermail\n" .
                "$contactphone: $userphone\n\n\n$logo\n\n";
        $emailAdministrator = get_config('email_sender');
        if (!send_mail($siteName, $emailAdministrator, '', $emailhelpdesk, $subject, $MailMessage, $charset, "Reply-To: $usermail")) {
            $tool_content .= "<div class='alert alert-warning'>$langMailErrorMessage&nbsp; <a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</div>";
        }

        // User Message
        $tool_content .= "<div class='alert alert-success'>" .
                ($prof ? $langDearProf : $langDearUser) .
                "!<br />$success</div><p>$infoprof<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
    }
    // email needs verification -> mail user
    else {
        $hmac = token_generate($username . $usermail . $request_id);
        //----------------------------- Email Verification -----------------------
        $subject = $langMailVerificationSubject;
        $MailMessage = sprintf($mailbody1 . $langMailVerificationBody1, $urlServer . 'modules/auth/mail_verify.php?h=' . $hmac . '&rid=' . $request_id);
        $emailhelpdesk = get_config('email_helpdesk');
        $emailAdministrator = get_config('email_sender');
        if (!send_mail($siteName, $emailAdministrator, '', $usermail, $subject, $MailMessage, $charset, "Reply-To: $emailhelpdesk")) {
            $mail_ver_error = sprintf("<div class='alert alert-warning'>" . $langMailVerificationError, $usermail, $urlServer . "modules/auth/registration.php", "<a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</div>");
            $tool_content .= $mail_ver_error;
            draw($tool_content, 0);
            exit;
        }

        // User Message
        $tool_content .= "<div class='alert alert-success'>" .
                ($prof ? $langDearProf : $langDearUser) .
                "<br />$langMailVerificationSuccess
			$langMailVerificationSuccess2</div><br /><p>$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
    }
    draw($tool_content, 0);
    exit();

// first time we visit the form or on error
} else {
    if ($am_required) {
        $am_text = $langCompulsory;
    } else {
        $am_text = $langOptional;
    }

    if ($prof) {
        $langUserData = $langInfoProfReq;
        $phone_star = $langCompulsory;
    } else {
        $langUserData = $langInfoStudReq;
        $phone_star = $langOptional;
    }

    $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "{$urlAppend}modules/auth/registration.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')), false);
    $tool_content .= "<div class='alert alert-info'>$langUserData</div>";
    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?auth=". @$_GET['auth'] ."' method='post'>
        <input type='hidden' name='p' value='$prof'>
        <fieldset>
        <div class='form-group'>
            <label for='Name' class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='givenname' value='" . q($givenname) . "' size='30' maxlength='60' placeholder='$langName'></td>
            </div>
        </div>
        <div class='form-group'>
            <label for='SurName' class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='surname' value='" . q($surname) . "' size='30' maxlength='60' placeholder='$langSurname'>
            </div>
        </div>
        <div class='form-group'>
            <label for='UserPhone' class='col-sm-2 control-label'>$langPhone:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='userphone' value='" . q($userphone) . "' size='20' maxlength='20' placeholder='$phone_star'>
            </div>
        </div>
        <div class='form-group'>
            <label for='UserName' class='col-sm-2 control-label'>$langUsername:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='username' size='30' maxlength='50' value='" . q($username) . "' placeholder='$langUserNotice'>
            </div>
        </div>
        <div class='form-group'>
            <label for='ProfEmail' class='col-sm-2 control-label'>$langProfEmail:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='usermail' value='" . q($usermail) . "' size='30' maxlength='100' placeholder='$langCompulsory'>
            </div>
        </div>";
    if (!$prof) {
        $tool_content .= "<div class='form-group'>
                <label for='ProfEmail' class='col-sm-2 control-label'>$langAm:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='am' value='" . q($am) . "' size='20' maxlength='20' placeholder='$am_text'>
                </div>
            </div>";
    }
    $tool_content .= "<div class='form-group'>
            <label for='ProfComments' class='col-sm-2 control-label'>$langComments:</label>
                <div class='col-sm-10'>
                    <textarea class='form-control' name='usercomment' cols='30' rows='4' placeholder='$profreason'>" . q($usercomment) . "</textarea>
                </div>
            </div>
            <div class='form-group'>
                <label for='ProfComments' class='col-sm-2 control-label'>$langFaculty:</label>
            <div class='col-sm-10'>";
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $department, 'tree' => null, 'where' => "AND node.allow_user = true", 'multiple' => false));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</div></div>";
        $tool_content .= "<div class='form-group'>
              <label for='UserLang' class='col-sm-2 control-label'>$langLanguage:</label>
              <div class='col-sm-10'>";
        $tool_content .= lang_select_options('localize', "class='form-control'");
        $tool_content .= "</div></div>";
    if ($display_captcha) {
        $tool_content .= "<div class='form-group'>
                      <div class='col-sm-offset-2 col-sm-10'><img id='captcha' src='{$urlAppend}include/securimage/securimage_show.php' alt='CAPTCHA Image' /></div><br>
                      <label for='Captcha' class='col-sm-2 control-label'>$langCaptcha:</label>
                      <div class='col-sm-10'><input class='form-control' type='text' name='captcha_code' maxlength='6'/></div>
                    </div>";
    }

//check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form
    if(!empty($provider_name) && !empty($provider_id)) {
        $tool_content .= "<div class='form-group'>
          <label for='UserLang' class='col-sm-2 control-label'>$langProviderConnectWith:</label>
          <div class='col-sm-10'>
            <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "' />&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>$langProviderConnectWithTooltip</small>
          </div>
          <div class='col-sm-offset-2 col-sm-10'>
            <input type='hidden' name='provider' value='" . $provider_name . "' />
            <input type='hidden' name='provider_id' value='" . $provider_id . "' />
          </div>
          </div>";
    }

    $tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'>
                    <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmitNew) . "' />
                    </div></div>
        </fieldset>
      </form>
      </div>";
}

draw($tool_content, 0, null, $head_content);
