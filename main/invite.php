<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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
 * @brief: course invitation accept/registration page
 */
$guest_allowed = true;

require_once '../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'modules/auth/auth.inc.php';

if (!get_config('course_invitation')) {
    Session::flash('message', $langNoLongerValid);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

$id = $_GET['id'] ?? null;

$invitation = Database::get()->querySingle('SELECT *, expires_at <= NOW() AS expired
    FROM course_invitation WHERE identifier = ?s', $id);
$data['invitation'] = $invitation;

if (!$invitation or $invitation->expired) {
    Session::flash('message',$langNoLongerValid);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

if ($invitation->registered_at) {
    Session::flash('message', $langInvitationAlreadyUsed);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page();
}

$course = Database::get()->querySingle('SELECT * FROM course WHERE id = ?d', $invitation->course_id);
$data['course'] = $course;

if ($course->visible == COURSE_INACTIVE) {
    redirect_to_home_page();
}

if ($uid) {
    if (!isset($_SESSION['courses'][$course->code]) or !$_SESSION['courses'][$course->code]) {
        handle_invitations_for_email($uid, $invitation->email);
    }
    redirect_to_home_page("courses/{$course->code}/");
}
$professor = q($course->prof_names);
$langUserPortfolio = q($course->title);

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
$data['cas'] = $cas;

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
            $surname, $givenname, $invitation->email, password_hash($_POST['password1'], PASSWORD_DEFAULT), $invitation->email, USER_STUDENT,
            get_config('default_language'));
    if ($user) {
        $user_id = $user->lastInsertID;
        Database::get()->query('INSERT IGNORE INTO user_department
                SET user = ?d, department = ?d',
                $user_id, 1);
        handle_invitations_for_email($user_id, $invitation->email);
        $ip = Log::get_client_ip();
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                      VALUES (?d, ?s, NOW(), 'LOGIN')", $user_id, $ip);
        $session->setLoginTimestamp();
        resetLoginFailure();
        $_SESSION['uid'] = $user_id;
        $_SESSION['uname'] = $invitation->email;
        $_SESSION['surname'] = $surname;
        $_SESSION['givenname'] = $givenname;
        $_SESSION['email'] = $invitation->email;
        $_SESSION['status'] = USER_STUDENT;
        redirect_to_home_page("courses/{$course->code}/");
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
    handle_invitations_for_email($user_id, $invitation->email);
    if ($uid) {
        redirect_to_home_page("courses/{$course->code}/");
    } else {
        redirect_to_home_page('modules/auth/cas.php?next=%2Fcourses%2F' . $course->code . '%2F');
    }
}

$pageName = $course->is_collaborative ? $langCollabInvitation : $langCourseInvitation;
$pageTitle = $langRegCourses;
$langCourse = $course->is_collaborative ? $langCollab : $langCourse;

$tree = new Hierarchy();
$courseObject = new Course();
$departments = [];
foreach ($courseObject->getDepartmentIds($course->id) as $dep) {
    $departments[] = q($tree->getFullPath($dep));
}
$data['departments'] = implode('<br>', $departments);

load_js('pwstrength.js');
$data['pwMessages'] = [
    'pwStrengthTooShort' => $langPwStrengthTooShort,
    'pwStrengthWeak' => $langPwStrengthWeak,
    'pwStrengthGood' => $langPwStrengthGood,
    'pwStrengthStrong' => $langPwStrengthStrong];

if ($uid) {
    $data['message'] = $langInvitationClickToAccept;
    $data['label'] = $langRegister;
} else {
    if ($cas) {
        $data['eclass_login_help'] = $langInviteEclassLoginAlt;
        $auth_title = getSerializedMessage($cas['auth_title']);
        $data['message'] = sprintf($langInvitationAcceptViaCAS, $auth_title);
    } else {
        $data['eclass_login_help'] = $langCourseInvitationReceived . ' ' . $langInviteEclassLoginCreate;
    }
}

view('main.invitation.index', $data);

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
