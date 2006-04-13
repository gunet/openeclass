<?

// Delete ancient possible other group values
session_unregister("secretDirectory");
session_unregister("userGroupId");
session_unregister("forumId");

$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
include('../../include/init.php');

$nameTools = $langGroupSpace;
$navigation[] = array ("url"=>"group.php", "name"=> $langGroupManagement);

begin_page();

########################### SQL SELF-REGISTRATION ################################

if(isset($registration) and $statut != 10)
{
	$sqlExist=mysql_query("SELECT id FROM `$dbname`.user_group 
				WHERE user='$uid' AND team='$userGroupId'");
				$countExist = mysql_num_rows($sqlExist);
	if($countExist==0 )
	{
		$sqlReg=mysql_query("INSERT INTO `$dbname`.user_group (user, team) VALUES ('$uid', '$userGroupId')");
		$message="<font color=red>$langGroupNowMember</font>";
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

	echo "&nbsp;</td>
		</tr><tr bgcolor=\"$color2\"><td colspan=2>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
		<tr>
		<td>
		<b>$langGroupName<br></b></td>";

	echo "<td align=right valign=top>";

	if ($is_adminOfCourse) 
	{
		echo "<a href=\"group_edit.php?userGroupId=$userGroupId\">$langEditGroup</a>";

	}
	elseif(isset($selfReg) AND ($uid))
	{
		echo "<a href=\"$_SERVER[PHP_SELF]?registration=1\">$langRegIntoGroup</a>"; 
	}
	elseif(isset($regDone))
	{
		echo "$message";
	}
	else
	{
		echo "&nbsp;";
	}

	echo "</td></tr>";
	echo "<tr><td colspan=2>$myGroup[name]
		<br><br>
		<b>$langGroupTutor</b><br>";

	$sqlTutor=mysql_query("SELECT tutor, user_id, nom, prenom, email, forumId 
					FROM `$mysqlMainDb`.user, student_group
					WHERE user.user_id=student_group.tutor
					AND student_group.id='$userGroupId'");
	$countTutor = mysql_num_rows($sqlTutor);

	if ($countTutor==0)
	{
		echo "$langGroupNoTutor<br><br>";	
	}
	else 
	{
		while ($myTutor = mysql_fetch_array($sqlTutor))
		{
			echo "$myTutor[nom] $myTutor[prenom] 
				<a href=mailto:$myTutor[email]>$myTutor[email]</a><br><br>";
		}	// while tutor

	}	// else

	echo "<b>$langGroupDescription</b><br>";

	// Show 'none' if no description
	$countDescription=strlen ($myGroup['description']);
	if(($countDescription <= 3))
	{
		echo "$langGroupNone<br><br>";
	}
	else
	{
		echo "$myGroup[description]<br><br>";
	}	// else
}	// while loop

echo "</td></tr>";

###################### TOOLS #############################

// Vars needed to determine group File Manager and group Forum
// They are unregistered when opening group.php once again.

session_register("secretDirectory");
session_register("userGroupId");
session_register("forumId");

echo "<tr>";

if(isset($selfReg))
{
	echo "<td>&nbsp;</td>";
}
else
{
	echo "<td valign=\"top\"><b>$langTools</b><br>";

	$resultProperties=mysql_query("SELECT id, self_registration, private, forum, document 
					FROM group_properties WHERE id=1");
	while ($myProperties = mysql_fetch_array($resultProperties))
	{
		// Drive members into their own forum
		if($myProperties['forum']==1){
			echo "<a href=\"../phpbb/viewforum.php?forum=$forumId\">$langForums</a>";
		}
		echo "<br>";

		// Drive members into their own File Manager
		if($myProperties['document']==1){
			echo "<a href=\"document.php?userGroupId=$userGroupId\">$langDocuments</a>";
		}
	}	// while loop

if ($is_adminOfCourse)
{
	echo "<br>";
	echo "<a href=\"group_email.php?userGroupId=$userGroupId\">$langEmailGroup</a>";
}
	echo "</td>";
}

################ MEMBERS ################################
echo "<td valign=\"top\" align=\"left\"><b>$langGroupMembers</b>&nbsp;&nbsp;<br>"; 

$resultMember=mysql_query("SELECT nom, prenom, email, am
			FROM `$mysqlMainDb`.user, user_group 
			WHERE user_group.team='$userGroupId' 
			AND user_group.user=$mysqlMainDb.user.user_id");
$countMember = mysql_num_rows($resultMember);

if(($countMember==0))
{
	echo "$langGroupNoneMasc<br><br>";
}
else
{
	while ($myMember = mysql_fetch_array($resultMember))
	{
		echo "$myMember[prenom] $myMember[nom]";
		if (!empty($myMember['am'])) {
			echo " ($myMember[am])";
		}
		echo ", <a href=mailto:$myMember[email]>$myMember[email]</a><br>";
	}	// while loop
}	// else
?>
<br>
<br>
	</td></tr></table>
</td>
</tr>
</table>
</body>
</html>
