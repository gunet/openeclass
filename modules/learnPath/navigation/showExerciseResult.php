<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/exercise/game.php';
ModalBoxHelper::loadModalBox();

$theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');
$theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
$cssFile = '';
if($theme_id > 0){
    $cssFile = "<link rel='stylesheet' type='text/css' href='{$urlAppend}courses/theme_data/$theme_id/style_str.css?".time()."'/>";
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
 . "\n<html>\n"
 . '<head>' . "\n"
 . '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . "\n"
 . "<script type='text/javascript' src='{$urlAppend}js/jquery-3.6.0.min.js'></script>
    <link href='{$urlAppend}template/modern/css/bootstrap.min.css' rel='stylesheet'>
    <link href='{$urlAppend}template/modern/css/font-Manrope/css/Manrope.css?" . time() . "' rel='stylesheet'>
    <link href='{$urlAppend}template/modern/css/lp.css?" . time() . "' rel='stylesheet'>\n"
 . "<link href='{$urlAppend}template/modern/css/default.css?" . time() . "' rel='stylesheet'>\n"
 . "$cssFile\n"
 . "<link href='{$urlAppend}template/modern/css/font-awesome-6.4.0/css/all.css' rel='stylesheet'>\n"
 . '<title>' . $langExercicesResult . '</title>' . "\n"
 . $head_content
 . '</head>' . "\n"
 . '<body class="body-learningPath" style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto;">' . "\n"
 . '<div id="content"  style="padding:20px;">';

$pageName = $langExercicesResult;
global $qtype;

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}

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

echo "<div class='card panelCard px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h3 class='mb-0'>" . q(stripslashes($exerciseTitle)) . "</h3>
        </div>";
if ($exerciseDescription) {
   echo "<div class='card-body'>
        " . standard_text_escape($exerciseDescription) . "
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
    echo "<br>
        <table class='table-default graded'>
        <tr class='odd list-header'>
            <td colspan='$colspan'><b><u>$langQuestion</u>: $iplus</b></td>
        </tr>
        <tr>
            <td colspan='$colspan'>
                <strong>" . q_math($questionName) . "</strong>
                <br>" . standard_text_escape($questionDescription) . "<br/><br/>
            </td>
        </tr>";

    $questionScore = 0;

    if ($displayResults == 1) {
        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            echo "<tr>
                        <td valign='top'><b>$langChoice</b></td>
                        <td valign='top'><b>$langExpectedChoice</b></td>
                        <td valign='top'><b>$langAnswer</b></td>
                        <td valign='top'><b>$langComment</b></td>
                        </tr>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
            echo "<tr>
                    <td><strong>$langAnswer</strong></td>
                </tr>";
        } else {
            echo "<tr>
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
        if (in_array($answerType, [UNIQUE_ANSWER, MULTIPLE_ANSWER, MATCHING, TRUE_FALSE])) {
            $answer = standard_text_escape($answer);
        }
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
                $temp = q_math($answer);
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
                        $questionScore += $answerWeighting[$j];
                        // increments total score
                        $totalScore += $answerWeighting[$j];
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
            case FILL_IN_FROM_PREDEFINED_ANSWERS :
                $answer_array = unserialize($answer);
                $answer = $answer_array[0]; // answer text
                // fetch possible answers for all choices
                preg_match_all('/\[[^]]+\]/', $answer, $out);
                $possible_answers = [];
                foreach ($out[0] as $output) {
                    $possible_answers[] = explode("|", str_replace(array('[',']'), ' ', q($output)));
                }
                $answer_string = $answer_array[1]; // answers
                $answerWeighting = $answer_array[2]; // answer weight
                $temp = $answer;
                $answer = '';
                $j = 1;
                // the loop will stop at the end of the text
                while (true) {
                    $answer_string = reindex_array_keys_from_one($answer_string); // start from 1
                    // quits the loop if there are no more blanks
                    if (($pos = strpos($temp, '[')) === false) {
                        // adds the end of the text
                        $answer .= q($temp);
                    }
                    // adds the piece of text that is before the blank and ended by [
                    $answer .= substr($temp, 0, $pos + 1);
                    $temp = substr($temp, $pos + 1);
                    // quits the loop if there are no more blanks
                    if (($pos = strpos($temp, ']')) === false) {
                        // adds the end of the text
                        $answer .= q($temp);
                        break;
                    }

                    $possible_answer = $possible_answers[$j-1]; // possible answers for each choice
                    $possible_answer = reindex_array_keys_from_one($possible_answer); // start from 1
                    if ($choice[$j] == $answer_string[$j]) { // correct answer
                        $questionScore += $answerWeighting[$j-1]; // weight assignment
                        $totalScore += $answerWeighting[$j-1]; // weight assignment
                        // adds the word in green at the end of the string
                        $answer .= '<strong>' . q($possible_answer[$choice[$j]]) . '</strong>';
                        $icon = "<span class='fa fa-check text-success'></span>";
                    }  else { // wrong answer
                        if (isset($possible_answer[$choice[$j]])) { // if we have chosen something
                            // adds the word in red at the end of the string, and strikes it
                            $answer_choice = '<span class="text-danger"><s>' . q($possible_answer[$choice[$j]]) . '</s></span>';
                        } else {
                            $answer_choice =  "&nbsp;&mdash;";
                        }
                        $answer .= $answer_choice;
                        $icon = "<span class='fa-solid fa-xmark text-danger'></span>";
                    }
                    // adds the correct word, followed by ] to close the blank
                    $answer .= ' / <span class="text-success"><strong>' . q($possible_answer[$answer_string[$j]]) . '</strong></span>';
                    $answer .= "]";
                    $answer .= "&nbsp;&nbsp;$icon";
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
                        $choice[$answerId] = '<span class="text-danger"><del>' . $matching[$choice[$answerId]] . '</del></span>';
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
                    echo "<tr><td><div>";
                    if ($studentChoice) {
                        $icon_choice= "fa-square-check";
                    } else {
                        $icon_choice = "fa-square";
                    }
                    echo icon($icon_choice);
                    echo "</div></div></td><td><div>";
                    if ($answerCorrect) {
                        $icon_choice= "fa-square-check";
                    } else {
                        $icon_choice = "fa-square";
                    }
                    echo icon($icon_choice) . "</div>";
                    echo "</td>
                            <td>" . standard_text_escape($answer) . "</td>
                            <td>";
                    if ($studentChoice) {
                        echo standard_text_escape($answerComment);
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td></tr>";
                } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                    echo "<tr>
                          <td>" . standard_text_escape($answer) . "</td>
                        </tr>";
                } else {
                    echo "<tr>
                          <td>" . standard_text_escape($answer) . "</td>
                          <td>{$choice[$answerId]} / <span class='text-success'><strong>{$matching[$answerCorrect]}</strong></span></td>
                        </tr>";
                }
            }
        } // end of if
    } // end for()
    if ($displayScore == 1) {
        echo "<tr>
              <th class='p-2' colspan='$colspan'><div>
                        $langQuestionScore: <strong>" . round($questionScore, 2) . " / $questionWeighting</strong></div>
              </th>
            </tr>";
    }
    echo "</table>";
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
Database::get()->query("UPDATE exercise_user_record SET total_score = ?f, total_weighting = ?f, attempt = ?d WHERE eurid = ?d", $totalScore, $totalWeighting, $attempt, $eurid);

if ($displayScore == 1) {
    echo "<br>
        <table class='table-default'>
            <tr>
                <td>$langTotalScore: <strong>" . round($totalScore, 2) . "/$totalWeighting</strong></td>
            </tr>
        </table>";
}
echo "<br>
  <div class='text-center'><input class='btn submitAdminBtn' type='submit' value='$langNext'></div>
  <br>
  </form><br>";

// apo edw kai katw einai LP specific
// record progression
// update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous
if ($uid) {
    // exercises can have a negative score, we don't accept that in LP
    // so if totalScore is negative use 0 as result
    $totalScore = max($totalScore, 0);
    if ($totalWeighting != 0) {
        $newRaw = round($totalScore / $totalWeighting * 100);
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
               AND UMP.`user_id` = ?d
               AND UMP.`attempt` = ?d";
    $row = Database::get()->querySingle($sql, $_SESSION['path_id'], $_SESSION['lp_module_id'], $uid, $_SESSION['lp_attempt']);

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
             AND `user_id` = ?d
             AND `attempt` = ?d";
    Database::get()->query($sqlupd, $scoreMin, $scoreMax, addScormTime($row->total_time, $scormSessionTime), $scormSessionTime, $row->learnPath_module_id, $uid, $_SESSION['lp_attempt']);
    triggerLPGame($course_id, $uid, $_SESSION['path_id'], LearningPathEvent::UPDPROGRESS);
    triggerLPAnalytics($course_id, $uid, $_SESSION['path_id']);
}

echo "</div></body></html>" . "\n";
