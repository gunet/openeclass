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

	if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
		$charset = 'Windows-1253';
	} else {
		$charset = 'UTF-8';
	}
	$crlf="\r\n";

	header("Content-Type: text/csv; charset=$charset");
	header("Content-Disposition: attachment; filename=listusers.csv");
	
	echo "$langSurname;$langName;$langEmail;$langAm;$langUsername;$langGroups$crlf";
	$sql = db_query("SELECT  user.nom, user.prenom, user.email, user.am, user.username, user_group.team
			FROM cours_user, user LEFT JOIN `$currentCourseID`.user_group ON `user`.user_id=user_group.user
			WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
			ORDER BY user.nom", $mysqlMainDb);
	$r=0;
	while ($r < mysql_num_rows($sql)) {
		$a = mysql_fetch_array($sql);
		echo "$crlf";
		$f=0;
		while ($f < mysql_num_fields($sql)) {
			if ($f > 0) {
				echo ';';
			}
			echo csv_escape($a[$f]);
			$f++;
		}
		$r++;
	}
	echo "$crlf";
}  // end of initial if



function csv_escape($string, $force = false)
{
	global $charset;

        if ($charset != 'UTF-8') {
                $string = iconv('UTF-8', $charset, $string);
        }
        if (!preg_match("/[ ,!;\"'\\\\]/", $string) and !$force) {
                return $string;
        } else {
                return '"' . str_replace('"', '""', $string) . '"';
        }
}
