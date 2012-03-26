<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/*===========================================================================
    stateclass.php
    @last update: 05-07-2006 by Pitsiougas Vagelis
    @authors list: Karatzidis Stratos <kstratos@uom.gr>
               Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Various Statistics

==============================================================================*/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

/*****************************************************************************
        general statistics
******************************************************************************/
$tool_content .= "<div id='operations_container'>
<ul id='opslist'>
<li><a href='platformStats.php?first='>".$langVisitsStats."</a></li>
<li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
<li><a href='oldStats.php'>".$langOldStats."</a></li>
<li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
</ul></div>";

// Actions
$tool_content .= "<table class='tbl_alt' width='100%'>
	<tr><th width='20'><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=login'>$langNbLogin</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=users'>$langUsers</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=percourse'>$langUsersPerCourse</a></td>
	</tr>
	<tr>
	<th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=cours'>$langStatCour</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td>
	<a href='$_SERVER[PHP_SELF]?stats=musers'>$langMultipleUsers</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=memail'>$langMultipleAddr e-mail</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=mlogins'>$langMultiplePairs LOGIN - PASS</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[PHP_SELF]?stats=vmusers'>$langMailVerification</a></td>
	</tr>
	</table>";

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
	switch ($_GET['stats']) {
		case 'login':
			mysql_select_db($mysqlMainDb);
			$result = db_query("SELECT code FROM cours");
			$course_codes = array();
			while ($row = mysql_fetch_assoc($result)) {
				$course_codes[] = $row['code'];
			}
			mysql_free_result($result);
	
			$first_date_time = time();
			$totalHits = 0;
		
			foreach ($course_codes as $course_code) {
				$sql = "SELECT COUNT(*) AS cnt FROM actions
                                        WHERE course_id = ". course_code_to_id($course_code);
				$result = db_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$totalHits += $row['cnt'];
				}
				mysql_free_result($result);
				
				$sql = "SELECT UNIX_TIMESTAMP(MIN(date_time)) AS first 
                                        FROM actions
                                        WHERE course_id = ". course_code_to_id($course_code);
				$result = db_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$tmp = $row['first'];
					if (!empty($tmp)) {
						if ($tmp < $first_date_time) {
							$first_date_time = $tmp;
						}
					}
				}
				mysql_free_result($result);
			}
			$uptime = date("d-m-Y", $first_date_time);
	
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
			<tr>
			<th colspan='2'>$langNbLogin</th>
			</tr>
			<tr>
			<td>$langFrom ".list_1Result("SELECT loginout.when FROM loginout ORDER BY loginout.when LIMIT 1")."</td>
			<td class='right' width='200'><b>".list_1Result("SELECT count(*) FROM loginout 
				WHERE loginout.action ='LOGIN'")."</b></td>
			</tr>
			<tr>
			<td>$langLast30Days</td>
			<td class='right'><b>".list_1Result("SELECT count(*) FROM loginout 
				WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></td>
			</tr>
			<tr>
			<td>$langLast7Days</td>
			<td class='right'><b>".list_1Result("SELECT count(*) FROM loginout 
				WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></td>
			</tr>
			<tr>
			<td>$langToday</td>
			<td class='right'><b>".list_1Result("SELECT count(*) FROM loginout 
				WHERE action ='LOGIN' AND (loginout.when > curdate())")."</b></td>
			</tr>
			<tr>
			<td>$langTotalHits</td>
			<td class='right'><b>$totalHits</b></td>
			</tr>
			<tr>
			<td>$langUptime</td>
			<td class='right'><b>$uptime</b></td>
			</tr>
			</table>";
		break;
		case 'users':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
                                <tr><th class='left' colspan='2'>$langUsers</th></tr>
                                <tr><td>$langNbProf</td>
                                    <td class='right' width='200'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE statut = 1;") .
                                        "</b></td></tr>
                                <tr><td>$langNbStudents</td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE statut = 5;") .
                                        "</b></td></tr>
                                <tr><td>$langNumGuest</td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE statut = 10;") .
                                        "</b></td></tr>
                                <tr><td>$langTotal</td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user;") .
                                        "</b></td></tr>
                                <tr><th class='left' colspan='2'>$langUserNotLogin</th></tr>
				<tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=no_login'>$langFrom ".list_1Result("SELECT loginout.when FROM loginout ORDER BY loginout.when LIMIT 1")."</a></td>
                                    <td class='right'><b>" .
					list_1Result("SELECT count(*) FROM `user` LEFT JOIN `loginout` ON `user`.`user_id` = `loginout`.`id_user` WHERE `loginout`.`id_user` IS NULL;") .
                                        "</b></td></tr>
                            </table>";
		break;
		case 'cours':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
			<tr>
			<th class='left' colspan='2'><b>$langCoursesHeader</b></th>
			</tr>
			<tr>
			<td class='left'>$langNumCourses</td>
			<td class='right'><b>".list_1Result("SELECT count(*) FROM cours")."</b></td>
			</tr>
			<tr>
			<th class='left' colspan='2'><b>$langNunEachAccess</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT visible, count(*) 
				FROM cours GROUP BY visible "))."
			<tr>
			<th class='left' colspan='2'><b>$langNumEachCourse</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT faculte.name AS faculte, count(*) 
				FROM cours, faculte WHERE cours.faculteid = faculte.id GROUP BY faculteid"))."
			<tr>
			<th class='left' colspan='2'><b>$langNumEachLang</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT languageCourse, count(*) FROM cours 
					GROUP BY languageCourse DESC"))."
			<tr>
			<th class='left' colspan='2'><b>$langNumEachCat</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT type, count(*) FROM cours 
					GROUP BY type"))."
			<tr>
			<th class='left' colspan='2'><b>$langAnnouncements</b></th>
			</tr>
			<tr>
			<td class='left'>$langNbAnnoucement</td>
			<td class='right'><b>".list_1Result("SELECT count(*) FROM announcements;")."</b></td>
			</tr>
			</table>";
		break;
		case 'musers':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>";
			$loginDouble = list_ManyResult("SELECT DISTINCT username, count(*) AS nb
				FROM user GROUP BY BINARY username HAVING nb > 1 ORDER BY nb DESC");
			$tool_content .= "<tr><th><b>$langMultipleUsers</b></th>
			<th class='right'><strong>$langResult</strong></th>
			</tr>";
			if (count($loginDouble) > 0) {
				$tool_content .= tablize($loginDouble);
				$tool_content .=  "<tr><td class='right' colspan='2'>".error_message()."</td></tr>";
			} else {
				$tool_content .= "<tr><td class='right' colspan='2'>".ok_message()."</td></tr>";
			}
			$tool_content .= "</table>";
		break;
		case 'percourse':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
			<tr><th class='left' colspan='2'><b>$langUsersPerCourse</b></th>";
			$result = db_query("SELECT cours_id, code, intitule, titulaires FROM cours ORDER BY intitule");
			while ($row = mysql_fetch_array($result)) {
				$result_numb = db_query("SELECT user.user_id, cours_user.statut FROM cours_user, user
					WHERE cours_id = $row[cours_id] AND cours_user.user_id = user.user_id", $mysqlMainDb);
				$teachers = $students = $visitors = 0;
				while ($numrows = mysql_fetch_array($result_numb)) {
					switch ($numrows['statut']) {
						case 1: $teachers++; break;
						case 5: $students++; break;
						case 10: $visitors++; break;
						default: break;
					}
					$cu_key = q("$row[intitule] ($row[code]) -- $row[titulaires]");
					$cu[$cu_key] = "<small>$teachers $langTeachers | $students $langStudents | $visitors $langGuests </small>";
				}
			}
			$tool_content .= "</tr>".tablize($cu)."</table>";
		break;
		case 'memail':
			$sqlLoginDouble = "SELECT DISTINCT email, COUNT(*) AS nb FROM user GROUP BY email 
				HAVING nb > 1 ORDER BY nb DESC";
			$loginDouble = list_ManyResult($sqlLoginDouble);
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
			<tr>
			<th><b>$langMultipleAddr e-mail</b></th>
			<th class='right'><strong>$langResult</strong></th>
			</tr>";
			if (count($loginDouble) > 0) {
				$tool_content .= tablize($loginDouble);
				$tool_content .= "<tr><td class=right colspan='2'>";
				$tool_content .= error_message();
				$tool_content .= "</td></tr>";
			} else {
				$tool_content .= "<tr><td class=right colspan='2'>";
				$tool_content .= ok_message();
				$tool_content .= "</td></tr>";
			}
			$tool_content .= "</table>";
		break;
		case 'mlogins':
			$sqlLoginDouble = "SELECT DISTINCT CONCAT(username, \" -- \", password) AS paire,
				COUNT(*) AS nb FROM user GROUP BY BINARY paire HAVING nb > 1 ORDER BY nb DESC";
			$loginDouble = list_ManyResult($sqlLoginDouble);
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
				<tr>
				<th><b>$langMultiplePairs LOGIN - PASS</b></th>
				<th class='right'><b>$langResult</b></th>
				</tr>";
			if (count($loginDouble) > 0) {
				$tool_content .=  tablize($loginDouble);
				$tool_content .= "<tr><td class='right' colspan='2'>";
				$tool_content .= error_message();
				$tool_content .= "</td></tr>";
			} else {
				$tool_content .= "<tr><td class='right' colspan='2'>";
				$tool_content .= ok_message();
				$tool_content .= "</td></tr>";
			}
			$tool_content .= "</table>";
		break;
		case 'vmusers':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>
                                <tr><th class='left' colspan='2'>$langUsers</th></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes&verified_mail=1'>$langMailVerificationYes</a></td>
                                    <td class='right' width='200'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE verified_mail = 1;") .
                                        "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE verified_mail = 2;") .
                                        "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user WHERE verified_mail = 0;") .
                                        "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes'>$langTotal</a></td>
                                    <td class='right'><b>" .
                                        list_1Result("SELECT count(*) FROM user;") .
                                        "</b></td></tr>
                            </table>";
		break;
		default:
		break;
	}
}

$tool_content .= "<br /><p class='right'><a href='index.php' class=mainpage>$langBackAdmin</a></p>";

/*
 * output a <table> with an array
 */

function tablize($table) {

	global $langClosed, $langTypesRegistration, $langOpen, $langPre, $langPost, $langOther, 
			$langEnglish, $langGreek, $langSpanish;

	$ret = "";
	if (is_array($table)) {
		while (list($key, $thevalue) = each($table)) {
			$ret .= "<tr>";
			switch ($key) {
				case '0': $key = $langClosed; break;
				case '1'; $key = $langTypesRegistration; break;
				case '2': $key = $langOpen; break;
				case 'pre': $key = $langPre; break;
				case 'post': $key = $langPost; break;
				case 'other': $key = $langOther; break;
				case 'greek': $key = $langGreek; break;
				case 'english': $key = $langEnglish; break;
				case 'spanish': $key = $langSpanish; break;
			}
			$ret .= "<td style='font-size: 90%'>".$key."</td>";
			$ret .= "<td class='right'><strong>".$thevalue."</strong></td></tr>";
		}
	}
	return $ret;
}

function ok_message() {
	global $langNotExist;

	return "<b><span style=\"color: #00FF00\">$langNotExist</span></b>";
}

function error_message() {
	global $langExist;

	return "<b><span style=\"color: #FF0000\">$langExist</span></b>";
}


function list_1Result($sql) {
	global $mysqlMainDb;

	$res = db_query($sql, $mysqlMainDb);
	$res = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql) {
	global $mysqlMainDb;
	$resu=array();

	$res = db_query($sql, $mysqlMainDb);
	while ($resA = mysql_fetch_array($res))
	{
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}

draw($tool_content, 3);