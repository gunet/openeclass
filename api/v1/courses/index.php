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
    $courses = Database::get()->queryArray('SELECT course.code AS id,
            course.title AS name,
            course.title AS shortname,
            course_department.department AS categoryid,
            "" AS summary,
            course.description AS description,
            created AS timecreated,
            created AS timemodified
        FROM course
            JOIN course_department ON course.id = course_department.course
        WHERE visible <> ' . COURSE_INACTIVE . '
        ORDER BY course.title');
    header('Content-Type: application/json');
    echo json_encode($courses, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'index.php';
