<?
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

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GROUPS');
/**************************************/

// Remove old group identification if
// possible entrance in another group space (admin for instance)
session_unregister("secretDirectory");
session_unregister("userGroupId");
session_unregister("forumId");

$currentCourse=$dbname;
mysql_select_db("$currentCourse");

$nameTools = $langGroupManagement;

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
		if (confirm("'.$langEmptyGroupAllWarn.' ?"))
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
############## GROUP MODIFICATIONS ###############################

// Group creation
if(isset($_REQUEST['creation']) && $is_adminOfCourse) {

	// For all Group forums, cat_id=2

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
			VALUES ('','$langForumGroup $lastId','',2,1,0,0,1,1,0)");


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
	while ($myDir = mysql_fetch_array($sqlDir))
	{
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
	$sqlGroups = "select id, maxStudent from student_group";
	$resGroups = db_query($sqlGroups);
	while (list($idGroup,$places) = mysql_fetch_array($resGroups))
	{
		$placeAivailableInGroups[$idGroup]= $places;
	}

	$sqlUsers = "select user, team from user_group";
	$resUsers = db_query($sqlUsers);
	while (list($idUser, $idGroup) = mysql_fetch_array($resUsers))
	{
		$placeAivailableInGroups[$idGroup]--;
	}
	$sqlUserSansGroupe= "SELECT cu.user_id FROM `$mysqlMainDb`.cours_user cu
			LEFT JOIN `$currentCourse`.user_group ug on ug.user = cu.user_id
			WHERE cu.code_cours='$currentCourse'
			AND cu.statut=5 AND ug.user is null AND cu.tutor=0";		
	$resUserSansGroupe= db_query($sqlUserSansGroupe);
	while (isset($placeAivailableInGroups) and is_array($placeAivailableInGroups) and (!empty($placeAivailableInGroups)) and list($idUser) = mysql_fetch_array($resUserSansGroupe))
	{
		$idGroupChoisi = array_keys($placeAivailableInGroups,max($placeAivailableInGroups));
		$idGroupChoisi = $idGroupChoisi[0];
		$userOfGroups[$idGroupChoisi][]=$idUser;
		$placeAivailableInGroups[$idGroupChoisi]--;
		if ($placeAivailableInGroups[$idGroupChoisi] <= 0)
		unset($placeAivailableInGroups[$idGroupChoisi]);
	}

	// NOW we have $userOfGroups containing new affectation. We must  write this in database
	if (isset($userOfGroups) and is_array($userOfGroups))
	{
		reset($userOfGroups);
		while (list($idGroup,$users)=each($userOfGroups))
		{
			while (list(,$idUser)=each($users))
			{
				$sqlInsert ="INSERT INTO user_group SET user = '".$idUser."',team = '".$idGroup."';";
				db_query($sqlInsert);
			}
		}
	}
	else
	{
		// no student without groups
	}
	$message = $langGroupFilledGroups;
}	// FILL

######################## TITLE AND HELP ##########################

/*****************************************
ADMIN AND TUTOR ONLY
*****************************************/

// Determine if uid is tutor for this course
$sqlTutor=db_query("SELECT tutor FROM `$mysqlMainDb`.cours_user
				WHERE user_id='$uid' AND code_cours='$currentCourse'");
while ($myTutor = mysql_fetch_array($sqlTutor))
{
	$tutorCheck=$myTutor['tutor'];
}

if ($is_adminOfCourse) {


	$tool_content .= "
<div id=\"operations_container\">
	<ul id=\"opslist\">
	<li><a href=\"group_creation.php\">$langNewGroupCreate</a></li>
	<li><a href=\"".$_SERVER['PHP_SELF']."?delete=yes\" onClick=\"return confirmation('delall');\">$langDeleteGroups</a></li>
	<li><a href=\"".$_SERVER['PHP_SELF']."?fill=yes\">$langFillGroups</a></li>
	<li><a href=\"".$_SERVER['PHP_SELF']."?empty=yes\" onClick=\"return confirmation('emptyall');\">$langEmtpyGroups</a></li>
	</ul></div>
	";
	// Show DB messages
	if(isset($message))
	{
		$tool_content .= "
		<table width=\"99%\">
		<thead>
		<tr><td class=\"success\">
		<p><b>$message</b></p>
		
		</td>
		</tr>
		</thead>
		</table>
		<br>";
	}
	unset($message);
	#################### SHOW PROPERTIES ######################
	$tool_content .= <<<tCont3
	<table width="99%">
	<thead>
	<tr>
	<th>

	$langGroupsProperties
	</th>
	<th>

	$langState
	</th>
	</tr>
	</thead>
tCont3;

	$resultProperties=db_query("SELECT id, self_registration, private, forum, document FROM group_properties WHERE id=1", $currentCourse);
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
		$tool_content .= "<tr><td>";
		if($myProperties['self_registration']==1)
		{
			$tool_content .= "$langGroupAllowStudentRegistration</td><td>$langYes";
		}
		else
		{
			$tool_content .= "$langGroupAllowStudentRegistration</td>
				<td>$langNo";
		}
		$tool_content .= "</td></tr><tr><td colspan=2 class=\"category\">$langTools</td></tr>
	<tr><td>";

		if($myProperties['forum']==1)
		{
			$tool_content .= "$langGroupForum</td><td>$langYes";
			$fontColor="black";
		}
		else
		{
			$tool_content .= "$langGroupForum</td>
				<td>$langNo";
			$fontColor="silver";
		}

		$tool_content .= "</td></tr><tr><td>";

		if($myProperties['private']==1)
		{
			$tool_content .= "$langForumType</td>
				<td >$langPrivate";
		}
		else
		{
			$tool_content .= "$langForumType</td>
				<td >$langPublic";
		}
		$tool_content .= "</td></tr><tr ><td>";

		if($myProperties['document']==1)
		{
			$tool_content .= "$langGroupDocument</td><td >$langYes";
		}
		else
		{
			$tool_content .= "$langGroupDocument</td>
				<td >$langNo";
		}
		$tool_content .= "</td></tr>";
	}	// while loop
	$tool_content .= "</table>";
	$tool_content .= "<p>
		<a href=\"group_properties.php\">".$langPropModify."</a>
		</p>";

	############## GROUPS LIST ######################################

	$tool_content .= "
			<br>
			<table width=\"99%\">
			<thead>
				<tr> 
					<th align=\"left\">
						$langExistingGroups
					</th>
					<th>
						$langRegistered
					</th>
					<th>
						$langMax
					</th>
					<th>
						$langEdit
					</th>
					<th>
						$langDelete
					</th>
				</tr>
			</thead>
			<tbody>";
	//	mysql_select_db("$currentCourse");
	$groupSelect=db_query("SELECT id, name, maxStudent FROM student_group", $currentCourse);

	$totalRegistered=0;
	$myIterator=0;
	while ($group = mysql_fetch_array($groupSelect))
	{
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team='".$group["id"]."'", $currentCourse);
		$countRegistered = mysql_num_rows($resultRegistered);

		if ($myIterator%2==0) {
			$tool_content .= "<tr>";
		}
		elseif ($myIterator%2==1) {
			$tool_content .= "<tr class=\"odd\">";
		}
		$tool_content .= "
				
					<td><div class=\"cellpos\">
						<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
					</div>
					</td>
					<td><div class=\"cellpos\">
						".$countRegistered."
					</div>
					</td>";
		if ($group['maxStudent']==0) {
			$tool_content .= "<td><div class=\"cellpos\">-</div></td>";
		} else {
			$tool_content .= "<td><div class=\"cellpos\">".$group["maxStudent"]."</div></td>";
		}
		$tool_content .= "
			<td><div class=\"cellpos\">
	<a href=\"group_edit.php?userGroupId=".$group["id"]."\"><img src=\"../../template/classic/img/edit.gif\" border=\"0\" alt=\"".$langEdit."\"></a>
	</div>
	</td>
	<td><div class=\"cellpos\">
	<a href=\"".$_SERVER['PHP_SELF']."?delete_one=yes&id=".$group["id"]."\" onClick=\"return confirmation('".addslashes($group["name"])."');\">
	<img src=\"../../template/classic/img/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
	</div>
	</td>
	</tr>";

		$totalRegistered=($totalRegistered+$countRegistered);
		$myIterator++;
	}	// while loop
	$tool_content .= <<<tCont4
	</tbody>
	</table>
	<br>
 
tCont4;

	//	mysql_select_db($mysqlMainDb);
	$coursUsersSelect=db_query("
	SELECT user_id FROM cours_user WHERE code_cours='$currentCourse' 
			AND statut=5 AND tutor=0", $mysqlMainDb);
	$countUsers = mysql_num_rows($coursUsersSelect);
	$countNoGroup=($countUsers-$totalRegistered);

	$tool_content .= "
		<p><b>$totalRegistered</b> $langGroupStudentsInGroup<br></p>
		<p><b>$countNoGroup</b> $langGroupNoGroup<br></p>
		<p><b>$countUsers</b> $langGroupStudentsRegistered ($langGroupUsersList)</p>";
	$tool_content .= <<<tCont5

</form>
		</td>
	</tr>
	<tr> 
		<td width="100%" colspan="3">

tCont5;
}	// end prof only

####  STUDENT VIEW  ###############

// else student view
else {

	// Check if Self-registration is allowed. 1=allowed, 0=not allowed
	$sqlSelfReg=db_query("SELECT self_registration FROM group_properties", $currentCourse);
	while ($mySelfReg = mysql_fetch_array($sqlSelfReg))
	{
		$selfRegProp=$mySelfReg['self_registration'];
	}

	// Guest users aren't allowed to register in a group
	if ($statut == 10) {
		$selfRegProp = 0;
	}

	// Check which group student is a member of
	$findTeamUser=db_query("SELECT team FROM user_group WHERE user='$uid'", $currentCourse);
	while ($myTeamUser = mysql_fetch_array($findTeamUser))
	{
		$myTeam=$myTeamUser['team'];
	}

	$tool_content .= "
	<table width=\"99%\">
	<thead>
	<tr> 
	<th>
	$langExistingGroups
	</th>";

	// If self-registration allowed by admin
	if($selfRegProp==1) {
		$tool_content .= "<th>
			
			$langGroupSelfRegistration
			
			</th>";
	}

	$tool_content .= "<th>$langRegistered</th>
	<th>$langMax</th>
	</tr></thead>
	<tbody>";

	//	mysql_select_db("$currentCourse");

	$groupSelect=db_query("SELECT id, name, maxStudent, tutor FROM student_group", $currentCourse);

	$totalRegistered=0;

	while ($group = mysql_fetch_array($groupSelect)) {
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team='".$group["id"]."'", $currentCourse);
		$countRegistered = mysql_num_rows($resultRegistered);
		$tool_content .= "<tr><td>";

		// Allow student to enter group only if member

		// TUTOR SEES ALL GROUPS AND KNOWS WHICH ARE HIS
		if(@($tutorCheck==1)) {
			if ($uid==$group['tutor']) {
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
					($langOneMyGroups)";
			} else {
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>";
			}
		}

		// STUDENT VIEW
		else {
			if(isset($myTeam) && $myTeam==$group['id'])	{
				$tool_content .= "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
						($langMyGroup)";
			} else {
				$tool_content .= $group['name'];
			}
		}	// else

		$tool_content .= "</td>";


		// SELF REGISTRATION

		// If self-registration allowed by admin
		if($selfRegProp==1)
		{
			$tool_content .= "<td>";
			if((!$uid) OR (isset($myTeam)) OR (($countRegistered>=$group['maxStudent']) AND ($group['maxStudent']>>0)))
			{
				$tool_content .= "&nbsp;-";
			}
			else
			{
				$tool_content .= "&nbsp;<a href=\"group_space.php?selfReg=1&userGroupId=".$group["id"]."\">$langGroupSelfRegInf</a>";
			}
			$tool_content .= "</td>";
		}	// If self reg allowed by admin

		$tool_content .= "<td>".$countRegistered."</td>";
		if ($group['maxStudent']==0)
		{
			$tool_content .= "<td>-</td>";
		}
		else
		{
			$tool_content .= "<td>".$group["maxStudent"]."</td>";
		}
		$tool_content .= "</tr>";
		$totalRegistered=($totalRegistered+$countRegistered);

	}	// while loop
	$tool_content .= "</tbody></table>";

} 	// else student view
if ($is_adminOfCourse) {
	draw($tool_content, 2, 'group', $head_content);
} else {
	draw($tool_content, 2, 'group');
}
?>
