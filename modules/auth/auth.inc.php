<?php

/* ========================================================================
 * Open eClass 3.2
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
use Hautelook\Phpass\PasswordHash;

require_once 'include/log.class.php';
require_once 'include/lib/user.class.php';
// pop3 class
require_once 'modules/auth/methods/pop3.php';

$auth_ids = array(1 => 'eclass',
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
);

$authFullName = array(
    8 => 'Facebook',
    9 => 'Twitter',
    10 => 'Google',
    11 => 'Microsoft Live',
    12 => 'Yahoo!',
    13 => 'LinkedIn',
);

$extAuthMethods = array('cas', 'shibboleth');
$hybridAuthMethods = array('facebook', 'twitter', 'google', 'live', 'yahoo', 'linkedin');


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
    global $langViaeClass, $langViaPop, $langViaImap, $langViaLdap, $langViaDB, $langViaShibboleth, $langViaCAS, $langViaFacebook, $langViaTwitter, $langViaGoogle, $langViaLive, $langViaYahoo, $langViaLinkedIn, $langNbUsers, $langAuthChangeUser;

    if(!empty($auth)) {
        switch($auth)
        {
            case '1': $m = $langViaeClass;
            break;
            case '2': $m = $langViaPop;
            break;
            case '3': $m = $langViaImap;
            break;
            case '4': $m = $langViaLdap;
            break;
            case '5': $m = $langViaDB;
            break;
            case '6': $m = $langViaShibboleth;
            break;
            case '7': $m = $langViaCAS;
            break;
            case '8': $m = $langViaFacebook;
            break;
            case '9': $m = $langViaTwitter;
            break;
            case '10': $m = $langViaGoogle;
            break;
            case '11': $m = $langViaLive;
            break;
            case '12': $m = $langViaYahoo;
            break;
            case '13': $m = $langViaLinkedIn;
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

    foreach (explode('|', $result->auth_settings) as $item) {
        if (preg_match('/(\w+)=(.*)/', $item, $matches)) {
            $settings[$matches[1]] = $matches[2];
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
    global $webDir;

    $testauth = false;
    switch ($auth) {
        case '1':
            $unamewhere = (get_config('case_insensitive_usernames')) ? "COLLATE utf8_general_ci = " : "COLLATE utf8_bin = ";
            $result = Database::get()->querySingle("SELECT password FROM user WHERE username $unamewhere ?s", $test_username);
            if ($result) {
                $hasher = new PasswordHash(8, false);
                if ($hasher->CheckPassword($test_password, $result->password)) {
                    $testauth = true;
                } else if (strlen($myrow->password) < 60 && md5($test_password) == $result->password) {
                    $testauth = true;
                    // password is in old md5 format, update transparently
                    $password_encrypted = $hasher->HashPassword($test_password);
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
                    if (empty($settings['ldap_login_attr2'])) {
                        $search_filter = "($settings[ldap_login_attr]=${test_username})";
                    } else {
                        $search_filter = "(|($settings[ldap_login_attr]=${test_username})
                                            ($settings[ldap_login_attr2]=${test_username}))";
                    }

                    $userinforequest = ldap_search($ldap, $settings['ldap_base'], $search_filter);
                    if ($entry_id = ldap_first_entry($ldap, $userinforequest)) {
                        $user_dn = ldap_get_dn($ldap, $entry_id);
                        if (@ldap_bind($ldap, $user_dn, $test_password)) {
                            $testauth = true;
                            $userinfo = ldap_get_entries($ldap, $userinforequest);
                            if ($userinfo['count'] == 1) {
                                $surname = get_ldap_attribute($userinfo, 'sn');
                                $givenname = get_ldap_attribute($userinfo, 'givenname');
                                if (empty($givennname)) {
                                    $cn = get_ldap_attribute($userinfo, 'cn');
                                    $givenname = trim(str_replace($surname, '', $cn));
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
            $link = new Database($settings['dbhost'], $settings['dbname'], $settings['dbuser'], $settings['dbpass']);
            if ($link) {
                if ($link) {
                    $res = $link->querySingle("SELECT `$settings[dbfieldpass]`
                                                FROM `$settings[dbtable]`
                                                WHERE `$settings[dbfielduser]` = ?s", $test_username);
                    if ($res) {
                        $testauth = external_DB_Check_Pass($test_password, $res->$settings['dbfieldpass'], $settings['dbpassencr']);
                    }
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

function check_activity($userid) {
    $result = Database::get()->querySingle("SELECT expires_at FROM user WHERE id = ?d", intval($userid));
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
    global $langConnectWith, $langNotSSL;

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
        phpCAS::client(SAML_VERSION_1_1, $cas_host, $cas_port, $cas_context, FALSE);

        // Set the CA certificate that is the issuer of the cert on the CAS server
        if (isset($cas_cachain) && !empty($cas_cachain) && is_readable($cas_cachain))
            phpCAS::setCasServerCACert($cas_cachain);
        else {
            phpCAS::setNoCasServerValidation();
            $ret['error'] = "$langNotSSL";
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

/* * **************************************************************
  Process login form submission
 * ************************************************************** */

function process_login() {
    global $warning, $surname, $givenname, $email, $status, $is_admin,
        $language, $session, $langInvalidId, $langAccountInactive1,
        $langAccountInactive2, $langNoCookies, $langEnterPlatform, $urlServer,
        $langHere, $auth_ids, $inactive_uid, $langTooManyFails, $urlAppend;

    if (isset($_POST['uname'])) {
        $posted_uname = canonicalize_whitespace($_POST['uname']);
    } else {
        $posted_uname = '';
    }

    $pass = isset($_POST['pass']) ? trim($_POST['pass']): '';
    $auth = get_auth_active_methods();

    if (isset($_POST['submit'])) {
        unset($_SESSION['uid']);
        $auth_allow = 0;

        if (get_config('login_fail_check')) {
            $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
                                        AND COUNT > " . intval(get_config('login_fail_threshold')) . "
                                        AND DATE_SUB(CURRENT_TIMESTAMP, interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail");
        }
        if (get_config('login_fail_check') && $r) {
            $auth_allow = 8;
        } else {
            if (get_config('case_insensitive_usernames')) {
                $sqlLogin = "COLLATE utf8_general_ci = ?s";
            } else {
                $sqlLogin = "COLLATE utf8_bin = ?s";
            }
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname, password,
                                    username, status, email, lang, verified_mail, am
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
                        $tool_content .= "<br>$langInvalidAuth<br>";
                    }
                }
            }
            if (!$exists and !$auth_allow) {
                Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $posted_uname));
                $auth_allow = 4;
            }
        }

        $invalidIdMessage = sprintf($langInvalidId, $urlAppend . 'modules/auth/registration.php');
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
                        $warning .= "<div class='alert alert-warning'>$invalidIdMessage</div>";
                    }
                    break;
                case 3: $warning .= "<div class='alert alert-warning'>$langAccountInactive1 " .
                            "<a href='modules/auth/contactadmin.php?userid=$inactive_uid&amp;h=" .
                            token_generate("userid=$inactive_uid") . "'>$langAccountInactive2</a></div>";
                    break;
                case 4:
                    if (isset($_GET['login_page'])) {
                        Session::flash('login_error', $invalidIdMessage);
                        redirect_to_home_page('main/login_form.php');
                    } else {
                        $warning .= "<div class='alert alert-warning'>$invalidIdMessage</div>";
                        increaseLoginFailure();
                    }
                    break;
                case 5: $warning .= "<div class='alert alert-warning'>$langNoCookies</div>";
                    break;
                case 6: $warning .= "<div class='alert alert-warning'>$langEnterPlatform <a href='{$urlServer}secure/index.php'>$langHere</a></div>";
                    break;
                case 7: $warning .= "<div class='alert alert-warning'>$langEnterPlatform <a href='{$urlServer}modules/auth/cas.php'>$langHere</a></div>";
                    break;
                case 8: $warning .= "<div class='alert alert-warning'>$langTooManyFails</div>";
                    break;
                default:
                    break;
            }
        } else {
            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action) "
                    . "VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
            $session->setLoginTimestamp();
            if (get_config('email_verification_required') and
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

function hybridauth_login() {
    global $surname, $givenname, $email, $status, $is_admin, $language,
        $langInvalidId, $langAccountInactive1, $langAccountInactive2,
        $langNoCookies, $langEnterPlatform, $urlServer, $langHere, $auth_ids,
        $inactive_uid, $langTooManyFails, $warning;
    
    // include HubridAuth libraries
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();
    
    $_SESSION['canChangePassword'] = false;

    // check for errors and whatnot
    $warning = '';
    
    if (isset($_GET['error'])) {
        Session::Messages(q(trim(strip_tags($_GET['error']))));
    }

    // if user select a provider to login with then include hybridauth config
    // and main class, try to authenticate, finally redirect to profile
    if (isset($_GET['provider'])) {
        try {
            $hybridauth = new Hybrid_Auth($config);
            
            // set selected provider name
            $provider = @trim(strip_tags($_GET['provider']));
        
            // try to authenticate the selected $provider
            $adapter = $hybridauth->authenticate($provider);
            
            // grab the user profile
            $user_data = $adapter->getUserProfile();
            
            // user profile debug print
            // echo '<pre>'; print_r($user_data); echo '</pre>';
            
        } catch (Exception $e) {
            // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
            // let hybridauth forget all about the user so we can try to authenticate again.
        
            // Display the recived error,
            // to know more please refer to Exceptions handling section on the userguide
            switch($e->getCode()) {
                case 0: Session::Messages($GLOBALS['langProviderError1']); break;
                case 1: Session::Messages($GLOBALS['langProviderError2']); break;
                case 2: Session::Messages($GLOBALS['langProviderError3']); break;
                case 3: Session::Messages($GLOBALS['langProviderError4']); break;
                case 4: Session::Messages($GLOBALS['langProviderError5']); break;
                case 5: Session::Messages($GLOBALS['langProviderError6']); break;
                case 6: Session::Messages($GLOBALS['langProviderError7']); $adapter->logout(); break;
                case 7: Session::Messages($GLOBALS['langProviderError8']); $adapter->logout(); break;
            }
        
            // debug messages for hybridauth errors
            //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
            //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
        
            return false;
        }
    } //endif( isset( $_GET["provider"] ) && $_GET["provider"] )
    
    
    // from here on an alternative version of proccess_login() runs where
    // instead of a password, the provider uid is used and matched against
    // the corresponding field in the db table.
    
    $pass = $user_data->identifier; // password = provider user id
    // $is_eclass_unique = is_eclass_unique();
    
    unset($_SESSION['uid']);
    $auth_allow = 0;
    
    if (get_config('login_fail_check')) {
        $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
                                       AND COUNT > " . intval(get_config('login_fail_threshold')) . "
                                       AND DATE_SUB(CURRENT_TIMESTAMP, interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail");
    }
    if (get_config('login_fail_check') && $r) {
        $auth_allow = 8;
    } else {
        $auth_id = array_search(strtolower($provider), $auth_ids);
        $auth_methods = get_auth_active_methods();
        $myrow = Database::get()->querySingle("SELECT user.id, surname,
                    givenname, password, username, status, email, lang,
                    verified_mail, uid
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
                Session::Messages($langInvalidAuth, 'alert-danger');
                redirect_to_home_page();
            }
        }
        if (!$exists and !$auth_allow) {
            // Since HybridAuth was used and there is not user id matched in the db, send the user to the registration form.
            redirect_to_home_page('modules/auth/registration.php?provider=' . $provider);
        }
    }
    
    if (!isset($_SESSION['uid'])) {
        switch ($auth_allow) {
            case 1:
                session_regenerate_id();
                break;
            case 2:
                $warning .= "<p class='alert alert-warning'>$langInvalidId</p>";
                break;
            case 3:
                $warning .= "<p class='alert alert-warning'>$langAccountInactive1 " .
                    "<a href='modules/auth/contactadmin.php?userid=$inactive_uid&amp;h=" .
                    token_generate("userid=$inactive_uid") . "'>$langAccountInactive2</a></p>";
                break;
            case 4:
                $warning .= "<p class='alert alert-warning'>$langInvalidId</p>";
                increaseLoginFailure();
                break;
            case 5:
                $warning .= "<p class='alert alert-warning'>$langNoCookies</p>";
                break;
            case 6:
                $warning .= "<p class='alert alert-info'>$langEnterPlatform <a href='{$urlServer}secure/index.php'>$langHere</a></p>";
                break;
            case 7:
                $warning .= "<p class='alert alert-info'>$langEnterPlatform <a href='{$urlServer}modules/auth/cas.php'>$langHere</a></p>";
                break;
            case 8:
                $warning .= "<p class='alert alert-danger''>$langTooManyFails</p>";
                break;
        }
    } else {
        Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action) "
                . "VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
        if (get_config('email_verification_required') and
            get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
            $_SESSION['mail_verification_required'] = 1;
            $next = "modules/auth/mail_verify_change.php";
        } elseif (isset($_POST['next'])) {
            $next = $_POST['next'];
        } else {
            $next = '';
        }
        resetLoginFailure();
        redirect_to_home_page($next);
    }
}

/* * **************************************************************
  Authenticate user via eclass
 * ************************************************************** */

function login($user_info_object, $posted_uname, $pass, $provider=null, $user_data=null) {
    global $session;

    $_SESSION['canChangePassword'] = false;
    $pass_match = false;
    $hasher = new PasswordHash(8, false);

    if (is_null($provider)) {
        if (check_username_sensitivity($posted_uname, $user_info_object->username)) {
            if ($hasher->CheckPassword($pass, $user_info_object->password)) {
                $pass_match = true;
            } elseif (strlen($user_info_object->password) < 60 and md5($pass) == $user_info_object->password) {
                $pass_match = true;
                // password is in old md5 format, update transparently
                $password_encrypted = $hasher->HashPassword($pass);
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

    if ($pass_match) {
        // check if account is active
        if ($user_info_object->status == USER_GUEST) {
            $is_active = get_config('course_guest') != 'off';
        } else {
            $is_active = check_activity($user_info_object->id);

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
            $_SESSION['canChangePassword'] = true;
            $_SESSION['uid'] = $user_info_object->id;
            $_SESSION['uname'] = $user_info_object->username;
            $_SESSION['surname'] = $user_info_object->surname;
            $_SESSION['givenname'] = $user_info_object->givenname;
            $_SESSION['status'] = $user_info_object->status;
            $_SESSION['email'] = $user_info_object->email;
            $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_object->lang;
            $auth_allow = 1;
            $session->setLoginTimestamp();
        } else {
            $auth_allow = 3;
            $GLOBALS['inactive_uid'] = $user_info_object->id;
        }
    } else {
        $auth_allow = 4; // means wrong password
        Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $posted_uname,
                                                   'pass' => $pass));
    }

    return $auth_allow;
}

/* * **************************************************************
  Authenticate user via alternate defined methods
 * ************************************************************** */

function alt_login($user_info_object, $uname, $pass) {
    global $warning, $auth_ids;

    $_SESSION['canChangePassword'] = false;
    $auth = array_search($user_info_object->password, $auth_ids);
    $auth_method_settings = get_auth_settings($auth);
    $auth_allow = 1;

    // a CAS user might enter a username/password in the form, instead of doing CAS login
    // check auth according to the defined alternative authentication method of CAS
    if ($auth == 7) {
        $cas = explode('|', $auth_method_settings['auth_settings']);
        $cas_altauth = intval(str_replace('cas_altauth=', '', $cas[7]));
        // check if alt auth is valid and configured
        if (($cas_altauth > 0) && check_auth_configured($cas_altauth)) {
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
    if ($user_info_object->password == $auth_method_settings['auth_name']) {
        $is_valid = auth_user_login($auth, $uname, $pass, $auth_method_settings);
        if ($is_valid) {
            $is_active = check_activity($user_info_object->id);
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
            if (!empty($is_active)) {
                $auth_allow = 1;
            } else {
                $auth_allow = 3;
                $user = $user_info_object->id;
            }
        } else {
            $auth_allow = 2;
            // log invalid logins
            Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $uname,
                                                       'pass' => $pass));
        }
        if ($auth_allow == 1) {
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
        }
    } else {
        $warning .= "<br>$langInvalidAuth<br>";
    }
    return $auth_allow;
}

/* * **************************************************************
  Authenticate user via Shibboleth or CAS
  $type is 'shibboleth' or 'cas'
 * ************************************************************** */

function shib_cas_login($type) {
    global $surname, $givenname, $email, $status, $language, $session,
        $urlServer, $is_admin, $is_power_user, $is_usermanage_user,
        $is_departmentmanage_user, $langUserAltAuth, $langRegistrationDenied;

    $_SESSION['canChangePassword'] = false;
    $alt_auth_stud_reg = get_config('alt_auth_stud_reg');

    if ($alt_auth_stud_reg == 2) {
        $autoregister = TRUE;
    } else {
        $autoregister = FALSE;
    }

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
    }

    // user is authenticated, now let's see if he is registered also in db
    if (get_config('case_insensitive_usernames')) {
        $sqlLogin = "COLLATE utf8_general_ci = ?s";
    } else {
        $sqlLogin = "COLLATE utf8_bin = ?s";
    }
    $info = Database::get()->querySingle("SELECT id, surname, username, password, givenname,
                            status, email, lang, verified_mail, am
						FROM user WHERE username $sqlLogin", $uname);

    if ($info) {
        // if user found
        if ($info->password != $type) {
            // has different auth method - redirect to home page
            unset($_SESSION['cas_uname']);
            unset($_SESSION['cas_email']);
            unset($_SESSION['cas_surname']);
            unset($_SESSION['cas_givenname']);
            unset($_SESSION['cas_userstudentid']);
            Session::Messages($langUserAltAuth, 'alert-danger');
            redirect_to_home_page();
        } else {
            // don't force email address from CAS/Shibboleth.
            // user might prefer a different one
            if (!empty($info->email)) {
                $email = $info->email;
            }

            $userObj = new User();

            $options = login_hook(array(
                'user_id' => $info->id,
                'attributes' => $attributes,
                'status' => $info->status,
                'departments' => $userObj->getDepartmentIds($info->id),
                'am' => $am));

            if (!$options['accept']) {
                foreach (array_keys($_SESSION) as $key) {
                    unset($_SESSION[$key]);
                }
                Session::Messages($langRegistrationDenied, 'alert-warning');
                redirect_to_home_page();
            }

            $status = $options['status'];

            // update user information
            Database::get()->query("UPDATE user SET surname = ?s, givenname = ?s, email = ?s,
                                           status = ?d WHERE id = ?d",
                                        $surname, $givenname, $email, $status, $info->id);
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
	    $verified_mail = EMAIL_UNVERIFIED;
    	if (isset($_SESSION['cas_email'])) {
    	    $verified_mail = EMAIL_VERIFIED;
    	} else { // redirect user to mail_verify_change.php
	    	$_SESSION['mail_verification_required'] = 1;
        }

        $options = login_hook(array(
            'user_id' => null,
            'attributes' => $attributes,
            'am' => $am));

        if (!$options['accept']) {
            foreach (array_keys($_SESSION) as $key) {
                unset($_SESSION[$key]);
            }
            Session::Messages($langRegistrationDenied, 'alert-warning');
            redirect_to_home_page();
        }
        $status = $options['status'];
        $_SESSION['uid'] = Database::get()->query("INSERT INTO user
                    SET surname = ?s, givenname = ?s, password = ?s,
                        username = ?s, email = ?s, status = ?d, lang = ?s,
                        am = ?s, verified_mail = ?d,
                        registered_at = " . DBHelper::timeAfter() . ",
                        expires_at = " . DBHelper::timeAfter(get_config('account_duration')) . ",
                        whitelist = ''",
                $surname, $givenname, $type, $uname, $email, $status,
                $language, $options['am'], $verified_mail)->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $_SESSION['uid']);
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
    //$_SESSION['is_admin'] = $is_admin;
    $_SESSION['shib_user'] = 1; // now we are shibboleth user

    Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
					VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', " . DBHelper::timeAfter() . ", 'LOGIN')");
    $session->setLoginTimestamp();
    if (get_config('email_verification_required') and
            get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
        $_SESSION['mail_verification_required'] = 1;
        // init.php is already loaded so redirect from here
        redirect_to_home_page('modules/auth/mail_verify_change.php');
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

    $ip = $_SERVER['REMOTE_ADDR'];
    $r = Database::get()->querySingle("SELECT 1 FROM login_failure WHERE ip = '" . $ip . "'");

    if ($r) {
        Database::get()->query("UPDATE login_failure SET count = count + 1, last_fail = CURRENT_TIMESTAMP WHERE ip = '" . $ip . "'");
    } else {
        Database::get()->query("INSERT INTO login_failure (id, ip, count, last_fail) VALUES (NULL, '" . $ip . "', 1, CURRENT_TIMESTAMP)");
    }
}

/**
 * @brief reset number of login failures
 * @return type
 */
function resetLoginFailure() {
    if (!get_config('login_fail_check'))
        return;

    Database::get()->query("DELETE FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND DATE_SUB(CURRENT_TIMESTAMP, INTERVAL " . intval(get_config('login_fail_forgive_interval')) . " HOUR) >= last_fail"); // de-penalize only after 24 hours
}

function external_DB_Check_Pass($test_password, $hash, $encryption) {
    switch ($encryption) {
        case 'none':
            return ($test_password == $hash);
            break;
        case 'md5':
            return (md5($test_password) == $hash);
        case 'ehasher':
            require_once 'include/phpass/PasswordHash.php';
            $hasher = new PasswordHash(8, false);
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
            Session::Messages("Error: mkdir($path)", 'alert-danger');
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
            Session::Messages("Error: write($indexfile)<pre>" .
                q($filecontents) . '</pre>', 'alert-danger');
            return false;
        }
        fclose($f);
    } else {
        Session::Messages("Error: open($indexfile)<pre>" .
            q($filecontents) . '</pre>', 'alert-danger');
        return false;
    }

    // Remove obsolete secure/index_reg.php
    $indexregfile = $path . '/index_reg.php';
    if (file_exists($indexregfile) and !unlink($indexregfile)) {
        Session::Messages("Warning: unable to delete obsolete $indexregfile", 'alert-warning');
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
