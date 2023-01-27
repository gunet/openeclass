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

if (empty($_REQUEST['uInfo'])) { // user info can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $uInfo = intval($_REQUEST['uInfo']);
}

// check if user is in this course
$rescnt = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM `course_user` as `cu` , `user` as `u`
            WHERE `cu`.`user_id` = `u`.`id`
            AND `cu`.`course_id` = ?d
            AND `u`.`id` = ?d", $course_id, $uInfo)->count;

if ($rescnt == 0) {
    header("Location: ./index.php?course=$course_code");
    exit();
}

// get list of learning paths of this course
// list available learning paths
$lpList = Database::get()->queryArray("SELECT name, learnPath_id
            FROM lp_learnPath
            WHERE course_id = ?d
            ORDER BY `rank`", $course_id);

// get infos about the user
$uDetails = Database::get()->querySingle("SELECT surname, givenname, email 
    FROM `user`
    WHERE id = ?d", $_REQUEST['uInfo']);

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . " - " . htmlspecialchars($uDetails->surname . " " . $uDetails->givenname) . "_user_stats.csv";
$csv->outputRecord($langLearnPath, $langAttemptsNb, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLessonStatus, $langProgress);

$totalProgress = 0;
$totalTimeSpent = "0000:00:00";
foreach ($lpList as $lpDetails) {
    list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($lpDetails->learnPath_id, $uInfo);
    $totalProgress += $lpProgress;
    if (!empty($lpTotalTime)) {
        $totalTimeSpent = addScormTime($totalTimeSpent, $lpTotalTime);
    }

    $csv->outputRecord(
        htmlspecialchars($lpDetails->name),
        $lpAttemptsNb,
        $lpTotalStarted,
        $lpTotalAccessed,
        $lpTotalTime,
        disp_lesson_status($lpTotalStatus),
        $lpProgress . '%'
    );
}

if (count($lpList) > 0) {
    $csv->outputRecord(
        $langTotal,
        '',
        '',
        '',
        $totalTimeSpent,
        '',
        round($totalProgress/count($lpList)) . '%'
    );
}