<?php

/* ========================================================================
 * Open eClass 3.3
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * @file: altsearch.php
 * @author Karatzidis Stratos <kstratos@uom.gr>
 * @author Vagelis Pitsioygas <vagpits@uom.gr>
 * @description This script/file tries to authenticate the user, using
 * his user/pass pair and the authentication method defined by the admin
 */
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/auth/auth.inc.php';

$tree = new Hierarchy();
$userObj = new User();

load_js('jstree3');

$user_registration = get_config('user_registration');
$alt_auth_stud_reg = get_config('alt_auth_stud_reg'); // user registration via alternative auth methods
$alt_auth_prof_reg = get_config('alt_auth_prof_reg'); // prof registration via alternative auth methods

if (!$user_registration) {
    Session::Messages($langCannotRegister, 'alert-info');
    draw($tool_content, 0);
    exit;
}

if (isset($_POST['auth'])) {
    $auth = intval($_POST['auth']);
    $_SESSION['u_tmp'] = $auth;
} else {
    $auth = isset($_SESSION['u_tmp']) ? $_SESSION['u_tmp'] : 0;
}

if (isset($_SESSION['u_prof'])) {
    $prof = intval($_SESSION['u_prof']);
}

if (!in_array($auth, get_auth_active_methods())) {
    $tool_content .= "<div class='alert alert-danger'>$langCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

if (!$_SESSION['u_prof'] and !$alt_auth_stud_reg) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

if ($_SESSION['u_prof'] and !$alt_auth_prof_reg) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

$phone_required = $prof;

if (!$prof and $alt_auth_stud_reg == 2) {
    $autoregister = TRUE;
} else {
    $autoregister = FALSE;
}
$comment_required = !$autoregister;
$email_required = !$autoregister || get_config('email_required');
$am_required = !$prof && get_config('am_required') &&
    !isset($_SESSION['auth_user_info']['studentid']) && !$_SESSION['auth_user_info']['studentid'];

$pageName = ($prof ? $langReqRegProf : $langUserData) . ' (' . (get_auth_info($auth)) . ')';
$email_message = $langEmailNotice;
$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

register_posted_variables(array('uname' => true, 'passwd' => true,
    'is_submit' => true, 'submit' => true));
$lastpage = 'altnewuser.php?' . ($prof ? 'p=1&amp;' : '') .
        "auth=$auth&amp;uname=" . urlencode($uname);
$navigation[] = array('url' => $lastpage, 'name' => $langConfirmUser);

$errormessage = "<br/><p>$ldapback <a href='$lastpage'>$ldaplastpage</a></p>";
$init_auth = $is_valid = false;

if (!isset($_SESSION['was_validated']) or
        $_SESSION['was_validated']['auth'] != $auth or
        $_SESSION['was_validated']['uname'] != $uname) {
    $init_auth = true;
    // If user wasn't authenticated in the previous step, try
    // an authentication step now:
    // First check for Shibboleth
    if (isset($_SESSION['shib_uname'])) {
        $uname = $_SESSION['shib_uname'];
        $_SESSION['auth_user_info'] = get_shibboleth_user_info();;
        $is_valid = true;
    } elseif ($is_submit or ($auth == 7 and !$submit)) {
        unset($_SESSION['was_validated']);
        if ($auth != 7 and $auth != 6 and
                ($uname === '' or $passwd === '')) {
            $tool_content .= "<div class='alert alert-danger'>$ldapempty $errormessage</div>";
            draw($tool_content, 0);
            exit();
        } else {
            // try to authenticate user
            $auth_method_settings = get_auth_settings($auth);
            if ($auth == 6) {
                redirect_to_home_page('secure/index.php?reg=1' . ($prof ? '&p=1' : ''));
            }
            $is_valid = auth_user_login($auth, $uname, $passwd, $auth_method_settings);
        }

        if ($auth == 7) {
            if (phpCAS::checkAuthentication()) {
                $uname = phpCAS::getUser();
                $cas = get_auth_settings($auth);
                // store CAS released attributes in $_SESSION['auth_user_info']
                get_cas_attrs(phpCAS::getAttributes(), $cas);
                if (!empty($uname)) {
                    $is_valid = true;
                }
            }
        }
    }

    if ($is_valid) { // connection successful
        $_SESSION['was_validated'] = array(
            'auth' => $auth,
            'uname' => $uname,
            'uname_exists' => user_exists($uname));
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langConnNo<br>$langAuthNoValidUser</div>" .
                "<p>&laquo; <a href='$lastpage'>$langBack</a></p>";
    }
} else {
    $is_valid = true;
}

// -----------------------------------------
// registration
// -----------------------------------------
if ($is_valid) {
    $ext_info = !isset($_SESSION['auth_user_info']);
    $ext_mail = !(isset($_SESSION['auth_user_info']['email']) && !empty($_SESSION['auth_user_info']['email']));
    $missing_posted_variables = array();
    if (isset($_POST['p']) and $_POST['p'] == 1) {
        $ok = register_posted_variables(
            array('submit' => false,
                  'uname' => true,
                  'email' => $email_required && $ext_mail,
                  'surname_form' => $ext_info,
                  'givenname_form' => $ext_info,
                  'am' => $am_required,
                  'department' => true,
                  'usercomment' => $comment_required,
                  'userphone' => $phone_required), 'all');
    } else {
        $ok = register_posted_variables(
            array('submit' => false,
                  'email' => $email_required && $ext_mail,
                  'surname_form' => $ext_info,
                  'givenname_form' => $ext_info,
                  'am' => $am_required,
                  'department' => true,
                  'usercomment' => $comment_required,
                  'userphone' => $phone_required), 'all');
    }

    if (!$ok and $submit) {
        $tool_content .= "<div class='alert alert-danger'>$langFieldsMissing</div>";
    }
    $depid = intval(getDirectReference($department));
    if (isset($_SESSION['auth_user_info'])) {
        $givenname_form = $_SESSION['auth_user_info']['givenname'];
        $surname_form = $_SESSION['auth_user_info']['surname'];
        if (!empty($_SESSION['auth_user_info']['studentid'])) {
            $am = $_SESSION['auth_user_info']['studentid'];
        }
        if (!$email and !empty($_SESSION['auth_user_info']['email'])) {
            $email = $_SESSION['auth_user_info']['email'];
        }
    }
    if (!empty($email) and !Swift_Validate::email($email)) {
        $ok = NULL;
        $tool_content .= "<div class='alert alert-danger'>$langEmailWrong</div>";
    } else {
        $email = mb_strtolower(trim($email));
    }

    $tool_content .= $init_auth ? ("<div class='alert alert-success'>$langTheUser $ldapfound.</div>") : '';
    if (@(!empty($_SESSION['was_validated']['uname_exists']) and $_POST['p'] != 1)) {
        $tool_content .= "<div class='alert alert-danger'>$langUserFree<br />
                                <br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</div>";
        draw($tool_content, 0, null, $head_content);
        exit();
    }
    if (!$ok) {
        user_info_form();
        draw($tool_content, 0, null, $head_content);
        exit();
    }
    if ($auth != 1) {
        $password = isset($auth_ids[$auth]) ? $auth_ids[$auth] : '';
    }

    $status = $prof ? USER_TEACHER : USER_STUDENT;
    $greeting = $prof ? $langDearProf : $langDearUser;

    $uname = canonicalize_whitespace($uname);
    // user already exists
    if (user_exists($uname)) {
        $_SESSION['uname_exists'] = 1;
    } elseif (isset($_SESSION['uname_exists'])) {
        unset($_SESSION['uname_exists']);
    }
    // user allready applied for account
    if (user_app_exists($uname)) {
        $_SESSION['uname_app_exists'] = 1;
    } elseif (isset($_SESSION['uname_app_exists'])) {
        unset($_SESSION['uname_app_exists']);
    }

    // register user
    if ($autoregister and empty($_SESSION['uname_exists']) and empty($_SESSION['uname_app_exists'])) {
        if (get_config('email_verification_required') && !empty($email)) {
            $verified_mail = 0;
            $vmail = TRUE;
        } else {
            $verified_mail = 2;
            $vmail = FALSE;
        }

        $authmethods = array('2', '3', '4', '5');
        $q1 = Database::get()->query("INSERT INTO user
                      SET surname = ?s,
                          givenname = ?s,
                          username = ?s,
                          password = ?s,
                          email = ?s,
                          status = " . USER_STUDENT . ",
                          am = ?s,
                          registered_at = " . DBHelper::timeAfter() . ",
                          expires_at = " . DBHelper::timeAfter(get_config('account_duration')) . ",
                          lang = ?s,
                          verified_mail = ?d,
                          whitelist='',
                          description = ''", $surname_form, $givenname_form, $uname, $password, $email, $am, $language, $verified_mail);
        $last_id = $q1->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $last_id);
        $userObj->refresh($last_id, array(intval($depid)));
        user_hook($last_id);

        if ($vmail and !empty($email)) {
            $hmac = token_generate($uname . $email . $last_id);
        }

        // Register a new user
        $password = $auth_ids[$auth];
        $telephone = get_config('phone');
        $administratorName = get_config('admin_name');
        $emailhelpdesk = get_config('email_helpdesk');
        $emailAdministrator = get_config('email_sender');
        $emailsubject = "$langYourReg $siteName";
        $emailbody = "$langDestination $givenname_form $surname_form\n" .
                "$langYouAreReg $siteName $langSettings $uname\n" .
                "$langPassSameAuth\n$langAddress $siteName: " .
                "$urlServer\n" .
                ($vmail ? "\n$langMailVerificationSuccess.\n$langMailVerificationClick\n{$urlServer}modules/auth/mail_verify.php?h=" . $hmac . "&id=" . $last_id . "\n" : "") .
                "$langProblem\n$langFormula" .
                "$administratorName\n" .
                "$langManager $siteName \n$langTel $telephone \n" .
                "$langEmail: $emailhelpdesk";

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
                    <li><span><b>$lang_username:</b></span> <span>$uname</span></li>
                    <li><span><b>$langPassword:</b></span> <span>$langPassSameAuth</span></li>
                    <li><span><b>$langAddress $siteName:</b></span> <span>$urlServer</span></li>
                    </ul>
                    <p>".($vmail ? "$langMailVerificationSuccess<br>$langMailVerificationClick<br><a href='{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id'>{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id</a>" : "")."</p>
                    <p>$langProblem<br><br>$langFormula<br>$administratorName<br>$langManager $siteName<br>$langTel: $telephone<br>$langEmail: $emailhelpdesk</p>

            </div>
        </div>";

        $emailbody = $header_html_topic_notify.$body_html_topic_notify;
        $plainemailbody = html2text($emailbody);

        if (!empty($email)) {
            send_mail_multipart($siteName, $emailAdministrator, '', $email, $emailsubject, $plainemailbody, $emailbody, $charset, "Reply-To: $emailhelpdesk");
        }

        $myrow = Database::get()->querySingle("SELECT id, surname, givenname FROM user WHERE id = ?d", $last_id);
        if ($myrow) {
            $uid = $myrow->id;
            $surname = $myrow->surname;
            $givenname = $myrow->givenname;
        }

        if (!$vmail) {
            Database::get()->query("INSERT INTO loginout SET id_user = $uid, ip = '$_SERVER[REMOTE_ADDR]',`when` = NOW(), action = 'LOGIN'");
            $_SESSION['uid'] = $uid;
            $_SESSION['status'] = USER_STUDENT;
            $_SESSION['givenname'] = $givenname;
            $_SESSION['surname'] = $surname;
            $_SESSION['uname'] = canonicalize_whitespace($uname);
            $session->setLoginTimestamp();

            $tool_content .= "<div class='alert alert-success'><p>$greeting,</p><p>";
            $tool_content .=!empty($email) ? $langPersonalSettings : $langPersonalSettingsLess;
            $tool_content .= "</p></div>
                                                <br /><br />
                                                <p>$langPersonalSettingsMore</p>";
        } else {
            $tool_content .= "<div class='alert alert-success'>" .
                    ($prof ? $langDearProf : $langDearUser) .
                    "!<br />$langMailVerificationSuccess: <strong>$email</strong></div>
                                                <p>$langMailVerificationSuccess4.<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
        }
    } elseif (empty($_SESSION['uname_app_exists'])) {
        $email_verification_required = get_config('email_verification_required');
        if (!$email_verification_required) {
            $verified_mail = 2;
        } else {
            $verified_mail = 0;
        }

        // check if mail address is valid
        if (!empty($email) and !Swift_Validate::email($email)) {
            $tool_content .= "<div class='alert alert-danger'>$langEmailWrong</div>";
            user_info_form();
            draw($tool_content, 0, null, $head_content);
            exit();
        } else {
            $email = mb_strtolower(trim($email));
        }

        // Record user request
        $q1 = Database::get()->query("INSERT INTO user_request SET
                                        givenname = ?s, surname = ?s, username = ?s, password = ?s,
                                        email = ?s, faculty_id = ?d, phone = ?s,
                                        am = ?s, state = 1, status = ?d, verified_mail = ?d,
                                        date_open = " . DBHelper::timeAfter() . ", comment = ?s, lang = ?s,
                                        request_ip = ?s",
                            $givenname_form, $surname_form, $uname, $password, $email, $depid, $userphone,
                            $am, $status, $verified_mail, $usercomment, $language, $_SERVER['REMOTE_ADDR']);
        $request_id = $q1->lastInsertID;
        // email does not need verification -> mail helpdesk
        if (!$email_verification_required) {
            $emailAdministrator = get_config('email_sender');
            $emailhelpdesk = get_config('email_helpdesk');
            // send email



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
                    <p>$mailbody2 $givenname_form $surname_form $mailbody3
                     $mailbody4  $mailbody5 $mailbody6 </p>
            <ul id='forum-category'>
                <li><span><b>$langProfUname:</b></span> <span>$uname</span></li>
                <li><span><b>$contactphone :</b></span> <span>$userphone</span></li>
                <li><span><b>$langProfEmail :</b></span> <span>$email</span></li>
                <li><span><b>$langFaculty:</b></span> <span>".$tree->getFullPath($depid)."</span></li>
                <li><span><b>$langComments:</b></span> <span> $usercomment </span></li>
            </ul>
            <p>$logo</p>
                </div>
            </div>";

            $MailMessage = $header_html_topic_notify.$body_html_topic_notify;
            $plainemailbody = html2text($MailMessage);

            if (!send_mail_multipart($siteName, $emailAdministrator, $gunet, $emailhelpdesk, $mailsubject, $plainemailbody, $MailMessage, $charset, "Reply-To: $email")) {
                $tool_content .= "<div class='alert alert-warning'>$langMailErrorMessage &nbsp; <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a></div>";
                draw($tool_content, 0);
                exit();
            }

            $tool_content .= "<div class='alert alert-success'>$greeting,<br />$success<br /></div><p>$infoprof</p><br />
                          <p>&laquo; <a href='$urlServer'>$langBack</a></p>";
        } else {
            // email needs verification -> mail user
            $hmac = token_generate($uname . $email . $request_id);
            $emailhelpdesk = get_config('email_helpdesk');
            $emailAdministrator = get_config('email_sender');
            $subject = $langMailVerificationSubject;

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
                    sprintf($mailbody1 . $langMailVerificationBody1, "<a href='{$urlServer}modules/auth/mail_verify.php?h=" . $hmac . "&amp;rid=" . $request_id."'>{$urlServer}modules/auth/mail_verify.php?h=" . $hmac . "&amp;rid=" . $request_id ."</a>")."
                </div>
            </div>";

            $MailMessage = $header_html_topic_notify.$body_html_topic_notify;
            $plainemailbody = html2text($MailMessage);

            if (!send_mail_multipart($siteName, $emailAdministrator, '', $email, $subject, $plainemailbody, $MailMessage, $charset, "Reply-To: $emailhelpdesk")) {
                $mail_ver_error = sprintf("<div class='alert alert-warning'>" . $langMailVerificationError, $email, $urlServer . "modules/auth/registration.php", "<a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</div>");
                $tool_content .= $mail_ver_error;
                draw($tool_content, 0);
                exit();
            }
            // User Message
            $tool_content .= "<div class='alert alert-success'>" .
                    ($prof ? $langDearProf : $langDearUser) .
                    "!<br />$langMailVerificationSuccess: <strong>$email</strong></div>
                                        <p>$langMailVerificationSuccess4.<br /><br />$click <a href='$urlServer'
                                        class='mainpage'>$langHere</a> $langBackPage</p>";
        }
    } elseif (!empty($_SESSION['uname_app_exists'])) {
        $tool_content .= "<div class='alert alert-danger'>$langUserFree3<br><br>$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</div>";
    }
}
draw($tool_content, 0);

/**
 * set variables
 * @param type $name
 * @return string
 */
function set($name) {
    if (isset($GLOBALS[$name]) and
            $GLOBALS[$name] !== '') {
        return " value='" . q($GLOBALS[$name]) . "'";
    } else {
        return '';
    }
}

/**
 * @brief display form
 *
 * @global type $tool_content
 * @global type $langName
 * @global type $langSurname
 * @global type $langEmail
 * @global type $langCompulsory
 * @global type $langOptional
 * @global type $langPhone
 * @global type $langComments
 * @global type $langFaculty
 * @global type $langRegistration
 * @global type $langLanguage
 * @global type $langAm
 * @global type $profreason
 * @global type $auth
 * @global type $prof
 * @global string $usercomment
 * @global int $depid
 * @global type $email_required
 * @global type $phone_required
 * @global type $comment_required
 * @global type $langEmailNotice
 * @global Hierarchy $tree
 * @global type $head_content
 */
function user_info_form() {
    global $tool_content, $langName, $langSurname, $langEmail, $langCompulsory, $langOptional,
    $langPhone, $langComments, $langFaculty, $langRegistration, $langLanguage,
    $langAm, $profreason, $auth, $prof, $usercomment, $depid, $email_required,
    $phone_required, $comment_required, $langEmailNotice, $tree, $head_content;

    if (!isset($usercomment)) {
        $usercomment = '';
    }
    if (!isset($depid)) {
        $depid = 0;
    }
    if (!get_config("email_required")) {
        $mail_message = $langEmailNotice;
    } else {
        $mail_message = '';
    }   
    if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['givenname'])) {
        $givennameClass = ' form-control-static';
        $givennameInput = q($_SESSION['auth_user_info']['givenname']);
    } else {
        $givennameClass = '';
        $givennameInput = '<input type="text" class="form-control" id="givenname_id" name="givenname_form" maxlength="100"' . set('givenname_form') . '> ';
    }
    if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['surname'])) {
        $surnameClass = ' form-control-static';
        $surnameInput = q($_SESSION['auth_user_info']['surname']);
    } else {
        $surnameClass = '';
        $surnameInput = '<input type="text" class="form-control" id="surname_id" name="surname_form" maxlength="100"' . set('surname_form') . '> ';
    }
    if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['studentid'])) {
        $amClass = ' form-control-static';
        $amInput = q($_SESSION['auth_user_info']['studentid']);
    } else {
        $amMessage = get_config('am_required')? $langCompulsory: $langOptional;
        $amClass = '';
        $amInput = '<input type="text" class="form-control" id="am_id" name="am" maxlength="20"' .
            set('am') . ' placeholder="' . q($amMessage) . '">';
    }
    $tool_content .= "<div class='form-wrapper'>
        <form role='form' class='form-horizontal' action='$_SERVER[SCRIPT_NAME]' method='post'>
        <fieldset>
        <div class='form-group'>
            <label for='givenname_id' class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10$givennameClass'>$givennameInput</div>
        </div>
        <div class='form-group'>
            <label for='surname_id' class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10$surnameClass'>$surnameInput</div>
        </div>
        <div class='form-group'>
            <label for='email_id' class='col-sm-2 control-label'>$langEmail:</label>
            <div class='col-sm-10'>
                <input type='text' name='email' id='email_id' class='form-control' maxlength='100'" . set('email') . "'>" .
                    ($email_required ? '' : "<span class='help-block'><small>$mail_message</small></span>") . "
            </div>
        </div>";
    if (!$prof) {
        $tool_content .= "<div class='form-group'>
                <label for='am_id' class='col-sm-2 control-label'>$langAm:</label>
                <div class='col-sm-10$amClass'>$amInput
                </div>
            </div>";
    }
    if ($prof) {
        $phone_message = $langCompulsory;
    } else {
        $phone_message = $langOptional;
    }
    $tool_content .= "<div class='form-group'>
                <label for='UserPhone' class='col-sm-2 control-label'>$langPhone:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='userphone' size='20' maxlength='20'" . set('userphone') . "' placeholder = '$phone_message'>
                </div>
            </div>";
    if ($comment_required) {
        $tool_content .= "<div class='form-group'>
          <label for='UserComment' class='col-sm-2 control-label'>$langComments:</label>
            <div class='col-sm-10'>
             <textarea class='form-control' name='usercomment' cols='30' rows='4' placeholder='$profreason'>" . q($usercomment) . "</textarea></div>
          </div>";
    }
    $tool_content .= "<div class='form-group'>
              <label for='UserFac' class='col-sm-2 control-label'>$langFaculty:</label>
                <div class='col-sm-10'>";
    list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'where' => 'AND node.allow_user = true', 'multiple' => false));
    $head_content .= $js;
    $tool_content .= $html . "</div>
        </div>
        <div class='form-group'>
          <label for='UserLang' class='col-sm-2 control-label'>$langLanguage:</label>
          <div class='col-sm-10'>" . lang_select_options('localize', "class='form-control'") . "</div>
        </div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
              <input class='btn btn-primary' type='submit' name='submit' value='" . q($langRegistration) . "'>
            </div>
        </div>
        <input type='hidden' name='p' value='$prof'>";

    if (isset($_SESSION['shib_uname'])) {
        $tool_content .= "<input type='hidden' name='uname' value='" . q($_SESSION['shib_uname']) . "'>";
    } else {
        $tool_content .= "<input type='hidden' name='uname' value='" . q($_SESSION['was_validated']['uname']) . "'>";
    }
    $tool_content .= "<input type='hidden' name='auth' value='$auth'>
      </fieldset>
    </form>
  </div>";
}
