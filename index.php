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
@include('./include/config.php'); 
@include("./modules/lang/english/index.inc");
@include("./modules/lang/english/trad4all.inc.php");
@include("./modules/lang/$language/index.inc");
@include("./modules/lang/$language/trad4all.inc.php");
@include('./mainpage.inc.php');

header('Content-Type: text/html; charset='. $charset);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?
	if (isset($siteName)) echo "<title>".$siteName."</title>"; 
	else echo "<title>Εγκατάσταση του e-Class</title>";
?>
<meta http-equiv="Description" content="elearn Platform">
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?= $charset ?>">	
</head>

<body bgcolor="white">
<?

// first check
// check if we can connect to database. If not then probably it is the first time we install eclass

if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword)) {
	$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
	if (mysql_version()) mysql_query("SET NAMES greek");
	}
if (!isset($db)) {
	echo "
	<html>
	<head><title>e-Class</title></head>
	<body bgcolor='white'>
	<center>
	<table cellpadding='6' cellspacing='0' border='0' width='650' bgcolor='#E6E6E6'>
	<tr bgcolor='navy'><td valign='top' align='center'>
	<font color='white' face='arial, helvetica'>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης GUNet e-Class</font>
	</td></tr><tr><td>&nbsp;</td></tr>
	<tr bgcolor='#E6E6E6'>
	<td>
	<b>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί !</b>
	<p>Πιθανοί λόγοι: 
	<ul>
	<li>Χρησιμοποιείτε την πλατφόρμα για πρώτη φορά.<br> Σε αυτή την περίπτωση κάντε κλίκ στον 
	<a href=\"./install/\">Οδηγό Εγκατάστασης</a> για να ξεκινήσετε το πρόγραμμα 
	εγκατάστασης.</li>
	<li>Το αρχείο <tt>config.php</tt> δεν υπάρχει ή δεν μπορεί να διαβαστεί.</li>
	<li>Η MySQL δεν λειτουργεί (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
	</ul></p> 
	</td>
        </tr>
	</table>";
	exit();
}

// second check
// can we select database ? if not then there is some problem

if (isset($mysqlMainDb)) $selectResult = mysql_select_db($mysqlMainDb,$db); 
if (!isset($selectResult)) {
	echo "<html><head><title>e-Class</title></head>
	<body bgcolor='white'><center>
	<table cellpadding='6' cellspacing='0' border='0' width='650' bgcolor='#E6E6E6'>
        <tr bgcolor='navy'>
        <td valign='top' align='center'>
        <font color='white' face='arial, helvetica'>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης e-Class</font>
        </td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr bgcolor='#E6E6E6'><td>
        <b>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί !</b>
        <p>Πιθανοί λόγοι:
        <ul><li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
        <li>Υπάρχει πρόβλημα στις ρυθμίσεις του αρχείου <tt>config.php</tt></li></ul></p>
        </td>
        </tr>
        <tr bgcolor='#E6E6E6'>
        <td><p>Ένας πιθανός λόγος, επίσης, είναι ότι χρησιμοποιείτε την πλατφόρμα για πρώτη φορά.</p>
        Σε αυτή την περίπτωση κάντε κλίκ στον <a href=\"./install/\">Οδηγό Εγκατάστασης</a>
        για να ξεκινήσετε το πρόγραμμα εγκατάστασης.
        </td>
        </tr>
	</table>";
	exit();
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

?>
<table width="600" align="center" cellpadding="3" cellspacing="2" border="0">
<tr><td colspan="3" align="center" style="padding: 0px;" bgcolor="<?= $colorMedium ?>">
<?= $main_page_banner ?>
</td></tr>  
<tr><td valign="top" align="left"  bgcolor="<?= $colorMedium ?>" colspan="3">
<font face="Arial, Helvetica, sans-serif" color="#FFFFFF" size="2">&nbsp; 
<?
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
else unset($uid);

if (isset($uid)) echo "$langUser : $prenom $nom";
else echo "<a href=\"#\"></a>"; 

?>
</font></td></tr>
<tr>
<td valign="top" align="left" colspan="3">
<font size="1" face="arial, helvetica">
<b><font face="arial, helvetica" size="1"><?= $siteName ?></font></b>
</font><br><br></td></tr>
<?

//----------------------------------------------------------------
// if login succesful display courses lists 
// --------------------------------------------------------------


// first case check in which courses are registered as a student
if (isset($uid) AND !isset($logout)) { 
	echo '<tr valign="top"><td><table cellpadding="4" border="0" width="410" cellspacing="2">';
	$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
		FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
		AND (cours_user.statut='5' OR cours_user.statut='10')");
        if (mysql_num_rows($result2) > 0) {
		echo '<tr><td><font size=2 face="arial, helvetica"><b>'.$langMyCoursesUser.'</b></font></td></tr>';
		$i=0; 
		// SHOW COURSES
		while ($mycours = mysql_fetch_array($result2)) {
			$dbname = $mycours["k"];
			$status[$dbname] = $mycours["s"];
			if ($i%2==0) echo '<tr bgcolor="'.$color1.'">';
			elseif($i%2==1) echo '<tr bgcolor="'.$color2.'">';
			echo '<td><font size="2" face="arial, helvetica">
			<a href="courses/'.$mycours['k'].'/">'.$mycours['i'].'</a>
			<br>'.$mycours['t'].'<br>'.$mycours['c'].'</font>
			</td>
			</tr>';
			$i++; 
		}	// while 
	} // end of if
// second case check in which courses are registered as a professeror
	$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
        	FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
	if (mysql_num_rows($result2) > 0) {
	        echo '<tr valign="top"><td><font size=2 face="arial, helvetica"><b>'.$langMyCoursesProf.'</b></font>
               </td></tr>';
        	$i=0;
        	while ($mycours = mysql_fetch_array($result2)) {
                	$dbname = $mycours["k"];
                	$status[$dbname] = $mycours["s"];
                	if ($i%2==0) echo '<tr bgcolor="'.$color1.'">';
                	elseif($i%2==1) echo '<tr bgcolor="'.$color2.'">';
                        echo '<td><font size="2" face="arial, helvetica">
                        <a href="'.$urlServer."courses/".$mycours['k'].'/">'.$mycours['i'].'</a>
                        <br>'.$mycours['t'].'<br>'.$mycours['c'].'</font>
                        </td>
                        </tr>';
                	$i++;
        	}       // while
	} // if
	echo '</table></td>'; 
	session_register('status');
// --------------------------------------------------------------
// display right menu
// --------------------------------------------------------------

?>
	<td colspan="2" rowspan="2">
	<table border="0" cellpadding="4" cellspacing="2" width="170"> 
	<tr><td><font size="2" face="arial,helvetica"><b><?= $langMenu ?></b></font></td></tr>
<?
	// User is not currently in a course - set statut from main database
	$res2 = mysql_query("SELECT statut FROM user WHERE user_id = '$uid'");
	if ($row = mysql_fetch_row($res2)) $statut = $row[0];
	if ($statut==1) { 
?>
		<tr bgcolor="#E6E6E6"><td><font size="2" face="arial, helvetica">
		<a href="<?= $urlServer ?>modules/create_course/create_course.php">
		<?= $langCourseCreate ?></a></font></td></tr>
<?
	} 
	if (isset($is_admin) and $is_admin) { 
?>
		<tr bgcolor="#ffff99"><td><font size="2" face="arial, helvetica">
		<a href="modules/admin/"><?= $langAdminTool?></a></font></td></tr>
<?
	}
 	if ($statut != 10) {  
?>
		<tr bgcolor="#F5F5F5">
		<td><font size="2" face="arial, helvetica"><a href="modules/auth/courses.php">
		<?= $langOtherCourses ?></a>
		</font></td></tr>
		<tr bgcolor="#E6E6E6"><td>
		<font size="2" face="arial, helvetica"><a href="modules/agenda/myagenda.php"><?= $langMyAgenda ?></a></font>
		</td></tr>
		<tr bgcolor="#F5F5F5"><td>
              	<a href="modules/announcements/myannouncements.php">
		<font size="2" face="arial, helvetica">
		<?= $langMyAnnouncements ?></font>
<? // check for new announces
                if (check_new_announce())
                    echo "<font size=\"1\" face=\"arial, helvetica\" color=\"blue\"><img src='./images/nea.gif' border=0 align=center alt = '(".$langNewAnnounce.")'></font>";
?>
		</a></td></tr>
		<tr bgcolor="#E6E6E6"><td><font size="2" face="arial, helvetica">
		<a href="modules/profile/profile.php">
		<?= $langModifyProfile ?></a>
		</font></td></tr>
<?
	}
?>
	<tr bgcolor="#F5F5F5"><td>
	<a href="modules/help/help.php?topic=Clar2" 
		onClick="window.open('modules/help/help.php?topic=Clar2','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); 
		return false;">
	<font size="2" face="arial, helvetica"><?= $langHelp ?></font></a></td></tr>
	<tr bgcolor="#E6E6E6"><td>
	<font size="2" face="arial, helvetica"><a href="<?= $_SERVER['PHP_SELF']?>?logout=yes">
	<?= $langLogout ?></a></font></td></tr>
	
	</table>
	</td> </tr></table>
<?
}	// end of if login

// -------------------------------------------------------------------------------------
// display login  page 
// -------------------------------------------------------------------------------------

elseif ((isset($logout) && $logout) OR (1==1)) { 
	echo "<tr><td bgcolor='#004571' valign='top' width='170'>&nbsp;</td><td bgcolor='#E6E6E6'>&nbsp;</td></tr>";
	echo "<tr><td rowspan='2' bgcolor='#e6e6e6' valign='top' width='170'><br>";
	echo "<table>";
	echo "<tr><td><img src='images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>
        <a href='modules/auth/listfaculte.php'>$langListFaculte</a></font></td></tr>";
        echo "<tr><td><img src='./images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>";
	
	/* Check for LDAP server entries */
	$ldap_entries = mysql_fetch_array(mysql_query("SELECT ldapserver FROM institution"));
	if ($ldap_entries['ldapserver'] <> NULL) $newuser = "newuser_info.php";
	else $newuser = "newuser.php";
	// end of checking	
	echo " <a href='modules/auth/$newuser'>$langNewUser</a></font></td></tr>";
	echo "<tr><td><img src='images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>";
        echo " <a href='modules/auth/formprof.php'>$langProfReq</a></font></td></tr>";
        echo "<tr><td><img src='images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>
        <a href='./manuals/manual.php'>$langManuals</a></font></td></tr>";
	echo "<tr><td><img src='images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>
        <a href='info/about.php'>$langInfoPlat</a></font></td></tr>";
	echo "<tr><td style='padding-top: 40px;'><img src='./images/arrow.gif' width='4' height='8'><font face='arial, helvetica' size='2'>
        <a href='http://eclass.gunet.gr/teledu/index.htm' target=_blank>$langSupportForum</a></font></td></tr>";
	echo "<tr><td style='padding-bottom: 60px;'><img src='./images/arrow.gif' width='4' 
height='8'><font face='arial, helvetica' size='2'>
        <a href='info/contact.php'>$langContact</a></font></td></tr>";
	echo "</table>";        
        echo "</td>";
	if (isset($logout) && $logout && isset($uid)) {
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user,
			loginout.ip, loginout.when, loginout.action)
			VALUES ('', '$uid', '$REMOTE_ADDR', NOW(), 'LOGOUT')");
		unset($prenom);
		unset($nom);
		session_destroy();
	}

?>	
      <td bgcolor="#f5f5f5" valign="middle" width="430" style='padding-top: 20px;'> 
      <form action="<?= $_SERVER['PHP_SELF']?>" method="post">
        <p align="center"><font face="Tahoma, arial, helvetica" size="2"><?= $langUserName ?></font><br>
        <input style='width:150px; heigth:25px;' name="uname" size="20"><br>
        <font face="arial, helvetica" size="2"><?= $langPass ?></font><br>
        <input style='width:150px; height:25px;' name="pass" type="password" size="20"><br>
        <input value="<?= $langEnter ?>" name="submit" type="submit"><br>
	<font size="1"><?= $warning ?></font>
	<font size="2"><a href="modules/auth/lostpass.php"><?= $lang_forgot_pass?></a></font><br></p>
      </form>
      <div style="padding-top: 4px; padding-left: 20px; padding-right:20px; padding-bottom: 5px;">
      <?= $main_text  ?>
      </div>
      </td>

<?

	echo "</tr></table>";
} // end of display 


// display page footer
echo $main_page_footer;

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
</body> 
</html>
