<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

function list_exercises($id = NULL) {
    global $course_id, $is_editor, $course_code, $urlServer, $langDescription, $langChoice, $langExercices, $langNoExercises, $langSelect;

    $ret_string = '';
    if ($is_editor) {
        $result = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $course_id);
    } else {
        $result = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d AND active = 1", $course_id);
    }

    $quizinfo = array();
    foreach ($result as $row) {
        $quizinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active);
    }
    if (count($quizinfo) == 0) {
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoExercises</span></div></div>";
    } else {
        $exist_exercise = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'exercise');
            foreach ($post_res as $exist_res) {
                $exist_exercise[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langExercices</th>" .
                "<th>$langDescription</th>" .
                "<th>$langChoice</th>" .
                "</tr></thead>";
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
            $ret_string .= "<td><a href='{$urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$entry[id]'>" . q($entry['name']) . "</a></td>";
            $ret_string .= "<td>" . mathfilter($entry['comment'], 12 , "../../courses/mathimg/") . "</td>";
            $ret_string .= "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $checked name='exercise[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $ret_string .= "</tr>";
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
