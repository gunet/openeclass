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

$pageName = $langCourseStatus;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
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
    
// Update course status
if (isset($_POST['submit'])) {
    // Update query
    $sql = Database::get()->query("UPDATE course SET visible=?d WHERE code=?s", $_POST['formvisible'], $_GET['c']);
    // Some changes occured
    if ($sql->affectedRows > 0) {
        $tool_content .= "<div class='alert alert-info'> $langCourseStatusChangedSuccess</div>";
    }
    // Nothing updated
    else {
        $tool_content .= "<div class='alert alert-warning'>$langNoChangeHappened</div>";
    }
}
// Display edit form for course status
else {
    // Get course information
    $visibleChecked[Database::get()->querySingle("SELECT * FROM course WHERE code=?s", $_GET['c'])->visible] = "checked";

    $tool_content .= "<div class='form-wrapper'>
            <form role='form' class='form-horizontal' action=" . $_SERVER['SCRIPT_NAME'] . "?c=" . q($_GET['c']) . " method='post'>                
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>$langAvailableTypes:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input id='courseopen' type='radio' name='formvisible' value='2'" . @$visibleChecked[2] . ">
                        <img src='$themeimg/lock_open.png' alt='$langOpenCourse' title='$langOpenCourse' width='16'>&nbsp;$langOpenCourse
                        <span class='help-block'><small>$langPublic</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='coursewithregistration' type='radio' name='formvisible' value='1'" . @$visibleChecked[1] . ">
                        <img src='$themeimg/lock_registration.png' alt='$m[legrestricted]' title='$m[legrestricted]' width='16'>&nbsp;$m[legrestricted]
                        <span class='help-block'><small>$langPrivOpen</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseclose' type='radio' name='formvisible' value='0'" . @$visibleChecked[0] . ">
                        <img src='$themeimg/lock_closed.png' alt='$langClosedCourse' title='$langClosedCourse' width='16'>&nbsp;$langClosedCourse
                        <span class='help-block'><small>$langClosedCourseShort</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseinactive' type='radio' name='formvisible' value='3'" . @$visibleChecked[3] . ">
                        <img src='$themeimg/lock_inactive.png' alt='$langInactiveCourse' title='$langInactiveCourse' width='16'>&nbsp;$langInactiveCourse
                        <span class='help-block'><small>$langCourseInactiveShort</small></span>
                      </label>
                    </div>                   
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='$langModify'>
                </div>
            </div>
            </fieldset>
            </form>
        </div>";
}

draw($tool_content, 3);

