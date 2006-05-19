<?
$langFiles = array('course_info', 'create_course', 'opencours','admin');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Επεξεργασία Μαθήματοςς";

// Initialise $tool_content
$tool_content = "";
// Main body

if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}


if (isset($submit))  {
	$dq = $dq * 1000000;
        $vq = $vq * 1000000;
        $gq = $gq * 1000000;
        $drq = $drq * 1000000;
	$sql = mysql_query("UPDATE cours SET visible='$formvisible' WHERE code='$c'");
	if (mysql_affected_rows() > 0) {
		$tool_content .= "<p>Ο τύπος πρόσβασης του μαθήματος άλλαγε με επιτυχία!</p>";
	} else {
		$tool_content .= "<p>Δεν πραγματοποιήθηκε καμία αλλαγή!</p>";
	}

} else {
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='$c'"));
	$visible = $row['visible'];
	$visibleChecked[$visible]="checked";
	
	$tool_content .= "<form action=".$_SERVER[PHP_SELF]."?c=".$c."".$searchurl." method=\"post\">";
	$tool_content .= "<table width=\"99%\"><caption>Αλλαγή τύπου πρόσβασης μαθήματος</caption><tbody>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><i>$langConfTip</i></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
    <td>".$langPublic."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
    <td>".$langPrivOpen."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
    <td>".$langPrivate."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='submit' value='$langModify'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>\n";
}

if (isset($c)) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".$c."".$searchurl."\">Επιστροφή</a></p></center>";
} else {
	$tool_content .= "<center><p><a href=\"index.php\">".$langBackAdmin."</a></p></center>";
}

draw($tool_content,3,'admin');

?>
