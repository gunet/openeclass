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
require_once("../../include/baseTheme.php");
require_once("../admin/admin.inc.php");
$nameTools = $langBrowseBCMSRepo;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$tool_content = "";

require_once("include/bcms.inc.php");
session_start();

if (!ini_get('allow_url_include')) {
	$tool_content .= "<p class=\"caution_small\">".
		$GLOBALS['langNeedAllowUrlInclude'].
		"</p>";
	draw($tool_content,3);
	die();
}

if (!ini_get('allow_url_fopen')) {
	$tool_content .= "<p class=\"caution_small\">".
		$GLOBALS['langNeedAllowUrlFopen'].
		"</p>";
	draw($tool_content,3);
	die();
}

if (isset($_GET['logout'])) {
	unset($_SESSION[BETACMSREPO]);
	unset ($_POST['submit']);
}

if (isset($_SESSION[BETACMSREPO])) {
	$_POST[BRIDGE_HOST] = $_SESSION[BETACMSREPO][BRIDGE_HOST];
	$_POST[BRIDGE_CONTEXT] = $_SESSION[BETACMSREPO][BRIDGE_CONTEXT];
	$_POST[BCMS_HOST] = $_SESSION[BETACMSREPO][BCMS_HOST];
	$_POST[BCMS_REPO] = $_SESSION[BETACMSREPO][BCMS_REPO];
	$_POST[BCMS_USER] = $_SESSION[BETACMSREPO][BCMS_USER];
	$_POST[BCMS_PASS] = $_SESSION[BETACMSREPO][BCMS_PASS];
	$_POST['submit'] = 'submit';
}

if (!isset($_POST['submit'])) {
	// print form
	$tool_content .= repoForm(); 
}
else {
	if (empty($_POST[BRIDGE_HOST]) || empty($_POST[BRIDGE_CONTEXT]) 
		|| empty($_POST[BCMS_HOST]) || empty($_POST[BCMS_REPO]) 
		|| empty($_POST[BCMS_USER]) || empty($_POST[BCMS_PASS]) ) {
		$tool_content .= "<p class=\"caution_small\">$langEmptyFields</p>
			<br/><br/><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	}
	else {
		$repo = array(
			BRIDGE_HOST => $_POST[BRIDGE_HOST],
			BRIDGE_CONTEXT => $_POST[BRIDGE_CONTEXT],
			BCMS_HOST => $_POST[BCMS_HOST],
			BCMS_REPO => $_POST[BCMS_REPO],
			BCMS_USER => $_POST[BCMS_USER],
			BCMS_PASS => $_POST[BCMS_PASS]
		);
		
		if (!checkConnectivityToRepo($repo)) {
			$tool_content .= "<p class=\"caution_small\">".
				$GLOBALS['langFailConnectBetaCMSBridge'].
				"</p>".
				"<br/><br/><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
			draw($tool_content,3);
			die();
		}
		
		// Fetch the list of Lessons from Beta CMS
		$lessonList = getLessonsList($repo);
		
		
		// Construct course list table
		$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">
			<tbody><tr>
			<td class=\"odd\" colspan='2'><div align=\"left\"><a href=\"browserepo.php?logout\">".$GLOBALS['langBetaCMSLogout']."</a></div></td>
			<td class=\"odd\" colspan='4'><div align=\"right\"><a href=\"createlesson.php\">".$GLOBALS['langBetaCMSCreateNewLesson']."</a></div></td>
			</tr>
			<tr>
			<th scope=\"col\">".$GLOBALS['langBetaCMSTitle']."</th>
			<th scope=\"col\">".$GLOBALS['langBetaCMSKeywords']."</th>
			<th scope=\"col\">".$GLOBALS['langBetaCMSCopyright']."</th>
			<th scope=\"col\">".$GLOBALS['langBetaCMSAuthors']."</th>
			<th scope=\"col\">".$GLOBALS['langBetaCMSProject']."</th>
			<th scope=\"col\">".$GLOBALS['langBetaCMSActions']."</th>
			</tr>";
		
		$k = 0;
		for ($j = 0; $j < count($lessonList); $j++) {
			if ($k%2 == 0) {
				$tool_content .= "<tr>";
			} else {
				$tool_content .= "<tr class=\"odd\">";
			}
			
			$tool_content .= "<td>".$lessonList[$j][KEY_TITLE]."</td>
				<td>".$lessonList[$j][KEY_KEYWORDS]."</td>
				<td>".$lessonList[$j][KEY_COPYRIGHT]."</td>
				<td>".$lessonList[$j][KEY_AUTHORS]."</td>
				<td>".$lessonList[$j][KEY_PROJECT]."</td>
				<td><a href='viewlesson.php?id=".$lessonList[$j][KEY_ID]."'>[show]</a>
				<a href='importlesson.php?id=".$lessonList[$j][KEY_ID]."'>[import]</a>";
		}
		
		// Close table correctly
		$tool_content .= "</tr></tbody></table>";
		// Display link to index.php
		$tool_content .= "<br/><p align=\"right\"><a href=\"../admin/index.php\">".$langBack."</a></p>";
		
		$_SESSION[BETACMSREPO] = $repo;
	}
}


// DEBUG
//$tool_content .= "<p><br/><br/><pre>";
//$tool_content .= print_r($lessonList, true);
//$tool_content .= "</pre><br/><br/></p>";

draw($tool_content,3);



// HELPER FUNCTIONS

function repoForm() {
	return "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>".$GLOBALS['langBetaCMSLoginProperties']."</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSBridgeHost']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".BRIDGE_HOST."' value='localhost:8080'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSContext']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".BRIDGE_CONTEXT."' value='JavaBridgeTemplate5541'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSHost']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".BCMS_HOST."' value='localhost'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSRepository']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".BCMS_REPO."' value='altsolrepo'>&nbsp;(*)</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSUsername']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".BCMS_USER."' value='SYSTEM'>&nbsp;(*)</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSPassword']."</b></th>
	<td><input class='FormData_InputText' type='password' name='".BCMS_PASS."' value='betaconcept'>&nbsp;(*)</b></td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td><input type='submit' name='submit' value='".$GLOBALS['langSubmit']."' >
		<small>".$GLOBALS['langRequiredFields']."</small></td>
	</tr>
	</tbody>
	</table>
	<input type='hidden' name='submit' value='submit' >
	</form>
	<br />
	<p align='right'><a href='../admin/index.php'>".$GLOBALS['langBack']."</p>";
}

?>