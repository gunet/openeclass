<?php session_start(); 

/*
+----------------------------------------------------------------------+
| e-class version 1.6                                                  |
| based on CLAROLINE version 1.3.0 $Revision$		     |
+----------------------------------------------------------------------+
|   $Id$
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
| Copyright (c) 2003 GUNet                                             |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or      |
|   modify it under the terms of the GNU General Public License        |
|   as published by the Free Software Foundation; either version 2     |
|   of the License, or (at your option) any later version.             |
|                                                                      |
|   This program is distributed in the hope that it will be useful,    |
|   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
|   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
|   GNU General Public License for more details.                       |
|                                                                      |
|   You should have received a copy of the GNU General Public License  |
|   along with this program; if not, write to the Free Software        |
|   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
|   02111-1307, USA. The GNU GPL license is also available through     |
|   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
+----------------------------------------------------------------------+
| Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
|          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
|          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
|                                                                      |
| e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
|                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
|                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
+----------------------------------------------------------------------+

/***************************************************************
*               HOME PAGE OF ECLASS		               *
****************************************************************
*/

//unset($language);
//$language = "english";

$path2add=0;
include("include/baseTheme.php");
@include("./modules/lang/english/index.inc");
@include("./modules/lang/english/trad4all.inc.php");
@include("./modules/lang/$language/index.inc");
@include("./modules/lang/$language/trad4all.inc.php");

//@include("./include/lib/main.lib.php");
@include("./modules/auth/auth.inc.php");
//
//$require_help = true;
//$helpTopic="Clar2";
$nameTools = $langWelcomeToEclass;//Put it in a lang file
//$homePage is used by baseTheme.php
//to parse correctly the breadcrumb.
$homePage = true;
//@include('./mainpage.inc.php');
//notify base theme that we are not in a module to fix relative paths
//$pathOverride = true;
//$_SESSION['pathOverride'] = $pathOverride;
//$relativePath = "";
//header('Content-Type: text/html; charset='. $charset);
//require './include/baseTheme.php';
$tool_content = "";
//Flag to modify the prefix for relative paths.(used by init.php)


//This will be setting a var in the template and NOT concat $tool_content!
//	if (isset($siteName)) $tool_content .=  "<title>".$siteName."</title>";
//	else $tool_content .= "<title>Εγκατάσταση του e-Class</title>";

//<!--<meta http-equiv="Description" content="elearn Platform">
//<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=$charset">
//</head>
//
//<body bgcolor="white">-->


// first check
// check if we can connect to database. If not then probably it is the first time we install eclass

if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword)) {
	$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
	if (mysql_version()) mysql_query("SET NAMES greek");
}
if (!isset($db)) {
	include ("not_installed.php");
}

// second check
// can we select database ? if not then there is some problem

if (isset($mysqlMainDb)) $selectResult = mysql_select_db($mysqlMainDb,$db);
if (!isset($selectResult))
{
	include("general_error.php");
}

// unset system that records visitor only once by course for statistics
unset($alreadyHome);
unset($dbname);

	$whatViewToLoad = "yes";
	if ($whatViewToLoad == "yes") session_register("perso_is_active");
// ------------------------------------------------------------------------
// if we try to login...
// then authenticate user. First via LDAP then via MyQL
// -----------------------------------------------------------------------
$warning = '';
$uname = isset($_POST['uname'])?$_POST['uname']:'';
$pass = isset($_POST['pass'])?$_POST['pass']:'';
$submit = isset($_POST['submit'])?$_POST['submit']:'';
$auth = get_auth_id();

if(!empty($submit))
{
	unset($uid);
	$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, inst_id, iduser is_admin, perso
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username='".$uname."'";
	$result=mysql_query($sqlLogin);
	$check_passwords = array("pop3","imap","ldap","db");
	$warning = "";
	while ($myrow = mysql_fetch_array($result))
	{

		if(!empty($auth))
		{
			if(!in_array($myrow["password"],$check_passwords))
			{
				// try to authenticate him via eclass
				if (($uname == $myrow["username"]) and ($pass == $myrow["password"]))
				{
					// check if his/her account is active
					$is_active = check_activity($myrow["user_id"]);
					if($myrow["user_id"]==$myrow["is_admin"])
					{
						$is_active = 1;
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
					}
					else
					{
						$warning .= "<br />Your account is inactive. <br />Please <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">contact the Eclass Admin.</a><br /><br />";
					}
				}
			}
			else
			{
				// try to authenticate him via the alternative defined method
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
						// check if the account is active
						$is_active = check_activity($myrow["user_id"]);

						// always the admin is active
						if($myrow["user_id"]==$myrow["is_admin"])
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
							//$warning .= "<br />Your account is inactive. <br />Please contact the Eclass Admin<br />";
						}
					}
					else
					{
						//$tool_content .= "<br />The connection does not seem to work!<br />";
						$auth_allow = 2;
					}
					if($auth_allow==1)
					{
						$uid = $myrow["user_id"];
						$nom = $myrow["nom"];
						$prenom = $myrow["prenom"];
						$statut = $myrow["statut"];
						$email = $myrow["email"];
						$is_admin = $myrow["is_admin"];
					}
					elseif($auth_allow==2)
					{
						$tool_content .= "<br />The connection with the auth server does not seem to work!<br />";
					}
					elseif($auth_allow==3)
					{
						$tool_content .= "<br />Your account is inactive. <br />Please <a href=\"modules/auth/contactadmin.php?userid=".$myrow["user_id"]."\">contact the Eclass Admin.</a><br /><br />";
					}
					else
					{
						$tool_content .= "CANNOT PROCEED<br />";
					}
				}
				else
				{
					$warning .= "<br>Invalid user auth method!Please contact the admin<br>";
					//exit;
				}

			}

		}
		else
		{
			$tool_content .= "<br>No authentication method defined.Cannot proceed!<br>";
			//exit;

		}


	}		// while

	if (!isset($uid))
	{
		$warning .= $langInvalidId;
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
	
	if (($userPerso == "yes") && session_is_registered("perso_is_active")) 
	{
		session_register("user_perso_active");
	}
	##[END personalisation modification]############
}  // end of user authentication


// -------------------------------------------------------------

//$tool_content .= <<<tCont
//<table width="600" align="center" cellpadding="3" cellspacing="2" border="0">
//<tr><td colspan="3" align="center" style="padding: 0px;" bgcolor="$colorMedium">
//$main_page_banner
//</td></tr>
//<tr><td valign="top" align="left"  bgcolor="$colorMedium" colspan="3">
//<font face="Arial, Helvetica, sans-serif" color="#FFFFFF" size="2">&nbsp;
//tCont;

//echo $tool_content;
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
else unset($uid);

// This must be setting a var in basethem and NOT concat $tool_content
//if (isset($uid)) $tool_content .=  "$langUser : $prenom $nom";
//else $tool_content .=  "<a href=\"#\"></a>";

//$tool_content .= <<<tCont2
//</font></td></tr>
//<tr>
//<td valign="top" align="left" colspan="3">
//<font size="1" face="arial, helvetica">
//<b><font face="arial, helvetica" size="1">$siteName</font></b>
//</font><br><br></td></tr>
//tCont2;

//echo $tool_content ;
//----------------------------------------------------------------
// if login succesful display courses lists
// --------------------------------------------------------------

// first case check in which courses are registered as a student
if (isset($uid) AND !isset($logout)) {

$require_help = true;
$helpTopic="Clar2";
	//$eclass_perso will be read from the db.
	//keep as is for now
//	$whatViewToLoad = "yes";
//	if ($whatViewToLoad == "yes") session_register("perso_is_active");
	
	$eclass_perso = 0;
	if (!session_is_registered("user_perso_active")) {
		include("logged_in_content.php");
		draw($tool_content,1);
	} else {
		include("perso.php");
		drawPerso($tool_content);
	}



}	// end of if login

// -------------------------------------------------------------------------------------
// display login  page
// -------------------------------------------------------------------------------------

elseif ((isset($logout) && $logout) OR (1==1)) {

	if (isset($logout) && $logout && isset($uid)) {
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user,
			loginout.ip, loginout.when, loginout.action)
			VALUES ('', '$uid', '$REMOTE_ADDR', NOW(), 'LOGOUT')");
		unset($prenom);
		unset($nom);
		session_destroy();
	}

	include("logged_out_content.php");


	draw($tool_content, 0,'index');
	//	$tool_content .=  "</tr></table>";
} // end of display



// display page footer
//echo $main_page_footer;

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

// -----------------------------------------------------------------------------------
// checking the mysql version
// note version_compare() is used for checking the php version but works for mysql too
// ------------------------------------------------------------------------------------

/*function mysql_version() {

$ver = mysql_get_server_info();
if (version_compare("4.1", $ver) <= 0)
return true;
else
return false;
}*/


?>
<!--</body> 
</html>
-->