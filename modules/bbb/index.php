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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'bbb';
require_once '../../include/baseTheme.php';

// For using with the pop-up calendar
require_once 'include/jscalendar/calendar.php';

$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();

require_once 'include/sendMail.inc.php';

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

if ($is_editor) {
    $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&add=1\">$langNewBBBSession</a></li>
          </ul>
        </div>";

    if (isset($_GET['add'])) {
        new_bbb_session();
    }
    elseif(isset($_GET['edit']))
    {
        update_bbb_session($course_id,$_POST['title'], $_POST['desc'], $_POST['$SessionStart'], $_POST['type'] ,$_POST['status'],$_POST['notifyUsers']);
    }
    elseif(isset($_GET['choice']))
    {
        switch($_GET['choice'])
        {
            case 'edit':
                edit_bbb_session($_GET['id']);
                break;
            case 'do_delete':
                $tool_content .= delete_bbb_session($_GET['id']);
                break;
            case 'do_disable':
                $tool_content .= disable_bbb_session($_GET['id']);
                break;
            case 'do_enable':
                $tool_content.= enable_bbb_session($_GET['id']);
                break;
            case 'do_join':
                if(bbb_session_running($_GET['meeting_id'])=='false')
                {
                    create_meeting($_GET['title'],$_GET['meeting_id'],$_GET['mod_pw'],$_GET['att_pw']);
                }
                header('Location: ' . bbb_join_moderator($_GET['meeting_id'],$_GET['mod_pw'],$_GET['att_pw'],$_SESSION['surname'],$_SESSION['givenname']));
                break;
        }
    }elseif(isset($_POST['new_bbb_session']))
    {
        //print_r($_POST['notifyUsers']);
        add_bbb_session($course_id,$_POST['title'], $_POST['desc'], $_POST['start_session'], $_POST['type'] ,$_POST['status'],$_POST['notifyUsers']);
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

// create form for new session scheduling
function new_bbb_session() {
    global $tool_content, $m, $langAdd, $course_code;
    global $langNewBBBSessionInfo, $langNewBBBSessionDesc, $langNewBBBSessionStart, $langNewBBBSessionType, $langNewBBBSessionPublic, $langNewBBBSessionPrivate, $langNewBBBSessionActive, $langNewBBBSessionInActive, $langNewBBBSessionStatus ;
    global $desc;
    global $start_session;
    global $langBack;
    global $langBBBNotifyUsers;

    $start_session = jscal_html('start_session');

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
          <td>$start_session</td>
        </tr>
        <tr>
        <th valign='top'>$langNewBBBSessionType:</th>
            <td><input type='radio' id='user_button' name='type' checked='true' value='0' />
            <label for='user_button'>$langNewBBBSessionPublic</label><br />
            <input type='radio' id='group_button' name='type' value='1' />
            <label for='group_button'>$langNewBBBSessionPrivate</label></td>
        </th>
        </tr>
        <tr>
        <th valign='top'>$langNewBBBSessionStatus:</th>
            <td><input type='radio' id='user_button' name='status' checked='true' value='1' />
            <label for='user_button'>$langNewBBBSessionActive</label><br />
            <input type='radio' id='group_button' name='status' value='0' />
            <label for='group_button'>$langNewBBBSessionInActive</label></td>
        </th>
        </tr>
        <tr>
                <th colspan='2' valign='top'>
                <input type='checkbox' name='notifyUsers' value='1'>$langBBBNotifyUsers
            </td>
        </tr>        
        <tr>
          <th>&nbsp;</th>
          <td class='right'><input type='submit' name='new_bbb_session' value='$langAdd' /></td>
        </tr>
        </table>
        </fieldset>
        </form>
        <br />";
    $tool_content .= "<p align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

// insert scheduled session data into database
function add_bbb_session($course_id,$title,$desc,$start_session,$type,$status,$notifyUsers)
{
    global $tool_content, $langBBBAddSuccessful;
    
    $query = db_query("INSERT INTO bbb_session (course_id,title,description,start_date,public,active,meeting_id,mod_pw,att_pw)"
            . " VALUES ('".q($course_id)."','".q($title)."','".$desc."','$start_session','$type','$status','".generateRandomString()."','".generateRandomString()."','".generateRandomString()."')");
    
    $tool_content .= "<p class='success'>$langBBBAddSuccessful</p>";

    // if we have to notify users for new session
    if($notifyUsers=="1")
    {
        $sql = "SELECT user_id, email FROM course_user, user
                WHERE course_user.course_id = $course_id AND course_user.user_id = user.id";
        $result_users = db_query($sql);
        $recipients = array();

        while ($row = mysql_fetch_array($result_users)) {
            $emailTo = $row["email"];
            $user_id = $row["user_id"];
            // we check if email notification are enabled for each user
            if (get_user_email_notification($user_id)) {
                //and add user to recipients
                array_push($recipients, $emailTo);
            }
        }
        if(count($recipients)>0)
        {
            $emailsubject = "Test subject";
            $emailbody = "Test body";
            $emailcontent = "Test content";
            
            //Notify course users for new bbb session
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, 'UTF-8');
        }
    }
}
//form to edit session data
function edit_bbb_session($session_id) {
    global $tool_content, $m, $langAdd, $course_code;
    global $langNewBBBSessionInfo, $langNewBBBSessionDesc, $langNewBBBSessionStart, $langNewBBBSessionType, $langNewBBBSessionPublic, $langNewBBBSessionPrivate, $langNewBBBSessionStatus, $langNewBBBSessionActive, $langNewBBBSessionInActive;
    global $desc;
    global $start_session;
    global $langBack;
    global $langBBBNotifyUsers;

    $query = db_query(" SELECT * FROM bbb_session WHERE id='$session_id'");
    if (mysql_num_rows($query)) {
         $row = mysql_fetch_array($query);
    }
    $type = ($row['5'] == 1 ? 1 : 0);
    $status = ($row['6'] == 1 ? 1 : 0);

    $start_session = jscal_html('start_session',$row['start_date']);

    $textarea = rich_text_editor('desc', 4, 20, $row['description']);

    $tool_content .= "
                    <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' onsubmit='return checkrequired(this, \"title\");'>
                    <fieldset>
                    <legend>$langNewBBBSessionInfo</legend>
                    <table class='tbl' width='100%'>
                    <tr>
                      <th>$m[title]:</th>
                      <td><input type='text' name='title' size='55' value=".$row['title']."></td>
                    </tr>
                    <tr>
                      <th>$langNewBBBSessionDesc:</th>
                      <td>$textarea</td>
                    </tr>
                    <tr>
                      <th>$langNewBBBSessionStart:</th>
                      <td>$start_session</td>
                    </tr>
                    <tr>
                    <th valign='top'>$langNewBBBSessionType:</th>
                        <td><input type='radio' id='user_button' name='type' value='1' "; 
                        if ($type==1) {
                            $tool_content .= "checked";
                        }
                        $tool_content .= " /><label for='user_button'>$langNewBBBSessionPublic</label><br />
                        <input type='radio' id='group_button' name='type' value='0' ";
                        if ($type==0) {
                            $tool_content .= "checked";
                        }
                        $tool_content .=" /><label for='group_button'>$langNewBBBSessionPrivate</label></td>
                    </td>
                    </tr>
                    <tr>
                    <th valign='top'>$langNewBBBSessionStatus:</th>
                        <td><input type='radio' id='user_button' name='status' value='1' ";
                        if ($status==1) {
                            $tool_content .= "checked";
                        }                        
                        $tool_content .=" /><label for='user_button'>$langNewBBBSessionActive</label><br />
                        <input type='radio' id='group_button' name='status' value='0' ";
                        if ($status==0) {
                            $tool_content .= "checked ";
                        }
                     $tool_content .= " /><label for='group_button'>$langNewBBBSessionInActive</label></td>
                    </td>
                    </tr>
                    <tr>
                    <th colspan='2' valign='top'>
                        <input type='checkbox' name='notifyUsers' value='1'>$langBBBNotifyUsers
                    </td>
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
    global $langNote, $langBBBNoteEnableJoin, $langBBBNoteEnableJoinEditor;

    $result = db_query("SELECT *
                        FROM bbb_session
                        WHERE course_id = $course_id
                        ORDER BY id DESC");

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
                $meeting_id = $row['meeting_id'];
                $att_pw = $row['att_pw'];
                $mod_pw = $row['mod_pw'];

                $tool_content .= "<tr>";

                if ($is_editor) {
                    $tool_content .= "
                        <th colspan='2'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=$meeting_id&amp;title=$title&amp;att_pw=$att_pw&amp;mod_pw=$mod_pw' target='_blank'>$title</a></th>
                        <td align='center'>$start_date</tdh>
                        <td align='center'>$type</td>
                        <td class='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=edit'>
                        <img src='$themeimg/edit.png' alt='$m[edit]' />
                        </a> <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]&amp;choice=do_delete' onClick='return confirmation(\"" . $langConfirmDelete . "\");'>
                        <img src='$themeimg/delete.png' alt='$m[delete]' /></a>";
                        if ($row['active']=='1') {
                            $deactivate_temp = htmlspecialchars($m['deactivate']);
                            $activate_temp = htmlspecialchars($m['activate']);
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_disable&amp;id=$row[id]'><img src='$themeimg/visible.png' title='$deactivate_temp' /></a>";
                        } else {
                            $activate_temp = htmlspecialchars($m['activate']);
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_enable&amp;id=$row[id]'><img src='$themeimg/invisible.png' title='$activate_temp' /></a>";
                        }
                        //$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=$meeting_id&amp;title=$title&amp;att_pw=$att_pw&amp;mod_pw=$mod_pw' target='_blank'><img src='$themeimg/bbb.png' title='$langBBBSessionJoin' /></a>";
                } else
                {
                    $tool_content .= "
                    <th colspan='2'>$title</th>
                    <td align='center'>$start_date</tdh>
                    <td align='center'>$type</td>
                    <td class='center'>";
                    if ($row['active']=='1' && bbb_session_running($meeting_id) == 'true') {
                        $tool_content .= "<a href='".bbb_join_user($meeting_id,$att_pw,$_SESSION['surname'],$_SESSION['givenname'])."' target='_blank'>$langBBBSessionJoin</a>";
                    } else {
                        $tool_content .= "-";
                    }
                }
                $tool_content .= "</tr>";
            }
        }

    $tool_content .= "
        </table>
        </fieldset>";
    
    if (! $is_editor) {
        $tool_content .= "<p class='noteit'><b>$langNote</b>:<br />$langBBBNoteEnableJoin</p>";
    }
}

function disable_bbb_session($id)
{
    global $langBBBUpdateSuccessful;
    $query = db_query("UPDATE bbb_session SET active='0' WHERE id=$id");
    return bbb_session_details() . "<p class='success'>$langBBBUpdateSuccessful</p>";
}

function enable_bbb_session($id)
{
    global $langBBBUpdateSuccessful;
    $query = db_query("UPDATE bbb_session SET active='1' WHERE id=$id");
    return  bbb_session_details() . "<p class='success'>$langBBBUpdateSuccessful</p>";

}

function delete_bbb_session($id)
{
    global $langBBBDeleteSuccessful;
    $query = db_query("DELETE FROM bbb_session WHERE id=$id");
    return  bbb_session_details() . "<p class='success'>$langBBBDeleteSuccessful</p>";

}

function create_meeting($title,$meeting_id,$mod_pw,$att_pw)
{
    //Algorithm to select BBB server GOES HERE ...
    
    global $course_code;
    //we find the bbb server that will serv the session
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
        'meetingId' => $meeting_id, // REQUIRED
        'meetingName' => $title, // REQUIRED
        'attendeePw' => $att_pw, // Match this value in getJoinMeetingURL() to join as attendee.
        'moderatorPw' => $mod_pw, // Match this value in getJoinMeetingURL() to join as moderator.
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
            // echo "Failed to get any response. Maybe we can't contact the BBB server.";
        }
        else {
            // We got an XML response, so let's see what it says:
            //print_r($result);
            if ($result['returncode'] == 'SUCCESS') {
                // Then do stuff ...
                //echo "<p>Meeting succesfullly created.</p>";
            }
            else {
                //echo "<p>Meeting creation failed.</p>";
            }
        }
    }
}

//create join as moderator link
function bbb_join_moderator($meeting_id,$mod_pw,$att_pw,$surname,$name){
    
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

    $joinParams = array(
        'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting to join.
        'username' => $surname . " " . $name,	// REQUIRED - The user display name that will show in the BBB meeting.
        'password' => $mod_pw,	// REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '',	// OPTIONAL - string
        'userId' => '',	// OPTIONAL - string
        'webVoiceConf' => ''	// OPTIONAL - string
    );

    // Get the URL to join meeting:
    $itsAllGood = true;
    try {$result = $bbb->getJoinMeetingURL($joinParams);}
        catch (Exception $e) {
            // echo 'Caught exception: ', $e->getMessage(), "\n";
            $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        //Output results to see what we're getting:
        //print_r($result);
    }

    return $result;
}

// create join as simple user link
function bbb_join_user($meeting_id,$att_pw,$surname,$name){
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

    $joinParams = array(
        'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting to join.
        'username' => $surname . " " . $name,	// REQUIRED - The user display name that will show in the BBB meeting.
        'password' => $att_pw,	// REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '',	// OPTIONAL - string
        'userId' => '',	// OPTIONAL - string
        'webVoiceConf' => ''	// OPTIONAL - string
    );

    // Get the URL to join meeting:
    $itsAllGood = true;
    try {$result = $bbb->getJoinMeetingURL($joinParams);}
        catch (Exception $e) {
            //echo 'Caught exception: ', $e->getMessage(), "\n";
            $itsAllGood = false;
    }

    if ($itsAllGood == true) {
        //Output results to see what we're getting:
        //print_r($result);
    }

    return $result;
}

// Generate random strings. Used to create meeting_id, attendance password and moderator password
function generateRandomString($length = 10) {
    return substr(str_shuffle(implode(array_merge(range(0,9), range('A', 'Z'), range('a', 'z')))), 0, $length);
}

function bbb_session_running($meeting_id)
{
    //echo "SELECT running_at FROM bbb_session WHERE meeting_id = '$meeting_id'";
    $running_server = db_query_get_single_value("SELECT running_at FROM bbb_session WHERE meeting_id = '$meeting_id'");
    if (! isset($running_server)) {
        return false;
    }
    $query = db_query("SELECT *
                        FROM bbb_servers
                        WHERE id=$running_server");

    if (mysql_num_rows($query)) {
        while ($row = mysql_fetch_array($query)) {
            $salt = $row['server_key'];
            $bbb_url = $row['api_url'];
        }
    }
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    // Get the URL to join meeting:
    $itsAllGood = true;
    try {$result = $bbb->isMeetingRunningWithXmlResponseArray($meeting_id);}
    catch (Exception $e) {
        //echo 'Caught exception: ', $e->getMessage(), "\n";
        $itsAllGood = false;
        return $itsAllGood;
    }
    return $result['running'];
}

/* * ***************************************************************************
  Create the HTML for a jscalendar field
 * **************************************************************************** */

function jscal_html($name, $u_date = FALSE) {
    global $jscalendar;
    if (!$u_date) {
        $u_date = strftime('%Y-%m-%d %H:%M', strtotime('now -0 day'));
    }

    $cal = $jscalendar->make_input_field(
            array('showsTime' => true,
        'showOthers' => true,
        'ifFormat' => '%Y-%m-%d %H:%M'), array('style' => '',
        'name' => $name,
        'value' => $u_date));
    return $cal;
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
