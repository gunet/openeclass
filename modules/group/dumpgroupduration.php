<?

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
$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {
	$userGroupId = intval($_REQUEST['userGroupId']);
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
                $u_date_start = $_REQUEST['u_date_start'];
                $u_date_end = $_REQUEST['u_date_end'];
	} else {
		list($min_date) = mysql_fetch_row(db_query(
                                'SELECT MIN(date_time) FROM actions', $currentCourseID));
		$u_date_start = strftime('%Y-%m-%d', strtotime($min_date));
                $u_date_end = strftime('%Y-%m-%d', strtotime('now'));
	}
	
	$date_spec = ' AND date_time BETWEEN ' .
                             autoquote($u_date_start) . ' AND ' .
                             autoquote($u_date_end);


	if (isset($u_date_start) and isset($u_date_end)) {
		$date_spec = ' AND date_time BETWEEN ' .
                             autoquote($u_date_start) . ' AND ' .
                             autoquote($u_date_end);
		$first_line = "$langFrom $u_date_start $langAs $u_date_end";
	} else {
		$date_spec = '';
		
	}
	echo "".csv_escape($first_line)."";
	echo "$crlf";
	echo "$crlf";
	echo join(';', array_map("csv_escape", array($langSurnameName, $langAm, $langGroup, $langDuration))),
	     $crlf;
	$totalDuration = 0;
	$sql= "SELECT user FROM user_group WHERE team = '$userGroupId'";
	$result= db_query($sql, $currentCourseID);
	
	while ($row = mysql_fetch_assoc($result)) {
		echo "$crlf";
		$user_id = $row['user'];
		$sql2 = db_query('SELECT SUM(duration) FROM actions WHERE user_id = ' . $user_id . $date_spec, $currentCourseID);
		list($duration[$currentCourseID]) = mysql_fetch_row($sql2);
		$totalDuration += $duration[$currentCourseID];
		$totalDuration = format_time_duration(0 + $totalDuration);
		foreach ($duration as $code => $time) {
			echo csv_escape(uid_to_name($user_id)) . ";" 
			. csv_escape(uid_to_am($user_id)) . ";" 
			. csv_escape(gid_to_name(user_group($user_id))) . ";" 
			. csv_escape(format_time_duration(0 + $time)) . ";";
			}	
		}
	echo "$crlf";
}  // end of initial if

