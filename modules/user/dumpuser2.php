<?

/**===========================================================================
*              GUnet eClass 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

header("Content-disposition: filename=listusers.xls");
header("Content-type: application/msexcel; charset=UTF-8");
header("Pragma: no-cache");
header("Expires: 0");

$crlf="\n";

// doing some DOS-CRLF magic...
$client=getenv("HTTP_USER_AGENT");
if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs)) {
	$os = $regs[1];
	// this looks better under WinX
	if (eregi("Win",$os)) $crlf="\r\n";
	}


	echo "$langSurname\t$langName\t$langEmail\t$langAm\t$langUsername\t$langGroup";
	echo "$crlf";
	echo "$crlf";
	$sql = db_query("SELECT  user.nom, user.prenom, user.email, user.am, user.username, user_group.team
                        FROM cours_user, user LEFT JOIN `$currentCourseID`.user_group ON `user`.user_id=user_group.user
                         WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
			ORDER BY user.nom", $mysqlMainDb);
	$r=0;
	while ($r < mysql_num_rows($sql)) {
		$a=mysql_fetch_array($sql);
		echo "$crlf";
		$f=0;
		while ($f < mysql_num_fields($sql)) {
			echo("$a[$f]\t");
			$f++;
			}
		$r++;
}
echo "$crlf";

}  // end of initial if
?>
