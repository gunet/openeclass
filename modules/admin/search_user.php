<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

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
$lang_jscalendar = langname_to_code($language);

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langSearchUser;

// Main body
$new = isset($_GET['new'])?$_GET['new']:'yes';	//variable of declaring a new search

// initialize the variables
$user_surname = $user_firstname = $user_username = $user_am = $user_type = $user_registered_at_flag = $user_registered_at = $user_email = '';

// display the search form
$tool_content .= "<form action=\"listusers.php?search=".$new."\" method=\"post\" name=\"user_search\">
<table width=\"99%\">
<tbody><tr>
<th width=\"220\">&nbsp;</th>
<td><b>$langUserData</b></td>
</tr>
<tr>
<th class='left'>$langSurname:</th>
<td><input type=\"text\" class='FormData_InputText' name=\"user_surname\" size=\"40\" value=\"".$user_surname."\"></td>
</tr>
<tr>
<th class='left'>$langName:</th>
<td><input type=\"text\" class='FormData_InputText' name=\"user_firstname\" size=\"40\" value=\"".$user_firstname."\"></td>
</tr>
<tr>
<th class='left'>$langAm:</th>
<td><input type=\"text\" class='FormData_InputText' name=\"user_am\" size=\"30\" value=\"".$user_am."\"></td>
</tr>
<tr>
<th class='left'>$langUserType:</th>
<td>";

$usertype_data = array();
$usertype_data[0] = $langAllUsers;
$usertype_data[1] = $langTeacher;
$usertype_data[5] = $langStudent;
$usertype_data[10] = $langGuest;
$tool_content .= selection($usertype_data,"user_type",$usertype_data[0]);
$tool_content .= "</td></tr>
<tr>
<th class='left'>$langRegistrationDate:</th>
<td>";
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
$tool_content .= "\n      <option value='$h'>$h</option>";
$tool_content .= "</select>&nbsp;&nbsp;&nbsp;";
@$tool_content .= "<select name=\"minute\">";
for ($m=0; $m<=55; $m=$m+5)
	$tool_content .= "<option value='$m'>$m</option>";
$tool_content .= "</select>\n    </td>";
$tool_content .= "\n  </tr>";

$tool_content .= "<tr>
<th class='left'>$langEmail:</th>
<td><input type=\"text\" class='FormData_InputText' name=\"user_email\" size=\"40\" value=\"".$user_email."\"></td>
</tr>
<tr>
<th class='left'><b>$langUsername:</b></th>
<td><input type=\"text\" name=\"user_username\" class='FormData_InputText' size=\"40\" value=\"".$user_username."\"></td>
</tr>
<tr>
<th>&nbsp;</th>
<td colspan=\"2\">
<input type=\"hidden\" name=\"c\" value=\"searchlist\">
<input type=\"submit\" name=\"search_submit\" value=\"$langSearch\">
</td>
</tr>";
$tool_content .= "\n  </tbody>\n  </table>\n</form>";
// end form

$tool_content .= "<p>&nbsp;</p><p align=\"right\"><a href=\"index.php\">$langBack</a></p>";

// 3: display administrator menu
draw($tool_content,3, 'admin', $head_content);
?>
