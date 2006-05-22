<?
$require_login = TRUE;
$langFiles = 'unreguser';

$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../auth/profile.php", "name"=> $langModifProfile);

//$local_style = 'li { font-size: 10pt; }';
include '../../include/baseTheme.php';
//begin_page();
$tool_content = "";

if (!isset($doit) or $doit != "yes") {
	$tool_content .=  "<table width=99%><tbody>";
	$tool_content .=  "<tr><td class=\"caution\">";

	// admin cannot be deleted
	if ($is_admin) {
		$tool_content .=  $langAdminNo;
		$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p>";
		exit;
	} else {
		$q = db_query ("SELECT code_cours FROM cours_user WHERE user_id = '$uid'") ;
		if (mysql_num_rows($q) == 0) {
			$tool_content .=  "<h3>$langConfirm</h3>";
			$tool_content .=  "<ul>";
			$tool_content .=  "<li>$langYes: ";
			$tool_content .=  "<a href='$_SERVER[PHP_SELF]?u=$uid&doit=yes'>$langDelete</a>";
			$tool_content .=  "</li>";
			$tool_content .=  "<br>";
			$tool_content .=  "<li>$langNo: <a href='../profile/profile.php'>$langBack</a>";
			$tool_content .=  "</li></ul>";
			$tool_content .= "</td></tr></tbody></table>";
		} else {
			$tool_content .=  "<h3>$langNotice</h3>";
			$tool_content .=  "<p>$langExplain</p>";
			$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
			$tool_content .= "</td></tr></tbody></table>";
		}
	}  //endif is admin
} else {
	if (isset($uid)) {
		$tool_content .=  "<table width=99%><tbody>";
		$tool_content .=  "<tr>";
		$tool_content .=  "<td class=\"success\">";
		db_query("DELETE from user WHERE user_id = '$uid'");
		if (mysql_affected_rows() > 0) {
			$tool_content .=  "<p>$langDelSuccess</p>";
			$tool_content .=  "<p>$langThanks</p>";
			$tool_content .=  "<br><a href='../../index.php?logout=yes'>$langLogout</a><br>";
			unset($_SESSION['uid']);
		} else {
			$tool_content .=  "<p>$langError</p>";
			$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
//			exit;
		}
	}
	
	$tool_content .= "</td></tr></tbody></table>";
}
draw($tool_content, 1);
?>

