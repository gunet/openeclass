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
// exercise/exercise_submit.php, but it is modified for the
// displaying needs of the learning path tool. The core
// application logic remains the same.
// It also contains a replicate from exercise/exercise.lib.php
// Ta objects prepei na ginoun include prin thn init
// gia logous pou sxetizontai me to object loading
// apo to session
require_once '../../exercise/exercise.class.php';
require_once '../../exercise/question.class.php';
require_once '../../exercise/answer.class.php';

$require_current_course = true;
require_once '../../../include/init.php';

// Genikws o kwdikas apo edw kai katw kanei akribws o,ti kai to
// exercise_submit.php. Oi mones diafores einai xrhsh twn echo
// anti gia to tool_content kai kapoies mikrodiafores opou xreiazetai
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$pageName = $langExercice;
$picturePath = "courses/$course_code/image";

if (isset($_GET['course'])) {
    $course = intval($_GET['course']);
}

if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = intval($_REQUEST['exerciseId']);
}

// if the user has clicked on the "Cancel" button
if (isset($_POST['buttonCancel'])) {
    // returns to the exercise list
    header('Location: backFromExercise.php?course=' . $course_code . '&op=cancel');
    exit();
}

if (!isset($_SESSION['exercise_begin_time'][$exerciseId])) {
    $_SESSION['exercise_begin_time'][$exerciseId] = time();
}

// if the user has submitted the form
if (isset($_POST['formSent'])) {
    $exerciseId = isset($_POST['exerciseId']) ? intval($_POST['exerciseId']) : '';
    $exerciseType = isset($_POST['exerciseType']) ? $_POST['exerciseType'] : '';
    $questionNum = isset($_POST['questionNum']) ? $_POST['questionNum'] : '';
    $nbrQuestions = isset($_POST['nbrQuestions']) ? $_POST['nbrQuestions'] : '';
    $exerciseTimeConstraint = isset($_POST['exerciseTimeConstraint']) ? $_POST['exerciseTimeConstraint'] : '';
    $eid_temp = isset($_POST['eid_temp']) ? $_POST['eid_temp'] : '';
    $recordStartDate = isset($_POST['record_start_date']) ? $_POST['record_start_date'] : '';
    $choice = isset($_POST['choice']) ? $_POST['choice'] : '';
    if (isset($_SESSION['exerciseResult'][$exerciseId])) {
        $exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
    } else {
        $exerciseResult = array();
    }

    if (isset($exerciseTimeConstraint) and $exerciseTimeConstraint != 0) {
        $exerciseTimeConstraint = $exerciseTimeConstraint * 60;
        $exerciseTimeConstraintSecs = time() - $exerciseTimeConstraint;
        $_SESSION['exercise_end_time'][$exerciseId] = $exerciseTimeConstraintSecs;

        if ($_SESSION['exercise_end_time'][$exerciseId] - $_SESSION['exercise_begin_time'][$exerciseId] > $exerciseTimeConstraint) {
            unset($_SESSION['exercise_begin_time']);
            unset($_SESSION['exercise_end_time']);
            header('Location: ../../exercise/exercise_redirect.php?course=' . $course_code . '&exerciseId=' . $exerciseId);
            exit();
        }
    }
    $recordEndDate = date("Y-m-d H:i:s", time());

    // if the user has answered at least one question
    if (is_array($choice)) {
        if ($exerciseType == 1) {
            // $exerciseResult receives the content of the form.
            // Each choice of the student is stored into the array $choice
            $exerciseResult = $choice;
        } else {
            // gets the question ID from $choice. It is the key of the array
            list($key) = array_keys($choice);
            // if the user didn't already answer this question
            if (!isset($exerciseResult[$key])) {
                // stores the user answer into the array
                $exerciseResult[$key] = $choice[$key];
            }
        }
    }

    // the script "exercise_result.php" will take the variable $exerciseResult from the session
    $_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
    // if it is the last question (only for a sequential exercise)
    if ($exerciseType == 1 || $questionNum >= $nbrQuestions) {
        // goes to the script that will show the result of the exercise
        header('Location: showExerciseResult.php?course=' . $course_code . '&exerciseId=' . $exerciseId);
        exit();
    }
} // end of submit

if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
    // construction of Exercise
    $objExercise = new Exercise();
    // if the specified exercise doesn't exist or is disabled
    if (!$objExercise->read($exerciseId) && (!$is_editor)) {
        $tool_content .= $langExerciseNotFound;
        draw($tool_content, 2);
        exit();
    }
    // saves the object into the session
    $_SESSION['objExercise'][$exerciseId] = $objExercise;
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions = $objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();
$recordStartDate = date("Y-m-d H:i:s", time());

$temp_CurrentDate = new DateTime();
$temp_StartDate = new DateTime($objExercise->selectStartDate());
$temp_EndDate = $objExercise->selectEndDate();
$temp_EndDate = isset($temp_EndDate) ? new DateTime($objExercise->selectEndDate()) : $temp_EndDate;

if (!$is_editor) {
    $error = FALSE;
    // check if exercise has expired or is active
    $currentAttempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record
                                                  WHERE eid = ?d AND uid = ?d", $eid_temp, $uid)->count;
    ++$currentAttempt;
    if ($exerciseAllowedAttempts > 0 and $currentAttempt > $exerciseAllowedAttempts) {
        $message = $langExerciseMaxAttemptsReached;
        $error = TRUE;
    }
    if (($temp_CurrentDate < $temp_StartDate) || isset($temp_EndDate) && ($temp_CurrentDate >= $temp_EndDate)) {
        $message = $langExerciseExpired;
        $error = TRUE;
    }
    if ($error == TRUE) {
        echo "<br/><td class='alert alert-warning'>$message</td>";
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
if (!isset($questionNum) || $_POST['questionNum']) {
    // only used for sequential exercises (see $exerciseType)
    if (!isset($questionNum)) {
        $questionNum = 1;
    } else {
        $questionNum++;
    }
}

if (@$_POST['questionNum']) {
    $QUERY_STRING = "questionNum=$questionNum";
}

$exerciseDescription_temp = standard_text_escape($exerciseDescription);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"   "http://www.w3.org/TR/html4/frameset.dtd">'
 . "\n<html>\n"
 . '<head>' . "\n"
 . '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . "\n"
 . "<link href='{$urlAppend}template/$theme/CSS/lp.css' rel='stylesheet'>\n"
 . "<link href='{$urlAppend}template/$theme/CSS/bootstrap-custom.css' rel='stylesheet'>\n"
 . '<title>' . $langExercice . '</title>' . "\n"
 . $head_content
 . '</head>' . "\n"
 . '<body style="margin: 0px; padding-left: 0px; height: 100% !important; height: auto; background-color: #ffffff;">' . "\n"
 . '<div id="content" style="padding:20px;">';

echo ("
  
<div class='panel panel-primary'>
    <div class='panel-heading'>
        <h3 class='panel-title'>" . q($exerciseTitle) . "</h3>
    </div>");
if (!empty($exerciseDescription_temp)) {
    echo ("<div class='panel-body'>
        $exerciseDescription_temp
    </div>");
}
    echo ("</div>
    

  <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />
  <input type='hidden' name='exerciseType' value='$exerciseType' />
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstraint' value='$exerciseTimeConstraint' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='record_start_date' value='$recordStartDate' />");

$i = 0;
foreach ($questionList as $questionId) {
    $i++;
    // for sequential exercises
    if ($exerciseType == 2) {
        // if it is not the right question, goes to the next loop iteration
        if ($questionNum != $i) {
            continue;
        } else {
            // if the user has already answered this question
            if (isset($exerciseResult[$questionId])) {
                // construction of the Question object
                $objQuestionTmp = new Question();
                // reads question informations
                $objQuestionTmp->read($questionId);
                $questionName = $objQuestionTmp->selectTitle();
                // destruction of the Question object
                unset($objQuestionTmp);
                echo '<div class\"alert1\" ' . $langAlreadyAnswered . ' &quot;' . q($questionName) . '&quot;</div>';
                break;
            }
        }
    }
    
    // shows the question and its answers
    echo ("<div class='panel panel-success'>
            <div class='panel-heading'>" . $langQuestion . ": " . $i . "</div>");

    if ($exerciseType == 2) {
        echo ("/" . $nbrQuestions);
    }
    echo ("<div class='panel-body'>");
    showQuestion($questionId);
    echo "</div></div>";
    // for sequential exercises
    if ($exerciseType == 2) {
        // quits the loop
        break;
    }
} // end foreach()

if (!$questionList) {
    echo ("<div class='alert alert-alert'>$langNoQuestion</div>");
} else {
    echo "<div class='panel'><div class='panel-body'><input class='btn btn-primary' type='submit' value=\"";
    if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
        echo "$langCont\" />&nbsp;";
    } else {
        echo $langNext . " &gt;" . "\" />";
    }
    echo "<input class='btn btn-primary' type='submit' name='buttonCancel' value='$langCancel' /></div></div>";
}
echo "</form>";
echo "</div></body>" . "\n";
echo "</html>" . "\n";

// auth edw h function einai kata bash idia me thn antistoixh sto
// exercise.lib.php, mono pou anti gia xrhsh tou tool_content kanei
// echo ton html kwdika epeidh 8eloume na ton deixnoume mesa sto
// iframe tou learningPath.
// ta global vars pou orizontai, den exoun na kanoun se kati me to
// register_globals, apla einai scoping twn metablhtwn pou yparxoun
// pio panw se auto edw to php arxeio.
function showQuestion($questionId, $onlyAnswers = false) {

    global $picturePath, $urlServer;
    global $langNoAnswer, $langColumnA, $langColumnB, $langMakeCorrespond;

    // construction of the Question object
    $objQuestionTmp = new Question();
    // reads question informations
    if (!$objQuestionTmp->read($questionId)) {
        // question not found
        return false;
    }
    $answerType = $objQuestionTmp->selectType();

    if (!$onlyAnswers) {
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        $questionDescription_temp = standard_text_escape($questionDescription);
        echo "<b>" . q($questionName) . "</b><br />
                    $questionDescription_temp";
        if (file_exists($picturePath . '/quiz-' . $questionId)) {
            echo "<img src='$urlServer/$picturePath/quiz-$questionId' />";
        }
    }  // end if(!$onlyAnswers)
    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    // only used for the answer type "Matching"
    if ($answerType == MATCHING) {
        $cpt1 = 'A';
        $cpt2 = 1;
        $select = array();
        echo "<table class='table-default'>
              <tr class='even'>
                    <th width='200'>$langColumnA</th>
                    <th width='130'>$langMakeCorrespond</th>
                    <th width='200'>$langColumnB</th>
                  </tr>
                  ";
    }

    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answer = mathfilter($answer, 12, '../../courses/mathimg/');
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            // splits text and weightings that are joined with the character '::'
            list($answer) = explode('::', $answer);
            // replaces [blank] by an input field
            $answer = preg_replace('/\[[^]]+\]/', '<input type="text" name="choice[' . $questionId . '][]" size="10" />', standard_text_escape($answer));
        }
        // unique answer
        if ($answerType == UNIQUE_ANSWER) {
            echo "
                      <div class='radio'>
                          <label>
                            <input type='radio' name='choice[${questionId}]' value='${answerId}'>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
                          
        }
        // multiple answers
        elseif ($answerType == MULTIPLE_ANSWER) {
            echo ("
                      <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='choice[${questionId}][${answerId}]' value='1'>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>");
        }
        // fill in blanks
        elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            echo ($answer);
        }
        // matching
        elseif ($answerType == MATCHING) {
            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown at the right side
                $select[$answerId]['Reponse'] = standard_text_escape($answer);
            } else {
                echo "<tr class='even'>
                                    <td width='200'><b>${cpt2}.</b> " . standard_text_escape($answer) . "</td>
                                    <td width='130'><div align='center'>
                                     <select name='choice[${questionId}][${answerId}]'>
                                       <option value='0'>--</option>";

                // fills the list-box
                foreach ($select as $key => $val) {
                    echo "<option value=\"${key}\">${val['Lettre']}</option>";
                }
                echo "</select></td>
                                    <td width='200'>";
                if (isset($select[$cpt2]))
                    echo '<b>' . $select[$cpt2]['Lettre'] . '.</b> ' . $select[$cpt2]['Reponse'];
                else
                    echo '&nbsp;';

                echo "</td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown at the right side
                    while (isset($select[$cpt2])) {
                        echo "<tr class='even'>
                                                <td colspan='2'>
                                                  <table>
                                                  <tr>
                                                    <td width='60%' colspan='2'>&nbsp;</td>
                                                    <td width='40%' align='right' valign='top'>" .
                        "<b>" . $select[$cpt2]['Lettre'] . ".</b> " . $select[$cpt2]['Reponse'] . "</td>
                                                  </tr>
                                                  </table>
                                                </td>
                                              </tr>";
                        $cpt2++;
                    } // end while()
                }  // end if()
            }
            
        } elseif ($answerType == TRUE_FALSE) {
            echo "<div class='radio'>
                          <label>
                            <input type='radio' name='choice[${questionId}]' value='${answerId}'>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
    } // end for()
    if ($answerType == MATCHING) {
        echo "</table>";
    }

    if (!$nbrAnswers) {
        echo "<tr><td colspan='2'><div class='alert alert-danger'>$langNoAnswer</div></td></tr>";
    }
    // destruction of the Answer object
    unset($objAnswerTmp);
    // destruction of the Question object
    unset($objQuestionTmp);
    return $nbrAnswers;
}
