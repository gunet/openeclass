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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';

include 'group_functions.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GROUPS');
/**************************************/

$nameTools = $langGroups;
$totalRegistered = 0;
unset($message);
if ($is_adminOfCourse) {
	$head_content = '
<script type="text/javascript">
function confirmation (name)
{
	if (name == "delall") {
		if(confirm("'.$langDeleteGroupAllWarn.' ?"))
		{return true;}
		else
		{return false;}
	} else if (name == "emptyall") {
		if (confirm("'.$langDeleteGroupAllWarn.' ?"))
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

unset($_SESSION['secret_directory']);
unset($_SESSION['forum_id']);

mysql_select_db($mysqlMainDb);
initialize_group_info();

if ($is_adminOfCourse) {

        if (isset($_POST['creation'])) {
                $group_quantity = intval($_POST['group_quantity']);
                if (preg_match('/^[0-9]/', $_POST['group_max'])) {
                        $group_max = intval($_POST['group_max']);
                } else {
                        $group_max = 0;
                }

                list($group_num) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM `group` WHERE course_id = $cours_id"));

                // Create a hidden category for group forums
                $req = db_query("SELECT cat_id FROM `$currentCourseID`.catagories WHERE cat_order = -1");
                if ($req and mysql_num_rows($req) > 0) {
                        list($cat_id) = mysql_fetch_row($req);
                } else {
                        db_query("INSERT INTO `$currentCourseID`.catagories (cat_title, cat_order)
                                         VALUES ('$langCatagoryGroup', -1)");
                        $cat_id = mysql_insert_id();
                }

                for ($i = 1; $i <= $group_quantity; $i++) {
                        do {
                                $group_num++;
                                $res = db_query("SELECT id FROM `group` WHERE name = '$langGroup $group_num'");
                        } while (mysql_num_rows($res) > 0);

                        db_query("INSERT INTO `$currentCourseID`.forums (forum_id, forum_name, forum_desc, forum_access,
                                                      forum_moderator, forum_topics, forum_posts,
                                                      forum_last_post_id, cat_id, forum_type)
                                  VALUES ('', '$langForumGroup $group_num', '', 2, 1, 0, 0, 1, $cat_id, 0)");
                        $forum_id = mysql_insert_id();

                        // Create a unique path to group documents to try (!)
                        // avoiding groups entering other groups area
                        $secretDirectory = uniqid('');
                        mkdir("../../courses/$code_cours/group/$secretDirectory", 0777, true);

                        db_query("INSERT INTO `group` (max_members, secret_directory)
                                VALUES ($group_max, '$secretDirectory')");

                        db_query("INSERT INTO `group` SET
                                         course_id = $cours_id,
                                         name = '$langGroup $group_num',
                                         forum_id =  $forum_id,
                                         max_members = $group_max,
                                         secret_directory = '$secretDirectory'");
                }
                if ($group_quantity == 1) {
                        $message = "$group_quantity $langGroupAdded";
                } else {
                        $message = "$group_quantity $langGroupsAdded";
                }

        } elseif (isset($_POST['properties'])) {
                register_posted_variables(array(
                        'self_reg' => true,
                        'multi_reg' => true,
                        'private_forum' => true,
                        'forum' => true,
                        'documents' => true), 'all', 'intval');
                db_query("UPDATE group_properties SET
                                 self_registration = $self_reg,
                                 multiple_registration = $multi_reg,
                                 private_forum = $private_forum,
                                 forum = $forum,
                                 documents = $documents WHERE course_id = $cours_id");
                $message = $langGroupPropertiesModified;

        } elseif (isset($_REQUEST['delete_all'])) {
                db_query("DELETE FROM group_members WHERE group_id IN (SELECT id FROM `group` WHERE course_id = $cours_id)");
                db_query("DELETE FROM `group` WHERE course_id = $cours_id");
                // FIXME db_query("DELETE FROM forums WHERE cat_id='1'");

                // Move all groups to garbage collector and re-create an empty work directory
                $groupGarbage = uniqid(20);

                @mkdir("../../courses/garbage");
                rename("../../courses/$code_cours/group", "../../courses/garbage/$groupGarbage");
                mkdir("../../courses/$code_cours/group", 0777);

                $message = $langGroupsDeleted;

        } elseif (isset($_REQUEST['delete'])) {
                $id = intval($_REQUEST['delete']);

                // Move group directory to garbage collector
                $groupGarbage = uniqid(20);
                $sqlDir = db_query("SELECT secret_directory, forum_id FROM `group` WHERE id = $id");
                $myDir = mysql_fetch_array($sqlDir);
                rename("../../courses/$code_cours/group/$myDir[secret_directory]",
                       "../../courses/garbage/$groupGarbage");
                // FIXME db_query("DELETE FROM forums WHERE forum_id = $myDir[forum_id]");

                db_query("DELETE FROM `group` WHERE id = $id");
                db_query("DELETE FROM group_members WHERE group_id = $id");
                $message = $langGroupDel;

        } elseif (isset($_REQUEST['empty'])) {
                $result = db_query("DELETE FROM user_group");
                $result2 = db_query("UPDATE student_group SET tutor='0'");
                $message = $langGroupsEmptied;

        } elseif (isset($_REQUEST['fill'])) {
                $resGroups = db_query("SELECT id, max_members -
                                                      (SELECT count(*) from group_members WHERE group_members.group_id = id)
                                                  AS remaining
                                              FROM `group` WHERE course_id = $cours_id ORDER BY id;");
                while (list($idGroup, $places) = mysql_fetch_row($resGroups)) {
                        if ($places > 0) {
                                $placeAvailableInGroups[$idGroup] = $places;
                        }
                }
                $resUserSansGroupe= db_query("SELECT DISTINCT u.user_id
                                                FROM (user u, cours_user cu)
                                                LEFT JOIN group_members ug
                                                     ON u.user_id = ug.user_id
                                                WHERE ug.user_id IS NULL AND
                                                      cu.cours_id = $cours_id AND
                                                      cu.user_id = u.user_id AND
                                                      cu.statut = 5 AND
                                                      cu.tutor = 0");
                while (isset($placeAvailableInGroups) and is_array($placeAvailableInGroups) and (!empty($placeAvailableInGroups)) and list($idUser) = mysql_fetch_array($resUserSansGroupe)) {
                        $idGroupChoisi = array_keys($placeAvailableInGroups, max($placeAvailableInGroups));
                        $idGroupChoisi = $idGroupChoisi[0];
                        $userOfGroups[$idGroupChoisi][] = $idUser;
                        $placeAvailableInGroups[$idGroupChoisi]--;
                        if ($placeAvailableInGroups[$idGroupChoisi] <= 0) {
                                unset($placeAvailableInGroups[$idGroupChoisi]);
                        }
                }

                // NOW we have $userOfGroups containing new affectation. We must write this in database
                if (isset($userOfGroups) and is_array($userOfGroups)) {
                        reset($userOfGroups);
                        while (list($idGroup,$users) = each($userOfGroups)) {
                                while (list(,$idUser) = each($users)) {
                                        db_query("INSERT INTO group_members SET user_id = $idUser, group_id = $idGroup");
                                }
                        }
                }
                $message = $langGroupFilledGroups;
        }

	// Show DB messages
	if (isset($message)) {
		$tool_content .= "<p class='success_small'>$message</p><br />";
	}

	$tool_content .= "<table width='99%' align='left' class='Group_Operations'>
	<thead>
	<tr>
	<td width='50%'>&nbsp;<a href='group_creation.php' class='operations_container'>$langNewGroupCreate</a></td>
	<td width='50%'><div align='right'><a href='$_SERVER[PHP_SELF]?delete_all=yes' onClick=\"return confirmation('delall');\">$langDeleteGroups</a>&nbsp;</div></td>
	</tr>
	<tr>
	<td>&nbsp;<a href='$_SERVER[PHP_SELF]?fill=yes'>$langFillGroups</a></td>
	<td><div align='right'><a href='$_SERVER[PHP_SELF]?empty=yes' onClick=\"return confirmation('emptyall');\">$langEmtpyGroups</a>&nbsp;</div></td>
	</tr>
	</thead></table><br /><br /><br />";

	// ---------- display properties ------------------------
	$tool_content .= "<table class='FormData' align='center' style='border: 1px solid #CAC3B5;'>
	<tbody>
	<tr class='odd'>
	<td colspan='2' class='right'><a href='group_properties.php'>$langPropModify</a> 
        <a href='group_properties.php'><img src='../../template/classic/img/edit.gif' align='middle' alt='$langEdit' title='$langEdit' /></a></td>
	</tr>
	<tr>
	<td><b>$langGroupsProperties</b></td>
	<td align='right'><b>$langGroupAccess</b></td>
	</tr>";

        $tool_content .= "<tr><td>$langGroupAllowStudentRegistration</td><td align='right'>";
        if ($self_reg) {
                $tool_content .= "<font color='green'>$langYes</font>";
        } else {
                $tool_content .= "<font color='red'>$langNo</font>";
        }
        $tool_content .= "</td></tr>
        <tr><td>$langGroupAllowMultipleRegistration</td><td align='right'>";

        if ($multi_reg) {
                $tool_content .= "<font color='green'>$langYes</font>";
        } else {
                $tool_content .= "<font color='red'>$langNo</font>";
        }
        $tool_content .= "</td></tr>
        <tr><td colspan=2 class='left'><b>$langTools</b></td></tr>
        <tr><td>";

        if ($forum) {
                $tool_content .= "$langGroupForum</td><td align='right'><font color='green'>$langYes</font>";
                $fontColor="black";
        } else {
                $tool_content .= "$langGroupForum</td><td align='right'>
                <font color='red'>$langNo</font>";$fontColor="silver";
        }
        $tool_content .= "</td></tr><tr><td>";
        if ($private_forum) {
                $tool_content .= "$langForumType</td><td align='right'>$langForumClosed";
        } else {
                $tool_content .= "$langForumType</td><td align='right'>$langForumOpen";
        }
        $tool_content .= "</td></tr><tr><td>";
        if ($documents) {
                $tool_content .= "$langDoc</td><td align='right'><font color='green'>$langYes</font>";
        } else {
                $tool_content .= "$langDoc</td><td align='right'><font color='red'>$langNo</font>";
        }
        $tool_content .= "</td></tr>";
	$tool_content .= "</tbody></table>";

	$groupSelect = db_query("SELECT id FROM `group` WHERE course_id = $cours_id ORDER BY id");
	$myIterator = 0;
	$num_of_groups = mysql_num_rows($groupSelect);
	// groups list
	if ($num_of_groups > 0) {
		$tool_content .= "<br />
		<table width='99%' align='left' class='GroupList'>
		<tbody>
		<tr>
		<th class='GroupHead' colspan='2'><div align='left'>$langGroupName</div></th>
		<th class='GroupHead' width='15%'>$langGroupTutor</th>
		<th class='GroupHead'>$langRegistered</th>
		<th class='GroupHead'>$langMax</th>
		<th class='GroupHead' width='50'>$langActions</th>
		</tr>";
                while ($group = mysql_fetch_array($groupSelect)) {
                        initialize_group_info($group['id']);
                        if ($myIterator % 2 == 0) {
                                $tool_content .= "<tr>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $tool_content .= "<td width='2%'>
                        <img src='../../template/classic/img/arrow_grey.gif' alt='' /></td><td>
                        <a href='group_space.php?userGroupId=$group[id]'>".q($name)."</a></td>";
                        $tool_content .= "<td width='35%'>" . display_user($tutors) . "</td>" .
                                         "<td><div class='cellpos'>$member_count</div></td>";
                        if ($max_members == 0) {
                                $tool_content .= "<td><div class='cellpos'>-</div></td>";
                        } else {
                                $tool_content .= "<td><div class='cellpos'>$max_members</div></td>";
                        }
                        $tool_content .= "<td width='10%'><div class='cellpos'>
                        <a href='group_edit.php?userGroupId=$group[id]'>
                        <img src='../../template/classic/img/edit.gif' alt='$langEdit' title='$langEdit' /></a>
                        <a href='$_SERVER[PHP_SELF]?delete=$group[id]' onClick=\"return confirmation('" .
                                js_escape($name) . "');\">
                        <img src='../../template/classic/img/delete.gif' alt='$langDelete' title='$langDelete' /></a></div></td>
                        </tr>";
                        $totalRegistered += $member_count;
                        $myIterator++;
                }
                $tool_content .= "</tbody></table>\n";
	} else {
		$tool_content .= "<p>&nbsp;</p><p class='caution_small'>$langNoGroup</p>";
	}


        list($total_students) = mysql_fetch_row(db_query(
                "SELECT count(*) FROM cours_user
                 WHERE cours_id = $cours_id AND statut = 5 AND tutor = 0"));
        list($unregistered_students) = mysql_fetch_row(db_query(
                "SELECT COUNT(DISTINCT u.user_id)
                        FROM (user u, cours_user cu)
                        LEFT JOIN group_members ug
                             ON u.user_id = ug.user_id
                        WHERE ug.user_id IS NULL AND
                              cu.cours_id = $cours_id AND
                              cu.user_id = u.user_id AND
                              cu.statut = 5 AND
                              cu.tutor = 0"));
	$registered_students = $total_students - $unregistered_students;
	$tool_content .= "<p>&nbsp;</p>" .
	                 "<table width='99%' class='FormData' style='border: 1px solid #edecdf;'>
        <tbody><tr>
	<td class='odd'>
	<p><b>$registered_students</b> $langGroupStudentsInGroup</p>
	<p><b>$unregistered_students</b> $langGroupNoGroup</p>
	<p><b>$total_students</b> $langGroupStudentsRegistered</p><div align='right'>($langGroupUsersList)</div>
	</td></tr></tbody></table>\n";

} else {
        // Begin student view
	$q = db_query("SELECT id FROM `group` WHERE course_id = $cours_id");
        if (mysql_num_rows($q) == 0) {
                $tool_content .= "<p class='alert1'>$langNoGroup</p>";
        } else {
		$tool_content .= "<table width='99%' align='left' class='GroupList'><thead><tr>
                                  <th colspan='2' class='GroupHead'><div align='left'>$langGroupName</div></th>
                                  <th width='15%' class='GroupHead'>$langGroupTutor</th>";
		// If self-registration allowed by admin
		if ($self_reg) {
			$tool_content .= "<th width='50' class='GroupHead'>$langRegistration</th>";
		}
		$tool_content .= "<th width='50' class='GroupHead'>$langRegistered</th>
                                  <th width='50' class='GroupHead'>$langMax</th></tr></thead><tbody>";
                $k = 0;
                while ($row = mysql_fetch_row($q)) {
                        initialize_group_info($row[0]);
                        if ($k % 2 == 0) {
                                $tool_content .= "\n<tr>";
                        } else {
                                $tool_content .= "\n<tr class='odd'>";
                        }
                        $tool_content .= "<td width='2%'><img src='../../template/classic/img/arrow_grey.gif' alt='' /></td>
                                          <td class='left'>";
                        // Allow student to enter group only if member
                        if ($is_member) {
                                $tool_content .= "<a href='group_space.php?userGroupId=$row[0]'>" . q($name) .
                                        "</a> <span style='color:#900; weight:bold;'>($langOneMyGroups)</span>";
			} else {
				$tool_content .= q($name);
			}
                        $tool_content .= "</td>";
                        $tool_content .= "<td width='35%' class='center'>" . display_user($tutors) . "</td>";

                        // If self-registration allowed by admin
                        if ($self_reg and !$is_member) {
                                $tool_content .= "<td class='center'>";
                                if (!isset($uid) or $is_member or ($max_members and $member_count >= $max_members)) {
                                        $tool_content .= "-";
                                } else {
                                        $tool_content .= "<a href='group_space.php?selfReg=1&amp;userGroupId=$row[0]'>$langRegistration</a>";
                                }
                                $tool_content .= "</td>";
                        }

                        $tool_content .= "<td class='center'>$member_count</td><td class='center'>" .
                                         ($max_members? $max_members: '-') . "</td></tr>\n";
                        $totalRegistered += $member_count;
                        $k++;
                }

                $tool_content .= "</tbody></table>";
	}
}

add_units_navigation(TRUE);

if ($is_adminOfCourse) {
	draw($tool_content, 2, 'group', $head_content);
} else {
	draw($tool_content, 2, 'group');
}
