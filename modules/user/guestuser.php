<?

$langFiles = array('registration','guest');
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
include('../../include/init.php');

$local_style = "input { font-size: 10px; }";

$nameTools = $langAddGuest;
$navigation[] = array ("url"=>"user.php", "name"=> $langUsers);
begin_page();

// IF PROF ONLY
if($is_adminOfCourse)
{

// Create guest account
function createguest($c,$p) {

	global $langGuestUserName,$langGuestSurname,$langGuestName, $mysqlMainDb;

	// guest account user name 	
	$guestusername=$langGuestUserName.$c;
	// Guest account created...
	mysql_select_db($mysqlMainDb);

	$q=mysql_query("SELECT user_id FROM user WHERE username='$guestusername'");
	if (mysql_num_rows($q) > 0) {
		$s = mysql_fetch_array($q);

		mysql_query("UPDATE user SET password='$p' WHERE user_id='$s[0]'")
				or die ($langGuestFail);

		mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,role) 
			VALUES ('$c','$s[0]','10','Επισκέπτης')")
				or die ($langGuestFail);

	} else {
		mysql_query("INSERT INTO user (nom,prenom,username,password,statut) 
			VALUES ('$langGuestName','$langGuestSurname','$guestusername','$p','10')")
				or die ($langGuestFail);

		mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,role) 
			VALUES ('$c','".mysql_insert_id()."','10','Επισκέπτης')")
				or die ($langGuestFail);
	}
}


// Checking if Guest account exists....
function guestid($c) {	
	global $mysqlMainDb;

	mysql_select_db($mysqlMainDb);
	$q1=mysql_query("SELECT user_id  from cours_user WHERE statut='10' AND code_cours='$c'");
	if (mysql_num_rows($q1) == 0) {
		return FALSE;
	} else {
		$s=mysql_fetch_array($q1);
		return $s[0];
	}
}

if (isset($createguest) and (!guestid($currentCourseID))) {

	createguest($currentCourseID,$guestpassword);
	echo "<tr><td>$langGuestSuccess</td></tr>";
} elseif (isset($changepass)) {

	$g=guestid($currentCourseID);
	$uguest=mysql_query("UPDATE user SET password='$guestpassword' WHERE user_id='$g'")
		or die($langGuestFail);
	echo "<tr><td>$langGuestChange</td></tr>";
} else {
	$id = guestid($currentCourseID);
	if ($id) {
		echo "<tr><td>$langGuestExist</td></tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr><td>";
		$q1=mysql_query("SELECT nom,prenom,username,password FROM user where user_id='$id'");
		$s=mysql_fetch_array($q1);
		echo "<table>";
		echo "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">";
		echo "<tr><td>$langName:</td><td>$s[nom]</td></tr>";
		echo "<tr><td>$langSurname:</td><td>$s[prenom]</td></tr>";
		echo "<tr><td>$langUsername:&nbsp;&nbsp;&nbsp;&nbsp;</td><td>$s[username]</td></tr>";
		echo "<tr><td>$langPass:</td><td><input type=\"text\" name=\"guestpassword\" value=\"".
			htmlspecialchars($s['password'])."\"></td></tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr><td><input type=\"submit\" name=\"changepass\" value=\"$langChangeGuestPasswd\"></td></tr>";
		echo "</form>";
		echo "</table>";
		echo "</td></tr>";
		echo "</font>";
	} else {

	?>
	<tr><td>
		<? echo $langAskGuest ?><br><br>
		<tr><td>
		<table>	
		<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
		<tr><td><? echo $langName ?>:</td><td><? echo $langGuestName?></td></tr>
		<tr><td><? echo $langSurname ?>:</td><td><? echo $langGuestSurname?></td></tr>
		<tr><td><? echo $langUsername ?>:&nbsp;&nbsp;&nbsp;</td><td><? echo $langGuestUserName.$currentCourseID?></td></tr>
		<tr><td><? echo $langPass ?>:</td><td><input type="text" name="guestpassword"></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><input type="submit" name="createguest" value="<? echo $langGuestAdd ?>"></td></tr>
		</form>
		</table>
		</td></tr>
	    </font>
	</td></tr>
	<tr><td>

<? 	
	}
}
?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><a href="user.php"><? echo $langBackUser ?></a>
</td></tr>
</table>
</body>
</html>
<?
	 }

?>
