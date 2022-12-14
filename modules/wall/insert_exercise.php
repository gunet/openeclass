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

function list_exercises($id = NULL) {
    global $course_id, $course_code, $urlServer, $langDescription, $langChoice, $langExercices, $langNoExercises;

    $ret_string = '';
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
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'>$langNoExercises</div></div>";
    } else {
        $exist_exercise = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'exercise');
            foreach ($post_res as $exist_res) {
                $exist_exercise[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th width='50%' class='text-start'>$langExercices</th>" .
                "<th class='text-start'>$langDescription</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $checked = '';
            if (in_array($entry['id'], $exist_exercise)) {
                $checked = 'checked';
            }
            $ret_string .= "<tr class='$vis'>";
            $ret_string .= "<td class='text-start'><a href='{$urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$entry[id]'>" . q($entry['name']) . "</a></td>";
            $ret_string .= "<td class='text-start'>" . mathfilter($entry['comment'], 12 , "../../courses/mathimg/") . "</td>";
            $ret_string .= "<td class='text-center'><input type='checkbox' $checked name='exercise[]' value='$entry[id]'></td>";
            $ret_string .= "</tr>";
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
