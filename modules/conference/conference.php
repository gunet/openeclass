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
$require_help = TRUE;
$helpTopic = 'Conference';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$coursePath = $webDir . '/courses/';
$conference_id = $_GET['conference_id'];
$q = Database::get()->querySingle("SELECT status FROM conference WHERE conf_id = ?d AND course_id = ?d", $conference_id, $course_id);
if ($q) { // additional security
    $conference_status = $q->status;
} else {
    Session::Messages($langForbidden, "alert-danger");
    redirect_to_home_page("modules/conference/index.php?course=$course_code");
}
if (!is_valid_chat_user($uid, $conference_id, $conference_status)) {
  Session::Messages($langForbidden, "alert-danger");
  redirect_to_home_page("modules/conference/index.php?course=$course_code");
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
    $tool_content .= "<div class='alert alert-danger'>$langNoGuest</div>";
    draw($tool_content, 2, 'conference');
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

$tool_content .= action_bar(array(
    array('title' => $langSave,
        'url' => "messageList.php?course=$course_code&amp;store=true&amp;conference_id=$conference_id&amp;".generate_csrf_token_link_parameter(),
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success',
        'link-attrs' => "target='messageList'",
        'show' => $is_editor
    ),
    array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label'
    ),
    array('title' => $langWash,
        'url' => "messageList.php?course=$course_code&amp;reset=true&amp;conference_id=$conference_id&amp;".generate_csrf_token_link_parameter(),
        'icon' => 'fa-trash',
        'level' => 'primary',
        'link-attrs' => "target='messageList'",
        'show' => $is_editor
        )
));

$tool_content .= "<div class='alert alert-info'>$langTypeMessage</div>
   <div class='row'><div class='col-sm-12'><div class='form-wrapper'>
   <form name='chatForm' action='messageList.php' method='POST' target='messageList' onSubmit='return prepare_message();'>
   <input type='hidden' name='course' value='$course_code'/>
   <input type='hidden' name='conference_id' value='$conference_id'/>
   <fieldset>
    <div class='col-xs-12'>
        <div class='input-group'>
          <input type='text' name='msg' size='80' class='form-control'>
          <input type='hidden' name='chatLine'>
          <span class='input-group-btn'>
            <input class='btn btn-primary' type='submit' value='&raquo;'>
          </span>
        </div>
        <div class='embed-responsive embed-responsive-4by3 margin-top-fat'>
          <iframe class='embed-responsive-item' id='iframe' src='messageList.php?course=$course_code&amp;conference_id=$conference_id' name='messageList' style='border: 1px solid #CAC3B5;width:100%;overflow-x: hidden;'></iframe>
        </div>       
    </div>   
   </fieldset>
   ". generate_csrf_token_form_field() ."
   </form></div></div></div>";

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
