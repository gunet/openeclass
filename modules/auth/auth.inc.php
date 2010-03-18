<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/


/*===========================================================================
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

// pop3 class
require("methods/pop3.php");

/****************************************************************
find/return the id of the default authentication method
return $auth_id (a value between 1 and 5: 1-eclass,2-pop3,3-imap,4-ldap,5-db)
****************************************************************/
function get_auth_id()
{
	global $db;
	$sql = "SELECT auth_id FROM auth WHERE auth_default=1";
  $auth_method = mysql_query($sql,$db);
  if($auth_method)
  {
		$authrow = mysql_fetch_row($auth_method);
		if(mysql_num_rows($auth_method)==1)
		{
	    $auth_id = $authrow[0];
	    return $auth_id;
		}
		else
		{
	    return 0;
		}
	}
  else
  {
		return 0;
	}
}

/****************************************************************
find/return the ids of the default authentication methods
return $auth_methods (array with all the values of the defined/active methods)
****************************************************************/
function get_auth_active_methods()
{
        global $db;
        $sql = "SELECT auth_id,auth_settings FROM auth WHERE auth_default=1";
        $auth_method = mysql_query($sql,$db);
        if($auth_method) {
                $auth_methods = array();
                while($authrow = mysql_fetch_row($auth_method)) {
                        // get only those with valid,not empty settings
                        if(($authrow[0]!=1) && (empty($authrow[1]))) {
                                continue;
                        } else {
                                $auth_methods[] = $authrow[0];
                        }
                }
                if(!empty($auth_methods)) {
                        return $auth_methods;
                } else {
                        return 0;
                }
        } else {
                return 0;
        }
}

/****************************************************************
find if the eclass method is the only one active in the platform
return $is_eclass_unique (integer)
****************************************************************/
function is_eclass_unique()
{
	global $db;
	$is_eclass_unique = 0;
	$sql = "SELECT auth_id,auth_settings FROM auth WHERE auth_default=1";
  $auth_method = mysql_query($sql,$db);
  if($auth_method)
  {
		$count_methods = 0;
		$is_eclass = 0;
		while($authrow = mysql_fetch_row($auth_method))
		{
			if($authrow[0]==1)
			{
				$is_eclass = 1;
				$count_methods++;
			}
			else
			{
				if(empty($authrow[1]))
				{
					continue;
				}
				else
				{
					$count_methods++;
				}
			}
		}
		if(($is_eclass==1) && ($count_methods==1))
		{
			$is_eclass_unique = 1;
		}
		else
		{
			$is_eclass_unique = 0;
		}
	}
  else
  {
		$is_eclass_unique = 0;
	}
	
	return $is_eclass_unique;
	
}

/****************************************************************
find/return the string, describing in words the default authentication method
return $m (string)
****************************************************************/
function get_auth_info($auth)
{
	global $langViaeClass, $langViaPop, $langViaImap, $langViaLdap, $langViaDB, $langViaShibboleth;

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
			default: $m = 0;
				break;
		}
		return $m;
	} else {
		return 0;
	}
}

/****************************************************************
find/return the settings of the default authentication method

$auth : integer a value between 1 and 5: 1-eclass,2-pop3,3-imap,4-ldap,5-db)
return $auth_row : an associative array
****************************************************************/
function get_auth_settings($auth)
{
	$qry = "SELECT * FROM auth WHERE auth_id = '".mysql_real_escape_string($auth)."'";
	$result = db_query($qry);
	if($result) {
		if(mysql_num_rows($result)==1) {
			$auth_row = mysql_fetch_array($result,MYSQL_ASSOC);
			return $auth_row;
		} else {
			return 0;
		}	
	} else {
		return 0;
	}
}

/****************************************************************
Try to authenticate the user with the admin-defined auth method
true (the user is authenticated) / false (not authenticated)

$auth an integer-value for auth method(1:eclass, 2:pop3, 3:imap, 4:ldap, 5:db)
$test_username
$test_password
return $testauth (boolean: true-is authenticated, false-is not)

Sets the global variable $auth_user_info to an array with the following
keys, if available from the current auth method:
firstname (LDAP attribute: givenname)
lastname (LDAP attribute: sn)
email (LDAP attribute: mail)
****************************************************************/
function auth_user_login ($auth, $test_username, $test_password)  {

	global $mysqlMainDb, $webDir;

    switch($auth)
    {
	case '1':
	    // Returns true if the username and password work and false if they don't
	    $sql = "SELECT user_id FROM user WHERE username='".mysql_real_escape_string($test_username)."' 
		AND password='".mysql_real_escape_string($test_password)."'";
	    $result = db_query($sql);
	    if(mysql_num_rows($result)==1) {
    		$testauth = true;
	    } else {
		$testauth = false;
	    }
	break;
	case '2':
	    $pop3host = $GLOBALS['pop3host'];
	    $pop3=new pop3_class;
	    $pop3->hostname = $pop3host; /* POP 3 server host name                      */
	    $pop3->port=110;	/* POP 3 server host port                      */
	    $user = $test_username;      /* Authentication user name                    */
	    $password = $test_password;                   	/* Authentication password                     */
	    $pop3->realm=""; /* Authentication realm or domain              */
	    $pop3->workstation="";	/* Workstation for NTLM authentication         */
	    $apop = 0;	/* Use APOP authentication                     */
	    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
	    $pop3->debug=0;                          /* Output debug information                    */
	    $pop3->html_debug=1;                     /* Debug information is in HTML                */
	    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

	    if(($error=$pop3->Open())=="") {
		if(($error=$pop3->Login($user,$password,$apop))=="") {
		    if($error=="" && ($error=$pop3->Close())=="")
		    {
			$testauth = true;
		    } else {
			$testauth = false;
		    }
		} else {
		    $testauth = false;
		}
	    } else {
		$testauth = false;
	    }
	    if($error!="") {
		$testauth = false;
	    }
	    break;
	
	case '3':
	    $imaphost = $GLOBALS['imaphost'];
	    $imapauth = imap_auth($imaphost, $test_username, $test_password);
	    if($imapauth)
	    {
		$testauth = true;
	    }
	    else
	    {
		$testauth = false;
	    }
	    break;
	case '4':
            $ldap_host = $GLOBALS['ldaphost'];
            $all_ldap_base_dn = array('uid' => $GLOBALS['ldapbind_dn']);
            // anonymous account:
            $a_user = $GLOBALS['ldapbind_user'];
            $a_pass = $GLOBALS['ldapbind_pw'];
            $testauth = false;

            $ldap = ldap_connect($ldap_host);

            if (!$ldap) {
                    $GLOBALS['auth_errors'] = 'Error connecting to LDAP host';
                    return false;
            } else {
                    // LDAP connection established - now process all
                    // base dn's until authentication is achieved or fail

                    @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

                    foreach ($all_ldap_base_dn as $attrib => $base_dn) {
                            // construct dn for user
                            $dn = "$attrib=$test_username,$base_dn";
                            // try an authenticated bind to confirm
                            // user/password pair
                            if (@ldap_bind($ldap, $dn, $test_password)) {
                                    $testauth = true;
                                    $search = "$attrib=$test_username";
                                    $userinforequest = ldap_search($ldap, $base_dn, $search);
                                    $userinfo = ldap_get_entries($ldap, $userinforequest);
                                    if ($userinfo["count"] == 1) {
                                            $GLOBALS['auth_user_info'] = array(
                                                'firstname' => get_ldap_attribute($userinfo, 'givenname'),
                                                'lastname' => get_ldap_attribute($userinfo, 'sn'),
                                                'email' => get_ldap_attribute($userinfo, 'mail'));
                                    }
                            } else {
                                $GLOBALS['auth_errors'] = ldap_error($ldap);
                                return false;
                            }
                    }
                    @ldap_unbind($ldap);
            }
            break;

	case '5':
	    $dbhost = $GLOBALS['dbhost'];
	    $dbname = $GLOBALS['dbname'];
	    $dbuser = $GLOBALS['dbuser'];
	    $dbpass = $GLOBALS['dbpass'];
	    $dbtable = $GLOBALS['dbtable'];
	    $dbfielduser = $GLOBALS['dbfielduser'];
	    $dbfieldpass = $GLOBALS['dbfieldpass'];
	    $newlink = true;
	    mysql_close($GLOBALS['db']); // close the previous link
	    $link = mysql_connect($dbhost,$dbuser,$dbpass,$newlink);
	    if($link) {
		$db_ext = mysql_select_db($dbname,$link);
		if($db_ext) {
		    	$qry = "SELECT * FROM ".$dbname.".".$dbtable." 
				WHERE ".$dbfielduser."='".mysql_real_escape_string($test_username)."' 
				AND ".$dbfieldpass."='".mysql_real_escape_string($test_password)."'";
		    	$res = mysql_query($qry,$link);
		    	if($res) {
				if(mysql_num_rows($res)>0) {
			     		$testauth = true;
			    		mysql_close($link);
					// Connect to database
					$GLOBALS['db'] = mysql_connect($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword']);
					if (mysql_version()) mysql_query("SET NAMES utf8");
					mysql_select_db($mysqlMainDb, $GLOBALS['db']);
				}
		    	} else {
				$testauth = false;
		    	}
		} else {
		    	$testauth = false;
			}
	    } else { 
		$testauth = false;
	    }
	    break;
	case '6':
		$path = "${webDir}secure/";
		if (!file_exists($path)) {
			if (!mkdir("$path", 0700)) {
				$testauth = false;
			}
		} else {
			// creation of secure/index.php file
			$f = fopen("${path}index.php", "w");
			$filecontents = '
<? 
session_start();
$_SESSION[\'shib_email\'] = '.autounquote($_POST['shibemail']).';
$_SESSION[\'shib_uname\'] = '.autounquote($_POST['shibuname']).';
$_SESSION[\'shib_nom\'] = '.autounquote($_POST['shibcn']).';
header("Location: ../index.php");
?>
';
			if (!fwrite($f, "$filecontents")) {
				$testauth = false;
			} else {
				$testauth = true;
			}
		}
		break;
	default:
	    $testauth = $auth;
	    break;
    }
    return $testauth;
}


/****************************************************************
Check if an account is active or not. Apart from admin, everybody has
a registration unix timestamp and an expiration unix timestamp.
By default is set to last a year

$userid : the id of the account
return $testauth (boolean: true-is authenticated, false-is not)
****************************************************************/
function check_activity($userid)
{
	global $db;
	$qry = "SELECT registered_at,expires_at FROM user WHERE user_id=".$userid;
	$res = mysql_query($qry,$db);
	if(($res) && (mysql_num_rows($res)==1))
	{
		$row = mysql_fetch_row($res);
		if($row[1]>time())
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	else
	{
		return 0;
	}
}

/****************************************************************
Return the value of an attribute from the result of an
LDAP search, converted to the current charset.
****************************************************************/
function get_ldap_attribute($search_result, $attribute)
{
        if (isset($search_result[0][$attribute][0])) {
                return iconv("UTF-8", $GLOBALS['charset'], $search_result[0][$attribute][0]);
        } else {
                return '';
        }
}
