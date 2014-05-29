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
 * @file statuscours.php
 * @brief Edit course status
 */

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
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

// Update course status
if (isset($_POST['submit'])) {
    // Update query
    $sql = Database::get()->query("UPDATE course SET visible=?d WHERE code=?s", $_POST['formvisible'], $_GET['c']);
    // Some changes occured
    if ($sql->affectedRows > 0) {
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
    $visibleChecked[Database::get()->querySingle("SELECT * FROM course WHERE code=?s", $_GET['c'])->visible] = "checked";

    $tool_content .= "<form action=" . $_SERVER['SCRIPT_NAME'] . "?c=" . q($_GET['c']) . " method='post'>
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
    $tool_content .= "<p align='right'><a href='editcours.php?c=" . q($_GET['c']) . "'>" . $langBack . "</a></p>";
}
// Else go back to index.php directly
else {
    $tool_content .= "<p align='right'><a href='index.php'>" . $langBackAdmin . "</a></p>";
}
draw($tool_content, 3);

