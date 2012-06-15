<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_help = true;
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$nameTools = $langAnnouncements;

if (isset($_GET['aid'])) {
	$aid = intval($_GET['aid']);
	$sql = "SELECT `date`, `title` , `body` FROM `admin_announcement` WHERE id = '$aid'";
} else {
	$sql = "SELECT `date`, `title` , `body` FROM `admin_announcement`
	        WHERE `visible` = 1 AND lang='$language' ORDER BY `date` DESC";
}
$result = db_query($sql);
if (mysql_num_rows($result) > 0) {
	$announceArr = array();
	while ($eclassAnnounce = mysql_fetch_array($result)) {
		array_push($announceArr, $eclassAnnounce);
	}
        $tool_content .= "<table width='100%' class='sortable'>";
	$numOfAnnouncements = count($announceArr);
	for($i=0; $i < $numOfAnnouncements; $i++) {
		$tool_content .= "<tr><td>
		<img src='$themeimg/arrow.png' alt='' /></td>
		<td><b>".q($announceArr[$i]['title'])."</b>
		&nbsp;<span class='smaller'>(".claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]['date'])).")</span>
		<p>
		".standard_text_escape($announceArr[$i]['body'])."<br /></p>
		</td>
		</tr>";
	}
	$tool_content .= "</table>";
}
draw($tool_content, 0);
