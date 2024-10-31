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


/**
 * @brief display list of available assignments (if any)
 */
function list_assignments() {
    global $id, $tool_content, $langWorks, $langChoice, $langGroupWorkDeadline_of_Submission,
    $langAddModulesButton, $langNoAssign, $langPassCode, $course_id, $course_code, $urlServer, $langSelect;

    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d ORDER BY title", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssign</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langWorks</th>" .
                "<th>$langGroupWorkDeadline_of_Submission</th>" .

                "</tr></thead>";
        foreach ($result as $row) {
            if ($row->password_lock) {
                $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$langPassCode'></span>";
            } else {
                $exclamation_icon = '';
            }
            if (!$row->active) {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $description = empty($row->description) ? '' :
                    "<div>" . mathfilter($row->description, 12 , "../../courses/mathimg/"). "</div>";
            $tool_content .= "<tr class='$vis'>" .
                    "<td><label class='label-container' aria-label='$langSelect'><input name='work[]' value='$row->id' type='checkbox' /><span class='checkmark'></span></label></td>" .
                    "<td><a href='{$urlServer}modules/work/index.php?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a>$exclamation_icon $description</td>" .
                    "<td>".format_locale_date(strtotime($row->submission_date), 'short')."</td>" .
                    "</tr>";
        }
        $tool_content .=
                "</table></div>" .
                "<div class='d-flex justify-content-start mt-4'><input class='btn submitAdminBtn' type='submit' name='submit_work' value='$langAddModulesButton' /></div></th></form>";
    }
}
