<?php
/* ========================================================================
 * Open eClass 2.4
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


if (!function_exists('ldap_connect'))		// Is php4-ldap really there?
{
    $tool_content .= "<p class='caution'<strong>$langLdapNotWork.</p>";
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
		 //ldapbase_base
	    $ldap_base = str_replace("ldap_base=","",$ldap[1]);
		 //ldapbind_dn
	    $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[2]);
	    //ldapbind_pw
	    $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);
		 //ldap_login_attr
	    if (isset($ldap[4])) {
		$ldap_login_attr = str_replace("ldap_login_attr=","",$ldap[4]);
	    }
	    if (empty($ldap_login_attr)) {
		$ldap_login_attr="uid";
	    }
	    if (isset($ldap[5])) {
		 //ldap_login_attr2 
		$ldap_login_attr2 = str_replace("ldap_login_attr2=","",$ldap[5]);
	    }
    }
    else
    {
    	$ldaphost = ""; $ldap_base = ""; $ldapbind_dn = ""; $ldapbind_pw = ""; $ldap_login_attr = "uid"; $ldap_login_attr2 = "";
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
      <th class=\"left\">$langldap_base:</th>
      <td><input class=\"FormData_InputText\" name=\"ldap_base\" type=\"text\" size=\"30\" value=\"".$ldap_base."\"></td>
    </tr>
    <tr>
	 	<th class=\"left\">$langldap_bind_dn:</th>
		<td><input class=\"FormData_InputText\" name=\"ldapbind_dn\" type=\"text\" size=\"30\" value=\"".$ldapbind_dn."\"></td>
    </tr>
    <tr>
      <th class=\"left\">$langldap_bind_pw:</th>
      <td><input class=\"FormData_InputText\" name=\"ldapbind_pw\" type=\"password\" size=\"30\" value=\"".$ldapbind_pw."\"></td>
    </tr>
	 <tr>
		<th class=\"left\">$langldap_login_attr:</th>
		<td><input class=\"FormData_InputText\" name=\"ldap_login_attr\" type=\"text\" size=\"30\" value=\"".$ldap_login_attr."\"></td>
	 </tr>
	 <tr>
		<th class=\"left\">$langldap_login_attr2:</th>
		<td><input class=\"FormData_InputText\" name=\"ldap_login_attr2\" type=\"text\" size=\"30\" value=\"".$ldap_login_attr2."\"></td>
	 </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</th>
      <td><textarea class=\"FormData_InputText\" name=\"ldapinstructions\" cols=\"30\" rows=\"10\">".$ldapinstructions."</textarea>   </td>
    </tr>
";
?>
