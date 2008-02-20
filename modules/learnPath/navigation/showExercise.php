<?php 

/**=============================================================================
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

/**===========================================================================
	showExercise.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
    @Description: This script is a replicate from 
                  exercice/exercice.php, but it is modified for the 
                  displaying needs of the learning path tool. The core 
                  application logic remains the same.
                  It also contains a replicate from exercice/exercise.lib.php

    @Comments:
 
    @todo: 
==============================================================================
*/

require_once('../../exercice/exercise.class.php');
require_once('../../exercice/question.class.php');
require_once('../../exercice/answer.class.php');
 
// answer types
define('UNIQUE_ANSWER',1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS',3);
define('MATCHING',4);

$require_current_course = TRUE;

require_once("../../../config/config.php");
require_once ('../../../include/init.php');

require_once('../../../include/lib/textLib.inc.php'); 

$nameTools = $langExercice;

$picturePath='../../'.$currentCourseID.'/image';

$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// if the user has clicked on the "Cancel" button
if(isset($buttonCancel)) {
	// returns to the exercise list
	header('Location: backFromExercise.php?op=cancel');
	exit();
}

// if the user has submitted the form
if(isset($formSent)) {
	// initializing
	if(!is_array(@$exerciseResult)) {
		$exerciseResult=array();
	}

	// if the user has answered at least one question
	if(@is_array($choice))
	{
		if($exerciseType == 1)
		{
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult=$choice;
		}
		else
		{
			// gets the question ID from $choice. It is the key of the array
			list($key)=array_keys($choice);

			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key]))
			{
				// stores the user answer into the array
				$exerciseResult[$key]=$choice[$key];
			}
		}
	}

	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	session_register('exerciseResult');

	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions) {
		// goes to the script that will show the result of the exercise
		header('Location: showExerciseResult.php');
		exit();
	}
}


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
$exerciseDescription=$objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType=$objExercise->selectType();
$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
$exerciseAllowedAttemtps=$objExercise->selectAttemptsAllowed();

$eid_temp = $objExercise->selectId();
$exerciseTimeConstrainSecs = time() + ($exerciseTimeConstrain*60);
$RecordStartDate = date("Y-m-d H:i:s",time());

// start time of the exercise (use session because in post it could be modified
// easily by user using a development bar in mozilla for an example)
// need to check if it already exists in session for sequential exercises
if(!isset($_SESSION['exeStartTime']) )
{
   $_SESSION['exeStartTime'] = time();
}


if ((!$is_adminOfCourse)&&(isset($uid))) { //if registered student
$CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record WHERE eid='$eid_temp' AND uid='$uid'", $currentCourseID));
++$CurrentAttempt[0];
	if (!isset($_COOKIE['marvelous_cookie'])) { // either expired or begin again
		if ((!$exerciseAllowedAttemtps)||($CurrentAttempt[0] <= $exerciseAllowedAttemtps)) { // if it is allowed begin again
			if (!$exerciseTimeConstrainSecs)
				$exerciseTimeConstrainSecs = 9999999;
				$CookieLife=time()+$exerciseTimeConstrainSecs;
			if (!setcookie("marvelous_cookie", $eid_temp, $CookieLife, "/")) {
				header('Location: ../../exercice/exercise_redirect.php');
				exit();
			}
			// record start of exercise
			mysql_select_db($currentCourseID);
			$sql="INSERT INTO `exercise_user_record` (eurid,eid,uid,RecordStartDate,RecordEndDate,".
				"TotalScore,TotalWeighting,attempt) VALUES".
				"(0,'$eid_temp','$uid','$RecordStartDate','','','',1)";
			$result=mysql_query($sql) or die("Error : SELECT in file ".__FILE__." at line ".__LINE__);		
		} else {  // not allowed begin again
			header('Location: ../../exercice/exercise_redirect.php');
			exit();
		}
	}
}

if(!session_is_registered('questionList')) {
	// selects the list of question ID
	$questionList=$randomQuestions?$objExercise->selectRandomList():$objExercise->selectQuestionList();

	// saves the question list into the session
	session_register('questionList');
}

$nbrQuestions=sizeof($questionList);

// if questionNum comes from POST and not from GET
if(!isset($questionNum) || $_POST['questionNum']) {
	// only used for sequential exercises (see $exerciseType)
	if(!isset($questionNum)) {
		$questionNum=1;
	} else {
		$questionNum++;
	}
}

echo "<html>"."\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="../../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />'."\n"
    .'<link href="../tool.css" rel="stylesheet" type="text/css" />'."\n"
    .'<title>'.$langExercice.'</title>'."\n"
    .'</head>'."\n"
    .'<body style="margin: 2px;">'."\n"
    .'<div align="left">';

if(@$_POST['questionNum']) {
	$QUERY_STRING="questionNum=$questionNum";
}

	$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
	echo <<<cData
		<h3>${exerciseTitle}</h3>

		<p>${exerciseDescription_temp}</p>
		
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<form method="post" action="${_SERVER['PHP_SELF']}" autocomplete="off">
		<input type="hidden" name="formSent" value="1">
		<input type="hidden" name="exerciseType" value="${exerciseType}">
		<input type="hidden" name="questionNum" value="${questionNum}">
		<input type="hidden" name="nbrQuestions" value="${nbrQuestions}">
		<tr>
		<td>
		<table width="100%" cellpadding="4" cellspacing="2" border="0">
cData;


$i=0;
foreach($questionList as $questionId) {
	$i++;
	//echo "<h1>".$i."</h1>";

	// for sequential exercises
	if($exerciseType == 2)
	{
		//echo "<h1>"."type=2"."</h1>";
		// if it is not the right question, goes to the next loop iteration
		if($questionNum != $i)
		{
			continue;
		}
		else
		{
			// if the user has already answered this question
			if(isset($exerciseResult[$questionId])) {
				// construction of the Question object
				$objQuestionTmp=new Question();

				// reads question informations
				$objQuestionTmp->read($questionId);

				$questionName=$objQuestionTmp->selectTitle();

				// destruction of the Question object
				unset($objQuestionTmp);

				echo '<tr><td>'.$langAlreadyAnswered.' &quot;'.$questionName.'&quot;</td></tr>';
				break;
			}
		}
	}
	echo "<tr bgcolor=\"#E6E6E6\"><td valign=\"top\" colspan=\"2\">".$langQuestion." ".$i; 
	
	if($exerciseType == 2) 
		echo " / ".$nbrQuestions;
	echo "</td></tr>";

	// shows the question and its answers
	showQuestion($questionId);

	// for sequential exercises
	if($exerciseType == 2)
	{
		// quits the loop
		break;
	}
}	// end foreach()
	echo "</table></td></tr><tr><td align=\"center\"><br><input type=\"submit\" value=\"";
	if ($exerciseType == 1 || $nbrQuestions == $questionNum)
		echo $langOk." &gt;"."\">";
	else	
		echo $langNext." &gt;"."\">";

 	echo " <input type=\"submit\" name=\"buttonCancel\" value=\"${langCancel}\">";
	echo "</td></tr></form></table>";
	echo "</div></body>"."\n";
	echo "</html>"."\n";
/////////////////////////////////////////////////////////////////////////////////

function showQuestion($questionId, $onlyAnswers=false)
{
	global $picturePath, $webDir;
 	require_once "$webDir"."/modules/latexrender/latex.php";

	// construction of the Question object
	$objQuestionTmp=new Question();

	// reads question informations
	if(!$objQuestionTmp->read($questionId))
	{
		// question not found
		return false;
	}

	$answerType=$objQuestionTmp->selectType();

	if(!$onlyAnswers)
	{
		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();
	// latex support

		$questionName=latex_content($questionName);
		$questionDescription=latex_content($questionDescription);

	$questionDescription_temp = nl2br(make_clickable($questionDescription));
	echo <<<cData
		<tr>
		  <td valign="top" colspan="2">
				${questionName}
		  </td>
		</tr>
		<tr>
		  <td valign="top" colspan="2">
			<i>${questionDescription_temp}</i>
		  </td>
		</tr>
cData;

		if(file_exists($picturePath.'/quiz-'.$questionId))
		{

	echo "<tr><td align=\"center\" colspan=\"2\"><img src=\"".
		${picturePath}."/quiz-".${questionId}."\" border=\"0\"></td></tr>";

		}
	}  // end if(!$onlyAnswers)

	// construction of the Answer object
	$objAnswerTmp=new Answer($questionId);

	$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

	// only used for the answer type "Matching"
	if($answerType == MATCHING)
	{
		$cpt1='A';
		$cpt2=1;
		$Select=array();
	}

	for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
	{
		$answer=$objAnswerTmp->selectAnswer($answerId);
		$answerCorrect=$objAnswerTmp->isCorrect($answerId);
		// latex support
		$answer=latex_content($answer);
		if($answerType == FILL_IN_BLANKS)
		{
			// splits text and weightings that are joined with the character '::'
			list($answer)=explode('::',$answer);

			// replaces [blank] by an input field
			$answer=ereg_replace('\[[^]]+\]','<input type="text" name="choice['.$questionId.'][]" size="10">',nl2br($answer));
		}

		// unique answer
		if($answerType == UNIQUE_ANSWER)
		{
	echo <<<cData
		<tr>
		  <td width="5%" align="center">
			<input type="radio" name="choice[${questionId}]" value="${answerId}">
		  </td>
		  <td width="95%">
			${answer}
		  </td>
		</tr>
cData;
		}
		// multiple answers
		elseif($answerType == MULTIPLE_ANSWER)
		{
	echo <<<cData
		<tr>
		  <td width="5%" align="center">
			<input type="checkbox" name="choice[${questionId}][${answerId}]" value="1">
		  </td>
		  <td width="95%">
			${answer}
		  </td>
		</tr>
cData;
		}
		// fill in blanks
		elseif($answerType == FILL_IN_BLANKS)
		{

			echo "<tr><td colspan=\"2\">${answer}</td></tr>";

		}
		// matching
		else
		{
			if(!$answerCorrect)
			{
				// options (A, B, C, ...) that will be put into the list-box
				$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$Select[$answerId]['Reponse']=$answer;
			}
			else
			{

				echo <<<cData
					<tr>
				  <td colspan="2">
					<table border="0" cellpadding="0" cellspacing="0" width="95%">
					<tr>
					  <td width="40%" valign="top"><b>${cpt2}.</b> ${answer}</td>
					  <td width="20%" align="center">&nbsp;&nbsp;<select name="choice[${questionId}][${answerId}]">
						<option value="0">--</option>
cData;

	            // fills the list-box
	            foreach($Select as $key=>$val)
	            {

			echo "<option value=\"${key}\">${val['Lettre']}</option>";

				}  // end foreach()

		  echo "</select>&nbsp;&nbsp;</td><td width=\"40%\" valign=\"top\">";
		  
		  if(isset($Select[$cpt2])) 
		  	echo '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']; 
		  else 
		  	echo '&nbsp;';
		  	
		  echo	"</td></tr></table></td></tr>";

				$cpt2++;

				// if the left side of the "matching" has been completely shown
				if($answerId == $nbrAnswers)
				{
					// if it remains answers to shown at the right side
					while(isset($Select[$cpt2]))
					{
/////////////////////////////////////////////////////////////////////////////////
		echo "<tr><td colspan=\"2\">".
			"<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"95%\">".
			"<tr><td width=\"60%\" colspan=\"2\">&nbsp;</td><td width=\"40%\" align=\"right\" valign=\"top\">".
			"<b>".$Select[$cpt2]['Lettre'].".</b> ".$Select[$cpt2]['Reponse']."</td></tr></table></td></tr>";
/////////////////////////////////////////////////////////////////////////////////
						$cpt2++;
					}	// end while()
				}  // end if()
			}
		}
	}	// end for()

	// destruction of the Answer object
	unset($objAnswerTmp);

	// destruction of the Question object
	unset($objQuestionTmp);

	return $nbrAnswers;
}
?>
