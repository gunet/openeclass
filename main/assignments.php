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



/**
 * Personalised Assignments Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package eClass Personalised
 *
 * @abstract This component populates the assignments block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/**
 * Function getUserAssignments
 *
 * Populates an array with data regarding the user's personalised assignments
 *
 * @param array $param
 * @param string $type (data, html)
 * @return array
 */
function getUserAssignments($param, $type) {
    global $mysqlMainDb;

    $uid = $param['uid'];
    $lesson_code = $param['lesson_code'];
    $lesson_id = $param['lesson_id'];
    $max_repeat_val = $param['max_repeat_val'];
    $lesson_titles = $param['lesson_titles'];
    $lesson_professor = $param['lesson_professor'];

    for ($i = 0; $i < $max_repeat_val; $i++) {
        $assignments_query[$i] = "SELECT DISTINCT assignment.id, assignment.title,
                        assignment.description, assignment.deadline,
                        course.title,(TO_DAYS(assignment.deadline) - TO_DAYS(NOW())) AS days_left
                        FROM assignment, course, course_module
                        WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) >= '0'
                        AND assignment.active = 1
                        AND assignment.course_id = $lesson_id[$i]
                        AND course.id = $lesson_id[$i]
                        AND course_module.course_id = course.id
                        AND course_module.visible = 1 AND course_module.module_id = " . MODULE_ID_ASSIGN . "
                        ORDER BY assignment.deadline";
    }

    //initialise array to store all assignments from all lessons
    $assignSubGroup = array();
    for ($i = 0; $i < $max_repeat_val; $i++) {//each iteration refers to one lesson
        $mysql_query_result = db_query($assignments_query[$i], $mysqlMainDb);
        if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {
            $assignmentLessonData = array();
            $assignmentData = array();
            array_push($assignmentLessonData, $lesson_titles[$i]);
            array_push($assignmentLessonData, $lesson_code[$i]);
        }

        $assignments_repeat_val = 0;
        while ($myAssignments = mysql_fetch_row($mysql_query_result)) {
            if ($myAssignments) {
                if (submitted($uid, $myAssignments[0], $lesson_id[$i])) {
                    $lesson_assign[$i][$assignments_repeat_val]['delivered'] = 1; //delivered
                    array_push($myAssignments, 1);
                } else {
                    $lesson_assign[$i][$assignments_repeat_val]['delivered'] = 0; //not delivered
                    array_push($myAssignments, 0);
                }
                array_push($assignmentData, $myAssignments);
            }
        }

        if ($num_rows > 0) {
            array_push($assignmentLessonData, $assignmentData);
            array_push($assignSubGroup, $assignmentLessonData);
        }
    }

    // order assignments according to lesson code
    //$assignGroup = columnSort($assignmentLessonData, 1);

    if ($type == "html") {
        return assignHtmlInterface($assignSubGroup);
    } elseif ($type == "data") {
        return $assignSubGroup;
    }
}

/**
 * Function assignHtmlInterface
 *
 * Generates html content for the assignments block of eClass personalised.
 *
 * @param array $data
 * @return string HTML content for the assignments block
 * @see getUserAssignments()
 */
function assignHtmlInterface($data) {
    global $langCourse, $langAssignment, $langDeadline, $langNoAssignmentsExist, $langGroupWorkSubmitted1, $langGroupWorkDeadline_of_Submission, $langGroupWorkSubmitted, $langExerciseEnd, $urlServer, $urlAppend;

    $assign_content = '';
    $assignmentsExist = false;
    $max_repeat_val = count($data);
    for ($i = 0; $i < $max_repeat_val; $i++) {
        if ($i == 0) {
            $assign_content = "<table width='100%'>";
        }
        $iterator = count($data[$i][2]);
        $assign_content .= "<tr><td class='sub_title1'>" . q($data[$i][0]) . "</td></tr>";
        if ($iterator > 0) {
            $assignmentsExist = true;
            for ($j = 0; $j < $iterator; $j++) {
                $url = $urlServer . "modules/work/index.php?course=" .
                        $data[$i][1] . "&amp;i=" . $data[$i][2][$j][0];

                if ($data[$i][2][$j][6] == 1) {
                    $submit_status = "" . $langGroupWorkSubmitted . "";
                } elseif ($data[$i][2][$j][6] == 0) {
                    $submit_status = "$langGroupWorkSubmitted1";
                } else {
                    $submit_status = "";
                }
                $assign_content .= "";
                $assign_content .= "<tr><td><ul class='custom_list'><li><a href='$url'><b>" .
                        q($data[$i][2][$j][1]) .
                        "</b></a><div class='smaller'>$langGroupWorkDeadline_of_Submission: <b>" .
                        nice_format($data[$i][2][$j][3], true) . "</b><div class='grey'>" .
                        $submit_status . "</div></div></li></ul></td></tr>";
            }
        }
        if ($i + 1 == $max_repeat_val) {
            $assign_content .= "</table>";
        }
    }

    if (!$assignmentsExist) {
        $assign_content .= "<p class='alert1'>$langNoAssignmentsExist</p>";
    }

    return $assign_content;
}

/**
 * Function columnSort
 *
 * Sorts an array by one of it's columns specified by $column
 *
 * @param array $unsorted
 * @param mixed $column (array dimension to sort)
 * @return array sorted $unsorted
 */
function columnSort($unsorted, $column) {
    //bubbleSort
    $sorted = $unsorted;
    for ($i = 0; $i < sizeof($sorted) - 1; $i++) {
        for ($j = 0; $j < sizeof($sorted) - 1 - $i; $j++)
            if ($sorted[$j][$column] > $sorted[$j + 1][$column]) {
                $tmp = $sorted[$j];
                $sorted[$j] = $sorted[$j + 1];
                $sorted[$j + 1] = $tmp;
            }
    }
    return $sorted;
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

/**
 * Function isGroupAssignment
 *
 * Checks if an assignments is a group assignment
 * Returns true if it is.
 *
 * @param int $id
 * @param string $lesson_db
 * @return boolean
 */
function isGroupAssignment($id) {
    $res = db_query("SELECT group_submissions FROM assignments WHERE id = $id");
    if ($res) {
        $row = mysql_fetch_row($res);
        if ($row[0] == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else {
        die("Error: assignment $id doesn't exist");
    }
}
