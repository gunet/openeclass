<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Giannis Kapetanakis <bilias@edu.physics.uoc.gr>
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

#include('../../include/CAS/CAS.php');

if (!method_exists('phpCAS', 'setDebug'))
{
    $tool_content .= "<p align='center'><font color='red'><strong>$langCASNotWork.</strong></font></p>";

}
$casdata = $auth_data;

$cassettings = $casdata['auth_settings'];
$casinstructions = $casdata['auth_instructions'];

if(!empty($cassettings))
{
// tabs
	$cas = explode("|",$cassettings);
	//cas_host
	$cas_host = str_replace("cas_host=","",$cas[0]);
	//cas_port
	$cas_port = str_replace("cas_port=","",$cas[1]);
	if (empty($cas_port))
		$cas_port = 443;
	//cas_context
	$cas_context = str_replace("cas_context=","",$cas[2]);
	if (empty($cas_context))
		$cas_context = "/cas/";
	//cas_cachain
	$cas_cachain = str_replace("cas_cachain=","",$cas[3]);
	//casusermailattr
	$casusermailattr = str_replace("casusermailattr=","",$cas[4]);
	//casuserfirstattr
	$casuserfirstattr = str_replace("casuserfirstattr=","",$cas[5]);
	//casuserlastattr
	$casuserlastattr = str_replace("casuserlastattr=","",$cas[6]);
	//cas_altauth
	$cas_altauth = intval(str_replace("cas_altauth=","",$cas[7]));
} else {
	// empty host
	$cas_host = "";
	$cas_port = 443;
  	$cas_context = "/cas/";
	$cas_cachain = "";
	$casusermailattr = "mail";
	// givenName is the default for LDAP not givename
	$casuserfirstattr = "givenName";
	$casuserlastattr = "sn";
	$cas_altauth = 0;
}

$tool_content .= "
    <tr>
      <th class=\"left\">$langcas_host:</th>
      <td><input class=\"FormData_InputText\" name=\"cas_host\" type=\"text\" size=\"30\" value=\"".$cas_host."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcas_port:</th>
      <td><input class=\"FormData_InputText\" name=\"cas_port\" type=\"text\" size=\"30\" value=\"".$cas_port."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcas_context:</th>
      <td><input class=\"FormData_InputText\" name=\"cas_context\" type=\"text\" size=\"30\" value=\"".$cas_context."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcas_cachain:</th>
      <td><input class=\"FormData_InputText\" name=\"cas_cachain\" type=\"text\" size=\"40\" value=\"".$cas_cachain."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcasusermailattr:</th>
      <td><input class=\"FormData_InputText\" name=\"casusermailattr\" type=\"text\" size=\"30\" value=\"".$casusermailattr."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcasuserfirstattr:</th>
      <td><input class=\"FormData_InputText\" name=\"casuserfirstattr\" type=\"text\" size=\"30\" value=\"".$casuserfirstattr."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcasuserlastattr:</th>
      <td><input class=\"FormData_InputText\" name=\"casuserlastattr\" type=\"text\" size=\"30\" value=\"".$casuserlastattr."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langcas_altauth:</th>
      <td>";
		
$cas_altauth_data = array();
$cas_altauth_data[0] = "-";
$cas_altauth_data[1] = "eClass";
$cas_altauth_data[2] = "POP3";
$cas_altauth_data[3] = "IMAP";
$cas_altauth_data[4] = "LDAP";
$cas_altauth_data[5] = "External DB";
$tool_content .= selection($cas_altauth_data,"cas_altauth",$cas_altauth);
$tool_content .= "    </td>
    </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</th>
      <td><textarea class=\"FormData_InputText\" name=\"casinstructions\" cols=\"30\" rows=\"10\">".$casinstructions."</textarea></td>
    </tr>";
?>
