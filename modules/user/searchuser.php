<?
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

/* This script allows a course admin to search users to the course. */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

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

$nameTools = $langSearchUser;
$navigation[] = array ("url"=>"user.php", "name"=> $langAdminUsers);

$tool_content="";

// IF PROF ONLY
if($is_adminOfCourse) {
	// give admin status
	if(isset($giveAdmin) && $giveAdmin && $is_adminOfCourse) {
		$result = db_query("UPDATE cours_user SET statut = 1
		WHERE user_id='".mysql_real_escape_string($_GET['user_id'])."' AND cours_id = $cours_id", $mysqlMainDb);
	}
	// give tutor status
	elseif(isset($giveTutor) && $giveTutor) {
		$result = db_query("UPDATE cours_user SET tutor = 1
		WHERE user_id='".mysql_real_escape_string($_GET['user_id'])."' AND cours_id = $cours_id",$mysqlMainDb);
		$result2=db_query("DELETE FROM user_group 
		WHERE user='".mysql_real_escape_string($_GET['user_id'])."'", $currentCourseID);
	}
        // remove admin status
        elseif(isset($removeAdmin) && $removeAdmin) {
                $result = db_query("UPDATE cours_user SET statut = 5
                        WHERE user_id != $uid AND user_id='".mysql_real_escape_string($_GET['user_id'])."'
                              AND cours_id = $cours_id", $mysqlMainDb);
        }
        // remove tutor status
        elseif(isset($removeTutor) && $removeTutor) {
                $result = db_query("UPDATE cours_user SET tutor = 0
                        WHERE user_id = '".mysql_real_escape_string($_GET['user_id'])."'
                              AND cours_id = $cours_id", $mysqlMainDb);
        }
        // unregister user from courses
        elseif(isset($unregister) && $unregister) {
                // Security: cannot remove myself
                $result = db_query("DELETE FROM cours_user WHERE user_id!= $uid
                        AND user_id = '".mysql_real_escape_string($_GET['user_id'])."'
                        AND cours_id = $cours_id", $mysqlMainDb);
                // Except: remove myself if there is another tutor
                if ($_GET['user_id'] == $uid) {
                        $result = db_query("SELECT user_id FROM cours_user
                                        WHERE cours_id = $cours_id
                                        AND user_id <> $uid
                                        AND statut = 1 LIMIT 1", $mysqlMainDb);
                        if (mysql_num_rows($result) > 0) {
                                db_query("DELETE FROM cours_user
                                        WHERE cours_id = $cours_id
                                        AND user_id = $uid");
                        }
                }
		$delGroupUser=db_query("DELETE FROM user_group 
		WHERE user='".mysql_real_escape_string($_GET['user_id'])."'", $currentCourseID);
	}
	
	if(!isset($search_nom)) $search_nom = "";
	if(!isset($search_prenom)) $search_prenom = "";
	if(!isset($search_uname)) $search_uname = ""; 
	
	$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]'>";
	$tool_content .= "<table width='99%' class='FormData'><tbody>
	<tr>
	<th width='220'>&nbsp;</th>
	<td><b>$langUserData</b></td>
	</tr>
	<tr>
	<th class='left'>$langSurname</th>
	<td><input type='text' name='search_nom' value='$search_nom' class='FormData_InputText'></td>
	</tr>
	<tr>
	<th class='left'>$langName</th>
	<td><input type='text' name='search_prenom' value='$search_prenom' class='FormData_InputText'></td>
	</tr>
	<tr>
	<th class='left'>$langUsername</th>
	<td><input type='text' name='search_uname' value='$search_uname' class='FormData_InputText'></td>
	</tr>
	<tr>
	<th class='left'>&nbsp;</th>
	<td><input type='submit' value='$langSearch'></td>
	</tr>
	</tbody>
	</table><br />
	</form>";

	mysql_select_db($mysqlMainDb);
	$search=array();
	if(!empty($search_nom)) {
		$search[] = "user.nom LIKE '".mysql_escape_string($search_nom)."%'";
		$s = "search_nom=$search_nom";
	}
	if(!empty($search_prenom)) {
		$search[] = "user.prenom LIKE '".mysql_escape_string($search_prenom)."%'";
		$s = "search_prenom=$search_prenom";
	}
	if(!empty($search_uname)) {
		$search[] = "user.username LIKE '".mysql_escape_string($search_uname)."%'";
		$s = "search_uname=$search_uname";
	}

	$query = join(' AND ', $search);
	if (!empty($query)) {
		$result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.am, cours_user.statut,
			cours_user.tutor, cours_user.reg_date, user_group.team
			FROM `$mysqlMainDb`.cours_user, `$mysqlMainDb`.user
			LEFT JOIN `$currentCourseID`.user_group
			ON user.user_id=user_group.user
			WHERE `user`.`user_id`=`cours_user`.`user_id` 
			AND `cours_user`.`cours_id` = $cours_id AND $query
			ORDER BY nom, prenom");
		if (mysql_num_rows($result) == 0) {
			$tool_content .= "<p class='caution_small'>$langNoUsersFound2</p>\n";
		} else {
			$tool_content .= "<table width=99% class=\"FormData\" style=\"border: 1px solid #CAC3B5;\">
			<thead>
			<tr class='odd'>
			<td rowspan='2' class='UsersHead'>$langID</td>
			<td rowspan='2' class='UsersHead'><div align=\"left\">$langSurname<br>$langName</div></td>";
			$tool_content .= "<td rowspan='2' class='UsersHead'>$langEmail</td>";
			$tool_content .= "<td rowspan='2' class='UsersHead'>$langAm</td>
			<td rowspan='2' class='UsersHead'>$langGroup</td>
			<td rowspan='2' class='UsersHead'>$langCourseRegistrationDate</td>";
			$tool_content .= "<td colspan='2' class='UsersHead'>$langUserPermitions</td>
			<td rowspan='2' class='UsersHead'>$langActions</td></tr>";
			$tool_content .= "<tr><td class='UsersHead'>$langTutor</td>
			<td class='UsersHead'>$langAdministrator</td>
			</tr>";
			$tool_content .= "</thead>";
			$i = 1;
			while ($myrow = mysql_fetch_array($result)) {
				if ($i%2 == 0) {
					$tool_content .= "<tr>";
				} else {
					$tool_content .= "<tr class='odd'>";
				}
			// display users	
			$tool_content .= "<td valign=\"top\" align=\"right\">$i.</td>
			<td valign=\"top\">$myrow[nom]<br>$myrow[prenom]</td>";
			$tool_content .= "<td valign=\"top\" align=\"center\">
			<a href=\"mailto:".$myrow["email"]."\">".$myrow["email"]."</a></td>";
			$tool_content .= "<td valign=\"top\" align=\"center\">$myrow[am]</td>
			<td valign='top' align=\"center\">";
			if($myrow["team"] == NULL) {
				$tool_content .= "$langUserNoneMasc";
			} else {
				$tool_content .= gid_to_name($myrow['team']);
			}
			$tool_content .= "</td>";
			$tool_content .= "<td align='center'>";
			if ($myrow['reg_date'] == '0000-00-00') {
				$tool_content .= $langUnknownDate;
			} else {
				$tool_content .= "".nice_format($myrow['reg_date'])."";
			}
			$tool_content .= "</td>";
			if ($myrow["tutor"]=='0') {
				$tool_content .= "<td valign='top' align='center' class='add_user'>
				<a href='$_SERVER[PHP_SELF]?giveTutor=yes&user_id=$myrow[user_id]&$s' title='$langGiveTutor'>$langAdd</a></td>";
			} else {
				$tool_content .= "<td class=\"highlight\" align='center'>$langTutor<br>
				<a href='$_SERVER[PHP_SELF]?removeTutor=yes&user_id=$myrow[user_id]&$s' title='$langRemoveRight'>$langRemove</a></td>";
			}
			// admin right
			if ($myrow["user_id"]!=$_SESSION["uid"]) {
				if ($myrow["statut"]=='1') {
					$tool_content .= "<td class='highlight' align='center'>$langAdministrator<br>
					<a href='$_SERVER[PHP_SELF]?removeAdmin=yes&user_id=$myrow[user_id]&$s' title='$langRemoveRight'>$langRemove</a></td>";
				} else {
					$tool_content .= "<td valign='top' align='center' class='add_user'>
					<a href='$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]&$s' title='$langGiveAdmin'>$langAdd</a></td>";
				}
			} else {
				if ($myrow["statut"]=='1') {
					$tool_content .= "<td valign=\"top\" class='highlight' align='center' title='$langAdmR'><b>$langAdministrator</b></td>";
				} else {
					$tool_content .= "<td valign=\"top\" align='center'>
					<a href='$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]&$s'>$langGiveAdmin</a></td>";
				}
			}
				$tool_content .= "<td valign=\"top\" align='center'>";
				$alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
				$tool_content .= "<a href='$_SERVER[PHP_SELF]?unregister=yes&user_id=$myrow[user_id]&$s' onClick=\"return confirmation('".addslashes($alert_uname)."');\">
				<img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></a>";
				$tool_content .= "</td></tr>";
				$i++;	
			}
			$tool_content .= "</tbody></table>";
		}
	} 
}
draw($tool_content, 2, 'user', $head_content);
?>
