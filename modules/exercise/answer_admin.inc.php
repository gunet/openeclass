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


$questionName = $objQuestion->selectTitle();
$answerType = $objQuestion->selectType();
$questionId = $objQuestion->selectId();
$questionTypeWord = $objQuestion->selectTypeWord($answerType);
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

function fix_float($str) {
    return str_replace(',', '.', $str);
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
                $objQuestion->save($exerciseId);                
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
            $objAnswer->createAnswer($reponse, 0, '', 0, 1);
            $objAnswer->save();
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            }            
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
        
        if (isset($_POST['match'])) { // check for blank matches
            if ($_POST['match'] != array_filter($_POST['match'])) {
                Session::Messages($langGiveAnswers, 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");    
            }
        }

        if (isset($_POST['option'])) { // check for blank options
            if ($_POST['option'] != array_filter($_POST['option'])) {
                Session::Messages($langGiveAnswers, 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
            }
        }
                                
        for ($i = 1; $i <= $_POST['nbrOptions']; $i++) {
            $option[$i] = trim($_POST['option'][$i]);
        }
        
        $data_sel = $data_weighting = array();
        $questionWeighting = 0;
        // merge arrays $_POST['options'] + $_POST['match']
        $temp_data = array_merge($option, $_POST['match']);
        for ($k = 0; $k < count($temp_data); $k++) {
            // start keys of previous array from index 1
            $data[$k+1] = $temp_data[$k];
            if (in_array($temp_data[$k], $_POST['match'])) {
                $index = key($_POST['match']);
                // update keys of array $_POST['sel']
                $data_sel[$k+1] = $_POST['sel'][$index];
                // update keys of array $_POST['weighting']
                $data_weighting[$k+1] = abs(fix_float($_POST['weighting'][$index]));
                next($_POST['match']);
                $questionWeighting += $data_weighting[$k+1];
            } else {
                $data_sel[$k+1] = $data_weighting[$k+1] = '';
            }
        }

        // update object Answer with new data
        for ($k = 1; $k <= count($data); $k++) {
            $objAnswer->createAnswer($data[$k], $data_sel[$k], '', $data_weighting[$k], $k);
        }
        
        // save object answer into database
        $objAnswer->save();
        // update object question
        $objQuestion->updateWeighting($questionWeighting);
        $objQuestion->save($exerciseId);        
        
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
                $weighting[$i] = abs(fix_float($_POST['weighting'][$i]));
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
                $objQuestion->save($exerciseId);                
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
        
        if (($htopic == 2) or ($htopic == 1)) {
            $nbrAnswers = 2; // default            
        } elseif ($newAnswer) {
            $nbrAnswers = $_POST['nbrAnswers']+1;
        } else {
            $nbrAnswers = $objAnswer->selectNbrAnswers();
        }
        if ($deleteAnswer) {
            $nbrAnswers = $_POST['nbrAnswers']-1;            
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
                list($reponse, $weighting) = explode('::', $reponse);                
                $weighting = explode(',', $weighting);
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
        
        if ($htopic == 4) { // new matching question
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
    $tool_content .= "<div class='panel panel-primary'>
                      <div class='panel-heading'>                            
                        <h3 class='panel-title'>$langQuestion &nbsp;" . 
                            icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . "?course=$course_code".(isset($exerciseId) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . $questionId)."</h3>
                      </div>      
                      <div class='panel-body'>
                            <h4><small>$questionTypeWord</small><br>" . nl2br(q_math($questionName)) . "</h4>
                                <p>$questionDescription</p>
                                ".(($okPicture)? "<div class='text-center'><img src='../../$picturePath/quiz-$questionId'></div>":"")."
                      </div>
                    </div>";
    
   if ($answerType != FREE_TEXT) {
    
        $tool_content .= "<div class='panel panel-info'>
                       <div class='panel-heading'>
                         <h3 class='panel-title'>$langQuestionAnswers</h3>
                       </div>
                       <div class='panel-body'>";

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {                
            if (!empty($msgErr)) {
                $tool_content .= "<div class='alert alert-danger'>$msgErr</div>";
            }

            $tool_content .= "
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                <input type='hidden' name='formSent' value='1'>
                <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                <fieldset>
                <table class='table table-striped table-hover'>";
            $tool_content .= "<tr>
                          <th class='text-right'></th>
                          <th class='text-center'>$langTrue</th>
                          <th class='text-center'>$langAnswer</th>
                          <th class='text-center'>$langComment</th>
                          <th class='text-center'>$langScore</th>
                        </tr>";

            for ($i = 1; $i <= $nbrAnswers; $i++) {
                $tool_content .="<tr><td class='text-right' valign='top'>$i.</td>";
                if ($answerType == UNIQUE_ANSWER) {
                    $tool_content .= "<td class='text-center'><input type='radio' value=\"" . $i . "\" name='correct' ";
                    if ((isset($correct) and $correct == $i) or (isset($_POST['correct']) and ($_POST['correct'] == $i))) {
                        $tool_content .= "checked='checked'></td>";
                    } else {
                        $tool_content .= "></td>";
                    }
                } else {                
                    $tool_content .= "<td class='text-center'><input type='checkbox' value='1' name=\"correct[" . $i . "]\" ";
                    if ((isset($correct[$i]) && ($correct[$i]) or (isset($_POST['correct'][$i]) and $_POST['correct'][$i]))) {
                        $tool_content .= "checked='checked'></td>";
                    } else {
                        $tool_content .= " /></td>";
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
                $tool_content .= "<td class='text-center'><input class='form-control' type='text' name='weighting[$i]' value='$thisWeighting'></td></tr>";                        
            }
            $tool_content .= "<tr>
                    <td class='text-left' colspan='3'><strong>$langSurveyAddAnswer :</strong>&nbsp;
                        <input type='submit' name='moreAnswers' value='$langMoreAnswers' />&nbsp;
                      <input type='submit' name='lessAnswers' value='$langLessAnswers' />                  
                    </td>
                    <td colspan='2'>&nbsp;</td>
                  </tr>
                </table>";
        } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
             $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
             $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>";
             $tempSW = isset($_POST['setWeighting']) ? $_POST['setWeighting'] : '';
             $tool_content .= "
                   <input type='hidden' name='formSent' value='1' />
                   <input type='hidden' name='setWeighting' value='$tempSW'>";
             if (!isset($displayBlanks)) {
                 $str_weighting = isset($weighting)? implode(',', $weighting): '';
                 $tool_content .= "<input type='hidden' name='str_weighting' value='$str_weighting'>
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
        } elseif ($answerType == MATCHING) {

         if (!empty($msgErr)) {
             $tool_content .= "<div class='alert alert-warning'>$msgErr</div>";
         }

         $tool_content .= "
         <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
             <input type='hidden' name='formSent' value='1'>
             <input type='hidden' name='nbrOptions' value='$nbrOptions'>
             <input type='hidden' name='nbrMatches' value='$nbrMatches'>
             <fieldset>
             <table class='table'>";
         $optionsList = array();        
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
               <td>$langScore</td>
             </tr>";        
         $i = $objAnswer->getFirstMatchingPosition();
         for ($j = 1; $j <= $nbrMatches; $i++, $j++) {        
             if (isset($_POST['match'][$i])) {
                 $optionText = htmlspecialchars($_POST['match'][$i]);
             } elseif (isset($match[$i])) {
                 $optionText = htmlspecialchars($match[$i]);
             } elseif (!count($match)) {
                 $optionText = ${'langDefaultMakeCorrespond' . $j}; // Default example option
             } else {
                 $optionText = '';
             }
             $optionWeight = isset($weighting[$i])? q($weighting[$i]): 1;

             $tool_content .= "<tr>
               <td class='text-right'><strong>$j</strong></td>
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
                       <td class='text-right'><strong>" . q($val) . "</strong></td>
                       <td><input class='form-control' type='text' " .
                     "name=\"option[" . $key . "]\" size='58' value=\"";            
             if (isset($_POST['option'][$key])) {
                 $tool_content .= htmlspecialchars($_POST['option'][$key]);
             } elseif (isset($option[$key])) {
                 $tool_content .= htmlspecialchars($option[$key]);            
             } elseif (($val == 'A') or ($val == 'B')) { // default option
                 $tool_content .= ${"langDefaultMatchingOpt$val"};
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
               <td class='text-center'><b>$langScore</b></td>
             </tr>
             <tr>
               <td width='30'>$langCorrect</td>
               <td><input type='radio' value='1' name='correct'$setChecked[1]></td>
               <td width='80%'>"  . rich_text_editor('comment[1]', 4, 30, @$comment[1], true) . "</td>
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
}