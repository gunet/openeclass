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
 * @file infocours.php
 * @brief edit basic course information
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

load_js('jstree');

// Define $nameTools
$nameTools = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCours);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

// Update cours basic information
if (isset($_POST['submit'])) {
    $departments = isset($_POST['department']) ? $_POST['department'] : array();

    // if depadmin then diff new/old deps and if new or deleted deps are out of juristinction, then error
    if (isDepartmentAdmin()) {
        $olddeps = $course->getDepartmentIds($cId);

        foreach ($departments as $depId) {
            if (!in_array($depId, $olddeps))
                validateNode(intval($depId), true);
        }

        foreach ($olddeps as $depId) {
            if (!in_array($depId, $departments))
                validateNode($depId, true);
        }
    }

    // Update query
    Database::get()->query("UPDATE course SET title = ?s,
                    prof_names = ?s
                    WHERE code = ?s", $_POST['title'], $_POST['prof_names'], $_GET['c']);

    $course->refresh($cId, $departments);

    $tool_content .= "<p class='success'>$langModifDone</p>
                <p>&laquo; <a href='editcours.php?c=$_GET[c]'>$langBack</a></p>";
}
// Display edit form for course basic information
else {
    $row = Database::get()->querySingle("SELECT course.code as code, course.title as title ,course.prof_names as prof_name, course.id as id
		  FROM course
		 WHERE course.code = ?s" ,$_GET['c']);
    $tool_content .= "
	<form action='" . $_SERVER['SCRIPT_NAME'] . "?c=" . q($_GET['c']) . "' method='post' onsubmit='return validateNodePickerForm();'>
	<fieldset>
	<legend>" . $langCourseInfoEdit . "</legend>
<table width='100%' class='tbl'><tr><th>$langFaculty</th><td>";

    if (isDepartmentAdmin())
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($row->id), 'allowables' => $user->getDepartmentIds($uid)));
    else
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($row->id)));

    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</td></tr>
	<tr>
	  <th width='150'>" . $langCourseCode . ":</th>
	  <td><i>" . $row->code . "</i></td>
	</tr>
	<tr>
	  <th>" . $langTitle . ":</th>
	  <td><input type='text' name='title' value='" . q($row->title) . "' size='60'></td>
	</tr>
	<tr>
	  <th>" . $langTeacher . ":</th>
	  <td><input type='text' name='prof_names' value='" . q($row->prof_name) . "' size='60'></td>
	</tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='right'><input type='submit' name='submit' value='$langModify'></td>
	</tr>
	</tbody>
	</table>
	</form></fieldset>\n";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
    $tool_content .= "<p align='right'><a href='editcours.php?c=" . q($_GET['c']) . "'>" . $langBack . "</a></p>";
}
// Else go back to index.php directly
else {
    $tool_content .= "<p align='right'><a href='index.php'>" . $langBackAdmin . "</a></p>";
}
draw($tool_content, 3, null, $head_content);
