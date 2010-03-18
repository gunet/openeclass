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

/**===========================================================================
refresh_course.php
@last update: 23-10-2006 by Pitsiougas Vagelis
@authors list: Karatzidis Stratos <kstratos@uom.gr>
Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
@Description: Refresh page for a course

==============================================================================*/

$require_current_course=TRUE;
$require_login=TRUE;
$require_prof = true;

include '../../include/baseTheme.php';

$nameTools = $langRefreshCourse;

$tool_content = "";

if (!$is_adminOfCourse)
{
	$tool_content .= "Error! access by non-admin.";
	exit();
}

if(isset($submit)) {
	$output = array();
	mysql_select_db($mysqlMainDb);
	if (isset($delusers))
	$output[] = delete_users();
	if (isset($delannounces))
	$output[] = delete_announcements();

	mysql_select_db($currentCourseID);
	if (isset($delagenda))
	$output[] = delete_agenda();
	if (isset($hideworks))
	$output[] = hide_work();


	if (($count_events = count($output)) > 0 ) {

		$tool_content .=  "<p class=\"success_small\">$langRefreshSuccess
		<ul class=\"listBullet\">";
		for ($i=0; $i< $count_events; $i++) {
			$tool_content .= "
			<li>$output[$i]</li>			";
		}

		$tool_content .= "\n		</ul>\n</p><br />";
	}



	$tool_content .="<p align=\"right\"><a href='infocours.php'>$langBack</a></p>";

} else {

	$tool_content .= "
<form action='refresh_course.php' method='post'>

    <table width=\"99%\" class=\"FormData\">
    <tbody>
    <tr>
      <th width='220'>&nbsp;</th>
      <td colspan='2'>$langRefreshInfo<br /><br /><b>$langRefreshInfo_A :</b></td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../template/classic/img/users_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"> $langUsers</th>
      <td width='1%'><input type='checkbox' name='delusers'></td>
      <td>$langUserDelCourse</td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../template/classic/img/announcements_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"> $langAnnouncements</th>
      <td><input type='checkbox' name='delannounces'></td>
      <td>$langAnnouncesDel</td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../template/classic/img/calendar_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"> $langAgenda</th>
      <td><input type='checkbox' name='delagenda'></td>
      <td>$langAgendaDel</td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../template/classic/img/assignments_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"> $langWorks</th>
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

  $tool_content .= "<p align=\"right\"><a href=\"infocours.php\">$langBack</a></p>";
}

draw($tool_content, 2, 'course_info');


function delete_users() {
	global $cours_id, $langUsersDeleted;

	db_query("DELETE FROM cours_user WHERE cours_id = $cours_id and statut <> '1'");
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



