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
 * @brief display available ebooks
 */
function list_ebooks() {
    global $id, $course_id, $tool_content,
    $langAddModulesButton, $langChoice, $langNoEBook,
    $langEBook, $course_code;

    $result = Database::get()->queryArray("SELECT * FROM ebook WHERE course_id = ?d ORDER BY `order`", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoEBook</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>
                <input type='hidden' name='id' value='$id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "<th class='text-start'>&nbsp;$langEBook</th>" .
                "</tr>";
        foreach ($result as $catrow) {
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-center'>
            <label class='label-container'><input type='checkbox' name='ebook[]' value='$catrow->id' />
                            <span class='checkmark'></span></label>
                            <input type='hidden' name='ebook_title[$catrow->id]'
                               value='" . q($catrow->title) . "'></td>";
            $tool_content .= "<td>" . q($catrow->title) . "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .=
                "</table></div>
                <div class='d-flex justify-content-center mt-3'>
                <input class='btn submitAdminBtn' type='submit' name='submit_ebook' value='$langAddModulesButton' /></div></form>";
    }
}
