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


require_once 'bbb-api.php';

/**
 * @brief create form for new session scheduling
 */
function bbb_session_form($session_id = 0) {

    global $course_id, $uid;
    global $tool_content, $langAdd, $course_code;
    global $langUnitDescr, $langStart;
    global $langVisible, $langInvisible;
    global $langNewBBBSessionStatus, $langBBBSessionAvailable, $langBBBMinutesBefore;
    global $start_session, $BBBEndDate, $langAnnouncements, $langBBBAnnDisplay;
    global $langTitle, $langBBBNotifyExternalUsersHelpBlock, $langBBBRecordFalse;
    global $langBBBNotifyUsers, $langBBBNotifyExternalUsers, $langBBBSessionMaxUsers;
    global $langAllUsers, $langParticipants, $langBBBRecord, $langBBBRecordTrue;
    global $langBBBSessionSuggestedUsers, $langBBBSessionSuggestedUsers2;
    global $langBBBAlertTitle, $langBBBAlertMaxParticipants, $langJQCheckAll, $langJQUncheckAll;
    global $langEnd, $langBBBEndHelpBlock, $langModify, $langBBBExternalUsers;
    global $langReadMore, $langBBBmuteOnStart, $langBBBlockSettingsDisableCam;
    global $langBBBlockSettingsDisableMic, $langBBBlockSettingsDisablePrivateChat;
    global $langBBBlockSettingsDisablePublicChat, $langBBBlockSettingsDisableNote;
    global $langBBBlockSettingsHideUserList, $langBBBwebcamsOnlyForModerator;
    global $langBBBMaxPartPerRoom, $langBBBHideParticipants;

    $BBBEndDate = Session::has('BBBEndDate') ? Session::get('BBBEndDate') : "";
    $enableEndDate = Session::has('enableEndDate') ? Session::get('enableEndDate') : ($BBBEndDate ? 1 : 0);

    $c = Database::get()->querySingle("SELECT COUNT(*) AS count FROM course_user WHERE course_id=(SELECT id FROM course WHERE code=?s)",$course_code)->count;
    if ($c > 80) {
        $c = floor($c/2); // If more than 80 course users, we suggest 50% of them
    }
    $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
    if (!empty($bbb_max_part_per_room) and ($c > $bbb_max_part_per_room)) {
        $c = $bbb_max_part_per_room;
        $bbb_max_part_per_room_limit = true;
    }
    $found_selected = false;

    if ($session_id > 0 ) { // edit existing BBB session
        $row = Database::get()->querySingle("SELECT * FROM tc_session WHERE id = ?d", $session_id);
        $status = ($row->active == 1 ? 1 : 0);
        $record = ($row->record == "true" ? true : false);
        $running_at = $row->running_at;
        $unlock_interval = $row->unlock_interval;
        $r_group = explode(",",$row->participants);
        $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date);
        $start_session = q($start_date->format('d-m-Y H:i'));
        $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->end_date);
        if (isset($row->end_date)) {
            $BBBEndDate = $end_date->format('d-m-Y H:i');
        } else {
            $BBBEndDate = NULL;
        }
        $enableEndDate = Session::has('BBBEndDate') ? Session::get('BBBEndDate') : ($BBBEndDate ? 1 : 0);

        $textarea = rich_text_editor('desc', 4, 20, $row->description);
        $value_title = q($row->title);
        $value_session_users = $row->sessionUsers;
        $data_external_users = trim($row->external_users);
        if ($data_external_users) {
            $init_external_users = 'data: ' . json_encode(array_map(function ($item) {
                    $item = trim($item);
                    return array('id' => $item, 'text' => $item, 'selected' => true);
            }, explode(',', $data_external_users))) . ',';
        } else {
            $init_external_users = '';
        }
        if (!empty($row->options)) {
            $options = unserialize($row->options);
            $options_show = "show";
        } else {
            $options = NULL;
            $options_show = "";
        }
        $checked_muteOnStart = isset($options['muteOnStart']) ? 'checked' : '';
        $checked_lockSettingsDisableMic = isset($options['lockSettingsDisableMic']) ? 'checked' : '';
        $checked_lockSettingsDisableCam = isset($options['lockSettingsDisableCam']) ? 'checked' : '';
        $checked_webcamsOnlyForModerator = isset($options['webcamsOnlyForModerator']) ? 'checked' : '';
        $checked_lockSettingsDisablePrivateChat = isset($options['lockSettingsDisablePrivateChat']) ? 'checked' : '';
        $checked_lockSettingsDisablePublicChat = isset($options['lockSettingsDisablePublicChat']) ? 'checked' : '';
        $checked_lockSettingsDisableNote = isset($options['lockSettingsDisableNote']) ? 'checked' : '';
        $checked_lockSettingsHideUserList = isset($options['lockSettingsHideUserList']) ? 'checked' : '';
        $checked_hideParticipants = isset($options['hideParticipants']) ? 'checked' : '';

        $submit_name = 'update_bbb_session';
        $submit_id = "<input type=hidden name = 'id' value=" . getIndirectReference($session_id) . ">";
        $value_message = $langModify;
    } else { // new BBB meeting
        $status = 1;
        $unlock_interval = '10';
        $r_group = array();
        $start_date = new DateTime;
        $start_session = $start_date->format('d-m-Y H:i');
        $end_date = new DateTime;
        $BBBEndDate = $end_date->format('d-m-Y H:i');
        $textarea = rich_text_editor('desc', 4, 20, '');
        $value_title = '';
        $init_external_users = '';
        $value_session_users = $c;
        $submit_name = 'new_bbb_session';
        $submit_id = '';
        $value_message = $langAdd;
        $options = NULL;
        $record = get_config('bbb_recording', 1);
        $checked_muteOnStart = get_config('bbb_muteOnStart', 0) ? 'checked' : '';
        $checked_lockSettingsDisableMic = get_config('bbb_DisableMic', 0) ? 'checked' : '';
        $checked_lockSettingsDisableCam = get_config('bbb_DisableCam', 0) ? 'checked' : '';
        $checked_webcamsOnlyForModerator = get_config('bbb_webcamsOnlyForModerator', 0) ? 'checked' : '';
        $checked_lockSettingsDisablePrivateChat = get_config('bbb_DisablePrivateChat', 0) ? 'checked' : '';
        $checked_lockSettingsDisablePublicChat = get_config('bbb_DisablePublicChat', 0) ? 'checked' : '';
        $checked_lockSettingsDisableNote = get_config('bbb_DisableNote', 0) ? 'checked' : '';
        $checked_lockSettingsHideUserList = get_config('bbb_HideUserList', 0) ? 'checked' : '';
        $checked_hideParticipants = get_config('bbb_hideParticipants', 0) ? 'checked' : '';
        if (!empty($checked_muteOnStart) or !empty($checked_lockSettingsDisableMic) or
           !empty($checked_lockSettingsDisableCam) or !empty($checked_webcamsOnlyForModerator) or
           !empty($checked_lockSettingsDisablePrivateChat) or !empty($checked_lockSettingsDisablePublicChat) or
           !empty($checked_lockSettingsDisableNote) or !empty($checked_lockSettingsHideUserList) or
           !empty($checked_hideParticipants)) {
            $options_show = "show";
        } else {
            $options_show = "";
        }
    }

    $server_id = Database::get()->querySingle("SELECT id FROM tc_servers WHERE `type` = 'bbb'
                                                AND enabled = 'true' ORDER BY FIELD(enable_recordings, 'true', 'false'), weight ASC LIMIT 1")->id;

    $tool_content .= "
        <div class='col-12'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' >
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-6 control-label-notes'>$langTitle</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' name='title' id='title' value='$value_title' placeholder='$langTitle' size='50'>
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='desc' class='col-sm-6 control-label-notes'>$langUnitDescr</label>
            <div class='col-sm-12'>
                $textarea
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='start_session' class='col-sm-6 control-label-notes'>$langStart</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' name='start_session' id='start_session' value='$start_session'>
            </div>
        </div>";
        $tool_content .= "<div class='input-append date form-group".(Session::getError('BBBEndDate') ? " has-error" : "")." mt-4' id='enddatepicker' data-date='$BBBEndDate' data-date-format='dd-mm-yyyy'>
            <label for='BBBEndDate' class='col-sm-6 control-label-notes'>$langEnd</label>
            <div class='col-sm-12'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input style='cursor:pointer;' type='checkbox' id='enableEndDate' name='enableEndDate' value='1'".($enableEndDate ? ' checked' : '').">
                    </span>
                    <input class='form-control' name='BBBEndDate' id='BBBEndDate' type='text' value='$BBBEndDate'".($enableEndDate ? '' : ' disabled').">
                </div>
                <span class='help-block'>".(Session::hasError('BBBEndDate') ? Session::getError('BBBEndDate') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langBBBEndHelpBlock")."</span>
            </div>
        </div>";
        $en_recordings = has_enable_recordings($server_id);
        if ($en_recordings == 'true') {
            $tool_content .= "<div class='form-group mt-4'>
                <label for='group_button' class='col-sm-6 control-label-notes mb-2'>$langBBBRecord</label>
                <div class='col-sm-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='user_button' name='record' value='true' " . (($record == true) ? 'checked' : '') . ">
                        $langBBBRecordTrue
                      </label>
                    </div>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='group_button' name='record' value='false' " . (($record == false) ? 'checked' : '') . ">
                       $langBBBRecordFalse
                      </label>
                    </div>
                </div>
            </div>";
        }
        $tool_content .= "<div class='form-group mt-4'>
            <label for='active_button' class='col-sm-6 control-label-notes mb-2'>$langNewBBBSessionStatus:</label>
            <div class='col-sm-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='active_button' name='status' value='1' " . (($status==1) ? "checked" : "") . ">
                        $langVisible
                      </label>
                      <label style='margin-left: 10px;'>
                        <input type='radio' id='inactive_button' name='status' value='0' " . (($status==0) ? "checked" : "") .">
                       $langInvisible
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
        <label for='active_button' class='col-sm-6 control-label-notes mb-2'>$langAnnouncements</label>
            <div class='col-sm-12'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='addAnnouncement' value='1'>$langBBBAnnDisplay
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='minutes_before' class='col-sm-6 control-label-notes'>$langBBBSessionAvailable</label>
            <div class='col-sm-12'>" . selection(array(10 => '10', 15 => '15', 30 => '30'), 'minutes_before', $unlock_interval, "id='minutes_before'") . "
                $langBBBMinutesBefore
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='sessionUsers' class='col-sm-6 control-label-notes'>$langBBBSessionMaxUsers</label>
            <div class='col-sm-12'>
                <input class='form-control' type='number' min='1' pattern='\d+' name='sessionUsers' id='sessionUsers' value='$value_session_users'>";
        if (isset($bbb_max_part_per_room_limit)) {
            $tool_content .= " $langBBBMaxPartPerRoom: <strong>$bbb_max_part_per_room</strong>";
        } else {
            $tool_content .= " $langBBBSessionSuggestedUsers: <strong>$c</strong> ($langBBBSessionSuggestedUsers2)";
        }
        $tool_content .= "
            </div>
        </div>";
        $tool_content .= "<div class='form-group mt-4'>
                <label for='select-groups' class='col-sm-6 control-label-notes'>$langParticipants</label>
                <div class='col-sm-12'>
                <select name='groups[]' multiple='multiple' class='form-select' id='select-groups'>";

        if (empty($r_group) or (count($r_group)>=1 and in_array("0", $r_group))) {
            $tool_content .= "<option value='0' selected><h2>$langAllUsers</h2></option>";
        } else {
            $tool_content .= "<option value='0'><h2>$langAllUsers</h2></option>";
        }

        // select available course groups (if exist)
        $res = Database::get()->queryArray("SELECT `group`.`id`,`group`.`name` FROM `group`
                                                    RIGHT JOIN course ON group.course_id=course.id
                                                    WHERE course.code=?s ORDER BY UPPER(NAME)", $course_code);
        foreach ($res as $r) {
            if (isset($r->id)) {
                $tool_content .= "<option value= '_{$r->id}'";
                if (in_array(("_{$r->id}"), $r_group)) {
                    $found_selected = true;
                    $tool_content .= ' selected';
                }
                $tool_content .= ">" . q($r->name) . "</option>";
            }
        }
        //select all users from this course except yourself
        $sql = "SELECT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                            FROM user u, course_user cu
                            WHERE cu.course_id = ?d
                            AND cu.user_id = u.id
                            AND cu.status != ?d
                            AND u.id != ?d
                            GROUP BY u.id, name, u.username
                            ORDER BY UPPER(u.surname), UPPER(u.givenname)";
        $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);
        foreach ($res as $r) {
            if (isset($r->user_id)) {
                $tool_content .= "<option value='{$r->user_id}'";
                if (in_array(("$r->user_id"), $r_group)) {
                    $found_selected = true;
                    $tool_content .= ' selected';
                }
                $tool_content .= ">" . q($r->name) . " (".q($r->username).")</option>";
            }
        }

        $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                </div>
            </div>";


        $tool_content .= "<div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='notifyUsers' value='1'>$langBBBNotifyUsers
                      </label>
                    </div>
            </div>
        </div>";

        $tool_content .= "
        <div class='form-group mt-4'>
            <label for='tags_1' class='col-sm-6 control-label-notes'>$langBBBExternalUsers</label>
            <div class='col-sm-12'>
                <select id='tags_1' class='form-select' name='external_users[]' multiple></select>
                <span class='help-block'>&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langBBBNotifyExternalUsersHelpBlock</span>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='notifyExternalUsers' value='1'>$langBBBNotifyExternalUsers
                      </label>
                    </div>
            </div>
        </div>";
        $tool_content .= "<div class='clearfix mt-4'>
                            <a role='button' data-bs-toggle='collapse' href='#MoreInfo' aria-expanded='false' aria-controls='MoreInfo'>
                                 <h5 class='panel-heading' style='margin-bottom: 0px;'>
                                       <span class='fa fa-chevron-down fa-fw'></span> $langReadMore
                                 </h5>
                            </a>
                          </div>";
        $tool_content .= "<div class='collapse $options_show' id='MoreInfo'>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='muteOnStart' $checked_muteOnStart value='1'>$langBBBmuteOnStart
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsDisableMic' $checked_lockSettingsDisableMic value='1'>$langBBBlockSettingsDisableMic
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsDisableCam' $checked_lockSettingsDisableCam value='1'>$langBBBlockSettingsDisableCam
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='webcamsOnlyForModerator' $checked_webcamsOnlyForModerator value='1'>$langBBBwebcamsOnlyForModerator
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsDisablePrivateChat' $checked_lockSettingsDisablePrivateChat value='1'>$langBBBlockSettingsDisablePrivateChat
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsDisablePublicChat' $checked_lockSettingsDisablePublicChat value='1'>$langBBBlockSettingsDisablePublicChat
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsDisableNote' $checked_lockSettingsDisableNote value='1'>$langBBBlockSettingsDisableNote
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='lockSettingsHideUserList' $checked_lockSettingsHideUserList value='1'>$langBBBlockSettingsHideUserList
                      </label>
                    </div>
            </div>
        </div>
        <div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                     <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='hideParticipants' $checked_hideParticipants value='1'>$langBBBHideParticipants
                      </label>
                    </div>
            </div>
        </div> ";

        $tool_content .= "</div>
        $submit_id
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-center align-items-center'>
                <input class='btn submitAdminBtn' type='submit' name='$submit_name' value='$value_message'>
            </div>
        </div>
        </fieldset>
         ". generate_csrf_token_form_field() ."
        </form></div></div>";
        $tool_content .= "<script language='javaScript' type='text/javascript'>
        //<![CDATA[
            var chkValidator  = new Validator('sessionForm');
            chkValidator.addValidation('title', 'req', '".js_escape($langBBBAlertTitle)."');
            chkValidator.addValidation('sessionUsers', 'req', '".js_escape($langBBBAlertMaxParticipants)."');
            chkValidator.addValidation('sessionUsers', 'numeric', '".js_escape($langBBBAlertMaxParticipants)."');
            $(function () {
                $('#tags_1').select2({
                    $init_external_users
                    tags: true,
                    tokenSeparators: [',', ' '],
                    width: '100%',
                    selectOnClose: true});
                });
        //]]></script>";
}

/**
 * @brief insert scheduled session data into database
 * @param type $title
 * @param type $desc
 * @param type $start_session
 * @param type $BBBEndDate
 * @param type $status
 * @param type $notifyUsers
 * @param type $notifyExternalUsers
 * @param type $addAnnouncement
 * @param type $minutes_before
 * @param type $external_users
 * @param type $record
 * @param type $sessionUsers
 * @param type $options
 * @param type $update // true == add, false == update
 * @param type $session
 */
function add_update_bbb_session($title, $desc, $start_session, $BBBEndDate, $status, $notifyUsers, $notifyExternalUsers, $addAnnouncement, $minutes_before, $external_users, $record, $sessionUsers, $options, $update, $session_id = '')
{
    global $langBBBScheduledSession, $langBBBScheduleSessionInfo ,
        $langBBBScheduleSessionInfo2, $langBBBScheduleSessionInfoJoin,
        $langDescription, $course_code, $course_id, $urlServer;

    // Groups of participants per session
    $r_group = '';
    if (isset($_POST['groups']) and count($_POST['groups']) > 0) {
        if (in_array("0", $_POST['groups'])) {
            $r_group = '0';
        } else {
            foreach ($_POST['groups'] as $group) {
                $r_group .= "$group" .',';
            }
            $r_group = mb_substr($r_group, 0, -1); // remove last `comma` character
        }
    } else {
        $r_group = '0';
    }
    if (isset($update) and $update) { // update existing BBB session
        Database::get()->querySingle("UPDATE tc_session SET title=?s, description=?s, start_date=?t, end_date=?t,
                                        public=?s, active=?s, unlock_interval=?d, external_users=?s,
                                        participants=?s, record=?s, sessionUsers=?d, options=?s WHERE id=?d",
                                $title, $desc, $start_session, $BBBEndDate, 1, $status, $minutes_before,
                                $external_users, $r_group, $record, $sessionUsers, $options, $session_id);
        // logging
        Log::record($course_id, MODULE_ID_TC, LOG_MODIFY, array('id' => $session_id,
                                                                'title' => $title,
                                                                'desc' => html2text($desc)));

        $q = Database::get()->querySingle("SELECT meeting_id, title, mod_pw, att_pw FROM tc_session WHERE id = ?d", $session_id);
    } else { // new BBB session
        // check if course uses specific tc_server
        $t = Database::get()->querySingle("SELECT external_server FROM course_external_server WHERE course_id = ?d", $course_id);
        if ($t) {
            $server_id = Database::get()->querySingle("SELECT id FROM tc_servers WHERE id = $t->external_server AND `type` = 'bbb' AND enabled = 'true' ORDER BY weight ASC")->id;
        } else { // else course will use default tc_server
            $server_id = Database::get()->querySingle("SELECT id FROM tc_servers WHERE `type` = 'bbb' and enabled = 'true' ORDER BY weight ASC")->id;
        }
        $q = Database::get()->query("INSERT INTO tc_session SET course_id = ?d,
                                                            title = ?s,
                                                            description = ?s,
                                                            start_date = ?t,
                                                            end_date = ?t,
                                                            public = 1,
                                                            active = ?s,
                                                            running_at = ?d,
                                                            meeting_id = ?s,
                                                            mod_pw = ?s,
                                                            att_pw = ?s,
                                                            unlock_interval = ?s,
                                                            external_users = ?s,
                                                            participants = ?s,
                                                            record = ?s,
                                                            sessionUsers = ?s,
                                                            options = ?s",
                                                        $course_id, $title, $desc, $start_session, $BBBEndDate,
                                                        $status, $server_id,
                                                        generateRandomString(), generateRandomString() , generateRandomString() ,
                                                        $minutes_before, $external_users, $r_group, $record, $sessionUsers, $options);

        // logging
        Log::record($course_id, MODULE_ID_TC, LOG_INSERT, array('id' => $q->lastInsertID,
                                                                'title' => $_POST['title'],
                                                                'desc' => html2text($_POST['desc']),
                                                                'tc_type' => 'bbb'));

        $q = Database::get()->querySingle("SELECT meeting_id, title, mod_pw, att_pw FROM tc_session WHERE id = ?d", $q->lastInsertID);
    }
    $new_meeting_id = $q->meeting_id;
    $new_title = $q->title;
    $new_mod_pw = $q->mod_pw;
    $new_att_pw = $q->att_pw;
    // if we have to notify users for new session
    if ($notifyUsers == "1") {
        if (isset($_POST['groups']) and count($_POST['groups']) > 0) {
            $recipients = array();
            if ($_POST['groups'][0] === '0') { // all users
                $result = Database::get()->queryArray("SELECT cu.user_id, u.email FROM course_user cu
                                                        JOIN user u ON cu.user_id=u.id
                                                    WHERE cu.course_id = ?d
                                                    AND u.email <> ''
                                                    AND u.email IS NOT NULL", $course_id);

            } else {
                $r_group = '';
                foreach ($_POST['groups'] as $group) {
                    if (preg_match('/^_/', $group)) { // find group users (if any)
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
                $r_group = rtrim($r_group,',');
                $result = Database::get()->queryArray("SELECT course_user.user_id, user.email
                                                            FROM course_user, user
                                                       WHERE course_id = ?d AND user.id IN ($r_group) AND
                                                             course_user.user_id = user.id", $course_id);

            }
            foreach($result as $row) {
                $emailTo = $row->email;
                $user_id = $row->user_id;
                // we check if email notification are enabled for each user
                if (get_user_email_notification($user_id)) {
                    //and add user to recipients
                    array_push($recipients, $emailTo);
                }
            }
            if (count($recipients) > 0) {
                $emailsubject = $langBBBScheduledSession;
                $bbblink = $urlServer . "modules/tc/index.php?course=$course_code&amp;choice=do_join&amp;meeting_id=$new_meeting_id&amp;title=" . urlencode($new_title) . "&amp;att_pw=$new_att_pw";
                $emailheader = "
                    <div id='mail-header'>
                        <div>
                            <div id='header-title'>$langBBBScheduleSessionInfo" . q($title) .  " $langBBBScheduleSessionInfo2" . q($start_session). "</div>
                        </div>
                    </div>
                ";

                $emailmain = "
                <div id='mail-body'>
                    <div><b>$langDescription:</b></div>
                    <div id='mail-body-inner'>
                        $desc
                        <br><br>$langBBBScheduleSessionInfoJoin:<br><a href='$bbblink'>$bbblink</a>
                    </div>
                </div>
                ";

                $emailcontent = $emailheader . $emailmain;
                $emailbody = html2text($emailcontent);
                // Notify course users for new bbb session
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], '', $recipients, $emailsubject, $emailbody, $emailcontent);
            }
        }
    }

    // Notify external users for new bbb session
    if ($notifyExternalUsers == "1") {
        if (isset($external_users)) {
            $recipients = explode(',', $external_users);
            $emailsubject = $langBBBScheduledSession;
            $emailheader = "
                    <div id='mail-header'>
                        <div>
                            <div id='header-title'>$langBBBScheduleSessionInfo" . q($title) .  " $langBBBScheduleSessionInfo2" . q($start_session)."</div>
                        </div>
                    </div>
                ";
            foreach ($recipients as $row) {
                $bbblink = $urlServer . "modules/tc/ext.php?course=$course_code&amp;meeting_id=$new_meeting_id&amp;username=" . urlencode($row);

                $emailmain = "
                <div id='mail-body'>
                    <div><b>$langDescription:</b></div>
                    <div id='mail-body-inner'>
                        $desc
                        <br><br>$langBBBScheduleSessionInfoJoin:<br><a href='$bbblink'>$bbblink</a>
                    </div>
                </div>
                ";
                $emailcontent = $emailheader . $emailmain;
                $emailbody = html2text($emailcontent);
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], '', $row, $emailsubject, $emailbody, $emailcontent);
            }
        }
    }

    if ($addAnnouncement == '1') { // add announcement
        $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
        $order = $orderMax + 1;
        Database::get()->querySingle("INSERT INTO announcement (content,title,`date`,course_id,`order`,visible)
                                    VALUES ('".$langBBBScheduleSessionInfo . " \"" . q($title) . "\" " . $langBBBScheduleSessionInfo2 . " " . $start_session."',
                                             '$langBBBScheduledSession', " . DBHelper::timeAfter() . ", ?d, ?d, '1')", $course_id, $order);
    }

}


/**
 ** @brief Print a box with the details of a bbb session
 */
function bbb_session_details() {

    global $course_id, $tool_content, $is_editor, $course_code, $uid,
        $langParticipants,$langConfirmDelete, $langHasExpiredS,
        $langBBBSessionJoin, $langNote, $langBBBNoteEnableJoin, $langTitle,
        $langActivate, $langDeactivate, $langEditChange, $langDelete, $langParticipate,
        $langNoBBBSesssions, $langDaysLeft, $langBBBNotServerAvailableStudent,
        $langBBBNotServerAvailableTeacher,
        $langBBBImportRecordings, $langAllUsers, $langDuration, $langBBBNoServerForRecording,
        $langFrom, $langTill, $langBBBHideParticipants;

    $options = [];
    if (!is_enabled_tc_server($course_id)) { // check availability
        if ($is_editor) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'>$langBBBNotServerAvailableTeacher</div></div>";

        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'>$langBBBNotServerAvailableStudent</div></div>";
        }
    }

    load_js('trunk8');

    $myGroups = Database::get()->queryArray("SELECT group_id FROM group_members WHERE user_id=?d", $_SESSION['uid']);
    $activeClause = $is_editor? '': "AND active = '1'";
    $result = Database::get()->queryArray("SELECT * FROM tc_session WHERE course_id = ?s $activeClause
                                                ORDER BY start_date DESC", $course_id);
    if ($result) {
        if ((!$is_editor) and (!is_enabled_tc_server($course_id))) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'><label>$langNote</label>: $langBBBNoteEnableJoin</div></div>";
        }
        $headingsSent = false;
        $headings = "
                       <div class='col-sm-12'>
                         <div class='table-responsive'>
                           <table class='table-default'>
                             <tr class='list-header'>
                               <th class='ps-3' style='width: 50%'>$langTitle</th>
                               <th class='text-center'>$langDuration</th>
                               <th class='text-center'>$langParticipants</th>
                               <th class='text-center'>".icon('fa-gears')."</th>
                             </tr>";
        $i = 0;

        foreach ($result as $row) {
            if (!is_null($row->options)) {
                $options = unserialize($row->options);
            }
            if ($is_editor or empty($options['hideParticipants'])) {
                $participants = '';
                // Get participants
                $r_group = explode(",",$row->participants);
                foreach ($r_group as $participant_uid) {
                    if ($participants) {
                        $participants .= ', ';
                    }
                    $participant_uid = str_replace("'", '', $participant_uid);
                    if (preg_match('/^_/', $participant_uid)) {
                        $participants .= gid_to_name(str_replace("_", '', $participant_uid));
                    } else {
                        if ($participant_uid == 0) {
                            $participants .= $langAllUsers;
                        } else {
                            $participants .= uid_to_name($participant_uid, 'fullname');
                        }
                    }
                }
                $participants = "<span class='trunk8'>$participants</span>";
            } else {
                $participants = "<span class='trunk8'>$langBBBHideParticipants</span>";
            }
            $id = $row->id;
            $title = $row->title;
            $start_date = $row->start_date;
            $end_date = $row->end_date;
            $starttimeLabel = '';
            if (date_diff_in_minutes($start_date, date('Y-m-d H:i:s')) > 0) {
                $starttimeLeft = date_diff_in_minutes($start_date, date('Y-m-d H:i:s'));
                $starttimeLabel .= "<br><span class='label label-warning'><small>$langDaysLeft " .
                    format_time_duration($starttimeLeft * 60) .
                    "</small></span>";
            }

            if($end_date) {
                $timeLeft = date_diff_in_minutes($end_date, date('Y-m-d H:i:s'));
                $timeLabel = format_locale_date(strtotime($end_date));
            } else {
                $timeLeft = date_diff_in_minutes($start_date, date('Y-m-d H:i:s'));
                $timeLabel = '&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
            }
            if ($timeLeft > 0 and date_diff_in_minutes($start_date, date('Y-m-d H:i:s')) < 0) {
                $timeLabel .= "<br><span class='label label-warning'><small>$langDaysLeft " .
                    format_time_duration($timeLeft * 60) .
                    "</small></span>";
            } elseif (isset($end_date) and ($timeLeft < 0)) {
                $timeLabel .= "<br><span class='label label-danger'><small>$langHasExpiredS</small></span>";
            }
            $meeting_id = $row->meeting_id;
            $att_pw = $row->att_pw;
            $mod_pw = $row->mod_pw;
            $record = $row->record;
            $server_id = $row->running_at;
            $desc = isset($row->description)? $row->description: '';
            $tc_type = Database::get()->querySingle("SELECT `type` FROM tc_servers WHERE id = ?d", $server_id)->type;
            $canJoin = FALSE;
            if (($row->active == '1') and (date_diff_in_minutes($start_date, date('Y-m-d H:i:s')) < $row->unlock_interval)
                    and is_active_tc_server($tc_type, $course_id)) {
                $canJoin = TRUE;
            }
            if (isset($end_date) and ($timeLeft < 0)) {
                $canJoin = FALSE;
            }

            if ($canJoin) {
                if($is_editor) {
                    $i++;
                    $joinLink = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=" . urlencode($meeting_id) . "&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."&amp;mod_pw=".urlencode($mod_pw)."' target='_blank'>" . q($title) . "</a>";
                } else {
                    $joinLink = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=" . urlencode($meeting_id) . "&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."' target='_blank'>" . q($title) . "</a>";
                }
            } else {
                $joinLink = q($title);
            }

            if ($record == 'false' or has_enable_recordings($server_id) == 'false') {
                $warning_message_record = "<span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langBBBNoServerForRecording'></span>";
            } else {
                $warning_message_record = '';
            }

            if ($is_editor) {
                if (!$headingsSent) {
                    $tool_content .= $headings;
                    $headingsSent = true;
                }
                $tool_content .= '<tr' . ($row->active? '': " class='not_visible'") . ">
                    <td>
                        <div class='table_td'>
                            <div class='table_td_header clearfix'>$joinLink $warning_message_record</div>
                            <div class='table_td_body'>
                                $desc
                            </div>
                        </div>
                    </td>
                    <td class='text-center'>
                        <div style='padding-top: 7px;'>
                            <span class='text-success'>$langFrom</span>: ".format_locale_date(strtotime($start_date))."$starttimeLabel<br/>
                        </div>
                        <div style='padding-top: 7px;'>
                            <span class='text-danger'>$langTill</span>: $timeLabel</br></br>
                        </div>
                    </td>

                    <td style='width: 20%'>$participants</td>
                    <td class='option-btn-cell text-center'>".
                        action_button(array(
                            array(  'title' => $langEditChange,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=edit",
                                    'icon' => 'fa-edit'),
                            array(  'title' => $langBBBImportRecordings,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($row->id) . "&amp;choice=import_video",
                                    'icon' => "fa-edit",
                                    'show' => $tc_type == 'bbb'),
                            array(  'title' => $langParticipate,
                                    'url' => "tcuserduration.php?course=$course_code&amp;id=$row->id",
                                    'icon' => "fa-clock-o",
                                    'show' => $tc_type == 'bbb'),
                            array(  'title' => $row->active? $langDeactivate : $langActivate,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($row->id) . "&amp;choice=do_".
                                             ($row->active? 'disable' : 'enable'),
                                    'icon' => $row->active ? 'fa-eye': 'fa-eye-slash'),
                            array(  'title' => $langDelete,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($row->id) . "&amp;choice=do_delete",
                                    'icon' => 'fa-times',
                                    'class' => 'delete',
                                    'confirm' => $langConfirmDelete)
                            )) .
                    "</td></tr>";
            } else {
                $access = FALSE;
                // Allow access to session only if user is in participant group or session is scheduled for everyone
                $r_group = explode(",", $row->participants);
                if (in_array('0', $r_group)) { // all users
                    $access = TRUE;
                } else {
                    if (in_array("$uid", $r_group)) { // user search
                        $access = TRUE;
                    } else {
                        foreach ($myGroups as $user_gid) { // group search
                            if (in_array("_$user_gid->group_id", $r_group)) {
                                $access = TRUE;
                            }
                        }
                    }
                }

                // Always allow access to editor switched to student view
                $access = $access || (isset($_SESSION['student_view']) and $_SESSION['student_view'] == $course_code);

                if ($access) {
                    if (!$headingsSent) {
                        $tool_content .= $headings;
                        $headingsSent = true;
                    }
                    $tool_content .= "<tr>
                        <td>
                        <div class='table_td'>
                            <div class='table_td_header clearfix'>$joinLink $warning_message_record</div> 
                            <div class='table_td_body'>
                                $desc
                            </div>
                        </div>
                    </td>
                    <td class='text-center'>
                        <div style='padding-top: 7px;'>
                            <span class='text-success'>$langFrom</span>: ". format_locale_date(strtotime($start_date)) ."$starttimeLabel<br/>
                        </div>
                        <div style='padding-top: 7px;'>
                            <span class='text-danger'>$langTill</span>: $timeLabel</br></br>
                        </div>
                    </td>
                        <td style='width: 20%'>$participants</td>
                        <td class='text-center'>";
                    // Join url will be active only X minutes before scheduled time and if session is visible for users
                    if ($canJoin) {
                        $tool_content .= icon('fa-sign-in', $langBBBSessionJoin,"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;title=".urlencode($title)."&amp;meeting_id=" . urlencode($meeting_id) . "&amp;att_pw=".urlencode($att_pw)."&amp;record=$record' target='_blank");
                    } else {
                        $tool_content .= "-</td>";
                    }
                    $tool_content .= "</tr>";
                }
            }
        }
        if ($headingsSent) {
            $tool_content .= "</table></div></div>";
        }

        if (!$is_editor and !$headingsSent) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langNoBBBSesssions</div></div>";
        }
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langNoBBBSesssions</div></div>";
    }
}

/**
 * @brief disable bbb session
 * @param type $id
 * @return type
 */
function disable_bbb_session($id)
{
    global $langBBBUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE tc_session set active='0' WHERE id=?d",$id);
   // Session::Messages($langBBBUpdateSuccessful, 'alert-success');
    Session::flash('message',$langBBBUpdateSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/tc/index.php?course=$course_code");
}

/**
 * @brief enable bbb session
 * @param type $id
 * @return type
 */
function enable_bbb_session($id)
{
    global $langBBBUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE tc_session SET active='1' WHERE id=?d",$id);
   // Session::Messages($langBBBUpdateSuccessful, 'alert-success');
    Session::flash('message',$langBBBUpdateSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/tc/index.php?course=$course_code");
}


/**
 * @brief delete bbb sessions
 * @param type $id
 * @return type
 */
function delete_bbb_session($id)
{
    global $langBBBDeleteSuccessful, $course_code, $course_id;

    $tc_title = Database::get()->querySingle("SELECT title FROM tc_session WHERE id = ?d", $id)->title;
    Database::get()->querySingle("DELETE FROM tc_session WHERE id = ?d", $id);
    Log::record($course_id, MODULE_ID_TC, LOG_DELETE, array('id' => $id,
                                                            'title' => $tc_title));

   // Session::Messages($langBBBDeleteSuccessful, 'alert-success');
    Session::flash('message',$langBBBDeleteSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/tc/index.php?course=$course_code");
}

/**
 * @brief create bbb meeting
 * @param type $title
 * @param type $meeting_id
 * @param type $mod_pw
 * @param type $att_pw
 * @param type $record
 * @param type $options
 */
function create_bbb_meeting($title, $meeting_id, $mod_pw, $att_pw, $record, $options)
{
    global $langBBBCreationRoomError, $langBBBConnectionError, $course_code,
        $langBBBWelcomeMsg, $langBBBConnectionErrorOverload, $course_id, $urlServer;

    // get list of servers ordered by load balancing settings
    $servers = get_bbb_servers($record);

    if (is_array($servers) and count($servers) > 0) {
        $participants = get_tc_participants($meeting_id);
        // check if course uses a specific server
        $q = Database::get()->querySingle("SELECT external_server FROM course_external_server WHERE course_id = ?d", $course_id);
        if ($q) {
            $run_to = $q->external_server;
            if (in_array($run_to, $servers)) { // check if server is enabled

                // Reorder servers to put specific server on top.
                // Even if $record is 'true', recording will not be enabled if server has disabled recordings.
                $tmp[0] = $run_to;
                $i=1;
                foreach ($servers as $server) {
                    if ($server != $run_to) {
                        $tmp[$i] = $server;
                        $i++;
                    }
                }
                $servers = $tmp;
            }
        }

        foreach ($servers as $server_id) {
            if (is_bbb_server_available($server_id, $participants)) {
                $run_to = $server_id;
                Database::get()->query("UPDATE tc_session SET running_at = ?d WHERE meeting_id = ?s", $run_to, $meeting_id);
                break;
            } else {
                $run_to = -1; // no bbb server available
            }
        }
    } else {
        $run_to = -1; // no bbb server exists
    }
    if ($run_to == -1) {
      //  Session::Messages($langBBBConnectionErrorOverload, 'alert-danger');
        Session::flash('message',$langBBBConnectionErrorOverload);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/tc/index.php?course=$course_code");
    } else { // create the meeting
        $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d AND `type`='bbb'", $run_to);
        $salt = $res->server_key;
        $bbb_url = $res->api_url;
        $duration = 0;
        $r = Database::get()->querySingle("SELECT end_date FROM tc_session WHERE meeting_id = ?s", $meeting_id);
        if ($r->end_date != null) {
            $now = new DateTime('now');
            $end = new DateTime($r->end_date);
            $interval = $now->diff($end);
            $duration = $interval->y * 365 * 24 * 60 + $interval->m * 31 * 24 * 60 + $interval->d * 24 * 60 + $interval->h * 60 + $interval->i + ($interval->s >= 30? 1: 0);
        }

        $bbb_max_duration = get_config('bbb_max_duration', 0);
        if ($bbb_max_duration > 0 and ($duration === 0 or $duration > $bbb_max_duration)) {
            $duration = $bbb_max_duration;
        }

        $bbb = new BigBlueButton($salt, $bbb_url);

        if (isset($course_code) and $course_code) {
            $logoutUrl = $urlServer . 'modules/tc/index.php?course=' . $course_code;
        } else {
            $logoutUrl = '';
        }

        $muteOnStart = isset($options['muteOnStart']) ? "true" : "false";
        $lockSettingsDisableMic = isset($options['lockSettingsDisableMic']) ? "true" : "false";
        $lockSettingsDisableCam = isset($options['lockSettingsDisableCam']) ? "true" : "false";
        $webcamsOnlyForModerator = isset($options['webcamsOnlyForModerator']) ? "true" : "false";
        $lockSettingsDisablePrivateChat = isset($options['lockSettingsDisablePrivateChat']) ? "true" : "false";
        $lockSettingsDisablePublicChat = isset($options['lockSettingsDisablePublicChat']) ? "true" : "false";
        $lockSettingsDisableNote = isset($options['lockSettingsDisableNote']) ? "true" : "false";
        $lockSettingsHideUserList = isset($options['lockSettingsHideUserList']) ? "true" : "false";

        $creationParams = array(
            'meetingId' => $meeting_id, // REQUIRED
            'meetingName' => $title, // REQUIRED
            'attendeePw' => $att_pw, // Match this value in getJoinMeetingURL() to join as attendee.
            'moderatorPw' => $mod_pw, // Match this value in getJoinMeetingURL() to join as moderator.
            'welcomeMsg' => $langBBBWelcomeMsg,
            'dialNumber' => '', // The main number to call into. Optional.
            'voiceBridge' => '', // PIN to join voice. Optional.
            'webVoice' => '', // Alphanumeric to join voice. Optional.
            'logoutUrl' => $logoutUrl,
            'maxParticipants' => '-1', // Optional. -1 = unlimited. Not supported in BBB. [number]
            'record' => $record, // New. 'true' will tell BBB to record the meeting.
            'duration' => $duration, // Default = 0 which means no set duration in minutes. [number]
            'muteOnStart' => $muteOnStart, // Default = false. true will mute all users when the meeting starts
            'lockSettingsDisableMic' => $lockSettingsDisableMic, // Default = false. true will disable viewer's mic
            'lockSettingsDisableCam' => $lockSettingsDisableCam, // Default = false. true will prevent viewers from sharing their camera in the meeting
            'webcamsOnlyForModerator' => $webcamsOnlyForModerator, // Default = false. With true, webcams shared by viewers will only appear to moderators
            'lockSettingsDisablePrivateChat' => $lockSettingsDisablePrivateChat, // Default = false. true will disable private chats for viewers
            'lockSettingsDisablePublicChat' => $lockSettingsDisablePublicChat, // Default = false. true will disable public chat for viewers
            'lockSettingsDisableNote' => $lockSettingsDisableNote, // Default = false. true will disable shared notes for viewers
            'lockSettingsHideUserList' => $lockSettingsHideUserList, // Default = false. true will hide viewer's list for viewers
            //'meta_category' => '', // Use to pass additional info to BBB server. See API docs.
        );

        // Create the meeting and get back a response:
        $result = $bbb->createMeetingWithXmlResponseArray($creationParams);
        // If it's all good, then we've interfaced with our BBB php api OK:
        if ($result == null) {
            // If we get a null response, then we're not getting any XML back from BBB.
            //Session::Messages($langBBBConnectionError, 'alert-danger');
            Session::flash('message',$langBBBConnectionError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/tc/index.php?course=$course_code");
        }
        if ($result['returncode'] != 'SUCCESS') {
            //Session::Messages($langBBBCreationRoomError, 'alert-danger');
            Session::flash('message',$langBBBCreationRoomError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/tc/index.php?course=$course_code");
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
function bbb_join_moderator($meeting_id, $mod_pw, $att_pw, $surname, $name) {

    $res = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?s", $running_server);

    if ($res) {
        $salt = $res->server_key;
        $bbb_url = $res->api_url;

        // Instantiate the BBB class:
        $bbb = new BigBlueButton($salt, $bbb_url);

        $joinParams = array(
            'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting to join.
            'fullName' => $surname . " " . $name,   // REQUIRED - The user display name that will show in the BBB meeting.
            'password' => $mod_pw,  // REQUIRED - Must match either attendee or moderator pass for meeting.
            'createTime' => '', // OPTIONAL - string
            'userId' => $_SESSION['uname'], // OPTIONAL - string
            'webVoiceConf' => ''    // OPTIONAL - string
        );
        // Get the URL to join meeting:

        try {
            $result = $bbb->getJoinMeetingURL($joinParams);
        }
        catch (Exception $e) {
            echo $e->getMessage();
            return $result;
        }
    }
    return $result;
}

/**
 * @brief create join as simple user link
 * @param type $meeting_id
 * @param type $att_pw
 * @param type $surname
 * @param type $name
 * @return type
 */
function bbb_join_user($meeting_id, $att_pw, $surname, $name) {

    $res = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $running_server);

    $salt = $res->server_key;
    $bbb_url = $res->api_url;

    // Instantiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    if (isset($_SESSION['uname'])) {
      $userId = $_SESSION['uname'];
    } else { // ext.php users
      $userId = '';
    }

    $joinParams = array(
        'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting to join.
        'fullName' => $surname . " " . $name,   // REQUIRED - The user display name that will show in the BBB meeting.
        'password' => $att_pw,  // REQUIRED - Must match either attendee or moderator pass for meeting.
        'createTime' => '', // OPTIONAL - string
        'userId' => $userId, // OPTIONAL - string
        'webVoiceConf' => ''    // OPTIONAL - string
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

/**
 * @brief check if session is running
 * @param type $meeting_id
 * @return boolean
 */
function bbb_session_running($meeting_id)
{
    $res = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s",$meeting_id);

    if (!isset($res->running_at)) {
        return false;
    } else {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $running_server);
    $enabled = $res->enabled;
    if ($enabled == 'false') {
        return false;
    }
    $salt = $res->server_key;
    $bbb_url = $res->api_url;
    if (!isset($salt) || !isset($bbb_url)) {
        return false;
    }

    // instantiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);
    // Get the URL to join meeting:
    try {
        $result = $bbb->isMeetingRunningWithXmlResponseArray($meeting_id);
    }
    catch (Exception $e) {
        return false;
    }
    if ((string) $result['running'] == 'false') {
        return false;
    } else {
        return true;
    }
}

/**
 * @brief function to calculate date diff in minutes in order to enable join link
 * @param type $start_date
 * @param type $current_date
 * @return integer
 */
function date_diff_in_minutes($start_date, $current_date) {

    return round((strtotime($start_date) - strtotime($current_date)) / 60);

}

/**
 * @brief get connected number of users of BBB server
 * @param type $salt
 * @param type $bbb_url
 * @param type $ip
 * @return int
 */
function get_connected_users($salt, $bbb_url)
{

    $sum = 0;
    // instantiate the BBB class
    $bbb = new BigBlueButton($salt,$bbb_url);

    $meetings = $bbb->getMeetingsWithXmlResponseArray();
    if (!$meetings) {
        $meetings = array();
    }
    foreach ($meetings as $meeting) {
        $mid = $meeting['meetingId'];
        $pass = $meeting['moderatorPw'];
        if ($mid != null) {
            $info = $bbb->getMeetingInfoWithXmlResponseArray($bbb,$bbb_url,$salt,array('meetingId' => $mid, 'password' => $pass));
            $sum += $info['participantCount'];
        }
    }
    return $sum;

}

/**
 * @brief get number of active rooms
 * @param type $salt
 * @param type $bbb_url
 * @return int
 */
function get_active_rooms($salt, $bbb_url)
{
    $sum = 0;
    // instantiate the BBB class
    $bbb = new BigBlueButton($salt,$bbb_url);

    $meetings = $bbb->getMeetingsWithXmlResponseArray();

    if ($meetings) {
        foreach ($meetings as $meeting) {
            $mid = $meeting['meetingId'];
            $pass = $meeting['moderatorPw'];
            if ($mid != null) {
                $sum += 1;
            }
        }
    }

    return $sum;
}

/**
 * @brief get room details
 * @param type $salt
 * @param type $bbb_url
 * @return array
 */
function get_active_rooms_details($salt, $bbb_url)
{
    // instantiate the BBB class
    $bbb = new BigBlueButton($salt,$bbb_url);

    $meetings = $bbb->getMeetingsWithXmlResponseArray();
    if (!$meetings) {
        $meetings = array();
    }
    $info = array();
    foreach ($meetings as $meeting) {
        $mid = $meeting['meetingId'];
        $pass = $meeting['moderatorPw'];
        if ($mid != null) {
            $info[] = $bbb->getMeetingInfoWithXmlResponseArray($bbb, $bbb_url, $salt, array('meetingId' => $mid, 'password' => $pass));
        }
    }
    return $info;
}

/**
 * @brief display video recordings in multimedia
 * @param type $course_id
 * @param type $id
 * @return boolean
 */
function publish_video_recordings($course_id, $id)
{
    global $langBBBImportRecordingsOK, $langBBBImportRecordingsNo, $langBBBImportRecordingsNoNew, $tool_content;

    $sessions = Database::get()->queryArray("SELECT tc_session.id, tc_session.course_id AS course_id,"
            . "tc_session.title, tc_session.description, tc_session.start_date,"
            . "tc_session.meeting_id, course.prof_names FROM tc_session "
            . "LEFT JOIN course ON tc_session.course_id=course.id WHERE course.code=?s AND tc_session.id=?d", $course_id, $id);

    $servers = Database::get()->queryArray("SELECT * FROM tc_servers WHERE enabled='true' AND `type` = 'bbb'");

    $perServerResult = array(); /*AYTO THA EINAI TO ID THS KATASTASHS GIA KATHE SERVER*/

    if (($sessions) && ($servers)) {
        $msgID = array();
        foreach ($servers as $server) {
            $salt = $server->server_key;
            $bbb_url = $server->api_url;

            $bbb = new BigBlueButton($salt, $bbb_url);
            $sessionsCounter = 0;
            foreach ($sessions as $session) {
                $recordingParams = array(
                    'meetingId' => $session->meeting_id,
                );
                $ch = curl_init();
                $timeout = 0;
                curl_setopt ($ch, CURLOPT_URL, $bbb->getRecordingsUrl($recordingParams));
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $recs = curl_exec($ch);
                curl_close($ch);

                $xml = simplexml_load_string($recs);
                // If not set, it means that there is no video recording.
                // Skip and search for next one
                if (isset($xml->recordings->recording/*->playback->format->url*/)) {
                    foreach ($xml->recordings->recording as $recording) {
                        $format = $recording->playback->format;
                        if (!is_array($format)) {
                            $format = [$format];
                        }
                        foreach ($format as $entry) {
                            if ($entry->type != 'presentation') {
                                continue;
                            }
                            $url = (string) $entry->url;
                            // Check if recording already in videolinks and if not insert
                            $c = Database::get()->querySingle("SELECT id FROM videolink
                                WHERE url = ?s AND course_id = ?d LIMIT 1", $url, $course_id);
                            if (!$c) {
                                Database::get()->querySingle("INSERT INTO videolink
                                    (course_id, url, title, description, creator, publisher, date, visible, public)
                                    VALUES (?s, ?s, ?s, IFNULL(?s, '-'), ?s, ?s, ?t, ?d, ?d)",
                                    $session->course_id, $url, $session->title, strip_tags($session->description),
                                    $session->prof_names, $session->prof_names, $session->start_date, 1, 1);
                                $msgID[$sessionsCounter] = 2;  /*AN EGINE TO INSERT SWSTA PAIRNEI 2*/
                            } else {
                                if (isset($msgID[$sessionsCounter])) {
                                    if ($msgID[$sessionsCounter] <= 1) {
                                        $msgID[$sessionsCounter] = 1;  /*AN DEN EXEI GINEI KANENA INSERT MEXRI EKEINH TH STIGMH PAIRNEI 1*/
                                    }
                                } else {
                                    $msgID[$sessionsCounter] = 1;
                                }
                            }
                        }
                    }
                } else {
                    $msgID[$sessionsCounter] = 0;  /*AN DEN YPARXOUN KAN RECORDINGS PAIRNEI 0*/
                }
                $sessionsCounter++;
            }
            $finalMsgPerSession = max($msgID);
            array_push($perServerResult, $finalMsgPerSession);
        }
        $finalMsg = max($perServerResult);
        switch($finalMsg)
        {
            case 0:
                $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langBBBImportRecordingsNo</div></div>";
                break;
            case 1:
                $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langBBBImportRecordingsNoNew</div></div>";
                break;
            case 2:
                $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'>$langBBBImportRecordingsOK</div></div>";
                break;
        }
    }
    return true;
}

/**
 * @brief get number of meeting users
 * @param type $salt
 * @param type $bbb_url
 * @param type $meeting_id
 * @param type $pw
 * @return type
 */
function get_meeting_users($salt,$bbb_url,$meeting_id,$pw)
{
    global $langBBBGetUsersError, $langBBBConnectionError, $course_code;

    // Instantiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    $infoParams = array(
        'meetingId' => $meeting_id, // REQUIRED - We have to know which meeting.
        'password' => $pw,  // REQUIRED - Must match moderator pass for meeting.
    );

    // Now get meeting info and display it:
    $result = $bbb->getMeetingInfoWithXmlResponseArray($bbb,$bbb_url,$salt,$infoParams);
    // If it's all good, then we've interfaced with our BBB php api OK:
    if ($result == null) {
        // If we get a null response, then we're not getting any XML back from BBB.
       // Session::Messages($langBBBConnectionError, 'alert-danger');
        Session::flash('message',$langBBBConnectionError);
        Session::flash('alert-class', 'alert-danger');
        redirect("index.php?course=$course_code");
    } else {
        // We got an XML response, so let's see what it says:
        if (isset($result['messageKey'])) {
           // Session::Messages($langBBBGetUsersError, 'alert-danger');
            Session::flash('message',$langBBBGetUsersError);
            Session::flash('alert-class', 'alert-danger');
            redirect("index.php?course=$course_code");
        } else {
            return $result['participantCount'];
        }
    }
    return $result['participantCount'];
}


/**
 * @brief check if tc_server is enabled for all courses
 * @param type $tc_type
 * @return boolean
 */
function is_tc_server_enabled_for_all($tc_type) {

    $q = Database::get()->queryArray("SELECT all_courses FROM tc_servers WHERE enabled='true' AND `type` = '$tc_type'");
    if (count($q) > 0) {
       foreach ($q as $data) {
           if ($data->all_courses == 1) { // server is enabled for all courses
               return true;
           } else {
               return false;
           }
       }
    } else { // no active servers
        return false;
    }
}
/**
 * @brief find active tc server for a course
 * @param type $course_id
 * @param type $tc_type
 * @return boolean
 */
function is_active_tc_server($tc_type, $course_id) {

    $q = Database::get()->queryArray("SELECT id, all_courses FROM tc_servers WHERE enabled='true'
                                AND `type` = '$tc_type' ORDER BY weight");

    if (count($q) > 0) {
        foreach ($q as $data) {
            if ($data->all_courses == 1) { // tc_server is enabled for all courses
                return true;
            } else { // check if tc_server is enabled for specific course
                $q = Database::get()->querySingle("SELECT * FROM course_external_server
                                    WHERE course_id = ?d AND external_server = $data->id", $course_id);
                if ($q) {
                    return true;
                }
            }
        }
        return false;
    } else { // no active tc_servers
        return false;
    }
}

/**
 * @brief find if there is enabled tc server for a course
 * @param $course_id
 * @return bool
 */
function is_enabled_tc_server($course_id) {

    if (is_active_tc_server('bbb', $course_id) or
        is_active_tc_server('jitsi', $course_id) or
        is_active_tc_server('googlemeet', $course_id)) {
        return true;
    } else {
        return false;
    }
}


/**
 * @brief find out which tc servers are configured
 * @return array of tc_servers
 */
function is_configured_tc_server() {

    $tc_servers = [];
    if (get_config('ext_bigbluebutton_enabled')) {
        $tc_servers[] = 'bbb';
    }
    if (get_config('ext_googlemeet_enabled')) {
        $tc_servers[] = 'googlemeet';
    }
    if (get_config('ext_jitsi_enabled')) {
        $tc_servers[] = 'jitsi';
    }
    /*if (get_config('ext_openmeetings_enabled')) {
        $tc_type = 'om';
    }*/
    return $tc_servers;
}

/**
 * @brief check if bbb server is available
 * @param type $server_id
 * @param type $participants
 * @return boolean
 */
function is_bbb_server_available($server_id, $participants)
{

    global $course_id;

    //Get all course participants
    if ($participants == 0) {
        $users_to_join = Database::get()->querySingle("SELECT COUNT(*) AS count FROM course_user, user
                                WHERE course_user.course_id = ?d AND course_user.user_id = user.id", $course_id)->count;
    } else { // get specific participants
        $users_to_join = $participants;
    }

    $row = Database::get()->querySingle("SELECT id, server_key, api_url, max_rooms, max_users
                                    FROM tc_servers WHERE id = ?d AND enabled = 'true'", $server_id);
    if ($row) {
        $max_rooms = $row->max_rooms;
        $max_users = $row->max_users;
        // get connected users
        $connected_users = get_connected_users($row->server_key, $row->api_url);
        // get active rooms
        $active_rooms = get_active_rooms($row->server_key ,$row->api_url);
        //cases
        // max_users = 0 && max_rooms = 0 - UNLIMITED
        // active_rooms < max_rooms && active_users < max_users
        // active_rooms < max_rooms && max_users = 0 (UNLIMITED)
        // active_users < max_users && max_rooms = 0 (UNLIMITED)
        if (($max_rooms == 0 && $max_users == 0)
            or (($max_users >= ($users_to_join + $connected_users)) and $active_rooms <= $max_rooms)
            or ($active_rooms <= $max_rooms and $max_users == 0)
            or (($max_users >= ($users_to_join + $connected_users)) && $max_rooms == 0)) // YOU FOUND THE SERVER
            {
                return true;
            } else {
                return false;
        }
    } else {
        return false;
    }

}

/**
 * @brief get load of all enabled BBB servers
 * @return array
 */
function get_bbb_servers_load()
{
    $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'bbb' AND `enabled` = 'true' ORDER BY weight ASC");

    $server_count = count($q);

    if ($server_count <= 0 ) {
        return false;
    }

    // weight of all participants: they get the presentation
    $participant_weight = get_config('bbb_lb_weight_part', 1);
    // weight of audio: audio is sent to listeners. double weight due to mixing in cpu
    // Notice: listeners get audio from Kurento. Microphone/voice users get audio from Freeswitch
    // We handle it the same way (for now)
    $audio_weight = get_config('bbb_lb_weight_mic', 2);
    // weight of video: video is sent to participants. double weight due to higher bandwidth and cpu of SFU/Kurento
    $video_weight = get_config('bbb_lb_weight_camera', 2);
    // each room allocates resources. take that into account
    $room_load = get_config('bbb_lb_weight_room', 50);

    $servers = array();
    foreach ($q as $server) {
        $rooms = $tparticipants = $tlisten = $tvoice = $tvideo = $load = 0;
        $bbb_url = $server->api_url;
        $salt = $server->server_key;
        $server_id = $server->id;
        // instantiate the BBB class
        $bbb = new BigBlueButton($salt, $bbb_url);

        $meetings = $bbb->getMeetingsWithXmlResponseArray();
        // no active meetings
        if (empty($meetings)) {
            continue;
        }
        foreach ($meetings as $meeting) {
            if (!isset($meeting['meetingId'])) {
                continue;
            }
            $rooms++;
            $participantCount = $meeting['participantCount'];
            if ($participantCount == 0) {
                continue;
            }
            $listenerCount = $meeting['listenerCount'];
            $voiceParticipantCount = $meeting['voiceParticipantCount'];
            $videoCount = $meeting['videoCount'];
            $moderatorCount = $meeting['moderatorCount'];

            // presentation is going to all participants
            $presentation_load = $participantCount * $participant_weight;

            // voice is mixed. streams_in: voicePart / streams_out: listenPart
            $audio_load = ($voiceParticipantCount + $listenerCount) * $audio_weight;

            // each camera by default generates a stream to all participants
            $video_load = $videoCount * $participantCount * $video_weight;

            // unless cameras are locked to be shown only to moderators/professors
            // first check if meeting is local to us
            $res = Database::get()->querySingle("SELECT tc_session.options FROM tc_session
                WHERE meeting_id = ?s AND running_at = ?d",
                $meeting['meetingId'], $server_id);
            if (!empty($res->options)) {
                $options = unserialize($res->options);
                if (isset($options['lockSettingsDisableCam'])) {
                    $video_load = $videoCount * $moderatorCount * $video_weight;
                }
            }

            $tparticipants += $participantCount;
            $tlisten += $listenerCount;
            $tvoice += $voiceParticipantCount;
            $tvideo += $videoCount;
            $load += $presentation_load + $audio_load + $video_load + $room_load;
        }

        if ($server->enable_recordings == 'true') {
            $enable_recordings = 1;
        } else {
            $enable_recordings = 0;
        }

        $servers[] = array(
            'id' => $server->id,
            'weight' => $server->weight,
            'rooms' => $rooms,
            'participants' => $tparticipants,
            'listeners' => $tlisten,
            'voice' => $tvoice,
            'video' => $tvideo,
            'load' => $load,
            'enable_recordings' => $enable_recordings,
        );

    }
    return $servers;
}

/**
 * @brief get load of all enabled BBB servers
 * @return array indexed by server id in DB
 */
function get_bbb_servers_load_by_id()
{
    $servers = get_bbb_servers_load();
    if (empty($servers)) {
        return false;
    }

    foreach ($servers as $server) {
        $arr[$server['id']]['weight'] = $server['weight'];
        $arr[$server['id']]['rooms'] = $server['rooms'];
        $arr[$server['id']]['participants'] = $server['participants'];
        $arr[$server['id']]['listeners'] = $server['listeners'];
        $arr[$server['id']]['voice'] = $server['voice'];
        $arr[$server['id']]['video'] = $server['video'];
        $arr[$server['id']]['load'] = $server['load'];
        $arr[$server['id']]['enable_recordings'] = $server['enable_recordings'];
    }
    return $arr;
}

/**
 * @brief sort servers based on load balancing algorithm
 * @param string $record sort servers by recording capability
 * @return array
 */
function get_bbb_servers($record = 'false')
{
    $servers = get_bbb_servers_load();
    if (!$servers) {
        return false;
    }

    if (count($servers) == 1) {
        $servers_ids[0] = $servers[0]['id'];
        return $servers_ids;
    }

    $weight = array_column($servers, 'weight');
    $load = array_column($servers, 'load');
    $rooms = array_column($servers, 'rooms');
    $participants = array_column($servers, 'participants');
    $listeners = array_column($servers, 'listeners');
    $microphones = array_column($servers, 'voice');
    $cameras = array_column($servers, 'video');
    $enable_recordings = array_column($servers, 'enable_recordings');

    $record_sort = ($record=='true') ? SORT_DESC : SORT_ASC;

    $bbb_lb_algo = get_config('bbb_lb_algo', 'wo');

    switch ($bbb_lb_algo) {
        // weighted least load. Sort first by recording, then by weight and last by load
        case 'wll':
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $load, SORT_ASC, SORT_NUMERIC, $servers);
            break;
        // weighted least rooms, Sort first by recording, then by weight and last by #rooms
        case 'wlr':
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $rooms, SORT_ASC, SORT_NUMERIC, $servers);
            break;
        // weighted least connections. Sort first by recording, then by weight and last by #participants
        case 'wlc':
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $participants, SORT_ASC, SORT_NUMERIC, $servers);
            break;
        // weighted least microphones. Sort first by recording, then by weight and last by #microphones
        case 'wlm':
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $microphones, SORT_ASC, SORT_NUMERIC, $servers);
            break;
        // weighted least cameras. Sort first by recording, then by weight and last by #cameras
        case 'wlv':
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $cameras, SORT_ASC, SORT_NUMERIC, $servers);
            break;
        // Default. Sort by recording and then by weight only. No distribution of load. Each server fills based
        // max number of rooms and max number of participants. Then we move to next server
        default:
            array_multisort($enable_recordings, $record_sort, SORT_NUMERIC, $weight, SORT_ASC, SORT_NUMERIC, $servers);
            break;
    }

    foreach ($servers as $server) {
       $servers_ids[] = $server['id'];
    }

    return $servers_ids;
}

/**
 * @brief check if tc server has recordings enabled
 * @param type $server_id
 * @return type
 */
function has_enable_recordings($server_id) {

    $result = Database::get()->querySingle("SELECT enable_recordings FROM tc_servers WHERE id = ?d", $server_id)->enable_recordings;

    return $result;
}

/**
 * @brief get tc title given its meeting id
 * @param type $meeting_id
 * @return type
 */
function get_tc_title($meeting_id) {

    global $course_id;

    $result = Database::get()->querySingle("SELECT title FROM tc_session
                    WHERE meeting_id = ?s AND course_id = ?d", $meeting_id, $course_id)->title;

    return $result;

}

/**
 * @brief get encoded tc meeting id given its db id
 * @param type $id
 * @return type
 */
function get_tc_meeting_id($id) {

    $result = Database::get()->querySingle("SELECT meeting_id FROM tc_session
                    WHERE id = ?d", $id)->meeting_id;

    return $result;
}

/**
 * @brief get tc meeting id given its encoded meeting id
 * @param type $meeting_id
 * @return type
 */
function get_tc_id($meeting_id) {
    $result = Database::get()->querySingle("SELECT id FROM tc_session
                    WHERE meeting_id = ?s", $meeting_id)->id;

    return $result;

}


/**
 * @brief get tc meeting session users
 * @param $meeting_id
 * @return int
 */
function get_tc_participants($meeting_id) {
    $result = Database::get()->querySingle("SELECT sessionUsers FROM tc_session
                    WHERE meeting_id = ?s", $meeting_id)->sessionUsers;

    return $result;
}
