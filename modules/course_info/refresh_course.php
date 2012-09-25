<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/**===========================================================================
refresh_course.php
@last update: 23-10-2006 by Pitsiougas Vagelis
@authors list: Karatzidis Stratos <kstratos@uom.gr>
Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
@Description: Refresh page for a course

==============================================================================*/

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_login = TRUE;

include '../../include/baseTheme.php';
include '../../include/jscalendar/calendar.php';

$nameTools = $langRefreshCourse;

if(isset($_POST['submit'])) {
	$output = array();
	mysql_select_db($mysqlMainDb);
	if (isset($_POST['delusers'])) {
		if (isset($_POST['before_date'])) {
			$output[] = delete_users(q($_POST['before_date']));
		} else {
			$output[] = delete_users();
		}
	}
	if (isset($_POST['delannounces'])) {
		$output[] = delete_announcements();
	}

	mysql_select_db($currentCourseID);
	if (isset($_POST['delagenda'])) {
		$output[] = delete_agenda();
	}
	if (isset($_POST['hideworks'])) {
		$output[] = hide_work();
	}

	if (($count_events = count($output)) > 0 ) {
		$tool_content .=  "<p class='success_small'>$langRefreshSuccess
		<ul class='listBullet'>";
		for ($i=0; $i< $count_events; $i++) {
			$tool_content .= "<li>$output[$i]</li>";
		}
		$tool_content .= "\n</ul>\n</p><br />";
	}
	$tool_content .= "<p align='right'><a href='infocours.php?course=$code_cours'>$langBack</a></p>";

} else {
	$lang_jscalendar = langname_to_code($language);
        $jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
        $head_content .= $jscalendar->get_load_files_code();
        $datetoday = date("Y-n-j",time());
	
	$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' method='post'>
	<table width='100%' class=\"FormData\">
	<tbody>
	<tr>
	  <th width='220'>&nbsp;</th>
	  <td colspan='2'>$langRefreshInfo<br /><br /><b>$langRefreshInfo_A :</b></td>
	</tr>
	<tr>
	  <th class='left'><img src=\"$themeimg/groups_on.png\" alt=\"\" height=\"16\" width=\"16\"> $langUsers</th>
	  <td width='1%'><input type='checkbox' name='delusers'></td>
	  <td>$langUserDelCourse </td>
	</tr>
	<tr>
	  <th><td>&nbsp;</th><td>". make_calendar('before_date')."</td>
	</tr>
	<tr>
	  <th class='left'><img src=\"$themeimg/announcements_on.png\" alt=\"\" height=\"16\" width=\"16\"> $langAnnouncements</th>
	  <td><input type='checkbox' name='delannounces'></td>
	  <td>$langAnnouncesDel</td>
	</tr>
	<tr>
	  <th class='left'><img src=\"$themeimg/calendar_on.png\" alt=\"\" height=\"16\" width=\"16\"> $langAgenda</th>
	  <td><input type='checkbox' name='delagenda'></td>
	  <td>$langAgendaDel</td>
	</tr>
	<tr>
	  <th class='left'><img src=\"$themeimg/assignments_on.png\" alt=\"\" height=\"16\" width=\"16\"> $langWorks</th>
	  <td><input type='checkbox' name='hideworks'></td>
	  <td>$langHideWork</td>
	</tr>
	<tr>
	  <th>&nbsp;</th>
	  <td colspan='2'><input type='submit' value='$langSubmitActions' name='submit'></td>
	</tr>
	</tbody>
	</table>
	</form>";	
	$tool_content .= "<p align='right'><a href='infocours.php?course=$code_cours'>$langBack</a></p>";
}

draw($tool_content, 2, null, $head_content);

function delete_users($date = '') {
	global $cours_id, $langUsersDeleted;

	if (isset($date)) {
		db_query("DELETE FROM cours_user WHERE cours_id = $cours_id
				AND statut <> 1
				AND statut <> 10
				AND reg_date < '$date'");	  
	} else {
		db_query("DELETE FROM cours_user WHERE cours_id = $cours_id AND statut <> 1 AND statut <> 10");
	}
        db_query("DELETE FROM group_members
                         WHERE group_id IN (SELECT id FROM `group` WHERE course_id = $cours_id) AND
                               user_id NOT IN (SELECT user_id FROM cours_user WHERE cours_id = $cours_id)"); 
	return "<p>$langUsersDeleted</p>";
}

function delete_announcements() {
	global $cours_id, $langAnnDeleted;

	db_query("DELETE FROM annonces WHERE cours_id = $cours_id");
	return "<p>$langAnnDeleted</p>";
}

function delete_agenda() {
	global $langAgendaDeleted, $currentCourseID, $mysqlMainDb;

	db_query("DELETE FROM agenda");
	##[BEGIN personalisation modification]############
	db_query("DELETE FROM ".$mysqlMainDb.".agenda WHERE lesson_code='$currentCourseID'");
	##[END personalisation modification]############
	return "<p>$langAgendaDeleted</p>";
}

function hide_doc()  {
	global $langDocsDeleted;

	db_query("UPDATE document SET visibility='i'");
	return "<p>$langDocsDeleted</p>";
}

function hide_work()  {
	global $langWorksDeleted;

	db_query("UPDATE assignments SET active=0");
	return "<p>$langWorksDeleted</p>";
}

function make_calendar($name) {
	
	global $datetoday, $jscalendar, $langBeforeRegDate;
	
	return "$langBeforeRegDate ".
		$jscalendar->make_input_field(
		array('showOthers' => true,
		      'showsTime' => false,
		      'align' => 'Tl',
		      'ifFormat' => '%Y-%m-%d'),
		array('name' => $name,
		      'value' => $datetoday,
		      'style' => 'width: 8em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center')) .
		"";
}