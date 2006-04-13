<?
$langFiles = 'registration';
$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

header("Content-disposition: filename=listusers.xls");
header("Content-type: application/msexcel; charset=iso-8859-7");
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
