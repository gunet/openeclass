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
// exercice/exercice_submit.php, but it is modified for the
// displaying needs of the learning path tool. The core
// application logic remains the same.
// It also contains a replicate from exercice/exercise.lib.php

// Ta objects prepei na ginoun include prin thn init
// gia logous pou sxetizontai me to object loading 
// apo to session
require_once('../../exercice/exercise.class.php');
require_once('../../exercice/question.class.php');
require_once('../../exercice/answer.class.php');

$require_current_course = TRUE;

$path2add = 3;
include("../../../include/init.php");

// Genikws o kwdikas apo edw kai katw kanei akribws o,ti kai to
// exercice_submit.php. Oi mones diafores einai xrhsh twn echo
// anti gia to tool_content kai kapoies mikrodiafores opou xreiazetai 
require_once('../../../include/lib/textLib.inc.php');
// answer types
define('UNIQUE_ANSWER', 1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);
$nameTools = $langExercice;
$picturePath='../../'.$currentCourseID.'/image';

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

if (isset($_GET['course'])) {
	$course = intval($_GET['course']);       
}

if (isset($_GET['exerciseId'])) {
	$exerciseId = intval($_GET['exerciseId']);
}

// if the user has clicked on the "Cancel" button
if(isset($_POST['buttonCancel'])) {
	// returns to the exercise list
	header('Location: backFromExercise.php?course='.$code_cours.'&op=cancel');
	exit();
}

if (!isset($_SESSION['exercise_begin_time'][$exerciseId])) {
	$_SESSION['exercise_begin_time'][$exerciseId] = time();
}


// if the user has submitted the form
if (isset($_POST['formSent'])) {
	$exerciseType = isset($_POST['exerciseType'])?intval($_POST['exerciseType']):'';
        $exerciseId = isset($_POST['exerciseId'])?intval($_POST['exerciseId']):'';
	$questionNum  = isset($_POST['questionNum'])?$_POST['questionNum']:'';
	$nbrQuestions = isset($_POST['nbrQuestions'])?$_POST['nbrQuestions']:'';
	$exerciseTimeConstrain = isset($_POST['exerciseTimeConstrain'])?$_POST['exerciseTimeConstrain']:'';
	$eid_temp = isset($_POST['eid_temp'])?$_POST['eid_temp']:'';
	$RecordStartDate = isset($_POST['RecordStartDate'])?$_POST['RecordStartDate']:'';
	$choice = isset($_POST['choice'])?$_POST['choice']:'';
        if (isset($_SESSION['exerciseResult'][$exerciseId])) {
		$exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
	} else {
		$exerciseResult = array();
	}
	
	if (isset($exerciseTimeConstrain) and $exerciseTimeConstrain != 0) { 
		$exerciseTimeConstrain = $exerciseTimeConstrain*60;
		$exerciseTimeConstrainSecs = time() - $exerciseTimeConstrain;		
                $_SESSION['exercise_end_time'][$exerciseId] = $exerciseTimeConstrainSecs;
                if ($_SESSION['exercise_end_time'][$exerciseId] - $_SESSION['exercise_begin_time'][$exerciseId] > $exerciseTimeConstrain) {
			unset($_SESSION['exercise_begin_time']);
			unset($_SESSION['exercise_end_time']);
                        header('Location: ../../exercice/exercise_redirect.php?course='.$code_cours.'&exerciseId='.$exerciseId);
			exit();
		} 
	}
	$RecordEndDate = date("Y-m-d H:i:s", time());
	
	// if the user has answered at least one question
	if(is_array($choice)) {
		if($exerciseType == 1) {
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult=$choice;
		} else {
			// gets the question ID from $choice. It is the key of the array
			list($key)=array_keys($choice);
			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key])) {
				// stores the user answer into the array
				$exerciseResult[$key]=$choice[$key];
			}
		}
	}

	// the script "exercise_result.php" will take the variable $exerciseResult from the session
        $_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions) {
		// goes to the script that will show the result of the exercise
		header("Location: showExerciseResult.php?course=$code_cours&exerciseId=$exerciseId");
		exit();
	}
} // end of submit

if (isset($_SESSION['objExercise'][$exerciseId])) {
	$objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'][$exerciseId])) {
	// construction of Exercise
	$objExercise = new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_editor)) {
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2);
		exit();
	}
	// saves the object into the session
	$_SESSION['objExercise'][$exerciseId] = $objExercise;
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstrain = $objExercise->selectTimeConstrain();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();
$RecordStartDate = date("Y-m-d H:i:s", time());

$temp_CurrentDate = date("Y-m-d H:i");
$temp_StartDate = $objExercise->selectStartDate();
$temp_EndDate = $objExercise->selectEndDate();
$temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
$temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));

if (!$is_editor) {
    $error = FALSE;
    // check if exercise has expired or is active
    $CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record 
                    WHERE eid='$eid_temp' AND uid='$uid'", $currentCourseID));
    ++$CurrentAttempt[0];
    if ($exerciseAllowedAttempts > 0 and $CurrentAttempt[0] > $exerciseAllowedAttempts) {
        $message = $langExerciseMaxAttemptsReached;
        $error = TRUE;
    }
    if (($temp_CurrentDate < $temp_StartDate) || ($temp_CurrentDate >= $temp_EndDate)) {
        $message = $langExerciseExpired;
        $error = TRUE;
    }
    if ($error == TRUE) {
        echo "<br/><td class='alert1'>$message</td>";
        exit();
    }
}

if (isset($_SESSION['questionList'][$exerciseId])) {
	$questionList = $_SESSION['questionList'][$exerciseId];
}

if (!isset($_SESSION['questionList'][$exerciseId])) {
	// selects the list of question ID
	$questionList = $randomQuestions ? $objExercise->selectRandomList() : $objExercise->selectQuestionList();
	// saves the question list into the session
        $_SESSION['questionList'][$exerciseId] = $questionList;
}

$nbrQuestions = sizeof($questionList);

// if questionNum comes from POST and not from GET
if(!isset($questionNum) || $_POST['questionNum']) {
	// only used for sequential exercises (see $exerciseType)
	if(!isset($questionNum)) {
		$questionNum=1;
	} else {
		$questionNum++;
	}
}

if(@$_POST['questionNum']) {
	$QUERY_STRING="questionNum=$questionNum";
}

$exerciseDescription_temp = standard_text_escape($exerciseDescription);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"   "http://www.w3.org/TR/html4/frameset.dtd">'
    ."\n<html>\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="../../../template/'.$theme.'/theme.css" rel="stylesheet" type="text/css" />'."\n"
    .'<title>'.$langExercice.'</title>'."\n"
    .'</head>'."\n"
    .'<body style="margin: 0px; padding-left: 5px; height: 100%!important; height: auto; background-color: #ffffff;">'."\n"
    .'<div id="content">';

echo ("
  <table width='99%' class='tbl_border'>
  <tr class='odd'>
    <td colspan=\"2\"><b>$exerciseTitle</b>
    <br/>
    $exerciseDescription_temp</td>
  </tr>
  </table>
  <br />

  <form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours'>
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />	
  <input type='hidden' name='exerciseType' value='$exerciseType' />	
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstrain' value='$exerciseTimeConstrain' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='RecordStartDate' value='$RecordStartDate' />");

    $i=0;
    foreach($questionList as $questionId) {
            $i++;
            // for sequential exercises
            if($exerciseType == 2) {
                    // if it is not the right question, goes to the next loop iteration
                    if($questionNum != $i) {
                            continue;
                    } else {
                            // if the user has already answered this question
                            if(isset($exerciseResult[$questionId])) {
                                    // construction of the Question object
                                    $objQuestionTmp=new Question();
                                    // reads question informations
                                    $objQuestionTmp->read($questionId);
                                    $questionName=$objQuestionTmp->selectTitle();
                                    // destruction of the Question object
                                    unset($objQuestionTmp);
                                    echo '<div class\"alert1\" '.$langAlreadyAnswered.' &quot;'.$questionName.'&quot;</div>';
                                    break;
                            }
                    }
            }
            // shows the question and its answers
            echo ("<table width=\"99%\" class=\"tbl\">
          <tr class='odd'>
            <td colspan=\"2\"><b><u>".$langQuestion."</u>: ".$i);

            if($exerciseType == 2) { 
                    echo ("/".$nbrQuestions);
            }
            echo ("</b></td></tr>");
            showQuestion($questionId);
            echo  "<tr><td class='even' colspan=\"2\">&nbsp;</td></tr></table>";
            // for sequential exercises
            if($exerciseType == 2) {
                    // quits the loop
                    break;
            }
    }	// end foreach()

    if (!$questionList) {
            echo ("
      <table width=\"99%\" class=\"tbl\">
      <tr class='odd'>
        <td colspan='2'>
          <p class='caution'>'$langNoAnswer</p>
        </td>
      </tr>
      </table>");	 
    } else {
	echo "<br/><table width='99%' class='tbl'><tr>
               <td><div align='center'><input type='submit' value=\"";
                if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
                        echo "$langCont\" />&nbsp;";
                } else {
                        echo $langNext." &gt;"."\" />";
                }
            echo "<input type='submit' name='buttonCancel' value='$langCancel' /></div></td></tr>
              <tr>
                <td colspan=\"2\">&nbsp;</td>
              </tr>
              </table>";
    }	
echo "</form>";
echo "</div></body>"."\n";
echo "</html>"."\n";

// auth edw h function einai kata bash idia me thn antistoixh sto
// exercise.lib.php, mono pou anti gia xrhsh tou tool_content kanei 
// echo ton html kwdika epeidh 8eloume na ton deixnoume mesa sto 
// iframe tou learningPath.
// ta global vars pou orizontai, den exoun na kanoun se kati me to
// register_globals, apla einai scoping twn metablhtwn pou yparxoun
// pio panw se auto edw to php arxeio.
function showQuestion($questionId, $onlyAnswers = false) {
	global $picturePath;
	global $langNoAnswer, $langColumnA, $langColumnB, $langMakeCorrespond;

	// construction of the Question object
	$objQuestionTmp=new Question();
	// reads question informations
	if(!$objQuestionTmp->read($questionId)) {
		// question not found
		return false;
	}
	$answerType=$objQuestionTmp->selectType();

	if(!$onlyAnswers) {
		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();	
		$questionDescription_temp = standard_text_escape($questionDescription);
		echo "<tr class='even'>
                    <td colspan='2'><b>$questionName</b><br />
                    $questionDescription_temp
                    </td>
                    </tr>";
		if(file_exists($picturePath.'/quiz-'.$questionId)) {
                    echo "
                      <tr class='even'>
                        <td class='center' colspan='2'><img src='".${'picturePath'}."/quiz-".${'questionId'}."'></td>
                      </tr>";
		}
	}  // end if(!$onlyAnswers)

	// construction of the Answer object
	$objAnswerTmp=new Answer($questionId);
	$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

	// only used for the answer type "Matching"
	if($answerType == MATCHING) {
		$cpt1='A';
		$cpt2=1;
		$Select=array();
		echo "
              <tr class='even'>
                <td colspan='2'>
                  <table class='tbl'>
                  <tr>
                    <td width='200'><b>$langColumnA</b></td>
                    <td width='130'><b>$langMakeCorrespond</b></td>
                    <td width='200'><b>$langColumnB</b></td>
                  </tr>
                  </table>
                </td>
              </tr>";
	}

	for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
		$answer = standard_text_escape($objAnswerTmp->selectAnswer($answerId));
		$answerCorrect=$objAnswerTmp->isCorrect($answerId);
		if($answerType == FILL_IN_BLANKS) {
			// splits text and weightings that are joined with the character '::'
			list($answer)=explode('::',$answer);
			// replaces [blank] by an input field
                        $answer = preg_replace('/\[[^]]+\]/', 
                                    '<input type="text" name="choice['.$questionId.'][]" size="10" />', 
                                    standard_text_escape($answer));
		}
		// unique answer
		if($answerType == UNIQUE_ANSWER) {
			echo "
                      <tr class='even'>
                        <td class='center' width='1'>
                          <input type='radio' name='choice[${questionId}]' value='${answerId}' />
                        </td>
                        <td>${answer}</td>
                      </tr>";
		}
		// multiple answers
		elseif($answerType == MULTIPLE_ANSWER) {
			echo ("
                      <tr class='even'>
                        <td width='1' align='center'>
                          <input type='checkbox' name='choice[${questionId}][${answerId}]' value='1' />
                        </td>
                        <td>${answer}</td>
                      </tr>");
		}
		// fill in blanks
		elseif($answerType == FILL_IN_BLANKS) {
			echo ("
                      <tr class='even'>
                        <td colspan='2'>${answer}</td>
                      </tr>");
		}
		// matching
		elseif($answerType == MATCHING) { 
			if(!$answerCorrect) {
				// options (A, B, C, ...) that will be put into the list-box
				$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$Select[$answerId]['Reponse']=$answer;
			}
			else
			{
				echo ("
                              <tr class='even'>
                                <td colspan='2'>
                                  <table class='tbl'>
                                  <tr>
                                    <td width='200'><b>${cpt2}.</b> ${answer}</td>
                                    <td width='130'><div align='center'>
                                     <select name='choice[${questionId}][${answerId}]'>
                                       <option value='0'>--</option>");

				// fills the list-box
				 foreach($Select as $key=>$val) {
					 echo ("
                                            <option value=\"${key}\">${val['Lettre']}</option>");
				 }
				 echo ("
                                     </select></div>
                                    </td>
                                    <td width='200'>");
				 if(isset($Select[$cpt2]))
				       echo ('<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']);
				 else
				       echo ('&nbsp;');

				echo ("</td></tr></table></td></tr>");
				$cpt2++;
				// if the left side of the "matching" has been completely shown
				if($answerId == $nbrAnswers) {
					// if it remains answers to shown at the right side
					while(isset($Select[$cpt2])) 	{
						echo ("
                                              <tr class='even'>
                                                <td colspan='2'>
                                                  <table>
                                                  <tr>
                                                    <td width='60%' colspan='2'>&nbsp;</td>
                                                    <td width='40%' align='right' valign='top'>".
                                                      "<b>".$Select[$cpt2]['Lettre'].".</b> ".$Select[$cpt2]['Reponse']."</td>
                                                  </tr>
                                                  </table>
                                                </td>
                                              </tr>");
						$cpt2++;
					}	// end while()
				}  // end if()
			}
                               // echo (" </table>");
		}
		elseif($answerType == TRUE_FALSE) {
			echo ("
  <tr class='even'>
    <td width='1' align='center'>
      <input type='radio' name='choice[${questionId}]' value='${answerId}' />
    </td>
    <td>$answer</td>
  </tr>");
		}
	}	// end for()

	if(!$nbrAnswers) {
		echo ("
  <tr>
    <td colspan='2'><p class='caution'>$langNoAnswer</td>
  </tr>");
	}
	// destruction of the Answer object
	unset($objAnswerTmp);
	// destruction of the Question object
	unset($objQuestionTmp);
	return $nbrAnswers;
}
?>
