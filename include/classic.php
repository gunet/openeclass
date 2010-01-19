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

if (!defined('INDEX_START')) {
	die("Action not allowed!");
}

include("./include/lib/textLib.inc.php");
include("./include/phpmathpublisher/mathpublisher.php");

function cours_table_header($statut)
{
        global $langCourseCode, $langMyCoursesProf, $langMyCoursesUser, $langCourseCode,
               $langTeacher, $langManagement, $langUnregCourse, $tool_content;

        if ($statut == 1) {
                $legend = $langMyCoursesProf;
                $manage = $langManagement;
        } elseif ($statut == 5) {
                $legend = $langMyCoursesUser;
                $manage = $langUnregCourse;
        } else {
                $legend = "(? $statut ?)";
        }

	$tool_content .= "<p><b><font color='#a33033'>$legend</font></b></p>
	                 <script type='text/javascript' src='modules/auth/sorttable.js'></script>
                         <table style='border: 1px solid #edecdf;' width='99%'>
                         <tr><td>
                         <table width='100%' align='center' class='sortable' id='t1'>
                         <thead><tr>
                         <th class='left' colspan='2' style='border: 1px solid #edecdf;'>$langCourseCode</th>
                         <th width='150' class='left' style='border: 1px solid #edecdf;'>$langTeacher</th>
                         <th width='60' style='border: 1px solid #edecdf;'>$manage</th>
                         </tr></thead><tbody>";
}

function cours_table_end()
{
        $GLOBALS['tool_content'] .= "</tbody></table></td></tr></table><br />";
}

$tool_content .= " ";
$result2 = mysql_query("
        SELECT cours.code code, cours.fake_code fake_code,
               cours.intitule title, cours.titulaires profs, cours_user.statut statut
	FROM cours LEFT JOIN cours_user ON cours.cours_id = cours_user.cours_id
        WHERE cours_user.user_id = $uid
        ORDER BY statut, cours.intitule, cours.titulaires");
if (mysql_num_rows($result2) > 0) {
	$k = 0;
        $statut = 0;
	// display courses
	while ($mycours = mysql_fetch_array($result2)) {
                $old_statut = $statut;
                $statut = $mycours['statut'];
                if ($k == 0 or $old_statut <> $statut) {
                        if ($k > 0) {
                                cours_table_end();
                        }
                        cours_table_header($statut);
                }
		$code = $mycours['code'];
                $title = $mycours['title'];
		$status[$code] = $mycours['statut'];
		if ($k%2==0) {
			$tool_content .= "<tr>";
		} else {
			$tool_content .= "<tr class='odd'>";
		}
                if ($statut == 1) {
                        $manage_link = "${urlServer}modules/unreguser/unregcours.php?cid=$code&amp;u=$uid";
                        $manage_icon = 'template/classic/img/cunregister.gif';
                        $manage_title = $langManagement;
                } else {
                        $manage_link = "${urlServer}modules/unreguser/unregcours.php?cid=$code&amp;u=$uid";
                        $manage_icon = 'template/classic/img/cunregister.gif';
                        $manage_title = $langUnregCourse;
                }
		$tool_content .="<td width='1'><img src='${urlAppend}/template/classic/img/arrow_grey.gif' title='* ' /></td>";
		$tool_content .= "\n<td><a href='${urlServer}courses/$code' class='CourseLink'>$title</a>
			<font color='#a33033'> ($mycours[fake_code]</font></td>";
		$tool_content .= "\n<td><small>$mycours[profs]</small></td>";
		$tool_content .= "\n<td align='center'>
			<a href='$manage_link'><img src='$manage_icon' title='$manage_title' /></a></td>";
		$tool_content .= "\n    </tr>";
		$k++;
	}
        cours_table_end();
}  elseif ($_SESSION['statut'] == '5') {
        // if are loging in for the first time as student...
	$tool_content .= "<p>$langWelcomeStud</p>\n";
}  elseif ($_SESSION['statut'] == '1') {
        // ...or as professor
        $tool_content .= "<p>$langWelcomeProf</p>\n";
}

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
	// if announcements found display them
	if ($found > 0)  {
		$tool_content .= "<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
		<tr><td>
		<table width='100%' align='center'>
		<thead><tr>
		<th style=\"border: 1px solid #edecdf;\" colspan=\"2\" class=\"left\">$langMyPersoAnnouncements</th>
		</tr></thead><tbody>";
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
				if ($la%2 == 0) {
					$tool_content .= "\n<tr>";
				} else {
					$tool_content .= "\n<tr class=\"odd\">";
				}
				$tool_content .= "\n<td width='1' class='square_bullet2'>&nbsp;</td>";
				$tool_content .= "\n<td><span class=\"announce_pos\"><b>".$ann['title']."</b> ".nice_format($ann['temps'])."&nbsp;&nbsp;&nbsp;&nbsp;($langCourse: <b>$c[t]</b> | $langTutor: <b>$row[titulaires]</b>)<br />".$content."</span></td>";
				$tool_content .= "\n</tr>";
				$la++;
			}
		}
		$tool_content .= "\n</tbody>\n</table>\n";
		$tool_content .= "</td></tr></table>";
	}
}
$tool_content .= "</td></tr></table><br />";
if (isset($status)) {
	$_SESSION['status'] = $status;
}

?>
