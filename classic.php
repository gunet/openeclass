<?PHP
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

/*
 * Logged In Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content of the start page when the
 * user is logged in
 *
 */

include("./include/lib/textLib.inc.php");
include("./include/phpmathpublisher/mathpublisher.php");

$tool_content .= " ";
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c,
	cours.intitule i, cours.titulaires t, cours_user.statut s
	FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
	AND (cours_user.statut='5' OR cours_user.statut='10')");
if (mysql_num_rows($result2) > 0) {
	$tool_content .= "<table width=99% align='center' class='CourseListTitle'>
     	<tr><th><b>$langMyCoursesUser</b></th></tr>
     	</table>
     <script type='text/javascript' src='modules/auth/sorttable.js'></script>
     <table width='99%' align='center' class='sortable' id='t1'>";
	$tool_content .= "<thead><tr>
       <th width='65%' class='left'>$langCourseCode</th>
       <th width='30%' class='left'>$langTeacher</th>
       <th width='5%'>$langUnCourse</th>
     </tr>
     </thead>";

// display courses
while ($mycours = mysql_fetch_array($result2)) {
         $dbname = $mycours["k"];
         $status[$dbname] = $mycours["s"];
         $tool_content .= "
     <tr onMouseOver=\"this.style.backgroundColor='#fbfbfb'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
	$tool_content .= "
       <td>
       &nbsp;<img src='images/arrow_blue.gif'>&nbsp;
       <a href='${urlServer}courses/$mycours[k]' class=CourseLink>$mycours[i]</a>
       <font color='#a9a9a9'> ($mycours[c]) </font>
       </td>
       <td>$mycours[t]</td>
       <td align=center><a href='${urlServer}modules/unreguser/unregcours.php?cid=$mycours[c]&u=$uid'>
       <img src='template/classic/img/cunregister.gif' border='0' title='$langUnregCourse'></a>
       </td>
     </tr>";
         }
	$tool_content .= "</table><br/>";

}  else  {
           if ($_SESSION['statut'] == '5')  // if we are login for first time
           $tool_content .= "
    <p>$langWelcomeStud</p>\n";
} // end of if (if we are student)

// second case check in which courses are registered as a professeror
     $result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s FROM cours, cours_user WHERE cours.code=cours_user.code_cours
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
        if (mysql_num_rows($result2) > 0) {
     $tool_content .= "
     <table width=99% align='center' class='CourseListTitle'>
     <tr>
       <th><b>$langMyCoursesProf</b></th>
     </tr>
     </table>

     <script type='text/javascript' src='modules/auth/sorttable.js'></script>
     <table width='99%' align='center' class='sortable' id='t1'>
     <thead>
     <tr>
       <th width='65%' class='left'>$langCourseCode</th>
       <th width='30%' class='left'>$langTeacher</th>
       <th width='5%'>$langManagement</th>
     </tr>
     </thead>";

while ($mycours = mysql_fetch_array($result2)) {
             $dbname = $mycours["k"];
             $status[$dbname] = $mycours["s"];
        $tool_content .= "
     <tr onMouseOver=\"this.style.backgroundColor='#fbfbfb'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
        $tool_content .= "
       <td>&nbsp;<img src='images/arrow_blue.gif'>&nbsp;<a class='CourseLink' href='${urlServer}courses/$mycours[k]'>$mycours[i]</a><font color=#a9a9a9> ($mycours[c])</font></td>
       <td>$mycours[t]</span></td>
       <td align=center valign=middle>
       <a href='${urlServer}modules/course_info/infocours.php?from_home=TRUE&cid=$mycours[c]'>
       <img src='template/classic/img/referencement.gif' border=0 title='$langManagement' align='absbottom'></img></a>
       </td>
     </tr>";
        }
	$tool_content .= '</table>';
}  else {
         if ($_SESSION['statut'] == '1')  // if we are loggin for first time
         $tool_content .= "
      <p>$langWelcomeProf</p>\n";
} // if

// get last login date
$last_login_query = "SELECT `when` FROM  $mysqlMainDb.loginout
	WHERE action = 'LOGIN' AND id_user = '$uid' ORDER BY `when` DESC LIMIT 1,1";

$row = mysql_fetch_row(db_query($last_login_query));
// cut the hour, minutes and seconds
$logindate = eregi_replace(" ", "-",substr($row[0],0,10));

$tool_content .= "<table width='100%'>";

// get registered courses
$sql = "SELECT cours.code k, cours.fake_code c, cours.intitule t,
	cours.intitule i, cours_user.statut s
	FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
	 AND (cours_user.statut='5' OR cours_user.statut='1')";

if (mysql_num_rows(db_query($sql, $mysqlMainDb)) > 0) {
// display last week announcements
	$found = 0;
	$ansql = db_query($sql, $mysqlMainDb);
	while ($c = mysql_fetch_array($ansql)) {
		$result = db_query("SELECT temps FROM `$mysqlMainDb`.annonces, $c[k].accueil
			WHERE code_cours='$c[k]'
			AND DATE_SUB(DATE_FORMAT('".$logindate."','%Y-%m-%d'), INTERVAL 10 DAY) 
			<= DATE_FORMAT(temps,'%Y-%m-%d')
			AND $c[k].accueil.visible =1
			AND $c[k].accueil.id =7", $mysqlMainDb);
		if (mysql_num_rows($result) > 0) $found++;	  
	} 
// if announcements found then display them
	if ($found > 0)  {
		$tool_content .= "<tr><th>$langMyPersoAnnouncements</th></tr>";
		$ansql = db_query($sql, $mysqlMainDb);
		while ($c = mysql_fetch_array($ansql)) {
			$result = db_query("SELECT contenu, temps, title 
			FROM $mysqlMainDb.annonces, $c[k].accueil
			WHERE code_cours='$c[k]'
			AND DATE_SUB(DATE_FORMAT('".$logindate."','%Y-%m-%d'), INTERVAL 10 DAY)
			<= DATE_FORMAT(temps,'%Y-%m-%d')
			AND $c[k].accueil.visible =1
			AND $c[k].accueil.id =7
			ORDER BY temps DESC", $mysqlMainDb);
	
			while ($ann = mysql_fetch_array($result)) {
				$content = $ann['contenu'];
                		$content = make_clickable($content);
                		$content = nl2br($content);
				$content = mathfilter($content, 12, "../../include/phpmathpublisher/img/");
				$row = mysql_fetch_array(db_query("SELECT intitule,titulaires 
							FROM cours WHERE code='$c[k]'"));
				$tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#fbfbfb'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
                		$tool_content .= "<td>&nbsp;<img src='images/arrow_blue.gif'>&nbsp;$c[t]:&nbsp;($langTutor: <b>$row[titulaires]</b>)<br>$ann[title]<br>$content<br>
			<small><i>($langAnn: ".nice_format($ann['temps']).")</i></small></td></tr>";
			}
		}
	}
}

$tool_content .= "</table>";
session_register('status');
?>
