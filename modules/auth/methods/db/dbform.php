<?php
$dbdata = $auth_data;

if(!empty($dbdata))
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    // $dbhost = str_replace("imaphost=","",$imapsettings);
    $edb = empty($dbsettings)?"":explode("|",$dbsettings);
    if(!empty($edb))
    {
	    //dbhost
	    $dbhost = str_replace("dbhost=","",$edb[0]);
	    //dbname
	    $dbname = str_replace("dbname=","",$edb[1]);
	    //dbuser
	    $dbuser = str_replace("dbuser=","",$edb[2]);
	    // dbpass
	    $dbpass = str_replace("dbpass=","",$edb[3]);
	    //dbtable
	    $dbtable = str_replace("dbtable=","",$edb[4]);
	    //dbfielduser
	    $dbfielduser = str_replace("dbfielduser=","",$edb[5]);
	    //dbfieldpass
	    $dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);
	  }
	  else
	  {
	  	$dbhost = "";			//dbhost
	    $dbname = "";			//dbname
	    $dbuser = "";			//dbuser
	    $dbpass = "";			// dbpass
	    $dbtable = "";		//dbtable
	    $dbfielduser = "";	//dbfielduser
	    $dbfieldpass = "";	//dbfieldpass
	  }
}
else
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    $dbhost = $dbsettings;
}


$tool_content .= "<table border=\"0\">
<tr valign=\"top\">
    <td align=\"right\">$langdbhost:</td>
    <td>
        <input name=\"dbhost\" type=\"text\" size=\"30\" value=\"$dbhost\" />
    </td>
    <td>
	Database host (IP or domain name)
    </td>
</tr>";

$tool_content .= "<tr valign=\"top\">
    <td align=\"right\">$langdbname:</td>
    <td>
    <input name=\"dbname\" type=\"text\" size=\"30\" value=\"$dbname\" />
    </td>
    <td>
	Database name (e.g. eclass)
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langdbuser:</td>
    <td>
    <input name=\"dbuser\" type=\"text\" size=\"30\" value=\"$dbuser\" />
    </td>
    <td>
	The user that connects to the database host
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langdbpass:</td>
    <td>
    <input name=\"dbpass\" type=\"text\" size=\"30\" value=\"$dbpass\" />
    </td>
    <td>
	The password of user
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langdbtable:</td>
    <td>
    <input name=\"dbtable\" type=\"text\" size=\"30\" value=\"$dbtable\" />
    </td>
    <td>
	The name of the table that contains the accounts
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langdbfielduser:</td>
    <td>
    <input name=\"dbfielduser\" type=\"text\" size=\"30\" value=\"$dbfielduser\" />
    </td>
    <td>
	The name of the field in the table that contains the usernames
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langdbfieldpass:</td>
    <td>
    <input name=\"dbfieldpass\" type=\"text\" size=\"30\" value=\"$dbfieldpass\" />
    </td>
    <td>
	The name of the field in the table that contains the passwords
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">$langInstructions:</td>
    <td>
	<textarea name=\"dbinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$dbinstructions."</textarea> 
    </td>
    <td> 
	Here you can provide instructions for your users, so they know which username and password they should be using. The text you enter here will appear on the login page. 
	<br />If you leave this blank then no instructions will be printed.
    </td>
</tr>
</table>";



?>