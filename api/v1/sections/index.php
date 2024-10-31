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

function api_method($access) {
    if (!isset($_GET['course_id'])) {
        Access::error(2, 'Required parameter course_id missing');
    }
    $course_id = $_GET['course_id'];
    $course = Database::get()->querySingle('SELECT id, code, visible FROM course
        WHERE code = ?s AND visible <> ?d',
        $course_id, COURSE_INACTIVE);
    if (!$course) {
        Access::error(3, "Course with id '$course_id' not found");
    }
    if (!($access->isValid or $course->visible == COURSE_OPEN)) {
        Access::error(100, "Authentication required");
    }
    $units = Database::get()->queryArray('SELECT id, title AS name, comments AS summary
        FROM course_units
        WHERE course_id = ?d AND visible = 1
        ORDER BY `order`', $course->id);
    header('Content-Type: application/json');
    echo json_encode($units, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'apiCall.php';
