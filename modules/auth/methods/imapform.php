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
    <td align=\"right\">$langimaphost:</td>
    <td>
        <input name=\"imaphost\" type=\"text\" size=\"30\" value=\"".$imaphost."\" />
    </td>
    <td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langimapport:</td>
    <td>143</td>
    <td>&nbsp;</td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langInstructions:</td>
    <td>
	<textarea name=\"imapinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$imapinstructions."</textarea> 
    </td>
    <td>&nbsp; </td>
</tr>
</table>";

?>
