<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
require_once 'include/lib/csv.class.php';

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . "_learning_path_user_stats.csv";
$csv->outputRecord($langStudent, $langAm, $langGroup, $langProgress);

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`
		FROM `user` AS U, `course_user` AS CU
		WHERE U.`id`= CU.`user_id`
		AND CU.`course_id` = ?d
		ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);
foreach ($usersList as $user) {
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
    $csv->outputRecord(uid_to_name($user->id), uid_to_am($user->id), user_groups($course_id, $user->id, 'csv'), $total . '%');
}
