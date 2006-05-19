<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	admin.inc.php
	@last update: 09-05-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Functions Library for admin purposes

 	Thislibrary includes all the functions that admin is using 
	and their settings.

 	@Comments: 
	

==============================================================================
*/

include 'datetime/datetimeclass.inc';

/**
 * eclass replacement for php stripslashes() function
 *
 * The standard php stripslashes() removes ALL backslashes
 * even from strings - so  C:\temp becomes C:temp - this isn't good.
 * This function should work as a fairly safe replacement
 * to be called on quoted AND unquoted strings (to be sure)
 *
 * @param string the string to remove unsafe slashes from
 * @return string
 */
function stripslashes_safe($string) {

    $string = str_replace("\\'", "'", $string);
    $string = str_replace('\\"', '"', $string);
    $string = str_replace('\\\\', '\\', $string);
    return $string;
}

// Show a selection box. Taken from main.lib.php
// Difference: the return value and not just echo the select box
// $entries: an array of (value => label)
// $name: the name of the selection element
// $default: if it matches one of the values, specifies the default entry
function selection2($entries, $name, $default = '')
{
	$select_box = "<select name='$name'>\n";
	foreach ($entries as $value => $label) 
	{
	    if ($value == $default) 
	    {
		$select_box .= "<option selected value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
	    } 
	    else 
	    {
		$select_box .= "<option value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
	    }
	}
	$select_box .= "</select>\n";
	
	return $select_box;
}



/**
 * Print a message in a standard themed box.
 *
 * @param string $message ?
 * @param string $align ?
 * @param string $width ?
 * @param string $color ?
 * @param int $padding ?
 * @param string $class ?
 * @todo Finish documenting this function
 */
function print_simple_box($message, $align='', $width='', $color='', $padding=5, $style='') 
{
    $simple_box = print_simple_box_start($align, $width, $color, $padding, $style);
    $simple_box .= stripslashes_safe($message);
    $simple_box .= print_simple_box_end();
    
    return $simple_box;
}

/**
 * Print the top portion of a standard themed box.
 *
 * @param string $align ?
 * @param string $width ?
 * @param string $color ?
 * @param int $padding ?
 * @param string $class ?
 * @todo Finish documenting this function
*/
 
function print_simple_box_start($align='center', $width='', $color='', $padding=5, $style='') 
{
    if ($color) {
        $color = 'bgcolor="'. $color .'"';
    }
    if ($align) {
        $align = 'align="'. $align .'"';
    }
    if ($width) {
        $width = 'width="'. $width .'"';
    }

    $style = "border-width:1px;border-style:solid;margin-bottom: 15px;";

    $simple_box_start = "<table $align $width style=\"$style\" border=\"0\" cellpadding=\"$padding\" cellspacing=\"0\">".
         "<tr><td $color style=\"$style"."content\">";
    
    return $simple_box_start;
}

/*
* Print the end portion of a standard themed box.
*/
function print_simple_box_end() 
{
    $simple_box_end = '</td></tr></table>';
    
    return $simple_box_end;
}

function list_departments($department_value)
{
    $qry = "SELECT faculte.id,faculte.name FROM faculte ORDER BY faculte.name";
    $dep = mysql_query($qry);
    if($dep)
    {
	$departments_select = "";
	$departments = array();
	while($row=mysql_fetch_array($dep))
	{
	    $id = $row['id'];    
	    $name = $row['name'];
	    $departments[$id] = $name;
	}
	$departments_select = selection2($departments,"department",$department_value);
	
	return $departments_select;
	//return $departments;
	//return $dep;
    }
    else
    {
	return 0;
    }
}

function convert_time($seconds)
{
    $f_minutes = $seconds / 60;
    $i_minutes = floor($f_minutes);
    $r_seconds = intval(($f_minutes - $i_minutes) * 60);
	        
    $f_hours = $i_minutes / 60;
    $i_hours = floor($f_hours);
    $r_minutes = intval(($f_hours  - $i_hours) * 60);
			        
    $f_days = $i_hours / 24;
    $i_days = floor($f_days);
    $r_hours = intval(($f_days - $i_days) * 24);
					        
    if ($i_days > 0) 
    {
        if($i_days > 365)
	{
	    $i_years = floor($i_days / 365);    
	    $i_days = $i_days % 365;
	    $r = $i_years;
	    if($i_years>1)
	    {
		$r .= " years ";
	    }
	    else
	    {
		$r .= " year ";
	    }
	    $r .= $i_days . " days ";
	}
	else
	{
	    $r = "$i_days days ";
	}
    }
    if ($r_hours > 0) $r .= "$r_hours hours ";
    if ($r_minutes > 0) $r .= "$r_minutes min";
    else $r = "less than a minute";
								    
    return $r;
}

// purpose: find/return the id of the default authentication method
function get_auth_id()
{
    $sql = "SELECT auth_id FROM auth WHERE auth_default=1";
    $auth_method = db_query($sql);
    if($auth_method)
    {
	$authrow = mysql_fetch_row($auth_method);
	if(mysql_num_rows($auth_method)==1)
	{
	    $auth_id = $authrow[0];
	    return $auth_id;
	}
	else
	{
	    return 0;
	}
    }
    else
    {
	return 0;
    }
}

// purpose: find the settings for a specified auth method (e.g. ldap or pop3)
function get_auth_settings($auth)
{
    $qry = "SELECT * FROM auth WHERE auth_id = ".$auth;
    $result = db_query($qry);
    $db_auth_email = array();
    if($result)
    {
	if(mysql_num_rows($result)==1)
	{
	    $auth_row = mysql_fetch_array($result,MYSQL_ASSOC);
	    return $auth_row;
	}
	else
	{
	    return 0;
	}	
    }
    else
    {
	return 0;
    }
}

require("auth/pop3.php");

function auth_user_login ($auth,$test_username, $test_password) 
{
    switch($auth)
    {
	case 1:
	    // Returns true if the username and password work and false if they don't
	    $sql = "SELECT user_id FROM user WHERE username='".$test_username."' AND password='".$test_password."'";
	    $result = db_query($sql);
	    if(mysql_num_rows($result)==1)
	    {
    		$testauth = true;
	    }
	    else
	    {
		$testauth = false;
	    }
	break;	
    
    
	case 2:
	    $pop3host = $GLOBALS['pop3host'];
	    $pop3=new pop3_class;
	    $pop3->hostname = $pop3host;	/* POP 3 server host name                      */
	    $pop3->port=110;				/* POP 3 server host port                      */
	    $user = $test_username;                       	/* Authentication user name                    */
	    $password = $test_password;                   	/* Authentication password                     */
	    $pop3->realm="";                         	/* Authentication realm or domain              */
	    $pop3->workstation="";			/* Workstation for NTLM authentication         */
	    $apop = 0;			/* Use APOP authentication                     */
	    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
	    $pop3->debug=0;                          /* Output debug information                    */
	    $pop3->html_debug=1;                     /* Debug information is in HTML                */
	    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

	    if(($error=$pop3->Open())=="")
	    {
		if(($error=$pop3->Login($user,$password,$apop))=="")
		{
		    if($error=="" && ($error=$pop3->Close())=="")
		    {
		    $testauth = true;
		    }
		    else
		    {
		    $testauth = false;
		    }
		}
		else
		{
		    $testauth = false;
		}
	    }
	    else
	    {
		$testauth = false;
	    }
	    if($error!="")
	    {
		$testauth = false;
	    }
	    break;
	
	case 3:
	    $imaphost = $GLOBALS['imaphost'];
	    $imapauth = imap_auth($imaphost, $test_username, $test_password);
	    if($imapauth)
	    {
		$testauth = true;
	    }
	    else
	    {
		$testauth = false;
	    }
	    break;
	default:
	    $testauth = false;
    }
    
    return $testauth;
}


?>