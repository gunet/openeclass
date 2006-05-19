<?
$langFiles = array('course_info', 'create_course', 'opencours','admin');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Διαγραφή μαθήματος";

// Initialise $tool_content
$tool_content = "";
// Main body

if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}

if (isset($delete) && isset($c))  {
	mysql_query("DROP DATABASE `$c`");
	mysql_query("DELETE FROM `$mysqlMainDb`.cours WHERE code='$c'");
	mysql_query("DELETE FROM `$mysqlMainDb`.cours_user WHERE code_cours='$c'");
	mysql_query("DELETE FROM `$mysqlMainDb`.cours_faculte WHERE code='$c'");
	mysql_query("DELETE FROM `$mysqlMainDb`.annonces WHERE code_cours='$c'");
	@mkdir("../../courses/garbage");
	rename("../../courses/$c", "../../courses/garbage/$c");
	$tool_content .= "<p>Το μάθημα διαγράφηκε με επιτυχία!</p>";

} else {
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='$c'"));
	
	$tool_content .= "<table width=\"99%\"><caption>Επιβεβαίωση Διαγραφής Μαθήματος</caption><tbody>";
	$tool_content .= "  <tr>
    <td><br>Θέλετε σίγουρα να διαγράψετε το μάθημα με κωδικό <em>$c</em>;<br><br></td>
  </tr>";
	$tool_content .= "  <tr>
    <td><ul><li><a href=\"".$_SERVER['PHP_SELF']."?c=".$c."&delete=yes".$searchurl."\"><b>Ναι</b></a><br>&nbsp;</li>
              <li><a href=\"editcours.php?c=".$c."".$searchurl."\"><b>Όχι</b></a></li></ul></td>
  </tr>";
	$tool_content .= "</tbody></table><br>";
}

if (isset($c) && !isset($delete)) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".$c."".$searchurl."\">Επιστροφή</a></p></center>";
} else {
	if (isset($search) && ($search=="yes")) {
		$tool_content .= "<center><p><a href=\"listcours.php?search=yes\">Επιστροφή στα αποτελέσματα αναζήτησης</a></p></center>";
	}
	$tool_content .= "<center><p><a href=\"listcours.php\">Επιστροφή</a></p></center>";
}

draw($tool_content,3, 'admin');

?>
