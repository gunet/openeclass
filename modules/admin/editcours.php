<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


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

$require_power_user = true;

include '../../include/baseTheme.php';
include '../../include/lib/fileDisplayLib.inc.php';

if (isset($_GET['c'])) {
	$c = q($_GET['c']);
	$_SESSION['c_temp'] = $c;
}

if(!isset($c)) {
	$c = $_SESSION['c_temp'];
}

// Define $nameTools
$nameTools = $langCourseEdit;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// Initialize some variables
$searchurl = '';

// A course has been selected
if (isset($c)) {
	// Define $searchurl to go back to search results
	if (isset($search) && ($search=='yes')) {
		$searchurl = '&search=yes';
	}
	// Get information about selected course
	$sql = db_query("SELECT * FROM cours WHERE code = " . quote($c));
	$row = mysql_fetch_array($sql);
        
        if ($row === false) {
            // Print an error message
            $tool_content .= "<br><p align=\"right\">$langErrChoose</p>";
            // Display link to go back to listcours.php
            $tool_content .= "<br><p align=\"right\"><a href=\"listcours.php\">$langBack</a></p>";
            draw($tool_content, 3);
            exit();
        }
        
	// Display course information and link to edit
        $faculte = find_faculty_by_id($row['faculteid']);
	$tool_content .= "<fieldset>
	<legend>".$langCourseInfo." <a href=\"infocours.php?c=".htmlspecialchars($c)."".$searchurl."\">
                <img src='$themeimg/edit.png' alt='' title='".q($langModify)."'></a></legend>
	<table class='tbl' width='100%'>";
	$tool_content .= "
	<tr>
	  <th width='250'>$langFaculty:</th>
	  <td>".q($faculte)."</td>
	</tr>
	<tr>
	  <th>$langCode:</th>
	  <td>".q($row['code'])."</td>
	</tr>
	<tr>
	  <th><b>$langTitle:</b></th>
	  <td>".q($row['intitule'])."</td>
	</tr>
	<tr>
	  <th><b>".$langTutor.":</b></th>
	  <td>".q($row['titulaires'])."</td>
	</tr>
	</table>
	</fieldset>";
	// Display course quota and link to edit
	$tool_content .= "<fieldset>
	<legend>".$langQuota." <a href=\"quotacours.php?c=".q($c).$searchurl."\">
                <img src='$themeimg/edit.png' alt='' title='".q($langModify)."'></a></legend>
<table width='100%' class='tbl'>
	<tr>
	  <td colspan='2'><div class='sub_title1'>$langTheCourse " . q($row['intitule']) . " $langMaxQuota</div></td>
	  </tr>";
	// Get information about course quota
	$q = mysql_fetch_array(db_query("SELECT code, intitule, doc_quota, video_quota, group_quota, dropbox_quota
			FROM cours WHERE code='".mysql_real_escape_string($c)."'"));
	$dq = format_file_size($q['doc_quota']);
	$vq = format_file_size($q['video_quota']);
	$gq = format_file_size($q['group_quota']);
	$drq = format_file_size($q['dropbox_quota']);

	$tool_content .= "
	<tr>
	  <td>$langLegend <b>$langDoc</b>:</td>
	  <td>".$dq."</td>
	</tr>";
	$tool_content .= "
	<tr>
	  <td>$langLegend <b>$langVideo</b>:</td>
	  <td>".$vq."</td>
	</tr>";
	$tool_content .= "
	<tr>
	  <td width='250'>$langLegend <b>$langGroups</b>:</td>
	  <td>".$gq."</td>
	</tr>";
	$tool_content .= "
	<tr>
	  <td>$langLegend <b>$langDropBox</b>:</td>
	  <td>".$drq."</td>
	</tr>";
	$tool_content .= "</table></fieldset>";
	// Display course type and link to edit
	$tool_content .= "<fieldset>
                <legend>$langCourseStatus
                        <a href='statuscours.php?c=".q($c).
                                "$searchurl'><img src='$themeimg/edit.png' alt='".q($langModify)."' title='".q($langModify)."'></a>
                </legend>
                <table width='100%' class='tbl'>";
	$tool_content .= "<tr><th width='250'>".$langCurrentStatus.":</th><td>";
	switch ($row['visible']) {
                case COURSE_CLOSED:
                        $tool_content .= $langClosedCourse;
                        break;
                case COURSE_OPEN:
                        $tool_content .= $langOpenCourse;
                        break;
                case COURSE_REGISTRATION:
                        $tool_content .= $langRegCourse;
                        break;	
                case COURSE_INACTIVE:
                        $tool_content .= $langCourseInactive;
                        break;	
	}
	$tool_content .= "</td></tr></table></fieldset>";
	// Display other available choices
	$tool_content .= "
	<fieldset>
	<legend>".$langOtherActions."</legend>
        <table width='100%' class='tbl'>";
	// Users list
	$tool_content .= "
	<tr>
	  <td><a href=\"listusers.php?c=".course_code_to_id($c)."\">".$langListUsersActions."</a></td>
	</tr>";
  // Register unregister users
	$tool_content .= "
	<tr>
	  <td><a href=\"addusertocours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langAdminUsers."</a></td>
	</tr>";
  // Backup course
	$tool_content .= "<tr>
	  <td><a href=\"../course_info/archive_course.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langTakeBackup."</a></td>
	</tr>";
  // Delete course
	$tool_content .= "
	<tr>
	  <td><a href=\"delcours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langCourseDelFull."</a></td>
	</tr>";
	$tool_content .= "</table></fieldset>";

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

draw($tool_content, 3);
