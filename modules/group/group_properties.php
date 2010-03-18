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
/*
 * Groups Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This module is responsible for the user groups of each lesson
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';
$require_prof = true;

include '../../include/baseTheme.php';
$nameTools = $langGroupProperties;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

$dbname = $_SESSION['dbname'];

if ($is_adminOfCourse) {
	$tool_content = "";
	$tool_content .= <<<tCont

<form method="post" action="group.php">
    <table width="99%" align="left" class="FormData">
    <tbody>
    <tr>
      <th width="220">&nbsp;</th>
      <td><b>$langGroupProperties</b></td>
    </tr>
    <tr>
      <th class="left">$langGroupStudentRegistrationType :</th>
      <td>

tCont;
	$resultProperties=db_query("SELECT id, self_registration, private, forum, document
			FROM group_properties WHERE id=1", $dbname);
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
		if($myProperties['self_registration'])
		{
			$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1 checked>&nbsp;$langGroupAllowStudentRegistration";
		}
		else
		{
			$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1>&nbsp;$langGroupAllowStudentRegistration";
		}
		$tool_content .= "</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langTools</b></td>
    </tr>
    <tr>
    <th class=\"left\">$langGroupForum :</th>
      <td>";
		if($myProperties['forum'])
		{
			$tool_content .=  "<input type=checkbox name=\"forum\" value=1 checked>";
		}
		else
		{
			$tool_content .=  "<input type=checkbox name=\"forum\" value=1>";
		}
		$tool_content .=  "</td>
    </tr>";
		$tool_content .=  "
    <tr>
      <th class=\"left\">$langPrivate_1 :</th>
      <td>";
		if($myProperties['private'])
		{
			$tool_content .= "<input type=radio name=\"private\" value=1 checked>
		&nbsp;$langPrivate_2&nbsp;<br />
		<input type=radio name=\"private\" value=0>
		&nbsp;$langPrivate_3";
		}
		else
		{
			$tool_content .=  "<input type=radio name=\"private\" value=1>
		&nbsp;$langPrivate_2&nbsp;<br />
		<input type=radio name=\"private\" value=0 checked>
		&nbsp;$langPrivate_3";
		}
		$tool_content .=  "</td>
    </tr>";
		$tool_content .=  "
    <tr>
      <th class=\"left\">$langDoc :</th>
      <td>";
		if($myProperties['document'])
		{
			$tool_content .=  "<input type=checkbox name=\"document\" value=1 checked>";
		}
		else
		{
			$tool_content .=  "<input type=checkbox name=\"document\" value=1>";
		}
			$tool_content .=  "</td>
    </tr>";
	}

	$tool_content .= <<<tCont2

    <tr>
      <th>&nbsp;</th>
      <td><input type="submit" name="properties" value="$langModify"></td>
    </tr>
    </tbody>
    </table>
    <br />
    
    </form>

tCont2;

	draw($tool_content, 2, 'group');
}
?>
