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
        Session::flash('message',$langConnYes);
        Session::flash('alert-class', 'alert-success');
        Session::flash('message',"<p>$langCASRetAttr:<br>" . array2html(get_object_vars($user_data)) . "</p>");
        Session::flash('alert-class', 'alert-success');
    } catch (Exception $e) {
        Session::Messages($e->getMessage(), 'alert-danger');
        switch($e->getCode()) {
            case 0: Session::Messages(trans('langProviderError1')); break;
            case 1: Session::Messages(trans('langProviderError2')); break;
            case 2: Session::Messages(trans('langProviderError3')); break;
            case 3: Session::Messages(trans('langProviderError4')); break;
            case 4: Session::Messages(trans('langProviderError5')); break;
            case 5: Session::Messages(trans('langProviderError6')); break;
            case 6: Session::Messages(trans('langProviderError7')); $adapter->logout(); break;
            case 7: Session::Messages(trans('langProviderError8')); $adapter->logout(); break;
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

$data['action_bar'] = action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'level' => 'primary',
            'url' => 'auth.php'
        )));

$data['auth_ids'] = $auth_ids;
view('admin.users.auth.auth_test', $data);
