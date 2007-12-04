<?php
/**=============================================================================
GUnet e-Class 2.0
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
$langFiles="course_info";
$require_prof = true;
//include '../../include/init.php';

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

		$tool_content .=  "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
						<p><b>$langRefreshSuccess</b></p>
		<ul class=\"listBullet\">
		";
		for ($i=0; $i< $count_events; $i++) {
			$tool_content .= "
			<li>$output[$i]</li>
			";
		}

		$tool_content .= "</ul>
		</td>
					</tr>
				</tbody>
			</table>
		";
	}



	$tool_content .="<p><a href='infocours.php'>$langBack</a></p>";

} else {
	
	$tool_content .= "
<form action='refresh_course.php' method='post'>
    <table width=\"99%\">
    <tbody>
    <tr>
      <th width='20%'>&nbsp;</th>
      <td colspan='2'><small><b>$langRefreshInfo</b></small></td>
    </tr>
    <tr>
      <th rowspan='4' class='left'>$langActions</th>
      <td width='1%'><input type='checkbox' name='delusers'></td>
      <td>$langUserDelCourse</td>
    </tr>
    <tr>
      <td><input type='checkbox' name='delannounces'></td>
      <td>$langAnnouncesDel</td>
    </tr>
    <tr>
      <td><input type='checkbox' name='delagenda'></td>
      <td>$langAgendaDel</td>
    </tr>
    <tr>
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

}

draw($tool_content, 2, 'course_info');


function delete_users() {
	global $currentCourseID,$langUsersDeleted;

	db_query("DELETE FROM cours_user WHERE code_cours='$currentCourseID' and statut <> '1'");
	return "<p>$langUsersDeleted</p>";
}

function delete_announcements() {
	global $currentCourseID,$langAnnDeleted;

	db_query("DELETE FROM annonces WHERE code_cours='$currentCourseID'");
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



