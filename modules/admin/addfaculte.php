<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	addfaculte.php
	@last update: 12-07-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Manage Facultes

 	This script allows the administrator to list the available facultes, to
 	delete them or to make new ones.

 	The user can : - See the available facultes
 	               - Delete a faculte
 	               - Create a new faculte
 	               - Edit a faculte
                 - Return to main administrator page

 	@Comments: The script is organised in four sections.

  1) List of available facultes
  2) Add a new faculte
  3) Delete a faculte
  4) Display all on an HTML page

==============================================================================*/

/*****************************************************************************
		DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools=$langListFaculteActions;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
if (isset($a)) {
	switch ($a) {
		case 1:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteAdd;
			break;
		case 2:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteDel;
			break;
		case 3:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteEdit;
			break;
	}
}
// Initialise $tool_content
$tool_content = "";
/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Display all available faculties
if (!isset($a)) {
	// Count available faculties
	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM faculte"));
	// Construct a table
	$tool_content .= "<table width=\"99%\"><caption>".$langFaculteCatalog."</caption><tbody>";
	$tool_content .= "<tr><td colspan=\"3\"><i>".$langManyExist." $a[0] ".$langFaculteDepartments."</i></td</tr>";
	$tool_content .= "<tr><th scope=\"col\">$langCode</th><th scope=\"col\">".$langFaculteDepartment."</th scope=\"col\"><th>".$langActions."</th></tr>";
	$sql=mysql_query("SELECT code,name,id FROM faculte");

	// For all faculties display some info
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$logs = mysql_fetch_array($sql);
		$tool_content .= "<tr>";
		for ($i = 0; $i < 2; $i++) {
			$tool_content .= "<td>".htmlspecialchars($logs[$i])."</td>";
		}

		// Give administrator a link to delete or edit a faculty
    $tool_content .= "<td width=\"3%\" nowrap><a href=\"$_SERVER[PHP_SELF]?a=2&c=".$logs['id']."\">
				<img src='../../images/delete.gif' border='0' title='$langDelete'></img></a> 
			  &nbsp;&nbsp;<a href=\"$_SERVER[PHP_SELF]?a=3&c=".$logs['id']."\">
			  <img src='../../images/edit.gif' border='0' title='$langEdit'></img> 
			  </a></td></tr>\n"; 
	}
	// Close table correctly
	$tool_content .= "</tbody></table><br>";
	// Give administrator a link to add a new faculty
	$tool_content .= "<table width=\"99%\"><caption>".$langOtherActions."</caption><tbody>
	<tr><td><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langAdd."</a></td></tr></tbody></table>";
	$tool_content .= "<br><center><p><a href=\"index.php\">".$langBack."</a></p></center>";
}
// Add a new faculte
elseif ($a == 1)  {
	if (isset($add)) {
		// Check for empty fields
		if (empty($codefaculte) or empty($faculte)) {
			$tool_content .= "<p>".$langEmptyFaculte."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			}
		// Check for greek letters
		elseif (!preg_match("/^[A-Z0-9a-z_-]+$/", $codefaculte)) {
			$tool_content .= "<p>".$langGreekCode."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			}
		// Check if faculty code already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE code='$codefaculte'")) > 0) {
			$tool_content .= "<p>".$langFCodeExists."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			} 
		// Check if faculty name already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE name='$faculte'")) > 0) {
			$tool_content .= "<p>".$langFaculteExists."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
		} else {
		// OK Create the new faculty
			mysql_query("INSERT into faculte(code,name,generator,number) VALUES('$codefaculte','$faculte','100','1000')") 
				or die ($langNoSuccess);
			$tool_content .= "<p>".$langAddSuccess."</p><br>";
			}
	} else {
		// Display form for new faculte information
		$tool_content .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?a=1\">";
		$tool_content .= "<table width=\"99%\"><caption>$langFaculteIns</caption><tbody>";
		$tool_content .= "<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap>".$langCodeFaculte1.":</th><td><input type=\"text\" name=\"codefaculte\" value=\"".@$codefaculte."\"></td><td><i>".$langCodeFaculte2."</i></td></tr>
		<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap>".$langFaculte1.":</th><td><input type=\"text\" name=\"faculte\" value=\"".@$faculte."\"></td><td><i>".$langFaculte2."</i></td></tr>
		<tr><td colspan=\"2\"><input type=\"submit\" name=\"add\" value=\"".$langAddYes."\"></td</tr>
		</tbody></table></form>";
		}
		$tool_content .= "<br><center><p><a href=\"$_SERVER[PHP_SELF]\">".$langBack."</a></p></center>";
	}
// Delete faculty
elseif ($a == 2) {
	$s=mysql_query("SELECT * from cours WHERE faculteid='".mysql_real_escape_string($_GET['c'])."'");
	// Check for existing courses of a faculty
	if (mysql_num_rows($s) > 0)  {
		// The faculty cannot be deleted
		$tool_content .= "<p>".$langProErase."</p><br>";
		$tool_content .= "<p>".$langNoErase."</p><br>";
	} else {
		// The faculty can be deleted
		mysql_query("DELETE from faculte WHERE id='$c'");
		$tool_content .= "<p>$langErase</p><br>";
	}
	$tool_content .= "<br><center><p><a href=\"$_SERVER[PHP_SELF]\">".$langBack."</a></p></center>";
}
// Edit a faculte
elseif ($a == 3)  {
	if (isset($edit)) {
		// Check for empty fields
		if (empty($faculte)) {
			$tool_content .= "<p>".$langEmptyFaculte."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=3&c=$c\">$langReturnToEditFaculte</a></p></center>";
			} 
		// Check if faculte name already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE name='$faculte'")) > 0) {
			$tool_content .= "<p>".$langFaculteExists."</p><br>";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=3&c=$c\">$langReturnToEditFaculte</a></p></center>";
		} else {
		// OK Update the faculte
			mysql_query("UPDATE faculte SET name = '$faculte' WHERE id='$c'") 
				or die ($langNoSuccess);
		// For backwards compatibility update cours and cours_facult also
			mysql_query("UPDATE cours SET faculte = '$faculte' WHERE faculteid='$c'") 
				or die ($langNoSuccess);
			mysql_query("UPDATE cours_faculte SET faculte = '$faculte' WHERE facid='$c'") 
				or die ($langNoSuccess);
			$tool_content .= "<p>$langEditFacSucces</p><br>";
			}
	} else {
		// Get faculte information
		$sql = "SELECT code, name FROM faculte WHERE id='".mysql_real_escape_string($_GET['c'])."'";
		$result = mysql_query($sql);
		$myrow = mysql_fetch_array($result);
		// Display form for edit faculte information
		$tool_content .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?a=3\">";
		$tool_content .= "<table width=\"99%\"><caption>$langFaculteEdit</caption><tbody>";
		$tool_content .= "<tr><td width=\"3%\" nowrap>".$langCodeFaculte1.":</td>
				<td><input type=\"text\" name=\"codefaculte\" value=\"".$myrow['code']."\" readonly></td></tr>
				<tr><td>&nbsp;</td><td><i>".$langCodeFaculte2."</i></td></tr>
				<tr><td width=\"3%\" nowrap>".$langFaculte1.":</td>
				<td><input type=\"text\" name=\"faculte\" value=\"".$myrow['name']."\"></td></tr>
				<tr><td>&nbsp;</td><td><i>".$langFaculte2."</i></td></tr>
				<tr><td colspan=\"2\"><input type=\"hidden\" name=\"c\" value=\"".htmlspecialchars($_GET['c'])."\">
				<input type=\"submit\" name=\"edit\" value=\"$langAcceptChanges\"></td</tr>
				</tbody></table></form>";
		}
		$tool_content .= "<br><center><p><a href=\"$_SERVER[PHP_SELF]\">".$langBack."</a></p></center>";
	}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
?>
