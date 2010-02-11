<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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


/* ===========================================================================
	speedSubscribe.php
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Allow admin to enroll as professor to any course

 	This script allows the administrator to enroll as professor to any course

 	The user can : - Enroll to any course
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Get all courses
  2) Enroll to selected courses
  3) Display all on an HTML page

==============================================================================*/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;

include '../../include/baseTheme.php';

$nameTools = $langSpeedSubscribe;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Register administrator to selected courses
if (isset($_POST['submit'])) {
        $courses = isset($_POST['course'])? $_POST['course']: array();
        $old = isset($_POST['old'])? $_POST['old']: array();
	// Register admin to courses selected
        foreach ($courses as $cid) {
                $cid = intval($cid);
               db_query("INSERT IGNORE INTO cours_user (cours_id, user_id, statut, reg_date)
                                VALUES ($cid, $uid, '1', CURDATE())");
               db_query("UPDATE cours_user SET statut = 1 WHERE cours_id = $cid AND user_id = $uid");
	} 
	// Unregister admin from unselected courses
        foreach ($old as $cid) {
                $cid = intval($cid);
                if (!in_array($cid, $courses)) {
                       db_query("DELETE FROM cours_user WHERE cours_id = $cid AND user_id = $uid");
                }
	} 
	$tool_content .= "<p>$lang_subscribe_processing<br />$langSuccess</p>";
}
// Display list of courses
$tool_content .= "
<form name='speedSub' action='".$_SERVER['PHP_SELF']."' method='post'>";
// Select all courses
$q = db_query("SELECT cours.cours_id cid, faculte.name faculty, cours.code code,
                      cours.intitule title, cours.titulaires profs,
                      cours_user.statut statut
               FROM cours LEFT JOIN faculte ON cours.faculteid = faculte.id
                          LEFT JOIN cours_user ON cours_user.cours_id = cours.cours_id
               GROUP BY cid
               ORDER BY faculty, code");

$prev_faculty = '';
$firstfac = true;
while ($mycours = mysql_fetch_array($q)) {
	// Print header for each faculty
	if ($mycours['faculty'] != $prev_faculty or $firstfac) {
                $faculty = empty($mycours['faculty'])? '?': $mycours['faculty'];
		if ($firstfac) {
			$tool_content .= "<table class='FormData' width='100%' align='left'>";
			$firstfac = false;
		}
                $tool_content .= "
			<tr>
			<th width='30'>&nbsp;</th>
			<th class='left'><b>$faculty</b></th>
			</tr>";
	}
	$prev_faculty = $mycours['faculty'];
        $check = ($mycours['statut'] == 1)? 'checked="1" ': '';
        $old = $mycours['statut']? "<input type='hidden' name='old[]' value='$mycours[cid]' />": '';
        $tool_content .= "<tr>
	<th width='30'><input type='checkbox' name='course[]' value='$mycours[cid]' $check/>$old</th>
	<td><b>$mycours[title]</b> ($mycours[code]) <br />$mycours[profs]</td>
	</tr>";
}
if (!$firstfac) {
	$tool_content .= "
  <tr>
    <th width='30'>&nbsp;</th>
    <td class='left'><input type='submit' name='submit' value='".$langRegistration."' /></td>
  </tr>
  </table> ";
}

$tool_content .= "</form><br /><p align='right'><a href='index.php'>$langBack</a></p>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
