<?php 
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/*===========================================================================
	backFromExercise.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: backFromExercise.php Revision: 1.6
	      
	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description: This script refreshes the upper frame for the user to see 
                  his updated learning path progress and prompts him
                  to click next after finishing an exercise.

    @Comments:
 
    @todo: 
==============================================================================
*/

$require_current_course = TRUE;
$path2add = 3;
include("../../../include/init.php");
$TOCurl = "../viewer_toc.php?course=$code_cours";
?>
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset?>">
 <link href="../../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />
 <link href="../tool.css" rel="stylesheet" type="text/css" />
 <script>
  <!-- //
   parent.tocFrame.location.href="<?php echo $TOCurl; ?>";
  //-->
 </script>
</head>
<body>
 <center>
  <br /><br /><br />
  <p>
<?php
if($_GET['op'] == 'cancel')
{
    echo $langExerciseCancelled;
}
elseif($_GET['op'] == 'finish') // exercise done
{
    echo $langExerciseDone;
}
?>
   </p>
  </center>
 </body>
</html>
