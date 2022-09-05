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
 * @brief display list of available assignments (if any)
 */
function list_assignments() {
    global $id, $tool_content, $langWorks, $langChoice, $langGroupWorkDeadline_of_Submission,
    $langAddModulesButton, $langNoAssign, $langPassCode, $course_id, $course_code, $urlServer;

    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d ORDER BY title", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langNoAssign</div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "<th class='text-start'>&nbsp;$langWorks</th>" .
                "<th width='150'>$langGroupWorkDeadline_of_Submission</th>" .

                "</tr>";
        foreach ($result as $row) {
            if ($row->password_lock) {
                $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-toggle='tooltip' data-placement='right' data-html='true' data-title='$langPassCode'></span>";
            } else {
                $exclamation_icon = '';
            }
            if (!$row->active) {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $description = empty($row->description) ? '' :
                    "<div class='margin-top: 10px;'>" . mathfilter($row->description, 12 , "../../courses/mathimg/"). "</div>";
            $tool_content .= "<tr class='$vis'>" .
                    "<td class='text-center'><input name='work[]' value='$row->id' type='checkbox' /></td>" .
                    "<td><a href='${urlServer}modules/work/index.php?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a>$exclamation_icon $description</td>" .
                    "<td class='text-center'>".format_locale_date(strtotime($row->submission_date), 'short')."</td>" .
                    "</tr>";
        }
        $tool_content .=
                "</table></div>" .
                "<div class='text-end mt-3'><input class='btn btn-primary' type='submit' name='submit_work' value='$langAddModulesButton' /></div></th></form>";
    }
}
