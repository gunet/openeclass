<?php
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
<table border=\"0\">
<tr valign=\"top\">
    <td align=\"right\">imaphost:</td>
    <td>
        <input name=\"imaphost\" type=\"text\" size=\"30\" value=\"".$imaphost."\" />
    </td>
    <td>The imap server address. Use the IP number or the domain name.
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">imapport:</td>
    <td>
	143
    </td>
    <td>
	Server port: 143 is the most common and is set by default
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">instructions:</td>
    <td>
	<textarea name=\"imapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$imapinstructions."</textarea> 
    </td>
    <td> 
	Here you can provide instructions for your users, so they know which username and password they should be using. The text you enter here will appear on the login page. If you leave this blank then no instructions will be printed.
    </td>
</tr>
</table>";

?>