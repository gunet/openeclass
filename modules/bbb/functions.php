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


require_once 'bbb-api.php';


/**
 * @brief create form for new session scheduling
 * @global type $tool_content
 * @global type $langAdd
 * @global type $course_code 
 * @global type $langNewBBBSessionDesc
 * @global type $langNewBBBSessionStart
 * @global type $langNewBBBSessionType
 * @global type $langNewBBBSessionPublic
 * @global type $langNewBBBSessionPrivate
 * @global type $langNewBBBSessionActive
 * @global type $langNewBBBSessionInActive
 * @global type $langNewBBBSessionStatus
 * @global type $langBBBSessionAvailable
 * @global type $langBBBMinutesBefore 
 * @global type $start_session
 * @global type $langBBBNotifyUsers
 * @global type $langBBBNotifyExternalUsers 
 */
function new_bbb_session() {
    
    global $course_id, $uid;
    global $tool_content, $langAdd, $course_code;
    global $langNewBBBSessionDesc, $langNewBBBSessionStart, $langNewBBBSessionType;
    global $langNewBBBSessionPublic, $langNewBBBSessionPrivate, $langNewBBBSessionActive, $langNewBBBSessionInActive;
    global $langNewBBBSessionStatus, $langBBBSessionAvailable, $langBBBMinutesBefore;
    global $start_session;
    global $langTitle;
    global $langBBBNotifyUsers,$langBBBNotifyExternalUsers;    
    global $langAllUsers, $langParticipants, $langBBBRecord, $langBBBRecordTrue, $langBBBRecordFalse,$langBBBSessionMaxUsers;
    global $langBBBSessionSuggestedUsers,$langBBBSessionSuggestedUsers2;
    global $langBBBAlertTitle,$langBBBAlertMaxParticipants, $langJQCheckAll, $langJQUncheckAll;
   
    $textarea = rich_text_editor('desc', 4, 20, '');
    $start_date = new DateTime;
    $start_session = $start_date->format('d-m-Y H:i'); 
    $c = Database::get()->querySingle("SELECT COUNT(*) count FROM course_user WHERE course_id=(SELECT id FROM course WHERE code=?s)",$course_code)->count;
    if ($c>20) {
        $c = $c/2;
        
    } // If more than 20 course users, we suggest 50% of them
    $tool_content .= "
        <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' >
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='title' id='title' placeholder='$langTitle' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='desc' class='col-sm-2 control-label'>$langNewBBBSessionDesc:</label>
            <div class='col-sm-10'>
                $textarea
            </div>
        </div>       
        <div class='form-group'>
            <label for='start_session' class='col-sm-2 control-label'>$langNewBBBSessionStart:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='start_session' id='start_session' value='$start_session'>
            </div>
        </div>    
        <div class='form-group'>
            <label for='select-groups' class='col-sm-2 control-label'>$langParticipants:</label>
            <div class='col-sm-10'>
            <select name='groups[]' multiple='multiple' class='form-control' id='select-groups'>";
            // select available course groups (if exist)
            $sql = "SELECT `group`.`id`,`group`.`name` FROM `group` RIGHT JOIN course ON group.course_id=course.id WHERE course.code=?s ORDER BY UPPER(NAME)";
            $res = Database::get()->queryArray($sql,$course_code);            
            foreach ($res as $r) {
                if(isset($r->id)) {
                    $tool_content .= "<option value= '_$r->id'>" . q($r->name) . "</option>";                        
                }
            }
            //select all users from this course except yourself
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                        AND cu.user_id = u.id
                        AND cu.status != ?d
                        AND u.id != ?d
                        ORDER BY UPPER(u.surname), UPPER(u.givenname)";                
            $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);                       
            foreach ($res as $r) {
                if(isset($r->user_id)) {
                    $tool_content .= "<option value=" . $r->user_id . ">" . q($r->name) . " (".q($r->username).")</option>";                        
                }
            }
                
        $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
            </div>
        </div>
        <div class='form-group'>
            <label for='group_button' class='col-sm-2 control-label'>$langBBBRecord:</label>
            <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='user_button' name='record' value='1'";
                        if(Database::get()->querySingle("SELECT count(*) count FROM bbb_servers WHERE enabled='true' AND enable_recordings='true'")->count == 0)
                        {
                            $tool_content .=" disabled";
                        }
                        $tool_content .=">
                        $langBBBRecordTrue
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='group_button' name='record' value='0' checked>
                       $langBBBRecordFalse
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='public_button' class='col-sm-2 control-label'>$langNewBBBSessionType:</label>
            <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='public_button' name='type' value='1' checked>
                        $langNewBBBSessionPublic
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='private_button' name='type' value='0'>
                       $langNewBBBSessionPrivate
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='active_button' class='col-sm-2 control-label'>$langNewBBBSessionStatus:</label>
            <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='active_button' name='status' value='1' checked>
                        $langNewBBBSessionActive
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='inactive_button' name='status' value='0'>
                       $langNewBBBSessionInActive
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='minutes_before' class='col-sm-2 control-label'>$langBBBSessionAvailable:</label>
            <div class='col-sm-10'>
                    <select class='form-control' name='minutes_before' id='minutes_before'>
                        <option value='15'' selected='selected'>15</option>
                        <option value='30'>30</option>
                        <option value='10'>10</option>
                    </select> $langBBBMinutesBefore
            </div>
        </div>
        <div class='form-group'>
            <label for='sessionUsers' class='col-sm-2 control-label'>$langBBBSessionMaxUsers:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='sessionUsers' id='sessionUsers' value='$c'> $langBBBSessionSuggestedUsers:
                <strong>$c</strong> ($langBBBSessionSuggestedUsers2)
            </div>
        </div>
        <div class='form-group'>
            <label for='tags_1' class='col-sm-2 control-label'>$langBBBNotifyExternalUsers:</label>
            <div class='col-sm-10'>
                <input class='form-control' id='tags_1' name='external_users' type='text' class='tags' value=''>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='notifyUsers' value='1'>$langBBBNotifyUsers
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='new_bbb_session' value='$langAdd'>                
            </div>
        </div>                      
        </fieldset>
        </form></div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("sessionForm");
            chkValidator.addValidation("title","req","'.$langBBBAlertTitle.'");
            chkValidator.addValidation("sessionUsers","req","'.$langBBBAlertMaxParticipants.'");
            chkValidator.addValidation("sessionUsers","numeric","'.$langBBBAlertMaxParticipants.'");
        //]]></script>';
}

/**
 * @brief insert scheduled session data into database 
 * @global type $langBBBAddSuccessful
 * @global type $langBBBScheduledSession
 * @global type $langBBBScheduleSessionInfo
 * @global type $langBBBScheduleSessionInfoJoin
 * @param type $course_id
 * @param type $title
 * @param type $desc
 * @param type $start_session
 * @param type $type
 * @param type $status
 * @param type $notifyUsers
 * @param type $minutes_before
 * @param type $external_users
 */
function add_bbb_session($course_id,$title,$desc,$start_session,$type,$status,$notifyUsers,$minutes_before,$external_users,$record,$sessionUsers)
{    
    global $langBBBScheduledSession, $langBBBScheduleSessionInfo , $langBBBScheduleSessionInfo2, $langBBBScheduleSessionInfoJoin;

    // Groups of participants per session
    $r_group = "";     
    
    if (isset($_POST['groups'])) {
        foreach ($_POST['groups'] as $group) {
            if (preg_match('/^_/', $group)) { // find group users
                $g_id = intval((substr($group, 1, strlen($group))));
                $q = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = $g_id");
                if ($q) {                    
                    foreach ($q as $row) {                        
                        $r_group .= "'$row->user_id'" .',';
                    }
                }
            } else {
                $r_group .= "'$group'" .',';
            }
        }
    }
    $r_group = rtrim($r_group,',');   

    // Enable recording or not
    switch($record)
    {
        case 0:
            $record="false";
            break;
        case 1:
            $record="true";
            break;
    }
    
    $q = Database::get()->query("INSERT INTO bbb_session (course_id,title,description,start_date,public,active,running_at,meeting_id,mod_pw,att_pw,unlock_interval,external_users,participants,record,sessionUsers)"
        . " VALUES (?d,?s,?s,?t,?s,?s,'1',?s,?s,?s,?d,?s,?s,?s,?d)", $course_id, $title, $desc, $start_session, $type, $status, generateRandomString(), generateRandomString(), generateRandomString(), $minutes_before, $external_users,$r_group,$record,$sessionUsers);
    
    // if we have to notify users for new session
    if($notifyUsers == "1")
    {
        $recipients = array();
        
        $result = Database::get()->queryArray("SELECT course_user.user_id, user.email 
                                                    FROM course_user, user
                                                   WHERE course_id = ?d AND user.id IN ($r_group) AND 
                                                         course_user.user_id = user.id", $course_id);
                
        foreach($result as $row) {
            $emailTo = $row->email;
            $user_id = $row->user_id;
            // we check if email notification are enabled for each user
            if (get_user_email_notification($user_id)) {
                //and add user to recipients
                array_push($recipients, $emailTo);
            }
        }
        if(count($recipients) > 0)
        {            
            $emailsubject = $langBBBScheduledSession;
            $emailbody = $langBBBScheduleSessionInfo . " \"" . q($title) . "\" " . $langBBBScheduleSessionInfo2 . " " . q($start_session);
            $emailcontent = $emailbody;            
            //Notify course users for new bbb session
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, 'UTF-8');
        }
        //Notify external users for new bbb session
        if (isset($external_users)) {
            $recipients = explode(',', $external_users);
            $q = Database::get()->querySingle("SELECT meeting_id, att_pw FROM bbb_session WHERE id = ?d", $q->lastInsertID);
            foreach ($recipients as $row) {
                //$bbblink = bbb_join_user($q->meeting_id, $q->att_pw, $row, '');
                $bbblink = get_config('base_url')."modules/bbb/ext.php?meeting_id=".$q->meeting_id."&username=".$row;
                $emailsubject = $langBBBScheduledSession;
                $emailbody = $langBBBScheduleSessionInfo . " \"" . q($title) . "\" " . $langBBBScheduleSessionInfo2 . " " . q($start_session) . "<br><br>$langBBBScheduleSessionInfoJoin:<br> $bbblink";
                $emailcontent = $emailbody;
                send_mail_multipart('', '', '', $row, $emailsubject, $emailbody, $emailcontent, 'UTF-8');
            }            
        }
    }
    
    $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
 
    $order = $orderMax + 1;
            
    Database::get()->querySingle("INSERT INTO announcement (content,title,`date`,course_id,`order`,visible) VALUES ('".$langBBBScheduleSessionInfo . " \"" . $title . "\" " . $langBBBScheduleSessionInfo2 . " " . $start_session."',
                                             '$langBBBScheduledSession',NOW(),
                                             '$course_id','$order','1')");
}

/**
 * @brief update scheduled session data into database
 * @global type $tool_content
 * @global type $langBBBAddSuccessful
 * @global type $course_id
 * @global type $langBBBScheduleSessionInfo
 * @global type $langBBBScheduledSession
 * @global type $langBBBScheduleSessionInfoJoin
 * @param type $session_id
 * @param type $course_id
 * @param type $title
 * @param type $desc
 * @param type $start_session
 * @param type $type
 * @param type $status
 * @param type $notifyUsers
 * @param type $minutes_before
 * @param type $external_users
 */
function update_bbb_session($session_id,$title,$desc,$start_session,$type,$status,$notifyUsers,$minutes_before,$external_users,$record,$sessionUsers)
{
    global $course_id;
    global $langBBBScheduleSessionInfo , $langBBBScheduledSession, $langBBBScheduleSessionInfo2, $langBBBScheduleSessionInfoJoin;

    // Groups of participants per session
    $r_group = "";
    if (isset($_POST['groups'])) {
        foreach ($_POST['groups'] as $group) {
           $r_group .= "'$group'" .',';
        }
    }
            
    $r_group = rtrim($r_group,',');

    // Enable recording or not
    switch($record)
    {
        case 0:
            $record="false";
            break;
        case 1:
            $record="true";
            break;
    }
    Database::get()->querySingle("UPDATE bbb_session SET title=?s,description=?s,"
            . "start_date=?t,public=?s,active=?s,unlock_interval=?d,external_users=?s,participants=?s,record=?s,sessionUsers=?d WHERE id=?d",$title, $desc, $start_session, $type, $status, $minutes_before, $external_users, $r_group, $record, $sessionUsers, $session_id);
    
    // if we have to notify users for new session
    if($notifyUsers=="1")
    {
        $recipients = array();
                
        $result = Database::get()->queryArray("SELECT course_user.user_id, user.email 
                                                    FROM course_user, user
                                                   WHERE course_id = ?d AND user.id IN ($r_group) AND 
                                                         course_user.user_id = user.id", $course_id);
        

        foreach($result as $row) {
            $emailTo = $row->email;
            $user_id = $row->user_id;
            // we check if email notification are enabled for each user
            if (get_user_email_notification($user_id)) {
                //and add user to recipients
                array_push($recipients, $emailTo);
            }
        }
        if(count($recipients)>0)
        {
            $emailsubject = $langBBBScheduledSession;
            $emailbody = $langBBBScheduleSessionInfo . " \"" . q($title) . "\" " . $langBBBScheduleSessionInfo2 . " " . q($start_session) . "";
            $emailcontent = $emailbody;
            //Notify course users for new bbb session
            send_mail_multipart('', '', '', $recipients, $emailsubject, $emailbody, $emailcontent, 'UTF-8');
        }
        
        //Notify external users for new bbb session
        if (isset($external_users)) {
            $recipients = explode(',', $external_users);
            $q = Database::get()->querySingle("SELECT meeting_id, att_pw FROM bbb_session WHERE id = ?d", $_GET['id']);                        
            foreach ($recipients as $row) {
                //$bbblink = bbb_join_user($q->meeting_id, $q->att_pw, $row, '');                              
                $bbblink = get_config('base_url')."modules/bbb/ext.php?meeting_id=".$q->meeting_id."&username=".$row;
                $emailsubject = $langBBBScheduledSession;
                $emailbody = $langBBBScheduleSessionInfo . " \"" . q($title) . "\" " . $langBBBScheduleSessionInfo2 . " " . q($start_session) . "<br><br>$langBBBScheduleSessionInfoJoin:<br> $bbblink";
                $emailcontent = $emailbody;
                send_mail_multipart('', '', '', $row, $emailsubject, $emailbody, $emailcontent, 'UTF-8');
            }            
        }               
    }
    
    
    
    $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
    $order = $orderMax + 1;
            
    Database::get()->querySingle("INSERT INTO announcement (content,title,`date`,course_id,`order`,visible) VALUES ('".$langBBBScheduleSessionInfo . " \"" . $title . "\" " . $langBBBScheduleSessionInfo2 . " " . $start_session."',
                                             '$langBBBScheduledSession',NOW(),
                                             '$course_id','$order','1')");

}

/**
 * @brief form to edit session data
 * @global type $tool_content 
 * @global type $langModify
 * @global type $course_code
 * @global type $langNewBBBSessionDesc
 * @global type $langNewBBBSessionStart
 * @global type $langNewBBBSessionType
 * @global type $langNewBBBSessionPublic
 * @global type $langNewBBBSessionPrivate
 * @global type $langNewBBBSessionStatus
 * @global type $langNewBBBSessionActive
 * @global type $langNewBBBSessionInActive
 * @global type $langBBBSessionAvailable
 * @global type $langBBBMinutesBefore 
 * @global type $langBBBNotifyUsers
 * @global type $langBBBNotifyExternalUsers
 * @param type $session_id
 */
function edit_bbb_session($session_id) {
    global $tool_content, $langModify, $course_code, $course_id, $uid;
    global $langNewBBBSessionDesc, $langNewBBBSessionStart;
    global $langNewBBBSessionType, $langNewBBBSessionPublic, $langNewBBBSessionPrivate;
    global $langNewBBBSessionStatus, $langNewBBBSessionActive, $langNewBBBSessionInActive,$langBBBSessionAvailable,$langBBBMinutesBefore;       
    global $langTitle;
    global $langBBBNotifyUsers,$langBBBNotifyExternalUsers;
    global $langAllUsers,$langParticipants,$langBBBRecord,$langBBBRecordTrue,$langBBBRecordFalse,$langBBBSessionMaxUsers;
    global $langBBBSessionSuggestedUsers,$langBBBSessionSuggestedUsers2;
    global $langBBBAlertTitle, $langBBBAlertMaxParticipants, $langJQCheckAll, $langJQUncheckAll;

    
    $row = Database::get()->querySingle("SELECT * FROM bbb_session WHERE id = ?d ", $session_id);
    
    $type = ($row->public == 1 ? 1 : 0);
    $status = ($row->active == 1 ? 1 : 0);
    $record = ($row->record == "true" ? 1 : 0);
    #print_r($row);
    $r_group = explode(",",$row->participants);
    
    $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date);
    $start = $startDate_obj->format('d-m-Y H:i');    
    $textarea = rich_text_editor('desc', 4, 20, $row->description);
    $c = Database::get()->querySingle("SELECT COUNT(*) count FROM course_user WHERE course_id=(SELECT id FROM course WHERE code=?s)",$course_code)->count;
    if ($c>20) {
        $c = $c/2;
        
    } // If more than 20 course users, we suggest 50% of them
    $tool_content .= "
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?id=$session_id' method='post'>
                    <fieldset>
                    <div class='form-group'>
                        <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='title' id='title' value='".q($row->title)."'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='desc' class='col-sm-2 control-label'>$langNewBBBSessionDesc:</label>
                        <div class='col-sm-10'>
                            $textarea
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='start_session' class='col-sm-2 control-label'>$langNewBBBSessionStart:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='start_session' id='start_session' value='".q($start)."'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='select-groups' class='col-sm-2 control-label'>$langParticipants:</label>
                        <div class='col-sm-10'>
                                    <select name='groups[]' multiple='multiple' class='form-control' id='select-groups'>";
                        //select all users from this course except yourself
                        $sql = "SELECT `group`.`id`,`group`.`name` FROM `group` RIGHT JOIN course ON group.course_id=course.id WHERE course.code=?s ORDER BY UPPER(NAME)";
                        $res = Database::get()->queryArray($sql, $course_code);                        
                        foreach ($res as $r) {
                            if(isset($r->id)) {
                                $tool_content .= "<option value= '_$r->id'";
                                if(in_array(("'_".$r->id."'"),$r_group))
                                {
                                    $tool_content .=" selected";
                                }
                                $tool_content .=">" . q($r->name) . "</option>";                        
                            }
                        }
                        //select all users from this course except yourself
                        $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                                    FROM user u, course_user cu
                                                WHERE cu.course_id = ?d
                                    AND cu.user_id = u.id
                                    AND cu.status != ?d
                                    AND u.id != ?d
                                    ORDER BY UPPER(u.surname), UPPER(u.givenname)";                
                        $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);                       
                        foreach ($res as $r) {
                            if(isset($r->user_id)) {
                                $tool_content .= "<option value=" . $r->user_id . "";
                                if(in_array(("'".$r->user_id."'"),$r_group))
                                {
                                    $tool_content .=" selected";
                                }
                                $tool_content .=">" . q($r->name) . " (".q($r->username).")</option>";                        
                            }
                        }
                        
                    $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='group_button' class='col-sm-2 control-label'>$langBBBRecord:</label>
                        <div class='col-sm-10'>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='user_button' name='record' value='1' ".(($record==1) ? "checked" : "");
                                    if(Database::get()->querySingle("SELECT count(*) count FROM bbb_servers WHERE enabled='true' AND enable_recordings='true'")->count == 0)
                                    {
                                        $tool_content .=" disabled";
                                    }
                                    $tool_content.=">
                                    $langBBBRecordTrue
                                  </label>
                                </div>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='group_button' name='record' value='0' ".(($record==0) ? "checked" : "").">
                                   $langBBBRecordFalse
                                  </label>
                                </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='public_button' class='col-sm-2 control-label'>$langNewBBBSessionType:</label>
                        <div class='col-sm-10'>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='public_button' name='type' value='1' ".(($type==1) ? "checked" : "").">
                                    $langNewBBBSessionPublic
                                  </label>
                                </div>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='private_button' name='type' value='0' ".(($type==0) ? "checked" : "").">
                                   $langNewBBBSessionPrivate
                                  </label>
                                </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='active_button' class='col-sm-2 control-label'>$langNewBBBSessionStatus:</label>
                        <div class='col-sm-10'>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='active_button' name='status' value='1' ".(($status==1) ? "checked" : "").">
                                    $langNewBBBSessionActive
                                  </label>
                                </div>
                                <div class='radio'>
                                  <label>
                                    <input type='radio' id='inactive_button' name='status' value='0' ".(($status==0) ? "checked" : "").">
                                   $langNewBBBSessionInActive
                                  </label>
                                </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='minutes_before' class='col-sm-2 control-label'>$langBBBSessionAvailable:</label>
                        <div class='col-sm-10'>
                                <select class='form-control' name='minutes_before' id='minutes_before'>
                                    <option value='15' ".(($row->unlock_interval=='15') ? "selected" : "").">15</option>
                                    <option value='30' ".(($row->unlock_interval=='30') ? "selected" : "").">30</option>
                                    <option value='10' ".(($row->unlock_interval=='10') ? "selected" : "").">10</option>
                                </select> $langBBBMinutesBefore
                        </div>
                    </div>                    
                    <div class='form-group'>
                        <label for='sessionUsers' class='col-sm-2 control-label'>$langBBBSessionMaxUsers:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='sessionUsers' id='sessionUsers' size='5' value='".$row->sessionUsers."'> $langBBBSessionSuggestedUsers:
                            <strong>$c</strong> ($langBBBSessionSuggestedUsers2)
                        </div>
                    </div>                    
                    <div class='form-group'>
                        <label for='tags_1' class='col-sm-2 control-label'>$langBBBNotifyExternalUsers:</label>
                        <div class='col-sm-10'>
                            <input class='form-control tags' id='tags_1' name='external_users' type='text' value='".trim($row->external_users)."'>
                        </div>
                    </div>                    
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                                 <div class='checkbox'>
                                  <label>
                                    <input type='checkbox' name='notifyUsers' value='1'>$langBBBNotifyUsers
                                  </label>
                                </div>
                        </div>
                    </div>                    
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='update_bbb_session' value='$langModify'>                            
                        </div>
                    </div>
                    </fieldset>
                    </form></div>";
                $tool_content .='<script language="javaScript" type="text/javascript">
                    //<![CDATA[
                    var chkValidator  = new Validator("sessionForm");
                    chkValidator.addValidation("title","req","'.$langBBBAlertTitle.'");
                    chkValidator.addValidation("sessionUsers","req","'.$langBBBAlertMaxParticipants.'");
                    chkValidator.addValidation("sessionUsers","numeric","'.$langBBBAlertMaxParticipants.'");
                    //]]></script>';
        }

/**
 * @brief Print a box with the details of a bbb session
 * @global type $course_id
 * @global type $tool_content 
 * @global type $is_editor
 * @global type $langNewBBBSessionStart
 * @global type $langNewBBBSessionType
 * @global type $langConfirmDelete
 * @global type $langNewBBBSessionPublic
 * @global type $langNewBBBSessionPrivate
 * @global type $langBBBSessionJoin
 * @global type $langNewBBBSessionDesc
 * @global type $course_code
 * @global type $langNote
 * @global type $langBBBNoteEnableJoin
 * @global type $langTitle
 * @global type $langActivate
 * @global type $langDeactivate
 * @global type $langModify
 * @global type $langDelete
 */        
function bbb_session_details() {
    global $course_id, $tool_content, $is_editor, $langNewBBBSessionStart, $langNewBBBSessionType;
    global $langConfirmDelete, $langNewBBBSessionPublic, $langNewBBBSessionPrivate, $langBBBSessionJoin, $langNewBBBSessionDesc;
    global $course_code;   
    global $langNote, $langBBBNoteEnableJoin, $langTitle,$langActivate, $langDeactivate, $langModify, $langDelete, $langNoBBBSesssions;
    global $langBBBNotServerAvailableStudent, $langBBBNotServerAvailableTeacher;
    global $langBBBImportRecordings;
        
    $myGroups = Database::get()->queryArray("SELECT group_id FROM group_members WHERE user_id=?d", $_SESSION['uid']);

    $result = Database::get()->queryArray("SELECT * FROM bbb_session WHERE course_id = ?s ORDER BY id DESC", $course_id);

    if (get_total_bbb_servers() == '0')
        {
            if ($is_editor) {
                $tool_content .= "<p class='alert alert-danger'><b>$langNote</b>:<br />$langBBBNotServerAvailableTeacher</p>";                
            } else {
                $tool_content .= "<p class='alert alert-danger'><b>$langNote</b>:<br />$langBBBNotServerAvailableStudent</p>";                
            }
    }elseif (($result)) {
        if (!$is_editor) {
            $tool_content .= "<div class='alert alert-info'><label>$langNote</label>:<br>$langBBBNoteEnableJoin</div>";
        }    
        $tool_content .= "<div class='row'>
                            <div class='col-md-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <tr>
                                            <th class='text-center' style='width:25%'>$langTitle</th>
                                            <th class='text-center'>$langNewBBBSessionDesc</th>
                                            <th class='text-center'>$langNewBBBSessionStart</th>
                                            <th class='text-center'>$langNewBBBSessionType</th>
                                            <th class='text-center'>".icon('fa-gears')."</th>
                                        </tr>";

        foreach ($result as $row) {    
                // Get participants groups
                $r_group = explode(",",$row->participants);
                
                $id = $row->id;
                $title = $row->title;
                $start_date = $row->start_date;
                $row->public == '1' ? $type = $langNewBBBSessionPublic: $type = $langNewBBBSessionPrivate;
                $meeting_id = $row->meeting_id;
                $att_pw = $row->att_pw;
                $mod_pw = $row->mod_pw;
                $record = $row->record;
                (isset($row->description)? $desc = $row->description : $desc="");
                $tool_content .= "<tr ".($is_editor && !$row->active ? "class='not_visible'" : "").">";

                if ($is_editor) {
                    // If there no available bbb servers, disable join link. Otherwise, enable    
                    if(get_total_bbb_servers()=='0' || date_diff_in_minutes(date('Y-m-d H:i:s'),$start_date) > 1440 )
                    {
                        $tool_content .= "
                        <td>".q($title)."</td>";
                    } else {
                        $tool_content .= "
                        <td><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=$meeting_id&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."&amp;mod_pw=".urlencode($mod_pw)."&amp;record=$record' target='_blank'>".q($title)."</a></td>";
                    }
                    $tool_content.="<td class='text-center'>".$desc."</td>
                    <td class='text-center'>".q($start_date)."</td>
                    <td class='text-center'>$type</td>
                    <td class='option-btn-cell'>".
                            action_button(array(
                                array(  'title' => $langModify,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=edit",
                                        'icon' => 'fa-edit'),
                                array(  'title' => $langBBBImportRecordings,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=import_video",
                                        'icon' => "fa-edit"),
                                array(  'title' => $row->active? $langDeactivate : $langActivate,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=do_".
                                                 ($row->active? 'disable' : 'enable'),
                                        'icon' => $row->active ? 'fa-eye': 'fa-eye-slash'),
                                array(  'title' => $langDelete,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=do_delete",
                                        'icon' => 'fa-times',
                                        'class' => 'delete',
                                        'confirm' => $langConfirmDelete)                                
                                ));
                    $tool_content.= "</td>";
                } else {
                    //Allow access to session only if user is in participant group or session is scheduled for everyone
                    $access='false';
                    foreach($myGroups as $mg)
                    {
                        if(in_array("'_".$mg->group_id."'",$r_group)) { 
                            $access='true';                            
                        }
                    }
                    if(in_array("'".$_SESSION['uid']."'",$r_group) || $access == 'true')
                    {
                        $tool_content .= "<td class='text-center'>";
                        // Join url will be active only X minutes before scheduled time and if session is visible for users
                        if ($row->active=='1' && date_diff_in_minutes($start_date,date('Y-m-d H:i:s'))<= $row->unlock_interval && date_diff_in_minutes(date('Y-m-d H:i:s'),$start_date) < 1440 && get_total_bbb_servers()<>'0' )
                        {   
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;title=".urlencode($title)."&amp;meeting_id=$meeting_id&amp;att_pw=".urlencode($att_pw)."&amp;record=$record' target='_blank'>".q($title)."</a>";
                        } else {
                            $tool_content .= q($title);
                        }
                        $tool_content .="<td>".$desc."</td>
                            <td class='text-center'>".q($start_date)."</td>
                            <td class='text-center'>$type</td>
                            <td class='text-center'>";
                        // Join url will be active only X minutes before scheduled time and if session is visible for users
                        if ($row->active=='1' && date_diff_in_minutes($start_date,date('Y-m-d H:i:s'))<= $row->unlock_interval && get_total_bbb_servers()<>'0' ) {
                            $tool_content .= icon('fa-sign-in', $langBBBSessionJoin,"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;title=".urlencode($title)."&amp;meeting_id=$meeting_id&amp;att_pw=".urlencode($att_pw)."&amp;record=$record' target='_blank");
                        } else {
                            $tool_content .= "-</td>";
                        }
                    }
                }
                $tool_content .= "</tr>";
            }        
        $tool_content .= "</table></div></div></div>";
        if (get_total_bbb_servers() == '0')
        {
            if ($is_editor) {
                $tool_content .= "<p class='alert alert-danger'><b>$langNote</b>:<br />$langBBBNotServerAvailableTeacher</p>";                
            } else {
                $tool_content .= "<p class='alert alert-danger'><b>$langNote</b>:<br />$langBBBNotServerAvailableStudent</p>";                
            }
        }        
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoBBBSesssions</div>";
    }
}

/**
 * @brief disable bbb session
 * @global type $langBBBUpdateSuccessful
 * @global type $tool_content
 * @param type $id
 * @return type
 */
function disable_bbb_session($id)
{
    global $langBBBUpdateSuccessful, $tool_content;
    
    Database::get()->querySingle("UPDATE bbb_session set active='0' WHERE id=?d",$id);
    $tool_content .= "<div class='alert alert-success'>$langBBBUpdateSuccessful</div>";
    
    return;    
}

/**
 * @brief enable bbb session
 * @global type $langBBBUpdateSuccessful
 * @global type $tool_content
 * @param type $id
 * @return type
 */
function enable_bbb_session($id)
{
    global $langBBBUpdateSuccessful, $tool_content;
    
    Database::get()->querySingle("UPDATE bbb_session SET active='1' WHERE id=?d",$id);
    $tool_content .= "<div class='alert alert-success'>$langBBBUpdateSuccessful</div>";
    
    return;
}


/**
 * @brief delete bbb sessions
 * @global type $langBBBDeleteSuccessful
 * @global type $tool_content
 * @param type $id
 * @return type
 */
function delete_bbb_session($id)
{
    global $langBBBDeleteSuccessful, $tool_content;
    
    Database::get()->querySingle("DELETE FROM bbb_session WHERE id=?d",$id);
    $tool_content .= "<div class='alert alert-success'>$langBBBDeleteSuccessful</div>";
    
    return;
}

/**
 * 
 * @global type $course_id
 * @global type $langBBBCreationRoomError
 * @global type $langBBBConnectionError
 * @param type $title
 * @param type $meeting_id
 * @param type $mod_pw
 * @param type $att_pw
 * @param type $record
 */
function create_meeting($title, $meeting_id, $mod_pw, $att_pw, $record)
{
    global $course_id, $langBBBCreationRoomError, $langBBBConnectionError;

    $run_to = -1;
    $min_users  = 10000000;
    
    //Get all course participants
    $users_to_join = Database::get()->querySingle("SELECT COUNT(*) AS count FROM course_user, user
                                WHERE course_user.course_id = ?d AND course_user.user_id = user.id", $course_id)->count;
    //Algorithm to select BBB server GOES HERE ...
    if($record=='true')
    {
        $query = Database::get()->queryArray("SELECT * FROM bbb_servers WHERE enabled='true' AND enable_recordings=?s ORDER BY weight ASC",$record);
    }else
    {
        $query = Database::get()->queryArray("SELECT * FROM bbb_servers WHERE enabled='true' ORDER BY weight ASC");
    }

    if ($query) {
        foreach ($query as $row) {
            $max_rooms = $row->max_rooms;
            $max_users = $row->max_users;
            // GET connected Participants
            $connected_users = get_connected_users($row->server_key, $row->api_url, $row->ip);
            $active_rooms = get_active_rooms($row->server_key,$row->api_url);
            
            if($connected_users<$min_users)
            {
                $run_to=$row->id;
                $min_users = $connected_users;
            }
            
            //cases
            // max_users = 0 && max_rooms = 0 - UNLIMITED
            // active_rooms < max_rooms && active_users < max_users
            // active_rooms < max_rooms && max_users = 0 (UNLIMITED)
            // active_users < max_users && max_rooms = 0 (UNLIMITED)
            if(($max_rooms == 0 && $max_users == 0) || (($max_users > ($users_to_join + $connected_users)) && $active_rooms < $max_rooms) || ($active_rooms < $max_rooms && $max_users == 0) || (($max_users > ($users_to_join + $connected_users)) && $max_rooms == 0)) // YOU FOUND THE SERVER
            {             
                $run_to = $row->id;
                Database::get()->querySingle("UPDATE bbb_session SET running_at=?s WHERE meeting_id=?s",$row->id, $meeting_id);
                break;
            }            
        }
    }

    if($run_to == -1)
    {
        //WE SHOULD TAKE ACTION IF NO SERVER AVAILABLE DUE TO CAPACITY PROBLEMS
        // If no server available we select server with min connected users       
        $temp_conn = 10000000;        
        $query = Database::get()->queryArray("SELECT * FROM bbb_servers WHERE enabled='true' AND enable_recordings=?s ORDER BY weight ASC",$record);

        if ($query) {
            foreach ($query as $row) {

                // GET connected Participants
                $connected_users = get_connected_users($row->server_key, $row->api_url, $row->ip);

                if($connected_users<$temp_conn)
                {
                    $run_to=$row->id;
                    $temp_conn = $connected_users;
                }
            }
        }        
        Database::get()->querySingle("UPDATE bbb_session SET running_at=?d WHERE meeting_id=?s",$run_to,$meeting_id);
    }
    
    //we find the bbb server that will serv the session
    $res = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id=?d", $run_to);

    if ($res) {
        $salt = $res->server_key;
        $bbb_url = $res->api_url;
    
        $bbb = new BigBlueButton($salt, $bbb_url);

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
            'record' => $record, // New. 'true' will tell BBB to record the meeting.
            'duration' => '0', // Default = 0 which means no set duration in minutes. [number]
            //'meta_category' => '', // Use to pass additional info to BBB server. See API docs.
        );

        // Create the meeting and get back a response:        
        $result = $bbb->createMeetingWithXmlResponseArray($creationParams);        
        // If it's all good, then we've interfaced with our BBB php api OK:
        if ($result == null) {
            // If we get a null response, then we're not getting any XML back from BBB.
            echo "<div class='alert-danger'>$langBBBConnectionError</div>";
            exit;
        } else {
            if ($result['returncode'] == 'SUCCESS') {
                // Then do stuff ...
                //echo "<p>Meeting succesfullly created.</p>";
            }
            else {
                echo "<div class='alert alert-danger'>$langBBBCreationRoomError.</div>";
                exit;
            }
        }        
    }
}


/**
 * @brief create join as moderator link
 * @param type $meeting_id
 * @param type $mod_pw
 * @param type $att_pw
 * @param type $surname
 * @param type $name
 * @return string
 */
function bbb_join_moderator($meeting_id, $mod_pw, $att_pw, $surname, $name){
    
    $res = Database::get()->querySingle("SELECT running_at FROM bbb_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id=?s", $running_server);

    if ($res) {
        $salt = $res->server_key;
        $bbb_url = $res->api_url;

        // Instatiate the BBB class:
        $bbb = new BigBlueButton($salt, $bbb_url);

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
                echo 'Caught exception: ', $e->getMessage(), "\n";
                $itsAllGood = false;
        }
        if ($itsAllGood) {
            return $result;
        } else { 
            return '';
        }
    }
return;
}

/**
 * @brief create join as simple user link
 * @param type $meeting_id
 * @param type $att_pw
 * @param type $surname
 * @param type $name
 * @return type
 */
function bbb_join_user($meeting_id, $att_pw, $surname, $name){
    
    $res = Database::get()->querySingle("SELECT running_at FROM bbb_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id = ?d", $running_server);

    $salt = $res->server_key;
    $bbb_url = $res->api_url;
    
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
    $result = $bbb->getJoinMeetingURL($joinParams);    
    
    return $result;
}


/**
 * @brief Generate random strings. Used to create meeting_id, attendance password and moderator password
 * @param type $length
 * @return type
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(implode(array_merge(range(0,9), range('A', 'Z'), range('a', 'z')))), 0, $length);
}

function bbb_session_running($meeting_id)
{    
    $res = Database::get()->querySingle("SELECT running_at FROM bbb_session WHERE meeting_id = ?s",$meeting_id);

    if (! isset($res->running_at)) {
        return false;
    }
    $running_server = $res->running_at;    

    if(Database::get()->querySingle("SELECT count(*) as count
                                    FROM bbb_servers
                                    WHERE id=?d AND enabled='true'", $running_server)->count == 0)
    {
        //it means that the server is disabled so session must be recreated
        return false;
    }
    
    $res = Database::get()->querySingle("SELECT *
                                    FROM bbb_servers
                                    WHERE id=?d", $running_server);    
    $salt = $res->server_key;
    $bbb_url = $res->api_url;
    
    if(!isset($salt) || !isset($bbb_url)) { return false; }
    
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
    if((string) $result['running'] == 'false')
    {
        return false;
        
    }else return true;
}


/**
 * @brief function to calculate date diff in minutes in order to enable join link
 * @param type $start_date
 * @param type $current_date
 * @return type
 */
function date_diff_in_minutes($start_date,$current_date)
{
    return round((strtotime($start_date) - strtotime($current_date)) /60);
}

/**
 * @brief get connected number of users of BBB server
 * @param type $salt
 * @param type $bbb_url
 * @param type $ip
 * @return int
 */
function get_connected_users($salt, $bbb_url, $ip)
{
    
    $socket = @fsockopen($ip, '80', $errorNo, $errorStr, 3);
    if (!$socket) {
        return 0;
    } else {
        // Instatiate the BBB class:
            $bbb = new BigBlueButton($salt,$bbb_url);

            $meetings = $bbb->getMeetingsWithXmlResponseArray();

            $sum = 0;
            foreach($meetings as $meeting){
                    $mid = $meeting['meetingId'];
                    $pass = $meeting['moderatorPw'];
                    if($mid != null){
                            $info = $bbb->getMeetingInfoWithXmlResponseArray(array('meetingId' => $mid, 'password' => $pass));
                            $sum += $info['participantCount'];
                    }
            }
            return $sum;
        }          

}

/**
 * @brief get number of active rooms
 * @param type $salt
 * @param type $bbb_url
 * @return int
 */
function get_active_rooms($salt,$bbb_url)
{
    $sum = 0;
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    $meetings = $bbb->getMeetingsWithXmlResponseArray();

    foreach($meetings as $meeting){
        $mid = $meeting['meetingId'];
        $pass = $meeting['moderatorPw'];
        if($mid != null){
            $sum += 1;
        }
    }
    
    return $sum;
}

function get_total_bbb_servers()
{
    $total = 0;
    
    $total = Database::get()->querySingle("SELECT COUNT(*) AS count FROM bbb_servers WHERE enabled='true'")->count;
    
    return $total;
}

/**
 * @brief display video recordings in multimedia
 * @global type $langBBBImportRecordingsOK
 * @global type $langBBBImportRecordingsNo
 * @global type $tool_content;
 * @param type $course_id
 * @param type $id
 * @return boolean
 */
function publish_video_recordings($course_id, $id)
{    
    global $langBBBImportRecordingsOK, $langBBBImportRecordingsNo, $tool_content;
    
    $sessions = Database::get()->queryArray("SELECT bbb_session.id,bbb_session.course_id AS course_id,"
            . "bbb_session.title,bbb_session.description,bbb_session.start_date,"
            . "bbb_session.meeting_id,course.prof_names FROM bbb_session LEFT JOIN course ON bbb_session.course_id=course.id WHERE course.code=?s AND bbb_session.id=?d", $course_id, $id);

    $servers = Database::get()->queryArray("SELECT * FROM bbb_servers WHERE enabled='true' ORDER BY id DESC");

    if (($sessions) && ($servers)) {
        foreach ($servers as $server){
            $salt = $server->server_key;
            $bbb_url = $server->api_url;
            
            $bbb = new BigBlueButton($salt,$bbb_url);
            foreach ($sessions as $session) {    
                $recordingParams = array(
                    'meetingId' => $session->meeting_id,
                );
                $recs = file_get_contents($bbb->getRecordingsUrl($recordingParams));
                #print_r($recs);
                $xml = simplexml_load_string($recs);
                # If not set it means that there is no video recording.
                # Skip and search for next one
                if(isset($xml->recordings->recording->playback->format->url))
                {
                    $url = (string) $xml->recordings->recording->playback->format->url;

                    #Check if recording already in videolinks and if not insert
                    $c = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE url = ?s",$url);
                    if($c->cnt == 0) {
                        Database::get()->querySingle("INSERT INTO videolink (course_id,url,title,description,creator,publisher,date,visible,public)"
                        . " VALUES (?s,?s,?s,IFNULL(?s,'-'),?s,?s,?t,?d,?d)",$session->course_id,$url,$session->title,strip_tags($session->description),$session->prof_names,$session->prof_names,$session->start_date,1,1);
                        $tool_content .= "<div class='alert alert-success'>$langBBBImportRecordingsOK</div>";
                    }
                } else {
                        $tool_content .= "<div class='alert alert-warning'>$langBBBImportRecordingsNo</div>";
                }
            }
        }
    }
    return true;
}

/**
 * @brief get number of meeting users
 * @global type $langBBBGetUsersError
 * @global type $langBBBConnectionError
 * @param type $salt
 * @param type $bbb_url
 * @param type $meeting_id
 * @param type $pw
 * @return type
 */
function get_meeting_users($salt,$bbb_url,$meeting_id,$pw)
{
    global $langBBBGetUsersError, $langBBBConnectionError;
            
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    $infoParams = array(
        'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting.
        'password' => $pw,	// REQUIRED - Must match moderator pass for meeting.
    );

    // Now get meeting info and display it:    
    $result = $bbb->getMeetingInfoWithXmlResponseArray($infoParams);
    // If it's all good, then we've interfaced with our BBB php api OK:
    if ($result == null) {
        // If we get a null response, then we're not getting any XML back from BBB.
        echo "<div class='alert-danger'>$langBBBConnectionError</div>";
    }	
    else {
        // We got an XML response, so let's see what it says:                
        if (!isset($result['messageKey'])) {

        } else {
            echo "<div class='alert alert-danger'>$langBBBGetUsersError.</div>";
            exit;                    
        }
    }

    return (int)$result['participantCount'];
}
