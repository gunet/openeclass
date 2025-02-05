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


/**
 * @brief display question
 * @param $objQuestionTmp
 * @param array $exerciseResult
 * @param $question_number
 * @return int
 */
function showQuestion(&$objQuestionTmp, $question_number, $exerciseResult = [], $options = []) {

    global $tool_content, $picturePath, $langNoAnswer, $langQuestion, $langSelect,
            $langColumnA, $langColumnB, $langMakeCorrespond, $langInfoGrades,
            $exerciseType, $nbrQuestions, $langInfoGrade, $langHasAnswered, $langClear, $langSelect;

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
    $tool_content .= "
            <div class='card panelCard px-lg-4 py-lg-3 qPanel panelCard-exercise mt-4' id='qPanel$questionId'>
              <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3 class='mb-0 d-flex justify-content-start align-items-center gap-2 flex-wrap'>$langQuestion $qNumber
                    <small>($questionTypeWord &mdash; $questionWeight $message)</small>&nbsp;
                    <span title='$langHasAnswered' id='qCheck$question_number'></span>
                </h3>
            </div>
            <div class='panel-body'>
                <h4 class='mb-2'>" . q_math($questionName) . "</h4>
                <div class='mb-2'>$questionDescription</div>
                <div class='text-center'>" .
                    (file_exists($picturePath . '/quiz-' . $questionId) ?
                        "<img src='../../$picturePath/quiz-$questionId'>" : "") . "
                </div>";

    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    if ($answerType == FREE_TEXT) {
            $text = (isset($exerciseResult[$questionId])) ? $exerciseResult[$questionId] : '';
            $tool_content .= rich_text_editor("choice[$questionId]", 14, 90, $text, options: $options);
    }
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER ||$answerType == TRUE_FALSE) {
         $tool_content .= "<input type='hidden' name='choice[$questionId]' value='0' />";
    }
    // only used for the answer type "Matching"
    if ($answerType == MATCHING && $nbrAnswers>0) {
        $cpt1 = 'A';
        $cpt2 = 1;
        $Select = array();
        $tool_content .= "<div class='table-responsive'><table class='table-default'>
                            <thead><tr class='list-header'>
                              <th>$langColumnA</th>
                              <th>$langMakeCorrespond</th>
                              <th>$langColumnB</th>
                            </tr></thead>";
    }

    if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        $tool_content .= "<div class='form-inline' style='line-height:2.2;'>";
    }

    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        if (is_null($answer) or $answer == '') {  // don't display blank or empty answers
            continue;
        }
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        // fill in blanks
        if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            // splits text and weightings that are joined with the character '::'
            list($answer) = Question::blanksSplitAnswer($answer);
            // replaces [blank] by an input field
            $replace_callback = function () use ($questionId, $exerciseResult, $question_number) {
                    static $id = 0;
                    $id++;
                    $value = (isset($exerciseResult[$questionId][$id])) ? ('value = "'.q($exerciseResult[$questionId][$id]) .'"') : '';
                    return "<input class='form-control mb-4' type='text' style='line-height:normal;' name='choice[$questionId][$id]' $value onChange='questionUpdateListener(". $question_number . ",". $questionId .");'>";
            };
            $answer = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer));
            $tool_content .= $answer;
        }
        // fill in with selected words
        elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
            $temp_string = unserialize($answer);
            $answer_string = $temp_string[0];
            // replaces [choices] with `select` field
            $replace_callback = function ($blank) use ($questionId, $exerciseResult, $question_number, $langSelect) {
                static $id = 0;
                $id++;
                $selection_text = explode("|", str_replace(array('[',']'), ' ', q($blank[0])));
                array_unshift($selection_text, "--- $langSelect ---");
                $value = (isset($exerciseResult[$questionId][$id])) ? ($exerciseResult[$questionId][$id]) : '';
                return selection($selection_text, "choice[$questionId][$id]", $value,"class='form-select' onChange='questionUpdateListener($question_number, $questionId)'");
            };
            $answer_string = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer_string));
            $tool_content .= $answer_string;
        }
        // unique answer
        elseif ($answerType == UNIQUE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] == $answerId) ? 'checked="checked"' : '';
            $tool_content .= "
                        <div class='radio mb-1'>
                          <label>
                            <input type='radio' name='choice[$questionId]' value='$answerId' $checked onClick='updateQuestionNavButton(". $question_number . ");'>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
        // multiple answers
        elseif ($answerType == MULTIPLE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == 1) ? 'checked="checked"' : '';
            $tool_content .= "
                        <div class='checkbox mb-1'>
                            <label class='label-container' aria-label='$langSelect'>
                                <input type='checkbox' name='choice[$questionId][$answerId]' value='1' $checked onClick='updateQuestionNavButton(". $question_number . ");'>
                                <span class='checkmark'></span>
                                " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
        // matching
        elseif ($answerType == MATCHING) {
            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $Select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown on the right side
                $Select[$answerId]['Reponse'] = $answer;
            } else {
                $tool_content .= "<tr>
                                  <td><strong>$cpt2.</strong> " . q($answer) . "</td>
                                  <td><div class='text-start'>
                                   <select class='form-select w-50' name='choice[$questionId][$answerId]' onChange='questionUpdateListener($question_number, $questionId);'>
                                     <option value='0'>--</option>";

                // fills the list-box
                foreach ($Select as $key => $val) {
                    $selected = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == $key) ? 'selected="selected"' : '';
                    $tool_content .= "<option value=\"" . q($key) . "\" $selected>{$val['Lettre']}</option>";
                }
                $tool_content .= "</select></div></td><td>";
                if (isset($Select[$cpt2])) {
                    $tool_content .= '<strong>' . q($Select[$cpt2]['Lettre']) . '.</strong> ' . q($Select[$cpt2]['Reponse']);
                } else {
                    $tool_content .= '&nbsp;';
                }
                $tool_content .= "</td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown on the right side
                    while (isset($Select[$cpt2])) {
                            $tool_content .= "<tr class='even'>
                                              <td>&nbsp;</td>
                                              <td>&nbsp;</td>
                                              <td>" . "<strong>" . q($Select[$cpt2]['Lettre']) . ".</strong> " . q($Select[$cpt2]['Reponse']) . "</td>
                                          </tr>";
                        $cpt2++;
                    } // end while()
                }  // end if()
            }
        } elseif ($answerType == TRUE_FALSE) {
            $checked = (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] == $answerId) ? 'checked="checked"' : '';
            $tool_content .= "
                        <div class='radio mb-1'>
                          <label>
                            <input type='radio' name='choice[$questionId]' value='$answerId' $checked onClick='updateQuestionNavButton($question_number);'>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
    } // end for()
    if ($answerType == MATCHING && $nbrAnswers>0) {
        $tool_content .= "</table></div>";
    }
    if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        $tool_content .= "</div>";
    }
    if (!$nbrAnswers && $answerType != FREE_TEXT) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langNoAnswer</span></div></div>";
    }
    if (in_array($answerType, [TRUE_FALSE, UNIQUE_ANSWER])) {
        $tool_content .= "<button class='float-end clearSelect btn deleteAdminBtn' style='margin-top:0px;'><i class='fa-solid fa-xmark'></i> $langClear</button>";
    }
    $tool_content .= "
                </div>
            </div>";
    // destruction of the Answer object
    unset($objAnswerTmp);
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

    return $nbrAnswers;
}


/**
 * @brief exercise teacher view
 * @param type $exercise_id
 */
function display_exercise($exercise_id): void
{

    global $tool_content, $head_content, $is_editor, $langQuestion, $picturePath, $langChoice, $langCorrespondsTo,
           $langAnswer, $langComment, $langQuestionScore, $langTotalScore, $langQuestionsManagement,
           $langScore, $course_code, $langBack, $langModify, $langExerciseExecute, $langFrom2, $action_bar,
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

            if (file_exists($picturePath . '/quiz-' . $qid)) {
                $tool_content .= "<tr><td colspan='$colspan'><img src='../../$picturePath/quiz-" . $qid . "'></td></tr>";
            }

            if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                $tool_content .= "
                <tr>
                  <td colspan='2'><strong>$langAnswer</strong></td>
                  <td><strong>$langComment</strong></td>
                </tr>";
            } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                $tool_content .= "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";
            } elseif ($answerType == MATCHING) {
                $tool_content .= "
                <tr>
                  <td><strong>$langChoice</strong></td>
                  <td><strong>$langCorrespondsTo</strong></td>
                </tr>";
            }

            if ($answerType != FREE_TEXT) {
                $answer = new Answer($qid);
                $nbrAnswers = $answer->selectNbrAnswers();

                for ($answer_id = 1; $answer_id <= $nbrAnswers; $answer_id++) {
                    $answerTitle = $answer->selectAnswer($answer_id);
                    $answerComment = standard_text_escape($answer->selectComment($answer_id));
                    $answerCorrect = $answer->isCorrect($answer_id);
                    $answerWeighting = $answer->selectWeighting($answer_id);

                    if ($answerType == FILL_IN_BLANKS or $answerType == FILL_IN_BLANKS_TOLERANT) {
                        list($answerTitle, $answerWeighting) = Question::blanksSplitAnswer($answerTitle);
                    } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                        if (!empty($answerTitle)) {
                            $answer_array = unserialize($answerTitle);
                            $answer_text = $answer_array[0]; // answer text
                            $correct_answer = $answer_array[1]; // correct answer
                            $answer_weight = implode(' : ', $answer_array[2]); // answer weight
                        } else {
                            break;
                        }
                    } elseif ($answerType == MATCHING) {
                        $answerTitle = q($answerTitle);
                    } else {
                        $answerTitle = standard_text_escape($answerTitle);
                    }
                    if ($answerType != MATCHING || $answerCorrect) {
                        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                            $tool_content .= "<tr><td style='width: 70px;'><div align='start'>";
                            if ($answerCorrect) {
                                $icon_choice = "fa-regular fa-square-check";
                            } else {
                                $icon_choice = "fa-regular fa-square";
                            }
                            $tool_content .= icon($icon_choice) . "</div>";
                            $tool_content .= "</td><td>" . standard_text_escape($answerTitle) . " <strong><small>($langScore: $answerWeighting)</small></strong></td>
                                               <td style='width: 30%;'>" . $answerComment . "</td>
                                        </tr>";
                        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                            $tool_content .= "<tr><td>" . standard_text_escape(nl2br($answerTitle)) . " <strong><small>($langScore: " . preg_replace('/,/', ' : ', "$answerWeighting") . ")</small></strong>
                                          </td></tr>";
                        } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                            $possible_answers = [];
                            // fetch all possible answers
                            preg_match_all('/\[[^]]+\]/', $answer_text, $out);
                            foreach ($out[0] as $output) {
                                $possible_answers[] = explode("|", str_replace(array('[',']'), '', q($output)));
                            }
                            // find correct answers
                            foreach ($possible_answers as $possible_answer_key => $possible_answer) {
                               $possible_answer = reindex_array_keys_from_one($possible_answer);
                               $correct_answer_string[] = '['. $possible_answer[$correct_answer[$possible_answer_key]] . ']';
                            }

                            $formatted_answer_text = preg_replace_callback($correct_answer_string,
                                    function ($string) {
                                        return "<span style='color: red;'>$string[0]</span>";
                                    },
                                standard_text_escape(nl2br($answer_text)));
                            // format correct answers
                            $tool_content .= "<tr><td>" . $formatted_answer_text;
                            $tool_content .= "&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answer_weight)</small></strong>";
                            $tool_content .= "</td></tr>";
                        } else {
                            $tool_content .= "<tr><td>" . standard_text_escape($answerTitle) . "</td>";
                            $tool_content .= "<td>" . $answer->answer[$answerCorrect] . "&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answerWeighting)</small></strong></td>";
                            $tool_content .= "</tr>";
                        }
                    }
                }
            }
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
