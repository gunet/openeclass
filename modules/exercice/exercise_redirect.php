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


include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');
 
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

include '../../include/baseTheme.php';

$tool_content = "";
$nameTools = $langExercicesView;
include('../../include/lib/textLib.inc.php');

$picturePath='../../courses/'.$currentCourseID.'/image';
$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);

// if the object is not in the session
if(!session_is_registered('objExercise')) {
	// construction of Exercise
	$objExercise=new Exercise();

	// if the specified exercise doesn't exist or is disabled
	if(@(!$objExercise->read($exerciseId) && (!$is_allowedToEdit)))
		{
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2);
		exit();
	}
	// saves the object into the session
	session_register('objExercise');
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));

$tool_content .= "<table class='Exercise' width='99%'>
      <thead><tr>
        <td colspan='2'>
        <b>".stripslashes($exerciseTitle)."</b>
        <br/><br/>
        ".stripslashes($exerciseDescription_temp)."
        </td>
      </tr>
      </thead></table>";

$tool_content .= "<br/><table width='99%' class='Question'>
      <thead><tr>
      <td class='alert1'>$langExerciseExpiredTime</td>
      </tr>
      <tr>
      <td><br/><br/><br/><div align='center'><a href='exercice.php'>$langBack</a></div></td>
      </tr>
      </thead></table>"; 

draw($tool_content, 2, 'exercice');
?>