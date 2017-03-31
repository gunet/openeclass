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
            <div class='panel panel-success'>
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
        $tool_content .= "
                      <table class='table-default'>
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
                $tool_content .= "
				    <tr>
				      <td><b>${cpt2}.</b> " . standard_text_escape($answer) . "</td>
				      <td><div align='left'>
				       <select name='choice[${questionId}][${answerId}]'>
					 <option value='0'>--</option>";

                // fills the list-box
                foreach ($Select as $key => $val) {
                $selected = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == $key) ? 'selected="selected"' : '';
                    $tool_content .= "
					<option value=\"" . q($key) . "\" $selected>${val['Lettre']}</option>";
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
                                              <td>" . "<b>" . q($Select[$cpt2]['Lettre']) . ".</b> " . q($Select[$cpt2]['Reponse']) . "</td>                                                
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
