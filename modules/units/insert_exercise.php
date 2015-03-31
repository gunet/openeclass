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
 * display list of exercises
 * @global type $id
 * @global type $course_id
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langComments
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoExercises
 * @global type $langExercices
 * @global type $course_code
 */
function list_exercises() {
    global $id, $course_id, $tool_content, $urlServer,
    $langDescr, $langAddModulesButton, $langChoice, $langNoExercises, $langExercices, $course_code;


    $result = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $course_id);
    $quizinfo = array();
    foreach ($result as $row) {
        $quizinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active);
    }
    if (count($quizinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoExercises</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th width='50%' class='text-left'>$langExercices</th>" .
                "<th class='text-left'>$langDescr</th>" .
                "<th class='checkbox_cell'>$langChoice</th>" .
                "</tr>";        
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-left'><a href='${urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$entry[id]'>" . q($entry['name']) . "</a></td>";
            $tool_content .= "<td class='text-left'>" . $entry['comment'] . "</td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";            
        }
        $tool_content .= "</table>
                    <div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_exercise' value='$langAddModulesButton'></div>
                </form>";
    }
}
