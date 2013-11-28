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

  ============================================================================== */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (!isset($_GET['c'])) {
    die();
}

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
$cId = course_code_to_id($_GET['c']);
validateCourseNodes($cId, isDepartmentAdmin());

$nameTools = $langCourseStatus;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);
$navigation[] = array('url' => 'editcours.php?c=' . htmlspecialchars($_GET['c']), 'name' => $langCourseEdit);

// Update course status
if (isset($_POST['submit'])) {
    // Update query
    $sql = db_query("UPDATE course SET visible='" . intval($_POST['formvisible']) . "'
			WHERE code='" . mysql_real_escape_string($_GET['c']) . "'");
    // Some changes occured
    if (mysql_affected_rows() > 0) {
        $tool_content .= "<p>" . $langCourseStatusChangedSuccess . "</p>";
    }
    // Nothing updated
    else {
        $tool_content .= "<p>" . $langNoChangeHappened . "</p>";
    }
}
// Display edit form for course status
else {
    // Get course information
    $row = mysql_fetch_array(db_query("SELECT * FROM course
		WHERE code='" . mysql_real_escape_string($_GET['c']) . "'"));
    $visible = $row['visible'];
    $visibleChecked[$visible] = "checked";

    $tool_content .= "<form action=" . $_SERVER['SCRIPT_NAME'] . "?c=" . htmlspecialchars($_GET['c']) . " method=\"post\">
        <fieldset>
	<legend>" . $langCourseStatusChange . "</legend>
	<table class='tbl' width='100%'>";
    $tool_content .= "<tr><th class='left' rowspan='4'>$langConfTip</th>
	<td width='1'><input type='radio' name='formvisible' value='2'" . @$visibleChecked[2] . "></td>
	<td>" . $langPublic . "</td>
	</tr>
	<tr>
	<td><input type='radio' name='formvisible' value='1'" . @$visibleChecked[1] . "></td>
	<td>" . $langPrivOpen . "</td>
	</tr>
	<tr>
	<td><input type='radio' name='formvisible' value='0'" . @$visibleChecked[0] . "></td>
	<td>" . $langPrivate . "</td>
	</tr>
        <tr>
	<td><input type='radio' name='formvisible' value='3'" . @$visibleChecked[3] . "></td>
	<td>" . $langCourseInactive . "</td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td colspan='2' class='right'><input type='submit' name='submit' value='$langModify'></td>
	</tr>
	</table></fieldset>
	</form>";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
    $tool_content .= "<p align=\"right\"><a href='editcours.php?c=" . htmlspecialchars($_GET['c']) . "'>" . $langBack . "</a></p>";
}
// Else go back to index.php directly
else {
    $tool_content .= "<p align=\"right\"><a href=\"index.php\">" . $langBackAdmin . "</a></p>";
}
draw($tool_content, 3);

