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
	serachuser.php
	@last update: 16-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
  @Description: User Search form based upon criteria/filters

 	This script allows the admin to search for platform users,
 	specifying certain criteria/filters
	
 	The admin can : - specify the criteria
 									- view the list
 									
==============================================================================
*/

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('gunet','admin','registration');
include '../../include/baseTheme.php';
include 'admin.inc.php';
@include "check_admin.inc";		// check if user is administrator
$nameTools = $langSearchUser;		// Define $nameTools
$tool_content = "";		// Initialise $tool_content

// Main body
$new = isset($_GET['new'])?$_GET['new']:'yes';		//variable of declaring a new search

if((!empty($new)) && ($new=="yes")) 
{
	// It is a new search, so unregister the search terms/filters in session variables
	session_unregister('user_sirname');
	session_unregister('user_firstname');
	session_unregister('user_username');
	session_unregister('user_am');
	session_unregister('user_type');
	session_unregister('user_registered_at_flag');
	session_unregister('user_registered_at');
	session_unregister('user_email');
	unset($user_sirname);
	unset($user_firstname);
	unset($user_username);
	unset($user_am);
	unset($user_type);
	unset($user_registered_at_flag);
	unset($user_registered_at);
	unset($user_email);
}

// initialize the variables
$user_sirname = isset($_SESSION['user_sirname'])?$_SESSION['user_sirname']:'';
$user_firstname = isset($_SESSION['user_firstname'])?$_SESSION['user_firstname']:'';
$user_username = isset($_SESSION['user_username'])?$_SESSION['user_username']:'';
$user_am = isset($_SESSION['user_am'])?$_SESSION['user_am']:'';
$user_type = isset($_SESSION['user_type'])?$_SESSION['user_type']:'5';
$user_registered_at_flag = isset($_SESSION['user_registered_at_flag'])?$_SESSION['user_registered_at_flag']:'1';
$user_registered_at = isset($_SESSION['user_registered_at'])?$_SESSION['user_registered_at']:time();
$user_email = isset($_SESSION['user_email'])?$_SESSION['user_email']:'';


// display the search form
$tool_content .= "<form action=\"listusers.php?search=".$new."\" method=\"post\" name=\"user_search\">";
$tool_content .= "<table width=\"99%\"><tbody>";
$tool_content .= "<tr>
    <td width=\"3%\" nowrap><b>$langSurname</b>:</td>
    <td><input type=\"text\" name=\"user_sirname\" size=\"40\" value=\"".$user_sirname."\"></td>
</tr>
<tr>
    <td width=\"3%\" nowrap><b>$langName</b>:</td>
    <td><input type=\"text\" name=\"user_firstname\" size=\"40\" value=\"".$user_firstname."\"></td>
</tr>
";
$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>$langAm:</b></td>
    <td><input type=\"text\" name=\"user_am\" size=\"30\" value=\"".$user_am."\"></td>
</tr>";
$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>$langUserType ($langStudent2/$langTeacher):</b></td>
    <td>";
$usertype_data = array();
$usertype_data[5] = $langStudent2;
$usertype_data[1] = $langTeacher;
$tool_content .= selection2($usertype_data,"user_type",$user_type);
$tool_content .= "</td>
</tr>";
$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>$langRegistrationDate:</b></td>
    <td>";
$user_registered_at_flag_data = array();
$user_registered_at_flag_data[1] = $langBefore;
$user_registered_at_flag_data[2] = $langAfter;
$tool_content .= selection2($user_registered_at_flag_data,"user_registered_at_flag",$user_registered_at_flag);
    
// format the drop-down menu for data
$datetime = new DATETIME();
$datetime->set_timename("hour", "min", "sec");
$datetime->set_datetime_byvar2($user_registered_at);
$mytime = $datetime->get_timestamp_entered();
$user_registered_at = $mytime;

if ($datetime->get_date_error())
{
	$tool_content .= "<b><font color=red>".$datetime->get_date_error()."</font>";
}
else 
{
	$tool_content .= "&nbsp;";
}

$tool_content .= $datetime->get_select_years("ldigit", "2002", "2029", "year")." "
	. $datetime->get_select_months(1, "sword", "month")." "
	. $datetime->get_select_days(1, "day")."&nbsp;&nbsp;&nbsp;"
	. $datetime->get_select_hours(1, 12, "hour")
	. $datetime->get_select_minutes(1, "min")
		. $datetime->get_select_seconds(1, "sec")
	. $datetime->get_select_ampm();
	
$tool_content .= "</td>
  </tr>";  
$tool_content .= "<tr>
    <td width=\"3%\" nowrap><b>E-mail:</b></td>
    <td><input type=\"text\" name=\"user_email\" size=\"40\" value=\"".$user_email."\"></td>
</tr>";
$tool_content .= "<tr>
    <td width=\"3%\" nowrap><b>$langUsername:</b></td>
    <td><input type=\"text\" name=\"user_username\" size=\"40\" value=\"".$user_username."\"></td>
</tr>";
$tool_content .= "  <tr>
    <td colspan=\"2\"><br />
    <input type=\"hidden\" name=\"c\" value=\"searchlist\">
    <input type=\"submit\" name=\"search_submit\" value=\"$langSearch\"></td>
  </tr>";
$tool_content .= "</tbody></table></form>";
// end form


$tool_content .= "<br /><center><p><a href=\"index.php\">$back</a></p></center>";

// 3: display administrator menu
draw($tool_content,3);
?>