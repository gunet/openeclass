<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
  @last update: 31-05-2006 by Stratos Karatzidis
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: Functions Library for authentication purposes

  This library includes all the functions for authentication
  and their settings.

  ==============================================================================
 */

require_once 'include/log.php';
require_once 'include/lib/user.class.php';
// pop3 class
require_once 'modules/auth/methods/pop3.php';
require_once 'include/phpass/PasswordHash.php';

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
    13 => 'linked'
);


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


/* * **************************************************************
  count users for each authentication method
  return count of users for auth method
 * ************************************************************** */

function count_auth_users($auth) {
    global $auth_ids;
    $auth = intval($auth);

    if ($auth === 1) {
        for ($i = 2; $i <= count($auth_ids); $i++) {
            $extra = " AND password != '{$auth_ids[$i]}'";
        }
        $result = Database::get()->querySingle("SELECT COUNT(*) AS total FROM user WHERE password != '{$auth_ids[1]}' $extra");
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
    $settings['auth_name'] = $result->auth_id;

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
                $GLOBALS['auth_errors'] = 'Error connecting to LDAP host';
                return false;
            } else {
                // LDAP connection established - now search for user dn
                @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
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
            $path = $webDir . '/secure/';
            if (!file_exists($path)) {
                if (!mkdir($path, 0700)) {
                    $testauth = false;
                }
            } else {
                $indexfile = $path . 'index.php';
                $index_regfile = $path . 'index_reg.php';

                // creation of secure/index.php file
                $filecontents = '<?php
session_start();
$_SESSION[\'shib_email\'] = ' . $settings['shibemail'] . ';
$_SESSION[\'shib_uname\'] = ' . $settings['shibuname'] . ';
$_SESSION[\'shib_surname\'] = ' . $settings['shibcn'] . ';
header("Location: ../index.php");
';
                if ($f = fopen($indexfile, 'w')) {
                    if (fwrite($f, $filecontents)) {
                        $testauth = true;
                    }
                    fclose($f);
                }

                // creation of secure/index_reg.php
                // used in professor request registration process via shibboleth
                $f = fopen($index_regfile, "w");
                $filecontents = '<?php
session_start();
$_SESSION[\'shib_email\'] = ' . $settings['shibemail'] . ';
$_SESSION[\'shib_uname\'] = ' . $settings['shibuname'] . ';
$_SESSION[\'shib_surname\'] = ' . $settings['shibcn'] . ';
$_SESSION[\'shib_status\'] = $_SERVER[\'unscoped-affiliation\'];
$_SESSION[\'shib_auth\'] = true;
header("Location: ../modules/auth/altsearch.php" . (isset($_GET["p"]) && $_GET["p"]? "?p=1": ""));
';
                if (fwrite($f, $filecontents)) {
                    $testauth = true;
                }
                fclose($f);
            }
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
        $_SESSION['auth_user_info'][$name] = $ret[$attrname] = '';
        if (isset($settings[$attrname]) and $settings[$attrname]) {
            $setting = strtolower($settings[$attrname]);
            if (isset($attrs[$setting])) {
                $_SESSION['auth_user_info'][$name] = $ret[$attrname] = $attrs[$setting];
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
            $sqlLogin = "SELECT id, surname, givenname, password, username, status, email, lang, verified_mail
                                FROM user WHERE username ";
            if (get_config('case_insensitive_usernames')) {
                $sqlLogin = "COLLATE utf8_general_ci = ?s";
            } else {
                $sqlLogin = "COLLATE utf8_bin = ?s";
            }
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname, password, username, status, email, lang, verified_mail
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
                case 7: $warning .= "<div class='alert alert-warning'>$langEnterPlatform <a href='{$urlServer}secure/cas.php'>$langHere</a></div>";
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
    //this is needed so as to include the HybridAuth error codes
    global $language, $language_codes, $siteName, $Institution, $InstitutionUrl;
    if (isset($language)) {
        // include_messages
        include "lang/$language/common.inc.php";
        $extra_messages = "config/{$language_codes[$language]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "lang/$language/messages.inc.php";
        if ($extra_messages) {
            include $extra_messages;
        }
    }
    //end HybridAuth messages inclusion
    
    
    global $warning;
    
    // include HubridAuth libraries
    require_once 'modules/auth/methods/hybridauth/config.php';
    require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
    $config = get_hybridauth_config();
    
    //print_r($config);
    
    // check for erros and whatnot
    $warning = "";
    
    if( isset( $_GET["error"] ) ){
        $warning = "<p class='alert1'>" . trim( strip_tags(  $_GET["error"] ) ) . "</p>";
    }
    
    // if user select a provider to login with
    // then inlcude hybridauth config and main class
    // then try to authenticate te current user
    // finally redirect him to his profile page
    if( isset($_GET["provider"]) && $_GET["provider"]) {
        try {
            // create an instance for Hybridauth with the configuration file path as parameter
            $hybridauth = new Hybrid_Auth($config);
            
            // set selected provider name
            $provider = @ trim( strip_tags($_GET["provider"]));
        
            // try to authenticate the selected $provider
            $adapter = $hybridauth->authenticate( $provider );
            
            // grab the user profile
            $user_data = $adapter->getUserProfile();
            
            //user profile debug print
            //echo $user_data->displayName;
            //echo $user_data->email;
            //echo $user_data->photoURL;
            //echo $user_data->identifier;
            
        } catch(Exception $e) {
            // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
            // let hybridauth forget all about the user so we can try to authenticate again.
        
            // Display the recived error,
            // to know more please refer to Exceptions handling section on the userguide
            switch($e->getCode()) {
                case 0 : $warning = "<p class='alert1'>$langProviderError1</p>"; break;
                case 1 : $warning = "<p class='alert1'>$langProviderError2</p>"; break;
                case 2 : $warning = "<p class='alert1'>$langProviderError3</p>"; break;
                case 3 : $warning = "<p class='alert1'>$langProviderError4</p>"; break;
                case 4 : $warning = "<p class='alert1'>$langProviderError5</p>"; break;
                case 5 : $warning = "<p class='alert1'>$langProviderError6</p>"; break;
                case 6 : $warning = "<p class='alert1'>$langProviderError7</p>"; $adapter->logout(); break;
                case 7 : $warning = "<p class='alert1'>$langProviderError8</p>"; $adapter->logout(); break;
            }
        
            // debug messages for hybridauth errors
            //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
            //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
        
            return false;
        }
    } //endif( isset( $_GET["provider"] ) && $_GET["provider"] )
    
    
    // *****************************
    //from here on runs an alternative version of proccess_login() where instead of a password, the provider 
    //user id is used and matched against the corresponding field in the db table.
    global $surname, $givenname, $email, $status, $is_admin, $language,
    $langInvalidId, $langAccountInactive1, $langAccountInactive2,
    $langNoCookies, $langEnterPlatform, $urlServer, $langHere,
    $auth_ids, $inactive_uid, $langTooManyFails;
    
    $pass = $user_data->identifier; //password = provider user id
    $auth = get_auth_active_methods();
    $is_eclass_unique = is_eclass_unique();
    
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
        $myrow = Database::get()->querySingle("SELECT id, surname, givenname, password, username, status, email, lang, verified_mail, facebook_uid, twitter_uid, google_uid, live_uid, yahoo_uid, linkedin_uid
                FROM user WHERE " . $provider . "_uid = ?s", $user_data->identifier);
                // cas might have alternative authentication defined
        $exists = 0;
        if (!isset($_COOKIE) or count($_COOKIE) == 0) {
            // Disallow login when cookies are disabled
            $auth_allow = 5;
        } elseif ($myrow) {
            $exists = 1;
            if (!empty($auth)) {
                if (in_array($myrow->password, $auth_ids)) {
                    // alternate methods login
                    //$auth_allow = alt_login($myrow, $provider, $pass); //this should NOT be called during HybridAuth!
                } else {
                    // eclass login
                    $auth_allow = login($myrow, $provider, $pass, $provider);
                }
            } else {
                $tool_content .= "<br>$langInvalidAuth<br>";
            }
        }
        if (!$exists and !$auth_allow) {
            //Since HybridAuth was used and there is not user id matched in the db, send the user to the registration form.
            header('Location: ' . $urlServer . 'modules/auth/registration.php?provider=' . $provider);
            
            //from this point and on, the code does not need to run since the user is redirected to the registration page
            $auth_allow = 4;
        }
    }
    
    if (!isset($_SESSION['uid'])) {
        switch ($auth_allow) {
            case 1: $warning .= "";
            session_regenerate_id();
            break;
            case 2: $warning .= "<p class='alert1'>$langInvalidId</p>";
            break;
            case 3: $warning .= "<p class='alert1'>$langAccountInactive1 " .
            "<a href='modules/auth/contactadmin.php?userid=$inactive_uid&amp;h=" .
            token_generate("userid=$inactive_uid") . "'>$langAccountInactive2</a></p>";
            break;
            case 4: $warning .= "<p class='alert1'>$langInvalidId</p>";
            increaseLoginFailure();
            break;
            case 5: $warning .= "<p class='alert1'>$langNoCookies</p>";
            break;
            case 6: $warning .= "<p class='alert1'>$langEnterPlatform <a href='{$urlServer}secure/index.php'>$langHere</a></p>";
            break;
            case 7: $warning .= "<p class='alert1'>$langEnterPlatform <a href='{$urlServer}secure/cas.php'>$langHere</a></p>";
            break;
            case 8: $warning .= "<p class='alert1'>$langTooManyFails</p>";
            break;
            default:
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
        $next = autounquote($_POST['next']);
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

function login($user_info_object, $posted_uname, $pass, $provider = NULL) {
    global $session;

    $pass_match = false;
    $hasher = new PasswordHash(8, false);

    if(is_null($provider)) {
        if (check_username_sensitivity($posted_uname, $user_info_object->username)) {
            if ($hasher->CheckPassword($pass, $user_info_object->password)) {
                $pass_match = true;
            } else if (strlen($user_info_object->password) < 60 && md5($pass) == $user_info_object->password) {
                $pass_match = true;
                // password is in old md5 format, update transparently
                $password_encrypted = $hasher->HashPassword($pass);
                $user_info_object->password = $password_encrypted;
                Database::core()->query("SET sql_mode = TRADITIONAL");
                Database::get()->query("UPDATE user SET password = ?s WHERE id = ?d", $password_encrypted, $user_info_object->id);
            }
        }
    } else {
        switch ($provider) {
            case 'Facebook': if($pass == $user_info_object->facebook_uid) $pass_match = true; break;
            case 'Twitter': if($pass == $user_info_object->twitter_uid) $pass_match = true; break;
            case 'Google': if($pass == $user_info_object->google_uid) $pass_match = true; break;
            case 'Live': if($pass == $user_info_object->live_uid) $pass_match = true; break;
            case 'Yahoo': if($pass == $user_info_object->yahoo_uid) $pass_match = true; break;
            case 'LinkedIn': if($pass == $user_info_object->linkedin_uid) $pass_match = true; break;
        }
    }

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

    $auth = array_search($user_info_object->password, $auth_ids);
    $auth_method_settings = get_auth_settings($auth);
    $auth_allow = 1;

    // a CAS user might enter a username/password in the form, instead of doing CAS login
    // check auth according to the defined alternative authentication method of CAS
    if ($auth == 7) {
        $cas = explode('|', $auth_method_settings['auth_settings']);
        $cas_altauth = intval(str_replace('cas_altauth=', '', $cas[7]));
        // check if alt auth is valid and active
        if (($cas_altauth > 0) && check_auth_active($cas_altauth)) {
            $auth = $cas_altauth;
            // fetch settings of alt auth
            $auth_method_settings = get_auth_settings($auth);
        } else {
            return 7; // Redirect to CAS login
        }
    }

    if ($auth == 6) {
        return 6; // Redirect to Shibboleth login
    }

    if (($user_info_object->password == $auth_method_settings['auth_name']) || !empty($cas_altauth)) {
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
        $is_departmentmanage_user, $langUserAltAuth;

    $alt_auth_stud_reg = get_config('alt_auth_stud_reg');

    if ($alt_auth_stud_reg == 2) {
        $autoregister = TRUE;
    } else {
        $autoregister = FALSE;
    }

    if ($type == 'shibboleth') {
        $uname = $_SESSION['shib_uname'];
        $email = $_SESSION['shib_email'];
        $shib_surname = $_SESSION['shib_surname'];
        $shibsettings = Database::get()->querySingle("SELECT auth_settings FROM auth WHERE auth_id = 6");
        if ($shibsettings) {
            if ($shibsettings->auth_settings != 'shibboleth' and $shibsettings->auth_settings != '') {
                $shibseparator = $shibsettings->auth_settings;
            }
            if (strpos($shib_surname, $shibseparator)) {
                $temp = explode($shibseparator, $shib_surname);
                $givenname = $temp[0];
                $surname = $temp[1];
            }
        }
    } elseif ($type == 'cas') {
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
    }

    // user is authenticated, now let's see if he is registered also in db
    if (get_config('case_insensitive_usernames')) {
        $sqlLogin = "COLLATE utf8_general_ci = ?s";
    } else {
        $sqlLogin = "COLLATE utf8_bin = ?s";
    }
    $info = Database::get()->querySingle("SELECT id, surname, username, password, givenname, status, email, lang, verified_mail
						FROM user WHERE username $sqlLogin", $uname);

    if ($info) {
        // if user found
        if ($info->password != $type) {
            // has different auth method - redirect to home page
            unset($_SESSION['shib_uname']);
            unset($_SESSION['shib_email']);
            unset($_SESSION['shib_surname']);
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

            $userObj->refresh($info->id, $options['departments']);
            user_hook($_SESSION['uid']);

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
            if (isset($_SESSION['langswitch'])) {
                $language = $_SESSION['langswitch'];
            } else {
                $language = $info->lang;
            }
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
