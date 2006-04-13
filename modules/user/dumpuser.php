<?
$langFiles = 'registration';
$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

// creation Excel file
require_once '../../include/excel/Writer.php';

// We give the path to our file here
$workbook = new Spreadsheet_Excel_Writer();

// patch
$workbook->setVersion(8); 
$workbook->setBIFF8InputEncoding($charset);

// Creating the format
//$format_bold =& $workbook->addFormat();
$format_header =& $workbook->addFormat(array('Size' => 12, 'Align' => 'center'));
$format_header->setBold();

$format_data =& $workbook->addFormat(array('Size' => 12, 'Align' => 'center'));

// sending HTTP headers
$workbook->send('listusers.xls');

$worksheet =&$workbook->addWorksheet('List of Users');
$worksheet->setColumn(0,2,30);
$worksheet->setColumn(3,5,20);
// title
$worksheet->write(0, 0, "$langSurname", $format_header);
$worksheet->write(0, 1, "$langName", $format_header);
$worksheet->write(0, 2, "$langEmail", $format_header);
$worksheet->write(0, 3, "$langAm", $format_header);
$worksheet->write(0, 4, "$langUsername", $format_header);
$worksheet->write(0, 5, "$langGroup", $format_header);

// data

$sql = db_query("SELECT user.nom, user.prenom, user.email, user.am, user.username, user_group.team
                     FROM cours_user, user LEFT JOIN `$currentCourseID`.user_group ON `user`.user_id=user_group.user
                     WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
		     ORDER BY user.nom", $mysqlMainDb);

$r=0;
while ($r < mysql_num_rows($sql)) {
		$a=mysql_fetch_array($sql);
		$f=0;
		while ($f < mysql_num_fields($sql)) {
			$worksheet->write($r+1, $f, "$a[$f]", $format_data);
			$f++;
			}
		$r++;
}

// We still need to explicitly close the workbook
$workbook->close();

}  // end of initial if
?>
