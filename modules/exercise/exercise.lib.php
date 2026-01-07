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
require_once 'CalculatedAnswer.php';
require_once 'OrderingAnswer.php';
require_once 'OralAnswer.php';

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
    } elseif ($answerType == CALCULATED) {
        // Update variables for each wildcard if necessary.
        require_once __DIR__ . '/../../vendor/autoload.php';
        updateWildCardsWithRandomVariables($questionId, $exerciseType);
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
                if (!empty($questionDescription) && $answerType != CALCULATED) {
                    $tool_content .= " <div class='mb-4'>$questionDescription</div>";
                }
                if (file_exists($picturePath . '/quiz-' . $questionId)) {
                    $tool_content .= "<div class='$classContainer' id='image-container-$questionId' style='position: relative; display: inline-block;'>
                                        <img class='$classImg' id='img-quiz-$questionId' src='../../$picturePath/quiz-$questionId' style='width: 100%;'>
                                        <canvas id='drawingCanvas-$questionId' class='$classCanvas'></canvas>
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
            'level' => 'primary',
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
                      <span class='fa-solid fa-edit' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langModify'></span>
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

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE || $answerType == CALCULATED || $answerType == ORDERING) {
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
                    <span class='fa-solid fa-edit' data-bs-toggle='tooltip' data-bs-placement ='bottom' data-bs-original-title ='$langModify' ></span >
                    </a >";
            }
                $tool_content .= "</td>
                </tr>
            </thead>
            <tr>
              <td colspan='$colspan'>";

              $arithmetic_expression_str = '';
            if ($answerType == CALCULATED) {
                $des_arr = unserialize($questionDescription);
                if (is_array($des_arr)) {
                    $questionDescription = $des_arr['question_description'] ?? '';
                    $arithmetic_expression = $des_arr['arithmetic_expression'] ?? '';
                }
                $objAn = new Answer($qid);
                $arithmetic_expression_str = $objAn->replaceItemsBracesWithWildCards($arithmetic_expression, $qid);
                unset($objAn);
            }
            $tool_content .= "
            <strong>" . q_math($questionName) . "</strong>
            <br>" . standard_text_escape($questionDescription) . "<br>" . $arithmetic_expression_str ."<br><br>
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
                                            <canvas class='$classCanvas' id='drawingCanvas-$qid'></canvas>
                                        </div>
                                    </td>
                                 </tr>";
            }

            if ($answerType == DRAG_AND_DROP_TEXT) {
                $objAnswerTmp = new Answer($qid);
                $questionText = $objAnswerTmp->get_drag_and_drop_text();
                $tool_content .= "<tr><td>$questionText</td></tr>";
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
        return "<span class='blank blank-drag-and-drop-text' data-answer='$blankId' data-blank-id='$blankId' data-card-id='$card'></span>";
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
            $answer = new DragAndDropMarkersAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case DRAG_AND_DROP_TEXT:
            $answer = new DragAndDropTextAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case CALCULATED:
            $answer = new CalculatedAnswer($question_id);
            $html_content .= $answer->PreviewQuestion();
            break;
        case ORDERING:
            $answer = new OrderingAnswer($question_id);
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
        case ORAL:
            $answer = new OralAnswer($question_id);
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
        case CALCULATED:
            $answer = new CalculatedAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
        case ORDERING:
            $answer = new OrderingAnswer($question_id);
            $html .= $answer->AnswerQuestion($question_number, $exerciseResult, $options);
            break;
    }
    unset($answer);

    return $html;
}

/**
 * @brief display user answer in question results
 * @param $answer_type
 * @param $question_id
 * @param $choice
 * @param $eurid
 * @param $regrade
 * @param $extra_type
 * @return string
 */
function question_result($answer_type, $question_id, $choice, $eurid, $regrade): string {

    $html = '';
    switch ($answer_type) {
        case MULTIPLE_ANSWER:
            $answer = new MultipleChoiceMultipleAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case UNIQUE_ANSWER:
        case TRUE_FALSE:
            $answer = new MultipleChoiceUniqueAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case FILL_IN_BLANKS_TOLERANT:
        case FILL_IN_BLANKS:
            $answer = new FillInBlanksAnswer($question_id);
            if ($answer_type == FILL_IN_BLANKS_TOLERANT) {
                $html .= $answer->QuestionResult($choice, $eurid, $regrade, 'tolerant');
            } else {
                $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            }
            break;
        case FILL_IN_FROM_PREDEFINED_ANSWERS:
            $answer = new FillInPredefinedAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case MATCHING:
            $answer = new MatchingAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case FREE_TEXT:
            $answer = new FreeTextAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case ORAL:
            $answer = new OralAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case DRAG_AND_DROP_TEXT:
            $answer = new DragAndDropTextAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case DRAG_AND_DROP_MARKERS:
            $answer = new DragAndDropMarkersAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case CALCULATED:
            $answer = new CalculatedAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
        case ORDERING:
            $answer = new OrderingAnswer($question_id);
            $html .= $answer->QuestionResult($choice, $eurid, $regrade);
            break;
    }

    unset($answer);

    return $html;
}

/**
 * @brief Create random variable regarding the range of values of a wildcard.
 * @param $min
 * @param $max
 * @param $decimals
 * @return float|mixed
 */
function getRandomDecimal($min, $max, $decimals = 0) {

    $scale = pow(10, $decimals);
    $minInt = (int)($min * $scale);
    $maxInt = (int)($max * $scale);
    $randInt = mt_rand($minInt, $maxInt);

    return $randInt / $scale;
}


/**
 * @brief Update the value of a wildcard regarding its range of values.
 * @param $questionId
 */
function updateWildCardsWithRandomVariables($questionId, $exerciseType) {

    global $uid;

    $questionDisplay = [];
    if (isset($_SESSION['QuestionDisplayed'][$uid])) {
        foreach ($_SESSION['QuestionDisplayed'][$uid] as $q) {
            $questionDisplay[] = $q;
        }
    }

    // If the user has not executed the current question , update the wildcards of the question with new values.
    if (!in_array($questionId, $questionDisplay)) {

        $_SESSION['QuestionDisplayed'][$uid][] = $questionId;

        // Instantiate ExpressionLanguage
        $expressionLanguage = new ExpressionLanguage();

        // These math functions must be registered that are not supported.
        $functions = [
            'cos' => 'cos',
            'sin' => 'sin',
            'tan' => 'tan',
            'acos' => 'acos',
            'asin' => 'asin',
            'atan' => 'atan',
            'atan2' => 'atan2',
            'pow' => 'pow',
            'sqrt' => 'sqrt',
            'abs' => 'abs',
            'log' => 'log',
            'log10' => 'log10',
            'exp' => 'exp',
            'max' => 'max',
            'min' => 'min',
            'round' => 'round',
            'floor' => 'floor',
            'ceil' => 'ceil',
        ];

        foreach ($functions as $name => $function) {
            $expressionLanguage->register($name, $function, function (array $variables, ...$args) use ($function) {
                return $function(...$args);
            });
        }

        $predefinedAnswers = Database::get()->queryArray("SELECT answer,r_position FROM exercise_answer WHERE question_id = ?d", $questionId);
        $q_data = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId);

        if (!is_null($q_data->options) or !empty($q_data->options)) {

            $newOptions = [];
            $oldOptions = json_decode($q_data->options, true);
            if (count($oldOptions) > 0) {
                for ($i = 0; $i < count($oldOptions); $i++) {
                    if ($oldOptions[$i]['type'] == 1 && is_numeric($oldOptions[$i]['minimum']) && is_numeric($oldOptions[$i]['maximum']) && is_numeric($oldOptions[$i]['decimal'])) {
                            $value_wcard = getRandomDecimal($oldOptions[$i]['minimum'], $oldOptions[$i]['maximum'], $oldOptions[$i]['decimal']);
                            $newOptions[] = [
                                                'item' => $oldOptions[$i]['item'],
                                                'minimum' => $oldOptions[$i]['minimum'],
                                                'maximum' => $oldOptions[$i]['maximum'],
                                                'decimal' => $oldOptions[$i]['decimal'],
                                                'value' => $value_wcard,
                                                'type' => $oldOptions[$i]['type']
                                            ];
                    } elseif ($oldOptions[$i]['type'] == 2) {
                        $newOptions[] = [
                                            'item' => $oldOptions[$i]['item'],
                                            'minimum' => '',
                                            'maximum' => '',
                                            'decimal' => '',
                                            'value' => $oldOptions[$i]['value'],
                                            'type' => $oldOptions[$i]['type']
                                        ];
                    }
                }
            }

            if (count($newOptions) > 0) {
                $newJsonItems = json_encode($newOptions);
                $q = Database::get()->query("UPDATE exercise_question SET options = ?s WHERE id = ?d", $newJsonItems, $questionId);
                if ($q) {
                    foreach ($predefinedAnswers as $an) {
                        $arr = unserialize($an->answer);
                        if (count($arr) > 0) {
                            $tmp_expression = '';
                            foreach ($arr as $r) {
                                $expression = $r['expression'];
                                $tmp_expression = $expression;
                            }
                            $dataItems = json_decode($newJsonItems, true);

                            // Create a key-value array for items
                            $wildCards = [];
                            foreach ($dataItems as $item) {
                                $wildCards[$item['item']] = $item['value'];
                            }

                            // Get expression of predefined answer
                            foreach ($wildCards as $key => $value) {
                                $expression = str_replace("{" . $key . "}", $value, $expression);
                            }

                            // Check for division by zero (simple check)
                            if (preg_match('/\/\s*0(\D|$)/', $expression)) {
                                return null; // or handle as needed
                            }

                            try {
                                $result = $expressionLanguage->evaluate($expression);
                                $tmpfinalAnswer = [];
                                $tmpfinalAnswer[] = [
                                    'expression' => $tmp_expression,
                                    'result' => $result
                                ];
                                $finalAnswer = serialize($tmpfinalAnswer);
                                Database::get()->query("UPDATE exercise_answer SET answer = ?s WHERE question_id = ?d AND r_position = ?d", $finalAnswer, $questionId, $an->r_position);
                            } catch (\Exception $e) {
                                // Handle evaluation error
                                return null;
                            }

                        }
                    }
                }
            }

        }

    }
}




/**
 * Removes JSON data associated with a given marker ID from the session and updates the corresponding question data in the database.
 * Also deletes a related file if it exists.
 *
 * @param int $markerId The ID of the marker to be removed.
 * @param int $questionId The ID of the question associated with the marker.
 * @return void
 */
function removeJsonDataFromMarkerId($markerId,$questionId) {
    global $webDir,$course_code;

    if ($markerId > 0 && isset($_SESSION['data_shapes'][$questionId])) {
        $jsonArray = explode('|', $_SESSION['data_shapes'][$questionId]);
        $newJsonArray = [];

        foreach ($jsonArray as $json) {
            $jsonDecoded = json_decode($json, true);
            if ($jsonDecoded && isset($jsonDecoded['marker_id'])) {
                if ($jsonDecoded['marker_id'] != $markerId) {
                    $newJsonArray[] = $json; // keep if not matching
                }
                // else, skip (this removes the matching marker_id)
            } else {
                // handle invalid JSON if needed
                $newJsonArray[] = $json; // keep invalid JSON as is
            }
        }

        $_SESSION['data_shapes'][$questionId] = implode('|', $newJsonArray);
        Database::get()->query("UPDATE exercise_question SET options = ?s WHERE id = ?d", $_SESSION['data_shapes'][$questionId], $questionId);
        $filePath = "$webDir/courses/$course_code/image/answer-$questionId-$markerId";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

}

/**
 * Extracts and processes data markers from a JSON structure stored in the database for a specific question.
 *
 * @param int $questionId The ID of the question to retrieve data markers for.
 * @return array An associative array of data markers, where each marker is indexed by its marker ID and contains
 *               information such as shape type, coordinates, grade, and answer details.
 */
function getDataMarkersFromJson($questionId) {
    global $webDir, $course_code;

    $arrDataMarkers = [];
    $jsonData = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
    if ($jsonData) {
        $dataJsonMarkers = explode('|', $jsonData);
        foreach ($dataJsonMarkers as $dataJsonValue) {
            $markersData = json_decode($dataJsonValue, true);
            // Loop through each item in the original array
            if ($markersData) {
                foreach ($markersData as $index => $value) {
                    if (count($markersData) == 10) { // circle or rectangle
                        $arrDataMarkers[$markersData['marker_id']] = [
                            'marker_answer' => $markersData['marker_answer'],
                            'marker_shape' => $markersData['shape_type'],
                            'marker_coordinates' => $markersData['x'] . ',' . $markersData['y'],
                            'marker_offsets' => $markersData['endX'] . ',' . $markersData['endY'],
                            'marker_grade' => $markersData['marker_grade'],
                            'marker_radius' => $markersData['marker_radius'],
                            'marker_answer_with_image' => $markersData['marker_answer_with_image']
                        ];
                    } elseif (count($markersData) == 6) { // polygon
                        $arrDataMarkers[$markersData['marker_id']] = [
                            'marker_answer' => $markersData['marker_answer'],
                            'marker_shape' => $markersData['shape_type'],
                            'marker_coordinates' => $markersData['points'],
                            'marker_grade' => $markersData['marker_grade'],
                            'marker_answer_with_image' => $markersData['marker_answer_with_image']
                        ];
                    } elseif (count($markersData) == 5) { // without shape . So the defined answer is not correct
                        $arrDataMarkers[$markersData['marker_id']] = [
                            'marker_answer' => $markersData['marker_answer'],
                            'marker_shape' => null,
                            'marker_coordinates' => null,
                            'marker_grade' => 0,
                            'marker_answer_with_image' => $markersData['marker_answer_with_image']
                        ];
                    }
                }
            }
        }
    }

    return $arrDataMarkers;
}

/**
 * Extracts distinct non-numeric variables enclosed within curly brackets from the given text.
 *
 * @param string $text The input string containing variables inside curly brackets.
 * @return array An array of unique variable names found, excluding numeric values.
 */
function extractValuesInCurlyBrackets($text) {
    // Find all occurrences of {...}
    preg_match_all('/\{([^{}]+)\}/u', $text, $matches);
    $variables = [];

    foreach ($matches[1] as $group) {
        // For each group, split to get individual variables
        // Match all Unicode letters and numbers
        preg_match_all('/[\p{L}\p{N}_]+/u', $group, $submatches);
        foreach ($submatches[0] as $var) {
            $variables[] = $var;
        }
    }

    // Remove numeric-only variables
    $variables = array_filter($variables, function($var) {
        return !is_numeric($var);
    });

    // Remove duplicates
    return array_unique($variables);
}

/**
 * Evaluates a mathematical expression by replacing wildcards with their corresponding values and computing the result.
 * Wildcards are dynamically replaced based on the associated question's data.
 *
 * @param string $expression The mathematical expression to evaluate, which may contain wildcards.
 * @param int $questionId The ID of the question whose data provides values for the wildcards in the expression.
 * @return mixed The evaluated result of the expression, or null in case of errors or invalid data.
 */
function evaluateExpression($expression, $questionId) {
    $options = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
    if ($options) {
        // Decode JSON to array
        $dataItems = json_decode($options, true);

        // Create a key-value array for items
        $wildCards = [];
        foreach ($dataItems as $item) {
            $wildCards[$item['item']] = $item['value'];
        }

        foreach ($wildCards as $key => $value) {
            $expression = str_replace("{" . $key . "}", $value, $expression);
        }

        // Check for division by zero (simple check)
        if (preg_match('/\/\s*0(\D|$)/', $expression)) {
            return null; // or handle as needed
        }

        // Instantiate ExpressionLanguage
        $expressionLanguage = new ExpressionLanguage();

        // These math functions must be registered that are not supported.
        $functions = [
            'cos' => 'cos',
            'sin' => 'sin',
            'tan' => 'tan',
            'acos' => 'acos',
            'asin' => 'asin',
            'atan' => 'atan',
            'atan2' => 'atan2',
            'pow' => 'pow',
            'sqrt' => 'sqrt',
            'abs' => 'abs',
            'log' => 'log',
            'log10' => 'log10',
            'exp' => 'exp',
            'max' => 'max',
            'min' => 'min',
            'round' => 'round',
            'floor' => 'floor',
            'ceil' => 'ceil',
        ];

        foreach ($functions as $name => $function) {
            $expressionLanguage->register($name, $function, function (array $variables, ...$args) use ($function) {
                return $function(...$args);
            });
        }

        // Evaluate the expression
        try {
            $result = $expressionLanguage->evaluate($expression);
            return $result;
        } catch (\Exception $e) {
            // Handle evaluation error
            return null;
        }
    }

    return null; // If options not found
}


/**
 * Generates a random floating-point number within a specified range and with a specified number of decimal places.
 * If the maximum value is less than the minimum, it returns 0. If the number of decimals is 0 or less, it generates a random integer.
 *
 * @param float $min The minimum value of the range.
 * @param float $max The maximum value of the range.
 * @param int $decimals The number of decimal places for the generated value.
 * @return float|int The randomly generated floating-point number, or an integer if decimals is 0 or less.
 */
function getRandomFloat($min, $max, $decimals) {
    if ($max < $min) {
        return 0;
    }
    if ($decimals <= 0) {
        // Return a random integer if decimals is 0 or less
        return mt_rand($min, $max);
    }
    $scale = pow(10, $decimals);
    $randomInt = mt_rand($min * $scale, $max * $scale);
    return $randomInt / $scale;
}
