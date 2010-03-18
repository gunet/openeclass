<?php
session_start();
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
	editcours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Show all information of a course and give links to edit

 	This script allows the administrator to see all available information of
 	a course and select other links to edit that information

 	The user can : - See all available course information
 	               - Select a link to edit some information
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Gather course information
  2) Embed available choices
  3) Display all on an HTML page

  @todo: Create a valid link for course statistics

==============================================================================*/

/*****************************************************************************
		DEAL WITH BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
include '../../include/lib/fileDisplayLib.inc.php';

if (isset($_GET['c'])) {
	$c = $_GET['c'];
	$_SESSION['c_temp']=$c;
}

if(!isset($c))
	$c=$_SESSION['c_temp'];

// Define $nameTools
$nameTools = $langCourseEdit;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listcours.php", "name" => $langListCours);

// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/

// Initialize some variables
$searchurl = "";

// Manage order of display list
if (isset($ord)) {
	switch ($ord) {
		case "s":
			$order = "b.statut"; break;
		case "n":
			$order = "a.nom"; break;
		case "p":
			$order = "a.prenom"; break;
		case "u":
			$order = "a.username"; break;
		default:
			$order = "b.statut"; break;
	}
} else {
	$order = "b.statut";
}
// A course has been selected
if (isset($c)) {
	// Define $searchurl to go back to search results
	if (isset($search) && ($search=="yes")) {
		$searchurl = "&search=yes";
	}
	// Get information about selected course
	$sql = mysql_query(
		"SELECT * FROM cours WHERE code = '".mysql_real_escape_string($c)."'");
	$row = mysql_fetch_array($sql);
	// Display course information and link to edit
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <th width=\"220\">&nbsp;</th>
    <td>".$langCourseInfo." (<a href=\"infocours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langModify."</a>)</td>
  </tr>";
	$tool_content .= "
  <tr>
    <th class=\"left\">".$langFaculty.":</th>
    <td>".$row['faculte']."</td>
  </tr>
  <tr>
    <th class=\"left\">".$langCode.":</th>
    <td>".$row['code']."</td>
  </tr>
  <tr>
    <th class=\"left\"><b>".$langTitle.":</b></th>
    <td>".$row['intitule']."</td>
  </tr>
  <tr>
    <th class=\"left\"><b>".$langTutor.":</b></td>
    <td>".$row['titulaires']."</td>
  </tr>
  </tbody>
  </table>
  <br />\n";
	// Display course quota and link to edit
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <td colspan=\"2\">&nbsp;</td>
  </tr>
  <tr>
    <th width=\"220\" class=\"left\"><b>$langTheCourse <b>$row[intitule]</b> $langMaxQuota</b></th>
    <td>".$langQuota." (<a href=\"quotacours.php?c=".htmlspecialchars($c).$searchurl."\">".$langModify."</a>)</td>
  </tr>";
	// Get information about course quota
	$q = mysql_fetch_array(mysql_query("SELECT code,intitule,doc_quota,video_quota,group_quota,dropbox_quota
			FROM cours WHERE code='".mysql_real_escape_string($c)."'"));
	$dq = format_file_size($q['doc_quota']);
	$vq = format_file_size($q['video_quota']);
	$gq = format_file_size($q['group_quota']);
	$drq = format_file_size($q['dropbox_quota']);

	$tool_content .= "
  <tr>
    <th class=\"left\">$langLegend <b>$langDoc</b>:</th>
    <td>".$dq."</td>
  </tr>";
	$tool_content .= "
  <tr>
    <th class=\"left\">$langLegend <b>$langVideo</b>:</th>
    <td>".$vq."</td>
  </tr>";
	$tool_content .= "
  <tr>
    <th class=\"left\">$langLegend <b>$langGroups</b>:</th>
    <td>".$gq."</td>
  </tr>";
	$tool_content .= "
  <tr>
    <th class=\"left\">$langLegend <b>$langDropBox</b>:</th>
    <td>".$drq."</td>
  </tr>";
	$tool_content .= "
  </tbody>
  </table>
  <br />\n";
	// Display course type and link to edit
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <td colspan=\"2\">&nbsp;</td>
  </tr>
  <tr>
    <th width=\"220\">&nbsp;</th>
    <td>".$langCourseStatus." (<a href=\"statuscours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langModify."</a>)</td>
  </tr>";
	$tool_content .= "
  <tr>
    <th class=\"left\"><b>".$langCurrentStatus.":</b></th>
    <td>";
	switch ($row['visible']) {
	case 2:
		$tool_content .= $langOpenCourse;
		break;
	case 1:
		$tool_content .= $langRegCourse;
		break;
	case 0:
		$tool_content .= $langClosedCourse;
		break;
	}
    $tool_content .= "</td>
  </tr>
  </tbody>
  </table>
  <br>\n";
	// Display other available choices
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <td colspan=\"2\">&nbsp;</td>
  </tr>
  <tr>
    <th width=\"220\">&nbsp;</th>
    <td>".$langOtherActions."</td>
  </tr>";
	// Users list
	$tool_content .= "
  <tr>
    <th rowspan=\"5\">&nbsp;</th>
    <td><a href=\"listusers.php?c=".course_code_to_id($c)."\">".$langListUsersActions."</a></td>
  </tr>";
  // Register unregister users
	$tool_content .= "
  <tr>
    <td><a href=\"addusertocours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langAdminUsers."</a></td>
  </tr>";
  // Backup course
	$tool_content .= "
  <tr>
    <td><a href=\"../course_info/archive_course.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langTakeBackup."<a/></td>
  </tr>";
  // Delete course
	$tool_content .= "
  <tr>
    <td><a href=\"delcours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langCourseDelFull."</a></td>
  </tr>";
	$tool_content .= "
  </tbody>
  </table>
  <br>";

	// If a search is on display link to go back to listcours with search results
	if (isset($search) && ($search=="yes")) {
		$tool_content .= "<br><p align=\"right\"><a href=\"listcours.php?search=yes\">".$langReturnToSearch."</a></p>";
	}
	// Display link to go back to listcours.php
	$tool_content .= "<br><p align=\"right\"><a href=\"listcours.php\">".$langBack."</a></p>";
}
// If $c is not set we have a problem
else {
	// Print an error message
	$tool_content .= "<br><p align=\"right\">$langErrChoose</p>";
	// Display link to go back to listcours.php
	$tool_content .= "<br><p align=\"right\"><a href=\"listcours.php\">$langBack</a></p>";
}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3, 'admin');
?>
