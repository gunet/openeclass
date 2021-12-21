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
