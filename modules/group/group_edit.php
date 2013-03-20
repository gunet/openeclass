<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

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

include '../../include/baseTheme.php';
$nameTools = $langEditGroup;

include 'group_functions.php';
initialize_group_id();
initialize_group_info($group_id);

$navigation[] = array ('url' => 'group.php?course='.$code_cours, 'name' => $langGroups);
$navigation[] = array ('url' => "group_space.php?course=$code_cours&amp;group_id=$group_id", 'name' => q($group_name));

load_js('jquery');
load_js('jquery-ui');
load_js('jquery.multiselect.min.js');
$head_content .= "<script type='text/javascript'>$(document).ready(function () {
        $('#select-tutor').multiselect({
                selectedText: '$langJQSelectNum',
                noneSelectedText: '$langJQNoneSelected',
                checkAllText: '$langJQCheckAll',
                uncheckAllText: '$langJQUncheckAll'
        });
        $('<input type=hidden name=jsCheck value=1>').appendTo('form[name=groupedit]');
});</script>
<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>
<link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";

if (!($is_editor or $is_tutor)) {
        header('Location: group_space.php?course='.$code_cours.'&group_id=' . $group_id);
        exit;
}

$head_content .= "<script type='text/javascript' src='$urlAppend/js/tools.js'></script>\n" .
        "<script type='text/javascript'>var langEmptyGroupName = '" .
        js_escape($langEmptyGroupName) . "';</script>\n";

$message = '';
// Once modifications have been done, the user validates and arrives here
if (isset($_POST['modify'])) {
	// Update main group settings
        register_posted_variables(array('name' => true, 'description' => true), 'all', 'autoquote');
        register_posted_variables(array('maxStudent' => true), 'all', 'intval');
        $student_members = $member_count - count($tutors);
        if ($maxStudent != 0 and $student_members > $maxStudent) {
                $maxStudent = $student_members;
                $message .= "<p class='alert1'>$langGroupMembersUnchanged</p>";
        }
        $updateStudentGroup = db_query("UPDATE `$mysqlMainDb`.`group`
                                               SET name = $name,
                                                   description = $description,
                                                   max_members = $maxStudent
                                               WHERE id = $group_id");

        db_query("UPDATE `$currentCourseID`.forums SET forum_name = $name WHERE forum_id =
                        (SELECT forum_id FROM `$mysqlMainDb`.`group` WHERE id = $group_id)");

        if ($is_editor and isset($_POST['tutor'])) {
                db_query("DELETE FROM `$mysqlMainDb`.group_members
                                 WHERE group_id = $group_id AND is_tutor = 1");
                foreach ($_POST['tutor'] as $tutor_id) {
                        $tutor_id = intval($tutor_id);
                        db_query("REPLACE INTO group_members SET group_id = $group_id, user_id = $tutor_id, is_tutor = 1");
                }
        }

	// Count number of members
	$numberMembers = @count($_POST['ingroup']);

        if (isset($_POST['jsCheck'])) {
                // Modifications possible only if JavaScript is enabled
                // Insert new list of members
                if ($maxStudent < $numberMembers and $maxStudent != 0) {
                        // More members than max allowed
                        $message .= "<p class='alert1'>$langGroupTooManyMembers</p>";
                } else {
                        // Delete all members of this group
                        $delGroupUsers = db_query("DELETE FROM `$mysqlMainDb`.group_members
                                                          WHERE group_id = $group_id AND is_tutor = 0");
                        $numberMembers--;

                        for ($i = 0; $i <= $numberMembers; $i++) {
                                db_query("INSERT IGNORE INTO `$mysqlMainDb`.group_members (user_id, group_id)
                                          VALUES (" . intval($_POST['ingroup'][$i]) . ", $group_id)");
                        }

                        $message .= "<p class='success'>$langGroupSettingsModified</p>";
                }
        }
        initialize_group_info($group_id);
}

$tool_content_group_name = q($group_name);

if ($is_editor) {
        $tool_content_tutor = "<select name='tutor[]' multiple id='select-tutor'>\n";
        $q = db_query("SELECT user.user_id, nom, prenom,
                                   user.user_id IN (SELECT user_id FROM group_members
                                                                   WHERE group_id = $group_id AND
                                                                         is_tutor = 1) AS is_tutor
                              FROM cours_user, user
                              WHERE cours_user.user_id = user.user_id AND
                                    cours_user.tutor = 1 AND
                                    cours_user.cours_id = $cours_id
                              ORDER BY nom, prenom, user_id");
        while ($row = mysql_fetch_array($q)) {
                $selected = $row['is_tutor']? ' selected="selected"': '';
                $tool_content_tutor .= "<option value='$row[user_id]'$selected>" . q($row['nom']) .
                                       ' ' . q($row['prenom']) . "</option>\n";

        }
        $tool_content_tutor .= '</select>';
} else {
        $tool_content_tutor = display_user($tutors);
}

$tool_content_max_student = $max_members? $max_members: '-';
$tool_content_group_description = q($group_description);


if ($multi_reg) {
        // Students registered to the course but not members of this group
        $sqll = "SELECT u.user_id, u.nom, u.prenom
                        FROM user u, cours_user cu
                        WHERE cu.cours_id = $cours_id AND
                              cu.user_id = u.user_id AND
                              u.user_id NOT IN (SELECT user_id FROM group_members WHERE group_id = $group_id) AND
                              cu.statut = 5
                        GROUP BY u.user_id
                        ORDER BY u.nom, u.prenom";
} else {
        // Students registered to the course but members of no group
        $sqll = "SELECT u.user_id, u.nom, u.prenom
                        FROM (user u, cours_user cu)
                        WHERE cu.cours_id = $cours_id AND
                              cu.user_id = u.user_id AND
                              cu.statut = 5 AND
                              u.user_id NOT IN (SELECT user_id FROM group_members, `group`
                                                               WHERE `group`.id = group_members.group_id AND
                                                               `group`.course_id = $cours_id)
                        GROUP BY u.user_id
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

if (!empty($message)) {
        $tool_content .= $message;
}

$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='group_space.php?course=$code_cours&amp;group_id=$group_id'>$langGroupThisSpace</a></li>" .
                ($is_editor? "<li><a href='../user/user.php?course=$code_cours'>$langAddTutors</a></li>": '') . "</ul></div>";


$tool_content .="
  <form name='groupedit' method='post' action='".$_SERVER['SCRIPT_NAME']."?course=$code_cours&amp;group_id=$group_id' onsubmit=\"return checkrequired(this,'name');\">
    <fieldset>
    <legend>$langGroupInfo</legend>
    <table width='99%' class='tbl'>
    <tr>
      <th class='left'>$langGroupName:</th>
      <td><input type=text name='name' size=40 value='$tool_content_group_name' /></td>
    </tr>
    <tr>
      <th class='left'>$langDescription $langUncompulsory:</th>
      <td><textarea name='description' rows='2' cols='60'>$tool_content_group_description</textarea></td>
    </tr>
    <tr>
      <th class='left'>$langMax $langGroupPlacesThis:</th>
      <td><input type=text name='maxStudent' size=2 value='$tool_content_max_student' /></td>
    </tr>
    <tr>
      <th class='left'>$langGroupTutor:</th>
      <td>
         $tool_content_tutor
      </td>
    </tr>
    <tr>
      <th class='left' valign='top'>$langGroupMembers :</th>
      <td>
          <table width='99%' align='center' class='tbl_white'>
          <tr class='title1'>
            <td>$langNoGroupStudents</td>
            <td width='100' class='center'>$langMove</td>
            <td class='right'>$langGroupMembers</td>
          </tr>
          <tr>
            <td>
              <select id='users_box' name='nogroup[]' size='15' multiple>
                $tool_content_not_Member
              </select>
            </td>
            <td class='center'>
              <input type='button' onClick=\"move('users_box','members_box')\" value='   &gt;&gt;   ' /><br /><input type='button' onClick=\"move('members_box','users_box')\" value='   &lt;&lt;   ' />
            </td>
            <td class='right'>
              <select id='members_box' name='ingroup[]' size='15' multiple>
                $tool_content_group_members
              </select>
            </td>
          </tr>
          </table>
      </td>
    </tr>
    <tr>
      <th class='left'>&nbsp;</th>
      <td><input type='submit' name='modify' value='".q($langModify)."' onClick=\"selectAll('members_box',true)\" /></td>
    </tr>
    </table>
    </fieldset>
</form>
";

draw($tool_content, 2, null, $head_content);
