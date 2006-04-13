<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0  $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$require_current_course=TRUE;
$require_login=TRUE;
$langFiles="course_info";

include '../../include/init.php';

$local_style="table#info { background: $color2 }";
$nameTools = $langRefreshCourse;
begin_page();

if (!$is_adminOfCourse) 
{
	echo "Error! access by non-admin.";
	end_page();
	exit();
}

if(isset($submit)) {
	mysql_select_db($mysqlMainDb);
	if (isset($delusers)) 	
		delete_users();
	if (isset($delannounces))
		delete_announcements();

	mysql_select_db($currentCourseID);
        if (isset($delagenda))
		delete_agenda();
	if (isset($hideworks))
		hide_work();
	echo "<p><center><a href='infocours.php'>$langBack</a></center></p>";
				
} else {
	echo "<blockquote>$langRefresh</blockquote>";
	echo "<form action='refresh_course.php' method='post'>
		<table id='info'>
			<tr><td>&nbsp;</td></tr>
			<tr><td><input type='checkbox' name='delusers'></td>
				<td>$langUserDelCourse</td></tr>
			<tr><td><input type='checkbox' name='delannounces'></td>
				<td>$langAnnouncesDel</td></tr>
			<tr><td><input type='checkbox' name='delagenda'></td>
				<td>$langAgendaDel</td></tr>
			<tr><td><input type='checkbox' name='hideworks'></td>
				<td>$langHideWork</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan='2'><input type='submit' value='$langSubmit' name='submit'></td></tr>
		</table>
		</form>";
	
}
end_page();

	  
function delete_users() {
	global $currentCourseID,$langUsersDeleted;

	db_query("DELETE FROM cours_user WHERE code_cours='$currentCourseID' and statut <> '1'");
	echo "<p>$langUsersDeleted</p>";
}

function delete_announcements() {
	global $currentCourseID,$langAnnDeleted;

	db_query("DELETE FROM annonces WHERE code_cours='$currentCourseID'");
	echo "<p>$langAnnDeleted</p>";
}

function delete_agenda() {
	global $langAgendaDeleted;

	db_query("DELETE FROM agenda");
	echo "<p>$langAgendaDeleted</p>";
}

function hide_doc()  {
	global $langDocsDeleted;
	
	db_query("UPDATE document SET visibility='i'");
	echo "<p>$langDocsDeleted</p>";
}

function hide_work()  {
	global $langWorksDeleted;

	db_query("UPDATE assignments SET active=0");
	echo "<p>$langWorksDeleted</p>";
}



