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


/* * ===========================================================================
  delcours.php
  @last update: 31-05-2006 by Pitsiougas Vagelis
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Pitsiougas Vagelis <vagpits@uom.gr>
  ==============================================================================
  @Description: Delete a course

  This script allows the administrator to delete a course

  The user can : - Confirm for course deletion
  - Delete a cours
  - Return to course list

  @Comments: The script is organised in three sections.

  1) Confirm course deletion
  2) Delete course
  3) Display all on an HTML page

  ============================================================================== */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (isset($_GET['c'])) {
    $course_id = intval($_GET['c']);
} else {
    $course_id = 0;
}

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
validateCourseNodes($course_id, isDepartmentAdmin());

$nameTools = $langCourseDel;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// Delete course
if (isset($_GET['delete']) && $course_id) {
    delete_course($course_id);
    $tool_content .= "<div class='alert alert-success'>" . $langCourseDelSuccess . "</div>";
}
// Display confirmatiom message for course deletion
else {
    if (!Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id)) {
        $tool_content .= "<p class='right'><a href='index.php'>$langBack</a></p>";
        draw($tool_content, 3);
        exit();
    }

    $tool_content .= "<fieldset>
	<legend>" . $langCourseDelConfirm . "</legend>
	<table class='tbl' width='100%'>";
    $tool_content .= "<tr><td>
		<div class='alert alert-danger'>" . $langCourseDelConfirm2 . " <em>" . q(course_id_to_title($course_id)) . "</em>;
		<br /><br /><i>" . $langNoticeDel . "</i><br />
		</div></td></tr>";
    $tool_content .= "<tr>
	<td><ul class='custom_list'><li><a href='" . $_SERVER['SCRIPT_NAME'] . "?c=" . q($course_id) . "&amp;delete=yes'><b>$langYes</b></a></li>
	<li><a href='listcours.php'><b>$langNo</b></a></li></ul></td>
	</tr>";
    $tool_content .= "</table></fieldset>";
}
// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
    $tool_content .= "<p class='right'><a href='listcours.php'>$langBack</a></p>";
}
// Display link to index.php
else {
    $tool_content .= "<p class='right'><a href='index.php'>$langBack</a></p>";
}
draw($tool_content, 3);
