<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

function list_polls($id = NULL) {
    global $course_id, $course_code, $urlServer, $langPollNone, $langQuestionnaire, $langChoice;

    $ret_string = '';
    $result = Database::get()->queryArray("SELECT * FROM poll WHERE course_id = ?d AND active = 1", $course_id);
    $pollinfo = array();
    foreach ($result as $row) {
        $pollinfo[] = array(
            'id' => $row->pid,
            'title' => $row->name,
            'active' => $row->active);
    }
    if (count($pollinfo) == 0) {
        $ret_string .= "<div class='alert alert-warning'>$langPollNone</div>";
    } else {
        $exist_poll = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'poll');
            foreach ($post_res as $exist_res) {
                $exist_poll[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langQuestionnaire</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($pollinfo as $entry) {
            $checked = '';
            if (in_array($entry['id'], $exist_poll)) {
                $checked = 'checked';
            }

            $ret_string .= "<tr>";
            $ret_string .= "<td>&nbsp;".icon('fa-question')."&nbsp;&nbsp;<a href='{$urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a></td>";
            $ret_string .= "<td class='text-center'><input type='checkbox' $checked name='poll[]' value='$entry[id]'></td>";
            $ret_string .= "</tr>";
        }
        $ret_string .= "</table>";
    }
    return $ret_string;
}
