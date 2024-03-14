<?php

/* ========================================================================
 * Open eClass 3.12
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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

/*
 * @file logout.php
 *
 * @abstract Redirect users to this file to log them out
 *
 */

$guest_allowed = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/auth/methods/hybridauth/config.php';

if ($uid and isset($_POST['token']) and validate_csrf_token($_POST['token'])) {
    $login_method = $session->getLoginMethod();
    Database::get()->query("INSERT INTO loginout (loginout.id_user,
                loginout.ip, loginout.when, loginout.action)
                VALUES (?d, ?s, " . DBHelper::timeAfter() . ", 'LOGOUT')", $uid, Log::get_client_ip());

    $config = get_hybridauth_config();
    $hybridauth = new Hybridauth\Hybridauth($config);
    foreach ($hybridauth->getConnectedAdapters() as $adapter) {
        $adapter->disconnect();
    }

    foreach (array_keys($_SESSION) as $key) {
        unset($_SESSION[$key]);
    }

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    Database::get()->query('DELETE FROM login_lock
        WHERE user_id = ?d AND session_id = ?s',
        $uid, session_id());

    session_destroy();

    $cas = ($login_method == 'cas')? get_auth_settings(7): false;
    if ($cas and isset($cas['cas_ssout']) and intval($cas['cas_ssout']) === 1) {
        $url_info = parse_url($urlServer);
        $service_base_url = "$url_info[scheme]://$url_info[host]";
        phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], $service_base_url, FALSE);
        phpCAS::logoutWithRedirectService($urlServer);
    }
} elseif ($uid) {
    $pageName = $langLogout;
    $tool_content = "
        <form method='post' action='logout.php'>
            <input type='hidden' name='token' value='$_SESSION[csrf_token]'>
            <input type='submit' name='submit' value='$langLogout'>
        </form>";
    draw_popup();
    exit;
}

redirect_to_home_page();
