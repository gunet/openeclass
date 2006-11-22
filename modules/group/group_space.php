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

// Delete ancient possible other group values
session_unregister("secretDirectory");
session_unregister("userGroupId");
session_unregister("forumId");

$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
$nameTools = $langGroupSpace;
$navigation[] = array ("url"=>"group.php", "name"=> $langGroupManagement);
$tool_content = "";
if(!session_is_registered('userGroupId')){
	$_SESSION['userGroupId'] = $_REQUEST['userGroupId'];
}

########################### SQL SELF-REGISTRATION ################################

if(isset($registration) and $statut != 10)
{
	 $userGroupId = $_SESSION['userGroupId'];
	 session_unregister('userGroupId');

	$sqlExist=mysql_query("SELECT id FROM `$dbname`.user_group 
				WHERE user='$uid' AND team='$userGroupId'");
				$countExist = mysql_num_rows($sqlExist);
	if($countExist==0 )
	{
		$sqlReg=mysql_query("INSERT INTO `$dbname`.user_group (user, team) VALUES ('$uid', '$userGroupId')");
		$message="<font color=red>$langGroupNowMember</font> | ";
		$regDone=1;
	}
}

$currentCourse=$dbname;

if ($is_adminOfCourse) {
	if (isset($_REQUEST['userGroupId'])) {
		$userGroupId = $_REQUEST['userGroupId'];
	}
 }	// if prof

############### Secret Directory for Documents #################

$sqlGroup=mysql_query("SELECT secretDirectory 
		FROM `$currentCourse`.student_group 
			WHERE id='$userGroupId'");

while ($myGroup= mysql_fetch_array($sqlGroup))
{
	$secretDirectory=$myGroup['secretDirectory'];
}


################ NAME AND DESCRIPTION ######################
mysql_select_db($dbname);
$resultGroup=mysql_query("SELECT name, description, tutor, forumId
				FROM student_group 
					WHERE id='$userGroupId'");

while ($myGroup = mysql_fetch_array($resultGroup))
{
	$forumId=$myGroup['forumId'];

	
	if ($is_adminOfCourse) 
	{
		$tool_content .=  "<p><a href=\"group_edit.php?userGroupId=$userGroupId\">$langEditGroup</a> | ";

	}
	elseif(isset($selfReg) AND ($uid))
	{
		$tool_content .=  "<p><a href=\"$_SERVER[PHP_SELF]?registration=1\">$langRegIntoGroup</a>"; 
	}
	elseif(isset($regDone))
	{
		$tool_content .=  "<p>$message";
	}
	
	$tool_content .= loadGroupTools()."</p>";
	
	$tool_content .=  "
		<table>
		<thead>
		<tr>
		<th>
			$langGroupName
		</th>
		<td>
			$myGroup[name]
		</td>
		</tr>
		</thead>
		</table>
		<br>
		";

	$sqlTutor=mysql_query("SELECT tutor, user_id, nom, prenom, email, forumId 
					FROM `$mysqlMainDb`.user, student_group
					WHERE user.user_id=student_group.tutor
					AND student_group.id='$userGroupId'");
	$countTutor = mysql_num_rows($sqlTutor);
	$tool_content_tutor="";
	if ($countTutor==0)
	{
		$tool_content_tutor .=  "$langGroupNoTutor";	
	}
	else 
	{
		while ($myTutor = mysql_fetch_array($sqlTutor))
		{
			$tool_content_tutor .=  "$myTutor[nom] $myTutor[prenom] 
				<a href=mailto:$myTutor[email]>$myTutor[email]</a>";
		}	// while tutor

	}	// else

	$tool_content .=  "
		<table>
		<thead>
		<tr>
		<th>
			$langGroupTutor
		</th>
		<td>
			$tool_content_tutor
		</td>
		</tr>
		</thead>
		</table>
		<br>
		";

	// Show 'none' if no description
	$countDescription=strlen ($myGroup['description']);
	$tool_content_description = "";
	if(($countDescription <= 3))
	{
		$tool_content_description .=  "$langGroupNone";
	}
	else
	{
		$tool_content_description .=  "$myGroup[description]";
	}	// else
	
	$tool_content .=  "
		<table>
		<thead>
		<tr>
		<th>
			$langGroupDescription
		</th>
		<td>
			$tool_content_description
		</td>
		</tr>
		</thead>
		</table>
		<br>
		";
}	// while loop


################ MEMBERS ################################

	$tool_content .=  "
	<br>
		<table>
		<thead>
		<tr>
		<th colspan=3>
			$langGroupMembers
		</th>
		</tr>
		<tr>
		<th>
			 $langNameSurname
		</th>
		<th>
			$langAM
		</th>
		<th>
			$langEmail
		</th>
		</tr>
		
			

		";

$resultMember=mysql_query("SELECT nom, prenom, email, am
			FROM `$mysqlMainDb`.user, user_group 
			WHERE user_group.team='$userGroupId' 
			AND user_group.user=$mysqlMainDb.user.user_id");
$countMember = mysql_num_rows($resultMember);

if(($countMember==0))
{
	$tool_content .=  "<td colspan=3>$langGroupNoneMasc</td>";
}
else
{
	while ($myMember = mysql_fetch_array($resultMember))
	{	
		$tool_content .= "<tr>";
		$tool_content .= "<td>";
		$tool_content .=  "$myMember[prenom] $myMember[nom]";
		$tool_content .= "</td>";
		$tool_content .= "<td>";
		if (!empty($myMember['am'])) {
			$tool_content .=  "$myMember[am]";
		} else {
			$tool_content .= "-";
		}
		$tool_content .= "</td>";
		$tool_content .= "<td>";
		$tool_content .= "<a href=mailto:$myMember[email]>$myMember[email]</a>";
		$tool_content .= "</td>";
		$tool_content .= "</tr>";
	}	// while loop
}	// else

$tool_content .= "</tbody>
		</table>";


draw($tool_content, 2, 'group');


function loadGroupTools(){
global $selfReg, $forumId, $langForums, $userGroupId, $langDocuments;
global $is_adminOfCourse, $userGroupId, $langEmailGroup;
###################### TOOLS #############################

// Vars needed to determine group File Manager and group Forum
// They are unregistered when opening group.php once again.

session_register("secretDirectory");
session_register("userGroupId");
session_register("forumId");

$group_tools = "";
if(isset($selfReg))
{
	$group_tools .=  "&nbsp;</td>";
}
else
{
	$resultProperties=mysql_query("SELECT id, self_registration, private, forum, document 
					FROM group_properties WHERE id=1");
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
		// Drive members into their own forum
		if($myProperties['forum']==1){
			$group_tools .=  "<a href=\"../phpbb/viewforum.php?forum=$forumId\">$langForums</a> | ";
		}

		// Drive members into their own File Manager
		if($myProperties['document']==1){
			$group_tools .=  "<a href=\"document.php?userGroupId=$userGroupId\">$langDocuments</a>";
		}
		
		
	}	// while loop

if ($is_adminOfCourse)
{

	$group_tools .=  " | <a href=\"group_email.php?userGroupId=$userGroupId\">$langEmailGroup</a>";
}

}
$group_tools .= "</p>";

session_unregister("secretDirectory");

session_unregister("forumId");

return $group_tools;
}
?>

