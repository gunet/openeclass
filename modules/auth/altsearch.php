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


require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/auth/auth.inc.php';

$tree = new Hierarchy();
$userObj = new User();
$autoregister = FALSE;
$data['user_registration'] = $user_registration = get_config('user_registration');
$data['alt_auth_stud_reg'] = $alt_auth_stud_reg = get_config('alt_auth_stud_reg'); // user registration via alternative auth methods

if (isset($_POST['auth'])) {
    $auth = intval($_POST['auth']);
    $_SESSION['u_tmp'] = $auth;
} else {
    $auth = $_SESSION['u_tmp'] ?? 0;
}

if ($alt_auth_stud_reg == 2) {
    $autoregister = TRUE;
}

$data['comment_required'] = $comment_required = !$autoregister;
$email_required = !$autoregister || get_config('email_required');
$am_required = get_config('am_required') && !isset($_SESSION['auth_user_info']['studentid']) && !$_SESSION['auth_user_info']['studentid'];

$pageName = $langUserData . ' (' . (get_auth_info($auth)) . ')';
$navigation[] = array('url' => 'registration.php', 'name' => $langRegistration);

register_posted_variables(array('uname' => true,
                                'passwd' => true,
                                'is_submit' => true,
                                'submit' => true));
$lastpage = "altnewuser.php?auth=$auth&amp;uname=" . urlencode($uname);
$navigation[] = array('url' => $lastpage, 'name' => $langConfirmUser);

$errormessage = "<br/><p>$ldapback <a href='$lastpage'>$ldaplastpage</a></p>";
$init_auth = $is_valid = false;

$data['lang_select_options'] = lang_select_options('localize', "class='form-control'");
list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => 0, 'tree' => null, 'where' => 'AND node.allow_user = true', 'multiple' => false));
$head_content .= $js;
$data['buildusernode'] = $html;

if (!isset($_SESSION['was_validated']) or $_SESSION['was_validated']['auth'] != $auth or $_SESSION['was_validated']['uname'] != $uname) {
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
        if ($auth != 7 and $auth != 6 and ($uname === '' or $passwd === '')) {
            Session::flash('message', "$ldapempty $errormessage");
            Session::flash('alert-class', 'alert-danger');
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
    } else { // wrong credentials
        Session::flash('message', $langAuthNoValidUser);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/auth/altnewuser.php?auth=$auth");
    }
} else {
    $is_valid = true;
}
$data['auth'] = $auth;

if ($is_valid) { // user credentials successful check
    if (!isset($_POST['submit'])) {
            if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['givenname'])) {
                $givennameClass = ' form-control-static';
                $givennameInput = q($_SESSION['auth_user_info']['givenname']);
            } else {
                $givennameClass = '';
                $givennameInput = '<input type="text" class="form-control" id="givenname_id" name="givenname_form" maxlength="100"' . set('givenname_form') . '> ';
            }
            $data['givennameClass'] = $givennameClass;
            $data['givennameInput'] = $givennameInput;

            if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['surname'])) {
                $surnameClass = ' form-control-static';
                $surnameInput = q($_SESSION['auth_user_info']['surname']);
            } else {
                $surnameClass = '';
                $surnameInput = '<input type="text" class="form-control" id="surname_id" name="surname_form" maxlength="100"' . set('surname_form') . '> ';
            }
            $data['surnameClass'] = $surnameClass;
            $data['surnameInput'] = $surnameInput;

            if (isset($_SESSION['auth_user_info']) and !empty($_SESSION['auth_user_info']['studentid'])) {
                $amClass = ' form-control-static';
                $amInput = q($_SESSION['auth_user_info']['studentid']);
            } else {
                $amMessage = get_config('am_required')? $langCompulsory: $langOptional;
                $amClass = '';
                $amInput = '<input type="text" class="form-control" id="am_id" name="am" maxlength="20"' .
                    set('am') . ' placeholder="' . q($amMessage) . '">';
            }
            $data['amClass'] = $amClass;
            $data['amInput'] = $amInput;
            $data['email_placeholder'] = get_config("email_required")? $langCompulsory: $langOptional;
    } else {
        $ip = Log::get_client_ip();
        $ext_info = !isset($_SESSION['auth_user_info']);
        $ext_mail = !empty($_SESSION['auth_user_info']['email']);
        $missing_posted_variables = array();
        $ok = register_posted_variables(
            array('submit' => false,
                'email' => $email_required && $ext_mail,
                'surname_form' => $ext_info,
                'givenname_form' => $ext_info,
                'am' => $am_required,
                'department' => true,
                'usercomment' => $comment_required), 'all');

        if (!$ok and $submit) {
            Session::flash('message', "$langFieldsMissing");
            Session::flash('alert-class', 'alert-danger');
        }
        if (!empty($_SESSION['auth_user_info']['givenname'])) {
            $givenname_form = $_SESSION['auth_user_info']['givenname'];
        }
        if (!empty($_SESSION['auth_user_info']['surname'])) {
            $surname_form = $_SESSION['auth_user_info']['surname'];
        }
        if (!empty($_SESSION['auth_user_info']['studentid'])) {
            $am = $_SESSION['auth_user_info']['studentid'];
        }
        if (!$email and !empty($_SESSION['auth_user_info']['email'])) {
            $email = $_SESSION['auth_user_info']['email'];
        }
        if (!empty($email) and !valid_email($email)) {
            $ok = NULL;
            Session::flash('message', "$langEmailWrong");
            Session::flash('alert-class', 'alert-danger');
        } else {
            $email = mb_strtolower(trim($email));
        }

        if ($init_auth) {
            Session::flash('message', "$langTheUser $ldapfound");
            Session::flash('alert-class', 'alert-success');
        }

        if (@(!empty($_SESSION['was_validated']['uname_exists']) and $_POST['p'] != 1)) {
            Session::flash('message', "$langUserFree $langUserFree2");
            Session::flash('alert-class', 'alert-warning');
        }
        if ($auth != 1) {
            $password = $auth_ids[$auth] ?? '';
        }

        $uname = canonicalize_whitespace($uname);
        // user already exists
        if (user_exists($uname)) {
            $_SESSION['uname_exists'] = 1;
        } elseif (isset($_SESSION['uname_exists'])) {
            unset($_SESSION['uname_exists']);
        }
        // user already applied for account
        if (user_app_exists($uname)) {
            $_SESSION['uname_app_exists'] = 1;
        } elseif (isset($_SESSION['uname_app_exists'])) {
            unset($_SESSION['uname_app_exists']);
        }
        // user account request
        if (!$autoregister) {
            if (empty($_SESSION['uname_app_exists']) and $ok) {
                $email_verification_required = get_config('email_verification_required');
                if (!$email_verification_required) {
                    $verified_mail = 2;
                } else {
                    $verified_mail = 0;
                }

                // check if mail address is valid
                if (!empty($email) and !valid_email($email)) {
                    Session::flash('message', $langEmailWrong);
                    Session::flash('alert-class', 'alert-warning');
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
                    $givenname_form, $surname_form, $uname, $password, $email, $_POST['department'], $_POST['userphone'],
                    $am, USER_STUDENT, $verified_mail, $usercomment, $language, $ip);
                $request_id = $q1->lastInsertID;
                // email does not need verification -> mail helpdesk
                if (!$email_verification_required) {
                    $emailAdministrator = get_config('email_sender');
                    $emailhelpdesk = get_config('email_helpdesk');
                    // send email

                    $fullname = q("$surname_form $givenname_form");
                    $am_html = $am? ("<li><span><b>$langAm :</b></span> <span>" . q($am) . "</span></li>"): '';
                    $username = q($uname);
                    $usermail = q($email);
                    $phone_html = $phone? ("<li><span><b>$contactphone:</b></span> <span>" . q($phone) . "</span></li>"): '';
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
                    <li><span><b>$langProfUname:</b></span> <span>$username</span></li>
                    <li><span><b>$langComments:</b></span> <span>$usercomment</span></li>
                    $am_html
                    $phone_html
                    <li><span><b>$langProfEmail :</b></span> <span>$usermail</span></li>
                    <li><span><b>$langFaculty:</b></span> <span>" . $tree->getFullPath($_POST['department']) . "</span></li>
                </ul>
                <p>$logo</p>
                    </div>
                </div>";

                    $MailMessage = $header_html_topic_notify . $body_html_topic_notify;
                    $plainemailbody = html2text($MailMessage);

                    if (!send_mail_multipart($siteName, $emailAdministrator, $gunet, $emailhelpdesk, $mailsubject, $plainemailbody, $MailMessage)) {
                        Session::flash('message', "$langMailErrorMessage &nbsp; <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>");
                        Session::flash('alert-class', 'alert-warning');
                    }
                    Session::Messages("<p>$success<br>$infoprof<br>&laquo; <a href='$urlAppend'>$langBack</a></p>", 'alert-success');
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
                    <div id='mail-body-inner'>" .
                        sprintf($mailbody1 . $langMailVerificationBody1, "<a href='{$urlServer}modules/auth/mail_verify.php?h=" . $hmac . "&amp;rid=" . $request_id . "'>{$urlServer}modules/auth/mail_verify.php?h=" . $hmac . "&amp;rid=" . $request_id . "</a>") . "
                    </div>
                </div>";

                    $MailMessage = $header_html_topic_notify . $body_html_topic_notify;
                    $plainemailbody = html2text($MailMessage);

                    if (!send_mail_multipart($siteName, $emailAdministrator, '', $email, $subject, $plainemailbody, $MailMessage)) {
                        Session::flash('message', "$langMailVerificationError2");
                        Session::flash('alert-class', 'alert-warning');
                    }

                    Session::flash('message', "$langRequestWithMailVerify");
                    Session::flash('alert-class', 'alert-success');
                }
            } else { // user account request exists
                Session::flash('message', "$langUserFree3");
                Session::flash('alert-class', 'alert-danger');
            }
        } else if (empty($_SESSION['uname_exists']) and $ok) { // user registration
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
                              phone = ?s,
                              registered_at = " . DBHelper::timeAfter() . ",
                              expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                              lang = ?s,
                              verified_mail = ?d,
                              whitelist='',
                              description = ''", $surname_form, $givenname_form, $uname, $password, $email, $am, $_POST['userphone'], $language, $verified_mail);
            $last_id = $q1->lastInsertID;
            // update personal calendar info table
            // we don't check if trigger exists since it requires `super` privilege
            Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $last_id);
            $userObj->refresh($last_id, array(intval($_POST['department'])));
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
                        <p>" . ($vmail ? "$langMailVerificationSuccess<br>$langMailVerificationClick<br><a href='{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id'>{$urlServer}modules/auth/mail_verify.php?h=$hmac&amp;id=$last_id</a>" : "") . "</p>
                        <p>$langProblem<br><br>$langFormula<br>$administratorName<br>$langManager $siteName<br>$langTel: $telephone<br>$langEmail: $emailhelpdesk</p>

                </div>
            </div>";

            $emailbody = $header_html_topic_notify . $body_html_topic_notify;
            $plainemailbody = html2text($emailbody);

            if (!empty($email)) {
                send_mail_multipart($siteName, $emailAdministrator, '', $email, $emailsubject, $plainemailbody, $emailbody);
            }

            $myrow = Database::get()->querySingle("SELECT id, surname, givenname FROM user WHERE id = ?d", $last_id);
            if ($myrow) {
                $uid = $myrow->id;
                $surname = $myrow->surname;
                $givenname = $myrow->givenname;
            }

            if (!$vmail) {
                Database::get()->query("INSERT INTO loginout SET id_user = ?d, ip = ?s,`when` = NOW(), action = 'LOGIN'", $uid, $ip);
                $_SESSION['uid'] = $uid;
                $_SESSION['status'] = USER_STUDENT;
                $_SESSION['givenname'] = $givenname;
                $_SESSION['surname'] = $surname;
                $_SESSION['uname'] = canonicalize_whitespace($uname);
                $session->setLoginTimestamp();

                Session::flash('message', "<span><br><br>$langPersonalSettingsLess $langClick
                                    <a href='$urlServer' class='mainpage'>$langHere</a> $langPersonalSettingsMore
                                  <ul>
                                    <li>$langPersonalSettingsMore1</li>
                                    <li>$langPersonalSettingsMore2</li>
                                  </ul></span>");
                Session::flash('alert-class', 'alert-success');
            } else {
                Session::flash('message', "$langRegistrationWithMailVerify");
                Session::flash('alert-class', 'alert-success');
            }
        }
    }
}

view('modules.auth.altsearch', $data);

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
