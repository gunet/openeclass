<?
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
	delcours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Delete a course

 	This script allows the administrator to delete a course

 	The user can : - Confirm for course deletion
 	               - Delete a cours
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Confirm course deletion
  2) Delete course
  3) Display all on an HTML page

==============================================================================*/

$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
if(!isset($_GET['c'])) { die(); }

$nameTools = $langCourseDel;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listcours.php", "name" => $langListCours);
$tool_content = "";

// Delete course
if (isset($_GET['delete']) && isset($_GET['c']))  {
	db_query("DROP DATABASE `".mysql_real_escape_string($_GET['c'])."`");
        mysql_select_db($mysqlMainDb);
        $code = quote($_GET['c']);
	db_query("DELETE FROM cours_faculte WHERE code = $code");
	db_query("DELETE FROM cours_user WHERE cours_id =
                        (SELECT cours_id FROM cours WHERE code = $code)");
	db_query("DELETE FROM annonces WHERE cours_id =
                        (SELECT cours_id FROM cours WHERE code = $code)");
	db_query("DELETE FROM cours WHERE code = $code");
	@mkdir("../../courses/garbage");
	rename("../../courses/".$_GET['c'], "../../courses/garbage/".$_GET['c']);
	$tool_content .= "<p>".$langCourseDelSuccess."</p>";
}
// Display confirmatiom message for course deletion
else {
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='".mysql_real_escape_string($_GET['c'])."'"));

	$tool_content .= "<table><caption>".$langCourseDelConfirm."</caption><tbody>";
	$tool_content .= "<tr>
	<td><br />".$langCourseDelConfirm2." <em>".htmlspecialchars($_GET['c'])."</em>;<br /><br /><i>".$langNoticeDel."</i><br /><br /></td>
	</tr>";
	$tool_content .= "<tr>
	<td><ul><li><a href='".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])."&amp;delete=yes'><b>$langYes</b></a><br />&nbsp;</li>
	<li><a href=\"listcours.php?c=".htmlspecialchars($_GET['c'])."\"><b>$langNo</b></a></li></ul></td>
	</tr>";
	$tool_content .= "</tbody></table><br />";
}
// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
	$tool_content .= "<center><p><a href='listcours.php?c=".htmlspecialchars($_GET['c'])."'>".$langBack."</a></p></center>";
}
// Go back to listcours.php
else {
	$tool_content .= "<center><p><a href=\"listcours.php\">$langBack</a></p></center>";
}

draw($tool_content, 3);