<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

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
<script>
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
	WHERE code_cours='$currentCourseID' AND cours_user.user_id = user.user_id";
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
$tool_content = ""; //initialise $tool_content

// IF PROF ONLY
//  show  help link and  link to Add new  user and  management page of groups
if ($is_adminOfCourse) {

	$tool_content .= "
    <table width=\"99%\" align=\"left\" class=\"Group_Operations\">
    <thead>
    <tr>
      <td width=\"50%\">&nbsp;<b>$langDumpUser</b> <a href=\"dumpuser.php\">$langExcel</a> <a href=\"dumpuser2.php\">$langCsv</a></td>
      <td width=\"50%\"><div align=\"right\"><a href=\"../group/group.php\">$langGroupUserManagement</a>&nbsp;</div></td>
    </tr>
    <tr>
      <td>&nbsp;<b>$langAdd:</b>&nbsp; <a href=\"adduser.php\">$langOneUser</a>, <a href=\"muladduser.php\">$langManyUsers</a>, <a href=\"guestuser.php\">$langGUser</a>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    </thead>
    </table>
	<br />
    <br />
    ";

	// display number of users
$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"odd\">
        <p>$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents, <b>$visitors</b> $langVisitors</p>
        <p align=\"right\">$langTotal: <b>$countUser</b> $langUsers</p>
      </td>
    </tr>
    </tbody>
    </table>
    <br />";

}	// if prof

// give admin status
if(isset($giveAdmin) && $giveAdmin && $is_adminOfCourse) {
	$result = db_query("UPDATE cours_user SET statut='1'
		WHERE user_id='".mysql_real_escape_string($_GET['user_id'])."' AND code_cours='$currentCourseID'",$mysqlMainDb);
}

// give tutor status
elseif(isset($giveTutor) && $giveTutor) {
	$result = db_query("UPDATE cours_user SET tutor='1'
		WHERE user_id='".mysql_real_escape_string($_GET['user_id'])."' AND code_cours='$currentCourseID'",$mysqlMainDb);
	$result2=db_query("DELETE FROM user_group WHERE user='".mysql_real_escape_string($_GET['user_id'])."'", $currentCourseID);
}

// remove admin status
elseif(isset($removeAdmin) && $removeAdmin) {
	$result = db_query("UPDATE cours_user SET statut='5'
		WHERE user_id!= $uid AND user_id='".mysql_real_escape_string($_GET['user_id'])."' "
		."AND code_cours='$currentCourseID'",$mysqlMainDb);
}

// remove tutor status
elseif(isset($removeTutor) && $removeTutor) {
	$result = db_query("UPDATE cours_user SET tutor='0'
		WHERE user_id='".mysql_real_escape_string($_GET['user_id'])."' "
		."AND code_cours='$currentCourseID'",$mysqlMainDb);
}

// unregister user from courses
elseif(isset($unregister) && $unregister) {
	// security : cannot remove myself
	$result = db_query("DELETE FROM cours_user WHERE user_id!= $uid
		AND user_id='".mysql_real_escape_string($_GET['user_id'])."' "
		."AND code_cours='$currentCourseID'", $mysqlMainDb);
	$delGroupUser=db_query("DELETE FROM user_group WHERE user='".mysql_real_escape_string($_GET['user_id'])."'", $currentCourseID);
}

// display list of users

// navigation buttons
$endList=20;

if(isset ($numbering) && $numbering) {
	if($numbList=="more") {
		$startList=$startList+$endList;
	}
	elseif($numbList=="less") {
		$startList=abs($startList-$endList);
	}
	elseif($numbList=="all") {
		$startList=0;
		$endList=2000;
	}
	elseif($numbList=="begin") {
		$startList=0;
	}
	elseif($numbList=="final") {
		$startList=((int)($countUser / $endList)*$endList);
	}
}	// if numbering

// default status for the list: users 0 to 50
else {
	$startList=0;
}

// Numerating the items in the list to show: starts at 1 and not 0
$i=$startList+1;

// Do not show navigation buttons if less than 50 users
if ($countUser>=$endList) {
	$tool_content .= "
   <table width=99% class=\"NavUser\">
   <thead>
   <tr>
     <td valign=bottom align=left width=20%>
       <form method=post action=\"$_SERVER[PHP_SELF]?numbList=begin\">
         <input type=submit value=\"<< $langBegin\" name=\"numbering\" class=\"auth_input\">
       </form>
     </td>
     <td valign=bottom align=middle width=20%>";

	// if beginning of list or complete listing, do not show "previous" button
	if ($startList!=0) {
		$tool_content .= "
       <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
         <div align=\"center\"><input type=submit value=\"< $langPreced50 $endList\" name=\"numbering\" class=\"auth_input\"></div>
       </form>";
	}
	$tool_content .= "
     </td>
     <td valign=bottom align=middle width=20%>
       <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=all\">
         <div align=\"center\"><input type=submit value=\"$langAll\" name=numbering class=\"auth_input\"></div>
       </form>
     </td>
    <td valign=bottom align=middle width=20%>";

	// if end of list  or complete listing, do not show "next" button
	if (!((($countUser-$startList)<=$endList) OR ($endList==2000))) {
		$tool_content .= "
     <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
       <div align=\"center\"><input type=submit value=\"$langFollow50 $endList >\" name=numbering class=\"auth_input\"></div>
     </form>";
	}
	$tool_content .= "
     </td>
     <td valign=bottom width=20%>
       <div align='right'>
       <form method=post action=\"$_SERVER[PHP_SELF]?numbList=final\">
         <input type=submit value=\"$langEnd >>\" name=numbering class=\"auth_input\">
       </form>
       </div>
     </td>
     </tr>
   </thead>
   </table>";

}	// Show navigation buttons

$tool_content .= "
   <table width=99% class=\"NavUser\">
   <thead>
   <tr>
     <td width=\"2%\" rowspan=\"2\" align=\"right\"><div align=\"right\"><b>$langID</b></div></td>
     <td scope=\"col\" width=\"120\" rowspan=\"2\"><div align=\"left\"><b>$langSurname<br>$langName</b></div></td>";

if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .="
     <td scope=\"col\" width=\"100\" rowspan=\"2\"><div align=\"center\"><b>$langEmail</b></div></td>";
}

$tool_content .= "
     <td scope=\"col\" width=\"60\" rowspan=\"2\"><div align=\"center\"><b>$langAm</b></div></td>
     <td scope=\"col\" width=\"60\" rowspan=\"2\"><div align=\"center\"><b>$langGroup</b></div></td>
     <td scope=\"col\" width=\"60\" rowspan=\"2\"><div align=\"center\"><b>$langCourseRegistrationDate</b></div></td>";

// show admin tutor and unregister only to admins
if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "
     <td scope=\"col\" width=\"60\" colspan=\"2\"><div align=\"center\"><b>$langUserPermitions</b></div></td>
     <td scope=\"col\" width=\"50\" rowspan=\"2\"><div align=\"center\"><b>$langActions</b></div></td>";
}

$tool_content .= "
   </tr>";

if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "
  <tr>
     <td scope=\"col\" width=\"60\"><div align=\"center\"><b>$langTutor</b></div></td>
     <td scope=\"col\" width=\"60\"><div align=\"center\"><b>$langAdministrator</b></div></td>
  </tr>";
}

$tool_content .= "
   </thead>
   <tbody>";
$result = mysql_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.am, cours_user.statut,
		cours_user.tutor, cours_user.reg_date, user_group.team
		FROM `$mysqlMainDb`.cours_user, `$mysqlMainDb`.user
		LEFT JOIN `$currentCourseID`.user_group
		ON user.user_id=user_group.user
		WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
		ORDER BY nom, prenom LIMIT $startList, $endList", $db);

while ($myrow = mysql_fetch_array($result)) {
	// bi colored table
	if ($i%2==0)
		$tool_content .= "
   <tr>";
	elseif ($i%2==1)
		$tool_content .= "
   <tr class=\"odd\">";

// show public list of users
	$tool_content .= "
     <td valign=\"top\" align=\"right\">$i.</td>
     <td valign=\"top\">$myrow[nom]<br>$myrow[prenom]</td>";

if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .= "
     <td valign=\"top\" align=\"center\"><a href=\"mailto:".$myrow["email"]."\">".$myrow["email"]."</a></td>";
}
	$tool_content .= "
     <td valign=\"top\" align=\"center\">$myrow[am]</td>
     <td valign=top align=\"center\">";

	// NULL AND NOT '0' BECAUSE TEAM CAN BE INEXISTENT
	if($myrow["team"]==NULL) {
		$tool_content .= "$langUserNoneMasc";
	} else {
		$tool_content .= "$myrow[team]";
	}
	$tool_content .= "</td>";
	$tool_content .= "
     <td align='center'>";
	if ($myrow['reg_date'] == '0000-00-00')
		$tool_content .= $langUnknownDate;
	else
		$tool_content .= "".greek_format($myrow['reg_date'])."";

	$tool_content .= "</td>";

// ************** tutor, admin and unsubscribe (admin only) ******************************
if(isset($status) && ($status["$currentCourseID"]=='1' OR $status["$currentCourseID"]=='2')) {
// tutor right
	if ($myrow["tutor"]=='0') {
		$tool_content .= "
     <td valign=\"top\" align='center' class=\"add_user\"><a href=\"$_SERVER[PHP_SELF]?giveTutor=yes&user_id=$myrow[user_id]\" title=\"$langGiveTutor\">$langAdd</a></td>";
		} else {
		$tool_content .= "
     <td class=\"highlight\" align='center'>$langTutor<br><a href=\"$_SERVER[PHP_SELF]?removeTutor=yes&user_id=$myrow[user_id]\" title=\"$langRemoveRight\">$langRemove</a></td>";
		}

		// admin right
		if ($myrow["user_id"]!=$_SESSION["uid"]) {
			if ($myrow["statut"]=='1') {
				$tool_content .= "
     <td class=\"highlight\" align='center'>$langAdministrator<br><a href=\"$_SERVER[PHP_SELF]?removeAdmin=yes&user_id=$myrow[user_id]\" title=\"$langRemoveRight\">$langRemove</a></td>";
			} else {
			$tool_content .= "
     <td valign=\"top\" align='center' class=\"add_user\"><a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\" title=\"$langGiveAdmin\">$langAdd</a></td>";
			}
		} else {
			if ($myrow["statut"]=='1') {
				$tool_content .= "
     <td valign=\"top\" class=\"highlight\" align='center' title=\"$langAdmR\"><b>$langAdministrator</b></td>";
			} else {
				$tool_content .= "
     <td valign=\"top\" align='center'><a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\">$langGiveAdmin</a></td>";
			}
		}
		$tool_content .= "
     <td valign=\"top\" align='center'>";
		$alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
		$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?unregister=yes&user_id=$myrow[user_id]\" onClick=\"return confirmation('".addslashes($alert_uname)."');\"><img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></a>";
	}	// admin only
	$tool_content .= "</td>
   </tr>";
	$i++;
} 	// end of while

$tool_content .= "
   </tbody>
   </table>";

// navigation buttons
// Do not show navigation buttons if less than 50 users
if($countUser>=50) {
	$tool_content .= "
   <table width=\"99%\" >
   <tr>
     <td valign=\"bottom\" align=\"left\" width=\"20%\">
       <form method=\"post\" action=\"$_SERVER[PHP_SELF]?numbList=begin\">
         <input type=\"submit\" value=\"$langBegin<<\" name=\"numbering\">
       </form>
     </td>
     <td valign=\"bottom\" align=\"middle\" width=\"20%\">";

	if ($startList!=0) {
		$tool_content .= "
       <form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
         <input type=\"submit\" value=\"$langPreced50<\" name=\"numbering\">
       </form>";
	}
	$tool_content .= "
     </td>
     <td valign=\"bottom\" align=\"middle\" width=\"20%\">
       <form method=post action=\"".$_SERVER['PHP_SELF']."?startList=$startList&numbList=all\">
         <input type=submit value=\"".$langAll."\" name=\"numbering\">
       </form>
     </td>
     <td valign=\"bottom\" align=\"middle\" width=\"20%\">";
	if (!((( $countUser-$startList ) <= 50) OR ($endList == 2000))) {
		$tool_content .= "
       <form method=\"post\" action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
         <input type=\"submit\" value=\"$langFollow50>\" name=\"numbering\">
       </form>";
	}
	$tool_content .= "
     </td>
     <td valign=\"bottom\" align=\"right\" width=\"20%\">
       <form method=\"post\" action=\"$_SERVER[PHP_SELF]?numbList=final\">
         <input type=\"submit\" value=\"$langEnd>>\" name=\"numbering\">
       </form>
     </td>
   </tr>
   </table>";
}	// navigation buttons
draw($tool_content, 2, 'user', $head_content);
?>
