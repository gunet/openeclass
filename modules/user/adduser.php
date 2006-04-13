<?
/* This script allows a course admin to add users to the course. */

$langFiles = 'registration';
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
include('../../include/init.php');

$local_style = "input { font-size: 10px; }";

$nameTools = $langAddUser;
$navigation[] = array ("url"=>"user.php", "name"=> $langUsers);

begin_page();

// IF PROF ONLY
if($is_adminOfCourse) {

if (isset($add)) {
	echo "<tr><td>";
	mysql_select_db($mysqlMainDb);
	$result = db_query("INSERT INTO cours_user (user_id, code_cours, statut) ".
		"VALUES ('".mysql_escape_string($add)."', '$currentCourseID', ".
		"'5')");
	if ($result) {
		echo "$langTheU $langAdded";
	} else {
		echo $langAddError;
	}
	echo "</td></tr><tr><td><br><br><a href=\"adduser.php\">$langAddBack</a></td></tr>\n";
} else {

?>
	<tr><td><font size="2"><?= $langAskUser ?></td></font></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>
	<table>	
    	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
	<tr><td><font size="2"><?= $langSurname ?></td><td><input type="text" name="search_nom" value="<? echo @$search_nom ?>"></td></font></tr>
	<tr><td><font size="2"><?= $langName ?></td><td><input type="text" name="search_prenom" value="<? echo @$search_prenom ?>"></td></font></tr>
	<tr><td><font size="2"><?= $langUsername ?></td><td><input type="text" name="search_uname" value="<? echo @$search_uname ?>"></td></font></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><input type="submit" value="<?= $langSearch ?>"></td></tr>
	</form>
	</table>	
	</td></tr>
	<tr><td>
<?
	mysql_select_db($mysqlMainDb);
	$search=array();
	if(!empty($search_nom)) {
		$search[] = "u.nom LIKE '".mysql_escape_string($search_nom)."%'";
	}
	if(!empty($search_prenom)) {
		$search[] = "u.prenom LIKE '".mysql_escape_string($search_prenom)."%'";
	}
	if(!empty($search_uname)) {
		$search[] = "u.username LIKE '".mysql_escape_string($search_uname)."%'";
	}
	// added by jexi
	if (!empty($users_file)) {
		$tmpusers=trim($_FILES['users_file']['name']);
		?>
		<table width=100% cellpadding=2 cellspacing=1 border=0>
		<tr bgcolor=silver>
		<th><? echo $langUsers ?></th><th><? echo $langResult ?></th>
		<?
		$f=fopen($users_file,"r");
		while (!feof($f))	{
			$uname=trim(fgets($f,1024));
			if (!$uname) continue;
			if (!check_uname_line($uname)) {
				echo "<tr><td colspan=\"2\">$langFileNotAllowed</td></tr>\n";
				break;
			}
			$result=adduser($uname,$currentCourseID);
			echo "<tr><td align=center>$uname</td><td>";
			if ($result == -1) {
				echo $langUserNoExist;
			} elseif ($result == -2) {
				echo $langUserAlready;
			} else {
				echo $langTheU.$langAdded;
			}
			echo "</td></tr>\n";
		}
		echo "</table>\n";
		fclose($f);
	}

    // end
    
	$query = join(' AND ', $search);
	if (!empty($query)) {
			db_query("CREATE TEMPORARY TABLE lala AS
			SELECT user_id FROM cours_user WHERE code_cours='$currentCourseID'
			");
		$result = db_query("SELECT u.user_id, u.nom, u.prenom, u.username FROM
			user u LEFT JOIN lala c ON u.user_id = c.user_id WHERE
			c.user_id IS NULL AND $query
			");
		if (mysql_num_rows($result) == 0) {
			echo $langNoUsersFound."</td></tr>\n";
		} else {
?>
	<table width=100% cellpadding=2 cellspacing=1 border=0>
		<tr bgcolor=silver>
			<th></th>
			<th><?= $langName ?></th>
			<th><?= $langSurname ?></th>
			<th><?= $langUsername ?></th>
			<th></th>
		</tr>
<?
			$i = 1;
			while ($myrow = mysql_fetch_array($result)) {
				if ($i % 2 == 0) {
					echo "<tr bgcolor=\"$color2\">";
		        	} else {
					echo "<tr bgcolor=\"$color1\">";
				}
				echo "<td>$i</td>".
				     "<td>$myrow[prenom]</td>".
				     "<td>$myrow[nom]</td>".
				     "<td>$myrow[username]</td>".
				     "<td><a href=\"$_SERVER[PHP_SELF]?add=$myrow[user_id]\">".
				     "$langRegister</a></td></tr>\n";
				$i++;
			}
?>
	</table>
<?
        	}
		db_query("DROP TABLE lala");
	}
	echo "</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><a href=\"user.php\">$langBackUser</a>\n";
	}
}
?>
</td></tr>
</table>
</body>
</html>
<?

// function for adding users 

// returns -1 (error - user doesnt exist)
// returns -2 (error - user is already in the course)
// returns userid (yes  everything is ok )

function adduser($user,$course) {
	$result=db_query("SELECT user_id FROM user WHERE username='".mysql_escape_string($user)."'");
	if (!mysql_num_rows($result))
		return -1;
 
	$userid=mysql_fetch_array($result);
	$userid=$userid[0];

	$result = db_query("SELECT * from cours_user WHERE user_id='$userid' AND code_cours='$course'");
	if (mysql_num_rows($result) > 0)
		return -2;
	
	$result = db_query("INSERT INTO cours_user (user_id, code_cours, statut) VALUES ('$userid', '$course', '5')");
	return $userid;
}

// function for checking file

function check_uname_line($uname)
{
	if (preg_match("/[^a-zA-Z0-9.-_á-ùÁ-Ù]/", $uname)) {
		return FALSE;
	} else {
		return 	TRUE;
	}

}
?>
