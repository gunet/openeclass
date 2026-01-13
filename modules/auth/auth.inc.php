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

/* ===========================================================================
  auth.inc.php
  @last update: 29-07-2015 by Sakis Agorastos
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
                 Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: Functions Library for authentication purposes

  This library includes all the functions for authentication
  and their settings.

  ==============================================================================
 */

require_once 'include/log.class.php';
require_once 'include/lib/user.class.php';
// pop3 class
require_once 'modules/auth/methods/pop3.php';

$auth_ids = [
    1 => 'eclass',
    2 => 'pop3',
    3 => 'imap',
    4 => 'ldap',
    5 => 'db',
    6 => 'shibboleth',
    7 => 'cas',
    8 => 'facebook',
    9 => 'twitter',
    10 => 'google',
    11 => 'live',
    12 => 'yahoo',
    13 => 'linkedin',
    14 => 'lti_publish',
    15 => 'oauth2',
];

$authFullName = [
    8 => 'Facebook',
    9 => 'Twitter',
    10 => 'Google',
    11 => 'Microsoft Live',
    12 => 'Yahoo!',
    13 => 'LinkedIn',
    15 => 'OAuth 2.0',
];

$extAuthMethods = ['cas', 'shibboleth', 'oauth2'];
$hybridAuthMethods = ['facebook', 'twitter', 'google', 'live', 'yahoo', 'linkedin'];


/**
 * get active authentication methods auth_ids
 * @return type
 */
function get_auth_active_methods() {

    $auth_methods = array();
    $q = Database::get()->queryArray("SELECT auth_id FROM auth
                            WHERE auth_default <> 0 AND (auth_settings <> '' OR auth_id = 1)");
    foreach ($q as $row) {
        $auth_methods[] = $row->auth_id;
    }
    return $auth_methods;
}

/**
 * get auth primary method
 * @return int
 */
function get_auth_primary_method() {

    $q = Database::get()->querySingle("SELECT auth_id FROM auth WHERE auth_default = 2");
    if ($q) {
        $auth_primary_method = $q->auth_id;
        return $auth_primary_method;
    } else {
        return 0;
    }

}

/**
 * @brief check if method $auth is active
 * @param type $auth_id
 * @return boolean
 */
function check_auth_active($auth_id) {

    $auth = Database::get()->querySingle("SELECT auth_id, auth_default, auth_settings FROM auth WHERE auth_id = ?d", $auth_id);
    if ($auth and $auth->auth_default and ($auth->auth_id == 1 or !empty($auth->auth_settings))) {
            return true;
    }
    return false;
}

/**
 * @brief check if method $auth is configured
 * @param type $auth_id
 * @return boolean
 */
function check_auth_configured($auth_id) {
    $auth = Database::get()->querySingle("SELECT auth_settings FROM auth WHERE auth_id = ?d", $auth_id);
    if ($auth and !empty($auth->auth_settings)) {
            return true;
    }
    return false;
}


/* * **************************************************************
  count users for each authentication method
  return count of users for auth method
 * ************************************************************** */

function count_auth_users($auth) {
    global $auth_ids;

    $auth = intval($auth);
    if ($auth === 1) {
        $result = Database::get()->querySingle("SELECT COUNT(*) AS total FROM user
                                                    WHERE password NOT IN (SELECT auth_name FROM auth WHERE id > 1)");
    } else {
        $result = Database::get()->querySingle("SELECT COUNT(*) AS total FROM user WHERE password = '" . $auth_ids[$auth] . "'");
    }
    if ($result) {
        return $result->total;
    }
    return 0;
}

/* * **************************************************************
  find/return the string, describing in words the default authentication method
  return $m (string)
 * ************************************************************** */
function get_auth_info($auth)
{
    if(!empty($auth)) {
        $title = Database::get()->querySingle('SELECT auth_title FROM auth WHERE auth_id = ?d', $auth);
        if ($title and $title->auth_title) {
            return getSerializedMessage($title->auth_title);
        }
        switch($auth)
        {
            case '1': $m = $GLOBALS['langViaeClass'];
            break;
            case '2': $m = $GLOBALS['langViaPop'];
            break;
            case '3': $m = $GLOBALS['langViaImap'];
            break;
            case '4': $m = $GLOBALS['langViaLdap'];
            break;
            case '5': $m = $GLOBALS['langViaDB'];
            break;
            case '6': $m = $GLOBALS['langViaShibboleth'];
            break;
            case '7': $m = $GLOBALS['langViaCAS'];
            break;
            case '8': $m = $GLOBALS['langViaFacebook'];
            break;
            case '9': $m = $GLOBALS['langViaTwitter'];
            break;
            case '10': $m = $GLOBALS['langViaGoogle'];
            break;
            case '11': $m = $GLOBALS['langViaLive'];
            break;
            case '12': $m = $GLOBALS['langViaYahoo'];
            break;
            case '13': $m = $GLOBALS['langViaLinkedIn'];
            break;
            case '15': $m = $GLOBALS['langViaOAuth2'];
            break;
            default: $m = 0;
            break;
        }
        return $m;
    } else {
        return 0;
    }
}

/* * **************************************************************
  find/return the settings of the default authentication method

  $auth : integer a value between 1 and 7: 1-eclass,2-pop3,3-imap,4-ldap,5-db,6-shibboleth,7-cas)
  return $auth_row : an associative array
 * ************************************************************** */

function get_auth_settings($auth) {
    global $auth_ids;

    $auth = intval($auth);
    $result = Database::get()->querySingle("SELECT * FROM auth WHERE auth_id = ?d", $auth);
    if (!$result) {
        return 0;
    }

    $settings['auth_id'] = $result->auth_id;
    $settings['auth_settings'] = $result->auth_settings;
    $settings['auth_title'] = $result->auth_title;
    $settings['auth_instructions'] = $result->auth_instructions;
    $settings['auth_default'] = $result->auth_default;
    $settings['auth_name'] = $result->auth_name;

    if ($result->auth_settings and ($decoded = @unserialize($result->auth_settings))) {
        $settings = $settings + $decoded;
    } else {
        foreach (explode('|', $result->auth_settings) as $item) {
            if (preg_match('/(\w+)=(.*)/', $item, $matches)) {
                $settings[$matches[1]] = $matches[2];
            }
        }
    }

    return $settings;
}

/* * **************************************************************
 find/return the settings of a HybridAuth provider

 $provider : a string value in lowercase which corresponds to a
             HybridAuth provider (e.g. facebook, twitter, google,
             live, yahoo, linkedin)
 return $provider_row
* ************************************************************** */

function get_hybridauth_settings($provider) {
    $result = Database::get()->querySingle("SELECT * FROM auth WHERE auth_name = ?s", $provider);
    if ($result && $result->auth_settings) {
        list($provider_id_key, $provider_secret) = explode('|', $result->auth_settings);
        return array ("provider_id_key" => $provider_id_key, "provider_secret" => $provider_secret, "provider_enabled" => $result->auth_enabled);
    } else return array();
}

/* * **************************************************************
  Try to authenticate the user with the admin-defined auth method
  true (the user is authenticated) / false (not authenticated)

  $auth an integer-value for auth method (1:eclass, 2:pop3, 3:imap, 4:ldap, 5:db, 6:shibboleth, 7:cas)
  $test_username
  $test_password
  return $testauth (boolean: true-is authenticated, false-is not)

  Sets the session variable $auth_user_info to an array with the following
  keys, if available from the current auth method:
  givenname (LDAP attribute: givenname)
  surname (LDAP attribute: sn)
  email (LDAP attribute: mail)
  studentid
 * ************************************************************** */

function auth_user_login($auth, $test_username, $test_password, $settings) {
    $testauth = false;
    switch ($auth) {
        case '1':
            $unamewhere = (get_config('case_insensitive_usernames')) ? "COLLATE utf8mb4_general_ci = " : "COLLATE utf8mb4_bin = ";
            $result = Database::get()->querySingle("SELECT password FROM user WHERE username $unamewhere ?s", $test_username);
            if ($result) {
                if (password_verify($test_password, $result->password)) {
                    $testauth = true;
                } else if (strlen($result->password) < 60 && md5($test_password) == $result->password) {
                    $testauth = true;
                    // password is in old md5 format, update transparently
                    $password_encrypted = password_hash($test_password, PASSWORD_DEFAULT);
                    Database::get()->query("UPDATE user SET password = ?s WHERE id = ?d", $password_encrypted, $result->id);
                }
            }
            break;

        case '2':
            $pop3 = new pop3_class;
            $pop3->hostname = $settings['pop3host'];    // POP 3 server host name
            $pop3->port = 110;                          // POP 3 server host port
            $user = $test_username;                     // Authentication user name
            $password = $test_password;                 // Authentication password
            $pop3->realm = '';                          // Authentication realm or domain
            $pop3->workstation = '';                    // Workstation for NTLM authentication
            $apop = 0;                                  // Use APOP authentication
            $pop3->authentication_mechanism = 'USER';   // SASL authentication mechanism
            $pop3->debug = 0;                           // Output debug information
            $pop3->html_debug = 1;                      // Debug information is in HTML
            $pop3->join_continuation_header_lines = 1;  // Concatenate headers split in multiple lines

            if (($error = $pop3->Open()) == '') {
                if (($error = $pop3->Login($user, $password, $apop)) == '') {
                    if ($error == '' and ($error = $pop3->Close()) == '') {
                        $testauth = true;
                    }
                }
            }
            if ($error != '') {
                $testauth = false;
            }
            break;

        case '3':
            $imaphost = $settings['imaphost'];
            $imapauth = imap_auth($imaphost, $test_username, $test_password);
            if ($imapauth) {
                $testauth = true;
            }
            break;

        case '4':
            $ldap = ldap_connect($settings['ldaphost']);
            if (!$ldap) {
                $GLOBALS['auth_errors'] = ldap_error($ldap);
                return false;
            } else {
                // LDAP connection established - now search for user dn
                @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                @ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0); // for search in Active Directory
                if (@ldap_bind($ldap, $settings['ldapbind_dn'], $settings['ldapbind_pw'])) {
                    $search_filter = "($settings[ldap_login_attr]=$test_username)";
                    if (isset($settings['ldap_login_attr2']) and !empty($settings['ldap_login_attr2'])) {
                        $search_filter = "(|$search_filter($settings[ldap_login_attr2]=$test_username))";
                    }
                    $userinforequest = ldap_search($ldap, $settings['ldap_base'], $search_filter);
                    if ($userinforequest and ($entry_id = ldap_first_entry($ldap, $userinforequest))) {
                        $user_dn = ldap_get_dn($ldap, $entry_id);
                        if (@ldap_bind($ldap, $user_dn, $test_password)) {
                            $testauth = true;
                            $userinfo = ldap_get_entries($ldap, $userinforequest);
                            foreach ($userinfo[0] as $key => $value) {
                                if (!is_numeric($key)) {
                                    if (is_array($value)) {
                                        if ($value['count'] == 1) {
                                            $GLOBALS['auth_userinfo'][strtolower($key)] = $value[0];
                                        } else {
                                            unset($value['count']);
                                            $GLOBALS['auth_userinfo'][strtolower($key)] = $value;
                                        }
                                    } else {
                                        $GLOBALS['auth_userinfo'][strtolower($key)] = $value;
                                    }
                                }
                            }
                            if ($userinfo['count'] == 1) {
                                $surname = $givenname = '';
                                if (isset($settings['ldap_surname_attr']) and !empty($settings['ldap_surname_attr'])) {
                                    // find ldap surname attribute
                                    $attr_surname = explode(' ', $settings['ldap_surname_attr']);
                                    foreach ($attr_surname as $asurname) {
                                        $l_surname = get_ldap_attribute($userinfo, $asurname);
                                        if (!empty($l_surname)) {
                                            $surname = $l_surname;
                                            break;
                                        }
                                    }
                                } else {
                                    $surname = get_ldap_attribute($userinfo, 'sn');
                                }
                                if (isset($settings['ldap_firstname_attr']) and !empty($settings['ldap_firstname_attr'])) {
                                    // find ldap name attribute
                                    $attr_givenname = explode(' ', $settings['ldap_firstname_attr']);
                                    foreach ($attr_givenname as $agivenname) {
                                        $l_givenname = get_ldap_attribute($userinfo, $agivenname);
                                        if (!empty($l_givenname)) {
                                            $givenname = $l_givenname;
                                            break;
                                        }
                                    }
                                } else {
                                    $givenname = get_ldap_attribute($userinfo, 'givenname');
                                }
                                $_SESSION['auth_user_info'] = array(
                                    'attributes' => get_ldap_attributes($userinfo),
                                    'givenname' => $givenname,
                                    'surname' => $surname,
                                    'email' => get_ldap_attribute($userinfo, 'mail'));
                                if (isset($settings['ldap_studentid']) and !empty($settings['ldap_studentid'])) {
                                    $_SESSION['auth_user_info']['studentid'] =
                                        get_ldap_attribute($userinfo, $settings['ldap_studentid']);
                                }
                            }
                        }
                    }
                } else {
                    $GLOBALS['auth_errors'] = ldap_error($ldap);
                    return false;
                }
                @ldap_unbind($ldap);
            }
            break;

        case '5':
            try {
                $link = new Database($settings['dbhost'], $settings['dbname'], $settings['dbuser'], $settings['dbpass']);
            } catch(Exception $ex) {
                break;
            }
            if ($link) {
                $res = $link->querySingle("SELECT `$settings[dbfieldpass]`
                                            FROM `$settings[dbtable]`
                                            WHERE `$settings[dbfielduser]` = ?s", $test_username);
                if ($res) {
                    $field = $settings['dbfieldpass'];
                    $testauth = external_DB_Check_Pass($test_password, $res->$field, $settings['dbpassencr']);
                }
            }
            break;

        case '6':
            return false; // this function doesn't support Shibboleth authentication
            break;

        case '7':
            cas_authenticate($auth);
            if (phpCAS::checkAuthentication()) {
                $testauth = true;
            }
            break;
    }
    return $testauth;
}

/* * **************************************************************
  Check if an account is active or not. Apart from admin, everybody has
  a registration timestamp and an expiration timestamp.
  By default is set to last a year

  $userid : the id of the account
  return $testauth (boolean: true-is authenticated, false-is not)
 * ************************************************************** */
function is_active_account($userid, $eclass_auth = true) {

    if ($eclass_auth) {
        if (get_config('block_duration_account')) { // user accounts never expire.
            Database::get()->query("UPDATE user SET expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE id = ?d", $userid);
            return 1;
        }
    } elseif (get_config('block_duration_alt_account')) { // user accounts never expire.
        Database::get()->query("UPDATE user SET expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE id = ?d", $userid);
        return 1;
    }
    $result = Database::get()->querySingle("SELECT expires_at FROM user WHERE id = ?d", $userid);
    if (!empty($result) && strtotime($result->expires_at) > time()) {
        return 1;
    } else {
        return 0;
    }
}

/* * **************************************************************
  Return the value of an attribute from the result of an
  LDAP search, converted to the current charset.
 * ************************************************************** */

function get_ldap_attribute($search_result, $attribute) {
    $attribute = strtolower($attribute);
    if (isset($search_result[0][$attribute][0])) {
        return iconv('UTF-8', $GLOBALS['charset'], $search_result[0][$attribute][0]);
    } else {
        return '';
    }
}

// Return an array of LDAP attributes
function get_ldap_attributes($search_result, $flatten = false) {
    $attrs = array();
    foreach ($search_result[0] as $key => $val) {
        if (!is_numeric($key) and isset($val['count'])) {
            if ($val['count'] > 1 and $flatten) {
                unset($val['count']);
                $attrs[$key] = implode(', ', $val);
            } else {
                $attrs[$key] = $val[0];
            }
        }
    }
    return $attrs;
}

/* * **************************************************************
  CAS authentication
  if $new is false then we use stored settings from db
  if $new in true then we use new connection settings
  from the rest of the arguments
  Returns array of messages, errors
 * ************************************************************** */

function cas_authenticate($auth, $new = false, $cas_host = null, $cas_port = null, $cas_context = null, $cas_cachain = null) {
    global $langConnectWith, $langNotSSL, $urlServer;

    // SESSION does not exist if user has not been authenticated
    $ret = array();

    if (!$new) {
        $cas = get_auth_settings($auth);
        if ($cas) {
            $cas_host = $cas['cas_host'];
            $cas_port = $cas['cas_port'];
            $cas_context = $cas['cas_context'];
            $cas_cachain = $cas['cas_cachain'];
            $casusermailattr = $cas['casusermailattr'];
            $casuserfirstattr = $cas['casuserfirstattr'];
            $casuserlastattr = $cas['casuserlastattr'];
            if (isset($cas['casuserstudentid']) and $cas['casuserstudentid']) {
                $casuserstudentid = $cas['casuserstudentid'];
            }
            $cas_altauth = $cas['cas_altauth'];
        }
    }
    if ($new or $cas) {
        $cas_url = 'https://' . $cas_host;
        $cas_port = intval($cas_port);
        if ($cas_port != '443') {
            $cas_url = $cas_url . ':' . $cas_port;
        }
        $cas_url = $cas_url . $cas_context;

        // The "real" hosts that send SAML logout messages
        // Assumes the cas server is load balanced across multiple hosts
        $cas_real_hosts = array($cas_host);

        // Uncomment to enable debugging
        // phpCAS::setDebug();

        // Initialize phpCAS - keep session in application
        $ret['message'] = "$langConnectWith $cas_url";
        $url_info = parse_url($urlServer);
        $service_base_url = "$url_info[scheme]://$url_info[host]";
        phpCAS::client(SAML_VERSION_1_1, $cas_host, $cas_port, $cas_context, $service_base_url, FALSE);

        // Set the CA certificate that is the issuer of the cert on the CAS server
        if (isset($cas_cachain) && !empty($cas_cachain) && is_readable($cas_cachain))
            phpCAS::setCasServerCACert($cas_cachain);
        else {
            phpCAS::setNoCasServerValidation();
            $ret['error'] = "$langNotSSL";
        }
        // Force renewal of CAS login during transition
        if (isset($GLOBALS['transition_script'])) {
             phpCAS::renewAuthentication();
        }
        // Single Sign Out
        //phpCAS::handleLogoutRequests(true, $cas_real_hosts);
        // Force CAS authentication on any page that includes this file
        phpCAS::forceAuthentication();

        //$ret['attrs'] = get_cas_attrs(phpCAS::getAttributes(), $cas);
        if (phpCAS::checkAuthentication())
            $ret['attrs'] = phpCAS::getAttributes();

        return $ret;
    } else {
        return null;
    }
}

/* * **************************************************************
  Return CAS attributes[]
 * ************************************************************** */

function get_cas_attrs($phpCASattrs, $settings) {
    if (empty($phpCASattrs) || empty($settings))
        return null;

    $attrs = array();
    foreach ($phpCASattrs as $key => $value) {
        $key = strtolower($key);
        // multivalue: get only the first attribute
        if (is_array($value))
            $attrs[$key] = $value[0];
        else
            $attrs[$key] = $value;
    }

    $ret = array();
    foreach (array('email' => 'casusermailattr',
                   'givenname' => 'casuserfirstattr',
                   'surname' => 'casuserlastattr',
                   'studentid' => 'casuserstudentid') as $name => $attrname) {
        $_SESSION['auth_user_info'][$name] = $ret[$name] = '';
        $attrnames = explode(' ', $settings[$attrname]);
        foreach ($attrnames as $anam) {
            $anam = strtolower($anam);
            if (isset($attrs[$anam])) {
                $_SESSION['auth_user_info'][$name] = $ret[$name] = $attrs[$anam];
                break;
            }
        }
    }
    return $ret;
}


/**
 * @brief  Process login form submission
 */
function process_login() {
    global $warning, $session, $langInvalidId, $langAccountInactive1, $langInvalidAuth,
        $langAccountInactive2, $langNoCookies, $langEnterPlatform, $urlServer,
        $langHere, $auth_ids, $inactive_uid, $langTooManyFails, $urlAppend;

    if (isset($_POST['uname'])) {
        $posted_uname = canonicalize_whitespace($_POST['uname']);
    } else {
        $posted_uname = '';
    }

    $pass = isset($_POST['pass']) ? trim($_POST['pass']): '';
    $auth = get_auth_active_methods();

    $ip = Log::get_client_ip();

    if (isset($_POST['submit'])) {
        unset($_SESSION['uid']);
        $auth_allow = 0;

        if (get_config('login_fail_check')) {
            $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = ?s
                                        AND COUNT > " . intval(get_config('login_fail_threshold')) . "
                                        AND DATE_SUB(CURRENT_TIMESTAMP,
                                                interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail",
                                    $ip);
        }
        if (get_config('login_fail_check') && $r) {
            $auth_allow = 8;
        } else {
            if (get_config('case_insensitive_usernames')) {
                $sqlLogin = "COLLATE utf8mb4_general_ci = ?s";
            } else {
                $sqlLogin = "COLLATE utf8mb4_bin = ?s";
            }
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname, password,
                                    username, status, email, lang, verified_mail, am, options
                                FROM user WHERE username $sqlLogin", $posted_uname);
            $guest_user = get_config('course_guest') != 'off' && $myrow && $myrow->status == USER_GUEST;

            // cas might have alternative authentication defined
            $exists = 0;
            if (!isset($_COOKIE) or count($_COOKIE) == 0) {
                // Disallow login when cookies are disabled
                $auth_allow = 5;
            } elseif ($pass === '' and !$guest_user) {
                // Disallow login with empty password except for course guest users
                $auth_allow = 4;
            } else {
                if ($myrow) {
                    $exists = 1;
                    if (!empty($auth)) {
                        if (in_array($myrow->password, $auth_ids)) {
                            // alternate methods login
                            $auth_allow = alt_login($myrow, $posted_uname, $pass);
                        } else {
                            // eclass login
                            $auth_allow = login($myrow, $posted_uname, $pass);
                        }
                    } else {
                        $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInvalidAuth</span></div>";
                    }
                }
            }
            if (!$exists and !$auth_allow) {
                Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $posted_uname));
                $auth_allow = 4;
            }
        }

        $invalidIdMessage = $langInvalidId;
        if (!isset($_SESSION['uid'])) {
            switch ($auth_allow) {
                case 1:
                    session_regenerate_id();
                    break;
                case 2:
                    if (isset($_GET['login_page'])) {
                        Session::flash('login_error', $invalidIdMessage);
                        redirect_to_home_page('main/login_form.php');
                    } else {
                        $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$invalidIdMessage</span></div>";
                    }
                    break;
                case 3: $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAccountInactive1 " .
                            "<a href='modules/auth/contactadmin.php?userid=$inactive_uid&amp;h=" .
                            token_generate("userid=$inactive_uid") . "'>$langAccountInactive2</a></span></div>";
                    break;
                case 4:
                    if (isset($_GET['login_page'])) {
                        Session::flash('login_error', $invalidIdMessage);
                        if ($_GET['login_page'] == 'toolbox') {
                            redirect_to_home_page('main/toolbox.php');
                        } else {
                            redirect_to_home_page('main/login_form.php');
                        }
                    } else {
                        $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$invalidIdMessage</span></div>";
                        increaseLoginFailure();
                    }
                    break;
                case 5: $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCookies</span></div>";
                    break;
                case 6: $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEnterPlatform <a href='{$urlServer}secure/index.php'>$langHere</a></span></div>";
                    break;
                case 7: $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEnterPlatform <a href='{$urlServer}modules/auth/cas.php'>$langHere</a></span></div>";
                    break;
                case 8: $warning .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langTooManyFails</span></div>";
                    break;
                default:
                    break;
            }
            if ($warning and isset($_GET['login_page']) and $_GET['login_page'] == 'toolbox') {
                Session::flash('login_warning', $warning);
                redirect_to_home_page('main/toolbox.php');
            }
        } else {
            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                    VALUES (?d, ?s, " . DBHelper::timeAfter() . ", 'LOGIN')", $_SESSION['uid'], $ip);
            $session->setLoginTimestamp();

            if (!is_null($myrow->options)) {
                $options = json_decode($myrow->options, true);
                $option_force_password_change = $options['force_password_change'];
                if ($option_force_password_change == 1) {
                    $_SESSION['force_password_change'] = 1;
                    $next = 'modules/auth/password_change.php';
                }
            } elseif (get_config('email_verification_required') and
                    get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
                $_SESSION['mail_verification_required'] = 1;
                $next = 'modules/auth/mail_verify_change.php';
            } elseif (isset($_POST['next'])) {
                $next = $_POST['next'];
            } else {
                $next = '';
            }
            resetLoginFailure();
            redirect_to_home_page($next);
        }
    }  // end of user authentication
}

/* * **************************************************************
 Authenticate user via HybridAuth (Twitter, Google, Facebook,
 Yahoo, Live accounts)
* ************************************************************** */

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;

function hybridauth_login() {
    global $surname, $givenname, $email, $status, $language,
        $langInvalidId, $langAccountInactive1, $langAccountInactive2,
        $langNoCookies, $langEnterPlatform, $urlServer, $langHere, $auth_ids,
        $inactive_uid, $langTooManyFails, $warning, $langGeneralError,
        $langProviderError1, $langProviderError2, $langProviderError3,
        $langProviderError4, $langProviderError5, $langProviderError6,
        $langProviderError7, $langProviderError8, $session;


    require_once 'modules/auth/methods/hybridauth/config.php';

    $config = get_hybridauth_config();
    $_SESSION['canChangePassword'] = false;
    $autoregister = get_config('alt_auth_stud_reg') == 2;

    // check for errors and whatnot
    $warning = '';

    if (isset($_GET['error'])) {
        Session::flash('message',q(trim(strip_tags($_GET['error']))));
        Session::flash('alert-class', 'alert-warning');
    }

    $ip = Log::get_client_ip();

    // if user select a provider to login with then include hybridauth config
    // and main class, try to authenticate, finally redirect to profile
    if (isset($_GET['provider'])) {
        if($_GET['provider'] == 'live'){
            $provider = 'WindowsLive';
        } else {
            $provider = @trim(strip_tags($_GET['provider']));
        }
        try {
            if(isset($_SESSION['hybridauth_callback']) && $_SESSION['hybridauth_callback'] == 'login'){
                unset($_SESSION['hybridauth_callback']);
                if(isset($_SESSION['hybridauth_provider'])) unset($_SESSION['hybridauth_provider']);
            } else {
                $_SESSION['hybridauth_callback'] = 'login';
                $_SESSION['hybridauth_provider'] = $provider;
            }
            /**
             * Feed configuration array to Hybridauth.
             */
            $hybridauth = new Hybridauth($config);
            $hybridauth->authenticate($provider);
            $adapters = $hybridauth->getConnectedAdapters();
            foreach ($adapters as $name => $adapter) :
                $user_data = $adapter->getUserProfile();
            endforeach;
            /**
             * This will erase the current user authentication data from session, and any further
             * attempt to communicate with provider.
             */
            if (isset($_GET['logout'])) {
                $adapter = $hybridauth->getAdapter($_GET['logout']);
                $adapter->disconnect();
            }
        } catch (Exception $e) {
            // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                // let hybridauth forget all about the user so we can try to authenticate again.

                // Display the received error,
                // to know more please refer to Exceptions handling section on the user guide
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

                // debug messages for hybridauth errors
                //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
                //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";

                return false;
        }
    } //endif( isset( $_GET["provider"] ) && $_GET["provider"] )

    // from here on an alternative version of process_login() runs where
    // instead of a password, the provider uid is used and matched against
    // the corresponding field in the db table.

    $pass = $user_data->identifier; // password = provider user id

    unset($_SESSION['uid']);
    $auth_allow = 0;

    if (get_config('login_fail_check')) {
        $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = ?s
                                       AND COUNT > " . intval(get_config('login_fail_threshold')) . "
                                       AND DATE_SUB(CURRENT_TIMESTAMP,
                                            interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail",
                                 $ip);
    }
    if (get_config('login_fail_check') && $r) {
        $auth_allow = 8;
    } else {
	if($provider == 'WindowsLive') {
		$provider = 'live';
	}
        $auth_id = array_search(strtolower($provider), $auth_ids);
        $auth_methods = get_auth_active_methods();
        $myrow = Database::get()->querySingle("SELECT user.id, surname,
                    givenname, password, username, status, email, lang,
                    verified_mail, uid, am
                FROM user, user_ext_uid
                WHERE user.id = user_ext_uid.user_id AND
                      user_ext_uid.auth_id = ?d AND
                      user_ext_uid.uid = ?s",
            $auth_id, $user_data->identifier);
        $exists = 0;
        if (!isset($_COOKIE) or count($_COOKIE) == 0) {
            // Disallow login when cookies are disabled
            $auth_allow = 5;
        } elseif ($myrow) {
            $exists = 1;
            if (in_array($auth_id, $auth_methods)) {
                $auth_allow = login($myrow, null, null, $provider, $user_data);
            } else {
                Session::flash('message', $langInvalidAuth);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page();
            }
        }
        if (!$exists and !$auth_allow) {
            if ($autoregister and !get_config('am_required') and
                ($user_data->email or !get_config('email_required')) and
                ($user_data->emailVerified or !get_config('email_verification_required'))) {
                $verified_mail = EMAIL_UNVERIFIED;
                if ($user_data->emailVerified) {
                    $email = $user_data->email;
                    $verified_mail = EMAIL_VERIFIED;
                } else {
                    $email = $user_data->email;
                }
                if (is_null($email)) {
                    $email = '';
                }
                $options = login_hook(array(
                    'user_id' => null,
                    'attributes' => array('user_data' => $user_data),
                    'am' => ''));
                if (!$options['accept']) {
                    deny_access();
                }
                $status = $options['status'];
                $unameSuffix = $uname = null;
                do {
                    if (isset($user_data->username) and $user_data->username) {
                        $uname = $user_data->username;
                    } elseif ($user_data->email) {
                        $uname = $user_data->email;
                    } elseif ($user_data->displayName) {
                        $uname = $user_data->displayName;
                    } else {
                        $uname = $user_data->identifier;
                    }
                    $uname .= $unameSuffix++;
                } while (user_exists($uname));
                if (!(isset($user_data->lastName) and isset($user_data->firstName) and $user_data->lastName and $user_data->firstName)) {
                    if (isset($user_data->lastName) and $user_data->lastName) {
                        $name = $user_data->lastName;
                    } elseif (isset($user_data->firstName) and $user_data->firstName) {
                        $name = $user_data->firstName;
                    } else {
                        $name = $user_data->displayName;
                    }
                    $parts = explode(' ', $name);
                    $parts[] = '';
                    list($givenname, $surname) = $parts;
                } else {
                    $surname = $user_data->lastName;
                    $givenname = $user_data->firstName;
                }
                $_SESSION['uid'] = Database::get()->query("INSERT INTO user
                    SET surname = ?s, givenname = ?s, password = ?s,
                        username = ?s, email = ?s, status = ?d, lang = ?s,
                        am = ?s, verified_mail = ?d,
                        registered_at = " . DBHelper::timeAfter() . ",
                        expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                        whitelist = ''",
                        $surname, $givenname, $provider, $uname, $email, $status,
                        $language, $options['am'], $verified_mail)->lastInsertID;
                if ($_SESSION['uid']) {
                    $_SESSION['uname'] = $uname;
                    $_SESSION['surname'] = $surname;
                    $_SESSION['givenname'] = $givenname;
                    $_SESSION['email'] = $email;
                    $_SESSION['status'] = $status;
                    Database::get()->query('INSERT INTO user_ext_uid
                        (user_id, auth_id, uid) VALUES (?d, ?d, ?s)',
                        $_SESSION['uid'], $auth_id, $user_data->identifier);
                    // update personal calendar info table
                    Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $_SESSION['uid']);
                    $userObj = new User();
                    $userObj->refresh($_SESSION['uid'], $options['departments']);
                    user_hook($_SESSION['uid']);
                } else {
                    Session::flash('message',$langGeneralError);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page();
                }
            } else {
                // Since HybridAuth was used and no user id matched
                // in the DB, send the user to the registration form.
                redirect_to_home_page('modules/auth/registration.php?provider=' . $provider);
            }
        }
    }

    if (!isset($_SESSION['uid'])) {
        switch ($auth_allow) {
            case 1:
                session_regenerate_id();
                break;
            case 2:
                $warning .= "<p class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInvalidId</span></p>";
                break;
            case 3:
                $warning .= "<p class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAccountInactive1 " .
                    "<a href='modules/auth/contactadmin.php?userid=$inactive_uid&amp;h=" .
                    token_generate("userid=$inactive_uid") . "'>$langAccountInactive2</a></span></p>";
                break;
            case 4:
                $warning .= "<p class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInvalidId</span></p>";
                increaseLoginFailure();
                break;
            case 5:
                $warning .= "<p class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCookies</span></p>";
                break;
            case 6:
                $warning .= "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langEnterPlatform <a href='{$urlServer}secure/index.php'>$langHere</a></span></p>";
                break;
            case 7:
                $warning .= "<p class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langEnterPlatform <a href='{$urlServer}modules/auth/cas.php'>$langHere</a></span></p>";
                break;
            case 8:
                $warning .= "<p class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langTooManyFails</span></p>";
                break;
        }
    } else {
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action) "
                . "VALUES (?d, ?s, NOW(), 'LOGIN')", $_SESSION['uid'], $ip);
        if (get_config('email_verification_required') and
            get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
            $_SESSION['mail_verification_required'] = 1;
            $next = "modules/auth/mail_verify_change.php";
        } elseif (isset($_POST['next'])) {
            $next = $_POST['next'];
        } else {
            $next = '';
        }
        $session->setLoginTimestamp();
        $session->setLoginMethod($provider);
        resetLoginFailure();
        redirect_to_home_page($next);
    }
}

/**
 * @brief Authenticate user via eclass
 * @global type $session
 * @global array $auth_ids
 * @param type $user_info_object
 * @param type $posted_uname
 * @param type $pass
 * @param type $provider
 * @param type $user_data
 * @return int
 */
function login($user_info_object, $posted_uname, $pass, $provider=null, $user_data=null) {
    global $session, $auth_ids;

    $_SESSION['canChangePassword'] = false;
    $_SESSION['provider'] = $provider;
    $pass_match = false;

    if (is_null($provider)) {
        if (check_username_sensitivity($posted_uname, $user_info_object->username)) {
            if (password_verify($pass, $user_info_object->password)) {
                $pass_match = true;
            } elseif (strlen($user_info_object->password) < 60 and md5($pass) == $user_info_object->password) {
                $pass_match = true;
                // password is in old md5 format, update transparently
                $password_encrypted = password_hash($pass, PASSWORD_DEFAULT);
                $user_info_object->password = $password_encrypted;
                Database::core()->query("SET sql_mode = TRADITIONAL");
                Database::get()->query("UPDATE user SET password = ?s WHERE id = ?d", $password_encrypted, $user_info_object->id);
            } elseif (get_config('course_guest') != 'off' and $user_info_object->status = USER_GUEST and
                $pass === '' and $user_info_object->password === '') {
                // special case for guest login with empty password
                $pass_match = true;
            }
        }
    } else {
        // User was authenticated by HybridAuth
        $pass_match = true;
    }

    $attributes = array();
    if (!is_null($user_data)) {
        $attributes['user_data'] = $user_data;
    }
    $userObj = new User();
    $options = login_hook(array(
        'user_id' => $user_info_object->id,
        'attributes' => $attributes,
        'status' => $user_info_object->status,
        'departments' => $userObj->getDepartmentIds($user_info_object->id),
        'am' => $user_info_object->am));

    if (!$options['accept']) {
        deny_access();
    }

    if ($pass_match) {
        // check if account is active
        if ($user_info_object->status == USER_GUEST) {
            $is_active = get_config('course_guest') != 'off';
        } else {
            $is_active = is_active_account($user_info_object->id, true);

            // check for admin privileges
            $admin_rights = get_admin_rights($user_info_object->id);
            if ($admin_rights == ADMIN_USER) {
                $is_active = 1;   // admin user is always active
                $_SESSION['is_admin'] = 1;
            } elseif ($admin_rights == POWER_USER) {
                $_SESSION['is_power_user'] = 1;
            } elseif ($admin_rights == USERMANAGE_USER) {
                $_SESSION['is_usermanage_user'] = 1;
            } elseif ($admin_rights == DEPARTMENTMANAGE_USER) {
                $_SESSION['is_departmentmanage_user'] = 1;
            }
        }
        if ($is_active) {
            if ($options['status'] != $user_info_object->status) {
                // update user status
                $user_info_object->status = $options['status'];
                Database::get()->query('UPDATE user SET status = ?d WHERE id = ?d',
                    $options['status'], $user_info_object->id);
            }
            if ($options['am'] != $user_info_object->am) {
                // update student ID
                $user_info_object->am = $options['am'];
                Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d',
                    $options['am'], $user_info_object->id);
                $_SESSION['auth_user_info']['studentid'] = $options['am'];
            }
            $userObj->refresh($user_info_object->id, $options['departments']);
            if (!array_search($user_info_object->password, $auth_ids)) {
                $_SESSION['canChangePassword'] = true;
            }
            $_SESSION['uid'] = $user_info_object->id;
            $_SESSION['uname'] = $user_info_object->username;
            $_SESSION['surname'] = $user_info_object->surname;
            $_SESSION['givenname'] = $user_info_object->givenname;
            $_SESSION['status'] = $user_info_object->status;
            $_SESSION['email'] = $user_info_object->email;
            $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_object->lang;
            $auth_allow = 1;
            user_hook($user_info_object->id);
            $session->setLoginTimestamp();
            $session->setLoginMethod('eclass');
        } else {
            $auth_allow = 3;
            $GLOBALS['inactive_uid'] = $user_info_object->id;
        }
    } else {
        $auth_allow = 4; // means wrong password
        Log::record(0, 0, LOG_LOGIN_FAILURE,
            array('uname' => $posted_uname));
    }

    return $auth_allow;
}

/* * **************************************************************
  Authenticate user via alternate defined methods
 * ************************************************************** */

function alt_login($user_info_object, $uname, $pass, $mobile = false) {
    global $warning, $auth_ids, $langInvalidAuth, $session;

    $_SESSION['canChangePassword'] = false;
    $auth = array_search($user_info_object->password, $auth_ids);
    $auth_method_settings = get_auth_settings($auth);
    $auth_allow = 1;

    // a CAS user might enter a username/password in the form, instead of doing CAS login
    // check auth according to the defined alternative authentication method of CAS
    if ($auth == 7) {
        $cas_settings = get_auth_settings($auth);
        $cas_altauth = intval($cas_settings['cas_altauth']);
        $use_altauth = $mobile ||
            (isset($cas_settings['cas_altauth_use']) &&
             $cas_settings['cas_altauth_use'] == 'all');
        if ($use_altauth and $cas_altauth > 0 and
                check_auth_configured($cas_altauth)) {
            $auth = $cas_altauth;
            // fetch settings of alt auth
            $auth_method_settings = get_auth_settings($auth);
            $user_info_object->password = $auth_method_settings['auth_name'];
        } else {
            return 7; // Redirect to CAS login
        }
    }

    if ($auth == 6) {
        return 6; // Redirect to Shibboleth login
    }

    $GLOBALS['auth_userinfo'] = [];
    if ($user_info_object->password == $auth_method_settings['auth_name']) {
        $is_valid = auth_user_login($auth, $uname, $pass, $auth_method_settings);
        if ($is_valid) {
            $is_active = is_active_account($user_info_object->id, false);
            // check for admin privileges
            $admin_rights = get_admin_rights($user_info_object->id);
            if ($admin_rights == ADMIN_USER) {
                $is_active = 1;   // admin user is always active
                $_SESSION['is_admin'] = 1;
            } elseif ($admin_rights == POWER_USER) {
                $_SESSION['is_power_user'] = 1;
            } elseif ($admin_rights == USERMANAGE_USER) {
                $_SESSION['is_usermanage_user'] = 1;
            } elseif ($admin_rights == DEPARTMENTMANAGE_USER) {
                $_SESSION['is_departmentmanage_user'] = 1;
            }
            if ($is_active) {
                $auth_allow = 1;
            } else {
                $auth_allow = 3;
                $GLOBALS['inactive_uid'] = $user_info_object->id;
            }
        } else {
            $auth_allow = 2;
            // log invalid logins
            Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $uname));
        }
        if ($auth_allow == 1) {
            $userObj = new User();

            $options = login_hook(array(
                'user_id' => $user_info_object->id,
                'attributes' => $GLOBALS['auth_userinfo'],
                'status' => $user_info_object->status,
                'departments' => $userObj->getDepartmentIds($user_info_object->id),
                'am' => $user_info_object->am));

            if (!$options['accept']) {
                deny_access();
            }

            if ($options['status'] != $user_info_object->status) {
                // update user status
                $user_info_object->status = $options['status'];
                Database::get()->query('UPDATE user SET status = ?d WHERE id = ?d',
                    $options['status'], $user_info_object->id);
            }
            if ($options['am'] != $user_info_object->am) {
                // update student ID
                $user_info_object->am = $options['am'];
                Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d',
                    $options['am'], $user_info_object->id);
            }

            $userObj->refresh($user_info_object->id, $options['departments']);
            user_hook($user_info_object->id);

            $_SESSION['uid'] = $user_info_object->id;
            $_SESSION['uname'] = $user_info_object->username;
            // if ldap entries have changed update database
            if (!empty($_SESSION['auth_user_info']['givenname']) and
                !empty($_SESSION['auth_user_info']['surname']) and
                ($user_info_object->givenname != $_SESSION['auth_user_info']['givenname'] or
                 $user_info_object->surname != $_SESSION['auth_user_info']['surname'])) {
                Database::get()->query("UPDATE user SET givenname = ?s, surname = ?s
                                                    WHERE id = ?d",
                    $_SESSION['auth_user_info']['givenname'],
                    $_SESSION['auth_user_info']['surname'],
                    $user_info_object->id);
                $_SESSION['surname'] = $_SESSION['auth_user_info']['surname'];
                $_SESSION['givenname'] = $_SESSION['auth_user_info']['givenname'];
            } else {
                $_SESSION['surname'] = $user_info_object->surname;
                $_SESSION['givenname'] = $user_info_object->givenname;
            }
            if (!empty($_SESSION['auth_user_info']['studentid']) and
                $user_info_object->am != $_SESSION['auth_user_info']['studentid']) {
                Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d',
                    $_SESSION['auth_user_info']['studentid'],
                    $user_info_object->id);
            }
            $_SESSION['status'] = $user_info_object->status;
            $_SESSION['email'] = $user_info_object->email;
            $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_object->lang;
            $session->setLoginMethod($auth_ids[$auth]);
        }
    } else {
        $warning .= "<br>$langInvalidAuth<br>";
    }
    return $auth_allow;
}

/**
 * @brief Authenticate user via Shibboleth, CAS or OAuth 2.0
 * @param $type is 'shibboleth', 'cas' or 'oauth2'
 */
function shib_cas_login($type) {
    global $surname, $givenname, $email, $status, $language, $session,
        $is_admin, $is_power_user, $is_usermanage_user,
        $is_departmentmanage_user, $langUserAltAuth,
        $langAccountInactive1, $langAccountInactive2;


    $_SESSION['canChangePassword'] = false;
    $autoregister = get_config('alt_auth_stud_reg') == 2;
    $verified_mail = EMAIL_UNVERIFIED;

    if ($type == 'shibboleth') {
        $uname = $_SESSION['shib_uname'];
        $_SESSION['auth_user_info'] = $shib = get_shibboleth_user_info();
        if (isset($_SESSION['shib_auth_test'])) {
            $_SESSION['shib_auth_test'] = true;
            redirect_to_home_page('modules/admin/auth_test.php?auth=7');
        }
        $givenname = $shib['givenname'];
        $surname = $shib['surname'];
        $email = $shib['email'];
        $am = $shib['studentid'];
    } elseif ($type == 'cas') {
        $uname = $surname = $givenname = '';
        $uname = $_SESSION['cas_uname'];
        $surname = $_SESSION['cas_surname'];
        $givenname = $_SESSION['cas_givenname'];
        $email = isset($_SESSION['cas_email']) ? $_SESSION['cas_email'] : '';
        $am = isset($_SESSION['cas_userstudentid']) ? $_SESSION['cas_userstudentid'] : '';
    } elseif ($type == 'oauth2') {
        $uname = $_SESSION['auth_id'] ?? '';
        $surname = $_SESSION['auth_surname'] ?? '';
        $givenname = $_SESSION['auth_givenname'] ?? '';
        $email = $_SESSION['auth_email'] ?? '';
        $am = $_SESSION['auth_studentid'] ?? '';
    }
    if ($email) {
        // Email is considered verified if it came from CAS or Shibboleth
        $verified_mail = EMAIL_VERIFIED;
    }

    // Attributes passed to login_hook()
    $attributes = array();
    if (isset($_SESSION['cas_attributes'])) {
        foreach ($_SESSION['cas_attributes'] as $name => $value) {
            $attributes[strtolower($name)] = $value;
        }
        unset($_SESSION['cas_attributes']);
    } elseif (isset($_SESSION['shib_attributes'])) {
        foreach ($_SESSION['shib_attributes'] as $name => $value) {
            $attributes[strtolower($name)] = $value;
        }
        unset($_SESSION['shib_attributes']);
    } elseif (isset($_SESSION['auth_attributes'])) {
        foreach ($_SESSION['auth_attributes'] as $name => $value) {
            $attributes[strtolower($name)] = $value;
        }
        unset($_SESSION['auth_attributes']);
    }

    // user is authenticated, now let's see if he is registered also in db
    if (get_config('case_insensitive_usernames')) {
        $sqlLogin = "COLLATE utf8mb4_general_ci = ?s";
    } else {
        $sqlLogin = "COLLATE utf8mb4_bin = ?s";
    }
    $info = Database::get()->querySingle("SELECT id, surname, username, password, givenname,
                            status, email, lang, verified_mail, am
                        FROM user WHERE username $sqlLogin", $uname);

    if ($info) {
        if (!is_active_account($info->id, false)) { // check if user is active
            unset_shib_cas_session();
            $message = "$langAccountInactive1 <a href='modules/auth/contactadmin.php?userid=$info->id&amp;h=" .
                            token_generate("userid=$info->id") . "'>$langAccountInactive2</a>";
            Session::flash('message', $message);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page();
        }

        // if user found
        if ($info->password != $type) {
            // has different auth method - redirect to home page
            unset_shib_cas_session();
            $message = $langUserAltAuth;
            Session::flash('message', $langUserAltAuth);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page();
        } else {
            // don't force email address from CAS/Shibboleth.
            // user might prefer a different one
            if (!empty($info->email)) {
                $email = $info->email;
                $verified_mail = $info->verified_mail;
            }

            $userObj = new User();

            $options = login_hook([
                'user_id' => $info->id,
                'attributes' => $attributes,
                'status' => $info->status,
                'departments' => $userObj->getDepartmentIds($info->id),
                'am' => $am]);

            if ($type == 'cas') {
                $cas_settings = @unserialize(get_auth_settings(7)['auth_settings']);
                if ($cas_settings['cas_gunet'] ?? false) {
                    $options = gunet_idp_hook($options);
                }
            }

            if (!$options['accept']) {
                deny_access();
            }

            $status = $options['status'];
            $_SESSION['auth_user_info']['studentid'] = $am = $options['am'];

            if (!$surname and $info->surname !== '') {
                $_SESSION['auth_user_info']['surname'] = $_SESSION['cas_surname'] = $surname = $info->surname;
            }
            if (!$givenname and $info->givenname !== '') {
                $_SESSION['auth_user_info']['givenname'] = $_SESSION['cas_givenname'] = $givenname = $info->givenname;
            }

            // update user information
            Database::get()->query("UPDATE user SET surname = ?s, givenname = ?s, email = ?s,
                                           status = ?d, verified_mail = ?d WHERE id = ?d",
                    $surname, $givenname, $email, $status, $verified_mail, $info->id);
            if (!empty($am) and $info->am != $am) {
                Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d',
                    $am, $info->id);
            }

            $userObj->refresh($info->id, $options['departments']);
            user_hook($info->id);

            // check for admin privileges
            $admin_rights = get_admin_rights($info->id);
            if ($admin_rights == ADMIN_USER) {
                $is_active = 1;   // admin user is always active
                $_SESSION['is_admin'] = 1;
                $is_admin = 1;
            } elseif ($admin_rights == POWER_USER) {
                $_SESSION['is_power_user'] = 1;
                $is_power_user = 1;
            } elseif ($admin_rights == USERMANAGE_USER) {
                $_SESSION['is_usermanage_user'] = 1;
                $is_usermanage_user = 1;
            } elseif ($admin_rights == DEPARTMENTMANAGE_USER) {
                $_SESSION['is_departmentmanage_user'] = 1;
                $is_departmentmanage_user = 1;
            }
            $_SESSION['uid'] = $info->id;
            $language = $_SESSION['langswitch'] = $info->lang;
        }
    } elseif ($autoregister and !(get_config('am_required') and empty($am))) {
        // if user not found and autoregister enabled, create user
        if (!$verified_mail) {
            // redirect user to mail_verify_change.php
            $_SESSION['mail_verification_required'] = 1;
        }

        $options = login_hook(array(
            'user_id' => null,
            'attributes' => $attributes,
            'am' => $am));

        if ($type == 'cas') {
            $cas_settings = unserialize(get_auth_settings(7)['auth_settings']);
            if ($cas_settings['cas_gunet'] ?? false) {
                $options = gunet_idp_hook($options);
            }
        }

        if (!$options['accept']) {
            deny_access();
        }

        $status = $options['status'];
        $_SESSION['uid'] = Database::get()->query("INSERT INTO user
                    SET surname = ?s, givenname = ?s, password = ?s,
                        username = ?s, email = ?s, status = ?d, lang = ?s,
                        am = ?s, verified_mail = ?d,
                        registered_at = " . DBHelper::timeAfter() . ",
                        expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                        whitelist = ''",
                $surname, $givenname, $type, $uname, $email, $status,
                $language, $options['am'], $verified_mail)->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $_SESSION['uid']);
        $_SESSION['auth_user_info']['studentid'] = $options['am'];
        $userObj = new User();
        $userObj->refresh($_SESSION['uid'], $options['departments']);
        user_hook($_SESSION['uid']);
    } else {
        // user not registered, automatic registration disabled
        // redirect to registration screen
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        redirect_to_home_page('modules/auth/registration.php');
        exit;
    }

    $_SESSION['uname'] = $uname;
    $_SESSION['surname'] = $surname;
    $_SESSION['givenname'] = $givenname;
    $_SESSION['email'] = $email;
    $_SESSION['status'] = $status;

    unset_shib_cas_session();

    Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                    VALUES (?d, ?s, " . DBHelper::timeAfter() . ", 'LOGIN')",
                    $_SESSION['uid'], Log::get_client_ip());
    $session->setLoginTimestamp();
    $session->setLoginMethod($type);
    //triggerGame($_SESSION['uid'], $is_admin);
    if (get_config('email_verification_required') and
            get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
        $_SESSION['mail_verification_required'] = 1;
        // init.php is already loaded so redirect from here
        redirect_to_home_page('modules/auth/mail_verify_change.php');
    } else {
        if (isset($_GET['next'])) {
            redirect_to_home_page($_GET['next']);
        } else {
            redirect_to_home_page('main/portfolio.php');
        }
    }
}

/**
 * Check passwords entered in password change form for validity
 *
 * @param string $pass1 - First password field
 * @param string $pass2 - Second password field
 * @return array - Array of error messages, empty if no errors encountered
 */
function acceptable_password($pass1, $pass2) {
    global $ldapempty, $langPassTwo, $langPassShort;

    $errors = array();
    if ($pass1 === '' or $pass2 === '') {
        $errors[] = $ldapempty;
    }
    if ($pass1 !== $pass2) {
        $errors[] = $langPassTwo;
    }
    $min_len = intval(get_config('min_password_len'));
    if (mb_strlen($pass1, 'UTF-8') < $min_len) {
        $errors[] = sprintf($langPassShort, $min_len);
    }
    return $errors;
}

/**
 * @brief increase number of login failures
 * @return type
 */
function increaseLoginFailure() {
    if (!get_config('login_fail_check'))
        return;

    $ip = Log::get_client_ip();
    $r = Database::get()->querySingle('SELECT 1 FROM login_failure WHERE ip = ?s', $ip);

    if ($r) {
        Database::get()->query('UPDATE login_failure SET
                count = count + 1, last_fail = CURRENT_TIMESTAMP
            WHERE ip = ?s', $ip);
    } else {
        Database::get()->query('INSERT INTO login_failure (id, ip, count, last_fail)
            VALUES (NULL, ?s, 1, CURRENT_TIMESTAMP)', $ip);
    }
}

/**
 * @brief reset number of login failures
 * @return type
 */
function resetLoginFailure() {
    if (!get_config('login_fail_check'))
        return;

    Database::get()->query("DELETE FROM login_failure WHERE ip = ?s AND
        DATE_SUB(CURRENT_TIMESTAMP,
            INTERVAL " . intval(get_config('login_fail_forgive_interval')) . " HOUR) >= last_fail",
        Log::get_client_ip()); // de-penalize only after 24 hours
}

function external_DB_Check_Pass($test_password, $hash, $encryption) {
    switch ($encryption) {
        case 'none':
            return ($test_password == $hash);
        case 'md5':
            return (md5($test_password) == $hash);
        case 'ehasher':
            return password_verify($test_password, $hash);
        case 'phpass':
            require_once 'include/lib/PasswordHash.php';
            $hasher = new Hautelook\Phpass\PasswordHash(8, false);
            return $hasher->CheckPassword($test_password, $hash);
        default:
            /* Maybe append an error message to tool_content, telling not supported encryption */
    }
}

function get_shibboleth_user_info() {
    $info = array(
        'givenname' => '',
        'surname' => '',
        'email' => '',
        'studentid' => '',
        'attributes' => array());
    if (isset($_SESSION['shib_email'])) {
        $info['email'] = $_SESSION['shib_email'];
        unset($_SESSION['shib_email']);
    }
    if (isset($_SESSION['shib_surname'])) {
        $info['surname'] = $_SESSION['shib_surname'];
        unset($_SESSION['shib_surname']);
    }
    if (isset($_SESSION['shib_givenname'])) {
        $info['givenname'] = $_SESSION['shib_givenname'];
        unset($_SESSION['shib_givenname']);
    } elseif (isset($_SESSION['shib_cn'])) {
        if (!empty($info['surname'])) {
            $info['givenname'] = str_replace($info['surname'], '', $_SESSION['shib_cn']);
        } else {
            $shibseparator = ' ';
            if ($r = Database::get()->querySingle("SELECT auth_settings FROM auth WHERE auth_id = 6")) {
                $shibsettings = $r->auth_settings;
                if ($shibsettings !== 'shibboleth' and $shibsettings !== '') {
                    $shibseparator = $shibsettings;
                }
            }
            $parts = explode($shibseparator, $_SESSION['shib_cn']);
            $info['surname'] = array_pop($parts);
            $info['givenname'] = implode(' ', $parts);
        }
    }
    unset($_SESSION['shib_cn']);
    if (isset($_SESSION['shib_studentid'])) {
        $info['studentid'] = $_SESSION['shib_studentid'];
        unset($_SESSION['shib_studentid']);
    }
    $info['givenname'] = trim($info['givenname']);
    $info['surname'] = trim($info['surname']);
    return $info;
}

function update_shibboleth_endpoint($settings) {
    global $webDir;

    $path = $webDir . '/secure';
    if (!file_exists($path)) {
        if (!make_dir($path)) {
            Session::flash('message',"Error: mkdir($path)");
            Session::flash('alert-class', 'alert-danger');
            return false;
        }
    }

    $indexfile = $path . '/index.php';
    $filecontents = '<?php
session_start();
';
    foreach (array('shib_email', 'shib_uname', 'shib_cn', 'shib_surname',
                   'shib_givenname', 'shib_studentid') as $var) {
        if (isset($settings[$var]) and $settings[$var]) {
            $filecontents .= '$_SESSION["' . $var . '"] = @' .
                $settings[$var] . ";\n";
        }
    }
    $filecontents .= '
$_SESSION["shib_attributes"] = $_SERVER;
if (isset($_GET["reg"])) {
    header("Location: ../modules/auth/altsearch.php" . (isset($_GET["p"]) && $_GET["p"]? "?p=1": ""));
} else {
    header("Location: ../index.php");
}
';
    if ($f = fopen($indexfile, 'w')) {
        if (!fwrite($f, $filecontents)) {
                Session::flash('message',"Error: write($indexfile)<pre>" .
                q($filecontents) . '</pre>');
            Session::flash('alert-class', 'alert-danger');
            return false;
        }
        fclose($f);
    } else {
            Session::flash('message',"Error: open($indexfile)<pre>" .
            q($filecontents) . '</pre>');
        Session::flash('alert-class', 'alert-danger');
        return false;
    }

    // Remove obsolete secure/index_reg.php
    $indexregfile = $path . '/index_reg.php';
    if (file_exists($indexregfile) and !unlink($indexregfile)) {
        Session::flash('message',"Warning: unable to delete obsolete $indexregfile");
        Session::flash('alert-class', 'alert-warning');
    }
    return true;
}

function get_shibboleth_vars($file) {
    $shib_vars = array(
        'uname' => '',
        'email' => '',
        'cn' => '',
        'surname' => '',
        'givenname' => '',
        'studentid' => '');

    if (is_readable($file)) {
        $shib_index = file_get_contents($file);
        while (preg_match('/\[[^]]*shib_(\w+)[^=]+=\s*@?([^;]+)\s*;/', $shib_index, $matches)) {
            $shib_vars[$matches[1]] = $matches[2];
            $shib_index = substr($shib_index, strlen($matches[0]));
        }
    }
    if (isset($shib_vars['shib_nom']) and !isset($shib_vars['shib_cn'])) {
        $shib_vars['shib_cn'] = $shib_vars['shib_nom'];
    }
    return $shib_vars;
}

function deny_access() {
    global $langRegistrationDenied;

    if (!$options['accept']) {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        Session::flash('message',$langRegistrationDenied);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page();
    }
}

function unset_shib_cas_session () {
    foreach (['cas_uname', 'cas_email', 'cas_surname', 'cas_givenname', 'cas_userstudentid',
        'shib_uname', 'shib_email', 'shib_surname', 'shib_givenname', 'shib_cn', 'shib_studentid',
        'auth_id', 'auth_email', 'auth_givenname', 'auth_surname', 'auth_studentid'] as $var) {
            unset($_SESSION[$var]);
    }
}


/**
 * @brief Hook function to modify user options on login if GUnet Identity Schema is enabled
 * @return array $options
 */
function gunet_idp_hook($options) {
    $attributes = $options['attributes'];
    if (isset($attributes['schacpersonaluniquecode'])) {
        $spuc = is_array($attributes['schacpersonaluniquecode'])? $attributes['schacpersonaluniquecode']: [$attributes['schacpersonaluniquecode']];
        $new_am = canonicalize_whitespace(implode(' ', array_map(function ($item) {
            if (preg_match('/^urn:mace:terena.org:schac:personalUniqueCode/i', $item)) {
                $parts = explode(':', $item);
                return $parts[8];
            } else {
                return '';
            }
        }, $spuc)));
        if ($new_am != '') {
            $options['am'] = $new_am;
        }
    }

    if (isset($attributes['schacpersonalposition'])) {
        $spp = is_array($attributes['schacpersonalposition'])? $attributes['schacpersonalposition']: [$attributes['schacpersonalposition']];
        $assoc = Database::get()->queryArray('SELECT * FROM minedu_department_association');
        $minedu_id_map = [];
        foreach ($assoc as $item) {
            $minedu_id_map[$item->minedu_id] = $item->department_id;
        }
        $new_departments = [];
        foreach ($spp as $item) {
            $parts = explode(':', $item);
            if (isset($parts[9])) {
                $minedu_id = $parts[9];
                if (isset($minedu_id_map[$minedu_id])) {
                    $new_departments[] = $minedu_id_map[$minedu_id];
                }
            }
        }
        if ($new_departments) {
            if ($status == USER_STUDENT) {
                $options['departments'] = $new_departments;
            } else {
                $options['departments'] = array_unique(array_merge($options['departments'], $new_departments));
            }
        }
    }

    if (!$options['departments']) {
        $default = Database::get()->querySingle('SELECT * FROM minedu_department_association WHERE minedu_id = 0');
        if ($default) {
            $options['departments'] = [$default->department_id];
        }
    }

    return $options;
}

//function triggerGame($uid, $is_admin) {
//    if (!$is_admin) {
//        require_once 'modules/progress/CourseParticipationEvent.php';
//        $eventData = new stdClass();
//        $eventData->uid = $uid;
//        $eventData->activityType = CourseParticipationEvent::ACTIVITY;
//        $eventData->module = MODULE_ID_USAGE;
//        CourseParticipationEvent::trigger(CourseParticipationEvent::LOGGEDIN, $eventData);
//    }
//}
