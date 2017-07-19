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


// This script is a replicate from
// exercise/exercise_result.php, but it is modified for the
// displaying needs of the learning path tool. The core
// application logic remains the same.
// Ta objects prepei na ginoun include prin thn init
// gia logous pou sxetizontai me to object loading
// apo to session
require_once '../../exercise/exercise.class.php';
require_once '../../exercise/question.class.php';
require_once '../../exercise/answer.class.php';

$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

// Ksekiname to diko mas html output giati probaloume mesa se iframe
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
 . "\n<html>\n"
 . '<head>' . "\n"
 . '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . "\n"
 . "<link href='{$urlAppend}template/$theme/CSS/lp.css' rel='stylesheet'>\n"
 . "<link href='{$urlAppend}template/$theme/CSS/bootstrap-custom.css' rel='stylesheet'>\n"
 . '<title>' . $langExercicesResult . '</title>' . "\n"
 . $head_content
 . '</head>' . "\n"
 . '<body style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto; background-color: #ffffff;">' . "\n"
 . '<div id="content"  style="padding:20px;">';

$pageName = $langExercicesResult;
global $qtype;

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
if (!is_array($_SESSION['exerciseResult'][$exerciseId]) || !is_array($_SESSION['questionList'][$exerciseId]) || !is_object($objExercise)) {
    echo $langExerciseNotFound;
    exit();
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$displayResults = $objExercise->selectResults();
$displayScore = $objExercise->selectScore();

echo "<div class='panel panel-primary'>
        <div class='panel-heading'>
            <b>" . q(stripslashes($exerciseTitle)) . "</b>
        </div>";
if ($exerciseDescription) {
   echo "<div class='panel-body'>
        " . standard_text_escape($exerciseDescription, '../../../courses/mathimg/') . "
        </div>";
  }
echo "</div>";


// probaloume th dikia mas forma me to diko mas action
// kai me to katallhlo hidden pedio
echo "<form method='GET' action='backFromExercise.php'><input type='hidden' name='course' value='$course_code'>" .
 "<input type='hidden' name='op' value='finish'>";

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
    echo "<br/>
        <table class='table-default graded'>
        <tr class='odd list-header'>
        <td colspan='${colspan}'><b><u>$langQuestion</u>: $iplus</b></td>
        </tr>
        <tr>
        <td class='even' colspan='${colspan}'>
        <b>" . q($questionName) . "</b>
        <br />" .
        standard_text_escape($questionDescription, '../../../courses/mathimg/')
        . "<br/><br/>
        </td>
        </tr>";

    $questionScore = 0;

    if ($displayResults == 1) {
        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            echo "<tr class='even'>
                        <td width='50' valign='top'><b>$langChoice</b></td>
                        <td width='50' class='center' valign='top'><b>$langExpectedChoice</b></td>
                        <td valign='top'><b>$langAnswer</b></td>
                        <td valign='top'><b>$langComment</b></td>
                        </tr>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            echo "<tr>
                        <td class='even'><b>$langAnswer</b></td>
                        </tr>";
        } else {
            echo "<tr class='even'>
                        <td><b>$langElementList</b></td>
                        <td><b>$langCorrespondsTo</b></td>
                        </tr>";
        }
    }
    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answerComment = $objAnswerTmp->selectComment($answerId);       
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
        // support for math symbols
        $answer = mathfilter($answer, 12, "$webDir/courses/mathimg/");
        //$answerComment = mathfilter($answerComment, 12, "$webDir/courses/mathimg/");

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
            case FILL_IN_BLANKS :
            case FILL_IN_BLANKS_TOLERANT :    
                // splits text and weightings that are joined with the char '::'
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
                        $questionScore+=$answerWeighting;
                        $totalScore+=$answerWeighting;
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
                    $questionScore+=$answerWeighting;
                    $totalScore+=$answerWeighting;
                }
                break;
        } // end switch()
        if ($displayResults == 1) {
            if ($answerType != MATCHING || $answerCorrect) {
                if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                    echo ("<tr class='even'><td><div align='center'><img src='$themeimg/");
                    if ($answerType == UNIQUE_ANSWER || $answerType == TRUE_FALSE) {
                        echo ("radio");
                    } else {
                        echo ("checkbox");
                    }
                    if ($studentChoice) {
                        echo ("_on");
                    } else {
                        echo ("_off");
                    }
                    echo (".png' /></div></td><td><div align='center'>");
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
                            <td>" . standard_text_escape($answer, '../../../courses/mathimg/') . "</td>
                            <td>");
                    if ($studentChoice) {
                        echo standard_text_escape($answerComment, '../../../courses/mathimg/');
                    } else {
                        echo ('&nbsp;');
                    }
                    echo ("</td></tr>");
                } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                    echo ("
                        <tr class='even'>
                          <td>" . standard_text_escape($answer, '../../../courses/mathimg/') . "</td>
                        </tr>");
                } else {
                    echo ("
                        <tr class='even'>
                          <td>" . standard_text_escape($answer, '../../../courses/mathimg/') . "</td>
                          <td>${choice[$answerId]} / <font color='green'><b>${matching[$answerCorrect]}</b></font></td>
                        </tr>");
                }
            }
        } // end of if
    } // end for()
    if ($displayScore == 1) {
        echo ("
                <tr class='even'>
                  <th colspan='$colspan' class='odd'><div align='right'>
                            $langQuestionScore: <b>" . round($questionScore, 2) . " / $questionWeighting</b></div>
                  </th>
                </tr>");
    }
    echo ("</table>");
    // destruction of Answer
    unset($objAnswerTmp);
    $i++;
    $totalWeighting += $questionWeighting;
} // end foreach()
// update db with results
$eid = $objExercise->selectId();

$attempt = Database::get()->querySingle("SELECT COUNT(record_start_date) AS count FROM `exercise_user_record` WHERE eid = ?d AND uid = ?d", $eid, $uid)->count;
$eurid = Database::get()->querySingle("SELECT MAX(eurid) AS max FROM `exercise_user_record` WHERE eid = ?d AND uid = ?d", $eid, $uid)->max;

// record results of exercise
Database::get()->query("UPDATE exercise_user_record SET total_score = ?d, total_weighting = ?d, attempt = ?d WHERE eurid = ?d", $totalScore, $totalWeighting, $attempt, $eurid);

if ($displayScore == 1) {
    echo ("
    <br/>
    <table class='table-default'>
    <tr class='odd'>
	<td class='right'>$langYourTotalScore: <b>$totalScore/$totalWeighting</b>
      </td>
    </tr>
    </table>");
}
echo ("
  <br/>
  <div align='center'><input class='btn btn-primary' type='submit' value='$langFinish' /></div>
  <br />
  </form><br />");

// apo edw kai katw einai LP specific
// record progression
// update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous
if ($uid) {
    // exercises can have a negative score, we don't accept that in LP
    // so if totalScore is negative use 0 as result
    $totalScore = max($totalScore, 0);
    if ($totalWeighting != 0) {
        $newRaw = @round($totalScore / $totalWeighting * 100);
    } else {
        $newRaw = 0;
    }

    $scoreMin = 0;
    $scoreMax = $totalWeighting;
    // need learningPath_module_id and raw_to_pass value
    $sql = "SELECT LPM.`raw_to_pass`, LPM.`learnPath_module_id`, UMP.`total_time`, UMP.`raw`
			FROM `lp_rel_learnPath_module` AS LPM, `lp_user_module_progress` AS UMP
			WHERE LPM.`learnPath_id` = ?d
			AND LPM.`module_id` = ?d
			AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
			AND UMP.`user_id` = ?d";
    $row = Database::get()->querySingle($sql, $_SESSION['path_id'], $_SESSION['lp_module_id'], $uid);

    $scormSessionTime = seconds_to_scorm_time($timeToCompleteExe);

    // build sql query
    $sqlupd = "UPDATE `lp_user_module_progress` SET ";
    // if recorded score is less then the new score => update raw, credit and status

    if ($row->raw < $totalScore) {
        // update raw
        $sqlupd .= "`raw` = $totalScore,";
        // update credit and status if needed ( score is better than raw_to_pass )
        if ($newRaw >= $row->raw_to_pass) {
            $sqlupd .= "`credit` = 'CREDIT',`lesson_status` = 'PASSED',";
        } else { // minimum raw to pass needed to get credit
            $sqlupd .= "`credit` = 'NO-CREDIT',`lesson_status` = 'FAILED',";
        }
    }// else don't change raw, credit and lesson_status
    // default query statements
    $sqlupd .= " `scoreMin` 	= ?d,
                 `scoreMax` 	= ?d,
                 `total_time`	= ?s,
                 `session_time`	= ?s
           WHERE `learnPath_module_id` = ?d
             AND `user_id` = ?d";
    Database::get()->query($sqlupd, $scoreMin, $scoreMax, addScormTime($row->total_time, $scormSessionTime), $scormSessionTime, $row->learnPath_module_id, $uid);
}

echo "</div></body></html>" . "\n";
