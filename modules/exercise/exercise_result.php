<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';
$TBL_ANSWER = 'exercise_answer';
$TBL_RECORDS = 'exercise_user_record';

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$require_current_course = TRUE;
$guest_allowed = true;
include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$nameTools = $langExercicesResult;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

// picture path
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the above variables are empty or incorrect, stops the script
if (!is_array($_SESSION['exerciseResult'][$exerciseId]) || !is_array($_SESSION['questionList'][$exerciseId]) || !is_object($objExercise)) {
    $tool_content .= $langExerciseNotFound;
    draw($tool_content, 2);
    exit();
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
$exerciseDescription_temp = mathfilter($exerciseDescription_temp, 12, "../../courses/mathimg/");
$displayResults = $objExercise->selectResults();
$displayScore = $objExercise->selectScore();

$tool_content .= "
  <table class='tbl_border' width='99%'>
  <tr class='odd'>
    <td colspan='2'><b>" . q(stripslashes($exerciseTitle)) . "</b>
    <br/>" . standard_text_escape(stripslashes($exerciseDescription_temp)) . "
    </td>
  </tr>
  </table>";

$tool_content .= "<form method='GET' action='index.php'><input type='hidden' name='course' value='$course_code'/>";

$i = $totalScore = $totalWeighting = 0;

// for each question

foreach ($_SESSION['questionList'][$exerciseId] as $questionId) {
    // gets the student choice for this question
    $choice = @$_SESSION['exerciseResult'][$exerciseId][$questionId];

    // creates a temporary Question object
    $objQuestionTmp = new Question();
    $objQuestionTmp->read($questionId);

    $questionName = $objQuestionTmp->selectTitle();
    $questionDescription = $objQuestionTmp->selectDescription();
    $questionDescription_temp = nl2br(make_clickable($questionDescription));
    $questionDescription_temp = mathfilter($questionDescription_temp, 12, "../../courses/mathimg/");
    $questionWeighting = $objQuestionTmp->selectWeighting();
    $answerType = $objQuestionTmp->selectType();

    // destruction of the Question object
    unset($objQuestionTmp);

    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
        $colspan = 4;
    } elseif ($answerType == MATCHING) {
        $colspan = 2;
    } else {
        $colspan = 1;
    }
    $iplus = $i + 1;
    $tool_content .= "
	<br/>
	<table width='100%' class='tbl_alt'>
	<tr class='odd'>
	  <td colspan='${colspan}'><b><u>$langQuestion</u>: $iplus</b></td>
	</tr>
	<tr>
	  <td class='even' colspan='${colspan}'>
	    <b>" . q($questionName) . "</b>
	    <br />" .
            standard_text_escape($questionDescription_temp)
            . "<br/><br/>
	  </td>
	</tr>";
    if (file_exists($picturePath . '/quiz-' . $questionId)) {
        $tool_content .= "here
                  <tr class='even'>
                    <td class='center' colspan='${colspan}'><img src='../../" . ${'picturePath'} . "/quiz-" . ${'questionId'} . "'></td>
                  </tr>";
    }
    $questionScore = 0;

    if ($displayResults == 1) {
        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            $tool_content .= "
			<tr class='even'>
			  <td width='50' valign='top'><b>$langChoice</b></td>
			  <td width='50' class='center' valign='top'><b>$langExpectedChoice</b></td>
			  <td valign='top'><b>$langAnswer</b></td>
			  <td valign='top'><b>$langComment</b></td>
			</tr>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FREE_TEXT) {
            $tool_content .= "
			<tr>
			  <td class='even'><b>$langAnswer</b></td>
			</tr>";       
        } else {
            $tool_content .= "
			<tr class='even'>
			  <td><b>$langElementList</b></td>
			  <td><b>$langCorrespondsTo</b></td>
			</tr>";
        }
    }
    if ($answerType != FREE_TEXT) { // if NOT FREE TEXT (i.e. question has answers) 
        // construction of the Answer object
        $objAnswerTmp = new Answer($questionId);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
            $answer = $objAnswerTmp->selectAnswer($answerId);
            $answerComment = $objAnswerTmp->selectComment($answerId);
            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
            $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
            // support for math symbols
            $answer = mathfilter($answer, 12, "../../courses/mathimg/");
            $answerComment = mathfilter($answerComment, 12, "../../courses/mathimg/");

            switch ($answerType) {
                // for unique answer
                case UNIQUE_ANSWER : $studentChoice = ($choice == $answerId) ? 1 : 0;
                    if ($studentChoice) {
                        $questionScore+=$answerWeighting;
                        $totalScore+=$answerWeighting;
                    }
                    break;
                // for multiple answers
                case MULTIPLE_ANSWER : $studentChoice = @$choice[$answerId];
                    if ($studentChoice) {
                        $questionScore+=$answerWeighting;
                        $totalScore+=$answerWeighting;
                    }
                    break;
                // for fill in the blanks
                case FILL_IN_BLANKS : // splits text and weightings that are joined with the char '::'
                    list($answer, $answerWeighting) = explode('::', $answer);
                    // splits weightings that are joined with a comma
                    $answerWeighting = explode(',', $answerWeighting);
                    // we save the answer because it will be modified
                    $temp = $answer;
                    $answer = '';
                    $j = 0;
                    // the loop will stop at the end of the text
                    while (1) {
                        // quits the loop if there are no more blanks
                        if (($pos = strpos($temp, '[')) === false) {
                            // adds the end of the text
                            $answer.=$temp;
                            break;
                        }
                        // adds the piece of text that is before the blank and ended by [
                        $answer.=substr($temp, 0, $pos + 1);
                        $temp = substr($temp, $pos + 1);
                        // quits the loop if there are no more blanks
                        if (($pos = strpos($temp, ']')) === false) {
                            // adds the end of the text
                            $answer.=$temp;
                            break;
                        }
                        $choice[$j] = trim(stripslashes($choice[$j]));
                        // if the word entered is the same as the one defined by the professor
                        if (strtolower(substr($temp, 0, $pos)) == strtolower($choice[$j])) {
                            // gives the related weighting to the student
                            $questionScore+=$answerWeighting[$j];
                            // increments total score
                            $totalScore+=$answerWeighting[$j];
                            // adds the word in green at the end of the string
                            $answer.=$choice[$j];
                        }
                        // else if the word entered is not the same as the one defined by the professor
                        elseif (!empty($choice[$j])) {
                            // adds the word in red at the end of the string, and strikes it
                            $answer.='<font color="red"><s>' . $choice[$j] . '</s></font>';
                        } else {
                            // adds a tabulation if no word has been typed by the student
                            $answer.='&nbsp;&nbsp;&nbsp;';
                        }
                        // adds the correct word, followed by ] to close the blank
                        $answer.=' / <font color="green"><b>' . substr($temp, 0, $pos) . '</b></font>]';
                        $j++;
                        $temp = substr($temp, $pos + 1);
                    }
                    break;
                // for matching
                case MATCHING : if ($answerCorrect) {
                        if ($answerCorrect == $choice[$answerId]) {
                            $questionScore += $answerWeighting;
                            $totalScore += $answerWeighting;
                            $choice[$answerId] = $matching[$choice[$answerId]];
                        } elseif (!$choice[$answerId]) {
                            $choice[$answerId] = '&nbsp;&nbsp;&nbsp;';
                        } else {
                            $choice[$answerId] = '<font color="red">
                                                            <s>' . $matching[$choice[$answerId]] . '</s>
                                                            </font>';
                        }
                    } else {
                        $matching[$answerId] = $answer;
                    }
                    break;
                case TRUE_FALSE : $studentChoice = ($choice == $answerId) ? 1 : 0;
                    if ($studentChoice) {
                        $questionScore += $answerWeighting;
                        $totalScore += $answerWeighting;
                    }
                    break;
            } // end switch()
            if ($displayResults == 1) {
                if ($answerType != MATCHING || $answerCorrect) {
                    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                        $tool_content .= "
                                            <tr class='even'>
                                              <td>
                                              <div align='center'><img src='$themeimg/";
                        if ($answerType == UNIQUE_ANSWER || $answerType == TRUE_FALSE) {
                            $tool_content .= "radio";
                        } else {
                            $tool_content .= "checkbox";
                        }
                        if ($studentChoice) {
                            $tool_content .= "_on";
                        } else {
                            $tool_content .= "_off";
                        }

                        $tool_content .= ".png' /></div>
                                            </td>
                                            <td><div align='center'>";

                        if ($answerType == UNIQUE_ANSWER || $answerType == TRUE_FALSE) {
                            $tool_content .= "<img src='$themeimg/radio";
                        } else {
                            $tool_content .= "<img src='$themeimg/checkbox";
                        }
                        if ($answerCorrect) {
                            $tool_content .= "_on";
                        } else {
                            $tool_content .= "_off";
                        }
                        $tool_content .= ".png' /></div>";
                        $tool_content .= "
                                            </td>
                                            <td>" . standard_text_escape($answer) . "</td>
                                            <td>";
                        if ($studentChoice) {
                            $tool_content .= standard_text_escape(nl2br(make_clickable($answerComment)));
                        } else {
                            $tool_content .= '&nbsp;';
                        }
                        $tool_content .= "</td></tr>";
                    } elseif ($answerType == FILL_IN_BLANKS) {
                        $tool_content .= "
                                            <tr class='even'>
                                              <td>" . standard_text_escape(nl2br($answer)) . "</td>
                                            </tr>";          
                    } else {
                        $tool_content .= "
                                            <tr class='even'>
                                              <td>" . standard_text_escape($answer) . "</td>
                                              <td>${choice[$answerId]} / <font color='green'><b>${matching[$answerCorrect]}</b></font></td>
                                            </tr>";
                    }
                }
            } // end of if
        } // end for()
    } else { // If FREE TEXT type
        $tool_content .= "<tr class='even'>
                             <td>" . purify($choice) . "</td>
                          </tr>";
    }
    if ($displayScore == 1) {
        if (intval($questionScore) == $questionScore) {
            $questionScore = intval($questionScore);
        }
        if (intval($questionWeighting) == $questionWeighting) {
            $questionWeighting = intval($questionWeighting);
        }
        $tool_content .= "
		<tr class='even'>
		  <th colspan='$colspan' class='odd'>";
        if ($answerType == FREE_TEXT) {
            $tool_content .= "<span style='color:red;'>Η απάντηση δεν έχει ακόμα βαθμολογηθεί</span>";
        }
        $tool_content .= "<span style='float:right;'>
			    $langQuestionScore: <b>$questionScore/$questionWeighting</b></span>
		  </th>
		</tr>";
    }
    $tool_content .= "</table>";
    // destruction of Answer
    unset($objAnswerTmp);
    $i++;
    $totalWeighting += $questionWeighting;
} // end foreach()
// update db with results
$eid = $objExercise->selectId();
$sql = "SELECT MAX(eurid) FROM `$TBL_RECORDS` WHERE eid = '$eid' AND uid = '$uid'";
$result = db_query($sql);
$row = mysql_fetch_row($result);
$eurid = $row[0];

// record results of exercise
$sql = "UPDATE `$TBL_RECORDS` SET record_end_date = '".date('Y-m-d H:i:s', time())."', total_score = '$totalScore', 
			total_weighting = '$totalWeighting'
		WHERE eurid = '$eurid'";
db_query($sql);

if ($displayScore == 1) {
    $tool_content .= "
    <br/>
    <table width='100%' class='tbl_alt'>
    <tr class='odd'>
	<td class='right'><b>$langYourTotalScore: $totalScore/$totalWeighting</b>
      </td>
    </tr>
    </table>";
}
$tool_content .= "
  <br/>
  <div align='center'><input type='submit' value='$langFinish' /></div>
  <br />
  </form><br />";

draw($tool_content, 2, null, $head_content);
