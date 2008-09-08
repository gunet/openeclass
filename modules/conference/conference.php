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
	$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' cellpadding='0'>\n";
        $tool_content .= "<tr><td valign=top><br><br>";
        $tool_content .= "<div class=td_main>$langNoGuest</div>";
        $tool_content .= "</td></tr>";
        $tool_content .= "</table>";
	draw($tool_content, 2, 'conference');
}

if (!($uid) or !($_SESSION['uid'])) {
	$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' cellpadding='0'>\n";
        $tool_content .= "<tr><td valign=top><br><br>";
  	$tool_content .= "<div class=td_main>$langNoAliens</div>";
        $tool_content .= "</td></tr>";
        $tool_content .= "</table>";
	draw($tool_content, 2, 'conference');
}

$tool_content .= "<table height=316 width=100%>";
?>
<script>
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>
<?
if ($is_adminOfCourse) {
        $tool_content .= "<tr><td align=left class=tool_bar width=50%>
	<img src='../../template/classic/img/clean.gif'>&nbsp;<a href='messageList.php?reset=true' target='messageList' class=small_tools>$langWash</a></td><td width=49% align=right class=tool_bar><a href='messageList.php?store=true' target='messageList' class=small_tools>$langSave</a>&nbsp;<img src='../../template/classic/img/save.gif'></td></tr>";
}

$tool_content .= "<tr><td colspan ='2' valign=top>
<form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepare_message();'>
<span class='explanationtext'>$langTypeMessage</span><br>
<input type='text' name='msg' size='80'>
<input type='hidden' name='chatLine'>
<input type='submit' value=' >> '><br>";

$tool_content .= "&nbsp;<br><iframe frameborder='0' src='messageList.php' width='96%' height='300' name='messageList' style='border: 1px solid silver'><a href='messageList.php'>Message list</a></iframe></td></tr></table>";

draw($tool_content, 2, 'conference');
?>
