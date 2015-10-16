<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

header('Content-Type: application/xml; charset=utf-8');
use Hautelook\Phpass\PasswordHash;
if (isset($_POST['token'])) {
    $require_mlogin = true;
    $require_noerrors = true;
    require_once ('minit.php');

    if (isset($_REQUEST['logout'])) {
        require_once ('modules/auth/auth.inc.php');

        if (isset($_SESSION['uid'])) {
            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                                  VALUES (?d, ?s, NOW(), 'LOGOUT')", intval($_SESSION['uid']), $_SERVER['REMOTE_ADDR']);
        }

        if (isset($_SESSION['cas_uname'])) { // if we are CAS user
            define('CAS', true);
        }

        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }

        session_destroy();

        if (defined('CAS')) {
            $cas = get_auth_settings(7);
            if (isset($cas['cas_ssout']) and intval($cas['cas_ssout']) === 1) {
                phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], FALSE);
                phpCAS::logoutWithRedirectService($urlServer);
            }
        }

        echo RESPONSE_OK;
        exit();
    }

    if (isset($_REQUEST['redirect'])) {
        header('Location: ' . urldecode($_REQUEST['redirect']));
        exit();
    }

    echo RESPONSE_OK;
    exit();
}


if (isset($_POST['uname']) && isset($_POST['pass'])) {
    $require_noerrors = true;
    require_once ('minit.php');
    require_once ('modules/auth/auth.inc.php');

    $uname = canonicalize_whitespace($_POST['uname']);
    $pass = $_POST['pass'];

    foreach (array_keys($_SESSION) as $key) {
        unset($_SESSION[$key]);
    }

    $sqlLogin = (get_config('case_insensitive_usernames')) ? "COLLATE utf8_general_ci = ?s" : "COLLATE utf8_bin = ?s";
    $myrow = Database::get()->querySingle("SELECT * FROM user WHERE username $sqlLogin", $uname);
    
    if (get_config('login_fail_check')) {
        $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
                                    AND COUNT > " . intval(get_config('login_fail_threshold')) . "
                                    AND DATE_SUB(CURRENT_TIMESTAMP, interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail");
    }
    if (get_config('login_fail_check') && $r) {
        $ok = 8;
    } else {
        if (in_array($myrow->password, $auth_ids)) {
            $ok = alt_login($myrow, $uname, $pass);
        } else {
            $ok = login($myrow, $uname, $pass);
        }
    }

    if (isset($_SESSION['uid']) && $ok === 1) {
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                              VALUES (?d, ?s, NOW(), 'LOGIN')", intval($_SESSION['uid']), $_SERVER['REMOTE_ADDR']);
        resetLoginFailure();
        session_regenerate_id();
        set_session_mvars();
        echo session_id();
    } else {
        if ($ok === 4) {
            increaseLoginFailure();
        }
        echo RESPONSE_FAILED;
    }

    exit();
}

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
        echo RESPONSE_FAILED;
        exit();
    }

    $_SESSION['courses'] = $status;
    $_SESSION['mobile'] = true;
}
