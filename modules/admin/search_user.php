<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


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

$require_usermanage_user = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'admin.inc.php';
require_once 'include/jscalendar/calendar.php';

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$nameTools = $langSearchUser;

// Main body
$new = isset($_GET['new'])?$_GET['new']:'yes';	//variable of declaring a new search

// initialize the variables
$user_surname = $user_firstname = $user_username = $user_am = $user_type = $auth_type = $user_registered_at_flag = $user_registered_at = $user_email = $verified_mail = '';

// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='listusers.php?search=yes'>$langAllUsers</a></li>
      <li><a href='listusers.php?search=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";

// display the search form
$tool_content .= "
<form action='listusers.php?search=$new' method='post' name='user_search'>
<fieldset>
  <legend>$langUserData</legend>
  <table class='tbl' width='100%'>
  <tr>
    <th class='left'>$langName:</th>
    <td><input type='text' name='user_firstname' size='40' value='".q($user_firstname)."'></td>
  </tr>
  <tr>
    <th class='left' width='180'>$langSurname:</th>
    <td><input type='text' name='user_surname' size='40' value='".q($user_surname)."'></td>
  </tr>
  <tr>
    <th class='left'>$langAm:</th>
    <td><input type='text' name='user_am' size='30' value='".q($user_am)."'></td>
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
$tool_content .= "</td>
  </tr>
  <tr>
    <th class='left'>$langAuthMethod:</th>
    <td>";

// enalaktika mporoume na valoume mono tous energous tropous
//$auth_methods = get_auth_active_methods();
$authtype_data = $auth_ids;
$authtype_data[0] = $langAllAuthTypes;
$tool_content .= selection($authtype_data,"auth_type",$usertype_data[0]);
$tool_content .= "</td>
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
array('style' => 'width: 15em; text-align: center',
		'name' => 'date',
		'value' => ' '));

$tool_content .= $start_cal."&nbsp;&nbsp;&nbsp;";
@$tool_content .= "<select name='hour'>";
for ($h=0; $h<=24; $h++)
$tool_content .= "\n      <option value='$h'>$h</option>";
$tool_content .= "</select>&nbsp;&nbsp;&nbsp;";
@$tool_content .= "<select name='minute'>";
for ($min=0; $min<=55; $min=$min+5)
	$tool_content .= "<option value='$min'>$min</option>";
$tool_content .= "</select>\n    </td>";
$tool_content .= "\n  </tr>";

$tool_content .= "
  <tr>
    <th class='left'>$langEmailVerified:</th>
    <td>";

$verified_mail_data = array();
$verified_mail_data[0] = $m['pending'];
$verified_mail_data[1] = $m['yes'];
$verified_mail_data[2] = $m['no'];
$verified_mail_data[3] = $langAllUsers;
$tool_content .= selection($verified_mail_data,"verified_mail",3);
$tool_content .= "</td>
  </tr>
  <tr>
    <th class='left'>$langEmail:</th>
    <td><input type='text' name='user_email' size='40' value='".q($user_email)."'></td>
  </tr>
  <tr>
    <th class='left'><b>$langUsername:</b></th>
    <td><input type='text' name='user_username' size='40' value='".q($user_username)."'></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td colspan='2' class='right'>     
      <input type='submit' name='search_submit' value='$langSearch'>
    </td>
  </tr>
  </table>
</fieldset>
</form>";
// end form

$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

// 3: display administrator menu
draw($tool_content, 3, null, $head_content);
