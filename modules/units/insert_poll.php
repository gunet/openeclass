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
 * @global type $course_id
 * @global type $course_code
 * @global type $urlServer
 * @global type $tool_content
 * @global type $id
 * @global type $langPollNone
 * @global type $langQuestionnaire
 * @global type $langChoice
 * @global type $langAddModulesButton
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
            'active' => $row->active);
    }
    if (count($pollinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langPollNone</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langQuestionnaire</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($pollinfo as $entry) {            
            $tool_content .= "<tr>";
            $tool_content .= "<td>&nbsp;".icon('fa-question')."&nbsp;&nbsp;<a href='${urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a></td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='poll[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";            
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_poll' value='$langAddModulesButton'></div></form>";
    }    
}