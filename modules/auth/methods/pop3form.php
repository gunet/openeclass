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
    <td align=\"right\">pop3host:</td>
    <td>
        <input name=\"pop3host\" type=\"text\" size=\"30\" value=\"".$pop3host."\" />
    </td>
    <td>The POP3 server address. Use the IP number or the domain name.
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">pop3port:</td>
    <td>
	110
    </td>
    <td>
	Server port: 110 is the most common and is set by default
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">instructions:</td>
    <td>
	<textarea name=\"pop3instructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$pop3instructions."</textarea> 
    </td>
    <td> 
	Here you can provide instructions for your users, so they know which username and password they should be using. The text you enter here will appear on the login page. If you leave this blank then no instructions will be printed.
    </td>
</tr>
</table>";

/*
require("pop3.php");

function auth_user_login ($test_username, $test_password) 
{
    $pop3host = $GLOBALS['pop3host'];

    $pop3=new pop3_class;
    $pop3->hostname = $pop3host;		// POP 3 server host name                
    $pop3->port=110;				// POP 3 server host port                
    $user = $test_username;                     // Authentication user name      
    $password = $test_password;                 // Authentication password       
    $pop3->realm="";                         	// Authentication realm or domain        
    $pop3->workstation="";			// Workstation for NTLM authentication   
    $apop = 0;					// Use APOP authentication                    
    $pop3->authentication_mechanism="USER";  	// SASL authentication mechanism  
    $pop3->debug=0;                          	// Output debug information       
    $pop3->html_debug=1;                     	// Debug information is in HTML   
    $pop3->join_continuation_header_lines=1; 	// Concatenate headers split in multiple lines 

    if(($error=$pop3->Open())=="")
    {
	if(($error=$pop3->Login($user,$password,$apop))=="")
	{
	    if($error=="" && ($error=$pop3->Close())=="")
	    {
		return true;
	    }
	    else
	    {
		return false;
	    }
	}
	else
	{
	    return false;
	}
    }
    else
    {
	return false;
    }
    if($error!="")
    {
	return false;
    }
    
}
*/

?>