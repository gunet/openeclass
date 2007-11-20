<?php
    
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

$tool_content .= "<tr valign=\"top\">
    <td align=\"right\">$langldap_host_url:</td>
    <td>
        <input name=\"ldaphost\" type=\"text\" size=\"30\" value=\"".$ldaphost."\">
    </td>
    <td>&nbsp;</td>
    </tr>

<tr valign=\"top\">
    <td align=\"right\">$langldap_bind_dn:</td>
    <td>
    <input name=\"ldapbind_dn\" type=\"text\" size=\"30\" value=\"".$ldapbind_dn."\">
    </td><td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langldap_bind_user:</td>
    <td>
    <input name=\"ldapbind_user\" type=\"text\" size=\"30\" value=\"".$ldapbind_user."\">
    </td><td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langldap_bind_pw:</td>
    <td>
    <input name=\"ldapbind_pw\" type=\"password\" size=\"30\" value=\"".$ldapbind_pw."\">
		</td><td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langInstructions:</td>
    <td>
	<textarea name=\"ldapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$ldapinstructions."</textarea> 
    </td><td>&nbsp;</td>
</tr>
</table>";

?>
