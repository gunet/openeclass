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

require_once 'QuestionType.php';
require_once 'MultipleChoiceUniqueAnswer.php';
require_once 'MultipleChoiceMultipleAnswer.php';
require_once 'MatchingAnswer.php';
require_once 'FillInBlanksAnswer.php';
require_once 'FillInPredefinedAnswer.php';
require_once 'FreeTextAnswer.php';
require_once 'DragAndDropTextAnswer.php';
require_once 'DragAndDropMarkersAnswer.php';


/**
 * @brief display question
 * @param $objQuestionTmp
 * @param array $exerciseResult
 * @param $question_number
 * @return int
 */
function showQuestion(&$objQuestionTmp, $question_number, array $exerciseResult = [], $options = []) {

    global $tool_content, $picturePath, $langQuestion, $langInfoGrades,
            $exerciseType, $nbrQuestions, $langInfoGrade, $langHasAnswered;

    $questionId = $objQuestionTmp->selectId();
    $questionWeight = $objQuestionTmp->selectWeighting();
    $answerType = $objQuestionTmp->selectType();

    $message = $langInfoGrades;
    if (intval($questionWeight) == $questionWeight) {
        $questionWeight = intval($questionWeight);
    }
    if ($questionWeight == 1) {
        $message = $langInfoGrade;
    }

    $questionName = $objQuestionTmp->selectTitle();
    $questionDescription = standard_text_escape($objQuestionTmp->selectDescription());
    $questionTypeWord = $objQuestionTmp->selectTypeLegend($answerType);
    if ($exerciseType == SINGLE_PAGE_TYPE) {
        $qNumber = $question_number;
    } else {
        $qNumber = "$question_number / $nbrQuestions";
    }

    $classImg = '';
    $classContainer = '';
    $classCanvas = '';
    if ($answerType == DRAG_AND_DROP_MARKERS) {
        $classImg = 'drag-and-drop-markers-img';
        $classContainer = 'drag-and-drop-markers-container';
        $classCanvas = 'drag-and-drop-markers-canvas';
    }

    $tool_content .= "
            <div class='card panelCard px-lg-4 py-lg-3 qPanel panelCard-exercise mt-4' id='qPanel$questionId'>
              <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3 class='mb-0 d-flex justify-content-start align-items-center gap-2 flex-wrap'>$langQuestion $qNumber
                    <small>($questionTypeWord &mdash; $questionWeight $message)</small>&nbsp;
                    <span title='$langHasAnswered' id='qCheck$question_number'></span>
                </h3>
            </div>
            <div class='panel-body'>
                <div class='text-heading-h4 mb-4'>" . q_math($questionName) . "</div>";
                if (!empty($questionDescription)) {
                    $tool_content .= " <div class='mb-4'>$questionDescription</div>";
                }
                if (file_exists($picturePath . '/quiz-' . $questionId)) {
                    $tool_content .= "<div class='$classContainer' id='image-container-$questionId' style='position: relative; display: inline-block;'>
                                        <img class='$classImg' id='img-quiz-$questionId' src='../../$picturePath/quiz-$questionId' style='width: 100%;'>
                                        <canvas id='drawingCanvas-$questionId' class='$classCanvas' style='position: absolute; top: 0; left: 0; z-index: 10;'></canvas>
                                      </div>";
                }


    // display and execute question
    $tool_content .= answer_question($questionId, $question_number, $answerType, $exerciseResult, $options);

    $tool_content .= "
                </div>
            </div>";

    // destruction of the Question object
    unset($objQuestionTmp);

    $tool_content .= "
    <script>
        function tinyMceCallback(editor) {
            editor.on('Change', function (e) {
                if (this.getContent({format: 'text'}).trim() != '') {
                    var qPanel = $('#qPanel' + e.target.id.split(/[\[\]]/)[1]);
                    var qCheck = qPanel.find('span').first();
                    var qButton = $('#' + qCheck.attr('id').replace('qCheck', 'q_num'));
                    qCheck.addClass('fa fa-check');
                    qButton.removeClass('btn-default').addClass('btn-info');
                }
            });
        }
    </script>";

}


/**
 * @brief exercise teacher view
 * @param $exercise_id
 */
function display_exercise($exercise_id): void
{

    global $tool_content, $head_content, $is_editor, $langQuestion, $picturePath,
           $langQuestionScore, $langTotalScore, $langQuestionsManagement, $action_bar,
           $course_code, $langBack, $langModify, $langExerciseExecute, $langFrom2,
           $langFromRandomCategoryQuestions, $langFromRandomDifficultyQuestions, $langQuestionFeedback,
           $langUsedInSeveralExercises, $langModifyInAllExercises, $langModifyInThisExercise;

    $head_content .= "
        <script>
            $(function() {
                $(document).on('click', '.warnLink', function(e){
                    var modifyAllLink = $(this).attr('href');
                    var modifyOneLink = modifyAllLink.concat('&clone=true');
                    $('a#modifyAll').attr('href', modifyAllLink);
                    $('a#modifyOne').attr('href', modifyOneLink);
                });
            });
        </script>";

    // Modal
    $tool_content .= "
        <div class='modal fade' id='modalWarning' tabindex='-1' role='dialog' aria-hidden='true'>
          <div class='modal-dialog'>
            <div class='modal-content'>
              <div class='modal-body text-center'>
                $langUsedInSeveralExercises
              </div>
              <div class='modal-footer'>
                <a href='#' id='modifyAll' class='btn submitAdminBtn'>$langModifyInAllExercises</a>
                <a href='#' id='modifyOne' class='btn submitAdminBtn'>$langModifyInThisExercise</a>
              </div>
            </div>
          </div>
        </div>
        ";

    $exercise = new Exercise();
    $exercise->read($exercise_id);
    $question_list = $exercise->selectQuestionList();
    $totalWeighting = $exercise->selectTotalWeighting();

    $action_bar = action_bar([
        ['title' => $langBack,
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary'
        ],
        ['title' => $langExerciseExecute,
            'url' => "exercise_submit.php?course=$course_code&exerciseId=$exercise_id",
            'icon' => 'fa-play-circle',
            'button-class' => 'btn-danger',
            'show' => (!empty($question_list))
        ],
        ['title' => $langQuestionsManagement,
            'url' => "admin.php?course=$course_code&exerciseId=$exercise_id",
            'icon' => 'fa-cogs',
            'level' => 'primary-label',
            'button-class' => 'btn-success',
            'show' => $is_editor
        ]
    ]);

    $tool_content .= $action_bar;
    $tool_content .= "
    <div class='col-12 mb-4'><div class='card panelCard card-default px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
              <h3>" . q_math($exercise->selectTitle());
              if ($is_editor) {
                    $tool_content .= "<a class='ms-2' href='admin.php?course=$course_code&amp;exerciseId=$exercise_id&amp;modifyExercise=yes' aria-label='$langModify'>
                      <span class='fa fa-edit' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langModify'></span>
                    </a>";
                }
              $tool_content .= "</h3>
            </div>
            <div class='card-body'>" . standard_text_escape($exercise->selectDescription()) . "</div>
        </div>
    </div>";

    $i = 1;
    $hasRandomQuestions = false;
    foreach ($question_list as $qid) {
        $question = new Question();
        if (!is_array($qid)) {
            $question->read($qid);
        }
        $questionName = $question->selectTitle();
        $questionDescription = $question->selectDescription();
        $questionFeedback = $question->selectFeedback();
        $questionWeighting = $question->selectWeighting();
        $answerType = $question->selectType();

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            $colspan = 3;
        } elseif ($answerType == MATCHING) {
            $colspan = 2;
        } else {
            $colspan = 1;
        }

        $tool_content .= "<div class='col-12 mb-4'><div class='table-responsive'><table class='table-default'>";
        if (is_array($qid)) { // placeholder for random questions (if any)
            $hasRandomQuestions = true;
            $tool_content .= "<tr class='active'>
                                <td colspan='$colspan'>
                                    <strong><u>$langQuestion</u>: $i</strong>
                                </td>
                               </tr>";
            if ($qid['criteria'] == 'difficulty') {
                next($qid);
                $number = key($qid);
                $difficulty = $qid[$number];
                $tool_content .= "<tr><td>";
                $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomDifficultyQuestions '" . $question->selectDifficultyLegend($difficulty) . "'</em>";
                $tool_content .= "</td></tr>";
            } else if ($qid['criteria'] == 'category') {
                next($qid);
                $number = key($qid);
                $category = $qid[$number];
                $tool_content .= "<tr><td>";
                $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomCategoryQuestions '" . $question->selectCategoryName($category) . "'</em>";
                $tool_content .= "</td></tr>";
            }  else if ($qid['criteria'] == 'difficultycategory') {
                next($qid);
                $number = key($qid);
                $difficulty = $qid[$number][0];
                $category = $qid[$number][1];
                $tool_content .= "<tr><td>";
                $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span>
                    <em>$number $langFromRandomDifficultyQuestions '" . $question->selectDifficultyLegend($difficulty) . "' $langFrom2 '" . $question->selectCategoryName($category) . "'</em>";
                $tool_content .= "</td></tr>";
            }
        } else {
            if ($question->selectNbrExercises() > 1) {
                $modal_params = "class='warnLink' data-bs-toggle='modal' data-bs-target='#modalWarning' data-remote='false'";
            } else {
                $modal_params = '';
            }
            $tool_content .= "
            <thead>
                <tr class='active'>
                <td colspan='$colspan'>
                    <strong class='pe-2'><u>$langQuestion</u>: $i</strong>";
            if ($is_editor) {
                $tool_content .= "<a $modal_params href = 'admin.php?course=$course_code&amp;exerciseId=$exercise_id&amp;modifyAnswers=$qid' aria-label='$langModify'>
                    <span class='fa fa-edit' data-bs-toggle='tooltip' data-bs-placement ='bottom' data-bs-original-title ='$langModify' ></span >
                    </a >";
            }
                $tool_content .= "</td>
                </tr>
            </thead>
            <tr>
              <td colspan='$colspan'>";

            $tool_content .= "
            <strong>" . q_math($questionName) . "</strong>
            <br>" . standard_text_escape($questionDescription) . "<br><br>
            </td></tr>";

            $classImg = '';
            $classContainer = '';
            $classCanvas = '';
            if ($answerType == DRAG_AND_DROP_MARKERS) {
                $classImg = 'drag-and-drop-markers-img';
                $classContainer = 'drag-and-drop-markers-container';
                $classCanvas = 'drag-and-drop-markers-canvas';
            }

            if (file_exists($picturePath . '/quiz-' . $qid)) {
                $tool_content .= "<tr>
                                    <td colspan='$colspan'>
                                        <div class='$classContainer' id='image-container-$qid' style='position: relative; display: inline-block;'>
                                            <img class='$classImg' id='img-quiz-$qid' src='../../$picturePath/quiz-" . $qid . "'>
                                            <canvas class='$classCanvas' id='drawingCanvas-$qid' style='position: absolute; top: 0; left: 0; z-index: 10;'></canvas>
                                        </div>
                                    </td>
                                 </tr>";
            }

            // display answers
            $tool_content .= preview_question($qid, $answerType);

            if (!is_null($questionFeedback)) {
                $tool_content .= "<tr><td colspan='$colspan'>";
                $tool_content .= "<div style='margin-top: 10px;'><strong>$langQuestionFeedback:</strong><br>" . standard_text_escape($questionFeedback) . "</div>";
                $tool_content .= "</td></tr>";
            }

            $tool_content .= "<tr class='active'><th colspan='$colspan'>";
            $tool_content .= "<div class='px-2 py-3'><span>$langQuestionScore: <strong>" . round($questionWeighting, 2) . "</strong></span></div>";
            $tool_content .= "</th></tr>";
        }
        $tool_content .= "</table></div></div>";

        unset($answer);
        // question  numbering
        if (isset($number) and $number > 0) {
            $i = $i + $number;
            $number = 0;
        } else {
            $i++;
        }
    }
    if (!$hasRandomQuestions) {
        $tool_content .= "<div class='col-12 mt-4'>
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-circle-info fa-lg'></i>
                                <span><strong>$langTotalScore</strong>: $totalWeighting</span>
                            </div>
                          </div>";
    }
}


function replaceBracketsWithBlanks($text,$cardId) {
    // Use preg_replace_callback to find all brackets
    return preg_replace_callback('/\[(\d+)\]/', function($matches) use ($cardId) {
        $blankId = htmlspecialchars($matches[1]);
        // Return a span element with a data-blank-id attribute
        $card = "words_" . $cardId;
        return "<span class='blank' data-answer='$blankId' data-blank-id='$blankId' data-card-id='$card'></span>";
    }, $text);
}




/**
 * @brief preview question
 * @param $question_id
 * @param $answer_type
 * @return string
 */
function preview_question($question_id, $answer_type): string {

    $html_content = '';
    switch ($answer_type) {
        case UNIQUE_ANSWER:
        case TRUE_FALSE:
        case MULTIPLE_ANSWER:
            $answer = new MultipleChoiceUniqueAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case FILL_IN_BLANKS:
        case FILL_IN_BLANKS_TOLERANT:
            $answer = new FillInBlanksAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case FILL_IN_FROM_PREDEFINED_ANSWERS:
            $answer = new FillInPredefinedAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case MATCHING:
            $answer = new MatchingAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case DRAG_AND_DROP_MARKERS:
        case DRAG_AND_DROP_TEXT:
            $answer = new DragAndDropTextAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
    }

    return $html_content;
}

/**
 * @brief display questions during exercise submission
 * @param $question_id
 * @param $question_number
 * @param $exerciseResult
 * @param $options
 * @param $answer_type
 * @return string
 */
function answer_question($question_id, $question_number, $answer_type, $exerciseResult = [], $options = []): string {

    $html = '';
    switch ($answer_type) {
        case MULTIPLE_ANSWER:
            $answer = new MultipleChoiceMultipleAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case TRUE_FALSE:
        case UNIQUE_ANSWER:
            $answer = new MultipleChoiceUniqueAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case FILL_IN_BLANKS:
        case FILL_IN_BLANKS_TOLERANT:
            $answer = new FillInBlanksAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case FILL_IN_FROM_PREDEFINED_ANSWERS:
            $answer = new FillInPredefinedAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case MATCHING:
            $answer = new MatchingAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case FREE_TEXT:
            $answer = new FreeTextAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case DRAG_AND_DROP_TEXT:
            $answer = new DragAndDropTextAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case DRAG_AND_DROP_MARKERS:
            $answer = new DragAndDropMarkersAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
    }
    unset($answer);

    return $html;
}
