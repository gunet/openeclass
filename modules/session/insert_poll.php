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
function list_session_polls($sid,$cid) {

    global $course_id, $course_code, $urlServer, $tool_content, $id,
            $langPollNone, $langQuestionnaire, $langChoice, $langAddModulesButton, $langSelect;

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
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langPollNone</span></div></div>";
    } else {
        $tool_content .= "<form action='resource.php?course=$course_code&session=$sid' method='post'>" .
                "<input type='hidden' name='id' value='$sid'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langQuestionnaire</th>" .
                "</tr></thead>";
        foreach ($pollinfo as $entry) {
            if (!empty($entry['description'])) {
                $description_text = "<div>" .  $entry['description'] . "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr>";
            $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='poll[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $tool_content .= "<td><a href='{$urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a>$description_text</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_poll' value='$langAddModulesButton'></div></form>";
    }

    return $tool_content;
}


/**
 * @brief insert poll in database
 * @param integer $sid
 */
function insert_session_poll($sid) {
    global $course_id, $course_code;
    if(isset($_POST['poll'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $id)->maxorder;
        foreach ($_POST['poll'] as $poll_id) {
            $order++;
            $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $poll_id);
            $q = Database::get()->query("INSERT INTO session_resources SET 
                                            session_id = ?d, 
                                            type = 'poll', 
                                            comments = '',
                                            title = ?s, 
                                            visible = 1, 
                                            `order` = ?d,
                                            `date` = " . DBHelper::timeAfter() . ", 
                                            res_id = ?d", $sid, $poll->name, $order, $poll->pid);
        }
    }
    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}
