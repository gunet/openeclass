<?php
    
if (!function_exists('ldap_connect'))		// Is php4-ldap really there?
{ 
    $tool_content .= '<p align="center"><font color="red"><strong>Warning:
           The PHP LDAP module does not seem to be present. Please ensure it is 
	   installed and enabled.</strong></font></p>';
}

$ldapdata = $auth_data;

if(!empty($ldapdata))
{
    $ldapsettings = $ldapdata['auth_settings'];
    $ldapinstructions = $ldapdata['auth_instructions'];
    // $ldaphost = str_replace("imaphost=","",$imapsettings);
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
    $ldapsettings = $ldapdata['auth_settings'];
    $ldapinstructions = $ldapdata['auth_instructions'];
    $ldaphost = $ldapsettings;
}

$tool_content .= "<tr valign=\"top\">
    <td align=\"right\">ldap_host_url:</td>
    <td>
        <input name=\"ldaphost\" type=\"text\" size=\"30\" value=\"".$ldaphost."\">
    </td>
    <td>
    ldap host
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">ldap_bind_dn:</td>
    <td>
    <input name=\"ldapbind_dn\" type=\"text\" size=\"30\" value=\"".$ldapbind_dn."\">
    </td><td>
    ldap_bind_dn
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">ldap_bind_user:</td>
    <td>
    <input name=\"ldapbind_user\" type=\"text\" size=\"30\" value=\"".$ldapbind_user."\">
    </td><td>
    ldap_bind_user. Leave blank for anonymous binding
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">ldap_bind_pw:</td>
    <td>
    <input name=\"ldap_bind_pw\" type=\"password\" size=\"30\" value=\"".$ldapbind_pw."\">
    </td><td>
    ldap_bind_pw. Leave blank for anonymous binding
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">instructions:</td>
    <td>
	<textarea name=\"ldapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$ldapinstructions."</textarea> 
    </td>
    <td> 
	Here you can provide instructions for your users, so they know which username and password they should be using. The text you enter here will appear on the login page. 
	<br />If you leave this blank then no instructions will be printed.
    </td>
</tr>
</table>";



?>