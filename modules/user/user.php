<?php  
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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

/**
 * User Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This module is responsible for the user administration
 *
 */
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
$require_login = true;
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

$sqlUserOfCourse = "SELECT user.user_id
		FROM cours_user, user
		WHERE code_cours='$currentCourseID'
		AND cours_user.user_id = user.user_id";
$result_numb = db_query($sqlUserOfCourse, $mysqlMainDb);
$countUser = mysql_num_rows($result_numb);

$nameTools = $langUsers." ($langUserNumber : $countUser)";
$tool_content = "";//initialise $tool_content


// IF PROF ONLY 
//   show  help link and  link to Add new  user and  managment page  of groups
if ($is_adminOfCourse) {
$tool_content .= <<<cData
	<p><a href="../group/group.php">$langGroupUserManagement</a>&nbsp;-&nbsp;$langDumpUser <a href="dumpuser.php">$langExcel</a> <a href="dumpuser2.php">$langCsv</a></p>
	
	<p>$langAdd&nbsp; <a href="adduser.php">$langOneUser</a>, <a href="muladduser.php">$langManyUsers</a>, <a href="guestuser.php">$langGUser</a></p>
	
	
cData;
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
		// SECURITY : CANNOT REMOVE MYSELF !
	$result = db_query("DELETE FROM cours_user WHERE user_id!= $uid
			AND user_id='".mysql_real_escape_string($_GET['user_id'])."' "
			."AND code_cours='$currentCourseID'", $mysqlMainDb);
	$delGroupUser=db_query("DELETE FROM user_group WHERE user='".mysql_real_escape_string($_GET['user_id'])."'", $currentCourseID);
}

// display list of users

// navigation buttons
$endList=50;

if(isset ($numbering) && $numbering) {
	if($numbList=="more") {
		$startList=$startList+50;
	}
	elseif($numbList=="less") {
		$startList=abs($startList-50);
	}
	elseif($numbList=="all") {
		$startList=0;
		$endList=2000;
	}
	elseif($numbList=="begin") {
		$startList=0;
	}
	elseif($numbList=="final") {
		$startList=((int)($countUser / 50)*50);
	}
}	// if numbering

// default status for the list: users 0 to 50
else {
	$startList=0;
}

// Numerating the items in the list to show: starts at 1 and not 0
$i=$startList+1;

// Do not show navigation buttons if less than 50 users
if ($countUser>=50) {
	$tool_content .= "<table width=99%>
		<tr>
		<td valign=bottom align=left width=20%>
		<form method=post action=\"$_SERVER[PHP_SELF]?numbList=begin\">
			<input type=submit value=\"$langBegin<<\" name=\"numbering\">
		</form>
		</td>
		<td valign=bottom align=middle width=20%>";

	// if beginning of list or complete listing, do not show "previous" button
	if ($startList!=0) {
		$tool_content .= "<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
		<input type=submit value=\"$langPreced50<\" name=\"numbering\">
		</form>";	
	}
	$tool_content .= "</td><td valign=bottom align=middle width=20%>
		<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=all\">
			<input type=submit value=\"$langAll\" name=numbering>
		</form>
		</td>
		<td valign=bottom align=middle width=20%>";

	// if end of list  or complete listing, do not show "next" button
	if (!((($countUser-$startList)<=50) OR ($endList==2000))) {
		$tool_content .= "<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
			<input type=submit value=\"$langFollow50>\" name=numbering>
		</form>";
	}
	$tool_content .= "</td><td valign=bottom align=right width=20%>
		<form method=post action=\"$_SERVER[PHP_SELF]?numbList=final\">
		<input type=submit value=\"$langEnd>>\" name=numbering>
		</form>
		</td>
		</tr>
	</table>";

}	// Show navigation buttons

	
$tool_content .= "<table width=99%>
<thead><tr><th></th>
					<th scope=\"col\">$langSurname<br>$langName</th>";

if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .="<th scope=\"col\">$langEmail</th>";
	}

$tool_content .= "<th scope=\"col\">$langAm</th>
					<th scope=\"col\">$langGroup</th>";

// show admin tutor and unregister only to admins
if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "	<th scope=\"col\">$langTutor</th>
		<th scope=\"col\">$langAdmR</th>
			
		<th scope=\"col\">$langUnreg</th>";
			
}	// ADMIN ONLY

$tool_content .= "</tr>
			</thead><tbody>";

//select name,sunrname, email, status and group of users
$result = mysql_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.am, cours_user.statut, 
		cours_user.tutor, user_group.team
		FROM `$mysqlMainDb`.cours_user, `$mysqlMainDb`.user 
		LEFT JOIN `$currentCourseID`.user_group 
		ON user.user_id=user_group.user
		WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
		ORDER BY nom, prenom LIMIT $startList, $endList", $db);


// ORDER BY cours_user.statut, tutor DESC, nom, prenom
while ($myrow = mysql_fetch_array($result)) {
	// bi colored table
	if ($i%2==0) {
		$tool_content .= "<tr >";
	}     	
	elseif ($i%2==1) {
		$tool_content .= "<tr class=\"odd\">";
	}	

// show public list of users
	$tool_content .= "<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">$i</font>
		</td>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
		$myrow[nom]
		<br>
		$myrow[prenom]
		</font>
		</td>";

if (isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .= "<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
		<a href=\"mailto:".$myrow["email"]."\">".$myrow["email"]."</a>
		</font>
		</td>";
}
	$tool_content .= "<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
		$myrow[am]
		</font>
		</td>
		<td valign=top>
		";

	// NULL AND NOT '0' BECAUSE TEAM CAN BE INEXISTENT
	if($myrow["team"]==NULL) {
		$tool_content .= "$langUserNoneMasc";
	} else {
		$tool_content .= "$myrow[team]";
	}
	$tool_content .= "</td>";

################## TUTOR, ADMIN AND UNSUBSCRIBE (ADMIN ONLY) ######################
if(isset($status) && ($status["$currentCourseID"]=='1' OR $status["$currentCourseID"]=='2')) {
// tutor right
		if ($myrow["tutor"]=='0') {
			$tool_content .= "<td valign=\"top\" align='center'>
			
			<a href=\"$_SERVER[PHP_SELF]?giveTutor=yes&user_id=$myrow[user_id]\">$langGiveTutor</a>
			</td>";
		} else {
			$tool_content .=  "<td class=\"highlight\" align='center'>
			$langTutor
			<br>
			<a href=\"$_SERVER[PHP_SELF]?removeTutor=yes&user_id=$myrow[user_id]\">$langRemoveRight</a>
			</td>";
		}
		
		// admin right
		if ($myrow["user_id"]!=$_SESSION["uid"]) {
			if ($myrow["statut"]=='1') {
				$tool_content .= "<td class=\"highlight\" align='center'>
					$langAdmR
					<br><a href=\"$_SERVER[PHP_SELF]?removeAdmin=yes&user_id=$myrow[user_id]\">$langRemoveRight</a>
				</td>";
			} else {
				$tool_content .= "<td valign=\"top\" align='center'>
					<font face=\"arial, helvetica\" size=\"2\">
					<a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\">$langGiveAdmin</a>
					</td>";
				}		
		} else {
			if ($myrow["statut"]=='1') {
				$tool_content .= "<td valign=\"top\" bgcolor=\"#CCFF99\" align='center'>
					<font face=\"arial, helvetica\" size=\"2\">
						$langAdmR
					</font>
				</td>";
			} else {
				$tool_content .= "<td valign=\"top\" align='center'>
					<font face=\"arial, helvetica\" size=\"2\">
					<a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\">$langGiveAdmin</a>
					</font>
				</td>";
			}
		}	
		$tool_content .= "<td valign=\"top\" align='center'>";
		if ($myrow["user_id"]!=$_SESSION["uid"])
		$alert_uname = $myrow['nom'] . " " . $myrow['prenom'];
			$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?unregister=yes&user_id=$myrow[user_id]\" onClick=\"return confirmation('".addslashes($alert_uname)."');\"><img src='../../template/classic/img/delete.gif' border='0' title='$langUnreg'></a>";
					
	}	// admin only
	
	$tool_content .= "</td></tr>";

	$i++;

} 	// end of while

$tool_content .= "</tbody></table>";

// navigation buttons
// Do not show navigation buttons if less than 50 users

if($countUser>=50) {
	$tool_content .= "<table width=\"99%\">
		<tr>
		<td valign=\"bottom\" align=\"left\" width=\"20%\">
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]?numbList=begin\">
			<input type=\"submit\" value=\"$langBegin<<\" name=\"numbering\">
			</form>
		</td><td valign=\"bottom\" align=\"middle\" width=\"20%\">";
	
	if ($startList!=0) {
		$tool_content .= "<form method=post action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=less\">
			<input type=\"submit\" value=\"$langPreced50<\" name=\"numbering\">
		</form>";	
	}
	$tool_content .= "</td><td valign=\"bottom\" align=\"middle\" width=\"20%\">
		<form method=post action=\"".$_SERVER['PHP_SELF']."?startList=$startList&numbList=all\">
		<input type=submit value=\"".$langAll."\" name=\"numbering\">
		</form>
		</td>
	<td valign=\"bottom\" align=\"middle\" width=\"20%\">";
	if (!((( $countUser-$startList ) <= 50) OR ($endList == 2000))) {
		$tool_content .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]?startList=$startList&numbList=more\">
		<input type=\"submit\" value=\"$langFollow50>\" name=\"numbering\">
		</form>";
	}
	$tool_content .= "</td><td valign=\"bottom\" align=\"right\" width=\"20%\">
		<form method=\"post\" action=\"$_SERVER[PHP_SELF]?numbList=final\">
		<input type=\"submit\" value=\"$langEnd>>\" name=\"numbering\">
		</form>
		</td>
		</tr>
		</table>";

}	// navigation buttons
draw($tool_content, 2, 'user', $head_content);
?>
