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
require_once 'include/CAS/CAS.php';
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

register_posted_variables(array('imaphost' => true, 'pop3host' => true,
    'ldaphost' => true, 'ldap_base' => true,
    'ldapbind_dn' => true, 'ldapbind_pw' => true,
    'ldap_login_attr' => true, 'ldap_login_attr2' => true,
    'ldap_id_attr' => true,
    'dbhost' => true, 'dbtype' => true, 'dbname' => true,
    'dbuser' => true, 'dbpass' => true, 'dbtable' => true,
    'dbfielduser' => true, 'dbfieldpass' => true, 'dbpassencr' => true,
    'shibemail' => true, 'shibuname' => true,
    'shibcn' => true, 'checkseparator' => true,
    'submit' => true, 'auth_instructions' => true, 'auth_title' => true,
	'hybridauth_id_key' => true, 'hybridauth_secret' => true, 'hybridauth_instructions' => true,
    'test_username' => true), 'all');

$test_password = isset($_POST['test_password']) ? $_POST['test_password'] : '';

if ($auth == 7) {
    if ($submit) {
        $_SESSION['cas_do'] = true;
        // $_POST is lost after we come back from CAS
        foreach (array('cas_host', 'cas_port', 'cas_context', 'cas_cachain',
                        'casusermailattr', 'casuserfirstattr', 'casuserlastattr',
                        'cas_altauth', 'cas_logout', 'cas_ssout', 'casuserstudentid', 
                        'auth_instructions', 'auth_title') as $var) {
            if (isset($_POST[$var])) {
                $_SESSION[$var] = $_POST[$var];
            }
        }
    } else {
        $_SESSION['cas_do'] = false;
    }
}

if (isset($_GET['ticket'])) {
    $_SESSION['cas_do'] = true;
}

if (!empty($_SESSION['cas_warn'])) {
    $_SESSION['cas_do'] = false;
}

if (empty($ldap_login_attr)) {
    $ldap_login_attr = 'uid';
}


$tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'url' => 'auth.php'
        )));

// You have to logout from CAS and preferably close your browser
// to change CAS settings
if (!empty($_SESSION['cas_warn']) and $auth == 7) {
    $tool_content .= "<div class='alert alert-warning'>$langCASnochange</div>";
}

if ($submit or ! empty($_SESSION['cas_do'])) {
    if (!empty($_SESSION['cas_do']) and empty($_SESSION['cas_warn'])) {
        // test new CAS settings
        $cas_ret = cas_authenticate(7, true, $_SESSION['cas_host'], $_SESSION['cas_port'], $_SESSION['cas_context'], $_SESSION['cas_cachain']);
        if (phpCAS::checkAuthentication()) {
            $test_username = phpCAS::getUser();
            if (!empty($test_username)) {
                $cas_valid = true;
                $_SESSION['cas_warn'] = true;
            } else {
                $cas_valid = false;
            }
        } else {
            $cas_valid = false;
        }

        if (!empty($cas_ret['error']))
            $tool_content .= "<div class='alert alert-warning'>$cas_ret[error]</div>";
    }

    // if form is submitted
    if (isset($_POST['submit']) or $cas_valid == true) {
        if ($auth == 1) {
            $settings = array();
            $auth_allow = 1; //eclass method doesn't need test
        } else {
            $tool_content .= "<div class='alert alert-info'>$langConnTest</div>";
            if (($auth == 6) or (isset($cas_valid) and $cas_valid == true)) {
                $test_username = $test_password = " ";
            }
            // when we come back from CAS
            if (isset($_SESSION['cas_do']) && $_SESSION['cas_do']) {
                $auth = 7;
            }
            switch ($auth) {
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
                        'ldap_studentid' => $ldap_id_attr);
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
                    $settings = array('shibemail' => $shibemail,
                        'shibuname' => $shibuname,
                        'shibcn' => $shibcn);
                    break;
                case 7:
                    $settings = array('cas_host' => $_SESSION['cas_host'],
                        'cas_port' => $_SESSION['cas_port'],
                        'cas_context' => $_SESSION['cas_context'],
                        'cas_cachain' => $_SESSION['cas_cachain'],
                        'casusermailattr' => $_SESSION['casusermailattr'],
                        'casuserfirstattr' => $_SESSION['casuserfirstattr'],
                        'casuserlastattr' => $_SESSION['casuserlastattr'],
                        'cas_altauth' => $_SESSION['cas_altauth'],
                        'cas_logout' => $_SESSION['cas_logout'],
                        'cas_ssout' => $_SESSION['cas_ssout'],
                        'casuserstudentid' => $_SESSION['casuserstudentid']);
                    $auth_instructions = $_SESSION['auth_instructions'];
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
            if ($test_username !== '' and $test_password !== '') {
                $test_username = canonicalize_whitespace($test_username);
                if (isset($cas_valid) and $cas_valid) {
                    $is_valid = true;
                } else {
                    $is_valid = auth_user_login($auth, $test_username, $test_password, $settings);
                }
                if ($is_valid) {
                    $auth_allow = 1;
                    $tool_content .= "<div class='alert alert-success'>$langConnYes</div>";
                    // Debugging CAS
                    if ($debugCAS) {
                        if (!empty($cas_ret['message']))
                            $tool_content .= "<p>{$cas_ret['message']}</p>";
                        if (!empty($cas_ret['attrs']) && is_array($cas_ret['attrs'])) {
                            $tmp_attrs = "<p>$langCASRetAttr:<br />" . array2html($cas_ret['attrs']);
                            $tool_content .= "$tmp_attrs</p>";
                        }
                    }
                } else {
                    $tool_content .= "<div class='alert alert-danger'>$langConnNo";
                    if (isset($GLOBALS['auth_errors'])) {
                        $tool_content .= "<p>$GLOBALS[auth_errors]</p>";
                    }
                    $tool_content .= "</div>";
                    $auth_allow = 0;
                }
            } elseif ($auth < 8) { //display the wrong username/password message only if the auth method is NOT a hybridauth method
                $tool_content .= "<div class='alert alert-danger'>$langWrongAuth</div>";
                $auth_allow = 0;
            } elseif ($auth >= 8) {
                $auth_allow = 1; //hybridauth provider, so no username-password testing
            }
        } 

        // update table `auth`
        if (!empty($auth_allow) and $auth_allow == 1) {
            if ($auth != 6 && $auth < 8) {
                $auth_settings = pack_settings($settings);
            } elseif ($auth >= 8) {
                $auth_settings = serialize($settings);
            }
            $result = Database::get()->query("UPDATE auth
            			SET auth_settings = ?s,
                            auth_instructions = ?s,
                            auth_default = GREATEST(auth_default, 1),
                            auth_title = ?s,
                            auth_name = ?s
                        WHERE auth_id = ?d",
                function ($error) use(&$tool_content, $langErrActiv) {
                    $tool_content .= "<div class='alert alert-warning'>$langErrActiv</div>";
                }, $auth_settings, $auth_instructions, $auth_title, $auth_ids[$auth], $auth);
            if ($result) {
                if ($result->affectedRows == 1) {
                    $tool_content .= "<div class='alert alert-success'>$langHasActivate</div>";
                } else {
                    $tool_content .= "<div class='alert alert-warning'>$langAlreadyActiv</div>";
                }
            }
        }
    }
} else {
    // handle reloads on auth_process.php after authentication check
    // also handles requests with empty $auth
    // without this, a form with just username/password is displayed
    if (!$auth) {
        redirect_to_home_page('modules/admin/auth.php');
    }

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

    if (!empty($_SESSION['cas_warn']) && $_SESSION['cas_do']) {
        $auth = 7;
        $tool_content .= "<div class='alert alert-warning'>$langCASnochange</div>";
    }
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
        case 8:
        case 9:
        case 10:
        case 11:
        case 12:
        case 13:
            require_once 'modules/auth/methods/hybridauthform.php'; //generic HybridAuth form for provider settings
            hybridAuthForm($auth);
            break;
        default:
            break;
    }
    if ($auth > 1 and $auth < 6) {
        $tool_content .= "
                <div class='alert alert-info'>$langTestAccount</div>
                <div class='form-group'>
                    <label for='test_username' class='col-sm-2 control-label'>$langUsername:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='test_username' id='test_username' value='" . q(canonicalize_whitespace($test_username)) . "' autocomplete='off'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='test_password' class='col-sm-2 control-label'>$langPass:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='password' name='test_password' id='test_password' value='" . q($test_password) . "' autocomplete='off'>
                    </div>
                </div>";
    }
    $tool_content .= "
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langModify'>
                        <a class='btn btn-default' href='auth.php'>$langCancel</a>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>";
}

draw($tool_content, 3);

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

/**
 * @implode settings but only values
 * @param type $settings
 * @return string
 */
function pack_settings_alt($settings) {
    $items = array();
    foreach ($settings as $key => $value) {
        $items[] = "$value";
    }
    return implode('|', $items);
}
