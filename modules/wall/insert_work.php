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

function list_assignments($id = NULL) {
    global $course_id, $langTitle, $langChoice, $langGroupWorkDeadline_of_Submission, $langAddModulesButton, $langNoAssign;

    $ret_string = '';
    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d ORDER BY title", $course_id);
    if (count($result) == 0) {
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'>$langNoAssign</div></div>";
    } else {
        $exist_assignment = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'assignment');
            foreach ($post_res as $exist_res) {
                $exist_assignment[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-start'>&nbsp;$langTitle</th>" .
                "<th width='120'>$langGroupWorkDeadline_of_Submission</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($result as $row) {
            $checked = '';
            if (in_array($row->id, $exist_assignment)) {
                $checked = 'checked';
            }
            $description = empty($row->description) ? '' :
                    "<div>" . mathfilter($row->description, 12 , "../../courses/mathimg/"). "</div>";
            $ret_string .= "<tr>" .
                    "<td>".q($row->title)."<br><br><div class='text-muted'>$description</div></td>" .
                    "<td class='text-center'>".format_locale_date(strtotime($row->submission_date), 'short')."</td>" .
                    "<td class='text-center'><input type='checkbox' $checked name='assignment[]' value='$row->id' /></td>" .
                    "</tr>";
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}

