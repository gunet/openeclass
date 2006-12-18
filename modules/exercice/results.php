<?php 
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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

/*===========================================================================
	work.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the work tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
*/

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');
 
// answer types
define('UNIQUE_ANSWER',1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS',3);
define('MATCHING',4);

$require_current_course = TRUE;
$langFiles='exercice';
$require_help = TRUE;
$helpTopic = 'Exercise';

//include('../../include/init.php');

include '../../include/baseTheme.php';

$tool_content = "";

$nameTools = $langExercice;

include('../../include/lib/textLib.inc.php');

$picturePath='../../'.$currentCourseID.'/image';

$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);
//begin_page($nameTools);

// if the object is not in the session
if(!session_is_registered('objExercise')) {
	// construction of Exercise
	$objExercise=new Exercise();

	// if the specified exercise doesn't exist or is disabled
	//if(!$objExercise->read($exerciseId) || (!$objExercise->selectStatus() && !$is_allowedToEdit))
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit))
		{
		die($langExerciseNotFound);
	}

	// saves the object into the session
	session_register('objExercise');
}

$exerciseTitle=$objExercise->selectTitle();
//$exerciseDescription=$objExercise->selectDescription();
//$randomQuestions=$objExercise->isRandom();
//$exerciseType=$objExercise->selectType();


$tool_content .= "<h3>$exerciseTitle</h3>";


/////////////////
mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record`";
$result = mysql_query($sql);
//$i=0;
while($row=mysql_fetch_array($result)) {
	//++$i;
	//$tool_content .= $i;
	$sid = $row['uid'];
	$StudentName = db_query("select nom,prenom from user where user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	
	$tool_content .= "<table border=\"1\"><tr><td colspan=\"3\">".$theStudent["nom"]." ".$theStudent["prenom"]."</td></tr>";
	$tool_content .= "<tr><td>".$langExerciseStart."</td>";
	$tool_content .= "<td>".$langExerciseEnd."</td>";
	$tool_content .= "<td>".$langYourTotalScore2."</td></tr>";
	
	mysql_select_db($currentCourseID);
	$sql2="SELECT RecordStartDate,RecordEndDate,TotalScore,TotalWeighting  FROM `exercise_user_record` WHERE uid='$sid' AND eid='$exerciseId'";
	$result2 = mysql_query($sql2);
	while($row2=mysql_fetch_array($result2)) {

		$RecordEndDate = $row2['RecordEndDate'];
		$tool_content .= "<tr><td>".$row2['RecordStartDate']."</td>";
	
		if ($RecordEndDate != "0000-00-00 00:00:00") { 
			$tool_content .= "<td>".$RecordEndDate."</td>";
		} else { // user termination or excercise time limit exceeded
			$tool_content .= "<td>".$langResultsFailed."</td>";
		}
		
		$tool_content .= "<td>".$row2['TotalScore']. "/".$row2['TotalWeighting']."</td></tr>";
	}
$tool_content .= "</table><br><br>";
}

draw($tool_content, 2);
?>	
