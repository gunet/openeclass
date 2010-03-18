<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Conference';
$tool_content = "";
include '../../include/baseTheme.php';

if(!isset($MCU))
	$MCU="";

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_CHAT');
/**************************************/

$nameTools = $langConference;


// guest user not allowed
if (check_guest()) {
	$tool_content .= "
       <table width=\"99%\">
       <tbody>
       <tr>
         <td class=\"extraMessage\"><p>$langNoGuest</p></td>
       </tr>
       </tbody>
       </table>";
	draw($tool_content, 2, 'conference');
}

if (!($uid) or !($_SESSION['uid'])) {
	$tool_content .= "
       <table width=\"99%\">
       <tbody>
       <tr>
         <td class=\"extraMessage\"><p>$langNoAliens</p></td>
       </tr>
       </tbody>
       </table>";
	draw($tool_content, 2, 'conference');
}

$head_content = '<script type="text/javascript">
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>';

if ($is_adminOfCourse) {
    $tool_content .= "
      <div id=\"operations_container\">
        <ul id=\"opslist\">
          <li><a href='messageList.php?reset=true' target='messageList' class=small_tools>$langWash</a></li>
          <li><a href='messageList.php?store=true' target='messageList' class=small_tools>$langSave</a></li>
        </ul>
      </div>";
}

$tool_content .= "
<form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepare_message();'>
  <table width='99%' class='FormData'>
  <thead>
  <tr>
    <th>&nbsp;</th>
    <td>

      <b>$langTypeMessage</b><br />
      <input type='text' name='msg' size='80'style='border: 1px solid #CAC3B5; background: #fbfbfb;'>
      <input type='hidden' name='chatLine'>
      <input type='submit' value=' >> '>

    </td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><iframe frameborder='0' src='messageList.php' width='100%' height='300' name='messageList' style='background: #fbfbfb; border: 1px solid #CAC3B5;'><a href='messageList.php'>Message list</a></iframe></td>
  </tr>
  </thead>
  </table>
</form>
  ";
add_units_navigation(TRUE);
draw($tool_content, 2, 'conference', $head_content);
