<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @version $Id: group_edit.php,v 1.44 2011-06-24 13:40:33 adia Exp $
 *
 * @abstract This module is responsible for the user groups of each lesson
 *
 */
$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

require_once '../../include/baseTheme.php';
$nameTools = $langEditGroup;

require_once 'group_functions.php';
initialize_group_id();
initialize_group_info($group_id);

$navigation[] = array ('url' => 'index.php?course='.$course_code, 'name' => $langGroups);
$navigation[] = array ('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => q($group_name));

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
        header('Location: group_space.php?course='.$course_code.'&group_id=' . $group_id);
        exit;
}

$head_content .= "<script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n" .
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

        db_query("UPDATE forum SET name = $name WHERE id =
                        (SELECT forum_id FROM `group` WHERE id = $group_id)
                            AND course_id = $course_id");

        if ($is_editor and isset($_POST['tutor'])) {
                db_query("DELETE FROM `$mysqlMainDb`.group_members
                                 WHERE group_id = $group_id AND is_tutor = 1");
                foreach ($_POST['tutor'] as $tutor_id) {
                        $tutor_id = intval($tutor_id);
                        db_query("REPLACE INTO group_members SET group_id = $group_id, user_id = $tutor_id, is_tutor = 1, description='$description'");
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
        $q = db_query("SELECT user.id AS user_id, surname, givenname,
                                   user.id IN (SELECT user_id FROM group_members
                                                              WHERE group_id = $group_id AND
                                                                    is_tutor = 1) AS is_tutor
                              FROM course_user, user
                              WHERE course_user.user_id = user.id AND
                                    course_user.tutor = 1 AND
                                    course_user.course_id = $course_id
                              ORDER BY surname, givenname, user_id");
        while ($row = mysql_fetch_array($q)) {
                $selected = $row['is_tutor']? ' selected="selected"': '';
                $tool_content_tutor .= "<option value='$row[user_id]'$selected>" . q($row['surname']) .
                                       ' ' . q($row['givenname']) . "</option>\n";

        }
        $tool_content_tutor .= '</select>';
} else {
        $tool_content_tutor = display_user($tutors);
}

$tool_content_max_student = $max_members? $max_members: '-';
$tool_content_group_description = q($group_description);


if ($multi_reg) {
        // Students registered to the course but not members of this group
        $sqll = "SELECT u.id, u.surname, u.givenname
                        FROM user u, course_user cu
                        WHERE cu.course_id = $course_id AND
                              cu.user_id = u.id AND
                              u.id NOT IN (SELECT user_id FROM group_members WHERE group_id = $group_id) AND
                              cu.status = 5
                        GROUP BY u.id
                        ORDER BY u.surname, u.givenname";
} else {
        // Students registered to the course but members of no group
        $sqll = "SELECT u.id, u.surname, u.givenname
                        FROM (user u, course_user cu)
                        WHERE cu.course_id = $course_id AND
                              cu.user_id = u.user_id AND
                              cu.status = 5 AND
                              u.id NOT IN (SELECT user_id FROM group_members, `group`
                                                               WHERE `group`.id = group_members.group_id AND
                                                               `group`.course_id = $course_id)
                        GROUP BY u.id
                        ORDER BY u.surname, u.givenname";
}

$tool_content_not_Member = '';
$resultNotMember = db_query($sqll);
while ($myNotMember = mysql_fetch_array($resultNotMember)) {
        $tool_content_not_Member .= "<option value='$myNotMember[id]'>" .
                        q("$myNotMember[surname] $myNotMember[givenname]") . "</option>";
}

$q = db_query("SELECT user.id, user.surname, user.givenname
               FROM user, group_members
               WHERE group_members.user_id = user.id AND
                     group_members.group_id = $group_id AND
                     group_members.is_tutor = 0
               ORDER BY user.surname, user.givenname");

$tool_content_group_members = '';
while ($member = mysql_fetch_array($q)) {
        $tool_content_group_members .= "<option value='$member[id]'>" . q("$member[surname] $member[givenname]") .
                                       "</option>";
}

if (!empty($message)) {
        $tool_content .= $message;
}

$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='group_space.php?course=$course_code&amp;group_id=$group_id'>$langGroupThisSpace</a></li>" .
                ($is_editor? "<li><a href='../user/?course=$course_code'>$langAddTutors</a></li>": '') . "</ul></div>";


$tool_content .="
  <form name='groupedit' method='post' action='".$_SERVER['SCRIPT_NAME']."?course=$course_code&amp;group_id=$group_id' onsubmit=\"return checkrequired(this,'name');\">
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
      <th class=\"left\">&nbsp;</th>
      <td><input type='submit' name='modify' value='$langModify' onClick=\"selectAll('members_box',true)\" /></td>
    </tr>
    </table>
    </fieldset>
</form>";

draw($tool_content, 2, null, $head_content);