<?php
/**===========================================================================
*              GUnet e-Class 2.0
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
/**
 * Groups Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This module is responsible for the user groups of each lesson
 *
 */
$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
$nameTools = $langGroupProperties;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

$dbname = $_SESSION['dbname'];
$tool_content = "";
$tool_content .= <<<tCont

<form method="post" action="group.php">
<table>
<thead>
<tr> 
<th>
$langGroupProperties
</th>
</tr>
</thead>
<tbody>
<tr>
<td>

tCont;
$resultProperties=db_query("SELECT id, self_registration, private, forum, document 
			FROM group_properties WHERE id=1", $dbname);
while ($myProperties = mysql_fetch_array($resultProperties))
{
	if($myProperties['self_registration'])
	{
		$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1>";
	}
	$tool_content .=  "$langGroupAllowStudentRegistration</td></tr>
		<tr >
		<td class=\"category\">
		$langGroupTools
		</td>
		</tr>
		<tr>
		<td >";
	if($myProperties['forum'])
	{
		$tool_content .=  "<input type=checkbox name=\"forum\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"forum\" value=1>";
	}
	$tool_content .=  "$langGroupForum :";
	if($myProperties['private'])
	{
		$tool_content .=  "<input type=radio name=\"private\" value=1 checked>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0>
		&nbsp;$langPublic";
	}
	else 
	{
		$tool_content .=  "<input type=radio name=\"private\" value=1>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0 checked>
		&nbsp;$langPublic";
	}
	$tool_content .=  "</td></tr><tr><td>";
	if($myProperties['document'])
	{
		$tool_content .=  "<input type=checkbox name=\"document\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"document\" value=1>";
	}
	$tool_content .=  "$langGroupDocument"; 
}

$tool_content .= <<<tCont2
	</td></tr>
</tbody>
	</table>
	<br>
	<input type="submit" name="properties" value="$langValidate">

</form>


tCont2;

draw($tool_content, 2, 'group');

?>