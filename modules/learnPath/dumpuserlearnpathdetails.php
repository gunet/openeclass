<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'modules/group/group_functions.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
    $sendSep = true;
} else {
    $charset = 'UTF-8';
    $sendSep = false;
}
$crlf = "\r\n";

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=userslearningpathstats.csv");

if ($sendSep) {
    echo 'sep=;', $crlf;
}
    
echo join(';', array_map("csv_escape", array($langStudent, $langAm, $langGroup, $langProgress))),
 $crlf;

// display a list of user and their respective progress
$sql = "SELECT U.`surname`, U.`givenname`, U.`id`
		FROM `user` AS U, `course_user` AS CU
		WHERE U.`id`= CU.`user_id`
		AND CU.`course_id` = $course_id
		ORDER BY U.`surname` ASC, U.`givenname` ASC";
$usersList = get_limited_list($sql, 500000);
foreach ($usersList as $user) {
    echo "$crlf";
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d", $course_id);
    $iterator = 1;
    $globalprog = 0;

    foreach ($learningPathList as $learningPath) {
        // % progress
        $prog = get_learnPath_progress($learningPath->learnPath_id, $user->id);
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        $iterator++;
    }
    $total = round($globalprog / ($iterator - 1));
    echo csv_escape(uid_to_name($user->id)) .
    ";" . csv_escape(uid_to_am($user->id)) .
    ";" . csv_escape(user_groups($course_id, $user->id, 'csv')) .
    ";" . $total . "%";
}
echo $crlf;
