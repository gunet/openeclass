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

$require_current_course = true;
$path2add = 2;
require_once '../include/baseTheme.php';

if (isset($_SESSION['student_view'])) {
    unset($_SESSION['student_view']);
} else {
    $_SESSION['student_view'] = $course_code;
}

if (isset($_POST['next'])) {
    header('Location: ' . $_POST['next']);
} else {
    header("Location: {$urlServer}courses/$course_code/");
}
