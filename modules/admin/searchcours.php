<?php
$langFiles = array('gunet','admin','registration');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Αναζήτηση Μαθημάτων";

// Initialise $tool_content
$tool_content = "";
// Main body


if (isset($new) && ($new=="yes")) {
	session_unregister('searchtitle');
	session_unregister('searchcode');
	session_unregister('searchtype');
	session_unregister('searchfaculte');
	unset($searchtitle);
	unset($searchcode);
	unset($searchtype);
	unset($searchfaculte);
}

if (isset($searchtitle) && isset($searchcode) && isset($searchtype) && isset($searchfaculte)) {
	$newsearch = "(<a href=\"searchcours.php?new=yes\">Νέα Αναζήτηση</a>)";
}
	
	
	$tool_content .= "<form action=\"listcours.php?search=yes\" method=\"post\">";
	$tool_content .= "<table width=\"99%\"><caption>Κριτήρια Αναζήτησης ".$newsearch."</caption><tbody>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Τίτλος:</b></td>
    <td><input type=\"text\" name=\"formsearchtitle\" size=\"40\" value=\"".$searchtitle."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Κωδικός:</b></td>
    <td><input type=\"text\" name=\"formsearchcode\" size=\"40\" value=\"".$searchcode."\"></td>
</tr>";
	switch ($searchcode) {
		case "2":
			$typeSel[2] = "selected";
			break;
		case "1":
			$typeSel[1] = "selected";
			break;
		case "0":
			$typeSel[0] = "selected";
			break;
		default:
			$typeSel[-1] = "selected";
			break;
	}
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Τύπος πρόσβασης:</b></td>
    <td>
      <select name=\"formsearchtype\">
      	<option value=\"-1\" ".$typeSel[-1].">Όλα</option>
        <option value=\"2\" ".$typeSel[2].">Ανοιχτό</option>
        <option value=\"1\" ".$typeSel[1].">Ανοιχτό με εγγραφή</option>
        <option value=\"0\" ".$typeSel[0].">Κλειστό</option>
      </select>
    </td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>Τμήμα:</b></td>
    <td><select name=\"formsearchfaculte\">
    	<option value=\"0\">Όλα</option>\n";
  
$resultFac=mysql_query("SELECT name FROM faculte ORDER BY number");

	while ($myfac = mysql_fetch_array($resultFac)) {	
		if($myfac['name'] == $searchfaculte) 
			$tool_content .= "      <option selected>$myfac[name]</option>";
		else 
			$tool_content .= "      <option>$myfac[name]</option>";
	}
	$tool_content .= "</select>
    </td>
  </tr>";  
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='search_submit' value='Αναζήτηση'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>";

	$tool_content .= "<center><p><a href=\"index.php\">Επιστροφή</a></p></center>";


draw($tool_content,3,'admin');
?>