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
$lessonList = getLessonsList($repo);




// DEBUG
$tool_content = "";
$tool_content .= "<pre>";
$tool_content .= print_r($lessonList, true);
$tool_content .= "</pre>";

draw($tool_content,3);
?>