<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langAuthSettings;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);
$debugCAS = true;

if (isset($_REQUEST['auth']) && is_numeric($_REQUEST['auth'])) {
    $data['auth'] = $auth = intval($_REQUEST['auth']); // $auth gets the integer value of the auth method if it is set
    if ($auth == 7) {
        load_js('jstree3');
        load_js('select2');
        $tree = new Hierarchy();

        list($js, $html) = $tree->buildUserNodePicker(['defaults' => [], 'skip_preloaded_defaults' => false]);
        $head_content .= $js;

        $data['buildusernode'] = $html;

        $minedu_school_data = Database::get()->queryArray("
                SELECT CONCAT(md.School,' - ', md.Department) AS School_Department, md.MineduID AS minedu_id, mda.department_id, h.name
                    FROM minedu_department_association AS mda
                    JOIN hierarchy AS h ON mda.department_id = h.id
                    LEFT JOIN minedu_departments AS md ON CONVERT(mda.minedu_id, CHAR) = CONVERT(md.MineduID, CHAR)
                    ORDER BY School_Department");


        $data['minedu_department_association'] = $minedu_department_association = json_encode(array_map(function ($item) {
            return ['minedu_id' => $item->minedu_id ?? 0, 'department_id' => $item->department_id];
        }, $minedu_school_data));

        $minedu_institution = $auth_data['minedu_institution'] ?? '';
        $minedu_institutions = Database::get()->queryArray('SELECT DISTINCT Institution FROM minedu_departments ORDER BY Institution');

        $data['minedu_institutions_select_options'] = implode(array_map(function ($item) {
            global $minedu_institution;
            $institution = q($item->Institution);
            $selected = $item->Institution == $minedu_institution? 'selected': '';
            return "<option value='$institution' $selected>$institution</option>";
        }, $minedu_institutions));

        $data['tdata_minedu_school_data'] = implode(array_map(function ($item) {
            global $langDelete, $langDefaultCategory;
            $minedu_id = $item->minedu_id ?? '-';
            $minedu_name = $item->School_Department ? q($item->School_Department): $langDefaultCategory;
            return "
                <tr>
                    <td>$minedu_name</td>
                    <td>$minedu_id</td>
                    <td>" . q(getSerializedMessage($item->name)) . "</td>
                    <td>{$item->department_id}</td>
                    <td><button class='btn btn-xs delete-entry btn-danger' title='$langDelete' data-id='$minedu_id'><span class='fa fa-times'></span></btn></td>
                 </button>
                </tr>";
        }, $minedu_school_data));
    }
} else {
    $data['auth'] = $auth = false;
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
    'cas_altauth_use' => true, 'minedu_institution' => true, 'cas_gunet' => true, 'minedu_department_association' => true,
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
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($minedu_department_association) {
        $minedu_assoc = json_decode($minedu_department_association, true);
        if ($minedu_assoc !== null) {
            Database::get()->query('DELETE FROM minedu_department_association');
            foreach ($minedu_assoc as $association) {
                Database::get()->query('INSERT INTO minedu_department_association
                    SET minedu_id = ?d, department_id = ?d',
                    $association['minedu_id'], $association['department_id']);
            }
        }
    }

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

            $checked = '';
            if ($auth_data['cas_gunet'] ?? false) {
                $checked = 'checked';
            }
            $data['checked'] = $checked;

            if ($cas_gunet) {
                $settings = array('cas_host' => $cas_host,
                    'cas_port' => '443',
                    'cas_context' => '',
                    'cas_cachain' => $cas_cachain,
                    'casusermailattr' => 'mail',
                    'casuserfirstattr' => 'givenName-lang-el givenName;lang-el givenname',
                    'casuserlastattr' => 'sn-lang-el sn;lang-el sn',
                    'cas_altauth' => '0',
                    'cas_altauth_use' => 'mobile',
                    'cas_logout' => '/logout',
                    'cas_ssout' => '1',
                    'casuserstudentid' => '',
                    'cas_gunet' => $cas_gunet,
                    'minedu_institution' => $minedu_institution);
            } else {
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
                    'cas_gunet' => $cas_gunet);
            }

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
    $auth_settings = serialize($settings);

    $result = Database::get()->query('INSERT INTO auth
        (auth_id, auth_name, auth_settings, auth_instructions, auth_default, auth_title) VALUES
        (?d, ?s, ?s, ?s, 1, ?s) ON DUPLICATE KEY UPDATE
            auth_settings = VALUES(auth_settings),
            auth_instructions = VALUES(auth_instructions),
            auth_default = GREATEST(auth_default, 1),
            auth_title = VALUES(auth_title)',
        function ($error) use (&$tool_content, $langErrActiv) {
            Session::flash('message',$langErrActiv);
            Session::flash('alert-class', 'alert-warning');
        }, $auth, $auth_ids[$auth], $auth_settings, $auth_instructions, $auth_title);

    if ($result) {
        if ($result->affectedRows == 1) {
            Session::flash('message',$langHasActivate);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',$langAlreadyActiv);
            Session::flash('alert-class', 'alert-info');
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
        array('title' => $langBack,
              'icon' => 'fa-reply',
              'level' => 'primary',
              'url' => 'auth.php'),
        array('title' => $langConnTest,
              'url' => "auth_test.php?auth=$auth",
              'icon' => 'fa-plug',
              'level' => 'primary-label',
              'show' => $auth != 1 && get_auth_settings($auth))
        ));

    $pageName = get_auth_info($auth);
    $data['auth_data'] = $auth_data = get_auth_settings($auth);

    $checked ='';
    if ($auth_data['cas_gunet'] ?? false) {
        $checked = 'checked';
    }
    $data['checked'] = $checked;

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
            $r = Database::get()->querySingle("SELECT auth_settings, auth_instructions, auth_name FROM auth WHERE auth_id = ?d", $auth);
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
            $auth_instructions = $r->auth_instructions;
            $authName = q(ucfirst($r->auth_name));
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $callbackUri = "<strong>" . $protocol . "://" . $_SERVER['HTTP_HOST'] . "/</strong>";

            if (in_array($authName, ['Facebook','Twitter', 'Google', 'Linkedin', 'Live', 'Yahoo'], true )) {
                $authHelpUrl = 'https://docs.openeclass.org/el/admin/users_administration/'.strtolower($authName);
                $data['authHelp'] = $langHybridAuthSetup1 . $authName . $langHybridAuthSetup2 . $authName . $langHybridAuthSetup3 . $authHelpUrl . $langHybridAuthSetup4 . $langHybridAuthCallback . $callbackUri;
            } else {
                $data['authHelp'] = "";
            }
        }

    }
}

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
