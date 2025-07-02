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

if (!defined('TC_CRON')) {
    $require_current_course = true;
    $require_login = true;
}

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'modules/attendance/functions.php';
load_js('jquery' . JQUERY_VERSION . '.min.js');
load_js('tools.js');

$head_content .= "
<script type='text/javascript'>
    setInterval(function() {
        $.ajax({
            url: 'tc_attendance.php'
        })
    }, 60000)
</script>";

$pageName = $langBBBRecordUserParticipation;

// *** TO DO **** //
$q = Database::get()->queryArray("SELECT server_key, api_url FROM tc_servers WHERE type='bbb' AND enabled = 'true'");
if (empty($q)) {
    exit;
}

$tool_content .= $langWangBBBAttendance;

foreach($q as $server) {
    $salt = $server->server_key;
    $bbb_url = $server->api_url;

    // scan active bbb rooms
    $xml_url = $bbb_url."api/getMeetings?checksum=".sha1("getMeetings".$salt);
    // read the XML format of bbb answer and ...
    $bbb = new BigBlueButton($salt, $bbb_url);
    $xml = $bbb->getMeetingInfo($xml_url);
    if ($xml and $xml->meetings->meeting) {
        // ... for each meeting room scan connected users
        foreach ($xml->meetings->meeting as $row) {
            $meet_id = $row->meetingID;
            $moder_pw = $row->moderatorPW;

            $course = Database::get()->querySingle("SELECT code, course.title, tc_session.title AS mtitle
                FROM course LEFT JOIN tc_session ON course.id = tc_session.course_id
                WHERE tc_session.meeting_id = ?s", $meet_id);
            // Don't list meetings from other APIs
            if (!$course) {
                continue;
            }
            // Write attendees in SQL database
            $joinParams = array(
                'meetingId' => $meet_id, // REQUIRED - We have to know which meeting to join.
                'password' => $moder_pw, // REQUIRED - Must match either attendee or moderator pass for meeting.
            );
            // Get the URL to meeting info:
            $room_xml = $bbb->getMeetingInfoUrl($bbb_url, $salt, $joinParams);
            // Read XML from BBB URL and write to SQL
            xml2sql($room_xml, $bbb);
        }
    }
}
if (defined('TC_CRON')) {
    // update TC cron timestamp
    $ts = date('Y-m-d H:i', time());
    Database::get()->querySingle("INSERT INTO config
        SET `key` = 'tc_cron_ts', value = ?s
        ON DUPLICATE KEY UPDATE value = ?s",
        $ts, $ts);
} else {
    // Display pop-up window
    draw_popup();
}

/**
 * @brief record users attendance in db
 * @param type $room_xml
 */
function xml2sql($room_xml, $bbb) {

    $xml = $bbb->getMeetingInfo($room_xml);
    if (!$xml) {
        return;
    }
    $xml_meet_id = $xml->meetingID;   //meetingID of specific bbb request meeting room

    foreach ($xml->attendees->attendee as $row) {
            $bbbuserid = strval($row->userID);
            $fullName = strval($row->fullName);
            $meetingid = strval($xml_meet_id);
        /****************************************************/
        /*	Write users' presence in detail 	    */
        /*	per minute and 				    */
        /*	per room				    */
        /*	SQL table: tc_log			    */
        /****************************************************/
        Database::get()->query("INSERT INTO tc_log (meetingid, bbbuserid, fullName)
                    VALUES (?s, ?s, ?s)", $meetingid, $bbbuserid, $fullName);

        /****************************************************/
        /*	Write users' presence in summary            */
        /*	totaltime                                   */
        /*	per room                                    */
        /*	SQL table: tc_attendance                   */
        /****************************************************/
        $record = Database::get()->querySingle("SELECT id FROM tc_attendance
                                        WHERE bbbuserid = ?s AND meetingid = ?s AND
                                              TIMESTAMPDIFF(HOUR, `date`, NOW()) < 24",
                                        $bbbuserid, $meetingid);
        if ($record) {
            Database::get()->querySingle("UPDATE tc_attendance
                                            SET totaltime = totaltime + 1,
                                                `date` = NOW()
                                        WHERE id = ?d AND
                                              TIME_FORMAT(now(), '%H:%i') <> TIME_FORMAT(`date`, '%H:%i')",
                                        $record->id);
        } else {
            Database::get()->query('INSERT INTO tc_attendance (`meetingid`, `bbbuserid`, `totaltime`)
                VALUES (?s, ?s, 1)', $meetingid, $bbbuserid);
        }
        $user = Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $bbbuserid);
        if ($user) {
            update_attendance_book($user->id, get_tc_id($meetingid), GRADEBOOK_ACTIVITY_TC);
        }
    }
}
