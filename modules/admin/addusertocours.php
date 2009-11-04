<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
if(!isset($_GET['c'])) { die(); }
// Define $nameTools
$nameTools = $langAdminUsers;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listcours.php", "name" => $langListCours);
$navigation[] = array("url" => "editcours.php?c=".htmlspecialchars($_GET['c']), "name" => $langCourseEdit);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Initialize some variables
$searchurl = "";

// Define $searchurl to go back to search results
if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}
// Register - Unregister students - professors to course
if (isset($submit))  {
	// Count students and professors
	$numberStuds = count($regstuds);
	$numberProfs = count($regprofs);

	// Wash out all course users
	$sql = mysql_query("DELETE FROM cours_user WHERE code_cours = '".mysql_real_escape_string($_GET['c'])."'
		AND user_id != '".$uid."'");

	for ($i=0; $i < $numberStuds; $i++) {
		// Insert student
		$sqlInsertStud = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, reg_date)
			VALUES ('".mysql_real_escape_string($_GET['c'])."', '".$regstuds[$i]."', '5', CURDATE())";
		db_query($sqlInsertStud) ;
	}

	for ($i=0; $i < $numberProfs; $i++) {
		// Insert professor
		$sqlInsertProf = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`,  reg_date)
			VALUES ('".mysql_real_escape_string($_GET['c'])."', '".$regprofs[$i]."', '1', CURDATE())";
		db_query($sqlInsertProf) ;
	}

	$tool_content .= "<p>".$langQuickAddDelUserToCoursSuccess."</p>";

}
// Display form to manage users
else {
	// Some javascript is needed
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

	$tool_content .= "<form action=".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])."".$searchurl." method=\"post\">";
	$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\"><tbody>
	<tr><th colspan=\"3\">".$langFormUserManage."</th></tr>
	<tr><th align=left>".$langListNotRegisteredUsers."<br />
	<select name=\"unregusers[]\" size=20 multiple class=\"auth_input\">";

	// Registered users not registered in the selected course
	$sqll= "SELECT DISTINCT u.user_id , u.nom, u.prenom FROM user u
		LEFT JOIN cours_user cu ON u.user_id = cu.user_id 
		AND cu.code_cours = '".mysql_real_escape_string($_GET['c'])."'
		WHERE cu.user_id is null ORDER BY nom";

	$resultAll=mysql_query($sqll);
	while ($myuser = mysql_fetch_array($resultAll))
	{
		$tool_content .= "<option value='$myuser[user_id]'>$myuser[nom] $myuser[prenom]</option>";
	}

	$tool_content .= "</select></th>
	<td width='3%' class='center' nowrap>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p align='center'><b>".$langStudents."</b></p>
	<p>";

	// WATCH OUT ! form elements are called by numbers "form.element[3]"...
	// because select name contains "[]" causing a javascript element name problem
	$tool_content .= "<p align=\"center\"><input type=\"button\" onClick=\"move(this.form.elements[0],this.form.elements[5])\" value=\"   >>   \">
	<input type=\"button\" onClick=\"move(this.form.elements[5],this.form.elements[0])\" value=\"   <<   \">
	</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p align=\"center\"><b>".$langTeachers."</b></p>";

	// WATCH OUT ! form elements are called by numbers "form.element[3]"...
	// because select name contains "[]" causing a javascript element name problem
	$tool_content .= "<p align=\"center\"><input type=\"button\" onClick=\"move(this.form.elements[0],this.form.elements[6])\" value=\"   >>   \">
	<input type=\"button\" onClick=\"move(this.form.elements[6],this.form.elements[0])\" value=\"   <<   \"></p>
	</td>
	<th>".$langListRegisteredStudents."<br />
	<select name=\"regstuds[]\" size=\"8\" multiple class=\"auth_input\">";

	// Students registered in the selected course
	$resultStud=mysql_query("SELECT DISTINCT u.user_id , u.nom, u.prenom
				FROM user u, cours_user cu
				WHERE cu.code_cours='".mysql_real_escape_string($_GET['c'])."'
				AND cu.user_id=u.user_id
				AND cu.statut=5 ORDER BY nom");

	$a=0;
	while ($myStud = mysql_fetch_array($resultStud))
		{
		$tool_content .= "<option value=\"".$myStud['user_id']."\">$myStud[nom] $myStud[prenom]</option>";
		$a++;
	}

	$tool_content .= "</select>
		<p>&nbsp;</p>
		".$langListRegisteredProfessors."<br />
		<select name=\"regprofs[]\" size=\"8\" multiple class=\"auth_input\">";
	// Professors registered in the selected course
	// Administrator is excluded
	$resultProf=mysql_query("SELECT DISTINCT u.user_id , u.nom, u.prenom
				FROM user u, cours_user cu
				WHERE cu.code_cours='".mysql_real_escape_string($_GET['c'])."'
				AND cu.user_id=u.user_id
				AND cu.statut=1
				AND u.user_id!='$uid' ORDER BY nom");
	$a=0;
	while ($myProf = mysql_fetch_array($resultProf)) {
		$tool_content .= "<option value=\"".$myProf['user_id']."\">$myProf[nom] $myProf[prenom]</option>";
		$a++;
	}
	$tool_content .= "</select></th></tr><tr><td>&nbsp;</td>
		<td><input type=submit value=\"".$langAcceptChanges."\" name=\"submit\" onClick=\"selectAll(this.form.elements[5],this.form.elements[6],true)\"></td>
		<td>&nbsp;</td>
		</tr></tbody></table>";
	$tool_content .= "</form>";

}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
	$tool_content .= "<p align=\"right\">
	<a href=\"editcours.php?c=".htmlspecialchars($_GET['c'])."".$searchurl."\">".$langBack."</a></p>";
}
// Else go back to index.php directly
else {
	$tool_content .= "<p align='right'><a href='index.php'>".$langBackAdmin."</a></p>";
}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
