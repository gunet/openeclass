<?php
/*=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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

/*===========================================================================
	serachuser.php
	@last update: 16-10-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
  @Description: User Search form based upon criteria/filters

 	This script allows the admin to search for platform users,
 	specifying certain criteria/filters
	
 	The admin can : - specify the criteria
 			- view the list
 			- select the inactive users
 									
==============================================================================
*/

//  BASETHEME, OTHER INCLUDES AND NAMETOOLS
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
include '../../include/jscalendar/calendar.php';

$tool_content = $head_content = "";

if ($language == 'greek') {
    $lang_editor='gr';
    $lang_jscalendar = 'el';
}
  else {
    $lang_editor='en';
    $lang_jscalendar = $lang_editor;
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langSearchUser;		

// Main body
$new = isset($_GET['new'])?$_GET['new']:'yes';	//variable of declaring a new search

if((!empty($new)) && ($new=="yes")) {
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

// display link to inactive users
$tool_content .= "<a href=\"listusers.php?c=4\">".$langInactiveUsers."</a><br><br>";

// display the search form
$tool_content .= "<form action=\"listusers.php?search=".$new."\" method=\"post\" name=\"user_search\">";
$tool_content .= "<table width=\"99%\"><tbody>";
$tool_content .= "<tr><th width='150' class='left'><b>$langSurname</b>:</th>
 <td><input type=\"text\" class='FormData_InputText' name=\"user_sirname\" size=\"40\" value=\"".$user_sirname."\"></td></tr>
<tr><th class='left'><b>$langName</b>:</th>
<td><input type=\"text\" class='FormData_InputText' name=\"user_firstname\" size=\"40\" value=\"".$user_firstname."\"></td></tr>";
$tool_content .= "<tr><th class='left'><b>$langAm:</b></th><td><input type=\"text\" class='FormData_InputText' name=\"user_am\" size=\"30\" value=\"".$user_am."\"></td></tr>";
$tool_content .= "<tr><th class='left'><b>$langUserType:</b></th><td>";
$usertype_data = array();
$usertype_data[0] = $langAllUsers;
$usertype_data[1] = $langTeacher;
$usertype_data[5] = $langStudent;
$usertype_data[10] = $langGuest;
$tool_content .= selection($usertype_data,"user_type",$usertype_data[0]);
$tool_content .= "</td></tr>";
$tool_content .= " <tr><th class='left'><b>$langRegistrationDate:</b></th><td>";
$user_registered_at_flag_data = array();
$user_registered_at_flag_data[1] = $langAfter;
$user_registered_at_flag_data[2] = $langBefore;
$tool_content .= selection($user_registered_at_flag_data,"user_registered_at_flag",$user_registered_at_flag);

    $start_cal = $jscalendar->make_input_field(
       array('showOthers' => true,
                'align' => 'Tl',
                 'ifFormat' => '%d-%m-%Y'),
       array('style' => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name' => 'date',
                 'value' => ' '));

    $tool_content .= $start_cal."&nbsp;&nbsp;&nbsp;";
    @$tool_content .= "<select name='hour'>";
    for ($h=0; $h<=24; $h++)
       $tool_content .= "<option value='$h'>$h</option>";
    $tool_content .= "</select>&nbsp;&nbsp;&nbsp;";
    @$tool_content .= "<select name=\"minute\">";
    for ($m=0; $m<=55; $m=$m+5)
          $tool_content .= "<option value='$m'>$m</option>";
    $tool_content .= "</select></td>";
    $tool_content .= "</tr>";

$tool_content .= "<tr>
<th class='left'><b>$langEmail:</b></th><td><input type=\"text\" class='FormData_InputText' name=\"user_email\" size=\"40\" value=\"".$user_email."\"></td></tr>";
$tool_content .= "<tr><th class='left'><b>$langUsername:</b></th>
<td><input type=\"text\" name=\"user_username\" class='FormData_InputText' size=\"40\" value=\"".$user_username."\"></td></tr>";
$tool_content .= "<tr>
    <td colspan=\"2\"><br>
    <input type=\"hidden\" name=\"c\" value=\"searchlist\">
    <input type=\"submit\" name=\"search_submit\" value=\"$langSearch\"></td>
  </tr>";
$tool_content .= "</tbody></table></form>";
// end form

$tool_content .= "<br /><center><p><a href=\"index.php\">$langBack</a></p></center>";

// 3: display administrator menu
draw($tool_content,3, 'admin', $head_content);
?>
