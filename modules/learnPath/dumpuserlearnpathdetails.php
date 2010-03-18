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
require_once "../../include/lib/learnPathLib.inc.php";

// IF PROF ONLY
if($is_adminOfCourse) {

	if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
		$charset = 'Windows-1253';
	} else {
		$charset = 'UTF-8';
	}
	$crlf="\r\n";

	header("Content-Type: text/csv; charset=$charset");
	header("Content-Disposition: attachment; filename=userslearningpathstats.csv");
	
	echo join(';', array_map("csv_escape", array($langStudent, $langAm, $langGroup, $langProgress))),
	     $crlf;
	
	// display a list of user and their respective progress
	$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
		FROM `user` AS U, `cours_user` AS CU
		WHERE U.`user_id`= CU.`user_id`
		AND CU.`cours_id` = $cours_id
		ORDER BY U.`nom` ASC";
	$usersList = get_limited_list($sql, 500000);
	mysql_select_db($currentCourseID);
	foreach ($usersList as $user)
	{
		echo "$crlf";
		$sql = "SELECT LP.`learnPath_id` FROM `lp_learnPath` AS LP";
		$learningPathList = db_query_fetch_all($sql);
		$iterator = 1;
		$globalprog = 0;
		
		foreach($learningPathList as $learningPath)
		{
			// % progress
			$prog = get_learnPath_progress($learningPath['learnPath_id'], $user['user_id']);
			if ($prog >= 0)
			{
				$globalprog += $prog;
			}
			$iterator++;
		}
		$total = round($globalprog/($iterator-1));
		echo csv_escape(uid_to_name($user['user_id'])).
		";".csv_escape(uid_to_am($user['user_id'])).
		";".csv_escape(gid_to_name(user_group($user['user_id']))).
		";".$total."%";
	}
	echo "$crlf";
}  // end of initial if
