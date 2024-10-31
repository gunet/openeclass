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
$require_course_admin = true;

require_once '../../include/baseTheme.php';

if (isset($_GET['term'])) {
    $q = $_GET['term'];

    $courselist = Database::get()->queryArray("SELECT id, title, public_code FROM course
        WHERE title LIKE ?s OR code LIKE ?s OR public_code LIKE ?s
        ORDER BY title, public_code, code
        LIMIT 30", "%$q%", "$q%", "$q%");

    if ($courselist) {
        foreach ($courselist as $course) {
            $courses[] = array('id' => $course->id, 'text' => $course->title . ' (' . $course->public_code . ')');
        }
    } else {
        $courses[] = [];
    }

} else {
    $courses[] = [];
}

echo json_encode(['results' => $courses], JSON_UNESCAPED_UNICODE);
