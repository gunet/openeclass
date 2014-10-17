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
 * @global type $id
 * @global type $tool_content
 * @global type $langTitle
 * @global type $langChoice
 * @global type $m
 * @global type $langAddModulesButton
 * @global type $langNoAssign
 * @global type $langActive
 * @global type $langInactive
 * @global type $langVisible
 * @global type $course_id
 * @global type $course_code
 * @global type $themeimg
 */
function list_assignments() {
    global $id, $tool_content, $langTitle, $langChoice, $m,
    $langAddModulesButton, $langNoAssign, $langActive, $langInactive,
    $langVisible, $course_id, $course_code, $themeimg;

    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d ORDER BY active, title", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id' />" .
                "<table width='99%' class='tbl_alt'>" .
                "<tr>" .
                "<th class='left'>&nbsp;$langTitle</th>" .
                "<th width='110'>$langVisible</th>" .
                "<th width='120'>$m[deadline]</th>" .
                "<th width='80'>$langChoice</th>" .
                "</tr>";
        $i = 0;        
        foreach ($result as $row) {
            $visible = $row->active ?
                    "<img title='$langActive' src='$themeimg/visible.png' />" :
                    "<img title='$langInactive' src='$themeimg/invisible.png' />";
            $description = empty($row->description) ? '' :
                    "<div>$row->description</div>";
            if ($i % 2) {
                $rowClass = "class='odd'";
            } else {
                $rowClass = "class='even'";
            }
            $tool_content .= "<tr $rowClass>" .
                    "<td>&laquo; " . q($row->title) . " $description</td>" .
                    "<td class='center'>$visible</td>" .
                    "<td class='center'>$row->submission_date</td>" .
                    "<td class='center'><input name='work[]' value='$row->id' type='checkbox' /></td>" .
                    "</tr>";
            $i++;
        }
        $tool_content .= "<tr>" .
                "<th colspan='4'><div align='right'>" .
                "<input type='submit' name='submit_work' value='$langAddModulesButton' />" .
                "</div></th>" .
                "</tr>" .
                "</table>" .
                "</form>";
    }
}
