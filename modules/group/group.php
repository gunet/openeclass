<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * @version $Id: group.php,v 1.84 2011-06-24 13:40:33 adia Exp $
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
$action->record(MODULE_ID_GROUPS);
/**************************************/

$nameTools = $langGroups;
$totalRegistered = 0;
unset($message);

$head_content = <<< END
<script type="text/javascript">
function confirmation (name)
{
	if (name == "delall") {
		if(confirm("$langDeleteGroupAllWarn"))
		{return true;}
		else
		{return false;}
	} else if (name == "emptyall") {
		if (confirm("$langDeleteGroupAllWarn"))
		{return true;}
		else
		{return false;}
	} else {
		if (confirm("$langDeleteGroupWarn ("+ name + ") "))
        {return true;}
    	else
        {return false;}
    }
}
function confirm_delete()
{
    if (confirm("$langConfirmDelete"))
        {return true;}
    else
        {return false;}
}
</script>
END;

unset($_SESSION['secret_directory']);
unset($_SESSION['forum_id']);

mysql_select_db($mysqlMainDb);
initialize_group_info();
$user_groups = user_group_info($uid, $cours_id);

if ($is_editor) {
        if (isset($_POST['creation'])) {
                $group_quantity = intval($_POST['group_quantity']);
                if (preg_match('/^[0-9]/', $_POST['group_max'])) {
                        $group_max = intval($_POST['group_max']);
                } else {
                        $group_max = 0;
                }
                list($group_num) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM `group` WHERE course_id = $cours_id"));

                // Create a hidden category for group forums
                $req = db_query("SELECT cat_id FROM categories 
                                WHERE cat_order = -1
                                AND course_id = $cours_id");
                if ($req and mysql_num_rows($req) > 0) {
                        list($cat_id) = mysql_fetch_row($req);
                } else {
                        db_query("INSERT INTO categories (cat_title,  cat_order, course_id)
                                         VALUES ('$langCatagoryGroup', -1, $cours_id)");
                        $cat_id = mysql_insert_id();
                }

                for ($i = 1; $i <= $group_quantity; $i++) {
                        do {
                                $group_num++;
                                $res = db_query("SELECT id FROM `group` WHERE name = '$langGroup $group_num'");
                        } while (mysql_num_rows($res) > 0);

                        db_query("INSERT INTO forums (forum_name, forum_desc, forum_access,
                                                      forum_moderator, forum_topics, forum_posts,
                                                      forum_last_post_id, cat_id, forum_type, course_id)
                                  VALUES ('$langForumGroup $group_num', '', 2, 1, 0, 0, 1, $cat_id, 0, $cours_id)");
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
                        'has_forum' => true,
                        'documents' => true), 'all', 'intval');
                db_query("UPDATE group_properties SET
                                 self_registration = $self_reg,
                                 multiple_registration = $multi_reg,
                                 private_forum = $private_forum,
                                 forum = $has_forum,
                                 documents = $documents WHERE course_id = $cours_id");
                $message = $langGroupPropertiesModified;

        } elseif (isset($_REQUEST['delete_all'])) {
                db_query("DELETE FROM group_members WHERE group_id IN (SELECT id FROM `group` WHERE course_id = $cours_id)");
                db_query("DELETE FROM `group` WHERE course_id = $cours_id");
                db_query("DELETE FROM document WHERE course_id = $cours_id AND subsystem = 1");
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

                db_query("DELETE FROM document WHERE course_id = $cours_id AND subsystem = 1 AND subsystem_id = $id");
                db_query("DELETE FROM group_members WHERE group_id = $id");
                db_query("DELETE FROM `group` WHERE id = $id");
                $message = $langGroupDel;

        } elseif (isset($_REQUEST['empty'])) {
                $result = db_query("DELETE FROM group_members
				   WHERE group_id IN
				   (SELECT id FROM `group` WHERE course_id = $cours_id)");
                $message = $langGroupsEmptied;

        } elseif (isset($_REQUEST['fill'])) {
                $resGroups = db_query("SELECT id, max_members -
                                                      (SELECT count(*) from group_members WHERE group_members.group_id = id)
                                                  AS remaining
                                              FROM `group` WHERE course_id = $cours_id ORDER BY id");
                while (list($idGroup, $places) = mysql_fetch_row($resGroups)) {
                        if ($places > 0) {
                                $placeAvailableInGroups[$idGroup] = $places;
                        }
                }
                // Course members not registered to any group
                $resUserSansGroupe= db_query("
                        SELECT u.user_id, u.nom, u.prenom
                                FROM (user u, cours_user cu)
                                WHERE cu.cours_id = $cours_id AND
                                      cu.user_id = u.user_id AND
                                      cu.statut = 5 AND
                                      cu.tutor = 0 AND
                                      u.user_id NOT IN (SELECT user_id FROM group_members, `group`
                                                                       WHERE `group`.id = group_members.group_id AND
                                                                       `group`.course_id = $cours_id)
                                GROUP BY u.user_id
                                ORDER BY u.nom, u.prenom");
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
		$tool_content .= "<p class='success'>$message</p><br />";
	}

          $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='group_creation.php?course=$code_cours' title='$langNewGroupCreate'>$langCreate</a></li>
            <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;delete_all=yes' onClick=\"return confirmation('delall');\" title='$langDeleteGroups'>$langDelete</a></li>
            <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;fill=yes' title='$langFillGroups'>$langFillGroupsAll</a></li>
            <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;?empty=yes' onClick=\"return confirmation('emptyall');\" title='$langEmtpyGroups'>$langEmtpyGroupsAll</a></li>
          </ul>
        </div>";

	// ---------- display properties ------------------------
	$tool_content .= "
        <table class='tbl_courseid' width='100%'>
	<tr>
	  <td class='title1' colspan='2'><a href='group_properties.php?course=$code_cours' title='$langPropModify'>$langGroupProperties</a>&nbsp;
              <a href='group_properties.php?course=$code_cours' title='$langPropModify'><img src='$themeimg/edit.png' align='middle' alt='$langPropModify' title='$langPropModify' /></a>
          </td>
          <td class='even'>&nbsp;</td>
          <td class='title1'>$langGroupUsersList</td>
	</tr>";

        list($total_students) = mysql_fetch_row(db_query(
                "SELECT COUNT(*) FROM cours_user
                 WHERE cours_id = $cours_id AND statut = 5 AND tutor = 0"));
        list($unregistered_students) = mysql_fetch_row(db_query(
                        "SELECT COUNT(*)
                                FROM (user u, cours_user cu)
                                WHERE cu.cours_id = $cours_id AND
                                      cu.user_id = u.user_id AND
                                      cu.statut = 5 AND
                                      cu.tutor = 0 AND
                                      u.user_id NOT IN (SELECT user_id FROM group_members, `group`
                                                                       WHERE `group`.id = group_members.group_id AND
                                                                       `group`.course_id = $cours_id)"));

        $registered_students = $total_students - $unregistered_students;

        $tool_content .= "
        <tr>
          <td colspan='2'><u>$langGroupPrefs</u></td>
          <td rowspan='7' class='even'>&nbsp;</td>
          <td>
            <img src='$themeimg/arrow.png' alt='' />&nbsp;<b>$registered_students</b> $langGroupStudentsInGroup
          </td>
        </tr>
        <tr>
          <td class='smaller'><img src='$themeimg/arrow.png' alt='' />&nbsp;$langGroupAllowStudentRegistration</td> 
          <td align='right' width='50'>";
        if ($self_reg) {
                $tool_content .= "<font color='green'>$langYes</font>";
        } else {
                $tool_content .= "<font color='red'>$langNo</font>";
        }
        $tool_content .= "</td>
          <td><img src='$themeimg/arrow.png' alt='' />&nbsp;<b>$unregistered_students</b> $langGroupNoGroup</td>
        </tr>
        <tr>
          <td class='smaller'><img src='$themeimg/arrow.png' alt='' />&nbsp;$langGroupAllowMultipleRegistration</td>
          <td align='right'>";

        if ($multi_reg) {
                $tool_content .= "<font color='green'>$langYes</font>";
        } else {
                $tool_content .= "<font color='red'>$langNo</font>";
        }
        $tool_content .= "</td>
          <td><img src='$themeimg/arrow.png' alt='' />&nbsp;<b>$total_students</b> $langGroupStudentsRegistered</td>
        </tr>
        <tr>
          <td colspan=2 class='left'><u>$langTools</u></td>
        </tr>
        <tr>
          <td class='smaller'><img src='$themeimg/arrow.png' alt='' />&nbsp;";

        if ($has_forum) {
                $tool_content .= "$langGroupForum</td>
          <td align='right'><font color='green'>$langYes</font>";
                $fontColor="black";
        } else {
                $tool_content .= "$langGroupForum</td>
          <td align='right'>
                <font color='red'>$langNo</font>";$fontColor="silver";
        }
        $tool_content .= "</td>
        </tr>
        <tr>
          <td class='smaller'><img src='$themeimg/arrow.png' alt='' />&nbsp;";
        if ($private_forum) {
                $tool_content .= "$langForumType</td>
          <td align='right'><font color='red'>$langForumClosed</font>";
        } else {
                $tool_content .= "$langForumType</td>
          <td align='right'><font color='green'>$langForumOpen</font>";
        }
        $tool_content .= "</td>
        </tr>
        <tr>
          <td class='smaller'><img src='$themeimg/arrow.png' alt='' />&nbsp;";
        if ($documents) {
                $tool_content .= "$langDoc</td>
          <td align='right'><font color='green'>$langYes</font>";
        } else {
                $tool_content .= "$langDoc</td>
          <td align='right'><font color='red'>$langNo</font>";
        }
        $tool_content .= "</td>
        </tr>";
	$tool_content .= "
        </table>";

	$groupSelect = db_query("SELECT id FROM `group` WHERE course_id = $cours_id ORDER BY id");
	$myIterator = 0;
	$num_of_groups = mysql_num_rows($groupSelect);
	// groups list
	if ($num_of_groups > 0) {
		$tool_content .= "<br />
		<table width='100%' align='left' class='tbl_alt'>
		<tr>
           
		  <th colspan='2'><div align='left'>$langGroupName</div></th>
		  <th width='250'>$langGroupTutor</th>
		  <th width='30'>$langRegistered</th>
		  <th width='30'>$langMax</th>
		  <th width='30'>$langActions</th>
		</tr>";
                while ($group = mysql_fetch_array($groupSelect)) {
                        initialize_group_info($group['id']);
                        if ($myIterator % 2 == 0) {
                                $tool_content .= "
                <tr class='even'>";
                        } else {
                                $tool_content .= "
                <tr class='odd'>";
                        }
                        $tool_content .= "
                  <td width='16'>
                        <img src='$themeimg/arrow.png' alt='' /></td><td>
                        <a href='group_space.php?course=$code_cours&amp;group_id=$group[id]'>".q($group_name)."</a></td>";
                        $tool_content .= "
                  <td>" . display_user($tutors) . "</td>" . "
                  <td class='center'>$member_count</td>";
                        if ($max_members == 0) {
                                $tool_content .= "
                  <td>-</td>";
                        } else {
                                $tool_content .= "
                  <td class='center'>$max_members</td>";
                        }
                        $tool_content .= "
                  <td class='center'>
                        <a href='group_edit.php?course=$code_cours&amp;group_id=$group[id]'>
                        <img src='$themeimg/edit.png' alt='$langEdit' title='$langEdit' /></a>
                        <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;delete=$group[id]' onClick=\"return confirmation('" .
                                js_escape($group_name) . "');\">
                        <img src='$themeimg/delete.png' alt='$langDelete' title='$langDelete' /></a></td>
                </tr>";
                        $totalRegistered += $member_count;
                        $myIterator++;
                }
                $tool_content .= "
              </table><br />\n";
	} else {
		$tool_content .= "
              <p class='alert1'>$langNoGroup</p>";
	}


} else {
        // Begin student view
	$q = db_query("SELECT id FROM `group` WHERE course_id = $cours_id");
        if (mysql_num_rows($q) == 0) {
                $tool_content .= "<p class='alert1'>$langNoGroup</p>";
        } else {
		$tool_content .= "
                <table width='100%' align='left' class='tbl_alt'>
                <tr>
                  <th colspan='2'><div align='left'>$langGroupName</div></th>
                  <th width='250'>$langGroupTutor</th>";
		// If self-registration allowed by admin
		if ($self_reg) {
			$tool_content .= "
                  <th width='50'>$langRegistration</th>";
		}
		$tool_content .= "
                  <th width='50'>$langRegistered</th>
                  <th width='50'>$langMax</th>
                </tr>";
                $k = 0;
                while ($row = mysql_fetch_row($q)) {
                        initialize_group_info($row[0]);
                        if ($k % 2 == 0) {
                                $tool_content .= "
                <tr class='even'>";
                        } else {
                                $tool_content .= "
                <tr class='odd'>";
                        }
                        $tool_content .= "
                  <td width='2'><img src='$themeimg/arrow.png' alt='' /></td>
                  <td class='left'>";
                        // Allow student to enter group only if member
                        if ($is_member) {
                                $tool_content .= "<a href='group_space.php?course=$code_cours&amp;group_id=$row[0]'>" . q($group_name) .
                                        "</a> <span style='color:#900; weight:bold;'>($langMyGroup)</span>";
			} else {
				$tool_content .= q($group_name);
			}
			if ($user_group_description) {
				$tool_content .= "<br />".q($user_group_description)."&nbsp;&nbsp;
					<a href='group_description.php?course=$code_cours&amp;group_id=$row[0]'>
						<img src='$themeimg/edit.png' title='$langModify' /></a>
					<a href='group_description.php?course=$code_cours&amp;group_id=$row[0]&amp;delete=true' onClick=\"return confirm_delete();\">
						<img src='$themeimg/delete.png' title='$langDelete' /></a>";
			} elseif ($is_member) {
				$tool_content .= "<br /><a href='group_description.php?course=$code_cours&amp;group_id=$row[0]'><i>$langAddDescription</i></a>";
			}
                        $tool_content .= "</td>";
                        $tool_content .= "
                  <td class='center'>" . display_user($tutors) . "</td>";
			
                        // If self-registration and multi registration allowed by admin and group is not full
                        $tool_content .= "
                  <td class='center'>";
			if ($uid and
			    $self_reg and
			    (!$user_groups or $multi_reg) and
			    !$is_member and
			    (!$max_members or $member_count < $max_members)) {
                                        $tool_content .= "<a href='group_space.php?course=$code_cours&amp;selfReg=1&amp;group_id=$row[0]'>$langRegistration</a>";
			} else {
                                        $tool_content .= "-";
			}
                        $tool_content .= "</td>";
                        $tool_content .= "
                  <td class='center'>$member_count</td>
                  <td class='center'>" .
                                         ($max_members? $max_members: '-') . "</td>
                </tr>\n";
                        $totalRegistered += $member_count;
                        $k++;
                }
                $tool_content .= "
                </table>";
	}
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
