<?php
/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

global $require_current_course,$require_login,$head_content,$pageName,$tool_content;
global $langBBBRecordUserParticipation,$langWangBBBAttendance;

$require_current_course = TRUE;
$require_login = TRUE;

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
    if ($xml) {
        // ... for each meeting room scan connected users
        foreach ($xml->meetings->meeting as $row) {
            $meet_id = $row->meetingID;
            $moder_pw = $row->moderatorPW;

            $course = Database::get()->querySingle("SELECT code,course.title,tc_session.title as mtitle
            FROM course LEFT JOIN tc_session on course.id = tc_session.course_id
            WHERE tc_session.meeting_id = ?s", $meet_id);
            // don't list meetings from other APIs
            if (!$course) {
                continue;
            }
            /****************************************************/
            /*		write attends in SQL database		*/
            /****************************************************/
            $joinParams = array(
                'meetingId' => $meet_id, // REQUIRED - We have to know which meeting to join.
                'password' => $moder_pw //,	// REQUIRED - Must match either attendee or moderator pass for meeting.
            );
            // Get the URL to meeting info:
            $room_xml = $bbb->getMeetingInfoUrl($bbb_url, $salt, $joinParams);
            /****************************************************/
            /*		XML read from URL and write to SQL	*/
            /****************************************************/
            xml2sql($room_xml, $bbb);
        }
    }
}
// draws pop window
draw_popup();

/**
 * @brief record users attendance in db
 * @param type $room_xml
 */
function xml2sql($room_xml, $bbb) {

    $xml = $bbb->getMeetingInfo($room_xml);
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
        $nextid = Database::get()->querySingle("SELECT MAX(id) as id FROM tc_log")->id;
        $nextid++;

        Database::get()->query("INSERT INTO tc_log (id, meetingid, bbbuserid, fullName)
                    VALUES (?d, ?s, ?s, ?s)", $nextid, $meetingid, $bbbuserid, $fullName);

        /****************************************************/
        /*	Write users' presence in summary            */
        /*	totaltime                                   */
        /*	per room                                    */
        /*	SQL table: tc_attendance                   */
        /****************************************************/
        $currentDate = strtotime(date("Y-m-d H:i:s"));
        $q2 = Database::get()->querySingle("SELECT start_date, end_date FROM tc_session WHERE meeting_id = ?s", strval($xml_meet_id));
        $tcDateBegin = strtotime($q2->start_date);
        $tcDateEnd = strtotime($q2->end_date);

        if($currentDate > $tcDateBegin && ($currentDate < $tcDateEnd || empty($tcDateEnd))) {
            $cnt = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM tc_attendance
                                            WHERE bbbuserid = ?s AND meetingid = ?s AND
                                                  TIMESTAMPDIFF(HOUR, `date`, NOW()) < 24",
                                            $bbbuserid, $meetingid)->cnt;
            if ($cnt > 0) {
                Database::get()->querySingle("UPDATE tc_attendance
                                                SET totaltime = totaltime + 1,
                                                    `date` = NOW()
                                            WHERE bbbuserid = ?s AND meetingid = ?s AND
                                                  TIMESTAMPDIFF(SECOND, `date`, NOW()) >= 60",
                                            $bbbuserid, $meetingid);
            } else {
                $nextid = Database::get()->querySingle("SELECT MAX(id) AS id FROM tc_attendance")->id;
                $nextid++;
                Database::get()->query('INSERT INTO tc_attendance (`id`, `meetingid`, `bbbuserid`, `totaltime`)
                    SELECT COALESCE(MAX(id) + 1, 1), ?s, ?s, 1 FROM tc_attendance', $meetingid, $bbbuserid);
            }
            $u = Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $bbbuserid);
            if (!empty($u->id)) {
                update_attendance_book($u->id, get_tc_id($meetingid),GRADEBOOK_ACTIVITY_TC);
            }
        }
    }
}
