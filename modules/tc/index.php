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
$helpTopic = 'tc';
$guest_allowed = false;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
// for logging
require_once 'include/log.class.php';
// For creating bbb urls & params
require_once 'bbb-api.php';
//require_once 'om-api.php';
require_once 'functions.php';

require_once 'include/lib/modalboxhelper.class.php';
ModalBoxHelper::loadModalBox();

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_TC);
/* * *********************************** */

$toolName = $langBBB;

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('validation.js');

$head_content .= "
<script type='text/javascript'>

// Bootstrap datetimepicker Initialization
$(function() {
$('input#start_session').datetimepicker({
        format: 'dd-mm-yyyy hh:ii',
        pickerPosition: 'bottom-right',
        language: '".$language."',
        autoclose: true
    });
});

</script>";

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#BBBEndDate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            }).on('changeDate', function(ev){
                if($(this).attr('id') === 'BBBEndDate') {
                    $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                }
            }).on('blur', function(ev){
                if($(this).attr('id') === 'BBBEndDate') {
                    var end_date = $(this).val();
                    if (end_date === '') {
                        if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                            $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                        }
                        $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                    }
                }
            });
            $('#enableEndDate').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#BBB'+dateType).prop('disabled', false);
                    if (dateType === 'EndDate' && $('input#BBBEndDate').val() !== '') {
                        $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                    }
                } else {
                    $('input#BBB'+dateType).prop('disabled', true);
                    if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                    $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                }
            });
        });
    </script>";

load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#popupattendance1').click(function() {
            window.open($(this).prop('href'), '', 'height=200,width=500,scrollbars=no,status=no');
            return false;
        });

        $('#select-groups').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-groups').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-groups').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-groups').val(stringVal).trigger('change');
        });
    });

    function onAddTag(tag) {
        alert('Added a tag: ' + tag);
    }
    function onRemoveTag(tag) {
        alert('Removed a tag: ' + tag);
    }

    function onChangeTag(input,tag) {
        alert('Changed a tag: ' + tag);
    }

    $(function() {
        $('#tags_1').select2({tags:[], formatNoMatches: ''});
    });
</script>
";

if ($is_editor) {
    if (isset($_GET['new'])) {
        $pageName = $langChooseTCServer;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary')));
    } elseif (isset($_GET['add']) or isset($_GET['choice'])) {
        if (isset($_GET['add'])) {
            $pageName = $langNewBBBSession;
        } elseif ((isset($_GET['choice'])) and $_GET['choice'] == 'edit') {
            $pageName = $langModify;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary')));
    } else {
        if (
            isset($_GET['id'])
            || isset($_GET['zoom_not_registered'])
        ) {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary')));
        } else {
            /* find enabled tc servers */
            $servers = [];
            foreach (get_enabled_tc_services() as $name) {
                if (is_active_tc_server($name, $course_id)) {
                   $servers[] = $name;
                }
            }
            if (count($servers) == 1) {
                $tc_server_type = $servers[0];
            } else {
                $tc_server_type = '';
            }

            $tool_content .= action_bar([
                [ 'title' => $langNewBBBSession,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                  'icon' => 'fa-plus-circle',
                  'button-class' => 'btn-success',
                  'level' => 'primary-label',
                  'show' => count($servers) > 1 ],
                [ 'title' => $langNewBBBSession,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1&amp;tc_type=$tc_server_type",
                  'icon' => 'fa-plus-circle',
                  'button-class' => 'btn-success',
                  'level' => 'primary-label',
                  'show' => !empty($tc_server_type) ],
                [ 'title' => $langActivateParticipation,
                  'url' => "tc_attendance.php?course=$course_code",
                  'icon' => 'fa-user-group',
                  'level' => 'primary-label',
                  'link-attrs' => "id=popupattendance1",
                  'show' => is_active_tc_server('bbb', $course_id) ],
                [ 'title' => $langParticipate,
                  'url' => "tcuserduration.php?course=$course_code",
                  'icon' => 'fa-clock',
                  'level' => 'primary-label' ],
                [ 'title' => $langUserDuration,
                    'url' => "tcuserduration.php?course=$course_code&amp;per_user=true",
                    'icon' => 'fa-clock',
                    'level' => 'primary-label' ],
            ]);
        }
    }
} else {
    $tool_content .= action_bar(array(
                array('title' => $langParticipate,
                          'url' => "tcuserduration.php?course=$course_code&amp;u=$_SESSION[uid]",
                          'icon' => 'fa-clock',
                          'level' => 'primary-label')
                ));
}

if (isset($_GET['add'])) {
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);
    tc_session_form(0, $_GET['tc_type']);
}
elseif(isset($_POST['update_tc_session'])) { // update existing BBB session
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $tc_type = $_GET['tc_type'];
    if (isset($_POST['enableEndDate']) and ($_POST['enableEndDate'])) {
        $endDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['BBBEndDate']);
        $end = $endDate_obj->format('Y-m-d H:i:s');
    } else {
        $end = NULL;
    }

    $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_session']);
    $start = $startDate_obj->format('Y-m-d H:i:s');
    $notifyUsers = $addAnnouncement = $notifyExternalUsers = 0;
    if (isset($_POST['notifyUsers']) and $_POST['notifyUsers']) {
        $notifyUsers = 1;
    }
    if (isset($_POST['notifyExternalUsers']) and $_POST['notifyExternalUsers']) {
        $notifyExternalUsers = 1;
    }
    if (isset($_POST['addAnnouncement']) and $_POST['addAnnouncement']) {
        $addAnnouncement = 1;
    }
    $record = 'false';
    if (isset($_POST['record'])) {
        $record = $_POST['record'];
    }
    if (isset($_POST['external_users']) && is_array($_POST['external_users'])) {
        $ext_users = implode(',', $_POST['external_users']);
    } else {
        $ext_users = null;
    }

    $options_arr = array();
    if (isset($_POST['muteOnStart']) and $_POST['muteOnStart']) {
        $options_arr['muteOnStart'] = 1;
    }
    if (isset($_POST['lockSettingsDisableMic']) and $_POST['lockSettingsDisableMic']) {
        $options_arr['lockSettingsDisableMic'] = 1;
    }
    if (isset($_POST['lockSettingsDisableCam']) and $_POST['lockSettingsDisableCam']) {
        $options_arr['lockSettingsDisableCam'] = 1;
    }
    if (isset($_POST['webcamsOnlyForModerator']) and $_POST['webcamsOnlyForModerator']) {
        $options_arr['webcamsOnlyForModerator'] = 1;
    }
    if (isset($_POST['lockSettingsDisablePrivateChat']) and $_POST['lockSettingsDisablePrivateChat']) {
        $options_arr['lockSettingsDisablePrivateChat'] = 1;
    }
    if (isset($_POST['lockSettingsDisablePublicChat']) and $_POST['lockSettingsDisablePublicChat']) {
        $options_arr['lockSettingsDisablePublicChat'] = 1;
    }
    if (isset($_POST['lockSettingsDisableNote']) and $_POST['lockSettingsDisableNote']) {
        $options_arr['lockSettingsDisableNote'] = 1;
    }
    if (isset($_POST['lockSettingsHideUserList']) and $_POST['lockSettingsHideUserList']) {
        $options_arr['lockSettingsHideUserList'] = 1;
    }
    if (isset($_POST['hideParticipants']) and $_POST['hideParticipants']) {
        $options_arr['hideParticipants'] = 1;
    }
    if (count($options_arr) > 0) {
        $options = serialize($options_arr);
    } else {
        $options = NULL;
    }

    $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
    $sessionUsers = $_POST['sessionUsers'];
    if (!empty($bbb_max_part_per_room) and ($sessionUsers > $bbb_max_part_per_room)) {
        $sessionUsers = $bbb_max_part_per_room;
    }

    // update existing BBB session
    add_update_tc_session($tc_type, $_POST['title'], $_POST['desc'], $start, $end, $_POST['status'], $notifyUsers, $notifyExternalUsers, $addAnnouncement, $_POST['minutes_before'], $ext_users, $record, $sessionUsers, $options, true, getDirectReference($_POST['id']));
    Session::flash('message',$langBBBAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect("index.php?course=$course_code");
}
elseif(isset($_GET['choice']))
{
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);
    switch($_GET['choice'])
    {
        case 'edit':
            tc_session_form(getDirectReference($_GET['id']), $_GET['tc_type']);
            break;
        case 'do_delete':
            delete_tc_session(getDirectReference($_GET['id']));
            break;
        case 'do_disable':
            disable_tc_session(getDirectReference($_GET['id']));
            break;
        case 'do_enable':
            enable_tc_session(getDirectReference($_GET['id']));
            break;
        case 'do_join':
            //get info
            $sess = Database::get()->querySingle("SELECT * FROM tc_session WHERE meeting_id=?s", $_GET['meeting_id']);
            $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);
            if ($serv->type == 'bbb') { // if tc server is `bbb`
                $mod_pw = $sess->mod_pw;
                $record = $sess->record;
                if (bbb_session_running($_GET['meeting_id']) == false) { // create meeting
                    $title_meeting = "$_GET[title] - $course_code";
                    if (!empty($public_code)) {
                        $title_meeting = "$_GET[title] - $public_code";
                    }
                    if (!empty($sess->options)) {
                        $options = unserialize($sess->options);
                    } else {
                        $options = NULL;
                    }
                   $now = date('Y-m-d H:i:s');
                   if ($sess->active <> '1') {
                        //Session::Messages($langBBBDisabled, 'alert-danger');
                        Session::flash('message',$langBBBDisabled);
                        Session::flash('alert-class', 'alert-danger');
                        redirect("index.php?course=$course_code");
                   } else if (date_diff_in_minutes($sess->start_date, $now) > $sess->unlock_interval) {
                        //Session::Messages($langBBBNotStarted, 'alert-danger');
                        Session::flash('message',$langBBBNotStarted);
                        Session::flash('alert-class', 'alert-danger');
                        redirect("index.php?course=$course_code");
                   } else if (!empty($sess->end_date) and date_diff_in_minutes($now, $sess->end_date) > 0) {
                       // Session::Messages($langBBBHasEnded, 'alert-danger');
                        Session::flash('message',$langBBBHasEnded);
                        Session::flash('alert-class', 'alert-danger');
                        redirect("index.php?course=$course_code");
                   } else {
                        create_bbb_meeting($title_meeting, $_GET['meeting_id'], $mod_pw, $_GET['att_pw'], $record, $options);
                   }
                }
                if (isset($_GET['mod_pw'])) { // join moderator (== $is_editor)
                    header('Location: ' . bbb_join_moderator($_GET['meeting_id'], $_GET['mod_pw'], $_GET['att_pw'], $_SESSION['surname'], $_SESSION['givenname']));
                } else {
                    $ssUsers = get_meeting_users($serv->server_key, $serv->api_url, $_GET['meeting_id'], $mod_pw);
                    if (($sess->sessionUsers > 0) && ($sess->sessionUsers < $ssUsers)) { // session is full
                        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langBBBMaxUsersJoinError</span></div>";
                    } else { // join users
                        header('Location: ' . bbb_join_user($_GET['meeting_id'], $_GET['att_pw'], $_SESSION['surname'], $_SESSION['givenname']));
                    }
                }
            } elseif ($serv->type == 'jitsi' or $serv->type == 'googlemeet' or $serv->type == 'microsoftteams') { // if tc server is `jitsi` or Google Meet' or 'Microsoft Teams'
                header("Location: " . $serv->hostname . $sess->meeting_id);
            } elseif ($serv->type == 'zoom') { // zoom
                $course_user = Database::get()->querySingle("SELECT * FROM course_user 
                                                                WHERE user_id = " . $uid . " 
                                                                AND course_id = " . $course_id);
                if (
                    !empty($serv->webapp)
                    && $serv->webapp == 'api'
                ) {
                    if (
                        $course_user
                        && $course_user->editor
                    ) {
                        header("Location: " . unserialize($sess->options));
                    } else {
                        header("Location: " . rtrim($serv->hostname, '/') . '/j/'. $sess->meeting_id . '?pwd=' . $sess->mod_pw);
                    }
                } else {
                    header("Location: " . rtrim($serv->hostname, '/') . $sess->meeting_id  . '/?pwd=' . $sess->mod_pw);
                }
            } elseif ($serv->type == 'webex') { // webex
                header("Location: " . $sess->meeting_id);
            }
            break;
        case 'import_video':
            publish_video_recordings($course_code, getDirectReference($_GET['id']));
            break;
    }

} elseif (isset($_POST['new_tc_session'])) { // new TC session
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $tc_type = $_GET['tc_type'];
    $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_session']);
    $start = $startDate_obj->format('Y-m-d H:i:s');
    if (isset($_POST['enableEndDate']) and ($_POST['enableEndDate']) and !empty($_POST['BBBEndDate'])) {
        $endDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['BBBEndDate']);
        $end = $endDate_obj->format('Y-m-d H:i:s');
    } else {
        $end = NULL;
    }
    $notifyUsers = $notifyExternalUsers = $addAnnouncement = 0;
    if (isset($_POST['notifyUsers']) and $_POST['notifyUsers']) {
        $notifyUsers = 1;
    }
    if (isset($_POST['notifyExternalUsers']) and $_POST['notifyExternalUsers']) {
        $notifyExternalUsers = 1;
    }
    if (isset($_POST['addAnnouncement']) and $_POST['addAnnouncement']) {
        $addAnnouncement = 1;
    }
    $record = 'false';
    if (isset($_POST['record'])) {
        $record = $_POST['record'];
    }
    if (isset($_POST['external_users']) && is_array($_POST['external_users'])) {
        $external_users = implode(',', $_POST['external_users']);
    } else {
        $external_users = NULL;
    }

    $options_arr = array();
    if (isset($_POST['muteOnStart']) and $_POST['muteOnStart']) {
        $options_arr['muteOnStart'] = 1;
    }
    if (isset($_POST['lockSettingsDisableMic']) and $_POST['lockSettingsDisableMic']) {
        $options_arr['lockSettingsDisableMic'] = 1;
    }
    if (isset($_POST['lockSettingsDisableCam']) and $_POST['lockSettingsDisableCam']) {
        $options_arr['lockSettingsDisableCam'] = 1;
    }
    if (isset($_POST['webcamsOnlyForModerator']) and $_POST['webcamsOnlyForModerator']) {
        $options_arr['webcamsOnlyForModerator'] = 1;
    }
    if (isset($_POST['lockSettingsDisablePrivateChat']) and $_POST['lockSettingsDisablePrivateChat']) {
        $options_arr['lockSettingsDisablePrivateChat'] = 1;
    }
    if (isset($_POST['lockSettingsDisablePublicChat']) and $_POST['lockSettingsDisablePublicChat']) {
        $options_arr['lockSettingsDisablePublicChat'] = 1;
    }
    if (isset($_POST['lockSettingsDisableNote']) and $_POST['lockSettingsDisableNote']) {
        $options_arr['lockSettingsDisableNote'] = 1;
    }
    if (isset($_POST['lockSettingsHideUserList']) and $_POST['lockSettingsHideUserList']) {
        $options_arr['lockSettingsHideUserList'] = 1;
    }
    if (isset($_POST['hideParticipants']) and $_POST['hideParticipants']) {
        $options_arr['hideParticipants'] = 1;
    }
    if (count($options_arr) > 0) {
        $options = serialize($options_arr);
    } else {
        $options = NULL;
    }

    if ($tc_type == 'googlemeet') {
        $options = $_POST['google_meet_link'];
    } if ($tc_type == 'microsoftteams') {
        $options = $_POST['microsoft_teams_link'];
    } elseif ($tc_type == 'zoom') {
        $options = "";
        if (!empty($_POST['zoom_link'])) {
            $options = $_POST['zoom_link'];
        }
    } elseif ($tc_type == 'webex') {
        $options = "$_POST[webex_link]";
    }

    $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
    $sessionUsers = $_POST['sessionUsers'];
    if (!empty($bbb_max_part_per_room) and ($sessionUsers > $bbb_max_part_per_room)) {
        $sessionUsers = $bbb_max_part_per_room;
    }

    // new TC session
    add_update_tc_session($tc_type, $_POST['title'], $_POST['desc'], $start, $end, $_POST['status'], $notifyUsers, $notifyExternalUsers, $addAnnouncement, $_POST['minutes_before'], $external_users, $record, $sessionUsers, $options, false);
    Session::flash('message',$langBBBAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/tc/index.php?course=$course_code");
}elseif (isset($_GET['new'])) {
    select_tc_server($course_id);
} elseif (isset($_GET['zoom_not_registered'])) {
    show_zoom_registration();
} elseif (isset($_GET['register_zoom_user'])) {
    register_zoom_user();
}else { // display list of conferences
    tc_session_details();
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
