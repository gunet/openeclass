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
$imapdata = $auth_data;

if(!empty($imapdata))
{
    $imapsettings = $imapdata['auth_settings'];
    $imapinstructions = $imapdata['auth_instructions'];
    $imaphost = str_replace("imaphost=","",$imapsettings);
}
else
{
    $imapsettings = $imapdata['auth_settings'];
    $imapinstructions = $imapdata['auth_instructions'];
    $imaphost = $imapsettings;
}

$imaphost = isset($_POST['imaphost'])?$_POST['imaphost']:$imaphost;
$imapinstructions = isset($_POST['imapinstructions'])?$_POST['imapinstructions']:$imapinstructions;

$tool_content .= "

    <tr>
      <th class=\"left\">$langimaphost:</th>
      <td><input class=\"FormData_InputText\" name=\"imaphost\" type=\"text\" size=\"30\" value=\"".$imaphost."\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langimapport:</th>
      <td>143</td>
    </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</td>
      <td><textarea class=\"FormData_InputText\" name=\"imapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$imapinstructions."</textarea></td>
    </tr>
";
?>
