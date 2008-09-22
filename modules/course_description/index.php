<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/
/*
 * Index, Course Description
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This module displays the course description of every course. If the user
 * is the course's professor, he/she is shown of a link to add/edit the contents of
 * the module.
 *
 * Based on previous code of eclass 1.6
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
// support for math symbols
include '../../include/phpmathpublisher/mathpublisher.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DESCRIPTION');
/**************************************/

$nameTools = $langCourseProgram;
$tool_content = "";

if ($is_adminOfCourse) {
	$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
	  <li><a href=\"edit.php\">".$langEditCourseProgram."</a></li>
    </ul>
  </div>
  <br />";
}

$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
$res = db_query($sql, $currentCourseID);
if (mysql_num_rows($res) > 0) {
	while ($bloc = mysql_fetch_array($res)) {
	$tool_content .= "
    <table width=\"99%\" class=\"FormData\">
    <thead>
    <tr>
      <th class=\"left\" width=\"220\" style=\"border: 1px solid #edecdf;\"><u>".$bloc["title"]."</u></th>
      <td></td>
    </tr>
    </thead>
    </table>

    <table width=\"99%\" class=\"CourseDescr\">
    <thead>
    <tr>
      <td colspan=\"2\">".mathfilter(make_clickable(nl2br($bloc["content"])), 12, "../../courses/mathimg/")."</td>
    </tr>
    </tgead>
    </table>";

	$tool_content .= "<br />";
	}
} else {
	$tool_content .= "<br><p class=\"alert1\">$langThisCourseDescriptionIsEmpty</p>";
}

draw($tool_content, 2, 'course_description');
?>
