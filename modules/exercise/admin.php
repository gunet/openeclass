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


require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';

$require_course_reviewer = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'exercises';

if (isset($_GET['htopic'])) {
    $htopic = $_GET['htopic'];
    switch ($htopic) {
        case '1': $helpSubTopic = 'multiple_choice_one'; break;
        case '2': $helpSubTopic = 'multiple_choice_many'; break;
        case '3':
        case '7': $helpSubTopic = 'fill_gaps'; break;
        case '4': $helpSubTopic = 'matching'; break;
        case '5': $helpSubTopic = 'true_false'; break;
        case '6': $helpSubTopic = 'free_text'; break;
        case '8': $helpSubTopic = 'fill_gaps_predefined_answers'; break;
    }
}

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
load_js('sortable/Sortable.min.js');
ModalBoxHelper::loadModalBox();

$toolName = $langExercices;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

// picture path
$picturePath = "courses/$course_code/image";

// construction of the Exercise object
$objExercise = new Exercise();
if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
    $objExercise->read($exerciseId);
    $nbrQuestions = $objExercise->selectNbrQuestions();
    $randomQuestions = $objExercise->isRandom();
    $shuffleQuestions = $objExercise->selectShuffle();
}

if ($is_editor) {
// initializes the Question object
    if (isset($_GET['newQuestion']) || isset($_GET['modifyQuestion']) || isset($_GET['modifyAnswers'])) {
        // construction of the Question object
        $objQuestion = new Question();

        // reads question data
        if (isset($_GET['modifyQuestion']) || isset($_GET['modifyAnswers'])) {
            if (isset($_GET['modifyQuestion'])) {
                $question_id = intval($_GET['modifyQuestion']);
            } elseif (isset($_GET['modifyAnswers'])) {
                $question_id = intval($_GET['modifyAnswers']);
            }
            // if question not found
            if (!$objQuestion->read($question_id)) {
                Session::Messages($langQuestionNotFound);
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
            }
            if (isset($_GET['modifyAnswers'])) {
                //clone and redirect to edit
                if (isset($_GET['clone'])) {
                    // if user comes from an exercise page
                    if (isset($exerciseId)) {
                        // duplicates the question
                        $new_question_id = $objQuestion->duplicate();
                        // deletes the old question from the specific exercise
                        $objQuestion->delete($exerciseId);
                        // removes the old question ID from the question list of the Exercise object
                        $objExercise->removeFromList($question_id);
                        // adds the new question ID into the question list of the Exercise object
                        $objExercise->addToList($new_question_id);
                        // construction of the duplicated Question
                        $objQuestion = new Question();
                        $objQuestion->read($new_question_id);
                        // copies answers from the old question to the new
                        $objAnswer = new Answer($question_id);
                        $objAnswer->duplicate($new_question_id);
                        redirect_to_home_page("modules/exercise/admin.php?course=$course_code&modifyQuestion=$new_question_id&exerciseId=$exerciseId");
                        exit();
                        // if user comes from question pool
                    } else {
                        $new_question_id = $objQuestion->duplicate();
                        $objQuestion = new Question();
                        $objQuestion->read($new_question_id);
                        $objAnswer = new Answer($question_id);
                        $objAnswer->duplicate($new_question_id);
                        redirect_to_home_page("modules/exercise/admin.php?course=$course_code&modifyQuestion=$new_question_id");
                        exit();
                    }
                } else {
                    $objAnswer = new Answer($question_id);
                }
                include 'answer_admin.inc.php';
                $pageName = $langQuestionManagement;
                $navigation[] = array(
                    'url' => (isset($exerciseId) ? "admin.php?course=$course_code&amp;exerciseId=$exerciseId" : "question_pool.php?course=$course_code&amp;exerciseId=0"),
                    'name' => (isset($exerciseId) ? $langExerciseManagement : $langQuestionPool)
                );
            } else {
                $pageName = $langInfoQuestion;
                $navigation[] = array(
                    'url' => (isset($exerciseId) ? "admin.php?course=$course_code&amp;exerciseId=$exerciseId" : "question_pool.php?course=$course_code&amp;exerciseId=0"),
                    'name' => (isset($exerciseId) ? $langExerciseManagement : $langQuestionPool)
                );
                include 'statement_admin.inc.php';
            }
        } else {
            $pageName = $langNewQu;
            $navigation[] = array(
                'url' => (isset($exerciseId) ? "admin.php?course=$course_code&amp;exerciseId=$exerciseId" : "question_pool.php?course=$course_code&amp;exerciseId=0"),
                'name' => (isset($exerciseId) ? $langExerciseManagement : $langQuestionPool)
            );
            include 'statement_admin.inc.php';
        }
    } elseif (isset($_GET['importIMSQTI'])) {
        $pageName = $langNewQu;
        $navigation[] = array(
            'url' => (isset($exerciseId) ? "admin.php?course=$course_code&amp;exerciseId=$exerciseId" : "question_pool.php?course=$course_code&amp;exerciseId=0"),
            'name' => (isset($exerciseId) ? $langExerciseManagement : $langQuestionPool)
        );
        include 'imsqti.inc.php';
    } elseif (isset($_GET['importAiken'])) {
        $pageName = $langNewQu;
        $navigation[] = array(
            'url' => (isset($exerciseId) ? "admin.php?course=$course_code&amp;exerciseId=$exerciseId" : "question_pool.php?course=$course_code&amp;exerciseId=0"),
            'name' => (isset($exerciseId) ? $langExerciseManagement : $langQuestionPool)
        );
        include 'import_aiken.php';
    } elseif (isset($_GET['preview'])) { // exercise preview
            $pageName = $langSee;
            display_exercise($exerciseId);
    } else {
        if (isset($_GET['NewExercise'])) {
            $pageName = $langNewEx;
        } elseif (isset($_GET['modifyExercise'])) {
            $pageName = $langInfoExercise;
            $navigation[] = array('url' => "admin.php?course=$course_code&amp;exerciseId=$exerciseId", 'name' => $langExerciseManagement);
        } else {
            $pageName = $langExerciseManagement;
        }
        include 'exercise_admin.inc.php';
        if (!isset($_GET['NewExercise']) && !isset($_GET['modifyExercise'])) {
            include 'question_list_admin.inc.php';
        }
    }
} else if ($is_course_reviewer) {
    if (isset($_GET['preview'])) { // exercise preview
        $pageName = $langSee;
        display_exercise($exerciseId);
    }
}

draw($tool_content, 2, null, $head_content);
