<?php
/* ========================================================================
 * Open eClass 3.0
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

$require_login = true;
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'User';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/admin.inc.php';
require_once 'include/log.php';
load_js('tools.js');

define ('COURSE_USERS_PER_PAGE', 15);

$limit = isset($_REQUEST['limit'])? $_REQUEST['limit']: 0;

$nameTools = $langAdminUsers;

$sql = "SELECT user.user_id, course_user.statut FROM course_user, user
	WHERE course_user.course_id = $course_id AND course_user.user_id = user.user_id";
$result_numb = db_query($sql);
$countUser = mysql_num_rows($result_numb);

$teachers = $students = $visitors = 0;

while ($numrows = mysql_fetch_array($result_numb)) {
	switch ($numrows['statut']) {
		case USER_TEACHER: {
                                $teachers++;
                                break;                
                }                        
		case USER_STUDENT: {
                        $students++;
                        break;
                }
		case USER_GUEST: {
                        $visitors++; 
                        break;
                }
		default: break;
	}
}

$limit_sql = '';
// Handle user removal / status change
if (isset($_GET['giveAdmin'])) {
        $new_admin_gid = intval($_GET['giveAdmin']);
        db_query("UPDATE course_user SET statut = ".USER_TEACHER."
                        WHERE user_id = $new_admin_gid
                        AND course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $new_admin_gid,
                                                                   'right' => '+1'));
} elseif (isset($_GET['giveTutor'])) {
        $new_tutor_gid = intval($_GET['giveTutor']);
        db_query("UPDATE course_user SET tutor = ".USER_TEACHER."
                        WHERE user_id = $new_tutor_gid
                        AND course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $new_tutor_gid,
                                                                   'right' => '+3'));
        db_query("UPDATE group_members, `group` SET is_tutor = 0
                        WHERE `group`.id = group_members.group_id AND
                              `group`.course_id = $course_id AND
                              group_members.user_id = $new_tutor_gid");
} elseif (isset($_GET['giveEditor'])) {
        $new_editor_gid = intval($_GET['giveEditor']);
        db_query("UPDATE course_user SET editor = 1
                        WHERE user_id = $new_editor_gid
                        AND course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $new_editor_gid,
                                                                   'right' => '+2'));
} elseif (isset($_GET['removeAdmin'])) {
        $removed_admin_gid = intval($_GET['removeAdmin']);
        db_query("UPDATE course_user SET statut = ".USER_STUDENT."
                        WHERE user_id <> $uid AND
                              user_id = $removed_admin_gid AND
                              course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $removed_admin_gid,
                                                                   'right' => '-1'));
} elseif (isset($_GET['removeTutor'])) {
        $removed_tutor_gid = intval($_GET['removeTutor']);
        db_query("UPDATE course_user SET tutor = 0
                        WHERE user_id = $removed_tutor_gid
                              AND course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $removed_tutor_gid,
                                                                   'right' => '-3'));
} elseif (isset($_GET['removeEditor'])) {
        $removed_editor_gid = intval($_GET['removeEditor']);
        db_query("UPDATE course_user SET editor = 0
                        WHERE user_id = $removed_editor_gid
                        AND course_id = $course_id");
        Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $removed_editor_gid,
                                                                   'right' => '-2'));
} elseif (isset($_GET['unregister'])) {
        $unregister_gid = intval($_GET['unregister']);
        $unregister_ok = true;
        // Security: don't remove myself except if there is another prof
        if ($unregister_gid == $uid) {
                $result = db_query("SELECT user_id FROM course_user
                                        WHERE course_id = $course_id AND
                                              statut = ".USER_TEACHER." AND
                                              user_id != $uid
                                        LIMIT 1");
                if (mysql_num_rows($result) == 0) {
                        $unregister_ok = false;
                }
        }
        if ($unregister_ok) {
                db_query("DELETE FROM course_user
                                WHERE user_id = $unregister_gid AND
                                      course_id = $course_id");
                db_query("DELETE FROM group_members
                                WHERE user_id = $unregister_gid AND
                                      group_id IN (SELECT id FROM `group` WHERE course_id = $course_id)");
        }
        Log::record($course_id, MODULE_ID_USERS, LOG_DELETE, array('uid' => $unregister_gid,
                                                                   'right' => 0));
}
// show help link and link to Add new user, search new user and management page of groups
$tool_content .= "

<div id='operations_container'>
  <ul id='opslist'>
    <li><b>$langAdd:</b>&nbsp; <a href='adduser.php?course=$course_code'>$langOneUser</a></li>
    <li><a href='muladduser.php?course=$course_code'>$langManyUsers</a></li>
    <li><a href='guestuser.php?course=$course_code'>$langGUser</a>&nbsp;</li>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;search=1'>$langSearchUser</a></li>
    <li><a href='../group/index.php?course=$course_code'>$langGroupUserManagement</a></li>
    <li><a href='../course_info/refresh_course.php?course=$course_code'>$langDelUsers</a></li>
  </ul>
</div>";

// display number of users
$tool_content .= "
<div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langUsers &nbsp;($teachers $langTeachers, $students $langStudents, $visitors $langVisitors)</em></span><br />
  <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code'>$langcsvenc2</a>
       2. <a href='dumpuser.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>
  </div>";

// display and handle search form if needed
$search_sql = '';
if (isset($_GET['search'])) {
        $search_params = "&amp;search=1";
        $search_nom = $search_prenom = $search_uname = '';
        if (!empty($_REQUEST['search_nom'])) {
                $search_nom = ' value="' . q($_REQUEST['search_nom']) . '"';
                $search_sql .= " AND user.nom LIKE " . autoquote(mysql_escape_string($_REQUEST['search_nom']).'%');
                $search_params .= "&amp;search_nom=" . urlencode($_REQUEST['search_nom']);
        }
        if (!empty($_REQUEST['search_prenom'])) {
                $search_prenom = ' value="' . q($_REQUEST['search_prenom']) . '"';
                $search_sql .= " AND user.prenom LIKE " . autoquote(mysql_escape_string($_REQUEST['search_prenom']).'%');
                $search_params .= "&amp;search_prenom=" . urlencode($_REQUEST['search_prenom']);
        }
        if (!empty($_REQUEST['search_uname'])) {
                $search_uname = ' value="' . q($_REQUEST['search_uname']) . '"';
                $search_sql .= " AND user.username LIKE " . autoquote(mysql_escape_string($_REQUEST['search_uname']).'%');
                $search_params .= "&amp;search_uname=" . urlencode($_REQUEST['search_uname']);
        }

        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;search=1'>
        <fieldset>
        <legend>$langUserData</legend>
        <table width='100%' class='tbl'>
        <tr>
          <th class='left' width='180'>$langSurname:</th>
          <td><input type='text' name='search_nom'$search_nom></td>
        </tr>
        <tr>
          <th class='left'>$langName:</th>
          <td><input type='text' name='search_prenom'$search_prenom></td>
        </tr>
        <tr>
          <th class='left'>$langUsername:</th>
          <td><input type='text' name='search_uname'$search_uname></td>
        </tr>
        <tr>
          <th class='left'>&nbsp;</th>
          <td class='right'><input type='submit' value='$langSearch'></td>
        </tr>
        </table>
        </fieldset>
        </form>";
} else {
        $search_params = '';
}

// display navigation links if course users > COURSE_USERS_PER_PAGE
if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
        $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
        $tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser,
                                     $_SERVER['SCRIPT_NAME'], $search_params, TRUE);
}

if (isset($_GET['all'])) {
        $extra_link = '&amp;all=true' . $search_params;
} else {
        $extra_link = '&amp;limit=' . $limit . $search_params;
}

$tool_content .= "
<table width='100%' class='tbl_alt custom_list_order'>
<tr>
  <th width='1'>$langID</th>
  <th><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=s$extra_link'>$langName $langSurname</a></div></th>
  <th class='center' width='160'>$langGroup</th>
  <th class='center' width='90'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=rd$extra_link'>$langRegistrationDateShort</a></th>
  <th colspan='3' class='center'>$langAddRole</th>
</tr>";


// Numerating the items in the list to show: starts at 1 and not 0
$i = $limit + 1;
$ord = isset($_GET['ord'])?$_GET['ord']:'';

switch ($ord) {
        case 's': $order_sql = 'ORDER BY nom';
                break;
        case 'e': $order_sql = 'ORDER BY email';
                break;
        case 'am': $order_sql = 'ORDER BY am';
                break;
        case 'rd': $order_sql = 'ORDER BY course_user.reg_date DESC';
                break;
        default: $order_sql = 'ORDER BY statut, editor DESC, tutor DESC, nom, prenom';
                break;
}
$result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email,
                           user.am, user.has_icon, course_user.statut,
                           course_user.tutor, course_user.editor, course_user.reg_date
                    FROM course_user, user
                    WHERE `user`.`user_id` = `course_user`.`user_id`
                    AND `course_user`.`course_id` = $course_id
                    $search_sql $order_sql $limit_sql");

while ($myrow = mysql_fetch_array($result)) {
        // bi colored table
        if ($i%2 == 0) {
                $tool_content .= "<tr class='odd'>";
        } else {
                $tool_content .= "<tr class='even'>";
        }
        // show public list of users
        $am_message = empty($myrow['am'])? '': ("<div class='right'>($langAm: " . q($myrow['am']) . ")</div>");
        $tool_content .= "
        <td class='smaller' valign='top' align='right'>$i.</td>\n" .
                "<td valign='top' class='smaller'>" . display_user($myrow) . "&nbsp;&nbsp;(". mailto($myrow['email']) . ")  $am_message</td>\n";
        $tool_content .= "\n" .
                "<td class='smaller' valign='top' width='150'>" . user_groups($course_id, $myrow['user_id']) . "</td>\n" .
                "<td align='center' class='smaller'>";
        if ($myrow['reg_date'] == '0000-00-00') {
                $tool_content .= $langUnknownDate;
        } else {
                $tool_content .= nice_format($myrow['reg_date']);
        }
        $alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
        $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;unregister=$myrow[user_id]$extra_link'
                         onClick=\"return confirmation('" . $langDeleteUser . " &laquo;".$alert_uname."&raquo; ".$langDeleteUser2."');\">
                         <img src='$themeimg/cunregister.png' title='$langUnregCourse' /></a>";

        $tool_content .= "</td>";
        // tutor right
        if ($myrow['tutor'] == '0') {
                $tool_content .= "<td valign='top' align='center' class='add_user'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveTutor=$myrow[user_id]$extra_link'>
                                <img src='$themeimg/group_manager_add.png' title='$langGiveRightTutor' /></a></td>";
        } else {
                $tool_content .= "<td class='add_teacherLabel' align='center'  width='30'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeTutor=$myrow[user_id]$extra_link' title='$langRemoveRightTutor'>
                                <img src='$themeimg/group_manager_remove.png' title ='$langRemoveRightTutor' /></a></td>";
        }
        // editor right
        if ($myrow['editor'] == '0') {
            $tool_content .= "<td valign='top' align='center' class='add_user'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveEditor=$myrow[user_id]$extra_link'>
                                <img src='$themeimg/assistant_add.png' title='$langGiveRightΕditor' /></a></td>";
        } else {
                $tool_content .= "<td class='add_teacherLabel' align='center' width='30'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeEditor=$myrow[user_id]$extra_link' title='$langRemoveRightEditor'>
                                <img src='$themeimg/assistant_remove.png' title ='$langRemoveRightEditor' /></a></td>";
        }
        // admin right
        if ($myrow['user_id'] != $_SESSION["uid"]) {
                if ($myrow['statut']=='1') {
                        $tool_content .= "<td class='add_teacherLabel' align='center'  width='30'>
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeAdmin=$myrow[user_id]$extra_link' title='$langRemoveRightAdmin'>
                                        <img src='$themeimg/teacher_remove.png' title ='$langRemoveRightAdmin' /></a></td>";
                } else {
                        $tool_content .= "<td valign='top' align='center' class='add_user'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveAdmin=$myrow[user_id]$extra_link'>
                                <img src='$themeimg/teacher_add.png' title='$langGiveRightAdmin' /></a></td>";
                }
        } else {
                if ($myrow['statut']=='1') {
                        $tool_content .= "<td valign='top' class='add_teacherLabel' align='center'  width='30'>
                                        <img src='$themeimg/teacher.png' title='$langTutor' /></td>";
                } else {
                        $tool_content .= "<td class='smaller' valign='top' align='center'>
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveAdmin=$myrow[user_id]$extra_link'>
                                        <img src='$themeimg/add.png' title='$langGiveRightAdmin' /></a></td>";
                }
        }
        $tool_content .= "</tr>";
        $i++;
}
$tool_content .= "</table>";

draw($tool_content, 2, null, $head_content);
