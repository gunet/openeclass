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
$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
$nameTools = $langEditGroup;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroups);

$userGroupId = intval($_GET['userGroupId']);
list($tutor_id) = mysql_fetch_row(db_query("SELECT tutor FROM student_group WHERE id='$userGroupId'", $currentCourseID));
$is_tutor = ($tutor_id == $uid);
if (!$is_adminOfCourse and !$is_tutor) {
        header('Location: group_space.php?userGroupId=' . $userGroupId);
        exit;
}

$tool_content ="";
$head_content = <<<hCont
<script type="text/javascript" language="JavaScript">
<!-- // Begin javascript menu swapper
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
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyGroupName");
		return false;
	} else {
		return true;
	}
}
</script>
hCont;

// Once modifications have been done, the user validates and arrives here
if (isset($modify))
{
	// Update main group settings
        if (!$is_adminOfCourse) {
                $tutor = $tutor_id;
        }
	$updateStudentGroup = db_query("UPDATE student_group
		SET name='$name', description='$description', maxStudent='$maxStudent', tutor='$tutor'
		WHERE id='$userGroupId'", $currentCourseID);

	db_query("UPDATE forums SET forum_name='$name' 
		  WHERE forum_id=(SELECT forumId FROM student_group WHERE id=$userGroupId)", $currentCourseID);

	// Count number of members
	$numberMembers = @count($ingroup);

	// every letter introduced in field drives to 0
	settype($maxStudent, "integer");

	// Insert new list of members
	if($maxStudent < $numberMembers and $maxStudent != 0)
	{
		// Too much members compared to max members allowed
		$langGroupEdited=$langGroupTooMuchMembers;
	}
	else
        {
                // Delete all members of this group
                $delGroupUsers = db_query("DELETE FROM user_group WHERE team='$userGroupId'", $currentCourseID);
                $numberMembers--;

                for ($i = 0; $i <= $numberMembers; $i++) {
                        db_query("INSERT INTO user_group (user, team)
                                  VALUES ('$ingroup[$i]', '$userGroupId')", $currentCourseID);
                }

		$langGroupEdited=$langGroupSettingsModified;
	}	// else
		$message = $langGroupEdited;
}	// if $modify

//=======================================================================
################# NAME, DESCRIPTION, TUTOR AND MAX STUDENTS ########################

// Select name, description, max members and tutor from student_group DB
$groupSelect=db_query("SELECT name, tutor, description, maxStudent
			FROM student_group WHERE id='$userGroupId'", $currentCourseID);

while ($myStudentGroup = mysql_fetch_array($groupSelect))
{
	$tool_content_group_name = $myStudentGroup['name'];

        if ($is_adminOfCourse) {
                // SELECT TUTORS
                $tool_content_tutor = '<select name="tutor" class="FormData_InputText">';
                $resultTutor=db_query("SELECT user.user_id, user.nom, user.prenom, user.username
                        FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
                                WHERE cours_user.user_id = user.user_id
                                AND cours_user.tutor = 1
                                AND cours_user.cours_id = $cours_id
                        ORDER BY nom, prenom, username");
                $none_selected = 'selected="1"';
                while ($myTutor = mysql_fetch_array($resultTutor)) {
                        //  Present tutor appears first in select box
                        if($myStudentGroup['tutor']==$myTutor['user_id']) {
                                $none_selected = '';
                                $selected = 'selected="1"';
                        } else {
                                $selected = '';
                        }
                        $tool_content_tutor .= "<option $selected value='$myTutor[user_id]'>$myTutor[nom] $myTutor[prenom] ($myTutor[username])</option>";
                }
                $tool_content_tutor .=  "<option $none_selected value='0'>$langGroupNoTutor</option>";
                $tool_content_tutor .= '</select>';
                $element1 = 4;
                $element2 = 7;
        } else {
                $tool_content_tutor = uid_to_name($uid);
                $element1 = 3;
                $element2 = 6;
        }

	if ($myStudentGroup['maxStudent'] == 0) {
		$tool_content_max_student = "-";
	} else {
		$tool_content_max_student = $myStudentGroup['maxStudent'];
	}

	$tool_content_group_description = $myStudentGroup['description'];


}	// while

################### STUDENTS IN AND OUT GROUPS #######################

// Student registered to the course but inserted in no group
$sqll= "SELECT DISTINCT u.user_id , u.nom, u.prenom
			FROM (`$mysqlMainDb`.user u, `$mysqlMainDb`.cours_user cu)
			LEFT JOIN user_group ug
			ON u.user_id = ug.user
			WHERE ug.id IS null
			AND cu.cours_id = $cours_id
			AND cu.user_id = u.user_id
			AND cu.statut = 5
			AND cu.tutor = 0 ORDER BY u.nom, u.prenom";

$tool_content_not_Member = '';
$resultNotMember = db_query($sqll, $currentCourseID);
while ($myNotMember = mysql_fetch_array($resultNotMember)) {
	$tool_content_not_Member .= "<option value='$myNotMember[user_id]'>$myNotMember[nom] $myNotMember[prenom]</option>";
}

$resultMember = db_query("SELECT user_group.id, user.user_id, user.nom, user.prenom, user.email
	FROM `$mysqlMainDb`.user, user_group
	WHERE user_group.team='$userGroupId' AND user_group.user=$mysqlMainDb.user.user_id ORDER BY user.nom, user.prenom");

$a = 0;
$tool_content_group_members = '';
while ($myMember = mysql_fetch_array($resultMember)) {
	$userIngroupId=$myMember['user_id'];
 	$tool_content_group_members .= "<option value='$userIngroupId'>$myMember[nom] $myMember[prenom]</option>";
	$a++;
}

if (isset($message)) {
        $tool_content .= "<p class='success_small'>$message</p><p>&nbsp;</p>";
}

$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='group_space.php?userGroupId=$userGroupId'>$langGroupThisSpace</a></li>" .
                ($is_adminOfCourse? "<li><a href='../user/user.php'>$langAddTutors</a></li>": '') . "</ul></div>";


$tool_content .="
  <form name='groupedit' method='post' action='".$_SERVER['PHP_SELF']."?edit=yes&amp;userGroupId=$userGroupId' onsubmit=\"return checkrequired(this,'name');\">
    <br />
    <table width='99%' class='FormData'>
    <thead>
    <tr>
      <th width='220'>&nbsp;</th>
      <td><b>$langGroupInfo</b></td>
    </tr>
    <tr>
      <th class='left'>$langGroupName $myStudentGroup[name]:</th>
      <td><input type=text name='name' size=40 value='$tool_content_group_name' class='FormData_InputText' /></td>
    </tr>
    <tr>
      <th class='left'>$langGroupTutor:</th>
      <td>
         $tool_content_tutor
      </td>
    </tr>
    <tr>
      <th class='left'>$langMax $langGroupPlacesThis:</th>
      <td><input type=text name='maxStudent' size=2 value='$tool_content_max_student'  class='auth_input' /></td>
    </tr>
    <tr>
      <th class='left'>$langDescription $langUncompulsory:</th>
      <td><textarea name='description' rows='2' cols='60' class='FormData_InputText'>$tool_content_group_description</textarea></td>
    </tr>
    <tr>
      <th class='left' valign='top'>$langGroupMembers :</th>
      <td>
          <table width='99%' align='center' class='GroupSum'>
          <thead>
          <tr>
            <td><b>$langNoGroupStudents</b></td>
            <td width='100'><div align='center'><b>$langMove</b></div></td>
            <td><div align='right'><b>$langGroupMembers</b></div></td>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td><div align='left'>
              <select name='nogroup[]' size='15' multiple='1'>
                $tool_content_not_Member
              </select></div>
            </td>
            <td>
              <div align='center'>
              <input type='button' onClick=\"move(this.form.elements[$element1],this.form.elements[$element2])\" value='   &gt;&gt;   ' /><br /><input type='button' onClick=\"move(this.form.elements[$element2],this.form.elements[$element1])\" value='   &lt;&lt;   ' />
              </div>
            </td>
            <td><div align='right'>
              <select name='ingroup[]' size='15' multiple='1'>
                $tool_content_group_members
              </select></div>
            </td>
          </tr>
          </tbody>
          </table>
      </td>
    </tr>
    <tr>
      <th class=\"left\">&nbsp;</th>
      <td><input type='submit' value='$langModify'  name='modify' onClick=\"selectAll(this.form.elements[$element2],true)\" /></td>
    </tr>
    </thead>
    </table>
</form>
";

draw($tool_content, 2, 'group', $head_content);
