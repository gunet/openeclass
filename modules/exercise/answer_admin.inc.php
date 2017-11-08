<?php

/* ========================================================================
 * Open eClass 3.7
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


$questionName = $objQuestion->selectTitle();
$answerType = $objQuestion->selectType();
$questionId = $objQuestion->selectId();
$questionTypeWord = $objQuestion->selectTypeWord($answerType);

$okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
if (isset($_POST['submitAnswers'])) {
    $submitAnswers = $_POST['submitAnswers'];
}
if (isset($_POST['buttonBack'])) {
    $buttonBack = $_POST['buttonBack'];
}

function fix_float($str) {
    return str_replace(',', '.', $str);
}

// the answer form has been submitted
if (isset($submitAnswers) || isset($buttonBack)) {
    if (isset($_POST['nbrAnswers'])) {
        $nbrAnswers = intval($_POST['nbrAnswers']);
    }
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
        $questionWeighting = $nbrGoodAnswers = 0;

        for ($i = 1; $i <= $nbrAnswers; $i++) {
            $reponse[$i] = trim($_POST['reponse'][$i]);
            $comment[$i] = trim($_POST['comment'][$i]);
            $weighting[$i] = fix_float($_POST['weighting'][$i]);

            if ($answerType == UNIQUE_ANSWER) {
                $goodAnswer = @($_POST['correct'] == $i) ? 1 : 0;
            } else {
                $goodAnswer = @($_POST['correct'][$i]) ? 1 : 0;
            }
            if ($goodAnswer) {
                $nbrGoodAnswers++;
                // a good answer can't have a negative weighting
                $weighting[$i] = abs($weighting[$i]);
                // calculates the sum of answer weighting
                if ($weighting[$i]) {
                    $questionWeighting += $weighting[$i];
                }
            } else {
                // a bad answer can't have a positive weighting
                $weighting[$i] = -abs($weighting[$i]);
            }

            // check if field is empty
            if (!isset($reponse[$i]) || ($reponse[$i] === '')) {
                $msgErr = $langGiveAnswers;
                // clears answers already recorded into the Answer object
                $objAnswer->cancel();
                break;
            } else {
                // add answer into object
                $objAnswer->createAnswer(purify($reponse[$i]), $goodAnswer, purify($comment[$i]), $weighting[$i], $i);
            }
        }

        if (empty($msgErr)) {
            if (!$nbrGoodAnswers) {
                $msgErr = ($answerType == UNIQUE_ANSWER) ? $langChooseGoodAnswer : $langChooseGoodAnswers;
                // clears answers already recorded into the Answer object
                $objAnswer->cancel();
            } else {
                // save the answers into the data base
                $objAnswer->save();
                // set the total weighting of the question
                $objQuestion->updateWeighting($questionWeighting);
                $objQuestion->save($exerciseId);
                $editQuestion = $questionId;
            }
        }
    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        $reponse = trim($_POST['reponse']);
        if (isset($_POST['weighting']) and isset($_POST['blanksDefined'])) {
            // a blank can't have a negative weighting
            $weighting = array_map('abs', $_POST['weighting']);
            $weighting = array_map('fix_float', $weighting);
            // separate text and weightings by '::'
            $reponse .= '::' . implode(',', $weighting);
            $questionWeighting = array_sum($weighting);
            $objAnswer->createAnswer($reponse, 0, '', 0, 0);
            $objAnswer->save();
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            }
            $editQuestion = $questionId;
            $blanksDefined = true;
        }

        if (isset($buttonBack) or isset($blanksDefined)) {
            if (isset($exerciseId)) {
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
            } else {
                redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code");
            }
        }

        if (empty($reponse)) {
            // if no text has been typed or the text contains no blank
            $msgErr = $langGiveText;
        } elseif (!preg_match('/\[.+\]/', $reponse)) {
            $msgErr = $langDefineBlanks;
        } else {
            // now we're going to give a weighting to each blank
            $displayBlanks = true;
            unset($submitAnswers);
            $blanks = Question::getBlanks($_POST['reponse']);
        }
    } elseif ($answerType == MATCHING) {
        for ($i = 1; $i <= $_POST['nbrOptions']; $i++) {
            $option[$i] = trim($_POST['option'][$i]);
            // checks if field is empty
            if (empty($option[$i])) {
                $msgErr = $langFillLists;
                // clears options already recorded into the Answer object
                $objAnswer->cancel();
                break;
            } else {
                // adds the option into the object
                $objAnswer->createAnswer($option[$i], 0, '', 0, $i);
            }
        }
        $questionWeighting = 0;
        if (empty($msgErr)) {
            for ($j = 1; $j <= $_POST['nbrMatches']; $i++, $j++) {
                $match[$i] = trim($_POST['match'][$i]);
                $weighting[$i] = abs(fix_float($_POST['weighting'][$i]));
                $sel[$i] = intval($_POST['sel'][$i]);
                $questionWeighting += $weighting[$i];
                // check if field is empty
                if (empty($match[$i])) {
                    $msgErr = $langFillLists;
                    // clears matches already recorded into the Answer object
                    $objAnswer->cancel();
                    break;
                } else {
                    // add the answer into the object
                    $objAnswer->createAnswer($match[$i], $sel[$i], '', $weighting[$i], $i);
                }
            }
        }
        if (empty($msgErr)) {
            // all answers have been recorded, so we save them into the data base
            $objAnswer->save();
            // sets the total weighting of the question
            $objQuestion->updateWeighting($questionWeighting);
            $objQuestion->save($exerciseId);
            $editQuestion = $questionId;
        }
    } elseif ($answerType == TRUE_FALSE) {
        $questionWeighting = $nbrGoodAnswers = 0;
        for ($i = 1; $i <= $nbrAnswers; $i++) {
            $comment[$i] = trim($_POST['comment'][$i]);
            $goodAnswer = (isset($_POST['correct']) && $_POST['correct'] == $i) ? 1 : 0;

            if ($goodAnswer) {
                $nbrGoodAnswers++;
                // a good answer can't have a negative weighting
                $weighting[$i] = abs(fix_float($_POST['weighting'][$i]));
                // calculates the sum of answer weighting
                if ($weighting[$i]) {
                    $questionWeighting += $weighting[$i];
                }
            } else {
                // a bad answer can't have a positive weighting
                $weighting[$i] = -abs(fix_float($_POST['weighting'][$i]));
            }
            // checks if field is empty
            if (!isset($_POST['reponse'][$i]) || ($_POST['reponse'][$i] === '')) {
                $msgErr = $langGiveAnswers;
                // clears answers already recorded into the Answer object
                $objAnswer->cancel();
                break;
            } else {
                // adds the answer into the object
                $reponse[$i] = purify(trim($_POST['reponse'][$i]));
                $objAnswer->createAnswer($reponse[$i], $goodAnswer, purify($comment[$i]), $weighting[$i], $i);
            }
        }
        if (empty($msgErr)) {
            if (!$nbrGoodAnswers) {
                $msgErr = ($answerType == TRUE_FALSE) ? $langChooseGoodAnswer : $langChooseGoodAnswers;
                // clears answers already recorded into the Answer object
                $objAnswer->cancel();
            } else {
                // saves the answers into the data base
                $objAnswer->save();
                // sets the total weighting of the question
                $objQuestion->updateWeighting($questionWeighting);
                $objQuestion->save($exerciseId);
                $editQuestion = $questionId;
            }
        }
    }
    if (empty($msgErr) and !isset($_POST['setWeighting'])) {
        if (isset($exerciseId)) {
            Session::Messages($langQuestionReused, 'alert-success');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
        } else {
            redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code");
        }
    }
}
if (isset($_GET['modifyAnswers'])) {
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
        if (!isset($nbrAnswers)) {
            $nbrAnswers = $objAnswer->selectNbrAnswers();
            $reponse = Array();
            $comment = Array();
            $weighting = Array();

            // initializing
            if ($answerType == MULTIPLE_ANSWER) {
                $correct = Array();
            } else {
                $correct = 0;
            }
            for ($i = 1; $i <= $nbrAnswers; $i++) {
                $reponse[$i] = $objAnswer->selectAnswer($i);
                $comment[$i] = $objAnswer->selectComment($i);
                $weighting[$i] = $objAnswer->selectWeighting($i);

                if ($answerType == MULTIPLE_ANSWER) {
                    $correct[$i] = $objAnswer->isCorrect($i);
                } elseif ($objAnswer->isCorrect($i)) {
                    $correct = $i;
                }
            }
        }
        if (isset($_POST['lessAnswers'])) {
            $nbrAnswers--;
        }
        if (isset($_POST['moreAnswers'])) {                      
            $nbrAnswers++;
        }        
        // minimum 2 answers
        if ($nbrAnswers < 2) {
            $nbrAnswers = 2;
        }
    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        if (!isset($submitAnswers) && !isset($buttonBack)) {
            if (!(isset($_POST['setWeighting']) and $_POST['setWeighting'])) {
                $reponse = $objAnswer->selectAnswer(1);
                list($reponse, $weighting) = explode('::', $reponse);
                $weighting = explode(',', $weighting);
            } else {
                $weighting = explode(',', $_POST['str_weighting']);
            }
        }
    } elseif ($answerType == MATCHING) {
        if (!isset($_POST['nbrOptions']) or !isset($_POST['nbrMatches'])) {
            $option = Array();
            $match = Array();
            $sel = Array();
            $nbrOptions = $nbrMatches = 0;
            // fills arrays with data from data base
            for ($i = 1; $i <= $objAnswer->selectNbrAnswers(); $i++) {
                // it is a match
                if ($objAnswer->isCorrect($i)) {
                    $match[$i] = $objAnswer->selectAnswer($i);
                    $sel[$i] = $objAnswer->isCorrect($i);
                    $weighting[$i] = $objAnswer->selectWeighting($i);
                    $nbrMatches++;
                }
                // it is an option
                else {
                    $option[$i] = $objAnswer->selectAnswer($i);
                    $nbrOptions++;
                }
            }
        } else {
            $nbrOptions = intval($_POST['nbrOptions']);
            $nbrMatches = intval($_POST['nbrMatches']);
            $option = $_POST['option'];
            $match = $_POST['match'];
            $sel = $_POST['sel'];
            $weighting = fix_float($_POST['weighting']);
        }

        if (isset($_POST['lessOptions'])) {
            // keeps the correct sequence of array keys when removing an option from the list
            for ($i = $nbrOptions + 1, $j = 1; $nbrOptions > 2 && $j <= $nbrMatches; $i++, $j++) {
                $match[$i - 1] = $match[$i];
                $sel[$i - 1] = $sel[$i];
                $weighting[$i - 1] = $weighting[$i];
            }

            unset($match[$i - 1]);
            unset($sel[$i - 1]);

            $nbrOptions--;
        }

        if (isset($_POST['moreOptions'])) {
            // keeps the correct sequence of array keys when adding an option into the list
            for ($i = $nbrMatches + $nbrOptions; $i > $nbrOptions; $i--) {
                $match[$i + 1] = $match[$i];
                $sel[$i + 1] = $sel[$i];
                $weighting[$i + 1] = $weighting[$i];
            }

            unset($match[$i + 1]);
            unset($sel[$i + 1]);

            $nbrOptions++;
        }

        if (isset($_POST['lessMatches'])) {
            $nbrMatches--;
        }

        if (isset($_POST['moreMatches'])) {
            $nbrMatches++;
        }

        // minimum 2 options
        if ($nbrOptions < 2) {
            $nbrOptions = 2;
        }

        // minimum 2 matches
        if ($nbrMatches < 2) {
            $nbrMatches = 2;
        }
    } elseif ($answerType == TRUE_FALSE) {
        if (!isset($nbrAnswers)) {
            $nbrAnswers = $objAnswer->selectNbrAnswers();
            //$nbrAnswers = 2;
            $reponse = Array();
            $comment = Array();
            $weighting = Array();
            $correct = 0;
            for ($i = 1; $i <= $nbrAnswers; $i++) {
                $reponse[$i] = $objAnswer->selectAnswer($i);
                $comment[$i] = $objAnswer->selectComment($i);
                $weighting[$i] = $objAnswer->selectWeighting($i);
                if ($objAnswer->isCorrect($i)) {
                    $correct = $i;
                }
            }
        }
        // minimum 2 answers
        if ($nbrAnswers < 2) {
            $nbrAnswers = 2;
        }
    }
    $tool_content .= "<div class='panel panel-primary'>
                      <div class='panel-heading'>
                        <h3 class='panel-title'>$langQuestion</h3>
                      </div>
                      <div class='panel-body'>
                            <h4><small>$questionTypeWord</small><br>" . nl2br(q_math($questionName)) . "</h4>
                      </div>
                    </div>";
   $tool_content .= "<div class='panel panel-info'>
                      <div class='panel-heading'>
                        <h3 class='panel-title'>$langQuestionAnswers</h3>
                      </div>
                      <div class='panel-body'>";

    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {        
        $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
            <input type='hidden' name='formSent' value='1' />
            <input type='hidden' name='nbrAnswers' value='$nbrAnswers' />
            <fieldset>
            <table class='table table-striped table-hover'>";
        // if there is an error message
        if (!empty($msgErr)) {
            $tool_content .= "<tr><td colspan='5'><div class='alert alert-danger'>$msgErr</div></td></tr>";
        }
        $tool_content .= "
                    <tr>
                      <th class='text-right'></th>
                      <th class='text-center'>$langTrue</th>
                      <th class='text-center'>$langAnswer</th>
                      <th class='text-center'>$langComment</th>
                      <th class='text-center'>$langQuestionWeighting</th>
                    </tr>";
        for ($i = 1; $i <= $nbrAnswers; $i++) {
            $tool_content .="<tr><td class='text-right' valign='top'>$i.</td>";
            if ($answerType == UNIQUE_ANSWER) {
                $tool_content .= "<td class='text-center'><input type='radio' value=\"" . $i . "\" name=\"correct\" ";
                if (isset($correct) and $correct == $i) {
                    $tool_content .= "checked=\"checked\" /></td>";
                } else {
                    $tool_content .= "></td>";
                }
            } else {
                $tool_content .= "<td class='text-center'><input type='checkbox' value=\"1\" name=\"correct[" . $i . "]\" ";
                if ((isset($correct[$i])) && ($correct[$i])) {
                    $tool_content .= "checked=\"checked\"></td>";
                } else {
                    $tool_content .= " /></td>";
                }
            }

            $thisWeighting = isset($weighting[$i]) ? $weighting[$i] : 0;
            $tool_content .= "
                <td style='width:42%'>" . rich_text_editor("reponse[$i]", 7, 40, @$reponse[$i], true) . "</td>
                <td style='width:42%'>" . rich_text_editor("comment[$i]", 7, 40, @$comment[$i], true) . "</td>
                <td class='text-center'><input class='form-control' type='text' name='weighting[$i]' value='$thisWeighting'></td></tr>";
        }
        $tool_content .= "<tr>
                <td class='text-left' colspan='3'><strong>$langSurveyAddAnswer :</strong>&nbsp;
                    <input type='submit' name='moreAnswers' value='$langMoreAnswers' />&nbsp;
                  <input type='submit' name='lessAnswers' value='$langLessAnswers' />                  
                </td>
                <td colspan='2'>&nbsp;</td>
              </tr>
            </table>";
    }

    elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
        $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>";
        $tempSW = isset($_POST['setWeighting']) ? $_POST['setWeighting'] : '';
        $tool_content .= "
              <input type='hidden' name='formSent' value='1' />\n
              <input type='hidden' name='setWeighting' value='$tempSW'>\n";
        if (!isset($displayBlanks)) {
            $str_weighting = isset($weighting)? implode(',', $weighting): '';
            $tool_content .= "
              <input type='hidden' name='str_weighting' value='$str_weighting'>
              <fieldset>
                <table class='table'>
                  <tr>
                    <td>$langTypeTextBelow, $langAnd $langUseTagForBlank :<br/><br/>
                      <textarea class='form-control' name='reponse' cols='70' rows='6'>";
            if (!isset($submitAnswers) && empty($reponse)) {
                $tool_content .= $langDefaultTextInBlanks;
            } else {
                $tool_content .= q($reponse);
            }
            $tool_content .= "</textarea></td></tr>";
            // if there is an error message
            if (!empty($msgErr)) {
                $tool_content .= "
                  <tr>
                    <td>
                      <table border='0' cellpadding='3' align='center' width='400' bgcolor='#FFCC00'>
                        <tr><td>$msgErr</td></tr>
                      </table>
                    </td>
                  </tr>";
            }
            $tool_content .= "</table>";
        } else {
            $tool_content .= "
                <input type='hidden' name='blanksDefined' value='true'>
                <input type='hidden' name='reponse' value='" . q($_POST['reponse']) . "'>";
            // if there is an error message
            if (!empty($msgErr)) {
                $tool_content .= "
                            <table border='0' cellpadding='3' align='center' width='400'>
                            <tr><td class='alert alert-danger'>$msgErr</td></tr>
                            </table>";
            } else {
                $tool_content .= "
                                <fieldset>
                                <tr>
                                    <td>$langWeightingForEachBlank</td>
                                </tr>
                                <table class='table'>";
                foreach ($blanks as $i => $blank) {
                    $tool_content .= "<tr>
                                    <td class='text-right'><b>[" . q($blank) . "] :</b></td>" . "
                                        <td><input class='form-control' type='text' name='weighting[".($i)."]' value='" . (isset($weighting[$i]) ? $weighting[$i] : 0) . "'></td>
                                    </tr>";
                }
                $tool_content .= "</table>";
            }
        }
    }

    elseif ($answerType == MATCHING) {
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
            <input type='hidden' name='formSent' value='1'>
            <input type='hidden' name='nbrOptions' value='$nbrOptions'>
            <input type='hidden' name='nbrMatches' value='$nbrMatches'>
            <fieldset>
                <table class='table'>";

        // if there is an error message
        if (!empty($msgErr)) {
            $tool_content .= "<tr>
              <td colspan='4'>
                <table class='table'>
                <tr>
                  <td>$msgErr</td>
                </tr>
                </table>
              </td>
            </tr>";
        }
        $optionsList = Array();
        // create an array with the option letters
        for ($i = 1, $j = 'A'; $i <= $nbrOptions; $i++, $j++) {
            $optionsList[$i] = $j;
        }

        $tool_content .= "<tr><td colspan='2'><b>$langDefineOptions</b></td>
              <td class='text-center' colspan='2'><b>$langMakeCorrespond</b></td>
                </tr>
                <tr>
              <td>&nbsp;</td>
              <td><b>$langColumnA:</b> <span style='valign:middle;'>$langMoreLessChoices:</span> <input type='submit' name='moreMatches' value='+' />&nbsp;
              <input type='submit' name='lessMatches' value='-' /></td>
              <td><div align='text-right'>$langColumnB</div></td>
              <td>$langQuestionWeighting</td>
            </tr>";

        for ($j = 1; $j <= $nbrMatches; $i++, $j++) {
            if (!count($match)) {
                $optionText = ${'langDefaultMakeCorrespond' . $j}; // Default example option
            } elseif (isset($match[$i])) {
                $optionText = str_replace('{', '&#123;', htmlspecialchars($match[$i]));
            } else {
                $optionText = '';
            }
            $optionWeight = isset($weighting[$i])? q($weighting[$i]): 1;

            $tool_content .= "
            <tr>
              <td class='text-right'><b>$j</b></td>
              <td><input class='form-control' type='text' name='match[$i]' value='$optionText'></td>
              <td><div class='text-right'><select class='form-control' name='sel[$i]'>";
            foreach ($optionsList as $key => $val) {
                $tool_content .= "<option value='" . q($key) . "'";
                if ((!isset($submitAnswers) && !isset($sel[$i]) && $j == 2 && $val == 'B') || @$sel[$i] == $key) {
                    $tool_content .= " selected='selected'";
                }
                $tool_content .= ">" . q($val) . "</option>";
            }
            $tool_content .= "</select></div></td>
              <td><input class='form-control' type='text' name='weighting[$i]' value='$optionWeight'></td>
            </tr>";
        }

        $tool_content .= "
        <tr>
          <td class='text-right'>&nbsp;</td>
          <td colspan='3'>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td colspan='1'><b>$langColumnB:</b> <span style='valign:middle'>$langMoreLessChoices:</span> <input type='submit' name='moreOptions' value='+' />
          &nbsp;<input type='submit' name='lessOptions' value='-' />
          </td>
          <td>&nbsp;</td>
        </tr>";

        foreach ($optionsList as $key => $val) {
            $tool_content .= "<tr>
                      <td class='text-right'><b>" . q($val) . "</b></td>
                      <td><input class='form-control' type='text' " .
                    "name=\"option[" . $key . "]\" size='58' value=\"";
            if (!isset($_POST['formSent']) && !isset($_POST['option'][$key])) {
                $tool_content .= ${"langDefaultMatchingOpt$val"};
            } else {
                @$tool_content .= str_replace('{', '&#123;', htmlspecialchars($_POST['option'][$key]));
            }

            $tool_content .= "\" /></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>";
        }
        $tool_content .= "</table>";
    }

    elseif ($answerType == TRUE_FALSE) {
        $tool_content .= "
                    <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                    <input type='hidden' name='formSent' value='1'>
                    <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                    <fieldset>";
        // if there is an error message
        if (!empty($msgErr)) {
            $tool_content .= "<div class='alert alert-danger'>$msgErr</div>";
        }
        $setChecked[1] = (isset($correct) and $correct == 1) ? " checked='checked'" : '';
        $setChecked[2] = (isset($correct) and $correct == 2) ? " checked='checked'" : '';
        $setWeighting[1] = isset($weighting[1]) ? q($weighting[1]) : 0;
        $setWeighting[2] = isset($weighting[2]) ? q($weighting[2]) : 0;
        $tool_content .= "
            <input type='hidden' name='reponse[1]' value='$langCorrect'>
            <input type='hidden' name='reponse[2]' value='$langFalse'>
            <table class='table'>
            <tr>
              <td colspan='2'><b>$langAnswer</b></td>
              <td class='text-center'><b>$langComment</b></td>
              <td class='text-center'><b>$langQuestionWeighting</b></td>
            </tr>
            <tr>
              <td valign='top' width='30'>$langCorrect</td>
              <td valign='top' width='1'><input type='radio' value='1' name='correct'$setChecked[1]></td>
              <td>" . rich_text_editor('comment[1]', 4, 30, @$comment[1], true) . "</td>
              <td><input class='form-control' type='text' name='weighting[1]' value='$setWeighting[1]'></td>
            </tr>
            <tr>
              <td>$langFalse</td>
              <td><input type='radio' value='2' name='correct'$setChecked[2]></td>
              <td>" . rich_text_editor("comment[2]", 4, 40, @$comment[2]) . "</td>
              <td><input class='form-control' type='text' name='weighting[2]' size='5' value='$setWeighting[2]'></td>
            </tr>
          </table>";
    }

    $cancel_link = isset($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "question_pool.php?course=$course_code";
    $submit_text = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) && !isset($setWeighting) ? "$langNext &gt;" : $langCreate;
    $back_button = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) && isset($setWeighting) ? "<input class='btn btn-primary' type='submit' name='buttonBack' value='&lt; $langBack'' />" : "";
    $tool_content .= "
                    <div class='row'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            $back_button
                            <input class='btn btn-primary' type='submit' name='submitAnswers' value='$submit_text'>
                            <a class='btn btn-default' href='$cancel_link'>$langCancel</a>
                        </div>
                    </div>
               </fieldset>
           </form>
        </div>
    </div>";
}
