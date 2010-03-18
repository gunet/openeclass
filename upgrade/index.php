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


header('Content-Type: text/html; charset=UTF-8');

$path2add=2;
include '../include/baseTheme.php';

$nameTools = $langUpgrade;

if ($GLOBALS['language'] == 'greek') {
	$upgrade_info_file = 'upgrade_info.php';
	$link_changes_file = 'CHANGES_el.txt';
} else {
 	$upgrade_info_file = 'upgrade_info_en.php';
	$link_changes_file = 'CHANGES_en.txt';
}

// Initialise $tool_content
$tool_content = "";

// Main body
$tool_content .= "
<div class='warntitle'>$langWarnUpgrade</div><p>$langExplUpgrade</p>
<p>$langExpl2Upgrade</p>";
set_time_limit(0);
$max_execution_time = ini_get('max_execution_time');
if ($max_execution_time != 0 and $max_execution_time < 300) {
	$tool_content .= "<hr><p>$langExecTimeUpgrade</p><hr>";
	draw($tool_content, 0);
	exit;
}
$tool_content .= "<p>$langUpgToSee <a href='$link_changes_file'>$langHere</a>. $langUpgRead <a href='$upgrade_info_file'>$langUpgMan</a>
   $langUpgLastStep</p>
<p>$langUpgradeCont</p>
<form method='post' action='upgrade.php'>
<table width='70%' align='center'>
<tr><td style='border: 1px solid #FFFFFF;'>
<fieldset><legend><b>$langUpgDetails</b></legend>
<table cellpadding='1' cellspacing='2' width='99%'>
<tr><th style='text-align: left; background: #edecdf; color: #727266; font-size: 90%'>$langUsername :</th>
<td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' style='width:200px; heigth:20px;' type='text' name='login' size='20'></td>
</tr>
<tr><th style='text-align: left; background: #edecdf; color: #727266; font-size: 90%'>$langPass :</th>
    <td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' type='password' style='width:200px; heigth:20px;' name='password' size='20'></td>
</tr>
<tr><td colspan='2' style=\"border: 1px solid #FFFFFF;\" align='center'>
    <input type='submit' name='submit_upgrade' value='$langUpgrade'></td>
</tr>
</table>
</fieldset>
</td></tr></table>";

if (isset($from_admin)) {
        $tool_content .= "<input type='hidden' name='from_admin' value='$from_admin'>";
}

$tool_content .= "</form></td></tr><tr><td style=\"border: 1px solid #FFFFFF;\" colspan=2>";

if (isset($from_admin)) {
        $tool_content .= "<p align=right><a href='../modules/admin/index.php' class=mainpage>$langBackAdmin</a></p>";
} else {
        $tool_content .= "&nbsp;";
}
draw($tool_content, 0);
