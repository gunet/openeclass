<?
$langFiles = 'admin';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Επεξεργασία Μαθήματος";

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
    list($facid, $facname) = split("--", $faculte);
	$sql = mysql_query("UPDATE cours SET faculte='$facname', titulaires='$titulaires', intitule='$intitule', faculteid='$facid' WHERE code='$c'");
	if (mysql_affected_rows() > 0) {
		$sql = mysql_query("UPDATE cours_faculte SET faculte='$facname', facid='$facid' WHERE code='$c'");
		$tool_content .= "<p>Τα στοιχεία του μαθήματος άλλαξαν με επιτυχία!</p>";
	} else {
		$tool_content .= "<p>Δεν πραγματοποιήθηκε καμία αλλαγή!</p>";
	}

} else {
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='$c'"));

	$tool_content .= "<form action=".$_SERVER[PHP_SELF]."?c=".$c."".$searchurl." method=\"post\">";	
	$tool_content .= "<table width=\"99%\"><caption>Αλλαγή Στοιχείων Μαθήματος</caption><tbody>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><b><u>Στοιχεία Μαθήματος</u></b><br><br></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Τμήμα:</b></td>
    <td><select name=\"faculte\">\n";
  
$resultFac=mysql_query("SELECT id,name FROM faculte ORDER BY number");

	while ($myfac = mysql_fetch_array($resultFac)) {	
		if($myfac['id'] == $row['faculteid']) 
			$tool_content .= "      <option value=\"".$myfac['id']."--".$myfac[name]."\" selected>$myfac[name]</option>";
		else 
			$tool_content .= "      <option value=\"".$myfac['id']."--".$myfac[name]."\">$myfac[name]</option>";
	}
	$tool_content .= "</select>
    </td>
  </tr>";  
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Κωδικός:</b></td>
    <td><i>".$row['code']."</i></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Τίτλος:</b></td>
    <td><input type=\"text\" name=\"intitule\" value=\"".$row['intitule']."\" size=\"60\"></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Διδάσκων:</b></td>
    <td><input type=\"text\" name=\"titulaires\" value=\"".$row['titulaires']."\" size=\"60\"></td>
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
