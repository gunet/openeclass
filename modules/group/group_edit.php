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

include 'group_functions.php';
initialize_group_id();
initialize_group_info($group_id);

$navigation[] = array ('url' => 'group.php', 'name' => $langGroups);
$navigation[] = array ('url' => "group_space.php?group_id=$group_id", 'name' => q($group_name));

if (!($is_adminOfCourse or $is_tutor)) {
        header('Location: group_space.php?group_id=' . $group_id);
        exit;
}

$tool_content = '';
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
      } else {
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
if (isset($_POST['modify'])) {
	// Update main group settings
        register_posted_variables(array('name' => true, 'description' => true), 'all', 'autoquote');
        register_posted_variables(array('maxStudent' => true), 'all', 'intval');
        if ($member_count > $maxStudent) {
                $maxStudent = $max_members;
        }
        $updateStudentGroup = db_query("UPDATE `group` SET name = $name, description = $description,
                                                           max_members = $maxStudent
                                                       WHERE id = $group_id");

        db_query("UPDATE `$currentCourseID`.forums SET forum_name = $name WHERE forum_id =
                        (SELECT forum_id FROM `group` WHERE id = $group_id)");

        db_query("DELETE FROM group_members WHERE group_id = $group_id AND is_tutor = 1");
        if (isset($_POST['tutor'])) {
                foreach ($_POST['tutor'] as $tutor_id) {
                        $tutor_id = intval($tutor_id);
                        db_query("REPLACE INTO group_members SET group_id = $group_id, user_id = $tutor_id, is_tutor = 1");
                }
        }

	// Count number of members
	$numberMembers = @count($_POST['ingroup']);

	// Insert new list of members
	if ($maxStudent < $numberMembers and $maxStudent != 0) {
		// More members than max allowed
		$langGroupEdited = $langGroupTooMuchMembers;
	} else {
                // Delete all members of this group
                $delGroupUsers = db_query("DELETE FROM group_members WHERE group_id = $group_id AND is_tutor = 0");
                $numberMembers--;

                for ($i = 0; $i <= $numberMembers; $i++) {
                        db_query("INSERT IGNORE INTO group_members (user_id, group_id)
                                  VALUES (" . intval($_POST['ingroup'][$i]) . ", $group_id)");
                }

		$langGroupEdited = $langGroupSettingsModified;
        }
        $message = $langGroupEdited;
        initialize_group_info($group_id);
}

$tool_content_group_name = q($group_name);

if ($is_adminOfCourse) {
        $tool_content_tutor = "<select name='tutor[]' multiple='multiple'>\n";
        $resultTutor = db_query("SELECT user.user_id, nom, prenom FROM user, cours_user
                                        WHERE cours_user.user_id = user.user_id AND
                                              cours_user.tutor = 1 AND
                                              cours_user.cours_id = $cours_id
                                        ORDER BY nom, prenom, user_id");
        while ($row = mysql_fetch_array($resultTutor)) {
                $selected = $is_tutor? ' selected="selected"': '';
                $tool_content_tutor .= "<option value='$row[user_id]'$selected>" . q($row['nom']) .
                                       ' ' . q($row['prenom']) . "</option>\n";

        }
        $tool_content_tutor .= '</select>';
        $element1 = 4;
        $element2 = 7;
} else {
        $tool_content_tutor = display_user($tutors);
        $element1 = 3;
        $element2 = 6;
}

$tool_content_max_student = $max_members? $max_members: '-';
$tool_content_group_description = q($group_description);


if ($multi_reg) {
        // Students registered to the course but not members of this group
        $sqll = "SELECT DISTINCT u.user_id ,u.nom, u.prenom
                        FROM user u, cours_user cu, group_members ug
                        WHERE cu.cours_id = $cours_id AND
                              cu.user_id = u.user_id AND
                              u.user_id = ug.user_id AND
                              ug.user_id NOT IN (SELECT user_id FROM group_members WHERE group_id = $group_id) AND
                              cu.statut = 5 AND
                              cu.tutor = 0
                        ORDER BY u.nom, u.prenom";
} else {
        // Students registered to the course but members no group
        $sqll = "SELECT DISTINCT u.user_id, u.nom, u.prenom
                        FROM (user u, cours_user cu)
                        LEFT JOIN group_members ug
                             ON u.user_id = ug.user_id
                        WHERE ug.user_id IS NULL AND
                              cu.cours_id = $cours_id AND
                              cu.user_id = u.user_id AND
                              cu.statut = 5 AND
                              cu.tutor = 0
                        ORDER BY u.nom, u.prenom";
}

$tool_content_not_Member = '';
$resultNotMember = db_query($sqll);
while ($myNotMember = mysql_fetch_array($resultNotMember)) {
        $tool_content_not_Member .= "<option value='$myNotMember[user_id]'>" .
                        q("$myNotMember[nom] $myNotMember[prenom]") . "</option>";
}

$q = db_query("SELECT user.user_id, nom, prenom
               FROM user, group_members
               WHERE group_members.user_id = user.user_id AND
                     group_members.group_id = $group_id AND
                     group_members.is_tutor = 0
               ORDER BY nom, prenom");

$tool_content_group_members = '';
while ($member = mysql_fetch_array($q)) {
        $tool_content_group_members .= "<option value='$member[user_id]'>" . q("$member[nom] $member[prenom]") .
                                       "</option>\n";
}

if (isset($message)) {
        $tool_content .= "<p class='success_small'>$message</p>";
}

$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='group_space.php?group_id=$group_id'>$langGroupThisSpace</a></li>" .
                ($is_adminOfCourse? "<li><a href='../user/user.php'>$langAddTutors</a></li>": '') . "</ul></div>";


$tool_content .="
  <form name='groupedit' method='post' action='".$_SERVER['PHP_SELF']."?group_id=$group_id' onsubmit=\"return checkrequired(this,'name');\">
    <br />
    <table width='99%' class='FormData'>
    <thead>
    <tr>
      <th width='220'>&nbsp;</th>
      <td><b>$langGroupInfo</b></td>
    </tr>
    <tr>
      <th class='left'>$langGroupName:</th>
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

draw($tool_content, 2, '', $head_content);
