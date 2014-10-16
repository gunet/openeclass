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


/* ===========================================================================
  auth_process.php
  @last update: 27-06-2006 by Stratos Karatzidis
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: Platform Authentication Methods and their settings

  This script tries to get the values of an authentication method, establish
  a connectiond and with a test account successfully connect to the server.
  Possible scenarios:
  - The settings of the method are fine and the mechanism authenticates the
  test account
  - The settings of the method are fine, but the method does not work
  with the test account
  - The settings are wrong.

  The admin can: - choose a method and define its settings

  ==============================================================================
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/CAS/CAS.php';
require_once 'modules/auth/auth.inc.php';
$nameTools = $langAuthSettings;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);
$debugCAS = true;

$auth = isset($_REQUEST['auth']) ? intval($_REQUEST['auth']) : false;
register_posted_variables(array('imaphost' => true, 'pop3host' => true,
    'ldaphost' => true, 'ldap_base' => true,
    'ldapbind_dn' => true, 'ldapbind_pw' => true,
    'ldap_login_attr' => true, 'ldap_login_attr2' => true,
    'dbhost' => true, 'dbtype' => true, 'dbname' => true,
    'dbuser' => true, 'dbpass' => true, 'dbtable' => true,
    'dbfielduser' => true, 'dbfieldpass' => true, 'dbpassencr' => true,
    'shibemail' => true, 'shibuname' => true,
    'shibcn' => true, 'checkseparator' => true,
    'submit' => true, 'auth_instructions' => true,
    'test_username' => true), 'all', 'autounquote');

// unescapeSimple() preserves whitespace in password
$test_password = isset($_POST['test_password']) ? unescapeSimple($_POST['test_password']) : '';

if ($auth == 7) {
    if ($submit) {
        $_SESSION['cas_do'] = true;
        // $_POST is lost after we come back from CAS
        foreach (array('cas_host', 'cas_port', 'cas_context', 'cas_cachain',
    'casusermailattr', 'casuserfirstattr', 'casuserlastattr',
    'cas_altauth', 'cas_logout', 'cas_ssout', 'auth_instructions') as $var) {
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
        $tool_content .= "<br /><p>$langConnTest</p>";
        if (($auth == 6) or ( isset($cas_valid) and $cas_valid == true)) {
            $test_username = $test_password = " ";
        }
        // when we come back from CAS
        if (isset($_SESSION['cas_do']) && $_SESSION['cas_do']) {
            $auth = 7;
        }
        switch ($auth) {
            case '1':
                $settings = array();
                break;
            case '2':
                $settings = array('pop3host' => $pop3host);
                break;
            case '3':
                $settings = array('imaphost' => $imaphost);
                break;
            case '4':
                $settings = array('ldaphost' => $ldaphost,
                    'ldap_base' => $ldap_base,
                    'ldapbind_dn' => $ldapbind_dn,
                    'ldapbind_pw' => $ldapbind_pw,
                    'ldap_login_attr' => $ldap_login_attr,
                    'ldap_login_attr2' => $ldap_login_attr2);
                break;
            case '5':
                $settings = array('dbhost' => $dbhost,
                    'dbname' => $dbname,
                    'dbuser' => $dbuser,
                    'dbpass' => $dbpass,
                    'dbtable' => $dbtable,
                    'dbfielduser' => $dbfielduser,
                    'dbfieldpass' => $dbfieldpass,
                    'dbpassencr' => $dbpassencr);
                break;
            case '6':
                if ($checkseparator) {
                    $auth_settings = unescapeSimple($_POST['shibseparator']);
                } else {
                    $auth_settings = 'shibboleth';
                }
                $settings = array('shibemail' => $shibemail,
                    'shibuname' => $shibuname,
                    'shibcn' => $shibcn);
                break;
            case '7':
                $settings = array('cas_host' => $_SESSION['cas_host'],
                    'cas_port' => $_SESSION['cas_port'],
                    'cas_context' => $_SESSION['cas_context'],
                    'cas_cachain' => $_SESSION['cas_cachain'],
                    'casusermailattr' => $_SESSION['casusermailattr'],
                    'casuserfirstattr' => $_SESSION['casuserfirstattr'],
                    'casuserlastattr' => $_SESSION['casuserlastattr'],
                    'cas_altauth' => $_SESSION['cas_altauth'],
                    'cas_logout' => $_SESSION['cas_logout'],
                    'cas_ssout' => $_SESSION['cas_ssout']);
                $auth_instructions = $_SESSION['auth_instructions'];
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
                $tool_content .= "<table width='100%'><tbody><tr>
				<td class='alert alert-success'>$langConnYes</td></tr></tbody></table><br /><br />";
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
                $tool_content .= "<table width='100%'><tbody><tr><td class='alert alert-danger'>$langConnNo";
                if (isset($GLOBALS['auth_errors'])) {
                    $tool_content .= "<p>$GLOBALS[auth_errors]</p>";
                }
                $tool_content .= "</td></tr></tbody></table><br /><br />";
                $auth_allow = 0;
            }
        } else {
            $tool_content .= "<table width='100%'><tbody><tr>
			                  <td class='alert alert-danger'>$langWrongAuth</td></tr></tbody></table><br /><br />";
            $auth_allow = 0;
        }

        // store the values - do the updates //
        if (!empty($auth_allow) and $auth_allow == 1) {
            if ($auth != 6) {
                $auth_settings = pack_settings($settings);
            }
            $result = Database::get()->query("UPDATE auth
            			SET auth_settings = ?s,
                            auth_instructions = ?s,
                            auth_default = 1,
                            auth_name = ?s
                        WHERE
                        	auth_id = ?d"
                    , function ($error) use(&$tool_content, $langErrActiv) {
                $tool_content .= "<div class='alert alert-warning'>$langErrActiv</div>";
            }, $auth_settings, $auth_instructions, $auth_ids[$auth], $auth);
            if ($result) {
                if ($result->affectedRows == 1) {
                    $tool_content .= "<div class='alert alert-success'>$langHasActivate</div>";
                } else {
                    $tool_content .= "<div class='alert alert-warning'>$langAlreadyActiv</div>";
                }
            } else {
                
            }
        }
    }
} else {
    // handle reloads on auth_process.php after authentication check
    // also handles requests with empty $auth
    // without this, a form with just username/password is displayed
    if (!$auth) {
        header('Location: ../admin/auth.php');
        exit;
    }
    // Display the form
    // we need to load auth=7 settings
    if ($auth != 6) {
        $auth_data = get_auth_settings($auth);
    }
    $tool_content .= "<form name='authmenu' method='post' action='$_SERVER[SCRIPT_NAME]'>
	<fieldset>
	<legend>" . get_auth_info($auth) . "</legend>
	<table width='100%' class='tbl'><tr>
	<th colspan='2'>
	  <input type='hidden' name='auth' value='" . intval($auth) . "' />
	</th>
	</tr>";

    switch ($auth) {
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
        default:
            break;
    }
    if (!empty($_SESSION['cas_warn']) && $_SESSION['cas_do']) {
        $auth = 7;
        $tool_content .= "<div class='alert alert-warning'>$langCASnochange</div>";
    }
    if ($auth != 6 && $auth != 7) {
        $tool_content .= "<tr><td colspan='2'><div class='alert alert-info'>$langTestAccount</div></td></tr>
		<tr><th width='220' class='left'>$langUsername: </th>
		<td><input size='30' class='FormData_InputText' type='text' name='test_username' value='" . q(canonicalize_whitespace($test_username)) . "' autocomplete='off'></td></tr>
		<tr><th class='left'>$langPass: </th>
		<td><input size='30' class='FormData_InputText' type='password' name='test_password' value='" . q($test_password) . "' autocomplete='off'></td></tr>";
    }
    $tool_content .= "<tr><th>&nbsp;</th><td class='right'><input type='submit' name='submit' value='$langModify'></td></tr>";
    $tool_content .= "</table></fieldset></form>";
}

draw($tool_content, 3);

function pack_settings($settings) {
    $items = array();
    foreach ($settings as $key => $value) {
        $items[] = "$key=$value";
    }
    return implode('|', $items);
}
