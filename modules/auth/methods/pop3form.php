<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$pop3data = $auth_data;

if(!empty($pop3data))
{
    $pop3settings = $pop3data['auth_settings'];
    $pop3instructions = $pop3data['auth_instructions'];
    $pop3host = str_replace("pop3host=","",$pop3settings);
}
else
{
    $pop3settings = $pop3data['auth_settings'];
    $pop3instructions = $pop3data['auth_instructions'];
    $pop3host = $pop3settings;
}

$pop3host = isset($_POST['pop3host'])?$_POST['pop3host']:$pop3host;
$pop3instructions = isset($_POST['pop3instructions'])?$_POST['pop3instructions']:$pop3instructions;

$tool_content .= "
    <tr>
      <th class=\"left\">$langpop3host:</th>
      <td><input class=\"FormData_InputText\" name=\"pop3host\" type=\"text\" size=\"30\" value=\"".$pop3host."\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langpop3port:</th>
      <td>110</td>
    </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</th>
      <td><textarea class=\"FormData_InputText\" name=\"pop3instructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$pop3instructions."</textarea></td>
    </tr>";
?>
