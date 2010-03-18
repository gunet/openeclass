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

$teachers = 0;
$students = 0;
$visitors = 0;
while ($numrows=mysql_fetch_array($result_numb)) {
	switch ($numrows['statut'])
	{
		case 1:	 $teachers++; break;
		case 5:	 $students++; break;
		case 10: $visitors++; break;
		default: break;
	}
}

$nameTools = $langAdminUsers;
$tool_content = "";

// IF PROF ONLY
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
                db_query("DELETE FROM user_group WHERE user = $new_tutor_gid", $currentCourseID);
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
                                              cours_id = $cours_id", $mysqlMainDb);
                        db_query("DELETE FROM user_group
                                        WHERE user = $unregister_gid", $currentCourseID);
                }
        }

        // show help link and link to Add new user, search new user and management page of groups
	$tool_content .= "<table width='99%' align='left' class='Users_Operations'><thead>
	<tr>
	<td colspan='3'>&nbsp;<b>$langDumpUser $langCsv:</b>
	<br />&nbsp;&nbsp;1.&nbsp;<a href='dumpuser2.php'>$langcsvenc2</a>
	&nbsp;&nbsp;2.&nbsp;<a href='dumpuser2.php?enc=1253'>$langcsvenc1</a>
	</td>
	</tr>
	<tr>
	<td width='20%'><a href='../group/group.php'><b>$langGroupUserManagement</b></a></td>
	<td width='15%'><a href='searchuser.php'><b>$langSearchUser</b></a></td>
	<td><b>$langAdd:</b>&nbsp; <a href='adduser.php'>$langOneUser</a>, <a href='muladduser.php'>$langManyUsers</a>, <a href='guestuser.php'>$langGUser</a>&nbsp;</td>
	</tr></thead></table>";

	// display number of users
	$tool_content .= "<table width='99%' class='FormData' style='border: 1px solid #CAC3B5;'>
	<tbody><tr>
	<td class='odd'>
	<p>$langThereAre <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents, <b>$visitors</b> $langVisitors</p>
	<div align='right'>$langTotal: <b>$countUser</b> $langUsers</div>
	</td>
	</tr></tbody>
	</table><p>&nbsp;</p>";

}


// display list of users

// navigation buttons
$endList = 20;

if (isset($numbering) && $numbering) {
	if($numbList == 'more') {
		$startList = $startList + $endList;
	} elseif($numbList == 'less') {
		$startList = abs($startList - $endList);
	} elseif($numbList == 'all') {
		$startList = 0;
		$endList = 2000;
	} elseif($numbList == 'begin') {
		$startList=0;
	} elseif($numbList == 'final') {
		$startList=((int)($countUser / $endList)*$endList);
	}
} else {
        // default status for the list: users 0 to 50
	$startList = 0;
}

// Numerating the items in the list to show: starts at 1 and not 0
$i = $startList + 1;

// Do not show navigation buttons if less than 50 users
if ($countUser >= $endList) {
	$tool_content .= "
   <table width='99%' class='NavUser'>
   <thead>
   <tr>
     <td valign='bottom' align='left' width='20%'>
       <form method='post' action='$_SERVER[PHP_SELF]?numbList=begin'>
         <input type='submit' value='<< $langBegin' name='numbering' class='auth_input' />
       </form>
     </td>
     <td valign='bottom' align='center' width='20%'>";

	// if beginning of list or complete listing, do not show "previous" button
	if ($startList!=0) {
		$tool_content .= "
       <form method='post' action='$_SERVER[PHP_SELF]?startList=$startList&amp;numbList=less'>
         <div align='center'><input type='submit' value='< $langPreced50 $endList' name='numbering' class='auth_input' /></div>
       </form>";
	}
	$tool_content .= "
     </td>
     <td valign='bottom' align='center' width='20%'>
       <form method='post' action='$_SERVER[PHP_SELF]?startList=$startList&amp;numbList=all'>
         <div align='center'><input type='submit' value='$langAll' name=numbering class='auth_input' /></div>
       </form>
     </td>
    <td valign='bottom' align='center' width='20%'>";

	// if end of list  or complete listing, do not show "next" button
	if (!((($countUser-$startList)<=$endList) OR ($endList==2000))) {
		$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?startList=$startList&amp;numbList=more'>
		<div align='center'><input type='submit' value='$langFollow50 $endList >' name=numbering class='auth_input' /></div>
		</form>";
	}
	$tool_content .= "
     </td>
     <td valign='bottom' width='20%'>
       <div align='right'>
       <form method='post' action='$_SERVER[PHP_SELF]?numbList=final'>
         <input type='submit' value='$langEnd >>' name='numbering' class='auth_input' />
       </form>
       </div>
     </td>
     </tr>
   </thead>
   </table>";

}	// Show navigation buttons

$tool_content .= "
   <table width=99% class='FormData' style='border: 1px solid #CAC3B5;'>
   <thead>
   <tr class='odd'>
     <td rowspan='2' class='UsersHead'>$langID</td>
     <td rowspan='2' class='UsersHead'><div align='left'>$langSurname<br />$langName</div></td>";

if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .="<td rowspan='2' class='UsersHead'>$langEmail</td>";
}

$tool_content .= "<td rowspan='2' class='UsersHead'>$langAm</td>
     <td rowspan='2' class='UsersHead'>$langGroup</td>
     <td rowspan='2' class='UsersHead'>$langCourseRegistrationDate</td>";

// show admin tutor and unregister only to admins
if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "
     <td colspan='2' class='UsersHead'>$langUserPermitions</td>
     <td rowspan='2' class='UsersHead'>$langActions</td>";
}

$tool_content .= "
   </tr>";

if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "
  <tr>
     <td class='UsersHead'>$langTutor</td>
     <td class='UsersHead'>$langAdministrator</td>
  </tr>";
}

$result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.am, cours_user.statut,
		cours_user.tutor, cours_user.reg_date, user_group.team
		FROM `$mysqlMainDb`.cours_user, `$mysqlMainDb`.user
		LEFT JOIN `$currentCourseID`.user_group
		ON user.user_id = user_group.user
		WHERE `user`.`user_id` = `cours_user`.`user_id` AND `cours_user`.`cours_id` = $cours_id
		ORDER BY nom, prenom LIMIT $startList, $endList", $db);

$tool_content .= "</thead>\n";

while ($myrow = mysql_fetch_array($result)) {
        // bi colored table
        if ($i%2 == 0) {
                $tool_content .= "<tr>";
        } else {
                $tool_content .= "<tr class='odd'>";
        }
        // show public list of users
        $tool_content .= "<td valign='top' align='right'>$i.</td>\n" .
                "<td valign='top'>$myrow[nom]<br />$myrow[prenom]</td>\n";

        if (isset($status) and ($status[$currentCourseID] == 1 or $status[$currentCourseID] == 2))  {
                $tool_content .= "<td valign='top' align='center'><a href='mailto:$myrow[email]'>$myrow[email]</a></td>";
        }
        $tool_content .= "<td valign='top' align='center'>$myrow[am]</td>\n" .
                "<td valign=top align='center'>\n";

        // NULL and not '0' because team may not exist
        if ($myrow['team'] == NULL) {
                $tool_content .= $langUserNoneMasc;
        } else {
                $tool_content .= gid_to_name($myrow['team']);
        }
        $tool_content .= "</td>" .
                "<td align='center'>";
        if ($myrow['reg_date'] == '0000-00-00') {
                $tool_content .= $langUnknownDate;
        } else {
                $tool_content .= nice_format($myrow['reg_date']);
        }
        $tool_content .= "</td>";

        // ************** tutor, admin and unsubscribe (admin only) ******************************
        if(isset($status) && ($status["$currentCourseID"]=='1' OR $status["$currentCourseID"]=='2')) {
                // tutor right
                if ($myrow['tutor'] == '0') {
                        $tool_content .= "<td valign='top' align='center' class='add_user'><a href='$_SERVER[PHP_SELF]?giveTutor=$myrow[user_id]' title='$langGiveTutor'>$langAdd</a></td>";
                } else {
                        $tool_content .= "<td class='highlight' align='center'>$langTutor<br /><a href='$_SERVER[PHP_SELF]?removeTutor=$myrow[user_id]' title='$langRemoveRight'>$langRemove</a></td>";
                }

                // admin right
                if ($myrow['user_id'] != $_SESSION["uid"]) {
                        if ($myrow['statut']=='1') {
                                $tool_content .= "<td class='highlight' align='center'>$langAdministrator<br /><a href='$_SERVER[PHP_SELF]?removeAdmin=$myrow[user_id]' title='$langRemoveRight'>$langRemove</a></td>";
                        } else {
                                $tool_content .= "<td valign='top' align='center' class='add_user'><a href='$_SERVER[PHP_SELF]?giveAdmin=$myrow[user_id]' title='$langGiveAdmin'>$langAdd</a></td>";
                        }
                } else {
                        if ($myrow['statut']=='1') {
                                $tool_content .= "<td valign='top' class='highlight' align='center' title='$langAdmR'><b>$langAdministrator</b></td>";
                        } else {
                                $tool_content .= "<td valign='top' align='center'><a href='$_SERVER[PHP_SELF]?giveAdmin=$myrow[user_id]'>$langGiveAdmin</a></td>";
                        }
                }
                $tool_content .= "<td valign='top' align='center'>";
                $alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
                $tool_content .= "<a href='$_SERVER[PHP_SELF]?unregister=$myrow[user_id]' onClick=\"return confirmation('".addslashes($alert_uname)."');\"><img src='../../template/classic/img/delete.gif' title='$langDelete' /></a>";
        }	// admin only
        $tool_content .= "</td></tr>";$i++;
} 	// end of while

$tool_content .= "
   </table>";

// navigation buttons
// Do not show navigation buttons if less than 50 users
if($countUser>=50) {
	$tool_content .= "
	<table width='99%' >
	<tr>
	<td valign='bottom' align='left' width='20%'>
	<form method='post' action='$_SERVER[PHP_SELF]?numbList=begin'>
	<input type='submit' value='<< $langBegin' name='numbering' class='auth_input' />
	</form>
	</td>
	<td valign='bottom' align='center' width='20%'>";
	
	if ($startList!=0) {
		$tool_content .= "
		<form method='post' action='$_SERVER[PHP_SELF]?startList=$startList&amp;numbList=less'>
		<input type='submit' value='< $langPreced50 $endList' name='numbering' class='auth_input' />
		</form>";
	}
	$tool_content .= "</td>
	<td valign='bottom' align='center' width='20%'>
	<form method='post' action='".$_SERVER['PHP_SELF']."?startList=$startList&amp;numbList=all'>
		<input type='submit' value='$langAll' name='numbering' class='auth_input' />
	</form>
	</td>
	<td valign='bottom' align='center' width='20%'>";
	if (!((( $countUser-$startList ) <= 50) OR ($endList == 2000))) {
		$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?startList=$startList&amp;numbList=more'>
		<input type='submit' value='$langFollow50 $endList >' name='numbering' class='auth_input' />
		</form>";
	}
	$tool_content .= "</td>
	<td valign='bottom' align='right' width='20%'>
	<form method='post' action='$_SERVER[PHP_SELF]?numbList=final'>
	<input type='submit' value='$langEnd >>' name='numbering' class='auth_input' />
	</form>
	</td></tr></table>";
}	// navigation buttons

add_units_navigation(true);
draw($tool_content, 2, 'user', $head_content);
