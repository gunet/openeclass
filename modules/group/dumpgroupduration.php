<?

/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
$require_current_course = TRUE;

include '../../include/init.php';
include '../usage/duration_query.php';

$userGroupId = intval($_REQUEST['userGroupId']);
list($tutor_id, $group_name) = mysql_fetch_row(db_query("SELECT tutor, name FROM student_group WHERE id='$userGroupId'", $currentCourseID));
$is_tutor = ($tutor_id == $uid);
if (!$is_adminOfCourse and !$is_tutor) {
        header('Location: group_space.php?userGroupId=' . $userGroupId);
        exit;
}

if($is_adminOfCourse) {
	if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
		$charset = 'Windows-1253';
	} else {
		$charset = 'UTF-8';
	}
	$crlf="\r\n";

	header("Content-Type: text/csv; charset=$charset");
	header("Content-Disposition: attachment; filename=groupuserduration.csv");
	if (isset($_REQUEST['u_date_start']) and
            isset($_REQUEST['u_date_end'])) {
                $u_date_start = autounquote($_REQUEST['u_date_start']);
                $u_date_end = autounquote($_REQUEST['u_date_end']);
	} else {
		list($min_date) = mysql_fetch_row(db_query(
                                'SELECT MIN(date_time) FROM actions', $currentCourseID));
		$u_date_start = strftime('%Y-%m-%d', strtotime($min_date));
                $u_date_end = strftime('%Y-%m-%d', strtotime('now'));
	}
	
	if (isset($u_date_start) and isset($u_date_end)) {
		$first_line = "$langFrom $u_date_start $langAs $u_date_end";
	} else {
		$date_spec = '';
		
	}
	echo csv_escape($first_line), $crlf, $crlf,
	     join(';', array_map("csv_escape", array($langSurname, $langName, $langAm, $langGroup, $langDuration))),
	     $crlf;
	$totalDuration = 0;

	$result = user_duration_query($currentCourseID, $cours_id, $u_date_start, $u_date_end, $userGroupId);
	
	while ($row = mysql_fetch_assoc($result)) {
                echo csv_escape($row['nom']) . ";" .
                     csv_escape($row['prenom']) . ";" .
                     csv_escape($row['am']) . ";" .
                     csv_escape($group_name) . ";" .
                     csv_escape(format_time_duration(0 + $row['duration'])) . ";" .
                     csv_escape(round($row['duration'] / 3600));
                echo $crlf;
        }
} 

user_duration_query_end();

