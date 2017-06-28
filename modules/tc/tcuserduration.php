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
 * @file tcuserduration.php
 * @brief display user duration in teleconference
 */
$require_current_course = true;
$require_login = TRUE;
$require_help = true;
$helpTopic = 'tc';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$toolName = $langUserDuration;

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langBBB);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php?course = $course_code",
        'icon' => 'fa-reply',
        'level' => 'primary-label')
),false);

if (isset($_GET['id'])) {
    $meetingid = get_tc_meeting_id($_GET['id']);
}

$result = [];
if (isset($_GET['u']) and $_GET['u']) { // if we want specific user
    $name = uid_to_name($uid);
    $bbb_name = Database::get()->queryArray("SELECT DISTINCT(bbbuserid) FROM tc_log WHERE fullName = ?s", $name);
    foreach ($bbb_name as $data) {
        $r = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM tc_attendance, tc_session 
                                                WHERE tc_attendance.meetingid = tc_session.meeting_id
                                            AND tc_session.course_id = ?d 
                                            AND tc_attendance.bbbuserid = ?s
                                                ORDER BY date DESC", $course_id, $data->bbbuserid);
        foreach ($r as $data2) {
            array_push($result, (object) array('meetingid' => $data2->meetingid, 
                                               'bbbuserid' => $data2->bbbuserid,
                                               'totaltime' => $data2->totaltime,
                                               'date' => $data2->date));
        }
    }
} else {
    if (isset($meetingid)) { // specific course meeting
        $result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM tc_attendance
                                                    WHERE tc_attendance.meetingid = ?s
                                                ORDER BY date DESC", $meetingid);
    } else { // all course meetings        
            $result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM tc_attendance, tc_session 
                                                        WHERE tc_attendance.meetingid = tc_session.meeting_id
                                                        AND tc_session.course_id = ?d
                                                    ORDER BY date DESC", $course_id);                  
    }
}
// display results
if (count($result) > 0) {
    $tool_content .= "
    <table class='table-default'>
    <tr>
      <th>$langSurnameName</th>      
      <th>$langBBB</th>
      <th>$langTotalDuration</th>
    </tr>";

    $temp_date = null;
    foreach ($result as $row) {
        if ($row->date != $temp_date) {
            $tool_content .= "<tr><td colspan='4' class='monthLabel list-header'>"
                    . "<div align='center'><b>" . claro_format_locale_date($dateFormatLong, strtotime($row->date)) . "</b></div>"
                    . "</td></tr>";
            $temp_date = $row->date;
        }        
        $user_full_name = Database::get()->querySingle("SELECT fullName FROM tc_log
                                WHERE tc_log.bbbuserid = ?s", $row->bbbuserid)->fullName;
        $tc_title = get_tc_title($row->meetingid);        
        $tool_content .= "<tr><td class='bullet'>$user_full_name</td>                            
                            <td class='center'>$tc_title</td>
                            <td class='center'>" . format_time_duration(0 + 60 * $row->totaltime) . "</td>
                            </tr>";
        
    }
    $tool_content .= "</table>";
} else {
    $tool_content .= "<div class='alert alert-warning'>$langBBBNoParticipation</div>";
}

draw($tool_content, 2);
