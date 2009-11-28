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
	browserepo.php
	@last update: 28-11-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
$nameTools = $langBrowseBCMSRepo;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$tool_content = "";
$caption = "";
$searchurl = "";
$tool_content = "";

require_once("include/bcms.inc.php");

$repo = array(
	"bridge_host" => "localhost",
	"bridge_port" => "8080",
	"bridge_context" => "JavaBridgeTemplate554",
	"bcms_host" => "localhost",
	"bcms_port" => "8080",
	"bcms_repo" => "altsolrepo",
	"bcms_user" => "SYSTEM",
	"bcms_pass" => "betaconcept"
);

// Fetch the list of Lessons from Beta CMS
$lessonList = getLessonsList($repo);


// Construct course list table
$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">
	<tbody><tr>
	<td class=\"odd\" colspan='6'><div align=\"right\">"."caption"."</div></td></tr>
	<tr>
	<th scope=\"col\">"."title"."</th>
	<th scope=\"col\">"."keywords"."</th>
	<th scope=\"col\">"."copyright"."</th>
	<th scope=\"col\">"."authors"."</th>
	<th scope=\"col\">"."project"."</th>
	<th scope=\"col\">"."actions"."</th>
	</tr>";

$k = 0;
for ($j = 0; $j < count($lessonList); $j++) {
	if ($k%2 == 0) {
		$tool_content .= "<tr>";
	} else {
		$tool_content .= "<tr class=\"odd\">";
	}
	
	$tool_content .= "<td>".$lessonList[$j]["title"]."</td>
		<td>".$lessonList[$j]["keywords"]."</td>
		<td>".$lessonList[$j]["copyright"]."</td>
		<td>".$lessonList[$j]["authors"]."</td>
		<td>".$lessonList[$j]["project"]."</td>
		<td><a href=''>[show]</a><a href=''>[import]</a>";
}

// Close table correctly
$tool_content .= "</tr></tbody></table>";
// Display link to index.php
$tool_content .= "<br/><p align=\"right\"><a href=\"../admin/index.php\">".$langBack."</a></p>";



// DEBUG
//$tool_content .= "<p><br/><br/><pre>";
//$tool_content .= print_r($lessonList, true);
//$tool_content .= "</pre><br/><br/></p>";

draw($tool_content,3);
?>