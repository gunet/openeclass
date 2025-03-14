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

/**
 * @file searchcours.php
 * @brief search on courses by title, code, type and faculty
 */

$require_departmentmanage_user = true;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'course_search';


require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$toolName = $langAdmin;
$pageName = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$data['action_bar'] = action_bar(array(
    array('title' => $langAllCourses,
        'url' => "listcours.php",
        'icon' => 'fa-search',
        'level' => 'primary-label')
    ));

$data['reg_flag_data'][1] = $langAfter;
$data['reg_flag_data'][2] = $langBefore;


if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
}

$head_content .= $js;
$data['html'] = $html;

view('admin.courses.searchcours', $data);
