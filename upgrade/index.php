<?php
/* ========================================================================
 * Open eClass 2.6
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



header('Content-Type: text/html; charset=UTF-8');

$path2add=2;
include '../include/baseTheme.php';

$nameTools = $langUpgrade;

if ($GLOBALS['language'] == 'greek') {
	$upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=el:upgrade_doc';
	$link_changes_file = 'CHANGES_el.txt';
} else {
 	$upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=en:upgrade_doc';
	$link_changes_file = 'CHANGES_en.txt';
}

// Initialise $tool_content
$tool_content = "";

// Main body
$tool_content .= "
<div class='alert1'><b>$langWarnUpgrade</b><p>$langExplUpgrade</p>
<p> &nbsp; </p>
<p class='red'>$langExpl2Upgrade</p>";
@set_time_limit(0);
$max_execution_time = ini_get('max_execution_time');
if ($max_execution_time != 0 and $max_execution_time < 300) {
	$tool_content .= "<p>$langExecTimeUpgrade</p>";
	draw($tool_content, 0);
	exit;
}
$tool_content .= "<p> &nbsp; </p><p>$langUpgToSee <a href='$link_changes_file'>$langHere</a>. $langUpgRead <a href='$upgrade_info_file' target=_blank>$langUpgMan</a>
   $langUpgLastStep</p>
<p>$langUpgradeCont</p></div>
<form method='post' action='upgrade.php'>
<fieldset>
<legend><b>$langUpgDetails</b></legend>
<table class='tbl' width='100%'>
<tr>
<th width='200'>$langUsername:</th>
<td><input class='auth_input_admin' style='width:200px; heigth:20px;' type='text' name='login' size='20'></td>
</tr>
<tr>
<th width='200'>$langPass:</th>
<td><input class='auth_input_admin' type='password' style='width:200px; heigth:20px;' name='password' size='20'></td>
</tr>

<tr>
  <td class='right' colspan='2'><input type='submit' name='submit_upgrade2' value='".q($langUpgrade)."' /></td>
  </tr>

</table>
</fieldset>";

if (isset($from_admin)) {
        $tool_content .= "<input type='hidden' name='from_admin' value='$from_admin'>";
}

$tool_content .= "</form>";

if (isset($from_admin)) {
        $tool_content .= "<p align=right><a href='../modules/admin/index.php' class=mainpage>$langBackAdmin</a></p>";
} else {
        $tool_content .= "&nbsp;";
}
draw($tool_content, 0);
