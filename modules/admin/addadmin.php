<?php
$langFiles = array('admin','addadmin');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = $langNomPageAddHtPass;

// Initialise $tool_content
$tool_content = "";
// Main body








if (isset($encodeLogin)) {
	$res = mysql_query("SELECT user_id FROM user WHERE username='$encodeLogin'");
	if (mysql_num_rows($res) == 1) {
		$row = mysql_fetch_row($res);
		if (mysql_query("INSERT INTO admin VALUES('$row[0]')")) 
			$tool_content .= "<p>$langUser $encodeLogin $langWith  id='$row[0]' $langDone</p>";
		 else 
			$tool_content .= "<p>$langError</p>";
	} else {
		$tool_content .= "<p>$langUser $encodeLogin $langNotFound.</p>";
		$tool_content .= printform($langLogin);
	}
} else {
	$tool_content .= printform($langLogin);
}

$tool_content .= "<center><p><a href='index.php'>$langBack</a></p></center>";

draw($tool_content,3,'admin');

// -------------- functions -------------------------

function printform ($message) { 
	global $langAdd;

	$ret = "";
	$ret .= "<form method='post' name='makeadmin' action='$_SERVER[PHP_SELF]'>";
	$ret .= "<table width=\"99%\"><caption>Εισαγωγή στοιχείων χρήστη</caption><tbody>
	<tr><td width=\"3%\" nowrap>".$message."</td><td><input type='text' name='encodeLogin' size='20' maxlength='30'></td></tr>
	<tr><td colspan=\"2\"><input type='submit' name='crypt' value='$langAdd'></td></tr></tbody></table></form>";
	
	return $ret;
}

?>