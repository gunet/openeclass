<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'Conference';
$tool_content = "";
include '../../include/baseTheme.php';

if (!isset($MCU)) {
	$MCU="";
}

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_CHAT');
/**************************************/

$nameTools = $langConference;

// guest user not allowed
if (check_guest()) {
	$tool_content .= "<p class='caution'>$langNoGuest</p>";
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
	$tool_content .= "
   <div id=\"operations_container\">
     <ul id=\"opslist\">
       <li><a href='messageList.php?course=$code_cours&amp;reset=true' target='messageList' class=small_tools>$langWash</a></li>
       <li><a href='messageList.php?course=$code_cours&amp;store=true' target='messageList' class=small_tools>$langSave</a></li>
     </ul>
   </div>";
}

$tool_content .= "
   <form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepare_message();'><input type='hidden' name='course' value='$code_cours'/>
   <fieldset>
    <legend>$langTypeMessage</legend>
      <input type='text' name='msg' size='80'>
      <input type='hidden' name='chatLine'>
      <input type='submit' value=' &raquo;  '><br /><br />
      <iframe frameborder='0' src='messageList.php' width='100%' height='300' name='messageList' style='border: 1px solid #CAC3B5;'><a href='messageList.php?course=$code_cours'>Message list</a></iframe>
   </fieldset>
   </form>";
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
