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
 * @file chat.php
 * @brief Main script for chat module
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'chat';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$coursePath = $webDir . '/courses/';
$conference_id = $_GET['conference_id'];
$conference_activity = false;
$conference_agent = false;
$agent_id = false;
$colmooc_teacher_la_url = null;
$colmooc_student_la_url = null;
$q = Database::get()->querySingle("SELECT status, chat_activity, agent_created, agent_id FROM conference WHERE conf_id = ?d AND course_id = ?d", $conference_id, $course_id);
if ($q) { // additional security
    $conference_status = $q->status;
    $conference_activity = $q->chat_activity;
    $conference_agent = $q->agent_created;
    $agent_id = $q->agent_id;
} else {
    //Session::Messages($langForbidden, "alert-danger");
    Session::flash('message',$langForbidden); 
    Session::flash('alert-class', 'alert-danger');

    redirect_to_home_page();
}
if (!is_valid_chat_user($uid, $conference_id, $conference_status)) {
    //Session::Messages($langForbidden, "alert-danger");
    Session::flash('message',$langForbidden); 
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page();
}
if (!is_valid_activity_user($conference_activity, $conference_agent)) {
    //Session::Messages($langForbidden, "alert-danger");
    Session::flash('message',$langForbidden); 
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page();
}

  $fileChatName = $coursePath . $course_code . '/'. $conference_id. '_chat.txt';
  $tmpArchiveFile = $coursePath . $course_code . '/'. $conference_id. '_tmpChatArchive.txt';

  $nick = uid_to_name($uid);

// How many lines to show on screen
  define('MESSAGE_LINE_NB', 40);
// How many lines to keep in temporary archive
// (the rest are in the current chat file)
  define('MAX_LINE_IN_FILE', 80);

  if ($GLOBALS['language'] == 'el') {
      $timeNow = date("d-m-Y / H:i", time());
  } else {
      $timeNow = date("Y-m-d / H:i", time());
  }

  if (!file_exists($fileChatName)) {
      $fp = fopen($fileChatName, 'w') or die('<center>$langChatError</center>');
      fclose($fp);
  }

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_CHAT);
/* * *********************************** */

$toolName = $langChat;
// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'>$langNoGuest</div></div>";
    draw($tool_content, 2);
}

$head_content .= '<script type="text/javascript">
    function prepare_message() {
            document.chatForm.chatLine.value=document.chatForm.msg.value;
            document.chatForm.msg.value = "";
            document.chatForm.msg.focus();
            return true;
    }
    setTimeout(function(){
        $( "#iframe" ).attr( "src", function ( i, val ) { return val; });
    }, 2000);        
</script>';

if (!$conference_activity) {
    $colmooc_url = $chatindex_url = '';
    if (isset($_REQUEST['unit'])) {
        $save_link = "../units/view.php?course=$course_code&amp;res_type=chat_actions&amp;unit=$_REQUEST[unit]&amp;store=true&amp;conference_id=$conference_id&amp;" . generate_csrf_token_link_parameter();
        $back_link = "../units/index.php?course=$course_code&amp;id=$_REQUEST[unit]";
        $wash_link = "../units/view.php?course=$course_code&amp;res_type=chat_actions&amp;unit=$_REQUEST[unit]&amp;reset=true&amp;conference_id=$conference_id&amp;" . generate_csrf_token_link_parameter();
    } else {
        $save_link = "messageList.php?course=$course_code&amp;store=true&amp;conference_id=$conference_id&amp;" . generate_csrf_token_link_parameter();
        $back_link = "index.php?course=$course_code";
        $wash_link = "messageList.php?course=$course_code&amp;reset=true&amp;conference_id=$conference_id&amp;" . generate_csrf_token_link_parameter();
    }
    $tool_content .= action_bar(array(
        array('title' => $langSave,
            'url' => $save_link,
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success',
            'link-attrs' => "target='messageList'",
            'show' => $is_editor
        ),
        array('title' => $langBack,
            'url' => $back_link,
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        ),
        array('title' => $langWash,
            'url' => $wash_link,
            'icon' => 'fa-trash',
            'level' => 'primary',
            'link-attrs' => "target='messageList'",
            'show' => $is_editor
        )
    ));

    if (isset($_REQUEST['unit'])) {
        $action_form = "../units/view.php?course=$course_code&amp;res_type=chat_actions&amp;unit=$_REQUEST[unit]";
        $iframe_file = "../units/view.php?course=$course_code&amp;res_type=chat_actions&amp;unit=$_REQUEST[unit]&amp;conference_id=$conference_id";
    } else {
        $action_form = "messageList.php";
        $iframe_file = "messageList.php?course=$course_code&amp;conference_id=$conference_id";
    }
    $tool_content .= "<div class='col-12'><div class='alert alert-info'>$langTypeMessage</div></div>
       <div class='col-12'><div class='form-wrapper form-edit p-3 rounded'>
       <form name='chatForm' action='$action_form' method='POST' target='messageList' onSubmit='return prepare_message();'>
       <input type='hidden' name='course' value='$course_code'>
       <input type='hidden' name='conference_id' value='$conference_id'>
       <fieldset>
        <div class='col-12'>
            <div class='input-group'>
              <input type='text' placeholder='$typeyourmessage...' name='msg' size='80' class='form-control'>
              <input type='hidden' name='chatLine'>
              <span class='input-group-btn mt-2'>
                <input class='btn btn-success' type='submit' value='&raquo;'>
              </span>
            </div>
            <div class='embed-responsive embed-responsive-4by3 margin-top-fat mt-3'>
              <iframe class='embed-responsive-item' id='iframe' src='$iframe_file' name='messageList' style='border: 1px solid #CAC3B5;width:100%;overflow-x: hidden;'></iframe>
            </div>       
        </div>   
       </fieldset>
       " . generate_csrf_token_form_field() . "
       </form></div></div>";
} else {
    if ($is_editor && isset($_GET['create_agent'])) {
        $agent_id = colmooc_create_agent($conference_id);
        if ($agent_id) {
            Database::get()->querySingle("UPDATE conference SET agent_id = ?d WHERE conf_id = ?d", $agent_id, $conference_id);
            $conference_agent = true;
        }
    }
    $sessionId = false;
    $sessionToken = false;
    if (!$is_editor) {
        list($sessionId, $sessionToken) = colmooc_register_student($conference_id); // STEP 2
    }
    $laSessionId = false;
    $laSessionToken = false;
    if ($is_editor && $conference_agent && $agent_id && !isset($_GET['create_agent']) && !isset($_GET['edit_agent'])) {
        list($laSessionId, $laSessionToken) = colmooc_add_teacher_lasession();
        if ($laSessionId && $laSessionToken) {
            // Redirect teacher to colMOOC learning analytics
            $colmooc_teacher_la_url = $colmoocapp->getParam(ColmoocApp::ANALYTICS_URL)->value() . "/index.php?lasession_id=" . $laSessionId . "&lasession_token=" . $laSessionToken;
        }
    } else if (!$is_editor && $conference_agent && $agent_id) {
        list($laSessionId, $laSessionToken) = colmooc_add_student_lasession();
        if ($laSessionId && $laSessionToken) {
            // Redirect student to colMOOC learning analytics
            $colmooc_student_la_url = $colmoocapp->getParam(ColmoocApp::ANALYTICS_URL)->value() . "/index.php?lasession_id=" . $laSessionId . "&lasession_token=" . $laSessionToken;
        }
    }

    $tool_content .= action_bar(array(
        array('title' => $langCreateAgent,
            'url' => "chat.php?conference_id=" . $conference_id . "&create_agent=1",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success',
            'show' => $is_editor && !$conference_agent
        ),
        array('title' => $langEditAgent,
            'url' => "chat.php?conference_id=" . $conference_id . "&edit_agent=1",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success',
            'show' => $is_editor && $conference_agent && $agent_id
        ),
        array('title'=> $langColmoocPairLog,
            'url' => "chat.php?conference_id=" . $conference_id . "&pair_log=1",
            'level' => 'primary-label',
            'show' => $is_editor && $conference_agent && $agent_id && !isset($_GET['pair_log'])),
        array('title'=> $langColmoocCompletionsLog,
            'url' => "chat.php?conference_id=" . $conference_id,
            'level' => 'primary-label',
            'show' => $is_editor && $conference_agent && $agent_id && isset($_GET['pair_log'])),
        array('title' => $langLearningAnalytics,
            'url' => '#',
            'level' => 'primary-label',
            'button-class' => 'btn-default teacherLearningAnalytics',
            'show' => $is_editor && $laSessionId && $laSessionToken),
        array('title' => $langChat,
            'url' => '#',
            'level' => 'primary-label',
            'button-class' => 'btn-success studentChat',
            'show' => !$is_editor && $sessionId && $sessionToken),
        array('title' => $langLearningAnalytics,
            'url' => '#',
            'level' => 'primary-label',
            'button-class' => 'btn-default studentLearningAnalytics',
            'show' => !$is_editor && $laSessionId && $laSessionToken),
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        )
    ));

    if ($is_editor && isset($_GET['create_agent']) && $agent_id) {
        // Redirect teacher to colMOOC editor with agent_id & teacher_id parameters
        $colmooc_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/editor/index.php?agent_id=" . $agent_id . '&teacher_id=' . $uid;
        redirect_to_home_page($colmooc_url, true);
    } else if ($is_editor && isset($_GET['edit_agent']) && $agent_id) {
        // Redirect teacher to colMOOC editor with agent_id & teacher_id parameters
        $colmooc_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/editor/index.php?agent_id=" . $agent_id . '&teacher_id=' . $uid;
        redirect_to_home_page($colmooc_url, true);
    } else if ($is_editor && isset($_GET['create_agent']) && !$agent_id) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'>" . $langColmoocCreateAgentFailed . "</div></div>";
    } else if ($is_editor && !isset($_GET['create_agent']) && !isset($_GET['edit_agent']) && isset($_GET['pair_log'])) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>" . $langColMoocAgentCreateOrEdit . "</div></div>";
        $q = Database::get()->queryArray("select cpl.moderator_id, cpl.partner_id, cpl.session_status, cpl.created
            from colmooc_pair_log cpl
            join conference c on (c.chat_activity_id = cpl.activity_id)
            where c.course_id = ?d and c.conf_id = ?d
            order by cpl.created desc", $course_id, $conference_id);
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th style='width:5%'>$langID</th>
                    <th>$langColmoocModerator</th>
                    <th>$langColmoocPartner</th>
                    <th class='text-center' width='250'>$langNewBBBSessionStatus</th>
                    <th class='text-center' width='200'>$langDate</th>";
        $tool_content .="</tr></thead>";

        $cnt = 1;
        foreach ($q as $cpl) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>". $cnt++ . "</td>";
            $tool_content .= "<td>" . display_user($cpl->moderator_id) . "</td>";
            $tool_content .= "<td>" . display_user($cpl->partner_id) . "</td>";
            $tool_content .= "<td class='text-center'>" . display_session_status($cpl->session_status) . "</td>";
            $tool_content .= "<td class='text-center'>" . format_locale_date(strtotime($cpl->created), 'short') . "</td>";
            $tool_content .= "</tr>";
        }

        $tool_content .= "</table></div>";
    } else if ($is_editor && !isset($_GET['create_agent']) && !isset($_GET['edit_agent']) && !isset($_GET['pair_log'])) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>" . $langColMoocAgentCreateOrEdit . "</div></div>";
        $q1 = Database::get()->queryArray("select cus.user_id, cus.session_status, cus.session_status_updated 
            from colmooc_user_session cus 
            join conference c on (c.chat_activity_id = cus.activity_id) 
            join user u on (u.id = cus.user_id)
            where c.course_id = ?d and c.conf_id = ?d and cus.session_status = 1
            order by u.surname, u.givenname", $course_id, $conference_id);
        $q2 = Database::get()->queryArray("select cus.user_id, cus.session_status, cus.session_status_updated
            from colmooc_user_session cus
            join conference c on (c.chat_activity_id = cus.activity_id)
            join user u on (u.id = cus.user_id)
            where c.course_id = ?d and c.conf_id = ?d and cus.session_status <> 1
            order by u.surname, u.givenname", $course_id, $conference_id);
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th style='width:5%'>$langID</th>
                    <th>$langSurname $langName</th>
                    <th class='text-center' width='250'>$langNewBBBSessionStatus</th>
                    <th class='text-center' width='200'>$langDate</th>
                    <th class='text-center'>$langColMoocCompletions</th>";
        $tool_content .="</tr></thead>";

        $cnt = 1;
        foreach ($q1 as $cus) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>". $cnt++ . "</td>";
            $tool_content .= "<td>" . display_user($cus->user_id) . "</td>";
            $tool_content .= "<td class='text-center'>" . display_session_status($cus->session_status) . "</td>";
            $tool_content .= "<td class='text-center'>" . format_locale_date(strtotime($cus->session_status_updated), 'short') . "</td>";
            $tool_content .= "<td class='text-center'>" . display_finished_count($cus->user_id) . "</td>";
            $tool_content .= "</tr>";
        }
        foreach ($q2 as $cus) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>". $cnt++ . "</td>";
            $tool_content .= "<td>" . display_user($cus->user_id) . "</td>";
            $tool_content .= "<td class='text-center'>" . display_session_status($cus->session_status) . "</td>";
            $tool_content .= "<td class='text-center'>" . format_locale_date(strtotime($cus->session_status_updated), 'short') . "</td>";
            $tool_content .= "<td class='text-center'>" . display_finished_count($cus->user_id) . "</td>";
            $tool_content .= "</tr>";
        }

        $tool_content .= "</table></div>";
    }

    $colmooc_url = null;
    $chatindex_url = null;
    if (!$is_editor) {
        if ($sessionId && $sessionToken) {
            // Redirect student to colMOOC chat
            $colmooc_url = $colmoocapp->getParam(ColmoocApp::CHAT_URL)->value() . "/index.php?session_id=" . $sessionId . "&session_token=" . $sessionToken;
            if (isset($_REQUEST['unit'])) {
                $chatindex_url = $urlAppend . "modules/units/index.php?course=" . $course_code . "&id=" . intval($_REQUEST['unit']);
            } else {
                $chatindex_url = $urlAppend . "modules/chat/index.php";
            }
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>" . $langColmoocFollowLink1
                . ' <a class="studentChat" href="#" title="' . $langChat . '">' . $langChat . '</a> '
                . $langColmoocFollowLink2 . "</div></div>";

            $cus = Database::get()->querySingle("select cus.user_id, cus.session_status, cus.session_status_updated 
                from colmooc_user_session cus 
                join conference c on (c.chat_activity_id = cus.activity_id) 
                where c.course_id = ?d and c.conf_id = ?d and cus.user_id = ?d", $course_id, $conference_id, $uid);
            if ($cus) {
                $tool_content .= "<div class='table-responsive'>";
                $tool_content .= "<table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langName $langSurname</th>
                    <th class = 'text-center' width='250'>$langNewBBBSessionStatus</th>
                    <th class = 'text-center' width='200'>$langDate</th>";
                $tool_content .= "</tr></thead>";
                $tool_content .= "<tr>";
                $tool_content .= "<td>" . display_user($cus->user_id) . "</td>";
                $tool_content .= "<td class='text-center'>" . display_session_status($cus->session_status) . "</td>";
                $tool_content .= "<td class='text-center'>" . format_locale_date(strtotime($cus->session_status_updated), 'short') . "</td>";
                $tool_content .= "</tr>";
                $tool_content .= "</table></div>";
            }
        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>" . $langColmoocRegisterStudentFailed . "</div></div>";
        }
    }
}

load_js('screenfull/screenfull.min.js');
$head_content .= "<script>
    $(function(){
        $('.fileModal').click(function (e)
        {
            e.preventDefault();
            var fileURL = $(this).attr('href');
            var fileTitle = $(this).attr('title');
            
            // BUTTONS declare
            var bts = {};
            if (screenfull.enabled) {
                bts.fullscreen = {
                    label: '<i class=\"fa fa - arrows - alt\"></i> $langFullScreen',
                    className: 'btn-primary',
                    callback: function() {
                        screenfull.request(document.getElementById('fileFrame'));
                        return false;
                    }
                };
            }
            bts.cancel = {
                label: '$langCancel',
                className: 'btn-default'
            };
            
            bootbox.dialog({
                size: 'large',
                title: fileTitle,
                message: '<div class=\"row\">'+
                            '<div class=\"col-sm-12\">'+
                                '<div class=\"iframe-container\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\"></iframe></div>'+
                            '</div>'+
                        '</div>',
                buttons: bts
            });
        });
        
        $('.studentChat').click(function (e) {
            window.open('" . $colmooc_url . "', '_blank');
            window.location.href = '" . $chatindex_url . "';
        });
        
        $('.teacherLearningAnalytics').click(function (e) {
            window.open('" . $colmooc_teacher_la_url . "', '_blank');
        });
        
        $('.studentLearningAnalytics').click(function (e) {
            window.open('" . $colmooc_student_la_url . "', '_blank');
        });
    });
    </script>";

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
