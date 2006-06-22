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
	template_empty_page.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================        
    @Description: This is an empty page for example on using eclass API

    @Comments:
 
    @todo: 
==============================================================================
*/

/***************
Initializations
****************/

$require_current_course = TRUE;
$langFiles = "learnPath";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$nameTools = $langlearnPath;


if($is_adminOfCourse) {       // Teacher View

} // Teacher View

else { // Student View

} // Student View



/*>>>>>>>>>>>> COMMON FOR THEACHERS AND STUDENTS  <<<<<<<<<<<<*/

/*>>>>>>>>>>>> END: COMMON TO TEACHERS AND STUDENTS <<<<<<<<<<<<*/

draw($tool_content, 2, "learnPath", $head_content);

?>
