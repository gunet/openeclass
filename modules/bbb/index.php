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
 * @file chat.php
 * @brief Main script for chat module
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'bbb';

require_once '../../include/baseTheme.php';
// For using with the pop-up calendar
require_once 'jscalendar.inc.php';
// For creating bbb urls & params
require_once 'bbb-api.php';

require_once 'include/lib/modalboxhelper.class.php';
ModalBoxHelper::loadModalBox();

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_BBB);
/* * *********************************** */

$nameTools = $langBBB;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<p class='caution'>$langNoGuest</p>";
    draw($tool_content, 2, 'bbb');
}

$head_content = '';

//atsaloux temp function calls
create_meeting();

if ($is_editor) {
    $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&add=1\">$langNewBBBSession</a></li>
          </ul>
        </div>";
    
    if (isset($_GET['add'])) {
        $nameTools = $langNewAssign;
        #$navigation[] = $works_url;
        new_bbb_session();
    }
    else
    {
        bbb_session_details();   
    }

}
else
{
    bbb_session_details();
}

function new_bbb_session() {
    global $tool_content, $m, $langAdd, $course_code;
    global $langNewBBBSessionInfo, $langNewBBBSessionDesc, $langNewBBBSessionStart, $langNewBBBSessionType, $langNewBBBSessionPublic, $langNewBBBSessionPrivate;
    global $desc;
    global $end_cal_Work;
    global $langBack;
    
    $day = date("d");
    $month = date("m");
    $year = date("Y");

    $textarea = rich_text_editor('desc', 4, 20, '');

    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' onsubmit='return checkrequired(this, \"title\");'>
        <fieldset>
        <legend>$langNewBBBSessionInfo</legend>
        <table class='tbl' width='100%'>
        <tr>
          <th>$m[title]:</th>
          <td><input type='text' name='title' size='55' /></td>
        </tr>
        <tr>
          <th>$langNewBBBSessionDesc:</th>
          <td>$textarea</td>
        </tr>
        <tr>
          <th>$langNewBBBSessionStart:</th>
          <td>$end_cal_Work</td>
        </tr>
        <th valign='top'>$langNewBBBSessionType:</th>
            <td><input type='radio' id='user_button' name='group_submissions' value='0' />
            <label for='user_button'>$langNewBBBSessionPublic</label><br />
            <input type='radio' id='group_button' name='group_submissions' value='1' />
            <label for='group_button'>$langNewBBBSessionPrivate</label></td>
        </tr>
        <tr>
          <th>&nbsp;</th>
          <td class='right'><input type='submit' name='new_assign' value='$langAdd' /></td>
        </tr>
        </table>
        </fieldset>
        </form>
        <br />";
    $tool_content .= "<p align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

// Print a box with the details of a bbb session
function bbb_session_details() {
    global $course_id, $tool_content, $m, $is_editor, $langActions, $langNewBBBSessionStart, $langNewBBBSessionType;
    global $langConfirmDelete, $langNewBBBSessionPublic, $langNewBBBSessionPrivate, $langBBBSessionJoin;
    global $course_code;
    global $themeimg;

    $result = db_query("SELECT * 
                        FROM bbb_session
                        WHERE course_id = $course_id
                        ORDER BY start_date");

    if (mysql_num_rows($result)) {
                $tool_content .= "<table class='tbl_alt' width='100%'>
                                  <tr>
                                      <th colspan='2'>$m[title]</th>
                                      <td class='center'>$langNewBBBSessionStart</td>
                                      <td class='center'>$langNewBBBSessionType</td>
                                      <th class='center' colspan='3'>$langActions</th>
                                  </tr>";
        $k = 0;
        while ($row = mysql_fetch_array($result)) {
                $id = $row['id'];
                $title = $row['title'];
                $start_date = $row['start_date'];
                $row['public'] == '1' ? $type = $langNewBBBSessionPublic: $type = $langNewBBBSessionPrivate;

                if ($is_editor) {
                    $tool_content .= "
                        <th colspan='2'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;edit=$id'>$title</a></th>
                        <td align='center'>$start_date</tdh>
                        <td align='center'>$type</td>
                        <td class='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]&amp;choice=edit'>
                        <img src='$themeimg/edit.png' alt='$m[edit]' />
                        </a> <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]&amp;choice=do_delete' onClick='return confirmation(\"" . $langConfirmDelete . "\");'>
                        <img src='$themeimg/delete.png' alt='$m[delete]' /></a>";
                        if ($row['active']=='1') {
                            $deactivate_temp = htmlspecialchars($m['deactivate']);
                            $activate_temp = htmlspecialchars($m['activate']);
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=disable&amp;id=$row[id]'><img src='$themeimg/visible.png' title='$deactivate_temp' /></a>";
                        } else {
                            $activate_temp = htmlspecialchars($m['activate']);
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;id=$row[id]'><img src='$themeimg/invisible.png' title='$activate_temp' /></a>";
                        }
                        $tool_content .= "<a href='".bbb_join_moderator($_SESSION['surname'],$_SESSION['givenname'])."' target='_blank'><img src='$themeimg/bbb.png' title='$langBBBSessionJoin' /></a>";
                } else
                {
                    $tool_content .= "
                    <th colspan='2'>$title</th>
                    <td align='center'>$start_date</tdh>
                    <td align='center'>$type</td>
                    <td class='center'>";
                    if ($row['active']=='1') {
                        $tool_content .= "<a href='".bbb_join_user($_SESSION['surname'],$_SESSION['givenname'])."' target='_blank'>$langBBBSessionJoin</a>";
                    } else {
                        $tool_content .= $langBBBSessionJoin;
                    }
                }
            }
        }

    $tool_content .= "
        </table>
        </fieldset>";
}

function create_meeting(){
    $query = db_query("SELECT * 
                        FROM bbb_servers
                        WHERE id=1");

    if (mysql_num_rows($query)) {
        while ($row = mysql_fetch_array($query)) {
            $salt = $row['server_key'];
            $bbb_url = $row['api_url'];
        }
    }
    
    $bbb = new BigBlueButton($salt,$bbb_url);
    
    $creationParams = array(
        'meetingId' => '1234', // REQUIRED
        'meetingName' => 'Test Meeting Name', // REQUIRED
        'attendeePw' => 'ap', // Match this value in getJoinMeetingURL() to join as attendee.
        'moderatorPw' => 'mp', // Match this value in getJoinMeetingURL() to join as moderator.
        'welcomeMsg' => '', // ''= use default. Change to customize.
        'dialNumber' => '', // The main number to call into. Optional.
        'voiceBridge' => '', // PIN to join voice. Optional.
        'webVoice' => '', // Alphanumeric to join voice. Optional.
        'logoutUrl' => '', // Default in bigbluebutton.properties. Optional.
        'maxParticipants' => '-1', // Optional. -1 = unlimitted. Not supported in BBB. [number]
        'record' => 'false', // New. 'true' will tell BBB to record the meeting.
        'duration' => '0', // Default = 0 which means no set duration in minutes. [number]
        //'meta_category' => '', // Use to pass additional info to BBB server. See API docs.
    );

    // Create the meeting and get back a response:
    $itsAllGood = true;
    try {$result = $bbb->createMeetingWithXmlResponseArray($creationParams);}
    catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
    $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        // If it's all good, then we've interfaced with our BBB php api OK:
        if ($result == null) {
        // If we get a null response, then we're not getting any XML back from BBB.
        echo "Failed to get any response. Maybe we can't contact the BBB server.";
        }	
        else {
            // We got an XML response, so let's see what it says:
            //print_r($result);
            if ($result['returncode'] == 'SUCCESS') {
                // Then do stuff ...
                //echo "<p>Meeting succesfullly created.</p>";
            }
            else {
                echo "<p>Meeting creation failed.</p>";
            }
        }
    }
}

function bbb_join_moderator($surname,$name){
    $query = db_query("SELECT * 
                        FROM bbb_servers
                        WHERE id=1");

    if (mysql_num_rows($query)) {
        while ($row = mysql_fetch_array($query)) {
            $salt = $row['server_key'];
            $bbb_url = $row['api_url'];
        }
    }
    
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    /* ___________ JOIN MEETING w/ OPTIONS ______ */
    /* Determine the meeting to join via meetingId and join it.
    */

    $joinParams = array(
        'meetingId' => '1234', // REQUIRED - We have to know which meeting to join.
        'username' => $surname . " " . $name,	// REQUIRED - The user display name that will show in the BBB meeting.
        'password' => 'mp',	// REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '',	// OPTIONAL - string
        'userId' => '',	// OPTIONAL - string
        'webVoiceConf' => ''	// OPTIONAL - string
    );

    // Get the URL to join meeting:
    $itsAllGood = true;
    try {$result = $bbb->getJoinMeetingURL($joinParams);}
        catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        //Output results to see what we're getting:
        //print_r($result);
    }
    
    return $result;
}

function bbb_join_user($surname,$name){
    $query = db_query("SELECT * 
                        FROM bbb_servers
                        WHERE id=1");

    if (mysql_num_rows($query)) {
        while ($row = mysql_fetch_array($query)) {
            $salt = $row['server_key'];
            $bbb_url = $row['api_url'];
        }
    }
    
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    /* ___________ JOIN MEETING w/ OPTIONS ______ */
    /* Determine the meeting to join via meetingId and join it.
    */

    $joinParams = array(
        'meetingId' => '1234', // REQUIRED - We have to know which meeting to join.
        'username' => $surname . " " . $name,	// REQUIRED - The user display name that will show in the BBB meeting.
        'password' => 'mp',	// REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '',	// OPTIONAL - string
        'userId' => '',	// OPTIONAL - string
        'webVoiceConf' => ''	// OPTIONAL - string
    );

    // Get the URL to join meeting:
    $itsAllGood = true;
    try {$result = $bbb->getJoinMeetingURL($joinParams);}
        catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        //Output results to see what we're getting:
        //print_r($result);
    }
    
    return $result;
}
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
