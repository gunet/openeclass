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
require_once 'include/sendMail.inc.php';
// For creating bbb urls & params
require_once 'bbb-api.php';
require_once 'functions.php';

require_once 'include/lib/modalboxhelper.class.php';
ModalBoxHelper::loadModalBox();

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_BBB);
/* * *********************************** */

$toolName = $langBBB;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='alert alert-danger'>$langNoGuest</div>";
    draw($tool_content, 2);
}
load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('validation.js');

$head_content .= "
<script type='text/javascript'>
$(function() {
$('#start_session').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
        language: '".$language."',
        autoclose: true
    });
});
</script>";
load_js('select2');
         
$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
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
    if (isset($_GET['add']) or isset($_GET['choice'])) {
        if (isset($_GET['add'])) {  
            $pageName = $langNewBBBSession;
        } elseif ((isset($_GET['choice'])) and $_GET['choice'] == 'edit') {
            $pageName = $langModify;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } else {
        if (isset($_GET['id'])) {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
        } else {
            $tool_content .= action_bar(array(
                array('title' => $langNewBBBSession,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label',
                      'show' => get_total_bbb_servers())));
        }
    }    
}

if (isset($_GET['add'])) {        
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);
    new_bbb_session();
}
elseif(isset($_POST['update_bbb_session']))
{
    $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_session']);
    $start = $startDate_obj->format('Y-m-d H:i:s');   
    //update_bbb_session($_GET['id'],$_POST['title'], $_POST['desc'], $start, $_POST['type'] ,$_POST['status'],(isset($_POST['notifyUsers']) ? '1' : '0'),$_POST['minutes_before'],$_POST['external_users'],$_POST['record'],$_POST['sessionUsers']);
    update_bbb_session($_GET['id'],$_POST['title'], $_POST['desc'], $start, '0' ,$_POST['status'],(isset($_POST['notifyUsers']) ? '1' : '0'),$_POST['minutes_before'],$_POST['external_users'],$_POST['record'],$_POST['sessionUsers']);
    Session::Messages($langBBBAddSuccessful, 'alert-success');
    redirect("index.php?course=$course_code");
}
elseif(isset($_GET['choice']))
{
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);
    switch($_GET['choice'])
    {
        case 'edit':
            edit_bbb_session($_GET['id']);
            break;
        case 'do_delete':
            delete_bbb_session($_GET['id']);
            break;
        case 'do_disable':
            disable_bbb_session($_GET['id']);
            break;
        case 'do_enable':
            enable_bbb_session($_GET['id']);
            break;
        case 'do_join':
            #check if there is any record-capable bbb server. Otherwise notify users
            if($_GET['record']=='true' && Database::get()->querySingle("SELECT count(*) count FROM bbb_servers WHERE enabled='true' AND enable_recordings='true'")->count == 0)
            {
                $tool_content .= "<div class='alert alert-warning'>$langBBBNoServerForRecording</div>";
                break;
            }
            if(bbb_session_running($_GET['meeting_id']) == false)
            {
                $mod_pw = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->mod_pw;
                create_meeting($_GET['title'],$_GET['meeting_id'],$mod_pw,$_GET['att_pw'],$_GET['record']);
            }
            if(isset($_GET['mod_pw']))
            {
                header('Location: ' . bbb_join_moderator($_GET['meeting_id'],$_GET['mod_pw'],$_GET['att_pw'],$_SESSION['surname'],$_SESSION['givenname']));
            }else
            {
                # Get session capacity
                $c = Database::get()->querySingle("SELECT sessionUsers FROM bbb_session where meeting_id=?s",$_GET['meeting_id']);
                $sess = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id']);
                $serv = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id=?d", $sess->running_at);

                if( ($c->sessionUsers > 0) && ($c->sessionUsers < get_meeting_users($serv->server_key,$serv->api_url,$_GET['meeting_id'],$sess->mod_pw)))
                {
                    $tool_content .= "<div class='alert alert-warning'>$langBBBMaxUsersJoinError</div>";
                    break;
                }                
                else {
                    header('Location: ' . bbb_join_user($_GET['meeting_id'],$_GET['att_pw'],$_SESSION['surname'],$_SESSION['givenname']));
                }
            }
            break;
        case 'import_video':
            publish_video_recordings($course_code,$_GET['id']);
            break;
    } 
   
} elseif(isset($_POST['new_bbb_session'])) {
    $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_session']);
    $start = $startDate_obj->format('Y-m-d H:i:s');    
    //add_bbb_session($course_id,$_POST['title'], $_POST['desc'], $start, $_POST['type'] ,$_POST['status'],(isset($_POST['notifyUsers']) ? '1' : '0'),$_POST['minutes_before'],$_POST['external_users'], $_POST['record'], $_POST['sessionUsers']);
    add_bbb_session($course_id,$_POST['title'], $_POST['desc'], $start, '0' ,$_POST['status'],(isset($_POST['notifyUsers']) ? '1' : '0'),$_POST['minutes_before'],$_POST['external_users'], $_POST['record'], $_POST['sessionUsers']);
    Session::Messages($langBBBAddSuccessful, 'alert-success'); 
    redirect_to_home_page("modules/bbb/index.php?course=$course_code");
}
else {    
    bbb_session_details();
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
