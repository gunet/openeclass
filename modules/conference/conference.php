<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================
*/

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
          <li><img src='../../template/classic/img/clean.gif'>&nbsp;<a href='messageList.php?reset=true' target='messageList' class=small_tools>$langWash</a></li>
          <li><img src='../../template/classic/img/save.gif'>&nbsp;<a href='messageList.php?store=true' target='messageList' class=small_tools>$langSave</a></li>
        </ul>
      </div>";
}

$tool_content .= "

  <table width='99%' class='FormData'>
  <thead>
  <tr>
    <th>&nbsp;</th>
    <td>
      <form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepare_message();'>
      <b>$langTypeMessage</b><br />
      <input type='text' name='msg' size='80'>
      <input type='hidden' name='chatLine'>
      <input type='submit' value=' >> '>
      </form>
    </td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <th><iframe frameborder='0' src='messageList.php' width='100%' height='300' name='messageList' style='border: 0px solid #edecdf;'><a href='messageList.php'>Message list</a></iframe></th>
  </tr>
  </thead>
  </table>";

draw($tool_content, 2, 'conference', $head_content);
