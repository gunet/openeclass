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

/**
 * display available polls
 */
function list_polls() {

    global $course_id, $course_code, $urlServer, $tool_content, $id,
            $langPollNone, $langQuestionnaire, $langChoice, $langAddModulesButton;

    $result = Database::get()->queryArray("SELECT * FROM poll WHERE course_id = ?d AND active = 1", $course_id);
    $pollinfo = array();
    foreach ($result as $row) {
        $pollinfo[] = array(
            'id' => $row->pid,
            'title' => $row->name,
            'description' => $row->description,
            'active' => $row->active);
    }
    if (count($pollinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langPollNone</div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "<th class='text-start'>&nbsp;$langQuestionnaire</th>" .
                "</tr>";
        foreach ($pollinfo as $entry) {
            if (!empty($entry['description'])) {
                $description_text = "<div style='margin-top: 10px;'>" .  $entry['description'] . "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='poll[]' value='$entry[id]'></td>";
            $tool_content .= "<td><a href='{$urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a>$description_text</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='text-end mt-3'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_poll' value='$langAddModulesButton'></div></form>";
    }
}
