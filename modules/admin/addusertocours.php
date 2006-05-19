<?
$langFiles = 'admin';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Γρήγορη εγγραφή - διαγραφή εκπαιδευομένων - εκπαιδευτών";

// Initialise $tool_content
$tool_content = "";
// Main body

if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}

if (isset($submit))  {
	
	$numberStuds = @count($regstuds);
	$numberProfs = @count($regprofs);
	
	// Wash out all course users
	$sql = mysql_query("DELETE FROM cours_user WHERE code_cours = '".$c."' AND user_id != '".$uid."'");
	
	for ($i=0; $i < $numberStuds; $i++) {
		// Insert student
		$sqlInsertStud = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `role`)
			VALUES ('".$c."', '".$regstuds[$i]."', '5', ' ')"; 
		mysql_query($sqlInsertStud) ;
	}

	for ($i=0; $i < $numberProfs; $i++) {
		// Insert professor
		$sqlInsertProf = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `role`)
			VALUES ('".$c."', '".$regprofs[$i]."', '1', 'Καθηγητής')"; 
		mysql_query($sqlInsertProf) ;
	}

	$tool_content .= "<p>Η διαχείριση χρηστών ολοκληρώθηκε με επιτυχία!</p>";

} else {
	
	$tool_content .= '<script type="text/javascript" language="JavaScript">

<!-- Begin javascript menu swapper 
function move(fbox, tbox) {
var arrFbox = new Array();
var arrTbox = new Array();
var arrLookup = new Array();
var i;
for (i = 0; i < tbox.options.length; i++) {
arrLookup[tbox.options[i].text] = tbox.options[i].value;
arrTbox[i] = tbox.options[i].text;
}
var fLength = 0;
var tLength = arrTbox.length;
for(i = 0; i < fbox.options.length; i++) {
arrLookup[fbox.options[i].text] = fbox.options[i].value;
if (fbox.options[i].selected && fbox.options[i].value != "") {
arrTbox[tLength] = fbox.options[i].text;
tLength++;
}
else {
arrFbox[fLength] = fbox.options[i].text;
fLength++;
   }
}
arrFbox.sort();
arrTbox.sort();
fbox.length = 0;
tbox.length = 0;
var c;
for(c = 0; c < arrFbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrFbox[c]];
no.text = arrFbox[c];
fbox[c] = no;
}
for(c = 0; c < arrTbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrTbox[c]];
no.text = arrTbox[c];
tbox[c] = no;
   }
}
//  End -->
</script>

<script type="text/javascript" language="JavaScript">

function selectAll(cbList1,cbList2,bSelect) {
  for (var i=0; i<cbList1.length; i++) 
    cbList1[i].selected = cbList1[i].checked = bSelect
  for (var i=0; i<cbList2.length; i++) 
    cbList2[i].selected = cbList2[i].checked = bSelect
}

function reverseAll(cbList) {
  for (var i=0; i<cbList.length; i++) {
    cbList[i].checked = !(cbList[i].checked) 
    cbList[i].selected = !(cbList[i].selected)
  }
}

</script>';
	
	$tool_content .= "<form action=".$_SERVER[PHP_SELF]."?c=".$c."".$searchurl." method=\"post\">";
	$tool_content .= "<table width=\"99%\"><caption>Φόρμα Διαχείρισης Χρηστών</caption><tbody>";

	$tool_content .= "<tr valign=top align=center> 
			<td align=left><font size=\"2\" face=\"arial, helvetica\">
				<b>Λίστα Μη Εγγεγραμμένων Χρηστών</b> <p>
	<select name=\"unregusers[]\" size=20 multiple>";  
  
  
  
	// Student registered to the course but inserted in no group
	
	$sqll= "SELECT DISTINCT u.user_id , u.nom, u.prenom 
				FROM user u
				LEFT JOIN cours_user cu ON u.user_id = cu.user_id AND cu.code_cours = '".$c."'
				WHERE cu.user_id is null";
	
	$resultAll=mysql_query($sqll);
	while ($myuser = mysql_fetch_array($resultAll))
	{
		$tool_content .= "<option value=\"$myuser[user_id]\">
			$myuser[prenom] $myuser[nom]
		</option>";
	
	}	// while loop

	$tool_content .= "</select>
			</p>
			<p>&nbsp; </p>
			</td>
			<td width=\"3%\" nowrap> 
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><b>Εκπαιδευόμενοι</b></p>
			<p>";

	// WATCH OUT ! form elements are called by numbers "form.element[3]"... 
	// because select name contains "[]" causing a javascript element name problem
	
	$tool_content .= "<input type=\"button\" onClick=\"move(this.form.elements[0],this.form.elements[5])\" value=\"   >>   \">
		<input type=\"button\" onClick=\"move(this.form.elements[5],this.form.elements[0])\" value=\"   <<   \">
		</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><b>Εκπαιδευτές</b></p>
			<p>";

	// WATCH OUT ! form elements are called by numbers "form.element[3]"... 
	// because select name contains "[]" causing a javascript element name problem
	
	$tool_content .= "<input type=\"button\" onClick=\"move(this.form.elements[0],this.form.elements[6])\" value=\"   >>   \">
		<input type=\"button\" onClick=\"move(this.form.elements[6],this.form.elements[0])\" value=\"   <<   \">
		</p>
		</td>
		<td><font size=\"2\" face=\"arial, helvetica\">
		<p><b>Λίστα Εγγεγραμμένων Εκπαιδευομένων</b></p>
		<p> 
		<select name=\"regstuds[]\" size=\"8\" multiple>";
	
	$resultStud=mysql_query("SELECT DISTINCT u.user_id , u.nom, u.prenom 
				FROM user u, cours_user cu
				WHERE cu.code_cours='$c'
				AND cu.user_id=u.user_id
				AND cu.statut=5");
	
	$a=0;
	while ($myStud = mysql_fetch_array($resultStud))
		{
	 	$tool_content .= "<option value=\"".$myStud['user_id']."\">$myStud[prenom] $myStud[nom]</option>";
		$a++;
	}

	$tool_content .= "</select></p>
		<p>&nbsp;</p>
		<p><b>Λίστα Εγγεγραμμένων Εκπαιδευτών</b></p>
		<p> 
		<select name=\"regprofs[]\" size=\"8\" multiple>";
	
	$resultProf=mysql_query("SELECT DISTINCT u.user_id , u.nom, u.prenom 
				FROM user u, cours_user cu
				WHERE cu.code_cours='$c'
				AND cu.user_id=u.user_id
				AND cu.statut=1
				AND u.user_id!='".$uid."'");
	
	$a=0;
	while ($myProf = mysql_fetch_array($resultProf))
		{
	 	$tool_content .= "<option value=\"".$myProf['user_id']."\">$myProf[prenom] $myProf[nom]</option>";
		$a++;
	}

	$tool_content .= "</select></p></td></tr><tr><td colspan=\"3\">
	<table width=\"100%\"><tbody>
	<tr><td align=\"center\"><input type=submit value=\"Επικύρωση Αλλαγών\" style=\"font-weight: bold\" name=\"submit\" onClick=\"selectAll(this.form.elements[5],this.form.elements[6],true)\"></td></tr></tbody></table>
	</td></tr></tbody></table>";
	
	$tool_content .= "</form>";
	
}

if (isset($c)) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".$c."".$searchurl."\">Επιστροφή</a></p></center>";
} else {
	$tool_content .= "<center><p><a href=\"index.php\">".$langBackAdmin."</a></p></center>";
}

draw($tool_content,3,'admin');

?>
