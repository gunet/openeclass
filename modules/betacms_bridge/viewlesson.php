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
	viewlesson.php
	@last update: 10-01-2010 by Thanos Kyritsis
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
	$ret = "<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>".$GLOBALS['langBetaCMSEclassLessonObjectView']."</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSId']."</b></th>
	<td>".$obj[KEY_ID]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSTitle']."</b></th>
	<td>".$obj[KEY_TITLE]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSDescription']."</b></th>
	<td>".$obj[KEY_DESCRIPTION]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSKeywords']."</b></th>
	<td>".$obj[KEY_KEYWORDS]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSCopyright']."</b></th>
	<td>".$obj[KEY_COPYRIGHT]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSAuthors']."</b></th>
	<td>".$obj[KEY_AUTHORS]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSProject']."</b></th>
	<td>".$obj[KEY_PROJECT]."</td>
	</tr>
	<tr>
	<th class='left'><b>".$GLOBALS['langBetaCMSComments']."</b></th>
	<td>".$obj[KEY_COMMENTS]."</td>
	</tr>
	<tr><td></td></tr>
	<tr>
	<th width='220'>&nbsp;</th>
	<td><b>".$GLOBALS['langBetaCMSUnits']." (".$GLOBALS['langBetaCMSTotalNumber'].": ".$obj[KEY_UNITS_SIZE].")</b></td>
	</tr>";
	
	foreach ($obj[KEY_UNITS] as $key => $unit) {
		$ret .= "<tr>
			<th class='left'><b>".$GLOBALS['langBetaCMSUnitTitle'].$key."</b></th>
			<td>".$unit[KEY_TITLE]."</td>
			</tr>
			<tr>
			<th class='left'><b>".$GLOBALS['langBetaCMSUnitDescription'].$key."</b></th>
			<td>".$unit[KEY_DESCRIPTION]."</td>
			</tr>";
		
		$ret .= filesTableRows($GLOBALS['langBetaCMSUnitScormFiles'], $unit[KEY_SCORMFILES_SIZE], $unit[KEY_SCORMFILES]);
		
		$ret .= filesTableRows($GLOBALS['langBetaCMSUnitDocumentFiles'], $unit[KEY_DOCUMENTFILES_SIZE], $unit[KEY_DOCUMENTFILES]);
		
		$ret .= "<tr>
		<th width='220'>&nbsp;</th>
		<td><b>".$GLOBALS['langBetaCMSUnitTexts']." (".$GLOBALS['langBetaCMSTotalNumber'].": ".$unit[KEY_TEXTS_SIZE].")</b></td>
		</tr>";
		
		foreach ($unit[KEY_TEXTS] as $unittextkey => $unittext) {
			$ret .= "<tr>
				<th class='left'><b>".$GLOBALS['langBetaCMSText'].$unittextkey."</b></th>
				<td>".$unittext."</td>
				</tr>";
		}
	}
	
	$ret .= "<tr><td></td></tr>";
	$ret .= filesTableRows($GLOBALS['langBetaCMSScormFiles'], $obj[KEY_SCORMFILES_SIZE], $obj[KEY_SCORMFILES]);
	$ret .= "<tr><td></td></tr>";
	$ret .= filesTableRows($GLOBALS['langBetaCMSDocumentFiles'], $obj[KEY_DOCUMENTFILES_SIZE], $obj[KEY_DOCUMENTFILES]);
	
	$ret .= "</tbody>
	</table>
	<br />
	<p align='right'><a href='importlesson.php?id=".$obj[KEY_ID]."'>[import]</a>
	<a href='browserepo.php'>".$GLOBALS['langBack']."</p>";
	
	return $ret;
}

function filesTableRows($localizedMessage, $size, $objects) {
	$ret .= "<tr>
	<th width='220'>&nbsp;</th>
	<td><b>".$localizedMessage." (".$GLOBALS['langBetaCMSTotalNumber'].": ".$size.")</b></td>
	</tr>";
	
	foreach ($objects as $key => $obj) {
		$ret .= "<tr>
			<th class='left'><b>".$GLOBALS['langBetaCMSSourceFilename'].$key."</b></th>
			<td>".$obj[KEY_SOURCEFILENAME]."</td>
			</tr>
			<tr>
			<th class='left'><b>".$GLOBALS['langBetaCMSMimeType'].$key."</b></th>
			<td>".$obj[KEY_MIMETYPE]."</td>
			</tr>
			<tr>
			<th class='left'><b>".$GLOBALS['langBetaCMSCalculatedSize'].$key."</b></th>
			<td>".$obj[KEY_CALCULATEDSIZE]."</td>
			</tr>";
	}
	
	return $ret;
}
?>