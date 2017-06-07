<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

if (!isset($_GET['username']) || !isset($_GET['token']) || !isset($_GET['session'])) {
    exit();
}

$username = $_GET['username'];
$token = $_GET['token'];
$session_id = $_GET['session'];

session_id($session_id);
session_start();
require_once '../../include/init.php';
require_once 'include/log.class.php';
require_once 'modules/auth/auth.inc.php';

// validate token timestamp
if (!token_validate($username . $session_id, $token, 500)) {
    exit();
}

$exists = Database::get()->querySingle("SELECT 1 AS `exists` FROM user_sso WHERE username = ?s AND token = ?s AND session_id = ?s", $username, $token, $session_id);

if ($exists && intval($exists->exists) === 1) {
    foreach (array_keys($_SESSION) as $key) {
        unset($_SESSION[$key]);
    }
    
    $user = Database::get()->querySingle("SELECT * FROM user WHERE username COLLATE utf8_bin = ?s", $username);
    
    $is_active = check_activity($user->id);
    $admin_rights = get_admin_rights($user->id);
    if ($admin_rights == ADMIN_USER) {
        $is_active = 1;   // admin user is always active
        $_SESSION['is_admin'] = 1;
    } elseif ($admin_rights == POWER_USER) {
        $_SESSION['is_power_user'] = 1;
    } elseif ($admin_rights == USERMANAGE_USER) {
        $_SESSION['is_usermanage_user'] = 1;
    } elseif ($admin_rights == DEPARTMENTMANAGE_USER) {
        $_SESSION['is_departmentmanage_user'] = 1;
    }
    if ($is_active) {
        $_SESSION['uid'] = intval($user->id);
        $_SESSION['uname'] = $user->username;
        $_SESSION['surname'] = $user->surname;
        $_SESSION['givenname'] = $user->givenname;
        $_SESSION['status'] = $user->status;
        $_SESSION['email'] = $user->email;
        $_SESSION['langswitch'] = $user->lang;
        
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                              VALUES (?d, ?s, " . DBHelper::timeAfter() . ", 'LOGIN')", intval($_SESSION['uid']), Log::get_client_ip());
        session_regenerate_id();
        set_session_mvars();
        $session->setLoginTimestamp();
        echo session_id();
    }
    
    Database::get()->query("DELETE FROM user_sso WHERE username = ?s AND token = ?s AND session_id = ?s", $username, $token, $session_id);
}

exit();

function set_session_mvars() {
    $status = array();

    $from = "SELECT course.id course_id, course.code code, course.public_code,
                    course.title title, course.prof_names profs, course_user.status status
               FROM course JOIN course_user ON course.id = course_user.course_id
              WHERE course_user.user_id = ?d ";
    $visible = " AND course.visible != ?d ";
    $order = " ORDER BY status, course.title, course.prof_names";

    $callback = function($course) use (&$status) {
        $status[$course->code] = $course->status;
    };

    if ($_SESSION['status'] == 1) {
        $sql = $from . $order;
        Database::get()->queryFunc($sql, $callback, intval($_SESSION['uid']));
    } else if ($_SESSION['status'] == 5) {
        $sql = $from . $visible . $order;
        Database::get()->queryFunc($sql, $callback, intval($_SESSION['uid']), intval(COURSE_INACTIVE));
    } else {
        exit();
    }

    $_SESSION['courses'] = $status;
}
