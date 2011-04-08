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
 * User Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr> is responsible for the user administration
 *
 */

$require_login = true;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
$require_prof = true;

include '../../include/baseTheme.php';
include '../admin/admin.inc.php';

define ('COURSE_USERS_PER_PAGE', 15);

$limit = isset($_REQUEST['limit'])?$_REQUEST['limit']:0;

$nameTools = $langAdminUsers;
$q = '';

$head_content = '
<script type="text/javascript">
function confirmation (name)
{
    if (confirm("'.$langDeleteUser.' "+ name + " '.$langDeleteUser2.' ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

$sql = "SELECT user.user_id, cours_user.statut FROM cours_user, user
	WHERE cours_user.cours_id = $cours_id AND cours_user.user_id = user.user_id";
$result_numb = db_query($sql, $mysqlMainDb);
$countUser = mysql_num_rows($result_numb);

$teachers = $students = $visitors = 0;

while ($numrows=mysql_fetch_array($result_numb)) {
	switch ($numrows['statut'])
	{
		case 1:	 $teachers++; break;
		case 5:	 $students++; break;
		case 10: $visitors++; break;
		default: break;
	}
}

if ($is_adminOfCourse) {
        // Handle user removal / status change
        if (isset($_GET['giveAdmin'])) {
                $new_admin_gid = intval($_GET['giveAdmin']);
                db_query("UPDATE cours_user SET statut = 1
                                WHERE user_id = $new_admin_gid AND cours_id = $cours_id", $mysqlMainDb);
        } elseif (isset($_GET['giveTutor'])) {
                $new_tutor_gid = intval($_GET['giveTutor']);
                db_query("UPDATE cours_user SET tutor = 1
                                WHERE user_id = $new_tutor_gid AND cours_id = $cours_id", $mysqlMainDb);
                db_query("UPDATE group_members, `group` SET is_tutor = 0
				WHERE `group`.id = group_members.group_id AND 
				      `group`.course_id = $cours_id AND
				      group_members.user_id = $new_tutor_gid");
        } elseif (isset($_GET['removeAdmin'])) {
                $removed_admin_gid = intval($_GET['removeAdmin']);
                db_query("UPDATE cours_user SET statut = 5
                                WHERE user_id <> $uid AND
                                      user_id = $removed_admin_gid AND
                                      cours_id = $cours_id", $mysqlMainDb);
        } elseif (isset($_GET['removeTutor'])) {
                $removed_tutor_gid = intval($_GET['removeTutor']);
                db_query("UPDATE cours_user SET tutor = 0
                                WHERE user_id = $removed_tutor_gid AND
                                      cours_id = $cours_id", $mysqlMainDb);
        } elseif (isset($_GET['unregister'])) {
                $unregister_gid = intval($_GET['unregister']);
                $unregister_ok = true;
                // Security: don't remove myself except if there is another prof
                if ($unregister_gid == $uid) {
                        $result = db_query("SELECT user_id FROM cours_user
                                                WHERE cours_id = $cours_id AND
                                                      statut = 1 AND
                                                      user_id != $uid
                                                LIMIT 1", $mysqlMainDb);
                        if (mysql_num_rows($result) == 0) {
                                $unregister_ok = false;
                        }
                }
                if ($unregister_ok) {
                        db_query("DELETE FROM cours_user
                                        WHERE user_id = $unregister_gid AND
                                              cours_id = $cours_id");
                        db_query("DELETE FROM group_members
                                        WHERE user_id = $unregister_gid AND
                                              group_id IN (SELECT id FROM `group` WHERE course_id = $cours_id)");
                }
        }
        // show help link and link to Add new user, search new user and management page of groups
	$tool_content .= "

        <div id='operations_container'>
          <ul id='opslist'>
            <li>$langAdd:</b>&nbsp; <a href='adduser.php'>$langOneUser</a></li>
            <li><a href='muladduser.php'>$langManyUsers</a></li>
            <li><a href='guestuser.php'>$langGUser</a>&nbsp;</li>
            <li><a href='searchuser.php'>$langSearchUser</a></li>
            <li><a href='../group/group.php'>$langGroupUserManagement</a></li>
          </ul>
        </div>";

	// display number of users
	$tool_content .= "
        <table width='99%' class='tbl_border'>
	<tr class='smaller'>
	  <th><b>$langTotal</b>: <b>$countUser</b> $langUsers &nbsp;&nbsp;(<b>$teachers</b> $langTeachers, <b>$students</b> $langStudents, <b>$visitors</b> $langVisitors)<br />
          <b>$langDumpUser $langCsv</b>:
              &nbsp;&nbsp;1.&nbsp;<a href='dumpuser2.php'>$langcsvenc2</a>
              &nbsp;&nbsp;2.&nbsp;<a href='dumpuser2.php?enc=1253'>$langcsvenc1</a>
          </th>
        </tr>
	</table>
        <br />";

	// display navigation links if course users > COURSE_USERS_PER_PAGE
	if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
		$q = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
		$tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser, "$_SERVER[PHP_SELF]", '', TRUE);
	}
	
	if (isset($_GET['all']) and $_GET['all'] == true) {
		$extra_link = '&amp;all=true';
	} else {
		$extra_link = "&amp;limit=".$limit;
	}

	$tool_content .= "
        <table width='99%' class='tbl_alt'>
        <tr class='smaller'>
	  <th rowspan='2' width='1'>$langID</th>
	  <th rowspan='2'><div align='left'><a href='$_SERVER[PHP_SELF]?ord=s$extra_link'>$langName $langSurname</a></div></th>
	  <th rowspan='2' class='center'>$langGroup</th>
	  <th rowspan='2' class='center'><a href='$_SERVER[PHP_SELF]?ord=rd$extra_link'>$langRegistrationDate</a></th>
	  <th colspan='2' class='center'>$langUserPermitions</th>
          <th rowspan='2' width='10'>$langActions</th>
	</tr>
	<tr class='smaller'>
          <th width='10' class='center'>$langTutor</th>
          <th width='10' class='center'>$langAdministrator</th>
        </tr>";
	
	// Numerating the items in the list to show: starts at 1 and not 0
	$i = $limit + 1;
	$ord = isset($_GET['ord'])?$_GET['ord']:'';
	
	switch ($ord) {
		case 's': $order = 'ORDER BY nom';
			break;
		case 'e': $order = 'ORDER BY email';
			break;
		case 'am': $order = 'ORDER BY am';
			break;
		case 'rd': $order = 'ORDER BY cours_user.reg_date DESC';
			break;
		default: $order = 'ORDER  BY nom, prenom';
			break;
	}
	$result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email,
				   user.am, user.has_icon, cours_user.statut,
				   cours_user.tutor, cours_user.reg_date
			    FROM cours_user, user
			    WHERE `user`.`user_id` = `cours_user`.`user_id` AND `cours_user`.`cours_id` = $cours_id"
			   ." ".$order." ".$q); 

	while ($myrow = mysql_fetch_array($result)) {
		// bi colored table
		if ($i%2 == 0) {
			$tool_content .= "
        <tr class='even'>";
		} else {
			$tool_content .= "
        <tr class='odd'>";
		}
                // show public list of users
                $am_message = empty($myrow['am'])? '': ("<div class='smaller'>($langAm: " . q($myrow['am']) . ")</div>");
		$tool_content .= "
          <td class='smaller' valign='top' align='right'>$i.</td>\n" .
			"<td valign='top'>" . display_user($myrow) . "<div class='smaller'>" . mailto($myrow['email']) . "</div>$am_message</td>\n";
		$tool_content .= "\n" .
			"<td class='smaller' valign=top align='center' width='150' class='smaller'>" . user_groups($cours_id, $myrow['user_id']) . "</td>\n" .
			"<td align='center' width='90' class='smaller'>";
		if ($myrow['reg_date'] == '0000-00-00') {
			$tool_content .= $langUnknownDate;
		} else {
			$tool_content .= nice_format($myrow['reg_date']);
		}
		$tool_content .= "</td>";
		// tutor right
		if ($myrow['tutor'] == '0') {
			$tool_content .= "<td valign='top' align='center' class='add_user'><a href='$_SERVER[PHP_SELF]?giveTutor=$myrow[user_id]$extra_link' title='$langGiveTutor'>$langAdd</a></td>";
		} else {
			$tool_content .= "<td class='monthLabel' align='center'>$langTutor<br /><a href='$_SERVER[PHP_SELF]?removeTutor=$myrow[user_id]$extra_link' title='$langRemoveRight'>$langRemove</a></td>";
		}
		// admin right
		if ($myrow['user_id'] != $_SESSION["uid"]) {
			if ($myrow['statut']=='1') {
				$tool_content .= "<td class='smaller' align='center'>$langAdministrator<br /><a href='$_SERVER[PHP_SELF]?removeAdmin=$myrow[user_id]$extra_link' title='$langRemoveRight'>$langRemove</a></td>";
			} else {
				$tool_content .= "<td valign='top' align='center' class='add_user'><a href='$_SERVER[PHP_SELF]?giveAdmin=$myrow[user_id]$extra_link' title='$langGiveAdmin'>$langAdd</a></td>";
			}
		} else {
			if ($myrow['statut']=='1') {
				$tool_content .= "<td valign='top' class='monthLabel' align='center' title='$langAdmR'><b>$langAdministrator</b></td>";
			} else {
				$tool_content .= "<td class='smaller' valign='top' align='center'><a href='$_SERVER[PHP_SELF]?giveAdmin=$myrow[user_id]$extra_link'>$langGiveAdmin</a></td>";
			}
		}
		$tool_content .= "<td valign='top' align='center' class='smaller'>";
		$alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
                $tool_content .= "<a href='$_SERVER[PHP_SELF]?unregister=$myrow[user_id]$extra_link'
                                 onClick=\"return confirmation('" . js_escape($alert_uname) .
                                 "');\"><img src='../../template/classic/img/delete.png' title='$langDelete' /></a>";
                $tool_content .= "</td></tr>";
                $i++;
	}
	$tool_content .= "</table>";
}
add_units_navigation(true);
draw($tool_content, 2, '', $head_content);
