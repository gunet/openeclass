<?php

/* ========================================================================
 * Open eClass 4.0
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
 * @brief Platform Authentication Methods and their settings
 * @file auth_process.php
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);
$debugCAS = true;

if (isset($_REQUEST['auth']) && is_numeric($_REQUEST['auth'])) {
    $data['auth'] = $auth = intval($_REQUEST['auth']);
}

if (!isset($auth) or !isset($auth_ids[$auth])) {
    redirect_to_home_page('modules/admin/auth.php');
}

if ($auth == 7) { // CAS
    $cas_ret = cas_authenticate(7);
    if (phpCAS::checkAuthentication()) {
        Session::Messages($langConnYes, 'alert-success');
        // CAS debugging
        if (!empty($cas_ret['message'])) {
            Session::Messages(q($cas_ret['message']));
        }
        if (!empty($cas_ret['attrs']) && is_array($cas_ret['attrs'])) {
            Session::Messages("<p>$langCASRetAttr:<br>" . array2html($cas_ret['attrs']) . "</p>");
        }
    }
} elseif ($auth == 6) { // Shibboleth
    if (isset($_SESSION['shib_auth_test']) and $_SESSION['shib_auth_test']) {
        // logged-in successfully with Shibboleth
        unset($_SESSION['shib_auth_test']);
        Session::Messages($langConnYes, 'alert-success');
        Session::Messages("<p>$langCASRetAttr:<br>" . array2html($_SESSION['auth_user_info']) . "</p>");
        unset($_SESSION['auth_user_info']);
    } else {
        $_SESSION['shib_auth_test'] = false;
        redirect_to_home_page('secure/index.php');
    }
} elseif (in_array($auth_ids[$auth], $hybridAuthMethods)) {
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();
    $provider = $auth_ids[$auth];
	try {
		$hybridauth = new Hybrid_Auth($config);
		$adapter = $hybridauth->authenticate($provider);
		$user_data = $adapter->getUserProfile();
        Session::Messages($langConnYes, 'alert-success');
		Session::Messages("<p>$langCASRetAttr:<br>" . array2html(get_object_vars($user_data)) . "</p>");
	} catch (Exception $e) {
		Session::Messages($e->getMessage(), 'alert-danger');
		switch($e->getCode()) {
			case 0: Session::Messages(trans('langProviderError1'); break;
			case 1: Session::Messages(trans('langProviderError2'); break;
			case 2: Session::Messages(trans('langProviderError3'); break;
			case 3: Session::Messages(trans('langProviderError4'); break;
			case 4: Session::Messages(trans('langProviderError5'); break;
			case 5: Session::Messages(trans('langProviderError6'); break;
			case 6: Session::Messages(trans('langProviderError7'); $adapter->logout(); break;
			case 7: Session::Messages(trans('langProviderError8'); $adapter->logout(); break;
		}
    }
}

$toolName = $langConnTest . ' (' . $auth_ids[$auth] . ')';

register_posted_variables(array(
    'token' => true,
    'submit' => true,
    'test_username' => true), 'all');

$data['test_username'] = $test_username;
$data['test_password'] = '';
if (isset($_POST['test_password'])) {
    $data['test_password'] = $_POST['test_password'];
}

if ($submit and $test_username !== '' and $data['test_password'] !== '') {
    if (!$token or !validate_csrf_token($token)) {
        csrf_token_error();
    }
    $settings = get_auth_settings($auth);
    $is_valid = auth_user_login($auth, $test_username, $data['test_password'], $settings);
    if ($is_valid) {
        Session::Messages($langConnYes, 'alert-success');
        if (isset($_SESSION['auth_user_info']['attributes'])) {
            Session::Messages("<p>$langCASRetAttr:<br>" .
                array2html($_SESSION['auth_user_info']['attributes']) . "</p>");
        }
    } else {
        Session::Messages($langConnNo, 'alert-danger');
        if (isset($GLOBALS['auth_errors'])) {
            Session::Messages($GLOBALS['auth_errors'], 'alert-info');
        }
    } 
}

$data['action_bar'] = action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'url' => 'auth.php'
        )));

$data['auth_ids'] = $auth_ids;
$data['menuTypeID'] = 3;
view('admin.users.auth.auth_test', $data);

