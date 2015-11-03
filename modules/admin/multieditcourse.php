<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

$toolName = $langChangeDepartment;

$tree = new Hierarchy();
$course = new Course();

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
load_js('jstree3');

if (isset($_POST['submit'])) { 
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();   
    $newdepip = isset($_POST['newdepid']) ? arrayValuesDirect($_POST['newdepid']) : array();
    foreach ($_POST['lessons'] as $cId) {
        $course->refresh($cId, arrayValuesDirect($_POST['newdepid']));
    }
    $tool_content .= "<div class='alert alert-success'>$langModifDone</div>";
    
} else {
    $searchtitle = isset($_POST['formsearchtitle']) ? $_POST['formsearchtitle'] : '';
    $searchcode = isset($_POST['formsearchcode']) ? $_POST['formsearchcode'] : '';
    $searchtype = isset($_POST['formsearchtype']) ? intval($_POST['formsearchtype']) : '-1';
    $searchfaculte = isset($_POST['formsearchfaculte']) ? intval(getDirectReference($_POST['formsearchfaculte'])) : '';
    // Search for courses
    $query = '';
    $terms = array();
    if (!empty($searchtitle)) {
        $query .= ' AND title LIKE ?s';
        $terms[] = '%' . $searchtitle . '%';
    }
    if (!empty($searchcode)) {
        $query .= ' AND (course.code LIKE ?s OR public_code LIKE ?s)';
        $terms[] = '%' . $searchcode . '%';
        $terms[] = '%' . $searchcode . '%';
    }
    if ($searchtype != "-1") {
        $query .= ' AND visible = ?d';
        $terms[] = $searchtype;
    }

    if ($searchfaculte) {    
        $subs = $tree->buildSubtrees(array($searchfaculte));
        $ids = 0;
        foreach ($subs as $key => $id) {
            $terms[] = $id;
            $ids++;
        }
        $query .= ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
    }

    if (isset($_GET['reg_flag']) and ! empty($_GET['date'])) {
        $query .= ' AND created ' . (($_GET['reg_flag'] == 1) ? '>=' : '<=') . ' ?s';
        $date_created_at = DateTime::createFromFormat("d-m-Y H:i", $_GET['date']);
        $terms[] = $date_created_at->format("Y-m-d H:i:s");
    }

    $query .= (isDepartmentAdmin()) ? ' AND course_department.department IN (' . implode(', ', $user->getDepartmentIds($uid)) . ') ' : '';

    $sql = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                               FROM course, course_department, hierarchy
                              WHERE course.id = course_department.course
                                AND hierarchy.id = course_department.department
                                    $query", $terms); 

    $tool_content .= "<div class='alert alert-info'>$langMultiMoveCourseInfo</div>";
    $tool_content .= "<div class='form-wrapper'>        
            <form role='form' class='form-horizontal' action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();'>
            <fieldset>
            <div class='form-group'>
                <label for='Faculty' class='col-sm-2 control-label'>$langFaculty:</label>
                <div class='col-sm-10'>";    

            list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="newdepid[]"', 
                                                            'defaults' => $course->getDepartmentIds($searchfaculte),                                                            
                                                            'multiple' => false));        
            $head_content .= $js;
            $tool_content .= $html;
            $tool_content .= "</div></div>";
            foreach ($sql as $results) {
                $tool_content .= "<input type='hidden' name='lessons[]' value='$results->id'>";
            }
            $tool_content .= "<div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit' onclick='return confirmation(\"" . q($langConfirmMultiMoveCourses) . "\");'>
                            <a href='index.php' class='btn btn-default'>$langCancel</a>
                        </div>
                    </div>";
            $tool_content .= "</fieldset>
            ". generate_csrf_token_form_field() ."
            </form>
            </div>";
}

draw($tool_content, 3, '', $head_content);