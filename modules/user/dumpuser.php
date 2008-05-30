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

// creation Excel file
require_once '../../include/excel/Writer.php';

// We give the path to our file here
$workbook = new Spreadsheet_Excel_Writer();

// patch
$workbook->setVersion(8); 
$workbook->setBIFF8InputEncoding($charset);

// Creating the format

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
