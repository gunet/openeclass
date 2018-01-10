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

/**
 * @brief display question
 * @global type $tool_content
 * @global type $picturePath
 * @global type $langNoAnswer
 * @global type $langQuestion
 * @global type $langColumnA
 * @global type $langColumnB
 * @global type $langMakeCorrespond
 * @global type $langInfoGrades
 * @global type $i
 * @global type $exerciseType
 * @global type $nbrQuestions
 * @global type $langInfoGrade
 * @param type $objQuestionTmp
 * @param type $exerciseResult
 * @return type
 */
function showQuestion(&$objQuestionTmp, $exerciseResult = array()) {
    global $tool_content, $picturePath, $langNoAnswer, $langQuestion,
    $langColumnA, $langColumnB, $langMakeCorrespond, $langInfoGrades, $i,
    $exerciseType, $nbrQuestions, $langInfoGrade;

    $questionId = $objQuestionTmp->id;
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
    $questionTypeWord = $objQuestionTmp->selectTypeWord($answerType);
    $tool_content .= "
            <div class='panel panel-success qPanel' id='qPanel$questionId'>
              <div class='panel-heading'>
                <h3 class='panel-title'>$langQuestion : $i ($questionWeight $message)".(($exerciseType == 2) ? " / " . $nbrQuestions : "")."</h3>
              </div>
              <div class='panel-body'>
                    <h4>
                        <small>$questionTypeWord</small><br>" . q_math($questionName) . "
                    </h4>
                    $questionDescription
                    <div class='text-center'>
                        ".(file_exists($picturePath . '/quiz-' . $questionId) ? "<img src='../../$picturePath/quiz-$questionId'>" : "")."
                    </div>";

    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    if ($answerType == FREE_TEXT) {
            $text = (isset($exerciseResult[$questionId])) ? $exerciseResult[$questionId] : '';
            $tool_content .= rich_text_editor('choice['.$questionId.']', 14, 90, $text);
    }
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER ||$answerType == TRUE_FALSE) {
         $tool_content .= "<input type='hidden' name='choice[${questionId}]' value='0' />";
    }
    // only used for the answer type "Matching"
    if ($answerType == MATCHING && $nbrAnswers>0) {
        $cpt1 = 'A';
        $cpt2 = 1;
        $Select = array();
        $tool_content .= "<table class='table-default'>
                            <tr>
                              <th>$langColumnA</th>
                              <th>$langMakeCorrespond</th>
                              <th>$langColumnB</th>
                            </tr>";
    }

    if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        $tool_content .= "<div class='form-inline' style='line-height:2.2;'>";
    }

    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        if (is_null($answer) or $answer == '') {  // don't display blank or empty answers
            continue;
        }
        $answer = mathfilter($answer, 12, '../../courses/mathimg/');
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            // splits text and weightings that are joined with the character '::'
            list($answer) = Question::blanksSplitAnswer($answer);
            // replaces [blank] by an input field
            $replace_callback = function () use ($questionId, $exerciseResult) {
                    static $id = 0;
                    $id++;
                    $value = (isset($exerciseResult[$questionId][$id])) ? 'value = '.$exerciseResult[$questionId][$id] : '';
                    return "<input type='text' style='line-height:normal;' name='choice[$questionId][$id]' $value>";
            };
            $answer = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer));
        }
        // unique answer
        if ($answerType == UNIQUE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] == $answerId) ? 'checked="checked"' : '';
            $tool_content .= "
                        <div class='radio'>
                          <label>
                            <input type='radio' name='choice[${questionId}]' value='${answerId}' $checked>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
        // multiple answers
        elseif ($answerType == MULTIPLE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == 1) ? 'checked="checked"' : '';
            $tool_content .= "
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='choice[${questionId}][${answerId}]' value='1' $checked>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
        // fill in blanks
        elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            $tool_content .= $answer;
        }
        // matching
        elseif ($answerType == MATCHING) {
            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $Select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown at the right side
                $Select[$answerId]['Reponse'] = standard_text_escape($answer);
            } else {
                $tool_content .= "<tr>
                                  <td><b>${cpt2}.</b> " . standard_text_escape($answer) . "</td>
                                  <td><div align='left'>
                                   <select name='choice[${questionId}][${answerId}]'>
                                     <option value='0'>--</option>";

                // fills the list-box
                foreach ($Select as $key => $val) {
                    $selected = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == $key) ? 'selected="selected"' : '';
                    $tool_content .= "<option value=\"" . q($key) . "\" $selected>${val['Lettre']}</option>";
                }
                $tool_content .= "</select></div></td><td width='200'>";
                if (isset($Select[$cpt2])) {
                    $tool_content .= '<b>' . q($Select[$cpt2]['Lettre']) . '.</b> ' . $Select[$cpt2]['Reponse'];
                } else {
                    $tool_content .= '&nbsp;';
                }
                $tool_content .= "</td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown at the right side
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
                        <div class='radio'>
                          <label>
                            <input type='radio' name='choice[${questionId}]' value='${answerId}' $checked>
                            " . standard_text_escape($answer) . "
                          </label>
                        </div>";
        }
    } // end for()
    if ($answerType == MATCHING && $nbrAnswers>0) {
        $tool_content .= "</table>";
    }
    if ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        $tool_content .= "</div>";
    }
    if (!$nbrAnswers && $answerType != FREE_TEXT) {
        $tool_content .= "<div class='alert alert-danger'>$langNoAnswer</div>";
    }
    $tool_content .= "
                </div>
            </div>";
    // destruction of the Answer object
    unset($objAnswerTmp);
    // destruction of the Question object
    unset($objQuestionTmp);

    return $nbrAnswers;
}


/**
 * @brief exercise teacher view
 * @global type $tool_content
 * @global type $course_code
 * @global type $langBack
 * @global type $langQuestion
 * @global type $picturePath
 * @global type $langAnswer
 * @global type $langComment
 * @global type $langQuestionScore
 * @global type $langYourTotalScore
 * @global type $langScore
 * @global type $langChoice
 * @global type $langCorrespondsTo
 * @param type $exercise_id
 */
function display_exercise($exercise_id) {

    global $tool_content, $langQuestion, $picturePath, $langChoice, $langCorrespondsTo,
           $langAnswer, $langComment, $langQuestionScore, $langYourTotalScore,
           $langScore, $course_code, $langBack;

    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "index.php?course=$course_code",
              'icon' => 'fa-reply',
              'level' => 'primary-label'
        )
    ));

    $exercise = new Exercise();
    $exercise->read($exercise_id);

    $tool_content .= "<div class='panel panel-primary'>
            <div class='panel-heading'>
              <h3 class='panel-title'>" . q_math($exercise->selectTitle()) . "</h3>
            </div>
            <div class='panel-body'>" . $exercise->selectDescription() . "</div>
        </div>";

    $question_list = $exercise->selectQuestionList();
    $totalWeighting = $exercise->selectTotalWeighting();
    $i = 0;
    foreach ($question_list as $qid) {
        $i++;
        $question = new Question();
        $question->read($qid);
        $questionName = $question->selectTitle();
        $questionDescription = $question->selectDescription();
        $questionWeighting = $question->selectWeighting();
        $answerType = $question->selectType();

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            $colspan = 3;
        } elseif ($answerType == MATCHING) {
            $colspan = 2;
        } else {
            $colspan = 1;
        }

        $tool_content .= "
            <table class = 'table-default'>
            <tr class='active'>
              <td colspan='$colspan'><strong><u>$langQuestion</u>: $i</strong></td>
            </tr>
            <tr>
              <td colspan='$colspan'>";
        $tool_content .= "
            <strong>" . q_math($questionName) . "</strong>
            <br>" . standard_text_escape($questionDescription) . "<br><br>
            </td></tr>";

        if (file_exists($picturePath . '/quiz-' . $qid)) {
            $tool_content .= "<tr><td class='text-center' colspan='$colspan'><img src='../../$picturePath/quiz-" . $qid . "'></td></tr>";
        }

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
            $tool_content .= "
                <tr>
                  <td colspan='2'><strong>$langAnswer</strong></td>
                  <td><strong>$langComment</strong></td>
                </tr>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
            $tool_content .= "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";
        } elseif ($answerType == MATCHING) {
            $tool_content .= "
                <tr>
                  <td><b>$langChoice</b></td>
                  <td><b>$langCorrespondsTo</b></td>
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
                } else {
                    $answerTitle = standard_text_escape($answerTitle);
                }

                if ($answerType != MATCHING || $answerCorrect) {
                    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                        $tool_content .= "<tr><td style='width: 70px;'><div align='center'>";
                        if ($answerCorrect) {
                            $icon_choice= "fa-check-square-o";
                        } else {
                            $icon_choice = "fa-square-o";
                        }
                        $tool_content .= icon($icon_choice)."</div>";
                        $tool_content .= "</td><td>" . standard_text_escape($answerTitle) . " <strong><small>($langScore: $answerWeighting)</small></strong></td>
                                               <td style='width: 200px;'>" . $answerComment . "</td>
                                        </tr>";
                    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                        $tool_content .= "<tr><td>" . standard_text_escape(nl2br($answerTitle)) . " <strong><small>($langScore: $answerWeighting)</small></strong></td></tr>";
                    } else {
                        $tool_content .= "<tr><td>" . standard_text_escape($answerTitle) . "</td>";
                        $tool_content .= "<td>" . $answer->answer[$answerCorrect] . "&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answerWeighting)</small></strong></td>";
                        $tool_content .= "</tr>";
                    }
                }
            }
        }
        $tool_content .= "<tr class='active'><th colspan='$colspan'>";
        $tool_content .= "<span style='float:right;'>$langQuestionScore: <strong>" . round($questionWeighting, 2) . "</strong></span>";
        $tool_content .= "</th></tr>";
        $tool_content .= "</table>";

        unset($answer);
    }
    $tool_content .= "<br>
            <table class='table-default'>
            <tr><td class='text-right'><strong>$langYourTotalScore: $totalWeighting</strong></td></tr>
            </table>";
}
