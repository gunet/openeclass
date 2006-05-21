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

unset($language);
//@include('./config/config.php'); 
@include("./modules/lang/english/index.inc");
@include("./modules/lang/english/trad4all.inc.php");
@include("./modules/lang/$language/index.inc");
@include("./modules/lang/$language/trad4all.inc.php");

$require_help = true;
$helpTopic="Clar2";
$nameTools = "Καλωσορίσατε στο e-Class!";
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

//Flag to modify the prefix for relative paths.(used by init.php)
$path2add=0;
include("include/baseTheme.php");
//echo "hm2";

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
if (!isset($selectResult)) {
include("general_error.php");
}

// unset system that records visitor only once by course for statistics
unset($alreadyHome);
unset($dbname); 

// ------------------------------------------------------------------------
// if we try to login...
// then authenticate user. First via LDAP then via MyQL
// -----------------------------------------------------------------------
$warning = '';
if (isset($submit) && $submit) {
        unset($uid);
        $sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, inst_id, iduser is_admin
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username='$_POST[uname]'";
        $result=mysql_query($sqlLogin);
        while ($myrow = mysql_fetch_array($result)) {
                if ($myrow["inst_id"] == 0) {           // If user is not authenticated via LDAP...
                                                        // ...get account details from db.
                        if (($_POST["uname"] == $myrow["username"]) and ($_POST["pass"] == $myrow["password"])) {
                                $uid = $myrow["user_id"];
                                $nom = $myrow["nom"];
                                $prenom = $myrow["prenom"];
                                $statut = $myrow["statut"];
                                $email = $myrow["email"];
                                $is_admin = $myrow["is_admin"];
                        }
                } elseif (!empty($_POST["pass"])) {    // If user auth is via LDAP...
                        $findserver = "SELECT ldapserver, basedn FROM institution
                                       WHERE inst_id = ".$myrow["inst_id"];
                        $ldapresult = mysql_query($findserver);
                        while ($myrow1 = mysql_fetch_array($ldapresult)) {
                                $ds = ldap_connect($myrow1["ldapserver"]);  //get the ldapServer, baseDN from the db
                                if ($ds) {
                                        $r=@ldap_bind($ds);     // this is an "anonymous" bind
                                        if ($r) {
                                                $mailadd = ldap_search($ds, $myrow1["basedn"], "mail=".$_POST["uname"]);
                                                $info = ldap_get_entries($ds, $mailadd);
                                                if ($info["count"] == 1) {       // user found
                                                        $authbind = @ldap_bind($ds, $info[0]["dn"], $_POST["pass"]);
                                                        if ($authbind) {
                                                                $uid = $myrow["user_id"];
                                                                $nom = $myrow["nom"];
                                                                $prenom = $myrow["prenom"];
                                                                $statut = $myrow["statut"];
                                                                $email = $myrow["email"];
                                                                $is_admin = $myrow["is_admin"];
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }

       if (!isset($uid)) {
                $warning = $langInvalidId;
	} else {
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
if (isset($uid)) $tool_content .=  "$langUser : $prenom $nom";
else $tool_content .=  "<a href=\"#\"></a>"; 

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
	
include("logged_in_content.php");

draw($tool_content,1);

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

function mysql_version() {

$ver = mysql_get_server_info();
if (version_compare("4.1", $ver) <= 0)
        return true;
else
	return false;
}


?>
<!--</body> 
</html>
-->