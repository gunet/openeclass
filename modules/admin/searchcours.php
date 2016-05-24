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
 * @file searchcours.php
 * @brief search on courses by title, code, type and faculty
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#id_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-right', 
                language: '" . $language . "',
                autoclose: true    
            });
        });
    </script>";

$toolName = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$data['action_bar'] = action_bar(array(
    array('title' => $langAllCourses,
        'url' => "listcours.php",
        'icon' => 'fa-search',
        'level' => 'primary-label'),
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));


$data['reg_flag_data'][1] = $langAfter;
$data['reg_flag_data'][2] = $langBefore;

if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
}

$head_content .= $js;
$data['html'] = $html;

$data['menuTypeID'] = 3;
view('admin.courses.searchcours', $data);
