<?php

/* ========================================================================
 * Open eClass 3.0
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


/* ===========================================================================
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

  ============================================================================== */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

if (isset($_GET['c'])) {
    $c = q($_GET['c']);
    $_SESSION['c_temp'] = $c;
}

if (!isset($c)) {
    $c = $_SESSION['c_temp'];
}

// validate course Id
$cId = course_code_to_id($c);
validateCourseNodes($cId, isDepartmentAdmin());

// Define $nameTools
$nameTools = $langCourseEdit;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// A course has been selected
if (isset($c)) {    
    // Get information about selected course
    $row = Database::get()->querySingle("SELECT course.code as code, course.title as title , course.prof_names as prof_names, course.visible as visible
		  FROM course
		 WHERE course.code = ?s", $_GET['c']);    
    // Display course information and link to edit
    $tool_content .= "<fieldset>
                <legend>" . $langCourseInfo . " <a href='infocours.php?c=" . q($c) . "'>
                <img src='$themeimg/edit.png' alt='' title='" . q($langModify) . "'></a></legend>
	<table class='tbl' width='100%'>";

    $departments = $course->getDepartmentIds($cId);
    $i = 1;
    foreach ($departments as $dep) {
        $thtitle = ($i == 1) ? $langFaculty . ':' : '';
        $tool_content .= "
            <tr>
                <th width='250'>$thtitle</th>
                <td>" . $tree->getFullPath($dep) . "</td>
            </tr>";
        $i++;
    }

    $tool_content .= "
        <tr>
	  <th>$langCode:</th>
	  <td>" . q($row->code) . "</td>
	</tr>
	<tr>
	  <th><b>$langTitle:</b></th>
	  <td>" . q($row->title) . "</td>
	</tr>
	<tr>
	  <th><b>" . $langTutor . ":</b></th>
	  <td>" . q($row->prof_names) . "</td>
	</tr>
	</table>
	</fieldset>";
    // Display course quota and link to edit
    $tool_content .= "<fieldset>
	<legend>" . $langQuota . " <a href='quotacours.php?c=" . q($c) . "'><img src='$themeimg/edit.png' alt='' title='" . q($langModify) . "'></a></legend>
        <table width='100%' class='tbl'>
	<tr>
	  <td colspan='2'><div class='sub_title1'>$langTheCourse " . q($row->title) . " $langMaxQuota</div></td>
	  </tr>";
    // Get information about course quota
    $q = Database::get()->querySingle("SELECT code, title, doc_quota, video_quota, group_quota, dropbox_quota
			FROM course WHERE code=?s",$c);
    $dq = format_file_size($q->doc_quota);
    $vq = format_file_size($q->video_quota);
    $gq = format_file_size($q->group_quota);
    $drq = format_file_size($q->dropbox_quota);

    $tool_content .= "
	<tr>
	  <td>$langLegend <b>$langDoc</b>:</td>
	  <td>" . $dq . "</td>
	</tr>";
    $tool_content .= "
	<tr>
	  <td>$langLegend <b>$langVideo</b>:</td>
	  <td>" . $vq . "</td>
	</tr>";
    $tool_content .= "
	<tr>
	  <td width='250'>$langLegend <b>$langGroups</b>:</td>
	  <td>" . $gq . "</td>
	</tr>";
    $tool_content .= "
	<tr>
	  <td>$langLegend <b>$langDropBox</b>:</td>
	  <td>" . $drq . "</td>
	</tr>";
    $tool_content .= "</table></fieldset>";
    // Display course type and link to edit
    $tool_content .= "<fieldset>
                <legend>$langCourseStatus
                        <a href='statuscours.php?c=" . q($c) . "'><img src='$themeimg/edit.png' alt='".q($langModify)."' title='".q($langModify)."'></a>
                </legend>
                <table width='100%' class='tbl'>";
    $tool_content .= "<tr><th width='250'>" . $langCurrentStatus . ":</th><td>";
    switch ($row->visible) {
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
	<legend>" . $langOtherActions . "</legend>
        <table width='100%' class='tbl'>";
    // Users list
    $tool_content .= "
	<tr>
	  <td><a href='listusers.php?c=" . $cId . "'>" . $langListUsersActions . "</a></td>
	</tr>";
    // Register unregister users
    $tool_content .= "
	<tr>
	  <td><a href='addusertocours.php?c=" . q($c) . "'>" . $langAdminUsers . "</a></td>
	</tr>";
    // Backup course
    $tool_content .= "<tr>
	  <td><a href='../course_info/archive_course.php?c=" . q($c) . "'>" . $langTakeBackup . "</a></td>
	</tr>";
    // Course metadata 
    if (get_config('course_metadata'))
        $tool_content .= "<tr>
          <td><a href='../course_metadata/index.php?course=" . q($c) . "'>" . $langCourseMetadata . "</a></td>
        </tr>";
    // Delete course
    $tool_content .= "
	<tr>
	  <td><a href='delcours.php?c=" . $cId . "'>" . $langCourseDel . "</a></td>
	</tr>";
    $tool_content .= "</table></fieldset>";        
}
// If $c is not set we have a problem
else {
    // Print an error message
    $tool_content .= "<br><p align='right'>$langErrChoose</p>";    
}
// Display link to go back to listcours.php
    $tool_content .= "<br><p align='right'><a href='searchcours.php'>" . $langBack . "</a></p>";

draw($tool_content, 3);
