<?php
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
<table border=\"0\">
<tr valign=\"top\">
    <td align=\"right\">$langpop3host:</td>
    <td>
        <input name=\"pop3host\" type=\"text\" size=\"30\" value=\"".$pop3host."\" />
    </td>
    <td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langpop3port:</td>
    <td>110</td>
    <td>&nbsp </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langInstructions:</td>
    <td>
	<textarea name=\"pop3instructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$pop3instructions."</textarea> 
    </td>
    <td>&nbsp; 
    </td>
</tr>
</table>";

?>
