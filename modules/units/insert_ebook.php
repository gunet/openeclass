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
 *
 * @global type $id
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoEbook
 * @global type $langEBook
 * @global type $course_code
 */
function list_ebooks() {
    global $id, $course_id, $tool_content,
    $langAddModulesButton, $langChoice, $langNoEBook,
    $langEBook, $course_code;

    $result = Database::get()->queryArray("SELECT * FROM ebook WHERE course_id = ?d ORDER BY `order`", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoEBook</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>
                <input type='hidden' name='id' value='$id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "<th class='text-left'>&nbsp;$langEBook</th>" .
                "</tr>";
        foreach ($result as $catrow) {
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-center'>
                            <input type='checkbox' name='ebook[]' value='$catrow->id' />
                            <input type='hidden' name='ebook_title[$catrow->id]'
                               value='" . q($catrow->title) . "'></td>";
            $tool_content .= "<td class='bold'>".icon('fa-book')."&nbsp;&nbsp;" .
                    q($catrow->title) . "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .=
                "</table>
                <div class='text-right'>
                <input class='btn btn-primary' type='submit' name='submit_ebook' value='$langAddModulesButton' /></div></form>";
    }
}
