<?
$require_login = TRUE;
$langFiles = 'unreguser';
include "../../include/init.php";
$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../auth/profile.php", "name"=> $langModifProfile);
	
$local_style = 'li { font-size: 10pt; }';

begin_page();

if (!isset($doit) or $doit != "yes") {
	echo "<table cellpadding=3 cellspacing=0 border=0 width=100%>";
        echo "<tr bgcolor=$color2><td align=center>";

	 // admin cannot be deleted
	if ($is_admin) {
		echo $langAdminNo;
        	echo "<p><a href='../profile/profile.php'>$langBack</a></p>";
		exit;
	} else {
   		$q = db_query ("SELECT code_cours FROM cours_user WHERE user_id = '$uid'") ;
   		if (mysql_num_rows($q) == 0) {
           		echo "<h3>$langConfirm</h3>";
	   		echo "<ul>";
	   		echo "<li>$langYes: ";
	   		echo "<a href='$_SERVER[PHP_SELF]?u=$uid&doit=yes'>$langDelete</a>";
	   		echo "</li>";
			echo "<br>";
	   		echo "<li>$langNo: <a href='../profile/profile.php'>$langBack</a>";
	  		echo "</li></ul>";
   		} else {
			echo "<h3>$langNotice</h3>";
	  		echo $langExplain;
			echo "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
        	}
	}  //endif is admin
} else {
	if (isset($uid)) {
     		echo "<table cellpadding=3 cellspacing=0 border=0 width=100%>";
                echo "<br><tr valign=top bgcolor=$color2>";
		echo "<td align=center>";
		echo "<font size=3 face=arial, helvetica>";
     		db_query("DELETE from user WHERE user_id = '$uid'");
		if (mysql_affected_rows() > 0) {
       			echo "<p>$langDelSuccess</p>";
			echo "<p>$langThanks</p>";
			unset($_SESSION['uid']);
		} else {
       			echo "<p>$langError</p>";
			echo "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
			exit;
		}
	 }
	echo "<br><a href='../../index.php?logout=yes'>$langLogout</a><br>";
}
end_page();
?>

