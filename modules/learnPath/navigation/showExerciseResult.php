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


// This script is a replicate from
// exercice/exercise_result.php, but it is modified for the
// displaying needs of the learning path tool. The core
// application logic remains the same.

// Ta objects prepei na ginoun include prin thn init
// gia logous pou sxetizontai me to object loading 
// apo to session
require_once('../../exercice/exercise.class.php');
require_once('../../exercice/question.class.php');
require_once('../../exercice/answer.class.php');

$require_current_course = TRUE;
$path2add = 3;
include("../../../include/init.php");

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once('../../../include/lib/learnPathLib.inc.php');
require_once('../../../include/lib/textLib.inc.php');

// Ksekiname to diko mas html output giati probaloume mesa se iframe
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
    ."\n<html>\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="../../../template/'.$theme.'/theme.css" rel="stylesheet" type="text/css" />'."\n"
    .'<title>'.$langExercicesResult.'</title>'."\n"
    .'</head>'."\n"
    .'<body style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto; background-color: #ffffff;">'."\n"
    .'<div id="content">';

$nameTools = $langExercicesResult;

if (isset($_GET['exerciseId'])) {
	$exerciseId = intval($_GET['exerciseId']);
}    

// ypologismos tou xronou pou xreiasthke o xrhsths gia thn oloklhrwsh ths askhshs
if (isset($_SESSION['exercise_begin_time'][$exerciseId])) {
   $timeToCompleteExe = time() - $_SESSION['exercise_begin_time'][$exerciseId];
}


if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the above variables are empty or incorrect, stops the script
if(!is_array($_SESSION['exerciseResult'][$exerciseId]) 
            || !is_array($_SESSION['questionList'][$exerciseId]) 
            || !is_object($objExercise)) {
	echo $langExerciseNotFound;
	exit();
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
$exerciseDescription_temp = mathfilter($exerciseDescription_temp, 12, "{$webDir}courses/mathimg/");
$displayResults = $objExercise->selectResults();
$displayScore = $objExercise->selectScore(); 

echo "<table class='tbl_border' width='99%'>
  <tr class='odd'>
    <td colspan='2'><b>".stripslashes($exerciseTitle)."</b>
    <br/>".stripslashes($exerciseDescription_temp)."
    </td>
  </tr>
  </table>";

// probaloume th dikia mas forma me to diko mas action 
// kai me to katallhlo hidden pedio
echo "<form method='GET' action='backFromExercise.php'><input type='hidden' name='course' value='$code_cours'>".
	"<input type='hidden' name='op' value='finish'>";

$i = $totalScore = $totalWeighting=0;

// for each question

foreach($_SESSION['questionList'][$exerciseId] as $questionId) {
	// gets the student choice for this question
        $choice = @$_SESSION['exerciseResult'][$exerciseId][$questionId];
	// creates a temporary Question object
	$objQuestionTmp=new Question();
	$objQuestionTmp->read($questionId);

	$questionName=$objQuestionTmp->selectTitle();
	$questionName=$questionName;
	$questionDescription=$objQuestionTmp->selectDescription();
	$questionDescription=$questionDescription;
	$questionDescription_temp = nl2br(make_clickable($questionDescription));
	$questionDescription_temp = mathfilter($questionDescription_temp, 12, "{$webDir}courses/mathimg/");
	$questionWeighting=$objQuestionTmp->selectWeighting();
	$answerType=$objQuestionTmp->selectType();

	// destruction of the Question object
	unset($objQuestionTmp);

	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE)
	{
		$colspan=4;
	}
	elseif($answerType == MATCHING)
	{
		$colspan=2;
	}
	else
	{
		$colspan=1;
	}
	$iplus=$i+1;
	echo ("
    <br/>
    <table width='99%' class='tbl'>
    <tr class='odd'>
      <td colspan='${colspan}'><b><u>$langQuestion</u>: $iplus</b></td>
    </tr>
    <tr>
      <td class='even' colspan='${colspan}'>
        <b>$questionName</b>
        <br />
        $questionDescription_temp
        <br/><br/>
      </td>
    </tr>");

	$questionScore=0;

	if ($displayResults == 1) {
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
			echo ("
    <tr class='even'>
      <td width='50' valign='top'><b>$langChoice</b></td>
      <td width='50' class='center' valign='top'><b>$langExpectedChoice</b></td>
      <td valign='top'><b>$langAnswer</b></td>
      <td valign='top'><b>$langComment</b></td>
    </tr>");
		} elseif($answerType == FILL_IN_BLANKS) {
			echo ("
    <tr>
      <td class='even'><b>$langAnswer</b></td>
    </tr>");
		} else {
			echo ("
    <tr class='even'>
      <td><b>$langElementList</b></td>
      <td><b>$langCorrespondsTo</b></td>
    </tr>");
		}
	}
	// construction of the Answer object
	$objAnswerTmp=new Answer($questionId);
	$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
	
	for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
		$answer=$objAnswerTmp->selectAnswer($answerId);
		$answerComment=$objAnswerTmp->selectComment($answerId);
		$answerCorrect=$objAnswerTmp->isCorrect($answerId);
		$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
		// support for math symbols
		$answer = mathfilter($answer, 12, "{$webDir}courses/mathimg/");
		$answerComment = mathfilter($answerComment, 12, "{$webDir}courses/mathimg/");

		switch($answerType)
		{
			// for unique answer
			case UNIQUE_ANSWER :	$studentChoice=($choice == $answerId)?1:0;
				if($studentChoice) {
					$questionScore+=$answerWeighting;
					$totalScore+=$answerWeighting;
				}
				break;
			// for multiple answers
			case MULTIPLE_ANSWER :	$studentChoice=@$choice[$answerId];
				if($studentChoice) {
					$questionScore+=$answerWeighting;
					$totalScore+=$answerWeighting;
					}
				break;
			// for fill in the blanks
			case FILL_IN_BLANKS : // splits text and weightings that are joined with the char '::'
					list($answer,$answerWeighting)=explode('::',$answer);
					// splits weightings that are joined with a comma
					$answerWeighting=explode(',',$answerWeighting);
					// we save the answer because it will be modified
					$temp=$answer;
					$answer='';
					$j=0;
					// the loop will stop at the end of the text
					while(1)
					{
					// quits the loop if there are no more blanks
					if(($pos = strpos($temp,'[')) === false) {
						// adds the end of the text
						$answer.=$temp;
						break;
					}
				// adds the piece of text that is before the blank and ended by [
					$answer.=substr($temp,0,$pos+1);
					$temp=substr($temp,$pos+1);
					// quits the loop if there are no more blanks
					if(($pos = strpos($temp,']')) === false) {
						// adds the end of the text
						$answer.=$temp;
						break;
					}
					$choice[$j]=trim(stripslashes($choice[$j]));
				// if the word entered is the same as the one defined by the professor
				if(strtolower(substr($temp,0,$pos)) == strtolower($choice[$j])) {
					// gives the related weighting to the student
					$questionScore+=$answerWeighting[$j];
					// increments total score
					$totalScore+=$answerWeighting[$j];
					// adds the word in green at the end of the string
					$answer.=$choice[$j];
				}
			// else if the word entered is not the same as the one defined by the professor
					elseif(!empty($choice[$j])) {
						// adds the word in red at the end of the string, and strikes it
						$answer.='<font color="red"><s>'.$choice[$j].'</s></font>';
					} else {
						// adds a tabulation if no word has been typed by the student
						$answer.='&nbsp;&nbsp;&nbsp;';
					}
					// adds the correct word, followed by ] to close the blank
					$answer.=' / <font color="green"><b>'.substr($temp,0,$pos).'</b></font>]';
					$j++;
					$temp=substr($temp,$pos+1);
					}
				break;
			// for matching
			case MATCHING :	if($answerCorrect) {
						if($answerCorrect == $choice[$answerId]) {
							$questionScore+=$answerWeighting;
							$totalScore+=$answerWeighting;
							$choice[$answerId]=$matching[$choice[$answerId]];
						}
						elseif(!$choice[$answerId]) {
							$choice[$answerId]='&nbsp;&nbsp;&nbsp;';
						} else {
							$choice[$answerId]='<font color="red">
							<s>'.$matching[$choice[$answerId]].'</s>
							</font>';
						}
					} else {
						$matching[$answerId]=$answer;
					}
				break;
			case TRUE_FALSE : $studentChoice=($choice == $answerId)?1:0;
					if($studentChoice) {
						$questionScore+=$answerWeighting;
						$totalScore+=$answerWeighting;
					}
				break;
		}	// end switch()
		if ($displayResults == 1) { 
			if($answerType != MATCHING || $answerCorrect) {
				if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
					echo ("
                                            <tr class='even'>
                                              <td>
                                              <div align='center'><img src='$themeimg/");
					if ($answerType == UNIQUE_ANSWER || $answerType == TRUE_FALSE) {
						echo ("radio");
					} else {
						echo ("checkbox");
					}
					if ($studentChoice) {
						echo ("_on");
					} else {
						echo ('_off');
					}
					echo (".png' border='0' /></div>
                                              </td>
                                              <td><div align='center'>");
	
					if ($answerType == UNIQUE_ANSWER || $answerType == TRUE_FALSE) {
						echo ("<img src=\"$themeimg/radio");
					} else {
						echo ("<img src=\"$themeimg/checkbox");
					}
					if ($answerCorrect) {
						echo ("_on");
					} else {
						echo ("_off");	
					}
					echo (".png\" /></div>");	
					echo ("</td>
                                              <td>${answer}</td>
                                              <td>");
					if ($studentChoice) {
						echo nl2br(make_clickable($answerComment)); 
					} else { 
						echo ('&nbsp;');
					} 
					echo ("</td></tr>");
				} elseif($answerType == FILL_IN_BLANKS) {
					echo ("
                                        <tr class='even'>
                                          <td>".nl2br($answer)."</td>
                                        </tr>");
				} else {
					echo ("
                                        <tr class='even'>
                                          <td>${answer}</td>
                                          <td>${choice[$answerId]} / <font color='green'><b>${matching[$answerCorrect]}</b></font></td>
                                        </tr>");
				}
			} 
		} // end of if
	}	// end for()
	 if ($displayScore == 1) {
		echo ("
                <tr class='even'>
                  <td colspan='$colspan' class='odd'><div align='right'>
                            $langQuestionScore: <b>$questionScore/$questionWeighting</b></div>
                  </td>
                </tr>");
	}
	echo ("</table>");
	// destruction of Answer
	unset($objAnswerTmp);
	$i++;
	$totalWeighting += $questionWeighting;
}	// end foreach()

// update db with results
$eid=$objExercise->selectId();
mysql_select_db($currentCourseID);

$sql="SELECT RecordStartDate FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
$result=db_query($sql);
$attempt = count($result);

$sql="SELECT MAX(eurid) FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
$result = db_query($sql);
$row= mysql_fetch_row($result);
$eurid = $row[0];

// record results of exercise
$sql="UPDATE exercise_user_record SET TotalScore='$totalScore', TotalWeighting='$totalWeighting',
	attempt='$attempt' WHERE eurid='$eurid'";
db_query($sql, $currentCourseID);

if ($displayScore == 1) {
	echo ("
    <br/>
    <table width='99%' class='tbl'>
    <tr class='odd'>
	<td class='right'>$langYourTotalScore: <b>$totalScore/$totalWeighting</b>
      </td>
    </tr>
    </table>");
}
echo ("
  <br/>
  <div align='center'><input type='submit' value='$langFinish' /></div>
  <br />
  </form><br />");

// apo edw kai katw einai LP specific
// record progression
// update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous
if($uid)
{
	// exercices can have a negative score, we don't accept that in LP
	// so if totalScore is negative use 0 as result
	$totalScore = max($totalScore, 0);
	if ( $totalWeighting != 0 )
	{
			$newRaw = @round($totalScore/$totalWeighting*100);
	}
	else
	{
			$newRaw = 0;
	}

	$scoreMin = 0;
	$scoreMax = $totalWeighting;
	// need learningPath_module_id and raw_to_pass value
	$sql = "SELECT LPM.`raw_to_pass`, LPM.`learnPath_module_id`, UMP.`total_time`, UMP.`raw`
			FROM `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEUSERMODULEPROGRESS."` AS UMP
			WHERE LPM.`learnPath_id` = '".(int)$_SESSION['path_id']."'
			AND LPM.`module_id` = '".(int)$_SESSION['lp_module_id']."'
			AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
			AND UMP.`user_id` = ".(int)$uid;
	$query = db_query($sql);
	$row = mysql_fetch_array($query);

	$scormSessionTime = seconds_to_scorm_time($timeToCompleteExe);

	// build sql query
	$sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` SET ";
	// if recorded score is less then the new score => update raw, credit and status

	if ($row['raw'] < $totalScore)
	{
		// update raw
		$sql .= "`raw` = $totalScore,";
		// update credit and statut if needed ( score is better than raw_to_pass )
		if ($newRaw >= $row['raw_to_pass'])
		{
			$sql .= "`credit` = 'CREDIT',`lesson_status` = 'PASSED',";
		}
		else // minimum raw to pass needed to get credit
		{
			$sql .= "`credit` = 'NO-CREDIT',`lesson_status` = 'FAILED',";
		}
	}// else don't change raw, credit and lesson_status

	// default query statements
	$sql .= "	`scoreMin` 	= " . (int)$scoreMin . ",
				`scoreMax` 	= " . (int)$scoreMax . ",
				`total_time`	= '".addScormTime($row['total_time'], $scormSessionTime)."',
				`session_time`	= '".$scormSessionTime."'
				WHERE `learnPath_module_id` = ". (int)$row['learnPath_module_id']."
				AND `user_id` = " . (int)$uid . "";
	db_query($sql);
	}

echo "</div></body></html>"."\n";
?>