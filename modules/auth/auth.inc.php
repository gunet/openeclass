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
// pop3 class
include 'modules/auth/methods/pop3.php';

$auth_ids = array(1 => 'eclass',
    2 => 'pop3',
    3 => 'imap',
    4 => 'ldap',
    5 => 'db',
    6 => 'shibboleth',
    7 => 'cas');


/* * **************************************************************
  find/return the id of the default authentication method
  return $auth_id (a value between 1 and 7: 1-eclass,2-pop3,3-imap,4-ldap,5-db,6-shibboleth,7-cas)
 * ************************************************************** */

function get_auth_id() {
    $auth_method = db_query("SELECT auth_id FROM auth WHERE auth_default = 1");
    if ($auth_method) {
        $authrow = mysql_fetch_row($auth_method);
        if (mysql_num_rows($auth_method) == 1) {
            $auth_id = $authrow[0];
            return $auth_id;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

/* * **************************************************************
  find/return the ids of the default authentication methods
  return $auth_methods (array with all the values of the defined/active methods)
 * ************************************************************** */

function get_auth_active_methods() {
    $auth_methods = array();
    $q = db_query("SELECT auth_id, auth_settings FROM auth WHERE auth_default = 1");
    if ($q) {
        while ($row = mysql_fetch_row($q)) {
            // get only those with valid, not empty settings
            if ($row[0] == 1 or !empty($row[1])) {
                $auth_methods[] = $row[0];
            }
        }
    }
    return $auth_methods;
}

/* * **************************************************************
  check if method $auth is active
 * ************************************************************** */

function check_auth_active($auth) {
    $active_auth = db_query("SELECT auth_default, auth_settings FROM auth WHERE auth_id = $auth");
    if ($active_auth) {
        $authrow = mysql_fetch_row($active_auth);
        // return true only if method is valid,not empty settings
        if (($authrow[0] == 1) && !empty($authrow[1])) {
            return true;
        }
    }
    return false;
}

/* * **************************************************************
  find if the eclass method is the only one active in the platform
  return $is_eclass_unique (integer)
 * ************************************************************** */

function is_eclass_unique() {
    $is_eclass_unique = 0;
    $sql = "SELECT auth_id, auth_settings FROM auth WHERE auth_default=1";
    $auth_method = db_query($sql);
    if ($auth_method) {
        $count_methods = 0;
        $is_eclass = 0;
        while ($authrow = mysql_fetch_row($auth_method)) {
            if ($authrow[0] == 1) {
                $is_eclass = 1;
                $count_methods++;
            } else {
                if (empty($authrow[1])) {
                    continue;
                } else {
                    $count_methods++;
                }
            }
        }
        if (($is_eclass == 1) && ($count_methods == 1)) {
            $is_eclass_unique = 1;
        } else {
            $is_eclass_unique = 0;
        }
    } else {
        $is_eclass_unique = 0;
    }

    return $is_eclass_unique;
}

/* * **************************************************************
  count users for each authentication method
  return count of users for auth method
 * ************************************************************** */

function count_auth_users($auth) {
    global $auth_ids;
    $auth = intval($auth);

    if ($auth === 1) {
        $qry = "SELECT COUNT(*) FROM user WHERE password != '{$auth_ids[1]}'";
        for ($i = 2; $i <= count($auth_ids); $i++) {
            $qry .= " and password != '{$auth_ids[$i]}'";
        }
    } else {
        $qry = "SELECT COUNT(*) FROM user WHERE password = '" . $auth_ids[$auth] . "'";
    }
    $result = db_query($qry);
    if ($result) {
        return(intval(mysql_result($result, 0)));
    }
    return 0;
}

/* * **************************************************************
  find/return the string, describing in words the default authentication method
  return $m (string)
 * ************************************************************** */

function get_auth_info($auth) {
    global $langViaeClass, $langViaPop, $langViaImap, $langViaLdap, $langViaDB, $langViaShibboleth, $langViaCAS, $langNbUsers, $langAuthChangeUser;

    if (!empty($auth)) {
        switch ($auth) {
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
    $result = db_query("SELECT * FROM auth WHERE auth_id = " . $auth);
    if ($result) {
        if (mysql_num_rows($result) == 1) {
            $settings = mysql_fetch_assoc($result);
            $auth_settings = $settings['auth_settings'];
            switch ($auth) {
                case 2:
                    $settings['pop3host'] = str_replace('pop3host=', '', $auth_settings);
                    break;
                case 3:
                    $settings['imaphost'] = str_replace('imaphost=', '', $auth_settings);
                    break;
                case 4:
                    $ldap = explode('|', $auth_settings);
                    $settings = array_merge($settings, array(
                        'ldaphost' => str_replace('ldaphost=', '', @$ldap[0]),
                        'ldap_base' => str_replace('ldap_base=', '', @$ldap[1]),
                        'ldapbind_dn' => str_replace('ldapbind_dn=', '', @$ldap[2]),
                        'ldapbind_pw' => str_replace('ldapbind_pw=', '', @$ldap[3]),
                        'ldap_login_attr' => str_replace('ldap_login_attr=', '', @$ldap[4]),
                        'ldap_login_attr2' => str_replace('ldap_login_attr2=', '', @$ldap[5])));
                    break;
                case 5:
                    $edb = explode('|', $auth_settings);
                    $settings = array_merge($settings, array(
                        'dbhost' => str_replace('dbhost=', '', @$edb[0]),
                        'dbname' => str_replace('dbname=', '', @$edb[1]),
                        'dbuser' => str_replace('dbuser=', '', @$edb[2]),
                        'dbpass' => str_replace('dbpass=', '', @$edb[3]),
                        'dbtable' => str_replace('dbtable=', '', @$edb[4]),
                        'dbfielduser' => str_replace('dbfielduser=', '', @$edb[5]),
                        'dbfieldpass' => str_replace('dbfieldpass=', '', @$edb[6])));
                    break;
                case 7:
                    $cas = explode('|', $auth_settings);
                    $settings = array_merge($settings, array(
                        'cas_host' => str_replace('cas_host=', '', @$cas[0]),
                        'cas_port' => str_replace('cas_port=', '', @$cas[1]),
                        'cas_context' => str_replace('cas_context=', '', @$cas[2]),
                        'cas_cachain' => str_replace('cas_cachain=', '', @$cas[3]),
                        'casusermailattr' => str_replace('casusermailattr=', '', @$cas[4]),
                        'casuserfirstattr' => str_replace('casuserfirstattr=', '', @$cas[5]),
                        'casuserlastattr' => str_replace('casuserlastattr=', '', @$cas[6]),
                        'cas_altauth' => str_replace('cas_altauth=', '', @$cas[7]),
                        'cas_logout' => str_replace('cas_logout=', '', @$cas[8]),
                        'cas_ssout' => str_replace('cas_ssout=', '', @$cas[9])));
                    break;
            }
            $settings['auth_name'] = $auth_ids[$auth];
            return $settings;
        }
    }
    return 0;
}

/* * **************************************************************
  Try to authenticate the user with the admin-defined auth method
  true (the user is authenticated) / false (not authenticated)

  $auth an integer-value for auth method(1:eclass, 2:pop3, 3:imap, 4:ldap, 5:db, 6:shibboleth, 7:cas)
  $test_username
  $test_password
  return $testauth (boolean: true-is authenticated, false-is not)

  Sets the global variable $auth_user_info to an array with the following
  keys, if available from the current auth method:
  firstname (LDAP attribute: givenname)
  lastname (LDAP attribute: sn)
  email (LDAP attribute: mail)
 * ************************************************************** */

function auth_user_login($auth, $test_username, $test_password, $settings) {
    global $mysqlMainDb, $webDir;

    $testauth = false;
    switch ($auth) {
        case '1':

            $unamewhere = (get_config('case_insensitive_usernames')) ? "= " : "COLLATE utf8_bin = ";
            $sql = "SELECT password FROM user WHERE username " . $unamewhere . quote($test_username);
            $result = db_query($sql);

            if (mysql_num_rows($result) == 1) {

                $myrow = mysql_fetch_assoc($result);
                $hasher = new PasswordHash(8, false);

                if ($hasher->CheckPassword($test_password, $myrow['password']))
                    $testauth = true;
                else if (strlen($myrow['password']) < 60 && md5($test_password) == $myrow['password']) {
                    $testauth = true;

                    // password is in old md5 format, update transparently
                    $password_encrypted = $hasher->HashPassword($test_password);

                    $sql = "UPDATE user SET password = " . quote($password_encrypted) . " WHERE username COLLATE utf8_bin = " . quote($test_username);
                    db_query($sql, $mysqlMainDb);
                }
            }
            break;

        case '2':
            $pop3 = new pop3_class;
            $pop3->hostname = $settings['pop3host'];                // POP 3 server host name
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
                                $lastname = get_ldap_attribute($userinfo, 'sn');
                                $firstname = get_ldap_attribute($userinfo, 'givenname');
                                if (empty($firstname)) {
                                    $cn = get_ldap_attribute($userinfo, 'cn');
                                    $firstname = trim(str_replace($lastname, '', $cn));
                                }
                                $GLOBALS['auth_user_info'] = array(
                                    'firstname' => $firstname,
                                    'lastname' => $lastname,
                                    'email' => get_ldap_attribute($userinfo, 'mail'));
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
            $link = mysql_connect($settings['dbhost'], $settings['dbuser'], $settings['dbpass'], true);
            if ($link) {
                $db_ext = mysql_select_db($settings['dbname'], $link);
                if ($db_ext) {
                    $qry = "SELECT * FROM `$settings[dbname]`.`$settings[dbtable]`
                                           WHERE `$settings[dbfielduser]` = " . quote($test_username) . " AND
                                                 `$settings[dbfieldpass]` = " . quote($test_password);
                    $res = mysql_query($qry, $link);
                    if ($res) {
                        if (mysql_num_rows($res) > 0) {
                            $testauth = true;
                            mysql_close($link);
                            // Reconnect to main database
                            $GLOBALS['db'] = mysql_connect($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword']);
                            if (mysql_version())
                                mysql_query('SET NAMES utf8');
                            mysql_select_db($mysqlMainDb);
                        }
                    }
                }
            }
            break;

        case '6':
            $path = "${webDir}secure/";
            if (!file_exists($path)) {
                if (!mkdir($path, 0700)) {
                    $testauth = false;
                }
            } else {
                $indexfile = $path . 'index.php';
                $index_regfile = $path . 'index_reg.php';

                // creation of secure/index.php file
                $f = fopen($indexfile, 'w');
                $filecontents = '<?php
session_start();
$_SESSION[\'shib_email\'] = ' . $settings['shibemail'] . ';
$_SESSION[\'shib_uname\'] = ' . $settings['shibuname'] . ';
$_SESSION[\'shib_surname\'] = ' . $settings['shibcn'] . ';
header("Location: ../index.php");
';
                if (fwrite($f, $filecontents)) {
                    $testauth = true;
                }
                fclose($f);

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
  a registration unix timestamp and an expiration unix timestamp.
  By default is set to last a year

  $userid : the id of the account
  return $testauth (boolean: true-is authenticated, false-is not)
 * ************************************************************** */

function check_activity($userid) {
    $result = Database::get()->querySingle("SELECT expires_at FROM user WHERE id = ?", intval($userid));
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
        // multivalue: get only the first attribute
        if (is_array($value))
            $attrs[$key] = $value[0];
        else
            $attrs[$key] = $value;
    }

    $ret = array();
    if (!empty($settings['casusermailattr']))
        if (!empty($attrs[$settings['casusermailattr']])) {
            $ret['casusermailattr'] = $attrs[$settings['casusermailattr']];
            $GLOBALS['auth_user_info']['email'] = $attrs[$settings['casusermailattr']];
        }

    if (!empty($settings['casuserfirstattr']))
        if (!empty($attrs[$settings['casuserfirstattr']])) {
            $ret['casuserfirstattr'] = $attrs[$settings['casuserfirstattr']];
            $GLOBALS['auth_user_info']['firstname'] = $attrs[$settings['casuserfirstattr']];
        }

    if (!empty($settings['casuserlastattr']))
        if (!empty($attrs[$settings['casuserlastattr']])) {
            $ret['casuserlastattr'] = $attrs[$settings['casuserlastattr']];
            $GLOBALS['auth_user_info']['lastname'] = $attrs[$settings['casuserlastattr']];
        }

    return $ret;
}

/* * **************************************************************
  Process login form submission
 * ************************************************************** */

function process_login() {
    global $warning, $surname, $givenname, $email, $status, $is_admin, $language,
    $langInvalidId, $langAccountInactive1, $langAccountInactive2,
    $langNoCookies, $langEnterPlatform, $urlServer, $urlAppend, $langHere,
    $auth_ids, $inactive_uid, $langTooManyFails;

    if (isset($_POST['uname'])) {
        $posted_uname = autounquote(canonicalize_whitespace($_POST['uname']));
    } else {
        $posted_uname = '';
    }

    $pass = isset($_POST['pass']) ? autounquote($_POST['pass']) : '';
    $auth = get_auth_active_methods();
    $is_eclass_unique = is_eclass_unique();

    if (isset($_POST['submit'])) {
        unset($_SESSION['uid']);        
        $auth_allow = 0;

        if (get_config('login_fail_check')) {
            $r = db_query("SELECT 1 FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' 
                                        AND COUNT > " . intval(get_config('login_fail_threshold')) . " 
                                        AND DATE_SUB(CURRENT_TIMESTAMP, interval " . intval(get_config('login_fail_deny_interval')) . " minute) < last_fail");
        }
        if (get_config('login_fail_check') && $r && mysql_num_rows($r) > 0) {
            $auth_allow = 8;
        } else {
            $sqlLogin = "SELECT id, surname, givenname, password, username, status, email, lang, verified_mail
                                FROM user WHERE username ";
            if (get_config('case_insensitive_usernames')) {
                $sqlLogin .= "= " . quote($posted_uname);
            } else {
                $sqlLogin .= "COLLATE utf8_bin = " . quote($posted_uname);
            }
            $result = db_query($sqlLogin);
            // cas might have alternative authentication defined
            $exists = 0;
            if (!isset($_COOKIE) or count($_COOKIE) == 0) {
                // Disallow login when cookies are disabled
                $auth_allow = 5;
            } elseif ($pass === '') {
                // Disallow login with empty password
                $auth_allow = 4;
            } else {
                while ($myrow = mysql_fetch_assoc($result)) {
                    $exists = 1;
                    if (!empty($auth)) {
                        if (in_array($myrow['password'], $auth_ids)) {
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
                Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $posted_uname,
                    'pass' => $pass));
                $auth_allow = 4;
            }
        }

        if (!isset($_SESSION['uid'])) {
            switch ($auth_allow) {
                case 1: $warning .= "";
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
            db_query("INSERT INTO loginout
						(loginout.id_user, loginout.ip, loginout.when, loginout.action)
						VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");            
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
    }  // end of user authentication
}

/* * **************************************************************
  Authenticate user via eclass
 * ************************************************************** */

function login($user_info_array, $posted_uname, $pass) {
    $pass_match = false;
    $hasher = new PasswordHash(8, false);

    if (check_username_sensitivity($posted_uname, $user_info_array['username'])) {
        if ($hasher->CheckPassword($pass, $user_info_array['password'])) {
            $pass_match = true;
        } else if (strlen($user_info_array['password']) < 60 && md5($pass) == $user_info_array['password']) {
            $pass_match = true;
            // password is in old md5 format, update transparently
            $password_encrypted = $hasher->HashPassword($pass);
            $user_info_array['password'] = $password_encrypted;

            db_query('SET sql_mode = TRADITIONAL');
            $sql = "UPDATE user SET password = " . quote($password_encrypted) . " 
                                       WHERE id = " . intval($user_info_array['id']);
            db_query($sql);
        }
    }

    if ($pass_match) {
        // check if account is active
        $is_active = check_activity($user_info_array['id']);
        // check for admin privileges
        $admin_rights = get_admin_rights($user_info_array['id']);
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
            //$_SESSION['is_admin'] = !(!($user_info_array['is_admin'])); // double 'not' to handle NULL
            $_SESSION['uid'] = $user_info_array['id'];
            $_SESSION['uname'] = $user_info_array['username'];
            $_SESSION['surname'] = $user_info_array['surname'];
            $_SESSION['givenname'] = $user_info_array['givenname'];
            $_SESSION['status'] = $user_info_array['status'];
            $_SESSION['email'] = $user_info_array['email'];
            $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_array['lang'];
            $auth_allow = 1;
        } else {
            $auth_allow = 3;
            $GLOBALS['inactive_uid'] = $user_info_array['id'];
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

function alt_login($user_info_array, $uname, $pass) {
    global $warning, $auth_ids;

    $auth = array_search($user_info_array['password'], $auth_ids);
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

    if (($user_info_array['password'] == $auth_method_settings['auth_name']) || !empty($cas_altauth)) {
        $is_valid = auth_user_login($auth, $uname, $pass, $auth_method_settings);
        if ($is_valid) {
            $is_active = check_activity($user_info_array['id']);
            // check for admin privileges
            $admin_rights = get_admin_rights($user_info_array['id']);
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
                $user = $user_info_array["id"];
            }
        } else {
            $auth_allow = 2;
            // log invalid logins
            Log::record(0, 0, LOG_LOGIN_FAILURE, array('uname' => $uname,
                'pass' => $pass));
        }
        if ($auth_allow == 1) {
            $_SESSION['uid'] = $user_info_array['id'];
            $_SESSION['uname'] = $user_info_array['username'];
            // if ldap entries have changed update database
            if (!empty($auth_user_info['firstname']) and (!empty($auth_user_info['lastname'])) and (($user_info_array['givenname'] != $auth_user_info['firstname']) or
                    ($user_info_array['surname'] != $auth_user_info['lastname']))) {
                db_query("UPDATE user SET givenname = '" . $auth_user_info['firstname'] . "',
                                                          surname = '" . $auth_user_info['lastname'] . "'
                                                      WHERE id = " . $user_info_array['id'] . "");
                $_SESSION['surname'] = $auth_user_info['firstname'];
                $_SESSION['givenname'] = $auth_user_info['lastname'];
            } else {
                $_SESSION['surname'] = $user_info_array['surname'];
                $_SESSION['givenname'] = $user_info_array['givenname'];
            }
            $_SESSION['status'] = $user_info_array['status'];
            $_SESSION['email'] = $user_info_array['email'];
            $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_array['lang'];
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
    global $surname, $givenname, $email, $status, $language, $urlServer,
    $is_admin, $is_power_user, $is_usermanage_user, $is_departmentmanage_user, $langUserAltAuth;

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
        list($shibsettings) = mysql_fetch_row(db_query('SELECT auth_settings FROM auth WHERE auth_id = 6'));
        if ($shibsettings != 'shibboleth' and $shibsettings != '') {
            $shibseparator = $shibsettings;
        }
        if (strpos($shib_surname, $shibseparator)) {
            $temp = explode($shibseparator, $shib_surname);
            $givenname = $temp[0];
            $surname = $temp[1];
        }
    } elseif ($type == 'cas') {
        $uname = $_SESSION['cas_uname'];
        $surname = $_SESSION['cas_surname'];
        $givenname = $_SESSION['cas_givenname'];
        $email = isset($_SESSION['cas_email']) ? $_SESSION['cas_email'] : '';
    }
    // user is authenticated, now let's see if he is registered also in db
    $sqlLogin = "SELECT id, surname, username, password, givenname, status, email, lang, verified_mail
						FROM user WHERE username ";
    if (get_config('case_insensitive_usernames')) {
        $sqlLogin .= "= " . quote($uname);
    } else {
        $sqlLogin .= "COLLATE utf8_bin = " . quote($uname);
    }
    $r = db_query($sqlLogin);

    if (mysql_num_rows($r) > 0) {
        // if user found
        $info = mysql_fetch_assoc($r);
        if ($info['password'] != $type) {
            // has different auth method - redirect to home page
            unset($_SESSION['shib_uname']);
            unset($_SESSION['shib_email']);
            unset($_SESSION['shib_surname']);
            unset($_SESSION['cas_uname']);
            unset($_SESSION['cas_email']);
            unset($_SESSION['cas_surname']);
            unset($_SESSION['cas_givenname']);
            $_SESSION['errMessage'] = "<div class='caution'>$langUserAltAuth</div>";
            redirect_to_home_page();
        } else {
            // don't force email address from CAS/Shibboleth.
            // user might prefer a different one
            if (!empty($info['email'])) {
                $email = $info['email'];
            }
            if (!empty($info['status'])) {
                $status = $info['status'];
            }
            // update user information
            db_query("UPDATE user SET surname = " . quote($surname) . ",
							givenname = " . quote($givenname) . ",
							email = " . quote($email) . "
							WHERE id = $info[id]");
            // check for admin privileges
            $admin_rights = get_admin_rights($info['id']);
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
            $_SESSION['uid'] = $info['id'];
            //$is_admin = !(!($info['is_admin'])); // double 'not' to handle NULL            
            if (isset($_SESSION['langswitch'])) {
                $language = $_SESSION['langswitch'];
            } else {
                $language = $info['lang'];
            }
        }
    } elseif ($autoregister and !get_config('am_required')) {
        // else create him automatically
        if (get_config('email_verification_required')) {
            $verified_mail = 0;
            $_SESSION['mail_verification_required'] = 1;
        } else {
            $verified_mail = 2;
        }

        $_SESSION['uid'] = Database::get()->query("INSERT INTO user SET surname = ?, givenname = ?, password = ?, 
                                       username = ?, email = ?, status = ?, lang = 'el', 
                                       registered_at = " . DBHelper::timeAfter() . ",  expires_at = " .
                DBHelper::timeAfter(get_config('account_duration')) . ", whitelist = ''", $surname, $givenname, $type, $uname, $email, USER_STUDENT);        
        $language = $_SESSION['langswitch'] = 'el';
    } else {
        // user not registered, automatic registration disabled
        // redirect to registration screen
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        header("Location: {$urlServer}modules/auth/registration.php");
        exit;
    }

    $_SESSION['uname'] = $uname;
    $_SESSION['surname'] = $surname;
    $_SESSION['givenname'] = $givenname;
    $_SESSION['email'] = $email;
    $_SESSION['status'] = $status;
    //$_SESSION['is_admin'] = $is_admin;
    $_SESSION['shib_user'] = 1; // now we are shibboleth user    

    db_query("INSERT INTO loginout
					(loginout.id_user, loginout.ip, loginout.when, loginout.action)
					VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");

    if (get_config('email_verification_required') and
            get_mail_ver_status($_SESSION['uid']) == EMAIL_VERIFICATION_REQUIRED) {
        $_SESSION['mail_verification_required'] = 1;
        // init.php is already loaded so redirect from here
        header("Location:" . $urlServer . "modules/auth/mail_verify_change.php");
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

function increaseLoginFailure() {
    if (!get_config('login_fail_check'))
        return;

    $ip = $_SERVER['REMOTE_ADDR'];
    $r = db_query("SELECT 1 FROM login_failure WHERE ip = '" . $ip . "'");

    if (mysql_num_rows($r) > 0)
        db_query("UPDATE login_failure SET count = count + 1, last_fail = CURRENT_TIMESTAMP WHERE ip = '" . $ip . "'");
    else
        db_query("INSERT INTO login_failure (id, ip, count, last_fail) VALUES (NULL, '" . $ip . "', 1, CURRENT_TIMESTAMP)");
}

function resetLoginFailure() {
    if (!get_config('login_fail_check'))
        return;

    db_query("DELETE FROM login_failure WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND DATE_SUB(CURRENT_TIMESTAMP, INTERVAL " . intval(get_config('login_fail_forgive_interval')) . " HOUR) >= last_fail"); // de-penalize only after 24 hours
}
