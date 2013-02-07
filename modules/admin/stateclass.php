<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
/**
 * 
    @file.stateclass.php    
    @authors list: Karatzidis Stratos <kstratos@uom.gr>
                   Pitsiougas Vagelis <vagpits@uom.gr>
    @description: Various Statistics
==============================================================================*/

$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/admin.inc.php';
require_once 'include/log.php';
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
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=login'>$langNbLogin</a></td>
	</tr>
        <tr><th width='20'><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=failurelogin'>$langLoginFailures</a> <small>($langLast15Days)</small></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=users'>$langUsers</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=percourse'>$langUsersPerCourse</a></td>
	</tr>
	<tr>
	<th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=cours'>$langStatCour</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td>
	<a href='$_SERVER[SCRIPT_NAME]?stats=musers'>$langMultipleUsers</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=memail'>$langMultipleAddr e-mail</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=mlogins'>$langMultiplePairs LOGIN - PASS</a></td>
	</tr>
	<tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=vmusers'>$langMailVerification</a></td>
	</tr>
         <tr><th><img src='$themeimg/arrow.png' alt=''></th>
	<td><a href='$_SERVER[SCRIPT_NAME]?stats=unregusers'>$langUnregUsers</a>  <small>($langLastMonth)</small></td></td>
	</tr>
	</table>";

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {        
	switch ($_GET['stats']) {
                case 'failurelogin':
                        $limit = isset($_GET['limit'])?$_GET['limit']:0;
                        $tool_content .= "<br />";
                        $date_start = date("Y-m-d", strtotime("-15 days"));
                        $date_end = date("Y-m-d", strtotime("+1 days"));
                        $page_link = "&amp;stats=failurelogin";
                        $log = new Log();
                        $log->display(0, 0, 0, LOG_LOGIN_FAILURE, $date_start, $date_end, $_SERVER['PHP_SELF'], $limit, $page_link);
                break;
                case 'unregusers':
                        $limit = isset($_GET['limit'])?$_GET['limit']:0;
                        $tool_content .= "<br />";
                        $date_start = date("Y-m-d", strtotime("-1 month"));                       
                        $date_end = date("Y-m-d", strtotime("+1 days"));                        
                        $page_link = "&amp;stats=unregusers";
                        $log = new Log();
                        $log->display(0, -1, 0, LOG_DELETE_USER, $date_start, $date_end, $_SERVER['PHP_SELF'], $limit, $page_link);
                break;
		case 'login':			
			$result = db_query("SELECT code FROM course");
			$course_codes = array();
			while ($row = mysql_fetch_assoc($result)) {
				$course_codes[] = $row['code'];
			}
			mysql_free_result($result);

			$first_date_time = time();
			$totalHits = 0;

			foreach ($course_codes as $course_code) {
				$sql = "SELECT SUM(hits) AS cnt FROM actions_daily
                                        WHERE course_id = ". course_code_to_id($course_code);
				$result = db_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$totalHits += $row['cnt'];
				}
				mysql_free_result($result);

				$sql = "SELECT UNIX_TIMESTAMP(MIN(day)) AS first
                                        FROM actions_daily
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
			<td>$langFrom ".db_query_get_single_value("SELECT loginout.when FROM loginout ORDER BY loginout.when LIMIT 1")."</td>
			<td class='right' width='200'><b>".db_query_get_single_value("SELECT COUNT(*) FROM loginout
				WHERE loginout.action ='LOGIN'")."</b></td>
			</tr>
			<tr>
			<td>$langLast30Days</td>
			<td class='right'><b>".db_query_get_single_value("SELECT COUNT(*) FROM loginout
				WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></td>
			</tr>
			<tr>
			<td>$langLast7Days</td>
			<td class='right'><b>".db_query_get_single_value("SELECT COUNT(*) FROM loginout
				WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></td>
			</tr>
			<tr>
			<td>$langToday</td>
			<td class='right'><b>".db_query_get_single_value("SELECT COUNT(*) FROM loginout
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
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE statut = ".USER_TEACHER.";") .
                                        "</b></td></tr>
                                <tr><td>$langNbStudents</td>
                                    <td class='right'><b>" .
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE statut = ".USER_STUDENT.";") .
                                        "</b></td></tr>
                                <tr><td>$langNumGuest</td>
                                    <td class='right'><b>" .
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE statut = ".USER_GUEST.";") .
                                        "</b></td></tr>
                                <tr><td>$langTotal</td>
                                    <td class='right'><b>" .
                                        db_query_get_single_value("SELECT COUNT(*) FROM user;") .
                                        "</b></td></tr>
                                <tr><th class='left' colspan='2'>$langUserNotLogin</th></tr>
				<tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=no_login'>$langFrom ".db_query_get_single_value("SELECT loginout.when FROM loginout ORDER BY loginout.when LIMIT 1")."</a></td>
                                    <td class='right'><b>" .
					db_query_get_single_value("SELECT COUNT(*) FROM `user` LEFT JOIN `loginout` ON `user`.`user_id` = `loginout`.`id_user` WHERE `loginout`.`id_user` IS NULL;") .
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
			<td class='right'><b>".db_query_get_single_value("SELECT COUNT(*) FROM course")."</b></td>
			</tr>
			<tr>
			<th class='left' colspan='2'><b>$langNunEachAccess</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT visible, COUNT(*)
				FROM course GROUP BY visible "))."
			<tr>
			<th class='left' colspan='2'><b>$langNumEachCourse</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT hierarchy.name AS faculte, COUNT(*)
				FROM course, course_department, hierarchy
                                WHERE course.id = course_department.course
                                  AND hierarchy.id = course_department.department GROUP BY hierarchy.id"))."
			<tr>
			<th class='left' colspan='2'><b>$langNumEachLang</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT lang, COUNT(*) FROM course
					GROUP BY lang DESC"))."			
			</tr>			
			</table>";
                        /* // list courses per type -- TODO query must be updated
                         <tr>
			<th class='left' colspan='2'><b>$langNumEachCat</b></th>
			</tr>".tablize(list_ManyResult("SELECT DISTINCT type, COUNT(*) FROM course
					GROUP BY type"))."
			<tr> */
		break;
		case 'musers':
			$tool_content .= "<table width='100%' class='tbl_1' style='margin-top: 20px;'>";
			$loginDouble = list_ManyResult("SELECT DISTINCT username, COUNT(*) AS nb
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
                        $teachers = $students = $visitors = 0;
			$result = db_query("SELECT id, code, title, prof_names FROM course ORDER BY title");
			while ($row = mysql_fetch_array($result)) {
				$result_numb = db_query("SELECT user.user_id, course_user.statut FROM course_user, user, course
                                                        WHERE course.id = $row[id] 
                                                        AND course_user.user_id = user.user_id");                                
                                $cu_key = q("$row[title] ($row[code]) -- $row[prof_names]");                                
				while ($numrows = mysql_fetch_array($result_numb)) {
					switch ($numrows['statut']) {
						case USER_TEACHER: $teachers++; break;
						case USER_STUDENT: $students++; break;
						case USER_GUEST: $visitors++; break;
						default: break;
					}
				}
                                $cu[$cu_key] = "<small>$teachers $langTeachers | $students $langStudents | $visitors $langGuests </small>";
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
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE verified_mail = ".EMAIL_VERIFIED.";") . "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></td>
                                    <td class='right'><b>" .
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE verified_mail = ".EMAIL_UNVERIFIED.";") . "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></td>
                                    <td class='right'><b>" .
                                        db_query_get_single_value("SELECT COUNT(*) FROM user WHERE verified_mail = ".EMAIL_VERIFICATION_REQUIRED.";") . "</b></td></tr>
                                <tr><td><img src='$themeimg/arrow.png' alt=''><a href='listusers.php?search=yes'>$langTotal</a></td>
                                    <td class='right'><b>" . db_query_get_single_value("SELECT COUNT(*) FROM user;") . "</b></td></tr>
                            </table>";
		break;
		default:
		break;
	}
}
$tool_content .= "<br /><p class='right'><a href='index.php' class=mainpage>$langBackAdmin</a></p>";

/**
 * output a <table> with an array
 * @global type $langTypesClosed
 * @global type $langTypesRegistration
 * @global type $langTypesOpen
 * @global type $langsCourseInactiveShort
 * @global type $langPre
 * @global type $langPost
 * @global type $langOther
 * @global type $native_language_names_init
 * @param type $table
 * @return string
 */
function tablize($table) {

	global $langTypesClosed, $langTypesRegistration, $langTypesOpen, $langsCourseInactiveShort, 
                        $langPre, $langPost, $langOther, $native_language_names_init;

	$ret = "";
	if (is_array($table)) {
		while (list($key, $thevalue) = each($table)) {
			$ret .= "<tr>";
			switch ($key) {                                
                                case '0': $key = $langTypesClosed; break;
                                case COURSE_REGISTRATION; $key = $langTypesRegistration; break;
				case COURSE_OPEN: $key = $langTypesOpen; break;
                                case COURSE_INACTIVE: $key = $langsCourseInactiveShort; break;
				case 'pre': $key = $langPre; break;
				case 'post': $key = $langPost; break;
				case 'other': $key = $langOther; break;
				case 'el': $key = $native_language_names_init['el']; break;
				case 'en': $key = $native_language_names_init['en']; break;
				case 'es': $key = $native_language_names_init['es']; break;
                                case 'fr': $key = $native_language_names_init['fr']; break;
                                case 'it': $key = $native_language_names_init['it']; break;
                                case 'de': $key = $native_language_names_init['de']; break;
			}
			$ret .= "<td style='font-size: 90%'>".$key."</td>";
			$ret .= "<td class='right'><strong>".$thevalue."</strong></td></tr>";
		}
	}
	return $ret;
}

function ok_message() {
	global $langNotExist;

	return "<b><span style='color: #00FF00'>$langNotExist</span></b>";
}

function error_message() {
	global $langExist;

	return "<b><span style='color: #FF0000'>$langExist</span></b>";
}

function list_ManyResult($sql) {

	$resu=array();

	$res = db_query($sql);
	while ($resA = mysql_fetch_array($res))
	{
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}

draw($tool_content, 3);
