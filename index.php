<?php session_start(); 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * Index
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
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
$guest_allowed=true;
$path2add=0;

include("include/baseTheme.php");

//@include("./include/lib/main.lib.php");
@include("./modules/auth/auth.inc.php");

//$homePage is used by baseTheme.php to parse correctly the breadcrumb
$homePage = true;

$tool_content = "";

@include("./modules/lang/$language/index.inc");
@include("./modules/lang/$language/trad4all.inc.php");

// first check
// check if we can connect to database. If not then eclass is most likely not installed
if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword)) {
	$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
	if (mysql_version()) mysql_query("SET NAMES greek");
}
if (!isset($db)) {
	include ("not_installed.php");
}

// second check
// can we select a database? if not then there is some sort of a problem
if (isset($mysqlMainDb)) $selectResult = mysql_select_db($mysqlMainDb,$db);
if (!isset($selectResult)) {
	include("general_error.php");
}

// unset system that records visitor only once by course for statistics
unset($alreadyHome);
unset($dbname);

//if platform admin allows usage of eclass personalised
//create a session so that each user can activate it for himself.
if ($persoIsActive) session_register("perso_is_active");

// ------------------------------------------------------------------------
// if we try to login...
// then authenticate user. First via LDAP then via MyQL
// -----------------------------------------------------------------------
$warning = '';
$uname = isset($_POST['uname'])?$_POST['uname']:'';
$pass = isset($_POST['pass'])?$_POST['pass']:'';
$submit = isset($_POST['submit'])?$_POST['submit']:'';
// $auth = get_auth_id();
$auth = get_auth_active_methods();
$is_eclass_unique = is_eclass_unique();

$uname = escapeSimple($uname);

if(!empty($submit))
{
	unset($uid);
	$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, inst_id, iduser is_admin, perso, lang
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username='".$uname."'";
	//$tool_content .= "<br>QUERY:".$sqlLogin."<br><br>";
	$result=mysql_query($sqlLogin);
	$check_passwords = array("pop3","imap","ldap","db");
	$warning = "";
	$auth_allow = 0;	$exists = 0;
	while ($myrow = mysql_fetch_array($result))
	{
		$exists = 1;
		if(!empty($auth))
		{
			if(!in_array($myrow["password"],$check_passwords))
			{
				// try to authenticate him via eclass
				$crypt = new Encryption;
				$key = $encryptkey;
				$password_decrypted = $crypt->decrypt($key, $myrow["password"]);
				//$tool_content .= "decrypted password taken from db:".$password_decrypted."<br>";
				$errors = $crypt->errors;
				$myrow["password"] = $password_decrypted;

				if ($uname == escapeSimpleSelect($myrow["username"]))
				{
					if ($pass == escapeSimpleSelect($myrow["password"]))
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
							$userPerso = $myrow["perso"];//user perso flag
							$userLanguage = $myrow["lang"];//user preferred language
							$auth_allow = 1;
						}
						else
						{
							// $warning .= "<br />Your account is inactive. <br />Please <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">contact the Eclass Admin.</a><br /><br />";
							// $warning .= "<br />".$langAccountInactive1." <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">".$langAccountInactive2."</a><br /><br />";
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
				if($myrow['password']==$auth_method_settings['auth_name'])
				{
					switch($auth)
					{
						case '2':	$pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
						break;
						case '3':	$imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
						break;
						case 4:	$ldapsettings = $auth_method_settings['auth_settings'];
						$ldap = explode("|",$ldapsettings);
						$ldaphost = str_replace("ldaphost=","",$ldap[0]);	//ldaphost
						$ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]);	//ldapbase_dn
						$ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]);	//ldapbind_user
						$ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);		// ldapbind_pw
						break;
						case 5:	$dbsettings = $auth_method_settings['auth_settings'];
						$edb = explode("|",$dbsettings);
						$dbhost = str_replace("dbhost=","",$edb[0]);	//dbhost
						$dbname = str_replace("dbname=","",$edb[1]);	//dbname
						$dbuser = str_replace("dbuser=","",$edb[2]);//dbuser
						$dbpass = str_replace("dbpass=","",$edb[3]);// dbpass
						$dbtable = str_replace("dbtable=","",$edb[4]);//dbtable
						$dbfielduser = str_replace("dbfielduser=","",$edb[5]);//dbfielduser
						$dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);//dbfieldpass
						break;
						default:
							break;
					}

					$is_valid = auth_user_login($auth,$uname,$pass);
					if($is_valid)
					{
						$is_active = check_activity($myrow["user_id"]);		// check if the account is active
						if($myrow["user_id"]==$myrow["is_admin"])			// always the admin is active
						{
							$is_active = 1;
						}
						if(!empty($is_active))
						{
							$auth_allow = 1;
						}
						else
						{
							$auth_allow = 3;		//$warning .= "<br />Your account is inactive. <br />Please contact the Eclass Admin<br />";
							$user = $myrow["user_id"];
						}
					}
					else
					{
						$auth_allow = 2;		//$tool_content .= "<br />The connection does not seem to work!<br />";
					}
					if($auth_allow==1)
					{
						$uid = $myrow["user_id"];
						$nom = $myrow["nom"];
						$prenom = $myrow["prenom"];
						$statut = $myrow["statut"];
						$email = $myrow["email"];
						$is_admin = $myrow["is_admin"];
						$userPerso = $myrow["perso"];//user perso flag
						$userLanguage = $myrow["lang"];//user preferred language
					}
					elseif($auth_allow==2)
					{
						// $tool_content .= "<br />The connection with the auth server does not seem to work!<br />";
						//$tool_content .= "<br />".$langNoConnection."<br />";
						continue;
					}
					elseif($auth_allow==3)
					{
						// $tool_content .= "<br />Your account is inactive. <br />Please <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">contact the Eclass Admin.</a><br /><br />";
						// $tool_content .= "<br />".$langAccountInactive1." <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">".$langAccountInactive2."</a><br /><br />";
						continue;
					}
					else
					{
						// $tool_content .= "CANNOT PROCEED<br />";
						$tool_content .= $langLoginFatalError."<br />";
						continue;
					}
				}
				else
				{
					$warning .= "<br>Invalid user auth method!Please contact the admin<br>";
				}

			}

		}
		else
		{
			$tool_content .= "<br>No authentication method defined.Cannot proceed!<br>";
		}


	}		// while

	if(empty($exists))
	{
		$auth_allow = 4;
	}

	if (!isset($uid))
	{
		switch($auth_allow)
		{
			case 1 : $warning .= ""; break;
			case 2 : $warning .= "<br />".$langNoConnection."<br />"; break;
			case 3 : $warning .= $tool_content .= "<br />".$langAccountInactive1." <a href=\"modules/auth/contactadmin.php?userid=".$user."\">".$langAccountInactive2."</a><br /><br />"; break;
			case 4 : $warning .= "<br />" . $langInvalidId . "<br />"; break;
			default: break;
		}
		//$warning .= $auth_allow . "---". $langInvalidId;
	}
	else
	{
		$warning = '';
		$log='yes';
		session_register('uid');
		session_register('nom');
		session_register('prenom');
		session_register('email');
		session_register('statut');
		session_register('is_admin');
		$_SESSION['uid'] = $uid;
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action)
                VALUES ('', '".$uid."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");

	}

	##[BEGIN personalisation modification]############
	//if user has activated the personalised interface
	//register a control session for it
	if ((@$userPerso == "yes") && session_is_registered("perso_is_active")) {
		session_register("user_perso_active");
	}
	##[END personalisation modification]############

	//check user language preferences
	if (isset($userLanguage) && $userLanguage == "en") {
		$_SESSION['langswitch'] = "english";
		$_SESSION['langLinkText'] = "Greek";
		$_SESSION['langLinkURL'] = "?localize=el";
	} elseif(isset($userLanguage) && $userLanguage == "el") {
		$_SESSION['langswitch'] = "greek";
		$_SESSION['langLinkText'] = "Αγγλικά";
		$_SESSION['langLinkURL'] = "?localize=en";
	}
	if(session_is_registered('langswitch')) {
		$language = $_SESSION['langswitch'];
	} else {
		$language = "greek";
	}

}  // end of user authentication


if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
else unset($uid);

//if the user logged in include the correct language files
//in case he has a different language set in his/her profile
if (isset($language)) {
	@include("./modules/lang/$language/index.inc");
	@include("./modules/lang/$language/trad4all.inc.php");
}
$nameTools = $langWelcomeToEclass;


//----------------------------------------------------------------
// if login succesful display courses lists
// --------------------------------------------------------------

if (isset($uid) AND !isset($logout)) {
	
	$require_help = true;
	$helpTopic="Clar2";

	if (!session_is_registered("user_perso_active")) {

		if (!check_guest()){
			//if the user is not a guest, load classic view
			include("logged_in_content.php");
			draw($tool_content,1);
		} else {
			//if the user is a guest send him straight to the corresponding lesson
			$guestSQL = db_query("SELECT `code_cours` FROM `cours_user` WHERE `user_id` = $uid", $mysqlMainDb);
			$sql_row = mysql_fetch_row($guestSQL);
			$dbname=$sql_row[0];
			session_register("dbname");
			header("location:".$urlServer."courses/$dbname/index.php");
		}
	} else {
		//load personalised view
		include("./modules/lang/$language/perso.inc.php");
		include("perso.php");
		drawPerso($tool_content);
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
		session_destroy();
	}

	$require_help = true;
	$helpTopic="Clar";
	include("logged_out_content.php");

	draw($tool_content, 0,'index');

} // end of display

// check for new announcements
function check_new_announce() {

	global $uid;

	$row = mysql_fetch_array(mysql_query("SELECT * FROM loginout WHERE id_user='$uid' AND action = 'LOGIN' ORDER BY idLog DESC"));
	$lastlogin = $row['when'];
	$sql = "SELECT * FROM annonces,cours_user
                WHERE annonces.code_cours=cours_user.code_cours
                AND cours_user.user_id='$uid' AND annonces.temps >= '$lastlogin'
                ORDER BY temps DESC";
	if (mysql_num_rows(mysql_query($sql)) > 0)
	return TRUE;
	else
	return FALSE;

}
?>
