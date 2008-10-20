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

	header("Content-Type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=listusers.csv");
	
	if (isset($_GET['enc']) and $_GET['enc'] == 'cp1253') {
		$win = true;
	} else {
		$win = false;
	}
	$crlf="\n";
	
	// doing some DOS-CRLF magic...
	$client=getenv("HTTP_USER_AGENT");
	if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs)) {
		$os = $regs[1];
		// this looks better under WinX
		if (eregi("Win",$os)) $crlf="\r\n";
	}
	
		echo "$langSurname\t$langName\t$langEmail\t$langAm\t$langUsername\t$langGroups";
		echo "$crlf";
		echo "$crlf";
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
				echo("".csv_escape($a[$f])."\t");
				$f++;
			}
			$r++;
		}
	echo "$crlf";
}  // end of initial if



function csv_escape($string, $force = false)
{
        if ($GLOBALS['win']) {
                $string = iconv('UTF-8', 'Windows-1253', $string);
        }
        if (!preg_match("/[ ,!;\"'\\\\]/", $string) and !$force) {
                return $string;
        } else {
                return '"' . str_replace('"', '""', $string) . '"';

        }
}
