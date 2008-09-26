<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/


header('Content-Type: text/html; charset=UTF-8');

$path2add=2;
include '../include/baseTheme.php';

$nameTools = $langUpgrade;
$max_execution_time = trim(ini_get('max_execution_time'));

// Initialise $tool_content
$tool_content = "";

// Main body
$tool_content .= "
<div class='warntitle'>$langWarnUpgrade</div><p>$langExplUpgrade</p>
<p>$langExpl2Upgrade</p>";
if (intval($max_execution_time) < 300) {
	$tool_content .= "<hr><p>$langExecTimeUpgrade</p><hr>";
	draw($tool_content, 0);
	exit;
}
$tool_content .= "<p>$langUpgToSee <a href='CHANGES.txt'>$langHere</a>. $langUpgRead <a href='upgrade_info.php'>$langUpgMan</a>
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
?>
