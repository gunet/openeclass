<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

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
$shuffleQuestions = $objExercise->selectShuffle();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();
$recordStartDate = date("Y-m-d H:i:s", time());
$questionList = $objExercise->selectQuestionList();
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
        echo "<br/><td class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$message</span></td>";
        exit();
    }
}

if (isset($_SESSION['questionList'][$exerciseId])) {
    $questionList = $_SESSION['questionList'][$exerciseId];
}

if (!isset($_SESSION['questionList'][$exerciseId])) {
    if ($shuffleQuestions or (intval($randomQuestions) > 0)) {
        $qList = $objExercise->selectShuffleQuestions();
    } else {
        $qList = $objExercise->selectQuestions();
    }
    $qList = array_unique($qList); // avoid duplicates (if any)
    $i = 1;
    foreach ($qList as $data) { // just make sure that array key / values are ok
        $questionList[$i] = $data;
        $i++;
    }
    // saves the question list into the session
    if (count($questionList) > 0) {
        $_SESSION['questionList'][$exerciseId] = $questionList;
    } else {
        unset($_SESSION['objExercise'][$exerciseId]);
    }
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

$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));

$theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');
$theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Frameset//EN' 'http://www.w3.org/TR/html4/frameset.dtd'>
 <html>
 <head>
     <meta http-equiv='Content-Type' content='text/html' charset='" . $charset . "'>
     <link href='{$urlAppend}template/modern/css/bootstrap.min.css' rel='stylesheet'>
     <link href='{$urlAppend}template/modern/css/fonts_all/typography.css?" . time() . "' rel='stylesheet'>
     
     <link href='{$urlAppend}template/modern/css/all.css' rel='stylesheet'>

     <link href='{$urlAppend}template/modern/css/font-awesome-6.4.0/css/all.css' rel='stylesheet'>
     <link rel='stylesheet' type='text/css' href='{$urlAppend}template/modern/css/default.css?".time()."'>";
     if($theme_id > 0){
        echo "<link rel='stylesheet' type='text/css' href='{$urlAppend}courses/theme_data/$theme_id/style_str.css?".time()."'/>";
     }

    echo "
     <script type='text/javascript' src='{$urlAppend}js/jquery-3.6.0.min.js'></script>
     
     <title>$langExercice</title>" . $head_content ."
 </head>
 <body class='body-learningPath' style='margin: 0px; padding-left: 0px; height: 100% !important; height: auto;'>
 <div id='content' style='padding:20px;'>";

echo "<div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h3 class='mb-0'>" . q_math($exerciseTitle) . "</h3>
    </div>";
if (!empty($exerciseDescription_temp)) {
    echo "<div class='card-body'>" . standard_text_escape($exerciseDescription_temp) . "</div>";
}

echo "</div>
  <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />
  <input type='hidden' name='exerciseType' value='$exerciseType' />
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstraint' value='$exerciseTimeConstraint' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='record_start_date' value='$recordStartDate' />";

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
                // reads question information
                $objQuestionTmp->read($questionId);
                $questionName = $objQuestionTmp->selectTitle();
                // destruction of the Question object
                unset($objQuestionTmp);
                echo '<div class\"alert1\" ' . $langAlreadyAnswered . ' &quot;' . q_math($questionName) . '&quot;</div>';
                break;
            }
        }
    }

    // shows the question and its answers
    echo "<div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3 class='mb-0'>" . $langQuestion . ": " . $i .
               ($exerciseType == 2 ?  " / $nbrQuestions" : '') .
            "</h3></div>" .
         "<div class='card-body'>";
    showQuestion($questionId);
    echo "</div></div>";
    // for sequential exercises
    if ($exerciseType == 2) {
        // quits the loop
        break;
    }
} // end foreach()

if (!$questionList) {
    echo "<div class='alert alert-alert'>$langNoQuestion</div>";
} else {
    echo "<div class='panel mt-4'><div class='panel-body p-0 bg-transparent d-flex justify-content-between align-items-center'><input class='btn submitAdminBtn' type='submit' value=\"";
    if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
        echo "$langContinue\" />&nbsp;";
    } else {
        echo $langNext . " &gt;&nbsp;" . "\" />&nbsp;";
    }
    echo "<input class='btn cancelAdminBtn' type='submit' name='buttonCancel' value='$langCancel' /></div></div>";
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

    global $picturePath, $urlServer, $langSelect, $langNoAnswer, $langColumnA, $langColumnB, $langMakeCorrespond, $langSelect;

    // construction of the Question object
    $objQuestionTmp = new Question();
    // reads question information
    if (!$objQuestionTmp->read($questionId)) {
        // question not found
        return false;
    }
    $answerType = $objQuestionTmp->selectType();

    if (!$onlyAnswers) {
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        echo "<strong>" . q_math($questionName) . "</strong><br><br>" .
            standard_text_escape($questionDescription);
        if (file_exists($picturePath . '/quiz-' . $questionId)) {
            echo "<img src='$urlServer/$picturePath/quiz-$questionId' /><br><br>";
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
                    <th width='200' style='padding: 10px 10px 10px 10px;'>$langColumnA</th>
                    <th width='130' style='padding: 10px 10px 10px 10px;'>$langMakeCorrespond</th>
                    <th width='200' style='padding: 10px 10px 10px 10px;'>$langColumnB</th>
                  </tr>
                  ";
    }

    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        if (in_array($answerType, [UNIQUE_ANSWER, MULTIPLE_ANSWER, MATCHING, TRUE_FALSE])) {
            $answer = standard_text_escape($answer);
        } elseif ($answerType == FILL_IN_BLANKS or $answerType == FILL_IN_BLANKS_TOLERANT) {
            // splits text and weightings that are joined with the character '::'
            list($answer) = explode('::', $answer);
            // replaces [blank] by an input field
            $answer = preg_replace('/\[[^]]+\]/', '<input class="form-control" type="text" name="choice[' . $questionId . '][]" size="10" />', standard_text_escape($answer));
        }

        if ($answerType == UNIQUE_ANSWER) { // unique answer
            echo "
                  <div class='radio mb-2'>
                      <label>
                        <input type='radio' name='choice[$questionId]' value='$answerId'>
                        " . $answer . "
                      </label>
                    </div>";

        } elseif ($answerType == MULTIPLE_ANSWER) { // multiple answers
            echo "
                  <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='choice[$questionId][$answerId]' value='1'>
                        <span class='checkmark'></span>
                        " . $answer . "
                      </label>
                    </div>";
        }
        // fill in blanks
        elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            echo "<div class='container-fill-in-the-blank'>". $answer . "</div>";
        } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
            $temp_string = unserialize($answer);
            $answer_string = $temp_string[0];
            // replaces [choices] with `select` field
            $replace_callback = function ($blank) use ($questionId, $langSelect) {
                static $id = 0;
                $id++;
                $selection_text = explode("|", str_replace(array('[',']'), ' ', q($blank[0])));
                array_unshift($selection_text, "--- $langSelect ---");
                return selection($selection_text, "choice[$questionId][$id]", 0);
            };
            $answer_string = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer_string));
            echo "<div class='container-fill-in-the-blank'>" . $answer_string . "</div>";
        } elseif ($answerType == MATCHING) { // matching
            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown on the right side
                $select[$answerId]['Reponse'] = $answer;
            } else {
                echo "<tr class='even'>
                    <td width='200'><b>$cpt2.</b> $answer</td>
                    <td width='130'><div>
                     <select class='form-select' name='choice[$questionId][$answerId]'>
                       <option value='0'>--</option>";

                // fills the list-box
                foreach ($select as $key => $val) {
                    echo "<option value=\"$key\">$val[Lettre]</option>";
                }
                echo "</select></td>
                                    <td width='200'>";
                if (isset($select[$cpt2])) {
                    echo '<b>' . $select[$cpt2]['Lettre'] . '.</b> ' . $select[$cpt2]['Reponse'];
                } else {
                    echo '&nbsp;';
                }
                echo "</td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown on the right side
                    while (isset($select[$cpt2])) {
                        echo "<tr class='even'>
                                <td width='60%' colspan='2'>&nbsp;</td>
                                <td width='40%' valign='top'>" .
                                    "<b>" . $select[$cpt2]['Lettre'] . ".</b> " .
                                            $select[$cpt2]['Reponse'] . "</td>
                              </tr>";
                        $cpt2++;
                    } // end while()
                }  // end if()
            }

        } elseif ($answerType == TRUE_FALSE) {
            echo "<div class='radio mb-2'>
                          <label>
                            <input type='radio' name='choice[$questionId]' value='$answerId'>
                            " . $answer . "
                          </label>
                        </div>";
        }
    } // end for()
    if ($answerType == MATCHING) {
        echo "</table>";
    }

    if (!$nbrAnswers) {
        echo "<tr><td colspan='2'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langNoAnswer</span></div></td></tr>";
    }
    // destruction of the Answer object
    unset($objAnswerTmp);
    // destruction of the Question object
    unset($objQuestionTmp);
    return $nbrAnswers;
}
