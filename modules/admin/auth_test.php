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

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

if (isset($_REQUEST['auth']) && is_numeric($_REQUEST['auth'])) {
    $data['auth'] = $auth = intval($_REQUEST['auth']);
}

if (!isset($auth) or !isset($auth_ids[$auth])) {
    redirect_to_home_page('modules/admin/auth.php');
}

if ($auth == 7) { // CAS
    $cas_ret = cas_authenticate(7);
    if (phpCAS::checkAuthentication()) {
        Session::flash('message',$langConnYes);
        Session::flash('alert-class', 'alert-success');
        // CAS debugging
        if (!empty($cas_ret['message'])) {
            Session::flash('message',q($cas_ret['message']));
            Session::flash('alert-class', 'alert-success');
        }
        if (!empty($cas_ret['attrs']) && is_array($cas_ret['attrs'])) {
            Session::flash('message',"<p>$langCASRetAttr:<br>" . array2html($cas_ret['attrs']) . "</p>");
            Session::flash('alert-class', 'alert-success');
        }
    }
} elseif ($auth == 6) { // Shibboleth
    if (isset($_SESSION['shib_auth_test']) and $_SESSION['shib_auth_test']) {
        // logged-in successfully with Shibboleth
        unset($_SESSION['shib_auth_test']);
        Session::flash('message', $langConnYes);
        Session::flash('message', "<p>$langCASRetAttr:<br>" . array2html($_SESSION['auth_user_info']) . "</p>");
        Session::flash('alert-class', 'alert-success');
        unset($_SESSION['auth_user_info']);
    } else {
        $_SESSION['shib_auth_test'] = false;
        redirect_to_home_page('secure/index.php');
    }
} elseif ($auth == 15) { // OAuth 2.0
    if (isset($_SESSION['auth_user_info'])) {
        Session::flash('message', $langConnYes);
        Session::flash('message', "<p>$langCASRetAttr:<br>" . array2html($_SESSION['auth_user_info']) . "</p>");
        Session::flash('alert-class', 'alert-success');
        unset($_SESSION['auth_user_info']);
    } else {
        $_SESSION['oauth2_test'] = true;
        redirect_to_home_page('modules/auth/oauth2.php');
    }
} elseif (in_array($auth_ids[$auth], $hybridAuthMethods)) {
    include_once 'modules/auth/methods/hybridauth/config.php';
    $config = get_hybridauth_config();

    if($auth_ids[$auth] == 'linkedin'){
        $provider = 'LinkedIn';
    } else if($auth_ids[$auth] == 'live') {
        $provider = 'WindowsLive';
    } else {
        $provider = $auth_ids[$auth];
    }

    try {
        $hybridauth = new Hybrid_Auth($config);
        $adapter = $hybridauth->authenticate($provider);
        $user_data = $adapter->getUserProfile();
        Session::flash('message',"$langConnYes <p>$langCASRetAttr:<br>" . array2html(get_object_vars($user_data)) . "</p>");
        Session::flash('alert-class', 'alert-success');
    } catch (Exception $e) {
        switch($e->getCode()) {
            case 0: Session::flash('message', $e->getMessage() . "$langProviderError1");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 1: Session::flash('message', $e->getMessage() . "$langProviderError2");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 2: Session::flash('message', $e->getMessage() . "$langProviderError3");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 3: Session::flash('message', $e->getMessage() . "$langProviderError4");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 4: Session::flash('message', $e->getMessage() . "$langProviderError5");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 5: Session::flash('message', $e->getMessage() . "$langProviderError6");
                    Session::flash('alert-class', 'alert-danger');
                    break;
            case 6: Session::flash('message', $e->getMessage() . "$langProviderError7");
                    Session::flash('alert-class', 'alert-danger');
                    $adapter->logout();
                    break;
            case 7: Session::flash('message', $e->getMessage() . "$langProviderError8");
                    Session::flash('alert-class', 'alert-danger');
                    $adapter->logout();
                    break;
        }
    }
}

$toolName = $langConnTest . ' (' . $auth_ids[$auth] . ')';

register_posted_variables([
    'token' => true,
    'submit' => true,
    'test_username' => true], 'all');

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
        Session::flash('message',$langConnYes);
        Session::flash('alert-class', 'alert-success');
        if (isset($_SESSION['auth_user_info']['attributes'])) {
            Session::flash('message',"<p>$langCASRetAttr:<br>" .
            array2html($_SESSION['auth_user_info']['attributes']) . "</p>");
            Session::flash('alert-class', 'alert-success');
        }
    } else {
        Session::flash('message',$langConnNo);
        Session::flash('alert-class', 'alert-danger');
        if (isset($GLOBALS['auth_errors'])) {
            Session::flash('message', $GLOBALS['auth_errors']);
            Session::flash('alert-class', 'alert-info');
        }
    }
}

$data['auth_ids'] = $auth_ids;
view('admin.users.auth.auth_test', $data);
