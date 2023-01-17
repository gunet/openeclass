<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

function api_method($access) {
    $categories = Database::get()->queryArray('SELECT hierarchy.id, hierarchy.name, hierarchy.description,
            MIN(course.created) AS timemodified, 0 AS sortorder
        FROM hierarchy
            JOIN course_department ON hierarchy.id = course_department.department
            JOIN course ON course_department.course = course.id
        WHERE allow_course = 1
        ORDER BY name');
    header('Content-Type: application/json');
    echo json_encode($categories, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'index.php';
