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

if (empty($_REQUEST['path_id'])) { // path id can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $path_id = intval($_REQUEST['path_id']);
}

// get infos about the learningPath
$learnPathName = Database::get()->querySingle("SELECT `name` FROM `lp_learnPath` WHERE `learnPath_id` = ?d AND `course_id` = ?d", $path_id, $course_id);

if (!$learnPathName) {
    header("Location: ./index.php?course=$course_code");
    exit();
}

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . " - " . htmlspecialchars($learnPathName->name) . "_user_stats.csv";
$csv->outputRecord('Id', $langStudent, $langEmail, $langAm, $langGroup, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLessonStatus, $langProgress);

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`
        FROM `user` AS U, `course_user` AS CU
        WHERE U.`id`= CU.`user_id`
        AND CU.`course_id` = ?d
        ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);

foreach ($usersList as $user) {
    list($lpProgress, $lpTotalTime) = get_learnPath_progress_details($path_id, $user->id);
    list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus) = get_learnPath_progress_details($path_id, $user->id);

    $csv->outputRecord(
        $user->id,
        uid_to_name($user->id),
        $user->email,
        uid_to_am($user->id),
        user_groups($course_id,$user->id, 'csv'),
        $lpTotalStarted,
        $lpTotalAccessed,
        $lpTotalTime,
        disp_lesson_status($lpTotalStatus),
        $lpProgress . '%'
    );
}
