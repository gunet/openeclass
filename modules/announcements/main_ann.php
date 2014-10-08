<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$isaid = isset($_GET['aid']);
$announceArr = Database::get()->queryArray($isaid ? "SELECT `date`, `title` , `body` FROM `admin_announcement` WHERE id = ?d" :
                "SELECT `date`, `title` , `body` FROM `admin_announcement`
	        WHERE `visible` = 1 AND lang= ?s ORDER BY `date` DESC", $isaid ? intval($_GET['aid']) : $language);

$numOfAnnouncements = count($announceArr);
if ($numOfAnnouncements > 0) {
    $tool_content .= "<table width='100%' class='sortable'>";
    for ($i = 0; $i < $numOfAnnouncements; $i++) {
        $tool_content .= "<tr><td>
		<img src='$themeimg/arrow.png' alt='' /></td>
		<td><b>" . q($announceArr[$i]->title) . "</b>
		&nbsp;<span class='smaller'>(" . claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]->date)) . ")</span>
		<p>
		" . standard_text_escape($announceArr[$i]->body) . "<br /></p>
		</td>
		</tr>";
    }
    $tool_content .= "</table>";
}
draw($tool_content, 0);
