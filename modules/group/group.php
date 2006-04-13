<?
$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
include ('../../include/init.php');

$local_style = " body,p,td {font-family: Arial, Helvetica, sans-serif; font-size: 10pt}
		.select {border-color:blue;border-width : 3px;}
		.box {  width: 200px}
";

// Remove old group identification if 
// possible entrance in another group space (admin for instance)
session_unregister("secretDirectory");
session_unregister("userGroupId");
session_unregister("forumId");

$currentCourse=$dbname;
mysql_select_db("$currentCourse");

$nameTools = $langGroupManagement;
begin_page();

############## GROUP MODIFICATIONS ###############################

// Group creation
if(isset($_REQUEST['creation']))

{

// For all Group forums, cat_id=2

	for ($i = 1; $i <= $group_quantity; $i++)
	{
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
	rename("../../$currentCourse/group", "../../courses/garbage/$groupGarbage");
	mkdir("../../$currentCourse/group", 0777);

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
		rename("../../$currentCourse/group/$myDir[secretDirectory]", 
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
elseif (isset($_REQUEST['empty']))
{
	$result = mysql_query("DELETE FROM user_group");
	$result2 = mysql_query("UPDATE student_group SET tutor='0'");
	$message = $langGroupsEmptied;
}


// Fill all groups
elseif (isset($_REQUEST['fill']))
{
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
?>
		</td>
	</tr>
<tr><td>&nbsp;</td></tr>
<?

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

if ($is_adminOfCourse)
{
?>
	<tr>
	<td colspan="2"> 
	<table width="100%" border="0" cellspacing="5" cellpadding="2">
<?
// Show DB messages
	if(isset($message))
	{
		echo "<tr><td bgcolor=\"#9999FF\" colspan=\"2\">
		<font face=\"arial, helvetica\">$message</font>
		</td>
		</tr>";
	}
	unset($message);
?>	
	<tr align="center" bgcolor="<?= $color2 ?>">
	<td>
	<b><a href="group_creation.php"><?= $langNewGroupCreate;?></a>
	</b>
	</td>
	<td>
<?php echo "<a href=\"".$_SERVER['PHP_SELF']."?delete=yes\">$langDeleteGroups</a>" ?>
	</td>
	</tr>
	<tr align="center" bgcolor="<?php echo $color2; ?>" >
	<td>
	<a href="<?= $_SERVER['PHP_SELF'] ?>?fill=yes"><?= $langFillGroups ?></a>
	</td>
	<td bgcolor="<?= $color2 ?>">
	<a href="<?= $_SERVER['PHP_SELF'] ?>?empty=yes"><?= $langEmtpyGroups ?></a>
	</td>
	</tr>
	</table>
	<br>

	<!-- #################### SHOW PROPERTIES ###################### -->
	<table border="0" width="100%" cellspacing="2" cellpadding="2" bgcolor="<? $color2 ?>">
	<tr bgcolor="#000066">
	<td valign="top">
	<b>
	<font color="white">
	<?= $langGroupsProperties ?>
	</font>
	</b>
	</td>
	<td align="right">
	<b>
	<font color="white">
	<?= $langState?>
	</font>
	</b>
	</td>
	</tr>
<?
	$resultProperties=mysql_query("SELECT id, self_registration, private, forum, document FROM group_properties WHERE id=1");
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
	echo "<tr><td>";
		if($myProperties['self_registration']==1)
		{
			echo "$langGroupAllowStudentRegistration</td><td align=\"right\">$langYes";
		}
		else 
		{
			echo "<font color=\"silver\">$langGroupAllowStudentRegistration</font></td>
				<td align=\"right\"><font color=\"silver\">$langNo</font>";
		}
echo "</td></tr><tr><td colspan=2><b>$langTools</b></td></tr>
	<tr bgcolor=\"white\"><td>";

		if($myProperties['forum']==1)
		{
			echo "$langGroupForum</td><td align=\"right\">$langYes";
			$fontColor="black";
		}
		else
		{
			echo "<font color=\"silver\">$langGroupForum</font></td>
				<td align=\"right\"><font color=\"silver\">$langNo</font>";
			$fontColor="silver";
		}
		
echo "</td></tr><tr><td>";

		if($myProperties['private']==1)
		{
			echo "<font color=\"$fontColor\">$langForumType</font></td>
				<td align=\"right\"><font color=\"$fontColor\">$langPrivate</font>";
		}
		else 
		{
			echo "<font color=\"$fontColor\">$langForumType</font></td>
				<td align=\"right\"><font color=\"$fontColor\">$langPublic</font>";
		}
echo "</td></tr><tr bgcolor=\"white\"><td>";

		if($myProperties['document']==1)
		{
			echo "$langGroupDocument</td><td align=\"right\">$langYes";
		}
		else
		{
			echo "<font color=\"silver\">$langGroupDocument</font></td>
				<td align=\"right\"><font color=\"silver\">$langNo</font>";
		}
	echo "</td></tr>";	
	}	// while loop
	echo "<tr>
		<td colspan=2 align=right>
		<a href=\"group_properties.php\">".$langPropModify."</a>
		</td>
		</tr>
	</table>";

############## GROUPS LIST ######################################

	echo "
			<br>
			<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">
				<tr bgcolor=\"#000066\" align=\"center\"> 
					<td align=\"left\">
						<b>
							<font color=\"#FFFFFF\">
								&nbsp;
								$langExistingGroups
							</font>
						</b>
					</td>
					<td>
						<b><font color=\"#FFFFFF\">$langRegistered</font></b>
					</td>
					<td>
						<b><font color=\"#FFFFFF\">$langMax</font></b>
					</td>
					<td>
						<b><font color=\"#FFFFFF\">$langEdit</font></b>
					</td>
					<td>
						<b><font color=\"#FFFFFF\">$langDelete</font></b>
					</td>
				</tr>";
	mysql_select_db("$currentCourse");
	$groupSelect=mysql_query("SELECT id, name, maxStudent FROM student_group");
	
	$totalRegistered=0;	
	while ($group = mysql_fetch_array($groupSelect)) 
	{
		// Count students registered in each group
		$resultRegistered = mysql_query("SELECT id FROM user_group WHERE team='".$group["id"]."'");
		$countRegistered = mysql_num_rows($resultRegistered);
		echo "
				<tr align=\"center\"> 
					<td align=\"left\">
						<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
					</td>
					<td>
						".$countRegistered."
					</td>";
		if ($group['maxStudent']==0)
		{
			echo "<td>-</td>";
		}
		else
		{
			echo "<td>".$group["maxStudent"]."</td>";
		}
		echo "
			<td>
	<a href=\"group_edit.php?userGroupId=".$group["id"]."\"><img src=\"../../images/renommer.gif\" border=\"0\" alt=\"".$langEdit."\"></a>
	</td>
	<td>
	<a href=\"".$_SERVER['PHP_SELF']."?delete_one=yes&id=".$group["id"]."\">
	<img src=\"../../images/supprimer.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
	</td>
	</tr>";

		$totalRegistered=($totalRegistered+$countRegistered);

	}	// while loop
?>
	</table>
	<br>
	<center>
	<table border="0" cellspacing="0" width="100%" cellpadding="6">
	<tr> 
	<td valign="middle"> 

<?
	mysql_select_db($mysqlMainDb);
	$coursUsersSelect=mysql_query("
	SELECT user_id FROM cours_user WHERE code_cours='$currentCourse' 
			AND statut=5 AND tutor=0");
	$countUsers = mysql_num_rows($coursUsersSelect);
	$countNoGroup=($countUsers-$totalRegistered);

	echo "<hr noshade size=1>
		<b>$totalRegistered</b> $langGroupStudentsInGroup<br>
		<b>$countNoGroup</b> $langGroupNoGroup<br>
		<b>$countUsers</b> $langGroupStudentsRegistered <small>($langGroupUsersList)</small>.";
?>
		</td>
	</tr>
</table>
</center>
</form>
		</td>
	</tr>
	<tr> 
		<td width="100%" colspan="3">

<?
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

echo "<tr><td colspan=2><br>
	<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"600\">
	<tr bgcolor=\"#000066\" align=\"center\"> 
	<td align=\"left\">
	<b>
	<font color=\"#FFFFFF\">
	&nbsp;
	$langExistingGroups
	</font>
	</b>
	</td>";

// If self-registration allowed by admin
if($selfRegProp==1)
{
	echo "<td align=\"left\">uid : $uid
			<b><font color=\"#FFFFFF\">&nbsp;
			$langGroupSelfRegistration
			</font></b>
			</td>";
}

echo "<td><b><font color=\"#FFFFFF\">$langRegistered</font></b></td>
	<td><b><font color=\"#FFFFFF\">$langMax</font></b></td>
	</tr>";

	mysql_select_db("$currentCourse");

	$groupSelect=mysql_query("SELECT id, name, maxStudent, tutor FROM student_group");
	
	$totalRegistered=0;
	
	while ($group = mysql_fetch_array($groupSelect)) 
	{
		// Count students registered in each group
		$resultRegistered = mysql_query("SELECT id FROM user_group WHERE team='".$group["id"]."'");
		$countRegistered = mysql_num_rows($resultRegistered);
		echo "<tr align=\"center\"><td align=\"left\">";

			// Allow student to enter group only if member

			// TUTOR SEES ALL GROUPS AND KNOWS WHICH ARE HIS
			if(@($tutorCheck==1))
			{
				if ($uid==$group['tutor'])
				{
					echo "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
					($langOneMyGroups)";
				}
				else
				{
					echo "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>";
				}
			}
			
			// STUDENT VIEW
			else
			{
				if(isset($myTeam) && $myTeam==$group['id'])
				{
					echo "<a href=\"group_space.php?userGroupId=".$group["id"]."\">".$group["name"]."</a>
						($langMyGroup)";
				}
				else 
				{
					echo $group['name'];
				}
			}	// else
			
			echo "</td>";


			// SELF REGISTRATION

			// If self-registration allowed by admin
			if($selfRegProp==1)
			{
				echo "<td align=\"center\">";
				if((!$uid) OR (isset($myTeam)) OR (($countRegistered>=$group['maxStudent']) AND ($group['maxStudent']>>0)))
				{
					echo "&nbsp;-";
				}
				else
				{
					echo "&nbsp;<a href=\"group_space.php?selfReg=1&userGroupId=".$group["id"]."\">$langGroupSelfRegInf</a>";
				}
				echo "</td>";
			}	// If self reg allowed by admin

			echo "<td>".$countRegistered."</td>";
		if ($group['maxStudent']==0)
		{
			echo "<td>-</td>";
		}
		else
		{
			echo "<td>".$group["maxStudent"]."</td>";
		}
		echo "</tr>";
		$totalRegistered=($totalRegistered+$countRegistered);

	}	// while loop
?>
</table>
<?
	} 	// else student view
?>
		</td>
	</tr>
</table>
</body>
</html>

