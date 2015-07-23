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

load_js('jstree3d');

$toolName = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
    $tool_content .= action_bar(array(
     array('title' => $langBack,
           'url' => "editcours.php?c=$_GET[c]",
           'icon' => 'fa-reply',
           'level' => 'primary-label')));
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
  }


// Update course basic information
if (isset($_POST['submit'])) {
    $departments = isset($_POST['department']) ? $_POST['department'] : array();

    // if depadmin then diff new/old deps and if new or deleted deps are out of juristinction, then error
    if (isDepartmentAdmin()) {
        $olddeps = $course->getDepartmentIds($cId);

        foreach ($departments as $depId) {
            if (!in_array($depId, $olddeps)) {
                validateNode(intval($depId), true);
            }
        }

        foreach ($olddeps as $depId) {
            if (!in_array($depId, $departments)) {
                validateNode($depId, true);
            }
        }
    }

    // Update query
    Database::get()->query("UPDATE course SET title = ?s,
                    prof_names = ?s
                    WHERE code = ?s", $_POST['title'], $_POST['titulary'], $_GET['c']);
    $course->refresh($cId, $departments);
    $tool_content .= "<div class='alert alert-success'>$langModifDone</div>";
}
// Display edit form for course basic information
else {
    $row = Database::get()->querySingle("SELECT course.code AS code, course.title AS title, course.prof_names AS prof_name, course.id AS id
                                            FROM course
                                           WHERE course.code = ?s" ,$_GET['c']);
    $tool_content .= "<div class='form-wrapper'>
	<form role='form' class='form-horizontal' action='" . $_SERVER['SCRIPT_NAME'] . "?c=" . q($_GET['c']) . "' method='post' onsubmit='return validateNodePickerForm();'>
	<fieldset>
        <div class='form-group'>
	    <label for='Faculty' class='col-sm-2 control-label'>$langFaculty:</label>
            <div class='col-sm-10'>";    

        if (isDepartmentAdmin()) {
            list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($row->id), 'allowables' => $user->getDepartmentIds($uid)));
        } else {
            list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($row->id)));
        }
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</div></div>";
        $tool_content .= "<div class='form-group'>
            <label for='fcode' class='col-sm-2 control-label'>$langCode</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' name='fcode' id='fcode' value='$row->code' size='60' />
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langCourseTitle:</label>
            <div class='col-sm-10'>
		<input type='text' class='form-control' name='title' id='title' value='" . q($row->title) . "' size='60' />
	    </div>
        </div>
        <div class='form-group'>
            <label for='titulary' class='col-sm-2 control-label'>$langTeachers:</label>
            <div class='col-sm-10'>
		<input type='text' class='form-control' name='titulary' id='titulary' value='" . q($row->prof_name) . "' size='60' />
	    </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-4'>
                <input class='btn btn-primary' type='submit' name='submit' value='$langModify'>
            </div>
        </div>
        </fieldset>
	</form>
        </div>";
}
draw($tool_content, 3, null, $head_content);
