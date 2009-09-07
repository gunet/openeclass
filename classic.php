<?PHP
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
	AND (cours_user.statut='5' OR cours_user.statut='10') ORDER BY cours.intitule, cours.titulaires");
if (mysql_num_rows($result2) > 0) {
    $tool_content .= "\n<table width=99%>";
    $tool_content .= "\n<tr>";
    $tool_content .= "\n  <td><b><font color=\"#a33033\">$langMyCoursesUser</font></b></td>";
    $tool_content .= "\n</tr>";
    $tool_content .= "\n</table>";

    $tool_content .= "\n<script type='text/javascript' src='modules/auth/sorttable.js'></script>
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<tr>
  <td>

  <table width='100%' align='center' class='sortable' id='t1'>
  <thead>
  <tr>
    <th class='left' colspan=\"2\" style=\"border: 1px solid #edecdf;\">$langCourseCode</th>
    <th width=\"150\" class='left' style=\"border: 1px solid #edecdf;\">$langTeacher</th>
    <th width=\"60\" style=\"border: 1px solid #edecdf;\">$langUnCourse</th>
  </tr>
  </thead>
  <tbody>";

$k = 0;
// display courses
while ($mycours = mysql_fetch_array($result2)) {
         $dbname = $mycours["k"];
         $status[$dbname] = $mycours["s"];
                if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
    $tool_content .= "\n      <td width=\"1\"><img src='${urlAppend}/template/classic/img/arrow_grey.gif' alt='* ' /></td>";
    $tool_content .= "\n      <td><a href='${urlServer}courses/$mycours[k]' class=CourseLink>$mycours[i]</a><font color='#a33033'> ($mycours[c])</font></td>";
    $tool_content .= "\n      <td><small>$mycours[t]</small></td>";
    $tool_content .= "\n      <td align='center'><a href='${urlServer}modules/unreguser/unregcours.php?cid=$mycours[c]&amp;u=$uid'><img src='template/classic/img/cunregister.gif' title='$langUnregCourse' /></a></td>";
    $tool_content .= "\n    </tr>";
    $k++;
         }
	$tool_content .= "\n    </tbody>\n    </table>\n";

	$tool_content .= "
  </td>
</tr>
</table>
<br />";

}  else  {
           if ($_SESSION['statut'] == '5')  // if we are login for first time
           $tool_content .= "
    <p>$langWelcomeStud</p>\n";
} // end of if (if we are student)

// second case check in which courses are registered as a professeror
     $result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s FROM cours, cours_user WHERE cours.code=cours_user.code_cours
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1' ORDER BY cours.intitule, cours.titulaires");
        if (mysql_num_rows($result2) > 0) {
    $tool_content .= "\n<table width=99%>";
    $tool_content .= "\n<tr>";
    $tool_content .= "\n  <td><b><font color=\"#a33033\">$langMyCoursesProf</font></b></td>";
    $tool_content .= "\n</tr>";
    $tool_content .= "\n</table>";
    $tool_content .= "
<script type='text/javascript' src='modules/auth/sorttable.js'></script>
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<tr>
  <td>

     <table width='100%' align='center' class='sortable' id='t1'>
     <thead>
     <tr>
       <th class='left' colspan=\"2\" style=\"border: 1px solid #edecdf;\">$langCourseCode</th>
       <th width=\"150\" class='left' style=\"border: 1px solid #edecdf;\">$langTeacher</th>
       <th width=\"60\" style=\"border: 1px solid #edecdf;\">$langManagement</th>
     </tr>
     </thead>
     <tbody>";
    $k = 0;
    while ($mycours = mysql_fetch_array($result2)) {
        $dbname = $mycours["k"];
        $status[$dbname] = $mycours["s"];
        if ($k%2==0) {
            $tool_content .= "\n     <tr>";
        } else {
            $tool_content .= "\n     <tr class=\"odd\">";
        }
        $tool_content .= "\n      <td width=\"1\"><img src='${urlAppend}/template/classic/img/arrow_grey.gif' title='* ' /></td>";
        $tool_content .= "\n      <td><a class='CourseLink' href='${urlServer}courses/$mycours[k]'>$mycours[i]</a><font color='#a33033'> ($mycours[c])</font></td>";
        $tool_content .= "\n      <td><small>$mycours[t]</small></td>";
        $tool_content .= "\n      <td align='center'><a href='${urlServer}modules/course_info/infocours.php?from_home=TRUE&cid=$mycours[c]'><img src='template/classic/img/referencement.gif' border=0 title='$langManagement' align='absbottom'></img></a></td>";
        $tool_content .= "\n    </tr>";
        $k++;
        }
	$tool_content .= "\n    </tbody>\n    </table>\n";

	$tool_content .= "
  </td>
</tr>
</table>
<br />";
}  else {
         if ($_SESSION['statut'] == '1')  // if we are loggin for first time
         $tool_content .= "
      <p>$langWelcomeProf</p>\n";
} // if

// get last login date
$last_login_query = "SELECT `when` FROM  `$mysqlMainDb`.loginout
	WHERE action = 'LOGIN' AND id_user = '$uid' ORDER BY `when` DESC LIMIT 1,1";

$row = mysql_fetch_row(db_query($last_login_query));
// cut the hour, minutes and seconds
$logindate = eregi_replace(" ", "-",substr($row[0],0,10));


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
		$result = db_query("SELECT temps FROM `$mysqlMainDb`.annonces, `$c[k]`.accueil
			WHERE code_cours='$c[k]'
			AND DATE_SUB(DATE_FORMAT('".$logindate."','%Y-%m-%d'), INTERVAL 10 DAY)
			<= DATE_FORMAT(temps,'%Y-%m-%d')
			AND `$c[k]`.accueil.visible =1
			AND `$c[k]`.accueil.id =7", $mysqlMainDb);
		if (mysql_num_rows($result) > 0) $found++;
	}
// if announcements found then display them
	if ($found > 0)  {
$tool_content .= "
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<tr>
  <td>

     <table width='100%' align='center'>
     <thead>
     <tr>
       <th style=\"border: 1px solid #edecdf;\" colspan=\"2\" class=\"left\">$langMyPersoAnnouncements</th>
     </tr>
     </thead>
     <tbody>";
		$ansql = db_query($sql, $mysqlMainDb);
        $la = 0;
		while ($c = mysql_fetch_array($ansql)) {
			$result = db_query("SELECT contenu, temps, title
			FROM `$mysqlMainDb`.annonces, `$c[k]`.accueil
			WHERE code_cours='$c[k]'
			AND DATE_SUB(DATE_FORMAT('".$logindate."','%Y-%m-%d'), INTERVAL 10 DAY)
			<= DATE_FORMAT(temps,'%Y-%m-%d')
			AND `$c[k]`.accueil.visible =1
			AND `$c[k]`.accueil.id =7
			ORDER BY temps DESC", $mysqlMainDb);


			while ($ann = mysql_fetch_array($result)) {
				$content = $ann['contenu'];
                		$content = make_clickable($content);
                		$content = nl2br($content);
				$content = mathfilter($content, 12, "courses/mathimg/");
				$row = mysql_fetch_array(db_query("SELECT intitule,titulaires
							FROM cours WHERE code='$c[k]'"));
                    if ($la%2==0) {
                        $tool_content .= "\n     <tr>";
                    } else {
                        $tool_content .= "\n     <tr class=\"odd\">";
                    }
                $tool_content .= "\n       <td width=\"1\" class=\"square_bullet2\">&nbsp;</td>";
                $tool_content .= "\n       <td><span class=\"announce_pos\"><b>".$ann['title']."</b> ".nice_format($ann['temps'])."&nbsp;&nbsp;&nbsp;&nbsp;($langCourse: <b>$c[t]</b> | $langTutor: <b>$row[titulaires]</b>)<br />".$content."           </span></td>";
                $tool_content .= "\n     </tr>";
            $la++;
			}
		}
		$tool_content .= "\n    </tbody>\n    </table>\n";
	   $tool_content .= "
  </td>
</tr>
</table>";
	}
}
	$tool_content .= "
  </td>
</tr>
</table>
<br />";
//$tool_content .= "\n    </table>\n    </div>";
session_register('status');
?>
