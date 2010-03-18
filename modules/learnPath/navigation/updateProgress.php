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
	updateProgress.php
	@last update: 22-07-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: updateProgress.php Revision: 1.11
	      
	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description: This script updates the student's progress for a learning
                  path module after browsing it.

    @Comments:
 
    @todo: 
==============================================================================
*/

$require_current_course = TRUE;
require_once("../../../include/lib/learnPathLib.inc.php");
require_once("../../../config/config.php");
require_once("../../../include/init.php");
mysql_select_db($currentCourseID);


$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$TOCurl = "../viewer_toc.php"; 
$TOCleft = "../toc.php";

/*********************/
/* HANDLING API FORM */
/*********************/

// handling of the API form if posted by the SCORM API
if(isset($_POST['ump_id']))
{ 
  // set values for some vars because we are not sure we will change it later
  $lesson_status_value = strtoupper($_POST['lesson_status']);
  $credit_value = strtoupper($_POST['credit']);
  
  //set values for the scores
  $raw_value = (int)$_POST['raw'];
  $scoreMin_value = (int)$_POST['scoreMin'];
  $scoreMax_value = (int)$_POST['scoreMax'];
  
  // next visit of the sco will not be the first so entry must be setted to RESUME
  $entry_value = "RESUME"; 
  
  // Set lesson status to COMPLETED if the SCO didn't change it itself.
  if ( $lesson_status_value == "NOT ATTEMPTED" )
      $lesson_status_value = "COMPLETED";

  // set credit if needed
  if ( $lesson_status_value == "COMPLETED" || $lesson_status_value == "PASSED")
  {
      if ( strtoupper($_POST['credit']) == "CREDIT" )
        $credit_value = "CREDIT";
  }
  
  //set maxScore to 100 if the SCO didn't change it itself, but gave raw
  if (isset($raw_value) && isset($scoreMax_value) && $raw_value > 0 && $raw_value <= 100 && $scoreMax_value == 0)
  	$scoreMax_value = 100;

  if(isScorm2004Time($_POST['session_time']))
  {
    $total_time_value = addScorm2004Time($_POST['total_time'], $_POST['session_time']);
    $session_time_formatted = addScorm2004Time("0000:00:00.00", $_POST['session_time']);
  }
  else if(isScormTime($_POST['session_time']))
  {
    $total_time_value = addScormTime($_POST['total_time'], $_POST['session_time']);
    $session_time_formatted = $_POST['session_time'];
  }
  else
  {
    $total_time_value = $_POST['total_time'];
  }
  
  $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` 
            SET 
                `lesson_location` = '". addslashes($_POST['lesson_location'])."',
                `lesson_status` = '". addslashes($lesson_status_value) ."',
                `entry` = '". addslashes($entry_value) ."',
                `raw` = '". $raw_value ."',
                `scoreMin` = '". $scoreMin_value ."',
                `scoreMax` = '". $scoreMax_value ."',
                `total_time` = '". addslashes($total_time_value) ."',
                `session_time` = '". addslashes($session_time_formatted) ."',
                `suspend_data` = '". addslashes($_POST['suspend_data'])."',
                `credit` = '". addslashes($credit_value) ."'
          WHERE `user_module_progress_id` = ". (int)$_POST['ump_id'];
  db_query($sql);
}

// display the form to accept new commit and
// refresh TOC frame, has to be done here to show recorded progression as soon as it is recorded
            
?>

<!-- API form -->
<html>
<head>
   <title>update progression</title>
<?php
if(isset($_POST['ump_id']))
{
?>
    <script type="text/javascript">
    <!--//
      parent.tocFrame.location.href="<?php echo $TOCurl; ?>";
      parent.tocleftFrame.location.href="<?php echo $TOCleft; ?>";
    //--> 
    </script>
<?php
}
?>
</head>
<body>
   <form name="cmiForm" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>"> 
	<input type="hidden" name="ump_id" />
	<input type="hidden" name="lesson_status" />
	<input type="hidden" name="lesson_location" />
    <input type="hidden" name="credit" />
	<input type="hidden" name="entry" />
	<input type="hidden" name="raw" />
    <input type="hidden" name="total_time" />
	<input type="hidden" name="session_time" />
	<input type="hidden" name="suspend_data" />
	<input type="hidden" name="scoreMin" />
	<input type="hidden" name="scoreMax" />
   </form>
</body>
</html>
