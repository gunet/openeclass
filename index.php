<?php session_start();
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*
 * Index
 *
 * @version $Id$
 *
 * @abstract This file serves as the home page of eclass when the user
 * is not logged in.
 *
 */

/***************************************************************
*               HOME PAGE OF ECLASS		               *
****************************************************************
*/
define ("INDEX_START", 1);
$guest_allowed = true;
$path2add = 0;
include "include/baseTheme.php";
include "modules/auth/auth.inc.php";

//$homePage is used by baseTheme.php to parse correctly the breadcrumb
$homePage = true;

$tool_content = "";

// first check
// check if we can connect to database. If not then eclass is most likely not installed
if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword)) {
	$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
	if (mysql_version()) mysql_query("SET NAMES utf8");
}
if (!$db) {
	include ("not_installed.php");
}

// unset system that records visitor only once by course for statistics
include('include/action.php');
if (isset($dbname)) {
        mysql_select_db($dbname);
        $action = new action();
        $action->record('MODULE_ID_UNITS', 'exit');
}
unset($dbname);

// second check
// can we select a database? if not then there is some sort of a problem
if (isset($mysqlMainDb)) $selectResult = mysql_select_db($mysqlMainDb,$db);
if (!isset($selectResult)) {
	include("general_error.php");
}

//if platform admin allows usage of eclass personalised
//create a session so that each user can activate it for himself.
if (isset($persoIsActive)) {
	$_SESSION["perso_is_active"] = $persoIsActive;
}

// ------------------------------------------------------------------------
// if we try to login... then authenticate user.
// -----------------------------------------------------------------------
$warning = '';
if (isset($_SESSION['shib_uname'])) { // authenticate via shibboleth
	$shib_uname = $_SESSION['shib_uname'];
	$shib_email = $_SESSION['shib_email'];
	$shib_nom = $_SESSION['shib_nom'];
	if (strpos($shib_nom,';')) {
		$temp = explode(';',$shib_nom);
		$shib_nom_en = $temp[0];
		$shib_nom = $temp[1];
	}
	$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username='".$shib_uname."'";
	$r = db_query($sqlLogin); 
	if (mysql_num_rows($r) > 0) { // if shibboleth user found 
		while ($myrow = mysql_fetch_array($r)) {
			// update user information
			db_query("UPDATE user SET nom = '$shib_nom', prenom = '$shib_nom', email = '$shib_email' WHERE username = '$shib_uname'");

			$r2 = db_query($sqlLogin);
			while ($myrow2 = mysql_fetch_array($r2)) {
				$uid = $myrow2["user_id"];
	                	$is_admin = $myrow2["is_admin"];
        	        	$userPerso = $myrow2["perso"];
				$nom = $myrow2["nom"];
				$prenom = $myrow2["prenom"];
				if (isset($_SESSION['langswitch'])) {
	                		$language = $_SESSION['langswitch'];
	        		} else {
					$language = langcode_to_name($myrow["lang"]);
				}
			}
		}	
	} else { // else create him
		db_query("INSERT INTO user SET nom='$shib_nom', prenom='$shib_nom', password='shibboleth', 
			username='$shib_uname',email='$shib_email', statut=5, lang='el'");
		$uid = mysql_insert_id();
        	$userPerso = 'yes';
		$prenom = $nom = $shib_nom;
                $language = $_SESSION['langswitch'] = langcode_to_name('el');
	}
       	$_SESSION['uid'] = $uid;
	$_SESSION['nom'] = $nom;
	$_SESSION['prenom'] = $prenom;
	$_SESSION['email'] = $shib_email;
       	$_SESSION['statut'] = 5;
        $_SESSION['is_admin'] = $is_admin;
	$_SESSION['shib_user'] = 1; // now we are shibboleth user
	$log='yes';
} else { // normal authentication

if (isset($_POST['uname'])) {
        $uname = escapeSimple(preg_replace('/ +/', ' ', trim($_POST['uname'])));
} else {
        $uname = '';
}

$pass = isset($_POST['pass'])?$_POST['pass']:'';
$submit = isset($_POST['submit'])?$_POST['submit']:'';
$auth = get_auth_active_methods();
$is_eclass_unique = is_eclass_unique();

if(!empty($submit))
{
	unset($uid);
	$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username='".$uname."'";
	$result=mysql_query($sqlLogin);
	$check_passwords = array("pop3","imap","ldap","db");
	$warning = "";
	$auth_allow = 0;
	$exists = 0;
        if (empty($pass)) {
                $auth_allow = 4;        // Disallow login with empty password
        } else {
                while ($myrow = mysql_fetch_array($result))
                {
                        $exists = 1;
                        if(!empty($auth))
                        {
                                if(!in_array($myrow["password"],$check_passwords))
                                {
                                        // try to authenticate him via eclass

                                        if ($uname == escapeSimpleSelect($myrow["username"]))
                                        {
                                                if (md5($pass) == escapeSimpleSelect($myrow["password"]))
                                                {
                                                        // check if his/her account is active
                                                        $is_active = check_activity($myrow["user_id"]);
                                                        if($myrow["user_id"]==$myrow["is_admin"])
                                                        {
                                                                $is_active = 1;
                                                                $auth_allow = 1;
                                                        }
                                                        if($is_active==1)
                                                        {
                                                                $uid = $myrow["user_id"];
                                                                $nom = $myrow["nom"];
                                                                $prenom = $myrow["prenom"];
                                                                $statut = $myrow["statut"];
                                                                $email = $myrow["email"];
                                                                $is_admin = $myrow["is_admin"];
                                                                $userPerso = $myrow["perso"];
                                                                $language = $_SESSION['langswitch'] = langcode_to_name($myrow["lang"]);
                                                                $auth_allow = 1;
                                                        }
                                                        else
                                                        {
                                                                $auth_allow = 3;
                                                                $user = $myrow["user_id"];
                                                        }
                                                }
                                                else
                                                {
                                                        $auth_allow = 4; // means wrong password
                                                }
                                        }
                                        else
                                        {
                                                $auth_allow = 4; // means wrong username or password
                                        }
                                }
                                else
                                {
                                        // try to authenticate him via the alternative defined method
                                        switch($myrow["password"])
                                        {
                                                case 'eclass': $auth = 1; break;
                                                case 'pop3': $auth = 2; break;
                                                case 'imap': $auth = 3; break;
                                                case 'ldap': $auth = 4; break;
                                                case 'db': $auth = 5; break;
                                                default: break;
                                        }
                                        $auth_method_settings = get_auth_settings($auth);
                                        if($myrow['password'] == $auth_method_settings['auth_name'])
                                        {
                                                switch($auth)
                                                {
                                                        case '2': $pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
                                                        break;
                                                        case '3': $imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
                                                        break;
                                                        case 4: $ldapsettings = $auth_method_settings['auth_settings'];
                                                        $ldap = explode("|",$ldapsettings);
                                                        $ldaphost = str_replace("ldaphost=","",$ldap[0]); //ldaphost
                                                        $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]); //ldapbase_dn
                                                        $ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]); //ldapbind_user
                                                        $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]); // ldapbind_pw
                                                        break;
                                                        case 5: $dbsettings = $auth_method_settings['auth_settings'];
                                                        $edb = explode("|",$dbsettings);
                                                        $dbhost = str_replace("dbhost=","",$edb[0]); //dbhost
                                                        $dbname = str_replace("dbname=","",$edb[1]); //dbname
                                                        $dbuser = str_replace("dbuser=","",$edb[2]); //dbuser
                                                        $dbpass = str_replace("dbpass=","",$edb[3]); // dbpass
                                                        $dbtable = str_replace("dbtable=","",$edb[4]); //dbtable
                                                        $dbfielduser = str_replace("dbfielduser=","",$edb[5]);//dbfielduser
                                                        $dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);//dbfieldpass
                                                        break;
                                                        default:
							break;
                                                }
                                                $is_valid = auth_user_login($auth,$uname,$pass);
                                                if($is_valid)
                                                {
                                                        $is_active = check_activity($myrow["user_id"]);
                                                        if($myrow["user_id"] == $myrow["is_admin"]) // always the admin is active
                                                        {
                                                                $is_active = 1;
                                                        }
                                                        if(!empty($is_active))
                                                        {
                                                                $auth_allow = 1;
                                                        }
                                                        else
                                                        {
                                                                $auth_allow = 3;
                                                                $user = $myrow["user_id"];
                                                        }
                                                }
                                                else
                                                {
                                                        $auth_allow = 2;
                                                }
                                                if($auth_allow == 1)
                                                {
                                                        $uid = $myrow["user_id"];
                                                        $nom = $myrow["nom"];
                                                        $prenom = $myrow["prenom"];
                                                        $statut = $myrow["statut"];
                                                        $email = $myrow["email"];
                                                        $is_admin = $myrow["is_admin"];
                                                        $userPerso = $myrow["perso"];
                                                        $language = $_SESSION['langswitch'] = langcode_to_name($myrow["lang"]);
                                                }
                                                elseif($auth_allow == 2)
                                                {
                                                        continue;
                                                }
                                                elseif($auth_allow == 3)
                                                {
                                                        continue;
                                                }
                                                else
                                                {
                                                        $tool_content .= $langLoginFatalError."<br />";
                                                        continue;
                                                }
                                        }
                                        else
                                        {
                                                $warning .= "<br>$langInvalidAuth<br>";
                                        }
				} // end of alternative authentication  
                        }
                        else
                        {
                                $tool_content .= "<br>$langInvalidAuth<br>";
                        }
		}// while
        }

	if(empty($exists))
	{
		$auth_allow = 4;
	}

	if (!isset($uid))
	{
		switch($auth_allow)
		{
			case 1 : $warning .= ""; break;
			case 2 : $warning .= "<br /><font color='red'>".$langInvalidId ."</font><br />"; break;
			case 3 : $warning .= "<br />".$langAccountInactive1." <a href=\"modules/auth/contactadmin.php?userid=".$user."\">".$langAccountInactive2."</a><br /><br />"; break;
			case 4 : $warning .= "<br /><font color='red'>". $langInvalidId . "</font><br />"; break;
			default: break;
		}
	} else {
		$warning = '';
		$log='yes';
		$_SESSION['nom'] = $nom;
		$_SESSION['prenom'] = $prenom;
		$_SESSION['email'] = $email;
		$_SESSION['statut'] = $statut;
		$_SESSION['is_admin'] = $is_admin;
		$_SESSION['uid'] = $uid;
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action)
                VALUES ('', '$uid', '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
	}

	##[BEGIN personalisation modification]############
	//if user has activated the personalised interface
	//register a control session for it
//	if ((@$userPerso == "yes") && session_is_registered("perso_is_active")) {
	if (isset($_SESSION['perso_is_active'])) {
		$_SESSION['user_perso_active'] = $userPerso;
	}
	##[END personalisation modification]############

	}  // end of user authentication
} 

if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
else unset($uid);
//if the user logged in include the correct language files
//in case he has a different language set in his/her profile
if (isset($language)) {
	include("./modules/lang/$language/common.inc.php");
	include("./modules/lang/$language/messages.inc.php");
}
$nameTools = $langWelcomeToEclass;

//----------------------------------------------------------------
// if login succesful display courses lists
// --------------------------------------------------------------
if (isset($uid) AND !isset($logout)) {
	$nameTools = $langWelcomeToPortfolio;
	$require_help = true;
	$helpTopic="Portfolio";
	if (isset($_SESSION['user_perso_active']) and $_SESSION['user_perso_active'] == 'no') {
		if (!check_guest()){
			//if the user is not a guest, load classic view
			include "include/logged_in_content.php";
			draw($tool_content,1,null,null,null,null,$perso_tool_content);
		} else {
			//if the user is a guest send him straight to the corresponding lesson
			$guestSQL = db_query("SELECT `code_cours` FROM `cours_user` WHERE `user_id` = $uid", $mysqlMainDb);
			if (mysql_num_rows($guestSQL) > 0) {
				$sql_row = mysql_fetch_row($guestSQL);
				$dbname=$sql_row[0];
				session_register("dbname");
				header("location:".$urlServer."courses/$dbname/index.php");
			} else { // if course has deleted stop guest account
				$warning = "<br><font color='red'>".$langInvalidGuestAccount."</font><br>";
				include "include/logged_out_content.php";
				draw($tool_content, 0,'index');
			}
		}
	} else {
		//load classic view
		include "include/classic.php";
		draw($tool_content, 1, 'index');
	}

}	// end of if login

// -------------------------------------------------------------------------------------
// display login  page
// -------------------------------------------------------------------------------------

elseif ((isset($logout) && $logout && isset($uid)) OR (1==1)) {

	if (isset($logout) && $logout && isset($uid)) {
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user,
			loginout.ip, loginout.when, loginout.action)
			VALUES ('', '$uid', '$REMOTE_ADDR', NOW(), 'LOGOUT')");
		unset($prenom);
		unset($nom);
		unset($statut);
		session_destroy();
	}
	$require_help = true;
	$helpTopic="Init";
	include "include/logged_out_content.php";

	draw($tool_content, 0,'index');

} // end of display

?>
