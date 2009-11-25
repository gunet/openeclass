<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

// Delete ancient possible other group values
if (isset($_SESSION['secretDirectory'])) {
	unset($_SESSION['secretDirectory']);
}
if (isset($_SESSION['forumId'])) {
	unset($_SESSION['forumId']);
}

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
$nameTools = $langGroupSpace;
$navigation[] = array ("url"=>"group.php", "name"=> $langGroups);
$tool_content = "";

$countRegistered = mysql_num_rows(db_query("SELECT id FROM user_group 
	WHERE team='$userGroupId'", $currentCourse));
$total = mysql_fetch_array(db_query("SELECT maxStudent FROM student_group 
		WHERE id='$userGroupId'", $currentCourse));
$totalRegistered = $total[0];

if (isset($_REQUEST['userGroupId'])) {
        $userGroupId = intval($_REQUEST['userGroupId']);
} else {
	die("Wrong user group id / User group id not set");
}

if (isset($registration)) {
	if (($statut != 10) and ($countRegistered < $totalRegistered)) {
		$sqlExist=mysql_query("SELECT id FROM `$dbname`.user_group
			WHERE user='$uid' AND team='$userGroupId'");
		$countExist = mysql_num_rows($sqlExist);
		if($countExist == 0) {
			$sqlReg=mysql_query("INSERT INTO `$dbname`.user_group (user, team)
				VALUES ('$uid', '$userGroupId')");
			$message="<font color=red>$langGroupNowMember</font>: ";
			$regDone=1;
		}
	} else { 
		$tool_content .= $langForbidden;
		draw($tool_content, 2, 'group');
		exit();
	}
}

$currentCourse=$dbname;

############### Secret Directory for Documents #################
$sqlGroup=mysql_query("SELECT secretDirectory FROM `$currentCourse`.student_group WHERE id='$userGroupId'");
while ($myGroup = mysql_fetch_array($sqlGroup)) {
	$secretDirectory = $myGroup['secretDirectory'];
}

// name and description
mysql_select_db($dbname);
$resultGroup=mysql_query("SELECT name, description, tutor, forumId FROM student_group WHERE id='$userGroupId'");
while ($myGroup = mysql_fetch_array($resultGroup))
{
        if ($myGroup['tutor'] == $uid) {
                $is_tutor = true;
        } else {
                $is_tutor = false;
        }
	$forumId = $myGroup['forumId'];
	if ($is_adminOfCourse or $is_tutor) {
		$tool_content .= "<div id='operations_container'><ul id='opslist'>
		<li><a href='group_edit.php?userGroupId=$userGroupId'>$langEditGroup</a></li>";
	} elseif(isset($selfReg) and isset($uid)) { 
		if ($countRegistered < $totalRegistered) {
			$tool_content .=  "<div id='operations_container'><ul id='opslist'>
			<li>
			<a href='$_SERVER[PHP_SELF]?registration=1&amp;userGroupId=$userGroupId'>$langRegIntoGroup</a></li>";
		} else {
			$tool_content .= $langForbidden;
			draw($tool_content, 2, 'group');
			exit();
		}
	} elseif(isset($regDone)) {
		$tool_content .= "<div id='operations_container'><ul id='opslist'>";
		$tool_content .= "$message&nbsp;";
	} else {
		$tool_content .= "<div id='operations_container'><ul id='opslist'>";
	}
	$tool_content .= loadGroupTools();
	$tool_content .=  "<br /><table width='99%' class='FormData'>
	<thead><tr>
	<th width='220'>&nbsp;</th>
	<td><b>$langGroupInfo</b></td>
	</tr>
	<tr>
	<th class='left'>$langGroupName :</th>
	<td>$myGroup[name]</td>
	</tr>";

	$sqlTutor=mysql_query("SELECT tutor, user_id, nom, prenom, email, forumId
		FROM `$mysqlMainDb`.user, student_group
		WHERE user.user_id=student_group.tutor
		AND student_group.id='$userGroupId'");
	$countTutor = mysql_num_rows($sqlTutor);
	$tool_content_tutor="";
	if ($countTutor==0) {
		$tool_content_tutor .=  "$langGroupNoTutor";
	} else {
		while ($myTutor = mysql_fetch_array($sqlTutor)) {
			$tool_content_tutor .= "$myTutor[nom] $myTutor[prenom]
			(<a href=mailto:$myTutor[email]>$myTutor[email]</a>)";
		}	// while tutor
	}	// else

	$tool_content .= "<tr><th class=\"left\">$langGroupTutor :</th>
	<td>$tool_content_tutor</td></tr>";

	// Show 'none' if no description
	$countDescription=strlen ($myGroup['description']);
	$tool_content_description = "";
	if(($countDescription <= 3)) {
		$tool_content_description .=  "$langGroupNone";
	} else {
		$tool_content_description .=  "$myGroup[description]";
	}	// else

	$tool_content .=  "<tr><th class=\"left\">$langDescription :</th>
	<td>$tool_content_description</td></tr>";
}	// while loop

// members
$tool_content .= "<tr><th class=\"left\" valign=\"top\">$langGroupMembers :</th>
<td><table width=\"99%\" align=\"center\" class=\"GroupSum\">
<thead>
<tr>
<td><b>$langNameSurname</b></td>
<td width='100'><div align=\"center\"><b>$langAm</b></div></td>
<td><div align=\"center\"><b>$langEmail</b></div></td>
</tr>
</thead>
<tbody>";

$resultMember=mysql_query("SELECT nom, prenom, email, am
		FROM `$mysqlMainDb`.user, user_group
		WHERE user_group.team='$userGroupId'
		AND user_group.user=$mysqlMainDb.user.user_id");
$countMember = mysql_num_rows($resultMember);

if(($countMember==0)) {
	$tool_content .=  "<tr><td colspan=3>$langGroupNoneMasc</td></tr>";
} else {
	while ($myMember = mysql_fetch_array($resultMember)){
		$tool_content .= "<tr><td>$myMember[prenom] $myMember[nom]</td>
		<td><div align=\"center\">";
		if (!empty($myMember['am'])) {
			$tool_content .=  "$myMember[am]";
		} else {
			$tool_content .= "-";
		}
		$tool_content .= "</div></td>
		<td><div align=\"center\"><a href=mailto:$myMember[email]>$myMember[email]</a></div></td></tr>";
	}	// while loop
}	// else

$tool_content .=  "</tbody></table>";
$tool_content .= "</td></tr></thead></table>";
draw($tool_content, 2, 'group');

function loadGroupTools(){
	global $selfReg, $forumId, $secretDirectory, $langForums, $userGroupId, $langDoc,
               $is_adminOfCourse, $is_tutor, $userGroupId, $langEmailGroup,
               $langUsage;

	// Vars needed to determine group File Manager and group Forum
	// They are unregistered when opening group.php once again.
	$_SESSION['secretDirectory'] = $secretDirectory; 
	$_SESSION['forumId'] = $forumId;
	
	$group_tools = "";
	if(isset($selfReg)) {
		$group_tools .= "";
	} else {
		$resultProperties=mysql_query("SELECT id, self_registration, private, forum, document
			FROM group_properties WHERE id=1");
		while ($myProperties = mysql_fetch_array($resultProperties))
		{
			// Drive members into their own forum
			if($myProperties['forum'] == 1 and $forumId <> 0) {
				$group_tools .= "<li><a href='../phpbb/viewforum.php?forum=$forumId'>$langForums</a></li>";
			}
			// Drive members into their own File Manager
			if($myProperties['document'] == 1) {
				 $group_tools .=  "<li><a href='document.php?userGroupId=$userGroupId'>$langDoc</a></li>";
			}
		}	// while loop
		if ($is_adminOfCourse or $is_tutor)
		{
			$group_tools .=  "<li><a href='group_email.php?userGroupId=$userGroupId'>$langEmailGroup</a></li>
			<li><a href='group_usage.php?userGroupId=$userGroupId'>$langUsage</a></li>";
		}
	}
	$group_tools .= "</ul></div>";
	unset($_SESSION['secretDirectory']);
	unset($_SESSION['forumId']);

	return $group_tools;
}