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

$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
require_once 'include/lib/csv.class.php';
require_once 'functions.php';

if (isset($_GET['t'])) {
    $t = intval($_GET['t']);
}

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . "_users_gradebook.csv";

$gid = getDirectReference($_GET['gradebook_id']);

$gradebook_title = get_gradebook_title($gid);
$csv->outputRecord($gradebook_title);
$csv->outputRecord();
        
if ($t == 1) { // display gradebook activities results
    $range = Database::get()->querySingle('SELECT `range` FROM gradebook
        WHERE course_id = ?d AND id = ?d', $course_id, $gid)->range;

    $activities = Database::get()->queryArray("SELECT id, title FROM gradebook_activities
        WHERE gradebook_id = ?d", $gid);
    foreach ($activities as $act) {
        $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
        $csv->outputRecord($title)
            ->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langGradebookGrade);
        $entries = Database::get()->queryArray('SELECT surname, givenname, username, am, email, grade
            FROM gradebook_book, user
            WHERE gradebook_book.uid = user.id AND gradebook_activity_id = ?d',
            $act->id);
        foreach ($entries as $item) {
            $csv->outputRecord($item->surname, $item->givenname, $item->am,
                $item->username, $item->email, round($item->grade * $range, 2));
        }
        $csv->outputRecord();
    }
} else { // display gradebook users results            
    
    // data header
    $data_header = array();
    array_push($data_header, $langSurname, $langName, $langAm, $langUsername, $langEmail);
    array_push($data_header, $langGradebookTotalGrade);
    $activities = Database::get()->queryArray("SELECT title FROM gradebook_activities WHERE visible = 1 AND gradebook_id = ?d", $gid);
    foreach ($activities as $act_title) {
        array_push($data_header, $act_title->title);
    }    
    $csv->outputRecord($data_header);
    
    // user grades
    $range = get_gradebook_range($gid);
    $sql_users = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gid);    
    foreach ($sql_users as $data) {
        $data_user_grades = array();
        array_push($data_user_grades, uid_to_name($data->uid, 'surname'), 
                                      uid_to_name($data->uid, 'givenname'), 
                                      uid_to_name($data->uid, 'username'),
                                      uid_to_am($data->uid), 
                                      uid_to_email($data->uid));
        array_push($data_user_grades, userGradeTotal($gid, $data->uid, true)); // total grade
        $sql_grades = Database::get()->queryArray("SELECT grade FROM gradebook_book
                                            WHERE gradebook_activity_id IN 
                                        (SELECT id FROM gradebook_activities WHERE gradebook_activities.visible = 1 AND gradebook_id = ?d)
                                            AND uid = ?d", $gid, $data->uid);
        foreach ($sql_grades as $g) {
            array_push($data_user_grades, round($g->grade * $range, 2)); // activities grade
        }        
        $csv->outputRecord($data_user_grades);
    }
}