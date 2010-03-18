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

if (!function_exists('ldap_connect'))		// Is php4-ldap really there?
{
    $tool_content .= "<p align='center'><font color='red'><strong>$langLdapNotWork.</strong></font></p>";
}

$ldapdata = $auth_data;

if(!empty($ldapdata))
{
    $ldapsettings = $ldapdata['auth_settings'];
    $ldapinstructions = $ldapdata['auth_instructions'];
    // $ldaphost = str_replace("imaphost=","",$imapsettings);
    if(!empty($ldapsettings))
    {
    	$ldap = explode("|",$ldapsettings);
    	//ldaphost
	    $ldaphost = str_replace("ldaphost=","",$ldap[0]);
	    //ldapbase_dn
	    $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]);
	    //ldapbind_user
	    $ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]);
	    // ldapbind_pw
	    $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);
    }
    else
    {
    	$ldaphost = ""; $ldapbind_dn = ""; $ldapbind_user = ""; $ldapbind_pw = "";
    }

}
else
{
    $ldapsettings = $ldapdata['auth_settings'];
    $ldapinstructions = $ldapdata['auth_instructions'];
    $ldaphost = $ldapsettings;
}

$tool_content .= "
    <tr>
      <th class=\"left\">$langldap_host_url:</th>
      <td><input class=\"FormData_InputText\" name=\"ldaphost\" type=\"text\" size=\"30\" value=\"".$ldaphost."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langldap_bind_dn:</th>
      <td><input class=\"FormData_InputText\" name=\"ldapbind_dn\" type=\"text\" size=\"30\" value=\"".$ldapbind_dn."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langldap_bind_user:</th>
      <td><input class=\"FormData_InputText\" name=\"ldapbind_user\" type=\"text\" size=\"30\" value=\"".$ldapbind_user."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langldap_bind_pw:</th>
      <td><input class=\"FormData_InputText\" name=\"ldapbind_pw\" type=\"password\" size=\"30\" value=\"".$ldapbind_pw."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</td>
      <td><textarea class=\"FormData_InputText\" name=\"ldapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$ldapinstructions."</textarea>   </td>
    </tr>
";
?>
