<?

$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
include('../../include/init.php');

$nameTools = $langEditGroup;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);
begin_page();
?>
<script type="text/javascript" language="JavaScript">

<!-- Begin javascript menu swapper 
function move(fbox, tbox) {
var arrFbox = new Array();
var arrTbox = new Array();
var arrLookup = new Array();
var i;
for (i = 0; i < tbox.options.length; i++) {
arrLookup[tbox.options[i].text] = tbox.options[i].value;
arrTbox[i] = tbox.options[i].text;
}
var fLength = 0;
var tLength = arrTbox.length;
for(i = 0; i < fbox.options.length; i++) {
arrLookup[fbox.options[i].text] = fbox.options[i].value;
if (fbox.options[i].selected && fbox.options[i].value != "") {
arrTbox[tLength] = fbox.options[i].text;
tLength++;
}
else {
arrFbox[fLength] = fbox.options[i].text;
fLength++;
   }
}
arrFbox.sort();
arrTbox.sort();
fbox.length = 0;
tbox.length = 0;
var c;
for(c = 0; c < arrFbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrFbox[c]];
no.text = arrFbox[c];
fbox[c] = no;
}
for(c = 0; c < arrTbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrTbox[c]];
no.text = arrTbox[c];
tbox[c] = no;
   }
}
//  End -->
</script>

<script type="text/javascript" language="JavaScript">

function selectAll(cbList,bSelect) {
  for (var i=0; i<cbList.length; i++) 
    cbList[i].selected = cbList[i].checked = bSelect
}

function reverseAll(cbList) {
  for (var i=0; i<cbList.length; i++) {
    cbList[i].checked = !(cbList[i].checked) 
    cbList[i].selected = !(cbList[i].selected)
  }
}

</script>

<tr>
<td width="100%" colspan="2"> <font size="2" face="arial, helvetica">

<? 

################### IF MODIFY #######################################

// Once modifications have been done, the user validates and arrives here
if(isset($modify))
{
	// Update main group settings
	$updateStudentGroup=db_query("UPDATE student_group 
		SET name='$name', description='$description', maxStudent='$maxStudent', tutor='$tutor'
		WHERE id='$userGroupId'", $currentCourseID);

	if (isset($forumId)) 
		db_query("UPDATE forums SET forum_name='$name' WHERE forum_id='$forumId'", $currentCourseID);

	// Count number of members
	$numberMembers = @count ($ingroup);

	// every letter introduced in field drives to 0
	settype($maxStudent, "integer");

	// Insert new list of members
	if($maxStudent < $numberMembers AND $maxStudent!="0")
	{
		// Too much members compared to max members allowed
		$langGroupEdited=$langGroupTooMuchMembers;
	}
	else 
	{
		// Delete all members of this group
		$delGroupUsers=db_query("DELETE FROM user_group WHERE team='$userGroupId'", $currentCourseID);
		$numberMembers--;

	for ($i = 0; $i <= $numberMembers; $i++) 
	{
		$registerUserGroup=db_query("INSERT INTO user_group (user, team) 
			VALUES ('$ingroup[$i]', '$userGroupId')", $currentCourseID);
	}

		$langGroupEdited=$langGroupSettingsModified;
	}	// else

	echo "<table width=100% cellpadding=8 bgcolor=#9999FF>
		<tr>
		<td><font size=\"2\" face=\"arial, helvetica\">
			$langGroupEdited
		</td>
		</tr>
		</table>";

}	// if $modify

?>

<form name= "groupedit" method="POST" action="<?= $_SERVER['PHP_SELF']?>?edit=yes&userGroupId=<?= $userGroupId?>">
<table border="0" width="100%" cellspacing="3" cellpadding="5" bgcolor="<?= $color2 ?>">

<?
################# NAME, DESCRIPTION, TUTOR AND MAX STUDENTS ########################

// Select name, description, max members and tutor from student_group DB
$groupSelect=db_query("SELECT name, tutor, description, maxStudent
			FROM student_group WHERE id='$userGroupId'", $currentCourseID);

while ($myStudentGroup = mysql_fetch_array($groupSelect)) 
{
	echo "
	<tr valign=top> 
		<td><font size=\"2\" face=\"arial, helvetica\">
			<p>
				<b>
					$langGroupName
				</b>
				<br>
				<input type=text name=\"name\" size=40 value=\"$myStudentGroup[name]\">
			</p>
		</td>
		<td align=right><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"group_space.php?userGroupId=$userGroupId\">$langGroupThisSpace</a>
			<br>
		</td>
	</tr>
	<tr>
		<td><font size=\"2\" face=\"arial, helvetica\">
			<b>
				$langGroupTutor
			</b>
			<br>
			<select name=\"tutor\">";
	// SELECT TUTORS
	$resultTutor=mysql_query("SELECT user.user_id, user.nom, user.prenom
		FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
			WHERE cours_user.user_id=user.user_id
			AND cours_user.tutor='1'
			AND cours_user.code_cours='$currentCourse'");
	$tutorExists=0;
	while ($myTutor = mysql_fetch_array($resultTutor))
	{
		//  Present tutor appears first in select box
		if($myStudentGroup[tutor]==$myTutor[user_id])
		{
			$tutorExists=1;
			echo "
				<option SELECTED value=\"$myTutor[user_id]\">
					$myTutor[nom] $myTutor[prenom]
				</option>";
		}
		else 
		{
			
			echo "
				<option value=$myTutor[user_id]>
					$myTutor[nom] $myTutor[prenom]
				</option>";
		}
	}
	
	if($tutorExists==0)
	{
		echo "<option SELECTED value=0>$langGroupNoTutor</option>";
	}
	else 
	{
		echo "<option value=0>$langGroupNoTutor</option>";
	}

	echo "</select>&nbsp;&nbsp;
		<font size=1><a href=\"../user/user.php\">$langAddTutors</a></font>
		</td>
		<td align=right><font size=\"2\" face=\"arial, helvetica\">
		<nobr>
		$langMax ";


	if($myStudentGroup['maxStudent']==0)
	{
		echo "<input type=text name=\"maxStudent\" size=2 value=\"-\">";
	}
	else
	{
		echo "<input type=text name=\"maxStudent\" size=2 value=\"$myStudentGroup[maxStudent]\">";
	}

echo " $langGroupPlacesThis</nobr></td></tr>
	<tr valign=top> 
		<td colspan=2><font size=\"2\" face=\"arial, helvetica\">
			<b>
				$langGroupDescription
			</b>
			&nbsp;$langUncompulsory
			<br>
			<textarea name=\"description\" rows=4 cols=70 wrap=virtual>$myStudentGroup[description]</textarea>
		</td>
	</tr>";

}	// while	

echo "</table>";

################### STUDENTS IN AND OUT GROUPS #######################

echo "<center><table border=0 cellspacing=3 cellpadding=4 width=100% bgcolor=\"$color1\">
		<tr valign=top align=center> 
			<td align=left><font size=\"2\" face=\"arial, helvetica\">
				<b>$langNoGroupStudents</b> <p>
	<select name=\"nogroup[]\" size=20 multiple>";
// Student registered to the course but inserted in no group

$sqll= "SELECT DISTINCT u.user_id , u.nom, u.prenom 
			FROM `$mysqlMainDb`.user u, `$mysqlMainDb`.cours_user cu
			LEFT JOIN user_group ug
			ON u.user_id=ug.user
			WHERE ug.id IS null
			AND cu.code_cours='$currentCourse'
			AND cu.user_id=u.user_id
			AND cu.statut=5
			AND cu.tutor=0";

$resultNotMember=mysql_query($sqll);
while ($myNotMember = mysql_fetch_array($resultNotMember))
{
	echo "<option value=\"$myNotMember[user_id]\">
		$myNotMember[prenom] $myNotMember[nom]
	</option>";

}	// while loop

?>
			</select>
			</p>
			<p>&nbsp; </p>
			</td>
			<td> 
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p> 
<?
// WATCH OUT ! form elements are called by numbers "form.element[3]"... 
// because select name contains "[]" causing a javascript element name problem
?>
	<input type="button" onClick="move(this.form.elements[4],this.form.elements[7])" value="   >>   ">
	<br>
	<input type="button" onClick="move(this.form.elements[7],this.form.elements[4])" value="   <<   ">
	</p>
	</td>
	<td><font size="2" face="arial, helvetica">
	<p><b><?= $langGroupMembers ?></b></p>
	<p> 
	<select name="ingroup[]" size="8" multiple>

<?
$resultMember=mysql_query("SELECT user_group.id, user.user_id, user.nom, user.prenom, user.email 
	FROM `$mysqlMainDb`.user, user_group 
	WHERE user_group.team='$userGroupId' AND user_group.user=$mysqlMainDb.user.user_id");

$a=0;
while ($myMember = mysql_fetch_array($resultMember))
	{
	$userIngroupId=$myMember[user_id];
 	echo "<option value=\"$userIngroupId\">$myMember[prenom] $myMember[nom]</option>";
	$a++;
}

?>
	</select></p>
	<table bgcolor="#CCCCCC" width="80%" cellpadding="18">
	<tr><td><font size="2" face="arial, helvetica">
	<input type=submit value="<?= $langValidate ?>" style="font-weight: bold" name="modify" onClick="selectAll(this.form.elements[7],true)">
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
</center>
</form>
		</td>
	</tr>
	<tr> <td width="100%" colspan="3"></td></tr>
</table>
</body>
</html>



