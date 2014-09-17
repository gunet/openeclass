<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
    $langComments, $langAddModulesButton, $langChoice, $langNoExercises, $langExercices, $course_code;


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
        $tool_content .= "<p class='alert1'>$langNoExercises</p>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id'>" .
                "<table width='99%' class='tbl_alt'>" .
                "<tr>" .
                "<th><div align='left'>&nbsp;$langExercices</div></th>" .
                "<th><div align='left'>$langComments</div></th>" .
                "<th width='100'>$langChoice</th>" .
                "</tr>";
        $i = 0;
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'invisible';
            } else {
                if ($i % 2 == 0) {
                    $vis = 'even';
                } else {
                    $vis = 'odd';
                }
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td>&laquo; <a href='${urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$entry[id]'>$entry[name]</a></td>";
            $tool_content .= "<td><div align='left'>$entry[comment]</div></td>";
            $tool_content .= "<td class='center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";
            $i++;
        }
        $tool_content .= "<tr><th colspan='3'><div align='right'>";
        $tool_content .= "<input type='submit' name='submit_exercise' value='$langAddModulesButton'></div></th>";
        $tool_content .= "</tr></table></form>";
    }
}
