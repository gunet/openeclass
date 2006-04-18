<?php  
//Μετατροπή του εργαλείου για να χρησιμοποιεί το baseTheme
$require_current_course = TRUE;
$langFiles = 'registration';
$require_help = TRUE;
$helpTopic = 'User';
//include('../../include/init.php');
include '../../include/baseTheme.php';

$sqlUserOfCourse = "SELECT user.user_id
		FROM cours_user, user
		WHERE code_cours='$currentCourseID'
		AND cours_user.user_id = user.user_id";
$result_numb = db_query($sqlUserOfCourse, $mysqlMainDb);
$countUser = mysql_num_rows($result_numb);

$nameTools = $langUsers." ($langUserNumber : $countUser)";
$tool_content = "";//initialise $tool_content
//begin_page();

// IF PROF ONLY 
//   show  help link and  link to Add new  user and  managment page  of groups
if ($is_adminOfCourse) {
$tool_content .= <<<cData
	<table>
	<tr>
	<td>
	<font face="arial, helvetica" size="2">
	<a href="../group/group.php">$langGroupUserManagement</a>&nbsp;-&nbsp;
	$langDumpUser
	<a href="dumpuser.php">$langExcel</a>
	<a href="dumpuser2.php">$langCsv</a>
	</font>	
	</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
			<font face="arial, helvetica" size="2">
				$langAdd&nbsp;
				<a href="adduser.php">$langOneUser</a>,
				<a href="muladduser.php">$langManyUsers</a>,
				<a href="guestuser.php">$langGUser</a>
			</font>	
		<br>
		<br>
		</td>
	</tr>
	</table>
cData;
}	// if prof

$tool_content .= "<tr><td>";

#################### ADMIN SQL FUNCTIONS ########################

// GIVE ADMIN STATUS TO USER FOR THIS COURSE

if(isset($giveAdmin) && $giveAdmin && $is_adminOfCourse) {
	$result = db_query("UPDATE cours_user SET statut='1'
			WHERE user_id=$user_id AND code_cours='$currentCourseID'",$mysqlMainDb);
}
// GIVE TUTOR STATUS TO USER FOR THIS COURSE

elseif(isset($giveTutor) && $giveTutor) {
	$result = db_query("UPDATE cours_user SET tutor='1'
			WHERE user_id=$user_id AND code_cours='$currentCourseID'",$mysqlMainDb);

	// ... AND REMOVIG HIM FROM GROUPS IF HE IS ALREADY MEMBER AS STUDENT
	$result2=db_query("DELETE FROM user_group WHERE user='$user_id'", $currentCourseID);
}


// REMOVE ADMIN STATUS TO USER FOR THIS COURSE

elseif(isset($removeAdmin) && $removeAdmin) {
	$result = db_query("UPDATE cours_user SET statut='5'
			WHERE user_id!= $uid AND user_id=$user_id 
			AND code_cours='$currentCourseID'",$mysqlMainDb);
}

// REMOVE TUTOR STATUS TO USER FOR THIS COURSE

elseif(isset($removeTutor) && $removeTutor) {
	$result = db_query("UPDATE cours_user SET tutor='0'
			WHERE user_id=$user_id 
			AND code_cours='$currentCourseID'",$mysqlMainDb);
}

// UNREGISTER USER FROM COURSE (DOES NOT DELETE USER FROM CLAROLINE MAIN USER DB)

elseif(isset($unregister) && $unregister) {
		// SECURITY : CANNOT REMOVE MYSELF !
	$result = db_query("DELETE FROM cours_user WHERE user_id!= $uid
			AND user_id=$user_id 
			AND code_cours='$currentCourseID'", $mysqlMainDb);
	$delGroupUser=db_query("DELETE FROM user_group WHERE user=$user_id", $currentCourseID);
}

###################### SHOW LIST OF USERS #######################

// DEFINE SETTINGS FOR THE 5 NAVIGATION BUTTONS INTO THE USERS LIST: begin, less, all, more and end
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
	$tool_content .= "<table width=99% cellpadding=1 cellspacing=1 border=0>
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

############# SHOW FIELD NAMES FOR USERS LIST ##################
$tool_content .= "<table width=99% cellpadding=2 cellspacing=1 border=0>
	<tr bgcolor=silver>
	<td >
	</td>
	<td valign=\"top\">
	<font face=\"arial, helvetica\" size=\"2\">
	$langSurname
	<br>
	$langName
	</font>
	</td>";
if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2))  {
	$tool_content .=" <td valign=\"top\">
	<font face=\"arial, helvetica\" size=\"2\">
		$langEmail
	</font>
	</td>";
	}

$tool_content .= "<td valign=\"top\">
	<font face=\"arial, helvetica\" size=\"2\">
		$langAm
	</font>
	</td>
	<td valign=top>
	<font face=\"arial, helvetica\" size=2>
		$langGroup
	</font>
	</td>";

// SHOW ADMIN, TUTOR AND UNREGISTER ONLY TO ADMINS

if(isset($status) && ($status[$currentCourseID]==1 OR $status[$currentCourseID]==2)) {
	$tool_content .= "<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
		$langTutor</font>
		</td>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
			$langAdmR
		</font>
		</td>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
			$langUnreg
		</font>
		</td>";
}	// ADMIN ONLY

$tool_content .= "</tr>";

############## SELECT NAME, SURNAME, EMAIL, STATUS AND GROUP OF USERS ###########

$result = mysql_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.am, cours_user.statut, 
		cours_user.tutor, user_group.team
		FROM `$mysqlMainDb`.cours_user, `$mysqlMainDb`.user 
		LEFT JOIN `$currentCourseID`.user_group 
		ON user.user_id=user_group.user
		WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
		ORDER BY nom, prenom LIMIT $startList, $endList", $db);


// ORDER BY cours_user.statut, tutor DESC, nom, prenom
while ($myrow = mysql_fetch_array($result)) {
	// BI COLORED TABLE
	if ($i%2==0) {
		$tool_content .= "<tr bgcolor=\"".$color2."\">";
	}     	
	elseif ($i%2==1) {
		$tool_content .= "<tr bgcolor=\"".$color1."\">";
	}	

	// SHOW PUBLIC LIST OF USERS
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
		<font face=\"arial, helvetica\" size=2>";

	// NULL AND NOT '0' BECAUSE TEAM CAN BE INEXISTENT
	if($myrow["team"]==NULL) {
		$tool_content .= "$langUserNoneMasc";
	} else {
		$tool_content .= "$myrow[team]";
	}
	$tool_content .= "</font></td>";

################## TUTOR, ADMIN AND UNSUBSCRIBE (ADMIN ONLY) ######################
if(isset($status) && ($status["$currentCourseID"]=='1' OR $status["$currentCourseID"]=='2')) {
		// TUTOR RIGHT
		if ($myrow["tutor"]=='0') {
			$tool_content .= "<td valign=\"top\">
			<font face=\"arial, helvetica\" size=\"2\">
			<a href=\"$_SERVER[PHP_SELF]?giveTutor=yes&user_id=$myrow[user_id]\">$langGiveTutor</a></font>
			</td>";
		} else {
			$tool_content .=  "<td valign=\"top\" bgcolor=\"#CCFF99\">
			<font face=\"arial, helvetica\" size=\"2\">$langTutor
			<br>
			<a href=\"$_SERVER[PHP_SELF]?removeTutor=yes&user_id=$myrow[user_id]\">$langRemoveRight</a></font>
			</td>";
		}
		
		// ADMIN RIGHT
		if ($myrow["user_id"]!=$_SESSION["uid"]) {
			if ($myrow["statut"]=='1') {
				$tool_content .= "<td valign=\"top\" bgcolor=\"#CCFF99\">
					<font face=\"arial, helvetica\" size=\"2\">
					$langAdmR
					<br><a href=\"$_SERVER[PHP_SELF]?removeAdmin=yes&user_id=$myrow[user_id]\">$langRemoveRight</a>
				</td>";
			} else {
				$tool_content .= "<td valign=\"top\">
					<font face=\"arial, helvetica\" size=\"2\">
					<a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\">$langGiveAdmin</a>
					</td>";
				}		
		} else {
			if ($myrow["statut"]=='1') {
				$tool_content .= "<td valign=\"top\" bgcolor=\"#CCFF99\">
					<font face=\"arial, helvetica\" size=\"2\">
						$langAdmR
					</font>
				</td>";
			} else {
				$tool_content .= "<td valign=\"top\">
					<font face=\"arial, helvetica\" size=\"2\">
					<a href=\"$_SERVER[PHP_SELF]?giveAdmin=yes&user_id=$myrow[user_id]\">$langGiveAdmin</a>
					</font>
				</td>";
			}
		}	
		$tool_content .= "<td valign=\"top\"><font size=2 face='arial, helvetica'>";
		if ($myrow["user_id"]!=$_SESSION["uid"])
			$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?unregister=yes&user_id=$myrow[user_id]\">$langUnreg</a>";
		$tool_content .= "</font>";
					
	}	// ADMIN ONLY
	
	$tool_content .= "</td></tr>";

	$i++;

} 	// END WHILE AND END OF STUDENTS LIST SHOW

$tool_content .= "</table>";

############ BOTTOM NAVIGATION BUTTONS IF MORE THAN 50 USERS ###############

// Do not show navigation buttons if less than 50 users

if($countUser>=50) {
	$tool_content .= "<table width=\"99%\" cellpadding=\"1\" cellspacing=\"1\" border=\"0\">
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
		<form method=post action=\"".$_SERVER[PHP_SELF]."?startList=$startList&numbList=all\">
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
draw($tool_content,2);
?>
<!--</td></tr>
<tr><td colspan="2"></td></tr>
</table>
</body>
</html>-->
