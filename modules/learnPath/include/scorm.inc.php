<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	scorm.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
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
			WHERE `module_id` = " . (int) $_SESSION['module_id'] . "
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
       WHERE LPM.`module_id` = " . (int) $_SESSION['module_id'] . "
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