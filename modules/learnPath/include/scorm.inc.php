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

/**===========================================================================
	scorm.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: scorm.inc.php Revision: 1.12.2.3
	      
	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description:

    @Comments:
 
    @todo: 
==============================================================================
*/

// change raw if value is a number between 0 and 100
if( isset($_POST['newRaw']) && is_num($_POST['newRaw']) && $_POST['newRaw'] <= 100 && $_POST['newRaw'] >= 0 )
{
	$sql = "UPDATE `" . $TABLELEARNPATHMODULE . "`
			SET `raw_to_pass` = " . (int) $_POST['newRaw'] . "
			WHERE `module_id` = " . (int) $_SESSION['lp_module_id'] . "
			AND `learnPath_id` = " . (int) $_SESSION['path_id'];
	db_query($sql);

	$dialogBox = $langRawHasBeenChanged;
}


//####################################################################################\\
//############################### DIALOG BOX SECTION #################################\\
//####################################################################################\\
if( !empty($dialogBox) )
{
	$tool_content .= $dialogBox;
}

// form to change raw needed to pass the exercise
$sql = "SELECT `lock`, `raw_to_pass`
        FROM `" . $TABLELEARNPATHMODULE."` AS LPM
       WHERE LPM.`module_id` = " . (int) $_SESSION['lp_module_id'] . "
         AND LPM.`learnPath_id` = " . (int) $_SESSION['path_id'];

$learningPath_module = db_query_fetch_all($sql);

if( isset($learningPath_module[0]['lock'])
	&& $learningPath_module[0]['lock'] == 'CLOSE'
	&& isset($learningPath_module[0]['raw_to_pass']) ) // this module blocks the user if he doesn't complete
{
	$tool_content .= "\n\n" . '<hr noshade="noshade" size="1" />' . "\n"
	.    '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
	.    '<label for="newRaw">' . $langChangeRaw . '</label>'."\n"
	.    '<input type="text" value="' . htmlspecialchars($learningPath_module[0]['raw_to_pass']) . '" name="newRaw" id="newRaw" size="3" maxlength="3" /> % ' . "\n"
	.    '<input type="submit" value="' . $langOk . '" />'."\n"
	.    '</form>'."\n\n"
    ;
}

?>
