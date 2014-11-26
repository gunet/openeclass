<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_CHAT);
/* * *********************************** */

$nameTools = $langConference;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='alert alert-danger'>$langNoGuest</div>";
    draw($tool_content, 2, 'conference');
}

$head_content = '<script type="text/javascript">
function prepare_message() {
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>';

if ($is_editor) {
    $tool_content .= action_bar(array(
        array('title' => $langSave,
            'url' => "messageList.php?course=$course_code&amp;store=true",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ),
        array('title' => $langSave,
            'url' => "messageList.php?course=$course_code&amp;reset=true",
            'icon' => 'fa-university',
            'level' => 'primary'
            )
    ));
}

$tool_content .= "
    <div class='row'><div class='col-sm-12'><div class='form-wrapper'>
   <form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepare_message();'><input type='hidden' name='course' value='$course_code'/>
   <fieldset>
    <legend>$langTypeMessage</legend>
    <div class='col-xs-12'>
        <div class='input-group'>
          <input type='text' name='msg' size='80' class='form-control'>
          <input type='hidden' name='chatLine'>
          <span class='input-group-btn'>
            <input class='btn btn-primary' type='submit' value='&raquo;'>
          </span>
        </div>
        <div class='embed-responsive embed-responsive-4by3 margin-top-fat'>
          <iframe class='embed-responsive-item' src='messageList.php' name='messageList' style='border: 1px solid #CAC3B5;'></iframe>
        </div>       
    </div>   
   </fieldset>
   </form></div></div></div>";

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
