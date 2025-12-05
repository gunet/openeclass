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
 * display available polls
 */
function list_session_polls($sid,$cid) {

    global $course_id, $course_code, $urlServer, $tool_content, $id,
            $langPollNone, $langQuestionnaire, $langChoice, $langAddModulesButton, 
            $langSelect, $langDescription;

    $result = Database::get()->queryArray("SELECT * FROM poll WHERE course_id = ?d AND active = 1
                                            AND pid NOT IN (SELECT res_id FROM session_resources
                                                                WHERE session_id = ?d AND type = ?s)", $course_id, $sid, 'poll');
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
                "<th style='width:100px;'>$langChoice</th>" .
                "<th>$langQuestionnaire</th>" .
                "</tr></thead>";
        foreach ($pollinfo as $entry) {
            if (!empty($entry['description'])) {
                $description_text = "  </br>
                                        <div class='panel'>
                                            <div class='panel-group group-section' id='accordion_$entry[id]' role='tablist' aria-multiselectable='true'>
                                                <ul class='list-group list-group-flush mt-2'>
                                                    <li class='list-group-item px-0 bg-transparent'>
                                                        <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#des-$entry[id]' aria-expanded='false'>
                                                            <span class='fa-solid fa-chevron-down'></span>$langDescription
                                                        </a>
                                                        <div id='des-$entry[id]' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion_$entry[id]'>
                                                            <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                                " . $entry['description'] . "
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr>";
            $tool_content .= "<td style='width:100px;'><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='poll[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $tool_content .= "<td><a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=questionnaire&amp;pid=$entry[id]&amp;UseCase=1&amp;session=$_GET[session]&amp;from_session_view=true'>" . q($entry['title']) . "</a>$description_text</td>";
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
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        $res = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sid,1);
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

            // **************  IMPORTANT !!!!!!!!!!  ************* //
            // Insert session's users again.
            // Firstly delete all users from poll that are participated in session.
            Database::get()->query("DELETE FROM poll_to_specific WHERE poll_id = ?d
                                    AND user_id IN (SELECT participants FROM mod_session_users
                                                        WHERE session_id = ?d AND is_accepted = ?d)", $poll_id, $sid, 1);

            if(count($res) > 0){
                 foreach($res as $r){
                    Database::get()->query("INSERT INTO poll_to_specific SET user_id = ?d , poll_id = ?d", $r->participants, $poll_id);
                 }
            }
        }
    }
    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}
