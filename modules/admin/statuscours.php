<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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


/*===========================================================================
	statuscours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Edit status of a course

 	This script allows the administrator to edit the status of a selected
 	course

 	The user can : - Edit the status of a course
                 - Return to edit course list

 	@Comments: The script is organised in four sections.

  1) Get course status information
  2) Edit that information
  3) Update course status
  4) Display all on an HTML page

==============================================================================*/


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
include '../../include/baseTheme.php';

if(!isset($_GET['c'])) { die(); }
// Define $nameTools
$nameTools = $langCourseStatus;
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
// Update course status
if (isset($submit))  {
  // Update query
	$sql = mysql_query("UPDATE cours SET visible='$formvisible' WHERE code='".mysql_real_escape_string($_GET['c'])."'");
	// Some changes occured
	if (mysql_affected_rows() > 0) {
		$tool_content .= "<p>".$langCourseStatusChangedSuccess."</p>";
	}
	// Nothing updated
	else {
		$tool_content .= "<p>".$langNoChangeHappened."</p>";
	}

}
// Display edit form for course status
else {
	// Get course information
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='".mysql_real_escape_string($_GET['c'])."'"));
	$visible = $row['visible'];
	$visibleChecked[$visible]="checked";
	// Constract edit form
	$tool_content .= "<form action=".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])."".$searchurl." method=\"post\">
	<table class=\"FormData\" width=\"99%\" align=\"left\">
	<tbody>
	<tr><th width=\"220\">&nbsp;</th>
	<td colspan=\"2\"><b>".$langCourseStatusChange."<b></td></tr>";
	
	$tool_content .= "<tr><th class=\"left\" rowspan=\"3\">$langConfTip</th>
	<td width=\"1\"><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
	<td>".$langPublic."</td>
	</tr>
	<tr>
	<td><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
	<td>".$langPrivOpen."</td>
	</tr>
	<tr>
	<td><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
	<td>".$langPrivate."</td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td colspan=\"2\"><input type='submit' name='submit' value='$langModify'></td>
	</tr>
	</tbody></table>
	</form>";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
	$tool_content .= "<p align=\"right\"><a href=\"editcours.php?c=".htmlspecialchars($_GET['c'])."".$searchurl."\">".$langBack."</a></p>";
}
// Else go back to index.php directly
else {
	$tool_content .= "<p align=\"right\"><a href=\"index.php\">".$langBackAdmin."</a></p>";
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
