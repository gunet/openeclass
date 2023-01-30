<?php
/* ========================================================================
 * Open eClass 3.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
require_once 'include/lib/csv.class.php';
require_once 'functions.php';

if (isset($_GET['t'])) {
    $t = intval($_GET['t']);
} else {
    $t = 1;
}

$gid = getDirectReference($_GET['gradebook_id']);
$gradebook_title = get_gradebook_title($gid);
$range = get_gradebook_range($gid);

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . "_users_gradebook.csv";
$csv->outputRecord($gradebook_title);
$csv->outputRecord();

if ($t == 1) { // display gradebook activities results
    $activities = Database::get()->queryArray("SELECT id, title FROM gradebook_activities WHERE gradebook_id = ?d", $gid);
    foreach ($activities as $act) {
        $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
        $csv->outputRecord($title)
            ->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langGradebookGrade);

        $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, gradebook_users.uid, grade 
                    FROM gradebook_users
                    LEFT JOIN gradebook_book 
                        ON gradebook_book.uid = gradebook_users.uid
                        AND gradebook_activity_id = ?d
                    JOIN user
                        ON user.id = gradebook_users.uid
                    WHERE gradebook_id = ?d
                    ORDER BY surname", $act->id, $gid);
        foreach ($entries as $item) {
            if (!is_null($item->grade)) {
                $csv->outputRecord($item->surname, $item->givenname, $item->am, $item->username, $item->email, round($item->grade * $range, 2));
            } else {
                $csv->outputRecord($item->surname, $item->givenname, $item->am, $item->username, $item->email, $item->grade);
            }
        }
        $csv->outputRecord();
    }
} elseif ($t == 2) { // display gradebook users results
    // data header
    $data_header = array();
    // mapping of activity id's to output columns
    $actId = array();
    $actCounter = 0;
    array_push($data_header, $langSurname, $langName, $langUsername, $langAm, $langEmail);
    $activities = Database::get()->queryArray("SELECT id, title FROM gradebook_activities WHERE gradebook_id = ?d", $gid);
    foreach ($activities as $act) {
        $actId[$act->id] = $actCounter++;
        $data_header[] = $act->title;
    }
    $data_header[] = $langGradebookTotalGrade;
    $csv->outputRecord($data_header);

    // user grades
    $range = get_gradebook_range($gid);
    $sql_users = Database::get()->queryArray("SELECT uid, givenname, surname, username, am, email 
                                            FROM gradebook_users 
                                            JOIN user
                                            ON user.id = gradebook_users.uid
                                            WHERE gradebook_id = ?d
                                            ORDER BY surname", $gid);
    foreach ($sql_users as $data) {
        $data_user_intro = array();
        array_push($data_user_intro, $data->surname,
                                     $data->givenname,
                                     $data->username,
                                     $data->am,
                                     $data->email);

        $sql_grades = Database::get()->queryArray("SELECT gradebook_activity_id, grade FROM gradebook_book
                                        JOIN gradebook_activities 
                                            ON gradebook_activity_id = gradebook_activities.id
                                            AND gradebook_id = ?d
                                            AND uid = ?d", $gid, $data->uid);
        $data_user_grades = array_fill(0, $actCounter, '-');
        foreach ($sql_grades as $g) {
            $position = $actId[$g->gradebook_activity_id];
            $data_user_grades[$position] = round($g->grade * $range, 2); // activities grade
        }
        $data_user_grades[] = userGradeTotal($gid, $data->uid, true); // total grade
        $csv->outputRecord($data_user_intro, $data_user_grades);
    }
} elseif ($t == 3) { // display gradebook activity results
    $activity_id = $_GET['activity_id'];

    $activity_title = get_gradebook_activity_title($gid, $activity_id);

    $csv->outputRecord($activity_title)
        ->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langGradebookGrade);
    $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, gradebook_users.uid, grade 
                    FROM gradebook_users
                    LEFT JOIN gradebook_book 
                        ON gradebook_book.uid = gradebook_users.uid
                        AND gradebook_activity_id = ?d
                    JOIN user
                        ON user.id = gradebook_users.uid
                    WHERE gradebook_id = ?d
                    ORDER BY surname",
            $activity_id, $gid);
    foreach ($entries as $item) {
        if (!is_null($item->grade)) {
            $csv->outputRecord($item->surname, $item->givenname, $item->am, $item->username, $item->email, round($item->grade * $range, 2));
        } else {
            $csv->outputRecord($item->surname, $item->givenname, $item->am, $item->username, $item->email, $item->grade);
        }
    }
    $csv->outputRecord();
}
