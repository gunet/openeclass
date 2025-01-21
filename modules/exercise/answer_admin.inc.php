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


$questionName = $objQuestion->selectTitle();
$answerType = $objQuestion->selectType();
$questionId = $objQuestion->selectId();
$questionTypeWord = $objQuestion->selectTypeLegend($answerType);
$questionDescription = standard_text_escape($objQuestion->selectDescription());
$okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;

$newAnswer = $deleteAnswer = false;

$htopic = 0;
if (isset($_GET['htopic'])) { //new question
    $htopic = $_GET['htopic'];
}
if (isset($_POST['submitAnswers'])) {
    $submitAnswers = $_POST['submitAnswers'];
}
if (isset($_POST['buttonBack'])) {
    $buttonBack = $_POST['buttonBack'];
}
if (isset($_POST['nbrAnswers'])) {
    $nbrAnswers = intval($_POST['nbrAnswers']);
}
if (isset($_POST['lessAnswers'])) {
    $deleteAnswer = true;
}
if (isset($_POST['moreAnswers'])) {
    $newAnswer = true;
}

// the answer form has been submitted
if (isset($submitAnswers) || isset($buttonBack)) {

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
                $weighting[$i] = abs(fix_float($weighting[$i]));
                // calculates the sum of answer weighting
                if ($weighting[$i]) {
                    $questionWeighting += $weighting[$i];
                }
            } else {
                // a bad answer can't have a positive weighting
                $weighting[$i] = -abs(fix_float($weighting[$i]));
            }

            // check if field is empty
            if (!isset($reponse[$i]) || ($reponse[$i] === '')) {
                $msgErr = $langGiveAnswers;
                break;
            } else {
                // add answer into object
                $objAnswer->createAnswer(purify($reponse[$i]), $goodAnswer, purify($comment[$i]), $weighting[$i], $i);
            }
        }

        if (empty($msgErr)) {
            if (!$nbrGoodAnswers) {
                $msgErr = ($answerType == UNIQUE_ANSWER) ? $langChooseGoodAnswer : $langChooseGoodAnswers;
            } else {
                // save the answers into the data base
                $objAnswer->save();
                // set the total weighting of the question
                $objQuestion->updateWeighting($questionWeighting);
                if (isset($exerciseId)) {
                    $objQuestion->save($exerciseId);
                } else {
                    $objQuestion->save();
                }
            }
        }

    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        $reponse = trim($_POST['reponse']);
        if (isset($_POST['weighting']) and isset($_POST['blanksDefined'])) {
            // a blank can't have a negative weighting
            $weighting = array_map('fix_float', $_POST['weighting']);
            $weighting = array_map('abs', $weighting);
            if ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                $questionWeighting = array_sum($weighting);
                $answer_array = [$_POST['reponse'], $_POST['correct_selected_word'], $weighting];
                $answer = serialize($answer_array);
                $objAnswer->createAnswer($answer, 0, '', 0, 1);
            } else {
                // separate text and weightings by '::'
                $reponse .= '::' . implode(',', $weighting);
                $questionWeighting = array_sum($weighting);
                $objAnswer->createAnswer($reponse, 0, '', 0, 1);
            }
            // update db
            $objAnswer->save();
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            } else {
                $objQuestion->save();
            }
            $blanksDefined = true;
        }
        if (isset($buttonBack) or isset($blanksDefined)) {
            Session::flash('message',$langQuestionUpdated);
            Session::flash('alert-class', 'alert-success');

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
        } else { // now we're going to give a weighting to each blank
                $displayBlanks = true;
                unset($submitAnswers);
            if ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                preg_match_all('/\[[^]]+\]/', $_POST['reponse'], $out);
                foreach ($out[0] as $output) {
                    $blanks[] = explode("|", str_replace(array('[',']'), '', q($output)));
                }
            } else {
                $blanks = Question::getBlanks($_POST['reponse']);
            }
        }
    } elseif ($answerType == MATCHING) {

        function check_empty($item) {
            $item = trim($item);
            return $item !== '';
        }
        if (isset($_POST['match'])) { // check for blank matches
            if ($_POST['match'] != array_filter($_POST['match'],'check_empty')) {
                Session::flash('message',$langGiveAnswers);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
            }
        }

        if (isset($_POST['option'])) { // check for blank options
            if ($_POST['option'] != array_filter($_POST['option'], 'check_empty')) {
                Session::flash('message',$langGiveAnswers);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
            }
        }

        // walk through $_POST['options'] and $_POST['match'] arrays and
        // create corresponding Answer objects
        $questionWeighting = 0;
        for ($i = 1; $i <= count($_POST['option']); $i++) {
            $objAnswer->createAnswer(trim($_POST['option'][$i]), '', '', '', $i);
        }
        foreach ($_POST['match'] as $j => $match) {
            $weighting = abs(fix_float($_POST['weighting'][$j]));
            $objAnswer->createAnswer(trim($match), $_POST['sel'][$j], '', $weighting, $i);
            $questionWeighting += $weighting;
            $i++;
        }

        // save object answer into database
        $objAnswer->save();
        // update object question
        $objQuestion->updateWeighting($questionWeighting);
        if (isset($exerciseId)) {
            $objQuestion->save($exerciseId);
        } else {
            $objQuestion->save();
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
            } else {
                // saves the answers into the data base
                $objAnswer->save();
                // sets the total weighting of the question
                $objQuestion->updateWeighting($questionWeighting);
                if (isset($exerciseId)) {
                    $objQuestion->save($exerciseId);
                } else {
                    $objQuestion->save();
                }
            }
        }
    }
    if (empty($msgErr) and !isset($_POST['setWeighting'])) {
        if (isset($exerciseId)) {
            Session::flash('message',$langQuestionReused);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
        } else {
            redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code");
        }
    }
}

if (isset($_GET['modifyAnswers'])) {
    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
        if ($newAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] + 1;
        } else {
            $nbrAnswers = $objAnswer->selectNbrAnswers();
        }
        if ($deleteAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] - 1;
            if ($nbrAnswers < 2) { // minimum 2 answers
               $nbrAnswers = 2;
            }
        }

        $reponse = array();
        $comment = array();
        $weighting = array();

        // initializing
        if ($answerType == MULTIPLE_ANSWER) {
            $correct = array();
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

    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        if (!isset($submitAnswers) && !isset($buttonBack)) {
            if (!(isset($_POST['setWeighting']) and $_POST['setWeighting'])) {
                $reponse = $objAnswer->selectAnswer(1);
                if ($reponse) {
                    list($reponse, $weighting) = explode('::', $reponse);
                    $weighting = explode(',', $weighting);
                } else {
                    $reponse = '';
                    $weighting = [];
                }
            } else {
                $weighting = explode(',', $_POST['str_weighting']);
            }
        }
    }  elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        if (!isset($submitAnswers) && !isset($buttonBack)) {
            if (!(isset($_POST['setWeighting']) and $_POST['setWeighting'])) {
                $answer = $objAnswer->selectAnswer(1);
                if ($answer) {
                    $answer_array = unserialize($answer);
                    $reponse = $answer_array[0]; // answer text
                    $correct_answer = $answer_array[1]; // correct answer
                    $weighting = $answer_array[2]; // answer weight
                } else {
                    $reponse = '';
                    $weighting = [];
                }
            } else {
                $weighting = explode(',', $_POST['str_weighting']);
            }
        }

    } elseif ($answerType == MATCHING) {

        $option = $match = $sel = array();
        if (isset($_POST['option'])) {
            $option = $_POST['option'];
        }
        if (isset($_POST['match'])) {
           $match = $_POST['match'];
        }
        if (isset($_POST['sel'])) {
            $sel = $_POST['sel'];
        }
        if (isset($_POST['weighting'])) {
            $weighting = fix_float($_POST['weighting']);
        }
        if ($objAnswer->selectNbrAnswers() == 2) { // new matching question
            $nbrOptions = $nbrMatches = 2; // default options
                // option
            for ($k = 1; $k <= $nbrOptions; $k++) {
                $objAnswer->createAnswer(${"langDefaultMatchingOpt$k"}, 0, '', 0, $k, true);
                // match
                $objAnswer->createAnswer(${"langDefaultMakeCorrespond$k"}, $k, '', 1, $k + $nbrMatches, true);
            }
        } else { // question exists
            $nbrOptions = $nbrMatches = 0;
            // fills arrays from data base
            for ($i = 1; $i <= $objAnswer->selectNbrAnswers(); $i++) {
                // it is a match
                if ($objAnswer->isCorrect($i)) {
                    $match[$i] = $objAnswer->selectAnswer($i);
                    $sel[$i] = $objAnswer->isCorrect($i);
                    $weighting[$i] = $objAnswer->selectWeighting($i);
                    $nbrMatches++;
                } else { // it is an option
                    $option[$i] = $objAnswer->selectAnswer($i);
                    $nbrOptions++;
                }
            }
            if (isset($_POST['nbrOptions'])) {
                $nbrOptions = $_POST['nbrOptions'];
            }
            if (isset($_POST['nbrMatches'])) {
                $nbrMatches = $_POST['nbrMatches'];
            }
        }

        if (isset($_POST['lessOptions'])) {
            $nbrOptions = $_POST['nbrOptions']-1;
            if ($nbrOptions < 2) {
                $nbrOptions = 2;
            }
        }

        if (isset($_POST['moreOptions'])) {
            $nbrOptions++;
        }

        if (isset($_POST['lessMatches'])) {
            $nbrMatches = $_POST['nbrMatches']-1;
            // minimum 2 matches
            if ($nbrMatches < 2) {
                $nbrMatches = 2;
            }
        }

        if (isset($_POST['moreMatches'])) {
            $nbrMatches++;
        }


    } elseif ($answerType == TRUE_FALSE) {
        if (!isset($nbrAnswers)) {
            $nbrAnswers = $objAnswer->selectNbrAnswers();
            $reponse = array();
            $comment = array();
            $weighting = array();
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
    }
    $tool_content .= "<div class='col-12'><div class='card panelCard card-default px-lg-4 py-lg-3'>
                      <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3>$langQuestion &nbsp;" .
                            icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . "?course=$course_code".(isset($exerciseId) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . $questionId)."
                        </h3>
                      </div>
                      <div class='card-body'>
                            <h5>$questionTypeWord<br>" . nl2br(q_math($questionName)) . "</h5>
                                <p>$questionDescription</p>
                                ".(($okPicture)? "<div class='text-center'><img src='../../$picturePath/quiz-$questionId'></div>":"")."
                      </div>
                    </div></div>";

   if ($answerType != FREE_TEXT) {

        $tool_content .= "<div class='col-12 mt-4'><div class='card panelCard card-default px-lg-4 py-lg-3'>
                           <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                             <h3>$langQuestionAnswers";
                             if ($answerType == MULTIPLE_ANSWER) {
                                 $tool_content .= "<br><small class='msmall-text'>$langNegativeScoreLegend</small>";
                             }

        $tool_content .= "   </h3>
                           </div>
                       <div class='card-body'>";

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
            if (!empty($msgErr)) {
                $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$msgErr</span></div>";
            }

            $tool_content .= "
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                <input type='hidden' name='formSent' value='1'>
                <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                <div class='table-responsive'>
                <table class='table table-striped table-hover table-default'>";
            $tool_content .= "<tr>
                          <th aria-label='$langTotal'></th>
                          <th>$langCorrect</th>
                          <th>$langAnswer</th>
                          <th>$langComment</th>
                          <th>$langGradebookGrade</th>
                        </tr>";

            for ($i = 1; $i <= $nbrAnswers; $i++) {
                $tool_content .="<tr><td valign='top'>$i.</td>";
                if ($answerType == UNIQUE_ANSWER) {
                    $tool_content .= "<td><input type='radio' value=\"" . $i . "\" name='correct' ";
                    if ((isset($correct) and $correct == $i) or (isset($_POST['correct']) and ($_POST['correct'] == $i))) {
                        $tool_content .= "checked='checked'></td>";
                    } else {
                        $tool_content .= "></td>";
                    }
                } else {
                    $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' value='1' name=\"correct[" . $i . "]\" ";
                    if ((isset($correct[$i]) && ($correct[$i]) or (isset($_POST['correct'][$i]) and $_POST['correct'][$i]))) {
                        $tool_content .= "checked='checked'><span class='checkmark'></span></label></td>";
                    } else {
                        $tool_content .= " /><span class='checkmark'></span></label></td>";
                    }
                }

                if (isset($_POST['weighting'][$i])) {
                    $thisWeighting = $_POST['weighting'][$i];
                } else if (isset($weighting[$i])) {
                    $thisWeighting = $weighting[$i];
                } else {
                    $thisWeighting = 0;
                }

                if (isset($_POST['reponse'][$i])) {
                    $tool_content .= "<td style='width:42%'>" . rich_text_editor("reponse[$i]", 7, 40, $_POST['reponse'][$i], true) . "</td>";
                } else {
                    $tool_content .= "<td style='width:42%'>" . rich_text_editor("reponse[$i]", 7, 40, $reponse[$i], true) . "</td>";
                }
                if (isset($_POST['comment'][$i])) {
                    $tool_content .= "<td style='width:42%'>" . rich_text_editor("comment[$i]", 7, 40, $_POST['comment'][$i], true) . "</td>";
                } else {
                    $tool_content .= "<td style='width:42%'>" . rich_text_editor("comment[$i]", 7, 40, $comment[$i], true) . "</td>";
                }
                $tool_content .= "<td><input class='form-control' type='text' name='weighting[$i]' value='$thisWeighting'></td></tr>";
            }
            $tool_content .= "<tr>
                    <td class='text-start' colspan='3'><strong>$langPollAddAnswer :</strong>
                        <div class='d-flex gap-2 flex-wrap mt-2'>
                            <input type='submit' name='moreAnswers' value='$langMoreAnswers' />
                            <input type='submit' name='lessAnswers' value='$langLessAnswers' />
                        </div>
                    </td>
                    <td colspan='2'>&nbsp;</td>
                  </tr>
                </table></div>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
             $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
             $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>";
             $tempSW = isset($_POST['setWeighting']) ? $_POST['setWeighting'] : '';
             $tool_content .= "
                   <input type='hidden' name='formSent' value='1' />
                   <input type='hidden' name='setWeighting' value='$tempSW'>";
             if ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                 $legend = $langUseTagForSelectedWords;
                 $defaultText = $langDefaultMissingWords;
             } else {
                 $legend = $langUseTagForBlank;
                 $defaultText = $langDefaultTextInBlanks;
             }
             if (!isset($displayBlanks)) {
                 $str_weighting = isset($weighting)? implode(',', $weighting): '';
                 $tool_content .= "<input type='hidden' name='str_weighting' value='$str_weighting'>
                   <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                     <table class='table table-default'>
                       <tr>
                         <td>$langTypeTextBelow, $langAnd $legend :<br/><br/>
                           <textarea class='form-control' name='reponse' cols='70' rows='6'>";
                 if (!isset($submitAnswers) && empty($reponse)) {
                     $tool_content .= $defaultText;
                 } else {
                     $tool_content .= q($reponse);
                 }
                 $tool_content .= "</textarea></td></tr>";
                 // if there is an error message
                 if (!empty($msgErr)) {
                     $tool_content .= "<div class='alert alert-danger text-center'>$msgErr</div>";
                 }
                 $tool_content .= "</table>";
             } else {
                 $tool_content .= "
                     <input type='hidden' name='blanksDefined' value='true'>
                     <input type='hidden' name='reponse' value='" . q($_POST['reponse']) . "'>";
                 // if there is an error message
                 if (!empty($msgErr)) {
                     $tool_content .= "
                                 <table class='table-default' cellpadding='3' align='center' width='400'>
                                 <tr><td class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$msgErr</span></td></tr>
                                 </table>";
                 } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                     $tool_content .= "<tr><td>$langWeightingForEachBlankandChoose</td></tr>
                                     <table class='table table-default'>";
                     foreach ($blanks as $i => $blank) {
                         $blank = reindex_array_keys_from_one($blank);
                         if (!empty($correct_answer)) {
                             $default_selection = $correct_answer[$i];
                         } else {
                             $default_selection = '';
                         }
                         $tool_content .= "<tr>
                                            <td class='text-end'>" . selection($blank, "correct_selected_word[".$i."]", $default_selection,'class="form-control"') . "</td>
                                            <td><input class='form-control' type='text' name='weighting[".($i)."]' value='" . (isset($weighting[$i]) ? $weighting[$i] : 0) . "'></td>
                                         </tr>";
                     }
                     $tool_content .= "</table>";
                 } else {
                     $tool_content .= "<tr><td>$langWeightingForEachBlank</td></tr>
                                     <table class='table table-default'>";
                     foreach ($blanks as $i => $blank) {
                         $tool_content .= "<tr>
                                            <td class='text-end'><strong>[" . q($blank) . "] :</strong></td>" . "
                                            <td><input class='form-control' type='text' name='weighting[".($i)."]' value='" . (isset($weighting[$i]) ? $weighting[$i] : 0) . "'></td>
                                         </tr>";
                     }
                     $tool_content .= "</table>";
                 }
             }
        } elseif ($answerType == MATCHING) {

         if (!empty($msgErr)) {
             $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$msgErr</span></div>";
         }

         $tool_content .= "
         <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
             <input type='hidden' name='formSent' value='1'>
             <input type='hidden' name='nbrOptions' value='$nbrOptions'>
             <input type='hidden' name='nbrMatches' value='$nbrMatches'>
             <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
             <table class='table table-default'>";
         $optionsList = array();
         // create an array with the option letters
         for ($i = 1, $j = 'A'; $i <= $nbrOptions; $i++, $j++) {
             $optionsList[$i] = $j;
         }

         $tool_content .= "<tr><td colspan='2'><b>$langDefineOptions</b></td>
               <td colspan='2'><b>$langMakeCorrespond</b></td>
                 </tr>
                 <tr>
               <td>&nbsp;</td>
               <td>
                    <strong>$langColumnA:</strong> 
                    <span style='valign:middle;'>$langMoreLessChoices:</span> 
                    <div class='d-flex gap-2 mt-2 flex-wrap'>
                        <input type='submit' name='moreMatches' value='+' />
                        <input type='submit' name='lessMatches' value='-' />
                    </div>
                </td>
               <td><div align='text-end'><strong>$langColumnB</strong></div></td>
               <td>$langGradebookGrade</td>
             </tr>";
         $i = $objAnswer->getFirstMatchingPosition();
         for ($j = 1; $j <= $nbrMatches; $i++, $j++) {
             if (isset($_POST['match'][$i])) {
                 $optionText = $_POST['match'][$i];
             } elseif (isset($match[$i])) {
                 $optionText = $match[$i];
             } elseif (!count($match)) {
                 $optionText = ${'langDefaultMakeCorrespond' . $j}; // Default example option
             } else {
                 $optionText = '';
             }
             $optionWeight = isset($weighting[$i])? q($weighting[$i]): 1;

             $tool_content .= "<tr>
               <td class='text-end'><strong>$j</strong></td>
               <td><input class='form-control' type='text' name='match[$i]' value='" . q($optionText) . "'></td>
               <td><div class='text-end'><select class='form-select' name='sel[$i]'>";
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
           <td class='text-end'>&nbsp;</td>
           <td colspan='3'>&nbsp;</td>
         </tr>
         <tr>
           <td>&nbsp;</td>
           <td colspan='1'>
                <b>$langColumnB:</b> 
                <span style='valign:middle'>$langMoreLessChoices:</span> 
                <div class='d-flex gap-2 flex-wrap mt-2'>
                    <input type='submit' name='moreOptions' value='+' />
                    <input type='submit' name='lessOptions' value='-' />
                </div>
           </td>
           <td>&nbsp;</td>
         </tr>";

         foreach ($optionsList as $key => $val) {
             $tool_content .= "<tr>
                       <td class='text-end'><strong>" . q($val) . "</strong></td>
                       <td><input class='form-control' type='text' " .
                     "name=\"option[" . $key . "]\" size='58' value=\"";
             if (isset($_POST['option'][$key])) {
                 $tool_content .= htmlspecialchars($_POST['option'][$key]);
             } elseif (isset($option[$key])) {
                 $tool_content .= htmlspecialchars($option[$key]);
             } elseif (($val == 'A') or ($val == 'B')) { // default option
                 $valNum = ($val == 'A')? 1: 2;
                 $tool_content .= ${"langDefaultMatchingOpt$valNum"};
             } else {
                 $tool_content .= '';
             }

             $tool_content .= "\" /></td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                   </tr>";
         }
         $tool_content .= "</table>";
     } elseif ($answerType == TRUE_FALSE) {
         $tool_content .= "
                     <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                     <input type='hidden' name='formSent' value='1'>
                     <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                     <fieldset><legend class='mb-0' aria-label='$langForm'></legend>";
         // if there is an error message
         if (!empty($msgErr)) {
             $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$msgErr</span></div>";
         }
         $setChecked[1] = (isset($correct) and $correct == 1) ? " checked='checked'" : '';
         $setChecked[2] = (isset($correct) and $correct == 2) ? " checked='checked'" : '';
         $setWeighting[1] = isset($weighting[1]) ? q($weighting[1]) : 0;
         $setWeighting[2] = isset($weighting[2]) ? q($weighting[2]) : 0;
         $tool_content .= "
             <input type='hidden' name='reponse[1]' value='$langCorrect'>
             <input type='hidden' name='reponse[2]' value='$langFalse'>
             <table class='table table-default'>
             <tr>
               <td style='width: 10%;' colspan='2'><strong>$langAnswer</strong></td>
               <td><strong>$langComment</strong></td>
               <td style='width: 15%;'><strong>$langGradebookGrade</strong></td>
             </tr>
             <tr>
               <td style='width: 10%;'>$langTrue</td>
               <td><input type='radio' value='1' name='correct'$setChecked[1]></td>
               <td>"  . rich_text_editor('comment[1]', 4, 30, @$comment[1], true) . "</td>
               <td style='width: 15%'><input class='form-control' type='text' name='weighting[1]' value='$setWeighting[1]'></td>
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
     $submit_text = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) && !isset($setWeighting) ? "$langNext &gt;" : $langSubmit;
     $back_button = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) && isset($setWeighting) ? "<input class='btn submitAdminBtn' type='submit' name='buttonBack' value='&lt; $langBack'' />" : "";

     $tool_content .= "
                     <div class='row'>
                         <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                             $back_button
                             <input class='btn submitAdminBtn' type='submit' name='submitAnswers' value='$submit_text'>
                             <a class='btn cancelAdminBtn' href='$cancel_link'>$langCancel</a>
                         </div>
                     </div>
                </fieldset>
            </form>
         </div>
     </div></div>";
   }
}
