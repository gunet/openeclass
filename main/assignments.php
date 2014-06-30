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

/**
 * @brief get course user assingment
 * @file assignment.php
 */

/**
 * @brief display course user assingment
 * @global type $langNoAssignmentsExist
 * @global type $langGroupWorkSubmitted
 * @global type $langGroupWorkNotSubmitted
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langGroupWorkSubmitted
 * @global type $urlServer
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserAssignments($param) {
           
    global $langNoAssignmentsExist, $langGroupWorkSubmitted, $langDays, $langDaysLeft,
            $langGroupWorkDeadline_of_Submission, $langGroupWorkSubmitted,$urlServer, $uid;
      
    $lesson_id = $param['lesson_id'];    

    $found = false;
    $assign_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT DISTINCT assignment.id, assignment.title, assignment.deadline,
                                        (TO_DAYS(assignment.deadline) - TO_DAYS(NOW())) AS days_left
                                    FROM assignment, course, course_module
                                        WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) >= '0'
                                        AND assignment.active = 1
                                        AND assignment.course_id = ?d
                                        AND course.id = ?d
                                        AND course_module.course_id = course.id
                                        AND course_module.visible = 1 AND course_module.module_id = " . MODULE_ID_ASSIGN . "
                                    ORDER BY assignment.deadline", $lid, $lid);
    
        if ($q) {
            $found = true;
            $assign_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $data) {
                $url = $urlServer . "modules/work/index.php?course=" . course_id_to_code($lid) . "&amp;i=" . $data->id;
                if (submitted($uid, $data->id, $lid)) {
                    $submit_status = $langGroupWorkSubmitted;
                } else {
                    $submit_status = "($langDaysLeft $data->days_left $langDays)";
                }
                $assign_content .= "<tr><td><ul class='custom_list'><li><a href='$url'><b>" .
                        q($data->title) .
                        "</b></a><div class='smaller'>$langGroupWorkDeadline_of_Submission: <b>" .
                        nice_format($data->deadline, true) . "</b><div class='grey'>" .
                        $submit_status . "</div></div></li></ul></td></tr>";                
            }
        }
    }
    $assign_content .= "</table>";
    if ($found) {
        return $assign_content;
    } else {
        return "<p class='alert1'>$langNoAssignmentsExist</p>";
    }
}

/**
 *
 *  returns whether the user has submitted an assignment
 */
function submitted($uid, $assignment_id, $lesson_id) {
    // find prefix
    $prefix = './modules';
    if (!file_exists($prefix) && file_exists('../group') && file_exists('../work'))
        $prefix = '..';

    require_once($prefix . '/group/group_functions.php');
    require_once($prefix . '/work/work_functions.php');

    $gids = user_group_info($uid, $lesson_id);
    $GLOBALS['course_id'] = $lesson_id;

    if ($submission = find_submissions(is_group_assignment($assignment_id), $uid, $assignment_id, $gids))
        return true;
    else
        return false;
}