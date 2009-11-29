<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2009  Greek Universities Network - GUnet
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
	viewlesson.php
	@last update: 28-11-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/
$require_admin = TRUE;
require_once("../../include/baseTheme.php");
require_once("../admin/admin.inc.php");
$nameTools = $langBrowseBCMSRepo;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$tool_content = "";

require_once("include/bcms.inc.php");
session_start();

if (isset($_GET['id']) && isset($_SESSION[BETACMSREPO])) {
	$repo = $_SESSION[BETACMSREPO];
	$coId = $_GET['id'];
	
	$co = getLesson($repo, $coId);
	$tool_content .= objectTable($co);
	
	
	// DEBUG 
	// $tool_content .= $_GET['id'];
	// $tool_content .= "<pre>";
	// $tool_content .= print_r($_SESSION[BETACMSREPO], true);
	// $tool_content .= "<pre>";
}
else {
	$tool_content .= "<p class=\"caution_small\">$langEmptyFields</p>
			<br/><br/><p align=\"right\"><a href='browserepo.php'>$langAgain</a></p>";
}

draw($tool_content,3);


// HELPER FUNCTIONS

function objectTable($obj) {
	return "<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>eClass Lesson Object View</b></td>
	</tr>
	<tr>
	<th class='left'><b>"."id"."</b></th>
	<td>".$obj[KEY_ID]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."title"."</b></th>
	<td>".$obj[KEY_TITLE]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."description"."</b></th>
	<td>".$obj[KEY_DESCRIPTION]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."keywords"."</b></th>
	<td>".$obj[KEY_KEYWORDS]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."copyright"."</b></th>
	<td>".$obj[KEY_COPYRIGHT]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."authors"."</b></th>
	<td>".$obj[KEY_AUTHORS]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."project"."</b></th>
	<td>".$obj[KEY_PROJECT]."</td>
	</tr>
	<tr>
	<th class='left'><b>"."comments"."</b></th>
	<td>".$obj[KEY_COMMENTS]."</td>
	</tr>
	</tbody>
	</table>
	<br />
	<p align='right'><a href='importlesson.php?id=".$obj[KEY_ID]."'>[import]</a>
	<a href='browserepo.php'>".$GLOBALS['langBack']."</p>";
}
?>