<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
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
    $newdepip = isset($_POST['newdepid']) ? $_POST['newdepid'] : array();
    foreach ($_POST['lessons'] as $cId) {
        $course->refresh($cId, $_POST['newdepid']);
    }
    Session::flash('message',$langModifDone);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/listcours.php');

}

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

$data['sql'] = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                           FROM course, course_department, hierarchy
                          WHERE course.id = course_department.course
                            AND hierarchy.id = course_department.department
                                $query", $terms);



list($js, $html) = $tree->buildNodePicker(array('params' => 'name="newdepid[]"',
                                                'defaults' => $course->getDepartmentIds($searchfaculte),
                                                'multiple' => false));
$head_content .= $js;
$data['html'] = $html;

view('admin.courses.multieditcourse', $data);
