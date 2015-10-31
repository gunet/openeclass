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

$require_admin = true;
require_once '../../include/baseTheme.php';

$q = $_GET['q'];

$courselist = Database::get()->queryArray("SELECT id, title, public_code FROM course
    WHERE title LIKE ?s OR code LIKE ?s OR public_code LIKE ?s
    ORDER BY title, public_code, code
    LIMIT 30", "%$q%", "$q%", "$q%");

if ($courselist) {
    foreach ($courselist as $course) {
        $courses[] = array('id' => getIndirectReference($course->id), 'text' => $course->title . ' (' . $course->public_code . ')');
    }
} else {
    $courses[] = array('title' => '');
}

echo json_encode($courses, JSON_UNESCAPED_UNICODE);
