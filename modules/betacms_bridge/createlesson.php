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
	createlesson.php
	@last update: 09-12-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/
$require_admin = TRUE;
require_once("../../include/baseTheme.php");
require_once("../admin/admin.inc.php");
$nameTools = $langCreatBCMSLesson;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$tool_content = "";

require_once("include/bcms.inc.php");
session_start();

if (!isset($_POST['submit'])) {
	// print form
	$tool_content .= repoForm(); 
}
else {
	if (empty($_POST[KEY_TITLE]) || empty($_POST[KEY_DESCRIPTION]) || empty($_POST[KEY_KEYWORDS]) 
		|| empty($_POST[KEY_AUTHORS]) ) {
		$tool_content .= "<p class=\"caution_small\">$langEmptyFields</p>
			<br/><br/><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	}
	else {
		$lesson = array(
			KEY_TITLE => $_POST[KEY_TITLE],
			KEY_DESCRIPTION => $_POST[KEY_DESCRIPTION],
			KEY_KEYWORDS => $_POST[KEY_KEYWORDS],
			KEY_COPYRIGHT => $_POST[KEY_COPYRIGHT],
			KEY_AUTHORS => $_POST[KEY_AUTHORS],
			KEY_PROJECT => $_POST[KEY_PROJECT],
			KEY_COMMENTS => $_POST[KEY_COMMENTS]
			);
			
		$flag = putLesson($_SESSION[BETACMSREPO], $lesson);
		
		if ($flag == true) {
			$tool_content .= "<p>".$GLOBALS['langBetaCMSLessonCreatedOK']."</p>
			<br/><br/><p align=\"right\"><a href='browserepo.php'>$langBack</a></p>";
		}
		else {
			$tool_content .= "<p class=\"caution_small\">".$GLOBALS['langBetaCMSLessonCreateFail']."</p>
			<br/><br/><p align=\"right\"><a href='createlesson.php'>$langAgain</a></p>";
		}
	}
}


draw($tool_content,3);



// HELPER FUNCTIONS

function repoForm() {
	return "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>".$GLOBALS['langBetaCMSCreateNewLesson']."</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSTitle']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_TITLE."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSDescription']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_DESCRIPTION."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSKeywords']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_KEYWORDS."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSCopyright']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_COPYRIGHT."'>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSAuthors']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_AUTHORS."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSProject']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_PROJECT."'>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSComments']."</b></th>
	<td><input class='FormData_InputText' type='text' name='".KEY_COMMENTS."'>&nbsp;</td>
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
	<p align='right'><a href='browserepo.php'>".$GLOBALS['langBack']."</p>";
}

?>