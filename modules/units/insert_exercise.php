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
 */
function list_exercises() {
    global $id, $course_id, $tool_content, $urlServer, $langPassCode,
            $langAddModulesButton, $langChoice, $langNoExercises,
            $langExercices, $course_code;

    $result = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $course_id);
    $quizinfo = [];
    foreach ($result as $row) {
        $quizinfo[] = [
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active,
            'password_lock' => $row->password_lock];
    }
    if (count($quizinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning text-center'>$langNoExercises</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "<th class='text-left'>&nbsp;$langExercices</th>" .
                "</tr>";
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            if ($entry['password_lock']) {
                $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-toggle='tooltip' data-placement='right' data-html='true' data-title='$langPassCode'></span>";
            } else {
                $exclamation_icon = '';
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
            $tool_content .= "<td class='text-left'><a href='${urlServer}modules/exercise/admin.php?course=$course_code&amp;exerciseId=$entry[id]&amp;preview=1'>" . q($entry['name']) . "</a>"
                . $exclamation_icon . mathfilter($entry['comment'], 12 , "../../courses/mathimg/") . "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>
                    <div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_exercise' value='$langAddModulesButton'></div>
                </form>";
    }
}
