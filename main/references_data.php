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


$require_login = TRUE;

require_once '../include/baseTheme.php';
require_once 'include/lib/references.class.php';

if (isset($_GET['cid']) && isset($_GET['tid'])) {
    list($c, $cid) = explode(':',$_GET['cid']);
    $course = intval($cid);
    $module = intval($_GET['tid']);
    echo json_encode(References::get_course_module_items($course, $module));
} else if(isset($_GET['cid'])) {
    list($c, $cid) = explode(':',$_GET['cid']);
    $course = intval($cid);
    echo json_encode(References::get_course_modules($course));
} else if(isset($_GET['tid'])) {
    echo json_encode(References::get_general_module_items(intval($_GET['tid'])));
} else {
    echo json_encode(References::get_user_courselist());
}
