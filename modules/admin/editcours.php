<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


/**
 * @file editcours.php
 * @brief modify course details
 */

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

$toolName = $langCourseEdit;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

$tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "searchcours.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

// A course has been selected
if (isset($c)) {
    // Get information about selected course
    $row = Database::get()->querySingle("SELECT course.code as code, course.title as title , course.prof_names as prof_names, course.visible as visible
		  FROM course
		 WHERE course.code = ?s", $_GET['c']);

    // Display course information and link to edit
    $tool_content .= "<table class='table-default'>
                <th colspan='2'>" . $langCourseInfo . " ".icon('fa-gear',$langModify, "infocours.php?c=" . q($c) . "")."</th>";

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
	</table>";
    // Display course quota and link to edit
    $tool_content .= "<table class='table-default'>
	<th colspan='2'>$langQuota ".icon('fa-gear', $langModify, "quotacours.php?c=" . q($c) . ""). "</th>
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
    $tool_content .= "</table>";
    // Display course type and link to edit
    $tool_content .= "<table class='table-default'>
                <th colspan='2'>$langCourseStatus ".icon('fa-gear', $langModify, "statuscours.php?c=" . q($c) . "")."</th>";
    $tool_content .= "<tr><th width='250'>" . $langCurrentStatus . ":</th><td>" . course_status_message($cId) . "</td>";
    $tool_content .= "</tr></table>";
    // Display other available choices
    $tool_content .= "<table class='table-default'><th colspan='2'>$langOtherActions</th>";
    // Users list
    $tool_content .= "
	<tr>
	  <td><a href='listusers.php?c=$cId'>$langListUsersActions</a></td>
	</tr>";
    // Backup course
    $tool_content .= "<tr>
	  <td><a href='../course_info/archive_course.php?c=" . q($c). '&amp;' .generate_csrf_token_link_parameter() . "'>" . $langTakeBackup . "</a></td>
	</tr>";
    // Course metadata
    if (get_config('course_metadata')) {
        $tool_content .= "<tr>
          <td><a href='../course_metadata/index.php?course=" . q($c) . "'>" . $langCourseMetadata . "</a></td>
        </tr>";
    }
    if (get_config('opencourses_enable')) {
        $tool_content .= "<tr>
          <td><a href='../course_metadata/control.php?course=" . q($c) . "'>" . $langCourseMetadataControlPanel . "</a></td>
        </tr>";
    }
    // Delete course
    $tool_content .= "
	<tr>
	  <td><a href='delcours.php?c=$cId'>$langCourseDel</a></td>
	</tr>";
    $tool_content .= "</table>";
}
// If $c is not set we have a problem
else {
    // Print an error message
    $tool_content .= "<div class='alert alert-warning'>$langErrChoose</div>";
}
draw($tool_content, 3);
