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

    if ( isset($_GET['course_id']) ) {

        $courseCode = $_GET['course_id'];

        $course = Database::get()->queryArray("SELECT c.code, COUNT(cu.course_id) AS count
            FROM course c
            JOIN course_user cu ON c.id = cu.course_id
            WHERE c.code = ?s
            GROUP BY c.code;",$courseCode );

//        $count = $course[0]->count;


        header('Content-Type: application/json');
        echo json_encode($course, JSON_UNESCAPED_UNICODE);
        exit();
    }

}


chdir('..');
//require_once 'index.php';
require_once 'apiCall.php';