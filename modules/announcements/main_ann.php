<?php
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


$require_help = true;
$guest_allowed = true;

include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');

$nameTools = $langAnnouncements;

$qlang = ($language == "greek")? 'el': 'en';

if (isset($_GET['aid'])) {
	$aid = intval($_GET['aid']);
	$sql = "SELECT `date`, `title` , `body` FROM `admin_announcements` WHERE id = '$aid'";
} else {
	$sql = "SELECT `date`, `title` , `body` FROM `admin_announcements`
	        WHERE `visible` = 'V' AND lang='$qlang' ORDER BY `date` DESC";
}
$result = db_query($sql, $mysqlMainDb);
if (mysql_num_rows($result) > 0) {
	$announceArr = array();
	while ($eclassAnnounce = mysql_fetch_array($result)) {
		array_push($announceArr, $eclassAnnounce);
	}
        $tool_content .= "<br/>
        <table width='100%' class='tbl_border'>
	<tr><th>$langAnnouncements
	</th></tr>
	";

	$numOfAnnouncements = count($announceArr);
	for($i=0; $i < $numOfAnnouncements; $i++) {
		$tool_content .= "<tr><td>
		
		<img src='${urlAppend}/template/classic/img/arrow.png' alt='' />
		<b>".q($announceArr[$i]['title'])."</b>
		&nbsp;(".claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]['date'])).")
		<p>
		".standard_text_escape($announceArr[$i]['body'])."<br /></p>
		</td>
		</tr>";
	}
	$tool_content .= "</table>";
}
draw($tool_content, 0);
?>
