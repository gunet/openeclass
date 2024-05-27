<?php

/* ========================================================================
   * Open eClass 3.9
   * E-learning and Course Management System
   * ========================================================================
   * Copyright 2003-2020 Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'functions.php';

$gid = getDirectReference($_GET['gradebook_id']);
if (isset($_GET['activity'])) {
    $activity_id = getDirectReference($_GET['activity']);
} else {
    $activity_id = null;
}

$users = [];
foreach (Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gid) as $user) {
    $users[] = $user->uid;
}
$placeholders = implode(', ', array_fill(0, count($users), '?d'));
if ($activity_id) {
    $activities = Database::get()->queryArray("SELECT * FROM gradebook_activities
        WHERE gradebook_id = ?d AND id = ?d", $gid, $activity_id);
} else {
    $activities = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d", $gid);
}

foreach ($activities as $act) {
    if (!$act->auto) {
        continue;
    } elseif ($act->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
        $attempts = Database::get()->queryArray("SELECT total_score, total_weighting, uid
            FROM exercise_user_record
            WHERE eid = ?d AND
                  attempt_status = ?d AND
                  uid IN ($placeholders)",
            $act->module_auto_id, ATTEMPT_COMPLETED, $users);
        $seen_users = [];
        foreach ($attempts as $attempt) {
            if (!isset($seen_users[$attempt->uid])) {
                // First delete existing grade to ensure refresh
                update_gradebook_book($attempt->uid, $act->module_auto_id,
                    null, GRADEBOOK_ACTIVITY_EXERCISE, $gid);
                $seen_users[$attempt->uid] = true;
            }
            if (is_null($attempt->total_weighting) or $attempt->total_weighting == 0) {
                update_gradebook_book($attempt->uid, $act->module_auto_id,0,GRADEBOOK_ACTIVITY_EXERCISE, $gid);
            } else {
                update_gradebook_book($attempt->uid, $act->module_auto_id,
                    $attempt->total_score / $attempt->total_weighting,
                    GRADEBOOK_ACTIVITY_EXERCISE, $gid);
            }
        }
    } elseif ($act->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
        $g = Database::get()->querySingle('SELECT max_grade, group_submissions FROM assignment WHERE id = ?d', $act->module_auto_id);
        $grades = Database::get()->queryArray("SELECT grade, uid FROM assignment_submit
            WHERE assignment_id = ?d AND uid IN ($placeholders)", $act->module_auto_id, $users);
        foreach ($grades as $grade) {
            if ($grade->grade) {
                if ($g->group_submissions) {
                    $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE assignment_id = ?d", $act->module_auto_id)->group_id;
                    $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                    foreach ($user_ids as $user_id) {
                        update_gradebook_book($user_id->user_id, $act->module_auto_id, $grade->grade / $g->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT, $gid);
                    }
                } else {
                    update_gradebook_book($grade->uid, $act->module_auto_id, $grade->grade / $g->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT, $gid);
                }
            }
        }
    } elseif ($act->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
        foreach ($users as $user) {
            $lpProgress = get_learnPath_progress($act->module_auto_id, $user);
            update_gradebook_book($user, $act->module_auto_id, $lpProgress/100, GRADEBOOK_ACTIVITY_LP);
        }
    }
}

//Session::Messages($langRefreshGradesDone, 'alert-success');
Session::flash('message',$langRefreshGradesDone); 
Session::flash('alert-class', 'alert-success');
redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$_GET[gradebook_id]");
