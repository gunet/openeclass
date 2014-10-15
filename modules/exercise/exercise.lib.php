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

function showQuestion($questionId, $onlyAnswers = false, $exerciseResult = array()) {
    global $tool_content, $picturePath, $langNoAnswer,
    $langColumnA, $langColumnB, $langMakeCorrespond;

//    print_a($exerciseResult);
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
        $questionDescription_temp = $questionDescription;
        $tool_content .= "
                  <tr class='even'>
                    <td colspan='2'>
		<b>" . q($questionName) . "</b><br />
		$questionDescription_temp
                </td>
              </tr>";
        if (file_exists($picturePath . '/quiz-' . $questionId)) {
            $tool_content .= "
                  <tr class='even'>
                    <td class='center' colspan='2'><img src='../../$picturePath/quiz-$questionId'></td>
                  </tr>";
        }
    }  // end if(!$onlyAnswers)
    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    // only used for the answer type "Matching"
    if ($answerType == MATCHING) {
        $cpt1 = 'A';
        $cpt2 = 1;
        $Select = array();
        $tool_content .= "
                  <tr class='even'>
                    <td colspan='2'>
                      <table class='tbl_border' width='100%'>
                      <tr>
                        <th width='200'>$langColumnA</th>
                        <th width='100'>$langMakeCorrespond</th>
                        <th width='200'>$langColumnB</th>
                      </tr>
                      </table>
                    </td>
                  </tr>";
    }
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER ||$answerType == TRUE_FALSE) {
         $tool_content .= "<input type='hidden' name='choice[${questionId}]' value='0' />";
    }
    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answer = mathfilter($answer, 12, '../../courses/mathimg/');
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        if ($answerType == FILL_IN_BLANKS) {
            // splits text and weightings that are joined with the character '::'
            list($answer) = explode('::', $answer);
            // replaces [blank] by an input field
            $replace_callback = function () use ($questionId, $exerciseResult) {
                    static $id = 0;
                    $id++;
                    $value = (isset($exerciseResult[$questionId][$id])) ? 'value = '.$exerciseResult[$questionId][$id] : '';
                    return "<input type='text' name='choice[$questionId][$id]' size='10' $value>";
            };
            $answer = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape(($answer)));
        }
        // unique answer
        if ($answerType == UNIQUE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] == $answerId) ? 'checked="checked"' : '';
            $tool_content .= "
			<tr class='even'>
			  <td class='center' width='1'>
			    <input type='radio' name='choice[${questionId}]' value='${answerId}' $checked />
			  </td>
			  <td>" . standard_text_escape($answer) . "</td>
			</tr>";
        }
        // multiple answers
        elseif ($answerType == MULTIPLE_ANSWER) {
            $checked = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == 1) ? 'checked="checked"' : '';
            $tool_content .= "
			<tr class='even'>
			  <td width='1' align='center'>
			    <input type='checkbox' name='choice[${questionId}][${answerId}]' value='1' $checked />
			  </td>
			  <td>" . standard_text_escape($answer) . "</td>
			</tr>";
        }
        // fill in blanks
        elseif ($answerType == FILL_IN_BLANKS) {
            $tool_content .= "
			<tr class='even'>
			  <td colspan='2'>" . $answer . "</td>
			</tr>";
        }
        // matching
        elseif ($answerType == MATCHING) {
            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $Select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown at the right side
                $Select[$answerId]['Reponse'] = standard_text_escape($answer);
            } else {
                $tool_content .= "<tr class='even'>
				  <td colspan='2'>
				    <table class='tbl' width='100%'>
				    <tr>
				      <td width='200'><b>${cpt2}.</b> " . standard_text_escape($answer) . "</td>
				      <td width='100'><div align='left'>
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

                $tool_content .= "</td></tr></table></td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown at the right side
                    while (isset($Select[$cpt2])) {
                        $tool_content .= "
                                              <tr class='even'>
                                                <td colspan='2'>
                                                  <table width='100%'>
                                                  <tr>
                                                  <td width='200'>&nbsp;</td>
                                                  <td width='100'>&nbsp;</td>
                                                  <td width='200' valign='top'>" .
                                "<b>" . q($Select[$cpt2]['Lettre']) . ".</b> " . q($Select[$cpt2]['Reponse']) . "
                                                  </td>
                                                  </tr>
                                                  </table>
                                                </td>
                                              </tr>";
                        $cpt2++;
                    } // end while()
                }  // end if()
            }
            // $tool_content .= " </table>";
        } elseif ($answerType == TRUE_FALSE) {
            $checked = (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] == $answerId) ? 'checked="checked"' : '';
            $tool_content .= "
                          <tr class='even'>
                            <td width='1' align='center'>
                              <input type='radio' name='choice[${questionId}]' value='${answerId}' $checked />
                            </td>
                            <td>" . standard_text_escape($answer) . "</td>
                          </tr>";
        }
    } // end for()
    if ($answerType == FREE_TEXT) {
            $text = (isset($exerciseResult[$questionId])) ? $exerciseResult[$questionId] : '';
            $tool_content .= "
                          <tr class='even'>
                            <td align='center'>".  rich_text_editor('choice['.$questionId.']', 14, 90, $text, '')."</td></tr>";            
    }   
    if (!$nbrAnswers && $answerType != FREE_TEXT) {
        $tool_content .= "
                  <tr>
                    <td colspan='2'><div class='alert alert-danger'>$langNoAnswer</div></td>
                  </tr>";
    }
    // destruction of the Answer object
    unset($objAnswerTmp);
    // destruction of the Question object
    unset($objQuestionTmp);
    return $nbrAnswers;
}
