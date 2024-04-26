<?php

/* ========================================================================
 * Open eClass 3.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

if (isset($_REQUEST['auth']) && is_numeric($_REQUEST['auth'])) {
    $auth = intval($_REQUEST['auth']); // $auth gets the integer value of the auth method if it is set
} else {
    $auth = false;
}

register_posted_variables([
    'imaphost' => true, 'pop3host' => true,
    'ldaphost' => true, 'ldap_base' => true, 'ldapbind_dn' => true,
    'ldapbind_pw' => true, 'ldap_login_attr' => true,
    'ldap_firstname_attr' => true, 'ldap_surname_attr' => true,
    'ldap_studentid' => true, 'ldap_mail_attr' => true,
    'dbhost' => true, 'dbtype' => true, 'dbname' => true,
    'dbuser' => true, 'dbpass' => true, 'dbtable' => true,
    'dbfielduser' => true, 'dbfieldpass' => true, 'dbpassencr' => true,
    'shib_email' => true, 'shib_uname' => true, 'shib_surname' => true,
    'shib_givenname' => true, 'shib_cn' => true, 'shib_studentid' => true,
    'checkseparator' => true,
    //CAS settings
    'cas_host' => true, 'cas_port' => true, 'cas_context' => true,
    'cas_cachain' => true, 'casusermailattr' => true,
    'casuserfirstattr' => true, 'casuserlastattr' => true, 'cas_altauth' => true,
    'cas_logout' => true, 'cas_ssout' => true, 'casuserstudentid' => true,
    'cas_altauth_use' => true, 'gunet_identity' => true, 'minedu_institution' => true, 'cas_gunet' => true, 'minedu_departments_association' => true,
    //Auth common settings
    'auth_instructions' => true,
    'auth_title' => true,
    // HybridAuth settings
    'hybridauth_id_key' => true, 'hybridauth_secret' => true, 'hybridauth_instructions' => true,
    'test_username' => true,
    // OAuth 2.0 options
    'apiBaseUrl' => true, 'authorizePath' => true, 'accessTokenPath' => true, 'profileMethod' => true,
    'apiID' => true, 'apiSecret' => true,
], 'all');

if (empty($ldap_login_attr)) {
    $ldap_login_attr = 'uid';
}

if (isset($_POST['submit'])) {

    $data = json_decode($_POST['minedu_departments_association'], true);

    if ($data !== null) {

        foreach ($data as $association_string) {
            $association = json_decode($association_string, true);
            $minedu_School_id = $association['minedu_School_id'];
            $local_dep_id = $association['local_dep_id'];

            Database::get()->querySingle('INSERT INTO minedu_departments_association
                                    SET minedu_id = ?d, department_id = ?d',
                $minedu_School_id, $local_dep_id);

        }

    }

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
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
                'ldap_firstname_attr' => $ldap_firstname_attr,
                'ldap_surname_attr' => $ldap_surname_attr,
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
                'cas_altauth_use' => $cas_altauth_use,
                'cas_logout' => $cas_logout,
                'cas_ssout' => $cas_ssout,
                'casuserstudentid' => $casuserstudentid,
                'cas_gunet' => $cas_gunet,
            );
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
        case 15:
            $settings = [
                'apiBaseUrl' => $apiBaseUrl,
                'id' => $apiID,
                'secret' => $apiSecret,
                'authorizePath' => $authorizePath,
                'accessTokenPath' => $accessTokenPath,
                'profileMethod' => $profileMethod,
                'casusermailattr' => $casusermailattr,
                'casuserfirstattr' => $casuserfirstattr,
                'casuserlastattr' => $casuserlastattr,
                'casuserstudentid' => $casuserstudentid];
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
    $result = Database::get()->query('INSERT INTO auth
        (auth_id, auth_name, auth_settings, auth_instructions, auth_default, auth_title) VALUES
        (?d, ?s, ?s, ?s, 1, ?s) ON DUPLICATE KEY UPDATE
            auth_settings = VALUES(auth_settings),
            auth_instructions = VALUES(auth_instructions),
            auth_default = GREATEST(auth_default, 1),
            auth_title = VALUES(auth_title)',
        function ($error) use (&$tool_content, $langErrActiv) {
            Session::Messages($langErrActiv, 'alert-warning');
        }, $auth, $auth_ids[$auth], $auth_settings, $auth_instructions, $auth_title);
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

    $tool_content .= action_bar(array(
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

    // get authentication settings
    if ($auth != 6) {
        $auth_data = get_auth_settings($auth);
    }
    // display form
    $tool_content .= "<div class='form-wrapper'>
    <form class='form-horizontal' name='authmenu' method='post' action='$_SERVER[SCRIPT_NAME]'>
    <fieldset>
        <input type='hidden' name='auth' value='" . intval($auth) . "'>";

    switch ($auth) {
        case 1: $tool_content .= eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);
            break;
        case 2: require_once 'modules/auth/methods/pop3form.php';
            break;
        case 3: require_once 'modules/auth/methods/imapform.php';
            break;
        case 4: require_once 'modules/auth/methods/ldapform.php';
            break;
        case 5: require_once 'modules/auth/methods/dbform.php';
            break;
        case 6: require_once 'modules/auth/methods/shibform.php';
            break;
        case 7: require_once 'modules/auth/methods/casform.php';
            break;
        case 15: require_once 'modules/auth/methods/oauth2form.php';
            break;
        case 8:
        case 9:
        case 10:
        case 11:
        case 12:
        case 13:
            require_once 'modules/auth/methods/hybridauthform.php'; // generic HybridAuth form for provider settings
            hybridAuthForm($auth);
            break;
        default:
            break;
    }
    $tool_content .= "
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                        <a class='btn btn-default' href='auth.php'>$langCancel</a>
                    </div>
                </div>
            </fieldset>
            ". generate_csrf_token_form_field() ."
        </form>
    </div>";
}

draw($tool_content, 3, null, $head_content);

/**
 * @brief display form for completing info about authentication via eclass
 * @global type $langAuthTitle
 * @global type $langInstructionsAuth
 * @param type $auth_title
 * @param type $auth_instructions
 * @return string
 */
function eclass_auth_form($auth_title, $auth_instructions) {

    global $langAuthTitle, $langInstructionsAuth;

    $content = "<div class='form-group'>
            <label for='auth_title' class='col-sm-2 control-label'>$langAuthTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' name='auth_title' id='auth_title' type='text' value='" . q($auth_title) . "'>
            </div>
        </div>
        <div class='form-group'>
            <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
            <div class='col-sm-10'>
                <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
            </div>
        </div>";

    return $content;
}


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
