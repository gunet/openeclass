<?php
/*
$qry = "SELECT * FROM auth WHERE auth_name = 'pop3'";
$result = db_query($qry);
$db_auth_email = array();
if($result)
{
    $db_auth_email = mysql_fetch_array($result,MYSQL_ASSOC);
}

$db_pop3host = $db_auth_email['auth_settings'];
$db_pop3instructions = $db_auth_email['auth_instructions'];
*/

$imaphost = isset($_POST['imaphost'])?$_POST['imaphost']:$db_imaphost;
$imapinstructions = isset($_POST['imapinstructions'])?$_POST['imapinstructions']:$db_imapinstructions;

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

/*
function auth_user_login ($test_username, $test_password) 
{
    $imaphost = $GLOBALS['imaphost'];
    $imapauth = imap_auth($imaphost, $test_username, $test_password);
    if($imapauth)
    {
	return true;
    }
    else
    {
	return false;
    }
}
*/
?>