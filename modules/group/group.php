<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Á full copyright notice can be read in "/info/copyright.txt".
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

############## GROUP MODIFICATIONS ###############################

// Group creation
if(isset($_REQUEST['creation'])) {

	// For all Group forums, cat_id=2

	for ($i = 1; $i <= $group_quantity; $i++) {
		// Creating a Unique Id path to group documents to try (!)
		// avoiding groups entering other groups area
		$secretDirectory=uniqid("");

		mkdir("../../courses/$currentCourse/group/$secretDirectory", 0777);

		// Write group description in student_group table. Contains path to group document dir.
		db_query("INSERT INTO student_group (maxStudent, secretDirectory)
				VALUES ('$group_max', '$secretDirectory')");

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


if(isset($_REQUEST['properties']))
{
	@mysql_query("UPDATE group_properties
			SET self_registration='$self_registration', private='$private',
			forum='$forum', document='$document' WHERE id=1"); 
	$message = $langGroupPropertiesModified;
}	// if $submit


// Delete all groups
elseif (isset($_REQUEST['delete']))
{
	$result = mysql_query("DELETE FROM student_group");

	$result = mysql_query("DELETE FROM forums WHERE cat_id='1'");

	// Moving all groups to garbage collector and re-creating an empty work directory
	$groupGarbage=uniqid(20);

	@mkdir("../../courses/garbage");
	rename("../../courses/$currentCourse/group", "../../courses/garbage/$groupGarbage");
	mkdir("../../courses/$currentCourse/group", 0777);

	// Delete all members of this group
	$delGroupUsers=mysql_query("DELETE FROM user_group");
	$message = $langGroupsDeleted;
}

// Delete one group
elseif (isset($_REQUEST['delete_one']))
{

	// Moving group directory to garbage collector
	$groupGarbage=uniqid(20);
	$sqlDir=mysql_query("SELECT secretDirectory, forumId FROM student_group WHERE id='$id'");
	while ($myDir = mysql_fetch_array($sqlDir))
	{
		rename("../../courses/$currentCourse/group/$myDir[secretDirectory]",
		"../../courses/garbage/$groupGarbage");

		mysql_query("DELETE FROM forums WHERE cat_id='1' AND forum_id='$myDir[forumId]'");
	}

	// Deleting group record in table
	$result = mysql_query("DELETE FROM student_group WHERE id='$id'");

	// Delete all members of this group
	$delGroupUsers=mysql_query("DELETE FROM user_group WHERE team='$id'");

	$message = $langGroupDel;
}

// Empty all groups
elseif (isset($_REQUEST['empty'])) {
	$result = mysql_query("DELETE FROM user_group");
	$result2 = mysql_query("UPDATE student_group SET tutor='0'");
	$message = $langGroupsEmptied;
}


// Fill all groups
elseif (isset($_REQUEST['fill'])) {
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
				echo $sqlInsert;
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
$tool_content .= "
		</td>
	</tr>
<tr><td>&nbsp;</td></tr>";


/*****************************************
ADMIN AND TUTOR ONLY
*****************************************/

// Determine if uid is tutor for this course
$sqlTutor=mysql_query("SELECT tutor FROM `$mysqlMainDb`.cours_user
				WHERE user_id='$uid' AND code_cours='$currentCourse'");
while ($myTutor = mysql_fetch_array($sqlTutor))
{
	$tutorCheck=$myTutor['tutor'];
}

if ($is_adminOfCourse) {

	// Show DB messages
	if(isset($message))
	{
		$tool_content .= "
		<table>
		<thead>
		<tr><td class=\"success\">
		$message
		</td>
		</tr>
		</thead>
		</table>
		<br>";
	}
	unset($message);
	$tool_content .= "

	<p><a href=\"group_creation.php\">$langNewGroupCreate</a></p>
	<p><a href=\"".$_SERVER['PHP_SELF']."?delete=yes\">$langDeleteGroups</a><p>
	<p><a href=\"".$_SERVER['PHP_SELF']."?fill=yes\">$langFillGroups</a></p>
	<p><a href=\"".$_SERVER['PHP_SELF']."?empty=yes\">$langEmtpyGroups</a></p>

	<br>";

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

	$resultProperties=mysql_query("SELECT id, self_registration, private, forum, document FROM group_properties WHERE id=1");
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
	mysql_select_db("$currentCourse");
	$groupSelect=mysql_query("SELECT id, name, maxStudent FROM student_group");

	$totalRegistered=0;
	$myIterator=0;
	while ($group = mysql_fetch_array($groupSelect))
	{
		// Count students registered in each group
		$resultRegistered = mysql_query("SELECT id FROM user_group WHERE team='".$group["id"]."'");
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
	<a href=\"group_edit.php?userGroupId=".$group["id"]."\"><img src=\"../../images/edit.gif\" border=\"0\" alt=\"".$langEdit."\"></a>
	</div>
	</td>
	<td><div class=\"cellpos\">
	<a href=\"".$_SERVER['PHP_SELF']."?delete_one=yes&id=".$group["id"]."\">
	<img src=\"../../images/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
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

	mysql_select_db($mysqlMainDb);
	$coursUsersSelect=mysql_query("
	SELECT user_id FROM cours_user WHERE code_cours='$currentCourse' 
			AND statut=5 AND tutor=0");
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
	$sqlSelfReg=mysql_query("SELECT self_registration FROM group_properties");
	while ($mySelfReg = mysql_fetch_array($sqlSelfReg))
	{
		$selfRegProp=$mySelfReg['self_registration'];
	}

	// Guest users aren't allowed to register in a group
	if ($statut == 10) {
		$selfRegProp = 0;
	}

	// Check which group student is a member of
	$findTeamUser=mysql_query("SELECT team FROM user_group WHERE user='$uid'");
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

	mysql_select_db("$currentCourse");

	$groupSelect=mysql_query("SELECT id, name, maxStudent, tutor FROM student_group");

	$totalRegistered=0;

	while ($group = mysql_fetch_array($groupSelect)) {
		// Count students registered in each group
		$resultRegistered = mysql_query("SELECT id FROM user_group WHERE team='".$group["id"]."'");
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

draw($tool_content, 2, 'group');
?>
