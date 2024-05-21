<?php
/* ========================================================================
 * Open eClass 3.15
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

/**
 * @file: invite.php
 * @brief: course invitation page
 */
$guest_allowed = true;

require_once '../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'modules/auth/auth.inc.php';

if (!get_config('course_invitation')) {
    Session::flash('message',$langNoLongerValid);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

$id = $_GET['id'] ?? null;

$q = Database::get()->querySingle('SELECT *, expires_at <= NOW() AS expired
    FROM course_invitation WHERE identifier = ?s', $id);

if (!$q or $q->expired) {
    Session::flash('message',$langNoLongerValid);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

if ($q->registered_at) {
    Session::flash('message',$langInvitationAlreadyUsed);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

$course = Database::get()->querySingle('SELECT * FROM course WHERE id = ?d', $q->course_id);
$course_id = $course->id;
$course_code = $course->code;
if ($uid) {
    if (!isset($_SESSION['courses'][$course_code]) or !$_SESSION['courses'][$course_code]) {
        handle_invitations_for_email($uid, $q->email);
    }
    redirect_to_home_page("courses/$course_code/");
}
$professor = q($course->prof_names);
$langUserPortfolio = q($course->title);

if ($course->visible == COURSE_INACTIVE) {
    redirect_to_home_page();
}

$auth = 7; // CAS
$cas = get_auth_settings($auth);

if ($cas['auth_default']) {
    $url_info = parse_url($urlServer);
    $service_base_url = "$url_info[scheme]://$url_info[host]";
    phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], $service_base_url, false);
    phpCAS::setNoCasServerValidation();
} else {
    $cas = null;
}

$user_id = null;
if (isset($_POST['no_cas'])) {
    $surname = canonicalize_whitespace($_POST['surname_form']);
    $givenname = canonicalize_whitespace($_POST['givenname_form']);
    $user = Database::get()->query("INSERT IGNORE INTO user
        SET surname = ?s, givenname = ?s, username = ?s, password = ?s,
            email = ?s, status = ?d, registered_at = " . DBHelper::timeAfter() . ",
            expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
            lang = ?s, am = '', email_public = 0, phone_public = 0, am_public = 0, pic_public = 0,
            description = '', verified_mail = " . EMAIL_VERIFIED . ", whitelist = '',
            disable_course_registration = 1",
            $surname, $givenname, $q->email, password_hash($_POST['password1'], PASSWORD_DEFAULT), $q->email, USER_STUDENT,
            get_config('default_language'));
    if ($user) {
        $user_id = $user->lastInsertID;
        Database::get()->query('INSERT IGNORE INTO user_department
                SET user = ?d, department = ?d',
                $user_id, 1);
        handle_invitations_for_email($user_id, $q->email);
        $ip = Log::get_client_ip();
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                      VALUES (?d, ?s, NOW(), 'LOGIN')", $user_id, $ip);
        $session->setLoginTimestamp();
        resetLoginFailure();
        $_SESSION['uid'] = $user_id;
        $_SESSION['uname'] = $q->email;
        $_SESSION['surname'] = $surname;
        $_SESSION['givenname'] = $givenname;
        $_SESSION['email'] = $q->email;
        $_SESSION['status'] = USER_STUDENT;
        redirect_to_home_page("courses/$course_code/");
    }
}
if (isset($_POST['submit'])) {
    phpCAS::forceAuthentication();
    if ($uid) {
        $user_id = $uid;
    }
}
if (!$uid and $cas and phpCAS::checkAuthentication()) {
    $_SESSION['cas_attributes'] = phpCAS::getAttributes();
    $attrs = get_cas_attrs($_SESSION['cas_attributes'], $cas);
    $username = phpCAS::getUser();
    $user = Database::get()->querySingle('SELECT id FROM user WHERE username = ?s', $username);
    if ($user) {
        $user_id = $user->id;
    } else {
        $user = Database::get()->query("INSERT IGNORE INTO user
                    SET surname = ?s, givenname = ?s, username = ?s, password = ?s,
                        email = ?s, status = ?d, registered_at = " . DBHelper::timeAfter() . ",
                        expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                        lang = ?s, am = ?s, email_public = 0, phone_public = 0, am_public = 0, pic_public = 0,
                        description = '', verified_mail = " . EMAIL_VERIFIED . ", whitelist = ''",
                    $attrs['surname'], $attrs['givenname'], $username, 'cas',
                    mb_strtolower(trim($attrs['email'])), USER_STUDENT, get_config('default_language'),
                    $attrs['studentid']);
        if ($user) {
            $user_id = $user->lastInsertID;
            Database::get()->query('INSERT IGNORE INTO user_department
                SET user = ?d, department = ?d',
                $user_id, 1);
        }
    }
}
if ($user_id) {
    handle_invitations_for_email($user_id, $q->email);
    if ($uid) {
        redirect_to_home_page("courses/$course_code/");
    } else {
        redirect_to_home_page('modules/auth/cas.php?next=%2Fcourses%2F' . $course_code . '%2F');
    }
}

$pageName = $course->is_collaborative ? $langCollabInvitation : $langCourseInvitation;
$pageTitle = $langRegCourses;
$langCourse = $course->is_collaborative ? $langCollab : $langCourse;

$tree = new Hierarchy();
$courseObject = new Course();
$departments = [];
foreach ($courseObject->getDepartmentIds($course_id) as $dep) {
    $departments[] = q($tree->getFullPath($dep));
}
$departments = implode('<br>', $departments);

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
      var form_error = false;

      $(document).ready(function() {
          $('#password_field, #password_field_2').keyup(function() {
              var pass = $('#password_field').val(),
                  pass2 = $('#password_field_2').val();
              if (pass && pass2 && pass != pass2) {
                  $('.pw-group').addClass('has-error');
                  form_error = true;
                  $('#result').html('<span id="result" class="label label-error">$langPassTwice</span>');
              } else {
                  $('.pw-group').removeClass('has-error');
                  form_error = false;
                  $('#result').html(checkStrength(pass));
              }
          });
          $('#register_form').on('submit',function(e) {
              if (form_error) {
                  e.preventDefault();
              }
          });
      });

  /* ]]> */
  </script>
  hContent;

if ($uid) {
    $message = $langInvitationClickToAccept;
    $label = $langRegister;
    $eclass_login = $eclass_form = '';
} else {
    if ($cas) {
        $eclass_login_help = $langInviteEclassLoginAlt;
    } else {
        $eclass_login_help = $langCourseInvitationReceived . ' ' . $langInviteEclassLoginCreate;
    }
    $givenname = q($q->givenname);
    $surname = q($q->surname);
    $eclass_login_form = "
        <div class='row m-auto'>
            <div class='card panelCard px-lg-4 py-lg-3 mt-4'>
                <div class='card-body'>                    
                    <div class='form-group'>
                        <div class='col-12'>
                            <p class='form-control-static'>
                                $eclass_login_help
                            <p>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label class='col-sm-12 control-label-notes'>$langUsername:</label>
                        <div class='col-sm-12'>
                            <input class='form-control' type='text' name='username' value='". q($q->email) . "' disabled>
                            <span class='help-text'>($langSameAsYourEmail)</span>
                        </div>
                    </div>
                    <div class='form-group pw-group mt-4'>
                        <label for='password_field' class='col-sm-12 control-label-notes'>$langPass:</label >
                        <div class='col-sm-12' >
                            <input class='form-control' type='password' name='password1' maxlength='30' autocomplete='off' id='password_field' placeholder='$langUserNotice' required>
                            <span id='result'></span>
                        </div>
                    </div >
                    <div class='form-group pw-group mt-4'>
                        <label for='password_field_2' class='col-sm-12 control-label-notes'>$langConfirmation:</label >
                        <div class='col-sm-12' >
                            <input id='password_field_2' class='form-control' type='password' name='password' maxlength='30' autocomplete='off' required>
                        </div >
                    </div>
                    <div class='form-group mt-4'>
                        <label for='name_field' class='col-sm-12 control-label-notes'>$langName:</label>
                        <div class='col-sm-12'>
                            <input id='name_field' class='form-control' type='text' name='givenname_form' maxlength='100' value = '$givenname' placeholder='$langName' required>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='surname_field' class='col-sm-12 control-label-notes'>$langSurname:</label>
                        <div class='col-sm-12'>
                            <input id='surname_field' class='form-control' type='text' name='surname_form' maxlength='100' value = '$surname' placeholder='$langSurname' required>
                        </div>
                    </div>
                    <div class='form-group mt-5'>
                        <div class='col-sm-12 text-center'>
                            <button type='submit' name='no_cas' class='btn btn-primary'>$langRegisterAsVisitor</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    if ($cas) {
        $auth_title = getSerializedMessage($cas_auth->auth_title);
        $message = sprintf($langInvitationAcceptViaCAS, q($auth_title));
        $main_accept_form = "
            <div class='form-group mt-4'>
                <div class='col-sm-8 col-sm-offset-2'>
                    <p class='form-control-static'>
                        $langCourseInvitationReceived
                        $message
                    </p>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='col-sm-12 text-center'>
                    <button type='submit' name='submit' class='btn btn-primary'>$langLoginAndRegister</button>
                </div>
            </div>";
        $alt_accept_form = "
            <div class='row'>
                <div class='panel'>
                    <div class='panel-body'>
                        <fieldset>
                            $eclass_login_form
                        </fieldset>
                    </div>
                </div>
            </div>";
    } else {
        $main_accept_form = $eclass_login_form;
        $alt_accept_form = '';
    }
}

$tool_content .= "
    <form id='register_form' class='form-horizontal' method='post' action='invite.php?id=$id'>" .
        generate_csrf_token_form_field() . "
        <div class='row m-auto'>
            <div class=' card panelCard px-lg-4 py-lg-3'>
                <div class='card-body'>
                    <fieldset>
                        <div class='form-group'>
                            <label class='col-sm-12 control-label-notes'>$langCourse:</label>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>" . q($course->title) . "</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>$langFaculty:</label>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>$departments</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>$langCode:</label>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>" . q($course->public_code) . "</p>
                            </div>
                        </div>
                        $main_accept_form
                    </fieldset>
                </div>
            </div>
        </div>
        $alt_accept_form
    </form>";

draw($tool_content, 1, null, $head_content);

function handle_invitations_for_email($user_id, $email) {
    $invites = Database::get()->queryArray('SELECT * FROM course_invitation
        WHERE email = ?s AND registered_at IS NULL', $email);
    foreach ($invites as $invite) {
        Database::get()->query('INSERT IGNORE INTO course_user
            SET course_id = ?d, user_id = ?d, status = '  . USER_STUDENT . ',
            reg_date = NOW(), document_timestamp = NOW()',
            $invite->course_id, $user_id) &&
            Database::get()->query('UPDATE course_invitation
            SET registered_at = NOW() WHERE id = ?d',
            $invite->id) &&
            Log::record($invite->course_id, MODULE_ID_USERS, LOG_INSERT,
                ['uid' => $user_id, 'right' => '+5']);
    }
}
