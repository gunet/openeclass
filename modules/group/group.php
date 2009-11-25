<?
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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GROUPS');
/**************************************/

// Remove old group identification if
// possible entrance in another group space (admin for instance)
unset($_SESSION['secretDirectory']);
unset($_SESSION['forumId']);

$currentCourse=$dbname;
mysql_select_db("$currentCourse");
$nameTools = $langGroups;
$totalRegistered=0;
$tool_content = "";
if ($is_adminOfCourse) {
	$head_content = '
<script>
function confirmation (name)
{
	if (name == "delall") {
		if(confirm("'.$langDeleteGroupAllWarn.' ?"))
		{return true;}
		else
		{return false;}
	} else if (name == "emptyall") {
		if (confirm("'.$langDeleteGroupAllWarn.' ?"))
		{return true;}
		else
		{return false;}
	} else {
		if (confirm("'.$langDeleteGroupWarn.' ("+ name + ") ?"))
        {return true;}
    	else
        {return false;}
    }
}
</script>
';
}

// Group creation
if(isset($_REQUEST['creation']) && $is_adminOfCourse) {
	// Create a hidden category for group forums
        $req = db_query('SELECT cat_id FROM catagories WHERE cat_order = -1');
        if ($req and mysql_num_rows($req) > 0) {
                list($cat_id) = mysql_fetch_row($req);
        } else {
          	db_query("INSERT INTO catagories (cat_title, cat_order)
                                 VALUES ('$langCatagoryGroup', -1)");
        	$cat_id = mysql_insert_id();
        }
	for ($i = 1; $i <= $group_quantity; $i++) {
		// Creating a Unique Id path to group documents to try (!)
		// avoiding groups entering other groups area
		$secretDirectory=uniqid("");
		mkdir("../../courses/$currentCourse/group/$secretDirectory", 0777);
		// Write group description in student_group table. Contains path to group document dir.
		db_query("INSERT INTO student_group (maxStudent, secretDirectory)
			VALUES ('".mysql_real_escape_string($group_max)."', '$secretDirectory')");
		$lastId=mysql_insert_id();

		db_query("INSERT INTO forums
			(forum_id, forum_name, forum_desc,
			forum_access, forum_moderator, forum_topics,
			forum_posts, forum_last_post_id, cat_id, forum_type)
			VALUES ('','$langForumGroup $lastId','',2,1,0,0,1,$cat_id,0)");

		$forumInsertId=mysql_insert_id();
		db_query("UPDATE student_group SET name='$langGroup $lastId',
			forumId='$forumInsertId' WHERE id='$lastId'");
	}	// for
	if ($group_quantity == 1)
		$message = "$group_quantity $langGroupAdded";
	else
		$message = "$group_quantity $langGroupsAdded";
}	// if $submit


if(isset($_REQUEST['properties']) && $is_adminOfCourse)
{
	@db_query("UPDATE group_properties
		SET self_registration='".mysql_real_escape_string($self_registration)."', private='".mysql_real_escape_string($private)."',
		forum='".mysql_real_escape_string($forum)."', document='".mysql_real_escape_string($document)."' WHERE id=1", $currentCourse);
	$message = $langGroupPropertiesModified;
}	// if $submit


// Delete all groups
elseif (isset($_REQUEST['delete']) && $is_adminOfCourse)
{
	$result = db_query("DELETE FROM student_group", $currentCourse);
	$result = db_query("DELETE FROM forums WHERE cat_id='1'", $currentCourse);

	// Moving all groups to garbage collector and re-creating an empty work directory
	$groupGarbage=uniqid(20);

	@mkdir("../../courses/garbage");
	rename("../../courses/$currentCourse/group", "../../courses/garbage/$groupGarbage");
	mkdir("../../courses/$currentCourse/group", 0777);

	// Delete all members of this group
	$delGroupUsers=db_query("DELETE FROM user_group", $currentCourse);
	$message = $langGroupsDeleted;
}

// Delete one group
elseif (isset($_REQUEST['delete_one']) && $is_adminOfCourse)
{
	// Moving group directory to garbage collector
	$groupGarbage=uniqid(20);
	$sqlDir=db_query("SELECT secretDirectory, forumId FROM student_group WHERE id='$id'", $currentCourse);
	while ($myDir = mysql_fetch_array($sqlDir)) {
		rename("../../courses/$currentCourse/group/$myDir[secretDirectory]",
		"../../courses/garbage/$groupGarbage");
		db_query("DELETE FROM forums WHERE cat_id='1' AND forum_id='$myDir[forumId]'", $currentCourse);
	}

	// Deleting group record in table
	$result = db_query("DELETE FROM student_group WHERE id='$id'", $currentCourse);
	// Delete all members of this group
	$delGroupUsers=db_query("DELETE FROM user_group WHERE team='$id'", $currentCourse);
	$message = $langGroupDel;
}

// Empty all groups
elseif (isset($_REQUEST['empty'])  && $is_adminOfCourse) {
	$result = db_query("DELETE FROM user_group", $currentCourse);
	$result2 = db_query("UPDATE student_group SET tutor='0'", $currentCourse);
	$message = $langGroupsEmptied;
}


// Fill all groups
elseif (isset($_REQUEST['fill']) && $is_adminOfCourse) {
	$resGroups = db_query("SELECT id, maxStudent FROM student_group");
	while (list($idGroup,$places) = mysql_fetch_array($resGroups)) {
		$placeAvailableInGroups[$idGroup]= $places;
	}

	$resUsers = db_query($sqlUsers = "select user, team from user_group");
	while (list($idUser, $idGroup) = mysql_fetch_array($resUsers)) {
		$placeAvailableInGroups[$idGroup]--;
		if ($placeAvailableInGroups[$idGroup] <= 0) {
        		unset($placeAvailableInGroups[$idGroup]);
                }
	}
	$sqlUserSansGroupe= "SELECT cu.user_id FROM `$mysqlMainDb`.cours_user cu
			LEFT JOIN `$currentCourse`.user_group ug on ug.user = cu.user_id
			WHERE cu.code_cours='$currentCourse'
			AND cu.statut=5 AND ug.user is null AND cu.tutor=0";
	$resUserSansGroupe= db_query($sqlUserSansGroupe);
	while (isset($placeAvailableInGroups) and is_array($placeAvailableInGroups) and (!empty($placeAvailableInGroups)) and list($idUser) = mysql_fetch_array($resUserSansGroupe))
	{
		$idGroupChoisi = array_keys($placeAvailableInGroups, max($placeAvailableInGroups));
		$idGroupChoisi = $idGroupChoisi[0];
		$userOfGroups[$idGroupChoisi][] = $idUser;
		$placeAvailableInGroups[$idGroupChoisi]--;
		if ($placeAvailableInGroups[$idGroupChoisi] <= 0) {
        		unset($placeAvailableInGroups[$idGroupChoisi]);
                }
	}

	// NOW we have $userOfGroups containing new affectation. We must write this in database
	if (isset($userOfGroups) and is_array($userOfGroups)) {
		reset($userOfGroups);
		while (list($idGroup,$users) = each($userOfGroups)) {
			while (list(,$idUser) = each($users)) {
				$sqlInsert = "INSERT INTO user_group SET user='$idUser', team='$idGroup'";
				db_query($sqlInsert);
			}
		}
	} else {
		// no student without groups
	}
	$message = $langGroupFilledGroups;
}	// FILL

/*****************************************
	admin only
*****************************************/

// Determine if uid is tutor for this course
$sqlTutor=db_query("SELECT tutor FROM `$mysqlMainDb`.cours_user
		WHERE user_id='$uid' AND code_cours='$currentCourse'");
while ($myTutor = mysql_fetch_array($sqlTutor)) {
	$tutorCheck=$myTutor['tutor'];
}


if ($is_adminOfCourse) {

	// Show DB messages
	if(isset($message))
	{
		$tool_content .= "<p class=\"success_small\">$message</p><br />";
	}
	unset($message);

	$tool_content .= "<table width=\"99%\" align=\"left\" class=\"Group_Operations\">
	<thead>
	<tr>
	<td width=\"50%\">&nbsp;<a href=\"group_creation.php\" class=\"operations_container\">$langNewGroupCreate</a></td>
	<td width=\"50%\"><div align=\"right\"><a href=\"".$_SERVER['PHP_SELF']."?delete=yes\" onClick=\"return confirmation('delall');\">$langDeleteGroups</a>&nbsp;</div></td>
	</tr>
	<tr>
	<td>&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?fill=yes\">$langFillGroups</a></td>
	<td><div align=\"right\"><a href=\"".$_SERVER['PHP_SELF']."?empty=yes\" onClick=\"return confirmation('emptyall');\">$langEmtpyGroups</a>&nbsp;</div></td>
	</tr>
	</thead></table><br /><br /><br />";

	// ---------- display properties ------------------------
	$tool_content .= "<table class='FormData' align='center' style='border: 1px solid #CAC3B5;'>
	<tbody>
	<tr class='odd'>
	<td colspan='2' class='right'><a href='group_properties.php'>$langPropModify</a> 
	<img src='../../template/classic/img/edit.gif' align='middle' border='0' title='$langEdit'></td>
	</tr>
	<tr>
	<td><b>$langGroupsProperties</b></td>
	<td align='right'><b>$langGroupAccess</b></td>
	</tr>";

	$resultProperties=db_query("SELECT id, self_registration, private, forum, document 
			FROM group_properties WHERE id=1", $currentCourse);
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
		$tool_content .= "<tr><td>";
		if($myProperties['self_registration']==1)
		{
			$tool_content .= "$langGroupAllowStudentRegistration</td><td align=\"right\">
			<font color=\"green\">$langYes</font>";
		}
		else
		{
			$tool_content .= "$langGroupAllowStudentRegistration</td><td align=\"right\">
			<font color=\"red\">$langNo</font>";
		}
		$tool_content .= "</td></tr>
		<tr><td colspan=2 class=\"left\"><b>$langTools</b></td></tr>
		<tr><td>";

		if($myProperties['forum']==1) {
			$tool_content .= "$langGroupForum</td><td align=\"right\"><font color=\"green\">$langYes</font>";
			$fontColor="black";
		} else {
			$tool_content .= "$langGroupForum</td><td align=\"right\">
			<font color=\"red\">$langNo</font>";$fontColor="silver";
		}
		$tool_content .= "</td></tr><tr><td>";
		if($myProperties['private']==1) {
			$tool_content .= "$langForumType</td><td align=\"right\">$langForumClosed";
		}
		else
		{
			$tool_content .= "$langForumType</td><td align=\"right\">$langForumOpen";
		}
		$tool_content .= "</td></tr><tr><td>";
		if($myProperties['document']==1)
		{
			$tool_content .= "$langDoc</td><td align=\"right\"><font color=\"green\">$langYes</font>";
		}
		else
		{
			$tool_content .= "$langDoc</td><td align=\"right\"><font color=\"red\">$langNo</font>";
		}
		$tool_content .= "</td></tr>";
	}	// while loop
	$tool_content .= "</tbody></table>";

	$groupSelect=db_query("SELECT id, name, tutor, maxStudent FROM student_group", $currentCourse);
	$myIterator=0;
	$num_of_groups = mysql_num_rows($groupSelect);
	// groups list
	if ($num_of_groups > 0) {
		$tool_content .= "<br />
		<table width=\"99%\" align=\"left\" class=\"GroupList\">
		<tbody>
		<tr>
		<th colspan=\"2\" class=\"GroupHead\"><div align=\"left\">$langGroupName</div></th>
		<th width='15%' class=\"GroupHead\">$langGroupTutor</th>
		<th  class=\"GroupHead\">$langRegistered</th>
		<th  class=\"GroupHead\">$langMax</th>
		<th width='50' class=\"GroupHead\">$langActions</th>
		</tr>";
	} else {
		$tool_content .= "<p>&nbsp;</p><p class=\"caution_small\">$langNoGroup</p>";
	}

	while ($group = mysql_fetch_array($groupSelect)) {
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team='".$group["id"]."'", $currentCourse);
		$countRegistered = mysql_num_rows($resultRegistered);
		if ($myIterator%2 == 0) {
			$tool_content .= "<tr>";
		} else {
			$tool_content .= "<tr class=\"odd\">";
		}
		$tool_content .= "<td width='2%'>
		<img src='../../template/classic/img/arrow_grey.gif' title='bullet' border='0'></td><td>
		<div align='left'>
		<a href='group_space.php?userGroupId=".$group["id"]."'>".$group["name"]."</a></div></td>";
		$tool_content .= "<td width='35%'>".uid_to_name($group['tutor'])."</td>";
      		$tool_content .= "<td><div class=\"cellpos\">".$countRegistered."</div></td>";
		if ($group['maxStudent'] == 0) {
			$tool_content .= "<td><div class=\"cellpos\">-</div></td>";
		} else {
			$tool_content .= "
      			<td><div class=\"cellpos\">".$group["maxStudent"]."</div></td>";
		}
		$tool_content .= "<td width='10%'><div class=\"cellpos\">
		<a href=\"group_edit.php?userGroupId=".$group["id"]."\">
		<img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"".$langEdit."\"></a>
		<a href=\"".$_SERVER['PHP_SELF']."?delete_one=yes&id=".$group["id"]."\" onClick=\"return confirmation('".addslashes($group["name"])."');\">
		<img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"".$langDelete."\"></a></div></td>
    		</tr>";
		$totalRegistered = $totalRegistered+$countRegistered;
		$myIterator++;
	}	// while loop

	$coursUsersSelect=db_query("SELECT user_id FROM cours_user
		WHERE code_cours='$currentCourse'
		AND statut=5 AND tutor=0", $mysqlMainDb);
	$countUsers = mysql_num_rows($coursUsersSelect);
	$countNoGroup=($countUsers-$totalRegistered);
	$tool_content .= "</tbody></table><p>&nbsp;</p>";
	$tool_content .= "<table width=\"99%\" class=\"FormData\" style=\"border: 1px solid #edecdf;\">
	<tbody><tr>
	<td class=\"odd\">
	<p><b>$totalRegistered</b> $langGroupStudentsInGroup<br></p>
	<p><b>$countNoGroup</b> $langGroupNoGroup<br></p>
	<p><b>$countUsers</b> $langGroupStudentsRegistered <div align=\"right\">($langGroupUsersList)</div></p>
	</td></tr></tbody></table>\n";
}	// end prof only

// else student view
else {

	// Check if Self-registration is allowed. 1=allowed, 0=not allowed
	$sqlSelfReg=db_query("SELECT self_registration FROM group_properties", $currentCourse);
	while ($mySelfReg = mysql_fetch_array($sqlSelfReg)) {
		$selfRegProp=$mySelfReg['self_registration'];
	}
	// Guest users aren't allowed to register in a group
	if ($statut == 10) {
		$selfRegProp = 0;
	}
	// Check which group student is a member of
	$findTeamUser=db_query("SELECT team FROM user_group WHERE user='$uid'", $currentCourse);
	while ($myTeamUser = mysql_fetch_array($findTeamUser)) {
		$myTeam=$myTeamUser['team'];
	}

	$groupSelect=db_query("SELECT id, name, maxStudent, tutor FROM student_group", $currentCourse);
	$num_of_groups = mysql_num_rows($groupSelect);
	// groups list
	if ($num_of_groups > 0) {
		$tool_content .= "<table width=\"99%\" align=\"left\" class=\"GroupList\">
		<thead><tr>
		<th colspan=\"2\" class=\"GroupHead\"><div align=\"left\">$langGroupName</div></th>
		<th width='15%' class=\"GroupHead\">$langGroupTutor</th>";
		// If self-registration allowed by admin
		if($selfRegProp == 1) {
			$tool_content .= "<th width=\"50\" class=\"GroupHead\">$langRegistration</th>";
		}
		$tool_content .= "<th width=\"50\" class=\"GroupHead\">$langRegistered</th>
		<th width=\"50\" class=\"GroupHead\">$langMax</th>
		</tr></thead><tbody>";
	} else {
		$tool_content .= "<p class=\"alert1\">$langNoGroup</p>";
	}
	$k = 0;
	while ($group = mysql_fetch_array($groupSelect)) {
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team='".$group["id"]."'", $currentCourse);
		$countRegistered = mysql_num_rows($resultRegistered);
		if ($k%2 == 0) {
			$tool_content .= "\n<tr>";
		} else {
			$tool_content .= "\n<tr class=\"odd\">";
		}
		$tool_content .= "<td width='2%'><img src='../../template/classic/img/arrow_grey.gif' title='bullet' border='0'></td><td><div align=\"left\">";
		// Allow student to enter group only if member
		if(@($tutorCheck == 1)) {
			if ($uid == $group['tutor']) {
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
				<span style='color:#900; weight:bold;'>($langOneMyGroups)</span>";
			} else {
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>";
			}
		}
		// STUDENT VIEW
		else {
			if(isset($myTeam) && $myTeam == $group['id']) {
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>&nbsp;&nbsp;($langMyGroup)";
			} else {
				$tool_content .= $group['name'];
			}
		}	
		$tool_content .= "</div></td>";
		$tool_content .= "<td width='35%'><div align='center'>".uid_to_name($group['tutor'])."</div></td>";
		// SELF REGISTRATION
		// If self-registration allowed by admin
		if($selfRegProp == 1)
		{
			$tool_content .= "<td><div align='center'>";
			if((!$uid) OR (isset($myTeam)) OR 
				(($countRegistered>=$group['maxStudent']) AND ($group['maxStudent']>>0))) {
				$tool_content .= "-";
			} else {
				$tool_content .= "<a href=\"group_space.php?selfReg=1&userGroupId=".$group["id"]."\">$langRegistration</a>";
			}
			$tool_content .= "</div></td>";
		}	// If self reg allowed by admin
		$tool_content .= "<td><div align='center'>".$countRegistered."</div></td>";
		if ($group['maxStudent'] == 0) {
			$tool_content .= "<td><div align='center'>-</div></td>";
		} else {
			$tool_content .= "<td><div align='center'>".$group["maxStudent"]."</div></td>";
		}
		$tool_content .= "</tr>";
		$totalRegistered=($totalRegistered+$countRegistered);
		$k++;
	}	// while loop
	$tool_content .= "</tbody></table>";
} 	// else student view
add_units_navigation(TRUE);
if ($is_adminOfCourse) {
	draw($tool_content, 2, 'group', $head_content);
} else {
	draw($tool_content, 2, 'group');
}
?>
