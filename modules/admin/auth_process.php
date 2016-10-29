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


/**
 * @brief Platform Authentication Methods and their settings
 * @file auth_process.php
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$toolName = $langAuthSettings;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);
$debugCAS = true;

if (isset($_REQUEST['auth']) && is_numeric(getDirectReference($_REQUEST['auth']))) {
    $data['auth'] = $auth = intval(getDirectReference($_REQUEST['auth'])); // $auth gets the integer value of the auth method if it is set
} else {
    $data['auth'] = $auth = false;
}

register_posted_variables(array('imaphost' => true, 'pop3host' => true,
    'ldaphost' => true, 'ldap_base' => true,
    'ldapbind_dn' => true, 'ldapbind_pw' => true,
    'ldap_login_attr' => true, 'ldap_login_attr2' => true,
    'ldap_studentid' => true, 'ldap_mail_attr' => true,
    'dbhost' => true, 'dbtype' => true, 'dbname' => true,
    'dbuser' => true, 'dbpass' => true, 'dbtable' => true,
    'dbfielduser' => true, 'dbfieldpass' => true, 'dbpassencr' => true,
    'shib_email' => true, 'shib_uname' => true, 'shib_surname' => true,
    'shib_givenname' => true, 'shib_cn' => true, 'shib_studentid' => true,
    'checkseparator' => true,
    'cas_host' => true, 'cas_port' => true, 'cas_context' => true,
    'cas_cachain' => true, 'casusermailattr' => true,
    'casuserfirstattr' => true, 'casuserlastattr' => true, 'cas_altauth' => true,
    'cas_logout' => true, 'cas_ssout' => true, 'casuserstudentid' => true,
    'auth_instructions' => true, 'auth_title' => true,
	'hybridauth_id_key' => true, 'hybridauth_secret' => true, 'hybridauth_instructions' => true,
    'test_username' => true), 'all');

if (empty($ldap_login_attr)) {
    $ldap_login_attr = 'uid';
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    switch ($auth) {
        case 1:
            $settings = array();
            break;
        case 2:
            $settings = array('pop3host' => $pop3host);
            break;
        case 3:
            $settings = array('imaphost' => $imaphost);
            break;
        case 4:
            $settings = array('ldaphost' => $ldaphost,
                'ldap_base' => $ldap_base,
                'ldapbind_dn' => $ldapbind_dn,
                'ldapbind_pw' => $ldapbind_pw,
                'ldap_login_attr' => $ldap_login_attr,
                'ldap_login_attr2' => $ldap_login_attr2,
                'ldap_mail_attr' => $ldap_mail_attr,
                'ldap_studentid' => $ldap_studentid);
            break;
        case 5:
            $settings = array('dbhost' => $dbhost,
                'dbname' => $dbname,
                'dbuser' => $dbuser,
                'dbpass' => $dbpass,
                'dbtable' => $dbtable,
                'dbfielduser' => $dbfielduser,
                'dbfieldpass' => $dbfieldpass,
                'dbpassencr' => $dbpassencr);
            break;
        case 6:
            if ($checkseparator) {
                $auth_settings = $_POST['shibseparator'];
            } else {
                $auth_settings = 'shibboleth';
            }
            $settings = array('shib_email' => $shib_email,
                'shib_uname' => $shib_uname);
            if ($shib_cn) {
                $settings['shib_cn'] = $shib_cn;
            }
            if ($shib_surname) {
                $settings['shib_surname'] = $shib_surname;
            }
            if ($shib_givenname) {
                $settings['shib_givenname'] = $shib_givenname;
            }
            if ($shib_studentid) {
                $settings['shib_studentid'] = $shib_studentid;
            }
            update_shibboleth_endpoint($settings);
            break;
        case 7:
            $settings = array('cas_host' => $cas_host,
                'cas_port' => $cas_port,
                'cas_context' => $cas_context,
                'cas_cachain' => $cas_cachain,
                'casusermailattr' => $casusermailattr,
                'casuserfirstattr' => $casuserfirstattr,
                'casuserlastattr' => $casuserlastattr,
                'cas_altauth' => $cas_altauth,
                'cas_logout' => $cas_logout,
                'cas_ssout' => $cas_ssout,
                'casuserstudentid' => $casuserstudentid);
            break;
        case 8:  // Facebook
        case 10: // Google
        case 11: // Live
            $settings = array('id' => $hybridauth_id_key,
                'secret' => $hybridauth_secret);
            $auth_instructions = $hybridauth_instructions;
            break;
        case 9:  // Twitter
        case 12: // Yahoo
        case 13: // LinkedIn
            $settings = array('key' => $hybridauth_id_key,
                'secret' => $hybridauth_secret);
            $auth_instructions = $hybridauth_instructions;
            break;
        default:
            break;
    }

    // update table `auth`
    if ($auth != 6 && $auth < 8) {
        $auth_settings = pack_settings($settings);
    } elseif ($auth >= 8) {
        $auth_settings = serialize($settings);
    }
    $result = Database::get()->query("UPDATE auth
        SET auth_settings = ?s,
            auth_instructions = ?s,
            auth_default = GREATEST(auth_default, 1),
            auth_title = ?s
        WHERE auth_id = ?d",
        function ($error) use ($langErrActiv) {
            Session::Messages($langErrActiv, 'alert-warning');
        }, $auth_settings, $auth_instructions, $auth_title, $auth);
    if ($result) {
        if ($result->affectedRows == 1) {
            Session::Messages($langHasActivate, 'alert-success');
        } else {
            Session::Messages($langAlreadyActiv, 'alert-info');
        }
    }
    redirect_to_home_page('modules/admin/auth_process.php?auth=' . $auth);
} else {
    // handle reloads on auth_process.php after authentication check
    // also handles requests with empty $auth
    // without this, a form with just username/password is displayed
    if (!$auth) {
        redirect_to_home_page('modules/admin/auth.php');
    }

    $data['action_bar'] = action_bar(array(
        array('title' => $langConnTest,
              'url' => "auth_test.php?auth=$auth",
              'icon' => 'fa-plug',
              'level' => 'primary-label',
              'show' => $auth != 1 && get_auth_settings($auth)),
        array('title' => $langBack,
              'icon' => 'fa-reply',
              'level' => 'primary-label',
              'url' => 'auth.php')));

    $pageName = get_auth_info($auth);
    $data['auth_data'] = $auth_data = get_auth_settings($auth);
    if ($auth == 6) {
        $data['secureIndexPath'] = $webDir . '/secure/index.php';
        $data['shib_vars'] = get_shibboleth_vars($data['secureIndexPath']);
        //$r = Database::get()->querySingle("SELECT auth_settings, auth_instructions, auth_title FROM auth WHERE auth_id = 6");
        $shibsettings = $data['auth_data']['auth_settings'];
        if ($shibsettings != 'shibboleth' and $shibsettings != '') {
            $data['shibseparator'] = $shibsettings;
            $data['checkedshib'] = 'checked';
        } else {
            $data['checkedshib'] = $data['shibseparator'] = '';
        }        
    } else {
        if (in_array($auth, [8, 9, 10, 11, 12, 13])) {
            //$r = Database::get()->querySingle("SELECT auth_settings, auth_instructions, auth_name FROM auth WHERE auth_id = ?d", $auth);
            if (!empty($data['auth_data']['auth_settings'])) {
                foreach (unserialize($data['auth_data']['auth_settings']) as $key => $auth_setting) {
                    $data['auth_data'][$key] = $auth_setting;
                }
                if (isset($data['auth_data']['id'])) {
                    $data['auth_data']['key'] = $data['auth_data']['id'];
                }
            } else {
                $data['auth_data']['id'] = $data['auth_data']['key'] = $data['auth_data']['secret'] = '';
            }       
        }
    }
}

$data['menuTypeID'] = 3;
view ('admin.users.auth.auth_process', $data);

/**
 * @brief utility function
 * @param type $settings
 * @return type
 */
function pack_settings($settings) {
    $items = array();
    foreach ($settings as $key => $value) {
        $items[] = "$key=$value";
    }
    return implode('|', $items);
}