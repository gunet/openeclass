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
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
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

//<th>$langAm</th>
$tool_content .= "
    <table class='table-default'>
    <tr>
      <th>$langSurnameName</th>      
      <th>$langBBB</th>
      <th>$langTotalDuration</th>
    </tr>";

if (isset($meetingid)) {
    $result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM bbb_attendance
                                                WHERE bbb_attendance.meetingid = ?s 
                                            ORDER BY date DESC", $meetingid);
} else {
    $result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM bbb_attendance 
                                                ORDER BY date DESC");
}
if (count($result) > 0) {
    $temp_date = null;
    foreach ($result as $row) {
        if ($row->date != $temp_date) {
            $tool_content .= "<tr><td colspan='4' class='monthLabel list-header'><div align='center'><b>" . claro_format_locale_date($dateFormatLong, strtotime($row->date)) . "</b></div></td></tr>";
            $temp_date = $row->date;
        }
        
        $user_full_name = Database::get()->querySingle("SELECT fullName FROM bbb_log
                                WHERE bbb_log.bbbuserid = ?s", $row->bbbuserid)->fullName;
        $tc_title = get_tc_title($row->meetingid);
        //<td class='center'>" . uid_to_am($row->userid) . "</td>
        $tool_content .= "<tr><td class='bullet'>$user_full_name</td>                            
                            <td class='center'>$tc_title</td>
                            <td class='center'>" . format_time_duration(0 + 60 * $row->totaltime) . "</td>
                            </tr>";
        
    }    
}
$tool_content .= "</table>";    

draw($tool_content, 2);
