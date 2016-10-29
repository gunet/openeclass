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
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langAddModulesButton
 * @global type $langNoAssign
 * @global type $langActive
 * @global type $langInactive
 * @global type $langVisibility
 * @global type $course_id
 * @global type $course_code
 */
function list_assignments() {
    global $id, $tool_content, $langTitle, $langChoice, $langGroupWorkDeadline_of_Submission,
    $langAddModulesButton, $langNoAssign, $langActive, $langInactive,
    $langVisibility, $course_id, $course_code;

    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d ORDER BY active, title", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langTitle</th>" .
                "<th width='120'>$langGroupWorkDeadline_of_Submission</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($result as $row) {
            
            if ($row->active) {
                $visible = icon('fa-eye', $langActive);
            } else {
                $visible = icon('fa-eye-slash', $langInactive);
            }            
            $description = empty($row->description) ? '' :
                    "<div>$row->description</div>";            
            $tool_content .= "<tr>" .
                    "<td> " . q($row->title) . "<br><br><div class='text-muted'>$description</div></td>" .
                    "<td class='text-center'>".nice_format($row->submission_date, true)."</td>" .
                    "<td class='text-center'><input name='work[]' value='$row->id' type='checkbox' /></td>" .
                    "</tr>";            
        }
        $tool_content .= 
                "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='submit_work' value='$langAddModulesButton' /></div></th></form>";
    }
}
