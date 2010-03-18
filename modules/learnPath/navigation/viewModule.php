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
	viewModule.php
	@last update: 05-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: Auto to script praktika oloklhrwnei ton kyklo
                  zwhs ths html selidas otan perihgoumaste sta scorm paketa.
                  Dhladh: o xrhsths zhtaei na paei se allo scorm. Opote 
                  prwta fortwnoume auto to dummy html kai meta h javascript
                  autou tou script leei sto mainFrame na paei ekei pou o 
                  xrhsths zhthse na paei. Etsi, glitwnoume apo to bug opou
                  o javascript kwdikas sthn onunloadpage twn scorm den 
                  etrexe epeidh den prolabaine, epeidh ananewname kateu8eian
                  to mainFrame. Apla pros8esame auth th "stash" edw kai ola
                  kylane pleon mia xara.


    @Comments:

    @todo:
==============================================================================
*/


$require_current_course = TRUE;
require_once("../../../config/config.php");
require_once("../../../include/init.php");

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$clarolineRepositoryWeb = $urlServer."courses/".$currentCourseID;

// lib of this tool
require_once("../../../include/lib/learnPathLib.inc.php");
mysql_select_db($currentCourseID);

$unit_parm = isset($_SESSION['unit'])? ('?unit=' . $_SESSION['unit']): '';

if (isset($_GET['go']) and strlen($_GET['go']) > 0) {
	$redirect = "../".$_GET['go'].".php" . $unit_parm;
} else {
	$redirect="startModule.php?viewModule_id=".$_GET['viewModule_id'];
}

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<script type=\"text/javascript\">
    <!--//"."\n";
if (isset($_GET['go']) && strlen($_GET['go']) > 0) {
	echo "parent.parent.window.location.href=\"".$redirect."\";"."\n";
} else {
	echo "parent.parent.mainFrame.location.href=\"".$redirect."\";"."\n";
}
echo "    //--> 
    </script>
</head>
<body>
loading ...
</body>
</html>";
