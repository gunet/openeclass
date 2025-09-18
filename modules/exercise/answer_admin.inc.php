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

// Check if AI evaluation is available for FREE_TEXT questions
$aiEvaluationAvailable = false;
if ($answerType == FREE_TEXT) {
    require_once 'include/lib/ai/services/AIExerciseEvaluationService.php';
    $aiService = new AIService($course_id, $uid);
    $aiEvaluationAvailable = $aiService->isEnabledForCourse(AI_MODULE_FREE_TEXT_EVALUATION);
    // Get existing AI configuration if it exists
    $aiConfig = Database::get()->querySingle("SELECT * FROM exercise_ai_config WHERE question_id = ?d", $questionId);
}

$newAnswer = $deleteAnswer = $modifyWildCards = false;

$htopic = 0;
if (isset($_GET['htopic'])) { //new question
    $htopic = $_GET['htopic'];
}
if (isset($_POST['submitAnswers'])) {
    $submitAnswers = $_POST['submitAnswers'];
}
if (isset($_POST['submitAIConfig'])) {
    $submitAIConfig = $_POST['submitAIConfig'];
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

if (isset($_POST['modifyWildCards'])) {
    $modifyWildCards = true;
}
// Handle AI configuration form submission for FREE_TEXT questions
if (isset($submitAIConfig) && $answerType == FREE_TEXT && $aiEvaluationAvailable) {
    $aiEnabled = isset($_POST['ai_enabled']) ? 1 : 0;
    $evaluationPrompt = trim($_POST['evaluation_prompt'] ?? '');
    $maxPoints = $objQuestion->selectWeighting(); // Use question's weight as max points
    $sampleResponses = trim($_POST['sample_responses'] ?? '');

    // Validate AI configuration
    $aiConfigError = '';
    if ($aiEnabled && empty($evaluationPrompt)) {
        $aiConfigError = $langAIEvaluationPromptRequired;
    } elseif ($aiEnabled && $maxPoints <= 0) {
        $aiConfigError = $langAIMaxPointsRequired;
    }

    if (empty($aiConfigError)) {
        // Process sample responses if provided
        $sampleResponsesJson = null;
        if (!empty($sampleResponses)) {
            $lines = array_filter(array_map('trim', explode("\n", $sampleResponses)));
            $samples = [];
            foreach ($lines as $line) {
                if (strpos($line, '|') !== false) {
                    list($response, $quality) = array_map('trim', explode('|', $line, 2));
                    $samples[] = ['response' => $response, 'quality' => $quality];
                } else {
                    $samples[] = ['response' => $line, 'quality' => 'good'];
                }
            }
            if (!empty($samples)) {
                $sampleResponsesJson = json_encode($samples);
            }
        }

        if ($aiEnabled) {
            // Insert or update AI configuration
            if ($aiConfig) {
                Database::get()->query("UPDATE exercise_ai_config 
                                       SET enabled = ?d, evaluation_prompt = ?s, 
                                           sample_responses = ?s, updated_at = NOW()
                                       WHERE question_id = ?d",
                                       $aiEnabled, $evaluationPrompt,
                                       $sampleResponsesJson, $questionId);
            } else {
                Database::get()->query("INSERT INTO exercise_ai_config 
                                       (question_id, course_id, enabled, evaluation_prompt, sample_responses)
                                       VALUES (?d, ?d, ?d, ?s, ?s)",
                                       $questionId, $course_id, $aiEnabled, $evaluationPrompt,
                                       $sampleResponsesJson);
            }
            $msgSuccess = $langAIConfigSaved;
        } else {
            // Disable AI evaluation
            if ($aiConfig) {
                Database::get()->query("UPDATE exercise_ai_config SET enabled = 0 WHERE question_id = ?d", $questionId);
            }
            $msgSuccess = $langAIConfigDisabled;
        }

        // Refresh AI config after save
        $aiConfig = Database::get()->querySingle("SELECT * FROM exercise_ai_config WHERE question_id = ?d", $questionId);
    }
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
    } elseif ($answerType == DRAG_AND_DROP_TEXT) {
        $q_text = purify($_POST['drag_and_drop_question']);
        // Use preg_match_all to find all numbers within brackets
        preg_match_all('/\[(\d+)\]/', $q_text, $matches);
        // $matches[1] contains all the captured numbers
        $numbers = $matches[1];
        $totalAnsFromText = [];
        foreach ($numbers as $n) {
            if ($n>0) {
                $n = $n-1;
                $totalAnsFromText[] = $n;
            }
        }

        // Check for duplicates items about the unique number of a blank
        $countsArr = array_count_values($totalAnsFromText);
        $hasDuplicates = false;
        foreach ($countsArr as $value => $count) {
            if ($count > 1) {
                $hasDuplicates = true;
                break;
            }
        }
        if ($hasDuplicates) {
            Session::flash('message', $langErrorWithUniqueNumberOfBlank);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]");
        }

        $totalAnsFromChoices = [];
        $allPredefinedValues = [];
        if (isset($_POST['choice_answer'])) {
            foreach ($_POST['choice_answer'] as $index => $value) {
                $totalAnsFromChoices[] = $index;
                $allPredefinedValues[] = $value;
            }
        }

        // Check for duplicates or empty values
        $DuplicatesOn = (count($allPredefinedValues) !== count(array_unique($allPredefinedValues)));
        $EmptyOn = in_array("", $allPredefinedValues, true);
        if ($DuplicatesOn || $EmptyOn) {
            Session::flash('message', $langPredefinedAnswerExists);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . DRAG_AND_DROP_TEXT);
        }

        // The total number of defined answers can be the same or bigger than the total number of the question blanks.
        $totalNumberOfBlanks = count($totalAnsFromText);
        $totalNumberOfDefinedAnswers = count($totalAnsFromChoices);
        if ($totalNumberOfBlanks > $totalNumberOfDefinedAnswers) {
            Session::flash('message', $langErrorWithChoicesAsAnswers);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]");
        }

        sort($totalAnsFromText);
        sort($totalAnsFromChoices);
        $choicesAnsArr = [];
        foreach ($totalAnsFromChoices as $inde_x) {
            $choicesAnsArr[] = $inde_x . '|' . purify($_POST['choice_answer'][$inde_x]) . '|' . $_POST['choice_grade'][$inde_x];
        }
        $choices_ans = '';
        if (count($choicesAnsArr) > 0) {
            $choices_ans = implode(',', $choicesAnsArr);
            $choices_ans = '::' . $choices_ans;
        }

        $reponse = $q_text . $choices_ans;
        $objAnswer->createAnswer($reponse, 0, '', 0, 1);
        $objAnswer->save();
        if (isset($_POST['choice_grade'])) {
            $weighting = array_map('fix_float', $_POST['choice_grade']);
            $weighting = array_map('abs', $weighting);
            $questionWeighting = array_sum($weighting);
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            } else {
                $objQuestion->save();
            }
        }

    } elseif ($answerType == DRAG_AND_DROP_MARKERS) {

        $arrDataMarkers = [];
        $jsonData = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
        if ($jsonData) {
            $dataJsonMarkers = explode('|', $jsonData);
            foreach ($dataJsonMarkers as $dataJsonValue) {
                $markersData = json_decode($dataJsonValue, true);
                // Loop through each item in the original array
                foreach ($markersData as $index => $value) {
                    if (count($markersData) == 10) { // circle or rectangle
                        $arrDataMarkers[$markersData['marker_id']] = [
                                                                    'marker_answer' => purify($markersData['marker_answer']),
                                                                    'marker_shape' => $markersData['shape_type'],
                                                                    'marker_coordinates' => $markersData['x'] . ',' . $markersData['y'],
                                                                    'marker_offsets' => $markersData['endX'] . ',' . $markersData['endY'],
                                                                    'marker_grade' => $markersData['marker_grade'],
                                                                    'marker_radius' => $markersData['marker_radius'],
                                                                    'marker_answer_with_image' => $markersData['marker_answer_with_image']
                                                                ];
                    } elseif (count($markersData) == 6) { // polygon
                        $arrDataMarkers[$markersData['marker_id']] = [
                                                                    'marker_answer' => purify($markersData['marker_answer']),
                                                                    'marker_shape' => $markersData['shape_type'],
                                                                    'marker_coordinates' => $markersData['points'],
                                                                    'marker_grade' => $markersData['marker_grade'],
                                                                    'marker_answer_with_image' => $markersData['marker_answer_with_image']
                                                                ];
                    } elseif (count($markersData) == 5) { // without shape . So the defined answer is not correct
                        $arrDataMarkers[$markersData['marker_id']] = [
                                                                    'marker_answer' => purify($markersData['marker_answer']),
                                                                    'marker_shape' => null,
                                                                    'marker_coordinates' => null,
                                                                    'marker_grade' => 0,
                                                                    'marker_answer_with_image' => $markersData['marker_answer_with_image']
                                                                ];
                    }
                }
            }
        }

        $markerAnsArr = [];
        if (count($arrDataMarkers) > 0) {
            foreach($arrDataMarkers as $index => $marker) {
                $markerAnsArr[] = $index . '|' . $arrDataMarkers[$index]['marker_answer'] . '|' . $arrDataMarkers[$index]['marker_grade'];
            }
        }

        $marker_ans = '';
        if (count($markerAnsArr) > 0) {
            $marker_ans = implode(',', $markerAnsArr);
            $marker_ans = '::' . $marker_ans;
        }

        $bracketAnswers = '';
        for ($i=1; $i <= count($markerAnsArr); $i++) {
            $bracketAnswers .= ' [' . $i . '] ';
        }
        $reponse = $langDragAndDropMarkersTextAnswers . $bracketAnswers . $marker_ans;
        $objAnswer->createAnswer($reponse, 0, '', 0, 1);
        $objAnswer->save();
        if (isset($_POST['marker_grade'])) {
            $weighting = array_map('fix_float', $_POST['marker_grade']);
            $weighting = array_map('abs', $weighting);
            $questionWeighting = array_sum($weighting);
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            } else {
                $objQuestion->save();
            }
        }

    } elseif ($answerType == CALCULATED) {

        if (isset($_POST['calculated_answer']) && count($_POST['calculated_answer']) > 0
            && isset($_POST['calculated_question']) && !empty($_POST['calculated_question'])) {

            $checkOk = true;
            $checkOkVal = 0;

            $checkMessages[] = [
                1 => $langAddCorrectMandatoryWildCrds,
                2 => $langMissingFieldsInRangeOfWildCard,
                3 => $langMissingFieldsInConstantValOfWildCard,
                4 => $langSeperateCorrectlyTheTypeOfAnswer,
                5 => $langAddRandomOrConstantValOfWildCard
            ];

            // Check if the total number of predefined wildcards is less than the total number of wildcards which exist in the numeric expression.
            $allMandatoryWildCards = [];
            foreach ($_POST['calculated_answer'] as $wcard) {
                $allMandatoryWildCardsTmp = extractValuesInCurlyBrackets($wcard);
                foreach ($allMandatoryWildCardsTmp as $w) {
                    $allMandatoryWildCards[] = $w;
                }
            }
            $uniqueMandatoryWildCards = array_unique($allMandatoryWildCards); // All wildcards have been extracted by the question.

            $wildCardSelection = [];
            if (isset($_POST['wildCardSelection'])) {
                foreach ($_POST['wildCardSelection'] as $w) {
                    if ($w == 1) {
                        $wildCardSelection[] = $w;
                    }
                }
            }

            if (count($uniqueMandatoryWildCards) == count($wildCardSelection)) {
                if (isset($_POST['chooseTheValueForWildCard']) && count($_POST['chooseTheValueForWildCard']) > 0) { // Random or constant wildcard
                    foreach ($_POST['chooseTheValueForWildCard'] as $item => $val) {
                        if ($_POST['chooseTheValueForWildCard'][$item] == 1 && in_array($item, $uniqueMandatoryWildCards)) { // random
                            if (!is_numeric($_POST['wildCard_min'][$item]) or !is_numeric($_POST['wildCard_max'][$item]) or !is_numeric($_POST['wildCard_decimal'][$item])) {
                                $checkOk = false;
                                $checkOkVal = 2;
                            }
                        } elseif ($_POST['chooseTheValueForWildCard'][$item] == 2 && in_array($item, $uniqueMandatoryWildCards)) { // constant
                            if (!is_numeric($_POST['wildCard_answer'][$item])) {
                                $checkOk = false;
                                $checkOkVal = 3;
                            }
                        } elseif ($_POST['chooseTheValueForWildCard'][$item] == 0 && in_array($item, $uniqueMandatoryWildCards)) { // the item has not value
                            $checkOk = false;
                            $checkOkVal = 5;
                        }
                    }
                }
            } else {
                $checkOk = false;
                $checkOkVal = 1;
            }

            // Check if the Answer type field contains the type of expression with the final result of it seperated by the colon symbol (:)
            if (count($uniqueMandatoryWildCards) == 0 && count($_POST['calculated_answer']) > 0) {
                foreach ($_POST['calculated_answer'] as $an) {
                    $tmpArr = explode(':', $an);
                    if (count($tmpArr) < 2) {
                        $checkOk = false;
                        $checkOkVal = 4;
                    }
                }
            }

            if (!$checkOk) {

                // Get posted values after inserting invalid values
                $_SESSION['calculated_question_'.$questionId] = $_POST['calculated_question'];
                if (count($_POST['calculated_answer']) > 0) {
                    for ($i = 1; $i <= count($_POST['calculated_answer']); $i++) {
                        $_SESSION['calculated_answer_'.$questionId][$i] = $_POST['calculated_answer'][$i];
                    }
                    $_SESSION['count_calculated_answer_'.$questionId] = count($_POST['calculated_answer']);
                }
                if (count($_POST['calculated_answer_grade']) > 0) {
                    for ($i = 1; $i <= count($_POST['calculated_answer_grade']); $i++) {
                        $_SESSION['calculated_answer_grade_'.$questionId][$i] = $_POST['calculated_answer_grade'][$i];
                    }
                }
                foreach($_POST['wildCardSelection'] as $wildcard_index => $val) {
                    $_SESSION['wildCardSelection_'.$questionId][$wildcard_index] = $val;
                }
                foreach($_POST['chooseTheValueForWildCard'] as $wildcard_index => $val) {
                    $_SESSION['chooseTheValueForWildCard_'.$questionId][$wildcard_index] = $val;
                }
                foreach($_POST['wildCard_min'] as $wildcard_index => $val) {
                    $_SESSION['wildCard_min_'.$questionId][$wildcard_index] = $val;
                }
                foreach($_POST['wildCard_max'] as $wildcard_index => $val) {
                    $_SESSION['wildCard_max_'.$questionId][$wildcard_index] = $val;
                }
                foreach($_POST['wildCard_decimal'] as $wildcard_index => $val) {
                    $_SESSION['wildCard_decimal_'.$questionId][$wildcard_index] = $val;
                }
                foreach($_POST['wildCard_answer'] as $wildcard_index => $val) {
                    $_SESSION['wildCard_answer_'.$questionId][$wildcard_index] = $val;
                }

                unset($_SESSION['wildCard_'.$questionId]);

                $Msgerror = $checkMessages[0][$checkOkVal];
                Session::flash('message', $Msgerror);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . CALCULATED . "&invalid_val=true");
            } else { // Insert in db
                $arrItems = [];
                $wildCardOptions = false;
                if (isset($_POST['chooseTheValueForWildCard']) && count($_POST['chooseTheValueForWildCard']) > 0) {
                    foreach ($_POST['chooseTheValueForWildCard'] as $item => $val) {
                        if ($val == 1) { // Set a value of the wildcard by its range
                            $arrItems[] = [
                                'item' => $item,
                                'minimum' => $_POST['wildCard_min'][$item],
                                'maximum' => $_POST['wildCard_max'][$item],
                                'decimal' => $_POST['wildCard_decimal'][$item],
                                'value' => getRandomFloat($_POST['wildCard_min'][$item],$_POST['wildCard_max'][$item],$_POST['wildCard_decimal'][$item]),
                                'type' => $val
                            ];
                        } elseif ($val == 2) { // Set a constant value of the wildcard
                            $arrItems[] = [
                                'item' => $item,
                                'minimum' => '',
                                'maximum' => '',
                                'decimal' => '',
                                'value' => $_POST['wildCard_answer'][$item],
                                'type' => $val
                            ];
                        }
                    }
                }

                $description = purify($_POST['calculated_question']);
                $objQuestion->updateDescription($description);
                if (count($arrItems) > 0) {
                    $jsonItems = json_encode($arrItems);
                    if ($questionId > 0) {
                        $objQuestion->updateOptions($jsonItems);
                        $objQuestion->save();
                        $wildCardOptions = true;
                        unset($_SESSION['wildCard_'.$questionId]);
                    }
                }

                // Inserting the predefined answers for the current question in database.
                $questionWeighting = $nbrGoodAnswers = 0;
                for ($i = 1; $i <= count($_POST['calculated_answer']); $i++) {
                    $reponse[$i] = purify($_POST['calculated_answer'][$i]);
                    if ($wildCardOptions) {
                        $resultOfExpression = evaluateExpression($reponse[$i], $questionId);
                        if ($resultOfExpression or $resultOfExpression == 0) {
                            $reponse[$i] = $reponse[$i] . ':' . $resultOfExpression;
                        }
                    }
                    $comment[$i] = '';
                    $weighting[$i] = fix_float($_POST['calculated_answer_grade'][$i]);
                    $goodAnswer = ((isset($_POST['calculated_answer_grade'][$i]) && $_POST['calculated_answer_grade'][$i] > 0) ? 1 : 0);
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
                        $msgErr = $langChooseGoodAnswer;
                    } else {
                        // save the answers into the database
                        $objAnswer->save();

                        // set the total weighting of the question
                        $objQuestion->updateWeighting($questionWeighting);
                        if (isset($exerciseId)) {
                            $objQuestion->save($exerciseId);
                        } else {
                            $objQuestion->save();
                        }

                        // Unset session posted variables
                        unset($_SESSION['calculated_question_'.$questionId]);
                        unset($_SESSION['calculated_answer_'.$questionId]);
                        unset($_SESSION['calculated_answer_grade_'.$questionId]);
                        unset($_SESSION['count_calculated_answer_'.$questionId]);
                        unset($_SESSION['wildCardSelection_'.$questionId]);
                        unset($_SESSION['chooseTheValueForWildCard_'.$questionId]);
                        unset($_SESSION['wildCard_min_'.$questionId]);
                        unset($_SESSION['wildCard_max_'.$questionId]);
                        unset($_SESSION['wildCard_decimal_'.$questionId]);
                        unset($_SESSION['wildCard_answer_'.$questionId]);
                         
                    }
                }
            }
        } else {
            Session::flash('message', $langFieldsMissing);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . CALCULATED);
        }

    } elseif($answerType == ORDERING) {

        $totalAnsFromOrderingChoices = [];
        $PredefinedValues = [];
        if (isset($_POST['ordering_answer'])) {
            foreach ($_POST['ordering_answer'] as $index => $value) {
                $totalAnsFromOrderingChoices[] = $index;
                $PredefinedValues[] = $value;
            }
        }

        // Update options
        $layoutItems = (isset($_POST['layoutItems']) ? $_POST['layoutItems'] : '');
        $ItemsSelectionType = (isset($_POST['ltemsSelectionType']) ? $_POST['ltemsSelectionType'] : '');
        $SizeOfSubset = (isset($_POST['SizeOfSubset']) ? $_POST['SizeOfSubset'] : '');
        if (isset($ItemsSelectionType) && $ItemsSelectionType == 1) {
            $SizeOfSubset = '';
        } elseif (isset($ItemsSelectionType) && $ItemsSelectionType > 1) {
            if (!is_numeric($SizeOfSubset)) {
                Session::flash('message', $langFillInTheSizeOfSubset);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . ORDERING);
            } elseif (is_numeric($SizeOfSubset) && ($SizeOfSubset > count($PredefinedValues) or $SizeOfSubset <= 1)) { // A subset must have at least 2 items.
                Session::flash('message', $langTheSizeOfSubsetIsBiggerThanPrAnswers);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . ORDERING);
            }
        }
        $arrOptions = [
                        'layoutItems' => $layoutItems,
                        'itemsSelectionType' => $ItemsSelectionType,
                        'sizeOfSubset' => $SizeOfSubset
                        ];
        $opt = json_encode($arrOptions);
        $objQuestion->updateOptions($opt);

        // Check for duplicates or empty values
        $DuplicatesItemsOn = (count($PredefinedValues) !== count(array_unique($PredefinedValues)));
        $EmptyItemsOn = in_array("", $PredefinedValues, true);
        if ($DuplicatesItemsOn || $EmptyItemsOn) {
            Session::flash('message', $langPredefinedAnswerExists);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=" . ORDERING);
        }

        $choicesOrdArr = [];
        foreach ($totalAnsFromOrderingChoices as $inde_x) {
            $choicesOrdArr[] = $inde_x . '|' . $_POST['ordering_answer'][$inde_x] . '|' . $_POST['ordering_answer_grade'][$inde_x];
        }
        $choices_ordering_answer = '';
        if (count($choicesOrdArr) > 0) {
            $choices_ordering_answer = implode(',', $choicesOrdArr);
        }

        $reponse = purify($choices_ordering_answer);
        $objAnswer->createAnswer($reponse, 0, '', 0, 1);
        $objAnswer->save();
        if (isset($_POST['ordering_answer_grade'])) {
            $weighting = array_map('fix_float', $_POST['ordering_answer_grade']);
            $weighting = array_map('abs', $weighting);
            $questionWeighting = array_sum($weighting);
            $objQuestion->updateWeighting($questionWeighting);
            if (isset($exerciseId)) {
                $objQuestion->save($exerciseId);
            } else {
                $objQuestion->save();
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
            $reponse[$i] = $objAnswer->getTitle($i);
            $comment[$i] = $objAnswer->getComment($i);
            $weighting[$i] = $objAnswer->getWeighting($i);

            if ($answerType == MULTIPLE_ANSWER) {
                $correct[$i] = $objAnswer->isCorrect($i);
            } elseif ($objAnswer->isCorrect($i)) {
                $correct = $i;
            }
        }

    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
        if (!isset($submitAnswers) && !isset($buttonBack)) {
            if (!(isset($_POST['setWeighting']) and $_POST['setWeighting'])) {
                $reponse = $objAnswer->getTitle(1);
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
    } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        if (!isset($submitAnswers) && !isset($buttonBack)) {
            if (!(isset($_POST['setWeighting']) and $_POST['setWeighting'])) {
                $answer = $objAnswer->getTitle(1);
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
                    $match[$i] = $objAnswer->getTitle($i);
                    $sel[$i] = $objAnswer->isCorrect($i);
                    $weighting[$i] = $objAnswer->getWeighting($i);
                    $nbrMatches++;
                } else { // it is an option
                    $option[$i] = $objAnswer->getTitle($i);
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
                $reponse[$i] = $objAnswer->getTitle($i);
                $comment[$i] = $objAnswer->getComment($i);
                $weighting[$i] = $objAnswer->getWeighting($i);
                if ($objAnswer->isCorrect($i)) {
                    $correct = $i;
                }
            }
        }
    } elseif ($answerType == DRAG_AND_DROP_TEXT) {

        $drag_and_drop_question = (isset($_POST['drag_and_drop_question']) and !empty($_POST['drag_and_drop_question'])) ? $_POST['drag_and_drop_question'] : '';
        if (empty($drag_and_drop_question)) {
            $drag_and_drop_question = $objAnswer->get_drag_and_drop_text();
        }

        if ($newAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] + 1;
        } else {
            $nbrAnswers = $objAnswer->get_total_drag_and_drop_answers();
        }
        if ($deleteAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] - 1;
            if ($nbrAnswers < 2) { // minimum 2 answers
               $nbrAnswers = 2;
            }
        }

        $choices_from_db = $objAnswer->get_drag_and_drop_answer_text();
        $grades_from_db = $objAnswer->get_drag_and_drop_answer_grade();

    } elseif ($answerType == DRAG_AND_DROP_MARKERS) {
        if ($newAnswer && !isset($_GET['remImg'])) {
            $nbrAnswers = $_POST['nbrAnswers'] + 1;
        } else { // for edit
            $marker_ids_arr = $objAnswer->get_marker_ids($questionId);
            $nbrAnswers = (count($marker_ids_arr) > 0 ? max($marker_ids_arr) : 2);
        }
        if ($deleteAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] - 1;
            if ($nbrAnswers < 2) { // minimum 2 answers
               $nbrAnswers = 2;
            } elseif ($nbrAnswers+1 >= 3) {
                $nbrAnswersDel = $nbrAnswers + 1;
                removeJsonDataFromMarkerId($nbrAnswersDel,$questionId);
            }
        }

        $coordinatesXY = [];
        $arrDataMarkers = getDataMarkersFromJson($questionId);
        foreach ($arrDataMarkers as $index => $m) {
            $arr_m = explode(',', $m['marker_coordinates'] ?? '');
            if (count($arr_m) == 2) {
                $m['x'] = $arr_m[0];
                $m['y'] = $arr_m[1];
            }
            if ($m['marker_shape'] == 'circle' or $m['marker_shape'] == 'rectangle') {
                $arr_of = explode(',', $m['marker_offsets']);
                $m['endx'] = $arr_of[0];
                $m['endy'] = $arr_of[1];
            }
            if ($m['marker_shape'] == 'circle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'radius' => $m['marker_radius'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'rectangle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'width' => $m['endy'], 'height' => $m['endx'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'polygon') {
                $coordinatesXY[] = ['marker_id' => $index, 'points' => $m['marker_coordinates'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'marker_answer_with_image' => $m['marker_answer_with_image']];
            }
        }
        $DataMarkersToJson = json_encode($coordinatesXY) ?? '';

    } elseif ($answerType == CALCULATED) {

        $calc_question = Database::get()->querySingle("SELECT * FROM exercise_question WHERE id = ?d", $questionId);
        $calculated_question = $_POST['calculated_question'] ?? strip_tags($calc_question->description);
        if (isset($_GET['invalid_val']) && isset($_SESSION['calculated_question_'.$questionId])) { // After posting invalid values
            $calculated_question = $_SESSION['calculated_question_'.$questionId];
            // If the user is in invalid mode and posted new arithmetic expression, update it.
            if (isset($_POST['calculated_question']) && $_POST['calculated_question'] != $calculated_question) {
                $calculated_question = $_POST['calculated_question'];
            }
        }
        $options = $calc_question->options;

        $calculated_answer = [];
        if (isset($_POST['calculated_answer'])) {
            foreach ($_POST['calculated_answer'] as $index => $answer) { // for creating question
                $calculated_answer[$index] = $answer;
            }
        }
        if (isset($_GET['invalid_val']) && isset($_SESSION['calculated_answer_'.$questionId])) { // After posting invalid values
            foreach ($_SESSION['calculated_answer_'.$questionId] as $index => $answer) {
                $calculated_answer[$index] = $answer;
                // If the user is in invalid mode and posted new predefined answers, update them.
                if (isset($_POST['calculated_answer'][$index]) && $_POST['calculated_answer'][$index] != $calculated_answer[$index]) {
                    $calculated_answer[$index] = $_POST['calculated_answer'][$index];
                }
            }
        }

        $calculated_answer_grade = [];
        if (isset($_POST['calculated_answer_grade'])) {
            foreach ($_POST['calculated_answer_grade'] as $index => $grade) { // for creating question
                $calculated_answer_grade[$index] = $grade;
            }
        }
        if (isset($_GET['invalid_val']) && isset($_SESSION['calculated_answer_grade_'.$questionId])) { // After posting invalid values
            foreach ($_SESSION['calculated_answer_grade_'.$questionId] as $index => $grade) {
                $calculated_answer_grade[$index] = $grade;
                // If the user is in invalid mode and posted new predefined grades, update them.
                if (isset($_POST['calculated_answer_grade'][$index]) && $_POST['calculated_answer_grade'][$index] != $calculated_answer[$index]) {
                    $calculated_answer_grade[$index] = $_POST['calculated_answer_grade'][$index];
                }
            }
        }

        $arithmetic_expression = '';
        if (isset($calculated_answer) && count($calculated_answer) > 0) {
            $arithmetic_expression = implode(',', $calculated_answer);
        }

        ////////////////////////////////////////////////////
        // Modify the text of question and its answer types.
        if (!$modifyWildCards or isset($_POST['backModifyCalculated'])) {
            if ($newAnswer) {
                $nbrAnswers = $_POST['nbrAnswers'] + 1;
            } else { // for edit
                // Get the total number of predefined answers
                $totalNumberOfCalculatedAnswers = Database::get()->querySingle("SELECT COUNT(*) as total FROM exercise_answer WHERE question_id = ?d", $questionId)->total;
                $nbrAnswers = (isset($totalNumberOfCalculatedAnswers) && $totalNumberOfCalculatedAnswers > 0 ? $totalNumberOfCalculatedAnswers : 1); // minimum 1 answer
                if (isset($_POST['calculated_answer']) && count($_POST['calculated_answer']) > 0) {
                    $nbrAnswers = count($_POST['calculated_answer']);
                }
                if (isset($_GET['invalid_val']) && isset($_SESSION['count_calculated_answer_'.$questionId])) { // After inserting invalid posted variables get the session variables to show 
                    $nbrAnswers = $_SESSION['count_calculated_answer_'.$questionId];
                }
            }
            if ($deleteAnswer) {
                $nbrAnswers = $_POST['nbrAnswers'] - 1;
                if ($nbrAnswers <= 1) { // minimum 1 answers
                    $nbrAnswers = 1;
                }
            }

            if (isset($totalNumberOfCalculatedAnswers) && $totalNumberOfCalculatedAnswers > 0 && !isset($_GET['invalid_val'])) { // for editing question
                if (!isset($_POST['backModifyCalculated'])) {
                    $predefinedAns = Database::get()->queryArray("SELECT * FROM exercise_answer WHERE question_id = ?d", $questionId);
                    foreach ($predefinedAns as $an) {
                        $arrAns = explode(':', $an->answer);
                        if (count($arrAns) > 0) {
                            if (is_null($options)) {
                                $calculated_answer[$an->r_position] = $an->answer;
                            } else {
                                $calculated_answer[$an->r_position] = $arrAns[0];
                            }
                        } else {
                            $calculated_answer[$an->r_position] = '';
                        }
                        $calculated_answer_grade[$an->r_position] = $an->weight;
                    }
                }
            }

        } elseif($modifyWildCards) {

            ///////////////////////////////////////////////
            // Modify the variables from the Curly brackets
            $wildCardsAll = [];
            if ((!is_null($options) or !empty($options)) && !isset($_GET['invalid_val'])) { //  Items have been inserted in db - editing question
                $dataItems = json_decode($options, true);
                // Create a key-value array for items
                foreach ($dataItems as $wildcard) {
                    $wildCardsAll[] = $wildcard['item'];
                    $_SESSION['wildCard_'.$questionId][$wildcard['item']] = [
                                                                                'wildcard_minimum_val' => $wildcard['minimum'] ?? '',
                                                                                'wildcard_maximum_val' => $wildcard['maximum'] ?? '',
                                                                                'wildcard_decimal_val' => $wildcard['decimal'] ?? '',
                                                                                'wildcard_random_val' => $wildcard['value'] ?? '',
                                                                                'wildcard_type' => $wildcard['type'] ?? ''
                                                                            ];                                                         
                }
            }
        }
    } elseif ($answerType == ORDERING) {

        if ($newAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] + 1;
        } else { // for edit
            // Get the total number of predefined answers
            $nbrAnswers = $objAnswer->get_total_ordering_answers() ?? 2; // minimum 2 answer
            if ($nbrAnswers == 0) {
                $nbrAnswers = 2;
            }
        }
        if ($deleteAnswer) {
            $nbrAnswers = $_POST['nbrAnswers'] - 1;
            if ($nbrAnswers <= 2) { // minimum 1 answers
                $nbrAnswers = 2;
            }
        }

        $opts = $objQuestion->selectOptions();
        if ($opts) {
            $arrOpts = json_decode($opts, true);
        }

        $ordering_answer = $objAnswer->get_ordering_answers();
        $ordering_answer_grade = $objAnswer->get_ordering_answer_grade();

    }

    $classImg = '';
    $classContainer = '';
    $classCanvas = '';
    if ($answerType == DRAG_AND_DROP_MARKERS) {
        $classImg = 'drag-and-drop-markers-img';
        $classContainer = 'drag-and-drop-markers-container';
        $classCanvas = 'drag-and-drop-markers-canvas';
    }

    $tool_content .= "<div class='col-12'><div class='card panelCard card-default px-lg-4 py-lg-3'>
                      <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3>$langQuestion &nbsp;" .
                            icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . "?course=$course_code".(isset($exerciseId) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . $questionId)."
                        </h3>
                      </div>
                      <div class='card-body' style='overflow:auto;'>
                            <h5>$questionTypeWord<br>" . nl2br(q_math($questionName)) . "</h5>
                                <p>$questionDescription</p>
                                ".(($okPicture)? "<div class='$classContainer' id='image-container-$questionId' style='position: relative; display: inline-block;'><img class='$classImg' id='img-quiz-$questionId' src='../../$picturePath/quiz-$questionId'><canvas id='drawingCanvas-$questionId' class='$classCanvas'></canvas></div>":"")."
                      </div>
                    </div></div>";

    if ($answerType != FREE_TEXT and $answerType != ORAL) {
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
                $tool_content .="<tr><td style='min-width: 4%;' valign='top'>$i.</td>";
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
     } elseif ($answerType == DRAG_AND_DROP_TEXT) {
            $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
            $tool_content .= "  <div class='col-12 d-flex justify-content-between align-items-center gap-3'>
                                    <div>
                                        <p class='text-nowrap'><span class='Accent-200-cl'>(*)</span>$langCPFFieldRequired</p>
                                    </div>
                                    <div>
                                        " . form_popovers('help', $langInfoDragAndDropText) . "
                                    </div>
                                </div>";

            $tool_content .= "
                            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                                <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                                <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                                <label for='drag_and_drop_questionTEXT' class='form-label mt-4'>$langCompleteTheTextOfTheQuestion <span class='Accent-200-cl'>(*)</span></label>
                                <textarea id='drag_and_drop_questionTEXT' class='form-control' name='drag_and_drop_question' cols='70' rows='6'>{$drag_and_drop_question}</textarea>
                                <div class='table-responsive mb-4'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr>
                                                <th>$langChoice</th>
                                                <th>$langAnswer <span class='Accent-200-cl'>(*)</span></th>
                                                <th>$langGradebookGrade <span class='Accent-200-cl'>(*)</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                        for ($i=0; $i<$nbrAnswers; $i++) {
                                            $chAns = $i+1;
                                            $choiceAsAnswer = ((count($choices_from_db) > 0) && array_key_exists($i,$choices_from_db)) ? $choices_from_db[$i] : $_POST['choice_answer'][$i] ?? '';
                                            $choiceAsGrade = ((count($grades_from_db) > 0) && array_key_exists($i,$grades_from_db)) ? $grades_from_db[$i] : $_POST['choice_grade'][$i] ?? 0;
                                            $tool_content .= "
                                                <tr>
                                                    <td>[{$chAns}]</td>
                                                    <td class='col-9'>                                       
                                                        <input type='text' class='form-control' name='choice_answer[$i]' value='{$choiceAsAnswer}'>                                        
                                                    </td>
                                                    <td>                                        
                                                        <input type='number' class='form-control' name='choice_grade[$i]' value='{$choiceAsGrade}' min='0' step='0.05'>                                        
                                                    </td>
                                                </tr>";
                                        }
            $tool_content .= "          </tbody>
                                    </table>
                                </div>
                                <div class='col-12 d-flex justify-content-start align-items-center gap-3 flex-wrap my-4'>
                                    <input class='btn submitAdminBtn' type='submit' name='moreAnswers' value='$langMoreAnswers' />
                                    <input class='btn deleteAdminBtn' type='submit' name='lessAnswers' value='$langLessAnswers' />
                                </div>";
        } elseif ($answerType == DRAG_AND_DROP_MARKERS) {

            load_js('drag-and-drop-shapes');

            $tool_content .= "<input type='hidden' class='currentQuestionId' value='{$questionId}'>
                            <input type='hidden' class='currentCourseCode' value='{$course_code}'>";
            $head_content .= "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    shapesCreationProcess();
                                });

                                $(function() {
                                    const checkboxes = document.querySelectorAll('.checkUploadAnswerWithImg');
                                    checkboxes.forEach(ch => {
                                        var checkBoxId = ch.getAttribute('id');
                                        const partsCheckbox = checkBoxId.split('-');
                                        const val_chAns = partsCheckbox[partsCheckbox.length - 1];
                                        if (ch.checked) {
                                            $('#hasUploadedImg_'+val_chAns).show();
                                        } else {
                                            $('#hasUploadedImg_'+val_chAns).hide();
                                        }
                                    });

                                    $('.checkUploadAnswerWithImg').change(function() {
                                        var checkBoxId = $(this).attr('id');
                                        const partsCheckbox = checkBoxId.split('-');
                                        const val_chAns = partsCheckbox[partsCheckbox.length - 1];
                                        if ($(this).is(':checked')) {
                                            $(this).val(1);
                                            $('#hasUploadedImg_'+val_chAns).show();
                                        } else {
                                            $(this).val(0);
                                            $('#hasUploadedImg_'+val_chAns).hide();
                                        }
                                    });
                                });

                            </script>";

            $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
            $DataJsonFileVariables = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
            $tool_content .= "
                <div class='col-12 d-flex justify-content-between align-items-center gap-3'>
                    <div>
                        <p class='text-nowrap'><span class='Accent-200-cl'>(*)</span>$langCPFFieldRequired</p>
                    </div>
                    <div>
                        " . form_popovers('help', $langInfoDragAndDropMarkersCreation) . "
                    </div>
                </div>
            ";

            $tool_content .= "
                            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                                <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                                <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>
                                <input type='hidden' id='insertedMarkersAsJson' value='{$DataMarkersToJson}'>
                                <input type='hidden' id='dataJsonVariables' value='{$DataJsonFileVariables}'>
                                <input type='hidden' id='ImgSrc' value='../../$picturePath/quiz-$questionId'>
                                <div class='table-responsive mb-4'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr>
                                                <th>$langMarker</th>
                                                <th>$langAnswer <span class='Accent-200-cl'>(*)</span></th>
                                                <th>$langShape</th>
                                                <th>$langGradebookGrade <span class='Accent-200-cl'>(*)</span></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                        for ($i=0; $i<$nbrAnswers; $i++) {
                                            $chAns = $i+1;
                                            $markerShape = $arrDataMarkers[$chAns]['marker_shape'] ?? '';
                                            $markerCoordinates = $arrDataMarkers[$chAns]['marker_coordinates'] ?? '';
                                            if ($markerShape == 'rectangle' or $markerShape == 'circle') {
                                                $markerCoordinates = ($arrDataMarkers[$chAns]['marker_coordinates'] . ':' . $arrDataMarkers[$chAns]['marker_offsets']) ?? '';
                                            }
                                            $markerAnswer = $arrDataMarkers[$chAns]['marker_answer'] ?? '';
                                            $markerGrade = $arrDataMarkers[$chAns]['marker_grade'] ?? 0;
                                            $markerAnswerWithImageChecked = ((isset($arrDataMarkers[$chAns]['marker_answer_with_image']) && $arrDataMarkers[$chAns]['marker_answer_with_image'] == 1) ? 'checked' : '');
                                            $markerAnswerWithImageValue = $arrDataMarkers[$chAns]['marker_answer_with_image'] ?? 0;
                                            $htopic = DRAG_AND_DROP_MARKERS;

                                            $delUploadImage = '';
                                            $anUploadImg = "$webDir/courses/$course_code/image/answer-$questionId-$chAns";
                                            if (isset($_GET['fromExercise'])) {
                                                $exerciseId = $_GET['fromExercise'];
                                            }
                                            if (file_exists($anUploadImg)) {
                                                $pathDel = $urlAppend . "modules/exercise/upload_image_as_answer.php?delete_image=true&course=$course_code&exerciseId=$exerciseId&modifyAnswers=$_GET[modifyAnswers]&htopic=$htopic&questionId=$questionId&markerId=$chAns";
                                                $delUploadImage .= ' <div class="col-sm-12 d-inline-flex justify-content-start align-items-center">
                                                                        <img id="imageUploaded-'.$chAns.'" src="../../courses/'.$course_code.'/image/answer-'.$questionId.'-'.$chAns.'" style="height:80px; width:80px;" alt="answer-'.$questionId.'-'.$chAns.'"> 
                                                                        <a class="link-color Accent-200-cl" href="'.$pathDel.'"><i class="fa-solid fa-xmark fa-lg"></i></a>
                                                                    </div>';
                                            } else {
                                                $delUploadImage .= '<input type="file" id="hasUploadedImg_'.$chAns.'" name="image_as_answer">';
                                            }

                                            $tool_content .= "
                                            <tr>
                                                <td>[{$chAns}]</td>
                                                <td>
                                                    <div class='col-12'>
                                                        <input type='text' id='marker-answer-$chAns' class='form-control marker-answer' name='marker_answer[$chAns]' value='{$markerAnswer}'>
                                                    </div>
                                                    <div class='col-12 mt-3' style='width:200px;'>
                                                        <div class='checkbox'>
                                                            <label class='label-container' aria-label='$langSelect'>
                                                                <input type='checkbox' id='marker-answer-with-image-$chAns' class='checkUploadAnswerWithImg' value='{$markerAnswerWithImageValue}' $markerAnswerWithImageChecked>                                                                        
                                                                <span class='checkmark'></span>
                                                                $langAddAnswerThroughImg
                                                            </label>
                                                        </div>
                                                        $delUploadImage
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='col-12 d-flex gap-2 flex-wrap'>
                                                        <select class='shape-selection form-select' id='shapeType-$chAns'>
                                                            <option value=''>$langSelect</option>
                                                            <option value='rectangle' " . (($markerShape == 'rectangle') ? 'selected' : '') . ">$langRectangle</option>
                                                            <option value='circle' " . (($markerShape == 'circle') ? 'selected' : '') . ">$langCircle</option>
                                                            <option value='polygon' " . (($markerShape == 'polygon') ? 'selected' : '') . ">$langPolygon</option>
                                                        </select>
                                                        <input type='hidden' class='form-control' id='shape-coordinates-$chAns' name='marker_coordinates[$chAns]' value='{$markerCoordinates}'>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='col-12'>
                                                        <input type='number' id='marker-grade-$chAns' class='form-control' name='marker_grade[$chAns]' value='{$markerGrade}' min='0' step='0.1'>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='col-12 d-flex justify-content-center align-items-center gap-3 flex-wrap'>
                                                        <button id='add-data-shape-$chAns' class='btn submitAdminBtn add-data-shape text-nowrap'>$langAdd</button>
                                                        <button id='delete-data-shape-$chAns' class='btn deleteAdminBtn delete-data-shape text-nowrap'>$langDelete</button>
                                                    </div>
                                                </td>
                                            </tr>";
                                        }
            $tool_content .= "          </tbody>
                                    </table>
                                </div>
                                <div class='col-12 d-flex justify-content-start align-items-center gap-3 flex-wrap my-4'>
                                    <input class='btn submitAdminBtn' type='submit' name='moreAnswers' value='$langMoreAnswers' />
                                    <input class='btn deleteAdminBtn' type='submit' name='lessAnswers' value='$langLessAnswers' />
                                </div>";
        } elseif ($answerType == CALCULATED) {

            if (isset($_GET['fromExercise'])) {
                $exerciseId = $_GET['fromExercise'];
            }

            $invalid_posted = '';
            if (isset($_GET['invalid_val'])) {
                $invalid_posted = '&invalid_val=true';
            }

            $tool_content .= " <form id='calculatedFormId' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "$invalid_posted'>
                                    <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                                    <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>";

            if (!$modifyWildCards) {
                $tool_content .= "  <div class='col-12 d-flex justify-content-between align-items-center gap-3'>
                                        <div>
                                            <p class='text-nowrap'><span class='Accent-200-cl'>(*)</span>$langCPFFieldRequired</p>
                                        </div>
                                        <div class='d-flex gap-2'>
                                            " . form_popovers('help', $langCompleteVariablesOfQuestionInfo) . "
                                            " . form_popovers('warning', $langCompleteVariablesOfQuestionWarning) . "
                                        </div>
                                    </div>

                                    <label for='calculated_question_id' class='form-label mt-4'>$langCompleteTheTextOfTheQuestion<span class='Accent-200-cl'>(*)</span></label>
                                    <textarea id='calculated_question_id' class='form-control mt-0' name='calculated_question' cols='70' rows='6'>{$calculated_question}</textarea>
                                    <div class='table-responsive mb-4'>
                                        <table class='table-default'>
                                            <thead>
                                                <tr>
                                                    <th>$langTypeOfAnswer<span class='Accent-200-cl'>(*)</span></th>
                                                    <th>$langGradebookGrade<span class='Accent-200-cl'>(*)</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>";
                                            for ($i=1; $i<=$nbrAnswers; $i++) {
                                                $cal_answer = (isset($calculated_answer[$i]) ? $calculated_answer[$i] : '');
                                                $cal_grade = (isset($calculated_answer_grade[$i]) ? $calculated_answer_grade[$i] : '');
                                                $tool_content .= "
                                                    <tr>
                                                        <td>                                       
                                                            <input type='text' class='form-control' name='calculated_answer[$i]' value='{$cal_answer}'>                                        
                                                        </td>
                                                        <td>                                        
                                                            <input type='number' class='form-control' name='calculated_answer_grade[$i]' value='{$cal_grade}' min='0' step='any'>                                        
                                                        </td>
                                                    </tr>";
                                            }
                $tool_content .= "          </tbody>
                                        </table>
                                    </div>

                                    <div class='col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap mt-4'>
                                        <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                            <input class='btn submitAdminBtn' type='submit' name='moreAnswers' value='$langMoreAnswers' />
                                            <input class='btn deleteAdminBtn' type='submit' name='lessAnswers' value='$langLessAnswers' />
                                        </div>
                                        <div>
                                            <input class='btn submitAdminBtn' type='submit' name='modifyWildCards' value='$langEditItems &gt;' />
                                        </div>
                                    </div>";

            } elseif($modifyWildCards) {

                $head_content .= "<script>
                                    $(function() {

                                        $('.wildcard-selected').on('click', function (){
                                            var id = $(this).attr('id');
                                            var val = $(this).val();
                                            var parts = id.split('_');
                                            var wildCardPart = parts[1];
                                            if (val == 1) {
                                                $('#panelCard_'+wildCardPart).removeClass('d-none').addClass('d-block');
                                            } else {
                                                $('#panelCard_'+wildCardPart).removeClass('d-block').addClass('d-none');
                                                $('#'+wildCardPart).val('0');
                                                $('#wildCardMinimum_'+wildCardPart).val('');
                                                $('#wildCardMaximum_'+wildCardPart).val('');
                                                $('#wildCardDecimal_'+wildCardPart).val('');
                                                $('#wildCardAnswerId_'+wildCardPart).val('');
                                            }
                                        });

                                        $('.chooseValueForWildCard').on('click', function (){
                                            var id = $(this).attr('id');
                                            if ($(this).val() == 0) {
                                                $('#wildCardRandomContent_'+id).removeClass('d-block').addClass('d-none');
                                                $('#wildCardConstantContent_'+id).removeClass('d-block').addClass('d-none');
                                            } else if ($(this).val() == 1) {
                                                $('#wildCardRandomContent_'+id).removeClass('d-none').addClass('d-block');
                                                $('#wildCardConstantContent_'+id).removeClass('d-block').addClass('d-none');
                                            } else if ($(this).val() == 2) {
                                                $('#wildCardRandomContent_'+id).removeClass('d-block').addClass('d-none');
                                                $('#wildCardConstantContent_'+id).removeClass('d-none').addClass('d-block');
                                            }
                                        });

                                    });
                                </script>";

                                // Retrieve previous post variables like text , answer type and grade.
                                $tool_content .= "<input type='hidden' name='calculated_question' value='{$calculated_question}'>";
                                if (count($calculated_answer) > 0) {
                                    foreach ($calculated_answer as $index => $an) {
                                        $tool_content .= "<input type='hidden' name='calculated_answer[$index]' value='{$an}'>";
                                    }
                                }
                                if (count($calculated_answer_grade)) {
                                    foreach ($calculated_answer_grade as $index => $gr) {
                                        $tool_content .= "<input type='hidden' name='calculated_answer_grade[$index]' value='{$gr}'>";
                                    }
                                }

                                // Get the wildcards from the expression of the question.
                                $wildCardsArr = extractValuesInCurlyBrackets($calculated_question);
                                // Get the wildcards from the arithmetic type of the correct answer.
                                $wildCardsArrAr = extractValuesInCurlyBrackets($arithmetic_expression);
                                // Merge arrays
                                $mergedArray = array_merge($wildCardsArr, $wildCardsArrAr);
                                // Remove duplicates wildcards
                                $uniqueWildCards = array_unique($mergedArray);
                                if (count($uniqueWildCards) > 0) {
                                    $tool_content .= "<ul class='list-group list-group-flush'>";
                                    foreach ($uniqueWildCards as $wildCard) {
                                        $tool_content .= "<li class='list-group-item element px-0 mb-5 bg-transparent'>";

                                        $wildCardMinimumValue = '';
                                        $wildCardMaximumValue = '';
                                        $wildCardDecimalValue = '';
                                        $wildCardValue = '';
                                        $wildCardType = '';
                                        $displayRandomContentOfWildCard = 'd-none';
                                        $displayConstantContentOfWildCard = 'd-none';

                                        if (isset($_SESSION['wildCard_'.$questionId][$wildCard]) && !empty($_SESSION['wildCard_'.$questionId][$wildCard])) {
                                            $wildCardMinimumValue = $_SESSION['wildCard_'.$questionId][$wildCard]['wildcard_minimum_val'];;
                                            $wildCardMaximumValue = $_SESSION['wildCard_'.$questionId][$wildCard]['wildcard_maximum_val'];
                                            $wildCardDecimalValue = $_SESSION['wildCard_'.$questionId][$wildCard]['wildcard_decimal_val'];
                                            $wildCardValue = $_SESSION['wildCard_'.$questionId][$wildCard]['wildcard_random_val'];
                                            $wildCardType = $_SESSION['wildCard_'.$questionId][$wildCard]['wildcard_type'];
                                        }

                                        if (!empty($wildCardType) && $wildCardType == 1) {
                                            $displayRandomContentOfWildCard = 'd-block';
                                        } elseif (!empty($wildCardType) && $wildCardType == 2) {
                                            $displayConstantContentOfWildCard = 'd-block';
                                        }

                                        $wildCardSelectionIsSet = false;
                                        if (isset($_GET['invalid_val']) && isset($_SESSION['wildCardSelection_'.$questionId][$wildCard])) {
                                            $wildCardSelectionIsSet = true;
                                        }

                                        // Dropdown list with all possible wildcards. Select which of them will be the mandatory wildcard.
                                        $tool_content .= "<div class='col-12 my-2'>
                                                            <label class='form-label text-nowrap' for='selectWildCard_{$wildCard}'>
                                                                <h4 class='mb-0'>$langItemToAdd {{$wildCard}}</h4>
                                                            </label>
                                                            <select class='form-select wildcard-selected' id='selectWildCard_{$wildCard}' name='wildCardSelection[$wildCard]'>
                                                                <option value='0'>$langItIsNotWildCard</option>
                                                                <option value='1' " . ((in_array($wildCard,$wildCardsAll) or $wildCardSelectionIsSet) ? 'selected' : '') . ">$langItIsWildCard</option>
                                                            </select>
                                                          </div>";

                                        $displaypanelWildCard = 'd-none';
                                        if (in_array($wildCard,$wildCardsAll) or $wildCardSelectionIsSet) {
                                            $displaypanelWildCard = 'd-block';
                                        }

                                        $chooseValueTypeOfWildCard = 0;
                                        if (isset($_GET['invalid_val']) && isset($_SESSION['chooseTheValueForWildCard_'.$questionId][$wildCard])) {
                                            $chooseValueTypeOfWildCard = $_SESSION['chooseTheValueForWildCard_'.$questionId][$wildCard];
                                            if ($chooseValueTypeOfWildCard == 1) {
                                                $displayRandomContentOfWildCard = 'd-block';
                                            } elseif ($chooseValueTypeOfWildCard == 2) {
                                                $displayConstantContentOfWildCard = 'd-block';
                                            }
                                        }

                                        if (isset($_GET['invalid_val']) && isset($_SESSION['wildCard_min_'.$questionId][$wildCard])) {
                                            $wildCardMinimumValue = $_SESSION['wildCard_min_'.$questionId][$wildCard];
                                        }

                                        if (isset($_GET['invalid_val']) && isset($_SESSION['wildCard_max_'.$questionId][$wildCard])) {
                                            $wildCardMaximumValue = $_SESSION['wildCard_max_'.$questionId][$wildCard];
                                        }

                                        if (isset($_GET['invalid_val']) && isset($_SESSION['wildCard_decimal_'.$questionId][$wildCard])) {
                                            $wildCardDecimalValue = $_SESSION['wildCard_decimal_'.$questionId][$wildCard];
                                        }

                                        if (isset($_GET['invalid_val']) && isset($_SESSION['wildCard_answer_'.$questionId][$wildCard])) {
                                            $wildCardValue = $_SESSION['wildCard_answer_'.$questionId][$wildCard];
                                        }

                                        $tool_content .= "<div class='col-12 my-4 $displaypanelWildCard' id='panelCard_{$wildCard}'>";
                                        $tool_content .= "  <div class='form-group d-flex justify-content-start align-items-center gap-3'>
                                                                " . form_popovers('help', $langAutoCompleteWildCardInfo) . "
                                                                <select class='form-select chooseValueForWildCard' id='{$wildCard}' name='chooseTheValueForWildCard[$wildCard]'>
                                                                    <option value='0' " . (($wildCardType == 0 or $chooseValueTypeOfWildCard == 0) ? 'selected' : '') . ">$langSelect</option>
                                                                    <option value='1' " . (($wildCardType == 1 or $chooseValueTypeOfWildCard == 1) ? 'selected' : '') . ">$langRandomValue</option>
                                                                    <option value='2' " . (($wildCardType == 2 or $chooseValueTypeOfWildCard == 2) ? 'selected' : '') . ">$langConstantValue</option>
                                                                </select>
                                                            </div>
                                                            <div class='form-group mt-4 $displayRandomContentOfWildCard' id='wildCardRandomContent_{$wildCard}'>        
                                                                <div class='form-label text-decoration-underline mb-3'>$langRangeOfValues</div>
                                                                <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                                                    <div  class='flex-fill'>
                                                                        <label class='form-label' for='wildCardMinimum_$wildCard'>$langMinValue</label>
                                                                        <input type='number' id='wildCardMinimum_{$wildCard}' class='form-control mt-0' name='wildCard_min[$wildCard]' value='{$wildCardMinimumValue}' step='any'>
                                                                    </div>
                                                                    <div class='flex-fill'>
                                                                        <label class='form-label' for='wildCardMaximum_$wildCard'>$langMaxValue</label>
                                                                        <input type='number' id='wildCardMaximum_{$wildCard}' class='form-control mt-0' name='wildCard_max[$wildCard]' value='{$wildCardMaximumValue}' step='any'>
                                                                    </div>
                                                                    <div  class='flex-fill'>
                                                                        <label class='form-label' for='wildCardDecimal_$wildCard'>$langDecimalValues</label>
                                                                        <input type='number' id='wildCardDecimal_{$wildCard}' class='form-control mt-0' name='wildCard_decimal[$wildCard]' value='{$wildCardDecimalValue}' min='0' max='10' step='1'>
                                                                        <small>($langZeroForNoDecimal)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4 $displayConstantContentOfWildCard' id='wildCardConstantContent_{$wildCard}'>
                                                                <label for='wildCardAnswerId_{$wildCard}' class='form-label'>$langConstantValue</label>
                                                                <input type='text' class='form-control' id='wildCardAnswerId_{$wildCard}' name='wildCard_answer[$wildCard]' value='{$wildCardValue}' style='max-width:250px;'>
                                                            </div>";
                                        $tool_content .= "</div>";
                                        $tool_content .= "</li>";
                                    }
                                    $tool_content .= "</ul>";
                                } else {
                                    $tool_content .= "<div class='col-12 mt-4'><p>$langNoExistVariables</p></div>";
                                }
            }

        } elseif ($answerType == ORDERING) {

            $head_content .= "<script>
                                $(function() {
                                    $('#ItemsSelectionTypeId').on('click', function (){
                                        var valType = $(this).val();
                                        if (valType == 2 || valType == 3) {
                                            $('.SizeOfSubSetContainer').removeClass('d-none').addClass('d-block');
                                        } else {
                                            $('.SizeOfSubSetContainer').removeClass('d-block').addClass('d-none');
                                        }
                                    });
                                });
                                </script>";

            $setId = isset($exerciseId)? "&amp;exerciseId=$exerciseId" : '';
            $tool_content .= " <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$setId&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                                    <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                                    <input type='hidden' name='nbrAnswers' value='$nbrAnswers'>";

                $tool_content .= "  <div class='col-12 d-flex justify-content-between align-items-center gap-3'>
                                        <div>
                                            <p class='text-nowrap'><span class='Accent-200-cl'>(*)</span>$langCPFFieldRequired</p>
                                        </div>
                                        <div>
                                            " . form_popovers('help', $langInfoOrderingQuestion) . "
                                        </div>
                                    </div>

                                    <div class='table-responsive mb-4'>
                                        <table class='table-default'>
                                            <thead>
                                                <tr>
                                                    <th>$langItem</th>
                                                    <th>$langAnswer<span class='Accent-200-cl'>(*)</span></th>
                                                    <th>$langGradebookGrade<span class='Accent-200-cl'>(*)</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>";
                                            for ($i=1; $i <= $nbrAnswers; $i++) {
                                                $fromPostAnswer = $_POST['ordering_answer'][$i] ?? '';
                                                $fromPostAnswerGrade = $_POST['ordering_answer_grade'][$i] ?? '';
                                                $order_answer = (isset($ordering_answer[$i]) ? $ordering_answer[$i] : $fromPostAnswer);
                                                $order_grade = (isset($ordering_answer_grade[$i]) ? $ordering_answer_grade[$i] : $fromPostAnswerGrade);
                                                $tool_content .= "
                                                    <tr>
                                                        <td>($i)</td>
                                                        <td>                                       
                                                            <input type='text' class='form-control' name='ordering_answer[$i]' value='{$order_answer}'>                                        
                                                        </td>
                                                        <td>                                        
                                                            <input type='number' class='form-control' name='ordering_answer_grade[$i]' value='{$order_grade}' min='0' step='any'>                                        
                                                        </td>
                                                    </tr>";
                                            }
                $tool_content .= "          </tbody>
                                        </table>
                                    </div>

                                    <div class='col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap mt-4'>
                                        <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                            <input class='btn submitAdminBtn' type='submit' name='moreAnswers' value='$langMoreAnswers' />
                                            <input class='btn deleteAdminBtn' type='submit' name='lessAnswers' value='$langLessAnswers' />
                                        </div>
                                    </div>";

                $valSizeOfSubset = (isset($arrOpts) && !empty($arrOpts['sizeOfSubset']) ? $arrOpts['sizeOfSubset'] : '');
                $hiddenSize = 'd-none';
                if (!empty($valSizeOfSubset)) {
                    $hiddenSize = 'd-block';
                }

                $tool_content .= "  <div class='col-12 d-flex justify-content-start align-items-start gap-3 my-4'>
                                        <div style='flex: 1;'>
                                            <label for='layoutItemsId' class='form-label'>$langLayoutItems</label>
                                            <select class='form-select' id='layoutItemsId' name='layoutItems'>
                                                <option value='Vertical' " . (isset($arrOpts) && $arrOpts['layoutItems'] == 'Vertical' ? 'selected' : ''). ">$langVertical</option>
                                                <option value='Horizontal' " . (isset($arrOpts) && $arrOpts['layoutItems'] == 'Horizontal' ? 'selected' : ''). ">$langHorizontal</option>                                                
                                            </select>
                                        </div>
                                        <div style='flex: 1;'>
                                            <label for='ItemsSelectionTypeId' class='form-label'>$langItemsSelectionType</label>
                                            <select class='form-select' id='ItemsSelectionTypeId' name='ltemsSelectionType'>
                                                <option value='1' " . (isset($arrOpts) && $arrOpts['itemsSelectionType'] == 1 ? 'selected' : ''). ">$langSelectAllItems</option>
                                                <option value='2' " . (isset($arrOpts) && $arrOpts['itemsSelectionType'] == 2 ? 'selected' : ''). ">$langSelectRandomSubSetOfItems</option>
                                                <option value='3' " . (isset($arrOpts) && $arrOpts['itemsSelectionType'] == 3 ? 'selected' : ''). ">$langSelectContiguousSubSetOfItems</option>
                                            </select>
                                            <div class='SizeOfSubSetContainer $hiddenSize mt-3'>
                                                <label for='SizeOfSubsetId' class='form-label'>$langSizeOfSubset</label>
                                                <input type='text' id='SizeOfSubsetId' class='form-control' name='SizeOfSubset' value='{$valSizeOfSubset}'>
                                            </div>
                                        </div>
                                    </div>";


        }

        $cancel_link = isset($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "question_pool.php?course=$course_code";
        $submit_text = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) && !isset($setWeighting) ? "$langNext &gt;" : $langSubmit;
        $back_button = ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) && isset($setWeighting) ? "<input class='btn submitAdminBtn' type='submit' name='buttonBack' value='&lt; $langBack'' />" : "";
        if ($answerType == CALCULATED && $modifyWildCards) {
            $back_button = "<input type='submit' class='btn submitAdminBtn' name='backModifyCalculated' value='&lt; $langBack' />";
        }
        // Hide the submit button if the question is CALCULATED type and it must be modified.
        $hiddenClass = '';
        if ($answerType == CALCULATED && !$modifyWildCards) {
            $hiddenClass = 'd-none';
        }

        $tool_content .= "
                        <div class='col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap $hiddenClass mt-4'>
                            <div>$back_button</div>
                            <div class='d-flex justify-content-start align-itens-center gap-3 flex-wrap'>
                                <a class='btn cancelAdminBtn' href='$cancel_link'>$langCancel</a>
                                <input class='btn submitAdminBtn' type='submit' name='submitAnswers' value='$submit_text'>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div></div>";
    } else {
        // FREE_TEXT questions - show AI evaluation configuration if available
        if ($aiEvaluationAvailable) {

            // Display success/error messages
            if (!empty($aiConfigError)) {
                $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$aiConfigError</span></div>";
            }
            if (!empty($msgSuccess)) {
                $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$msgSuccess</span></div>";
            }

            $aiEnabled = $aiConfig ? $aiConfig->enabled : 0;
            $evaluationPrompt = $aiConfig ? $aiConfig->evaluation_prompt : '';
            $maxPoints = $objQuestion->selectWeighting(); // Use question's weight as max points
            $sampleResponses = '';

            if ($aiConfig && $aiConfig->sample_responses) {
                $samples = json_decode($aiConfig->sample_responses, true);
                if ($samples && is_array($samples)) {
                    $lines = [];
                    foreach ($samples as $sample) {
                        if (isset($sample['response']) && isset($sample['quality'])) {
                            $lines[] = $sample['response'] . ' | ' . $sample['quality'];
                        } else {
                            $lines[] = $sample['response'];
                        }
                    }
                    $sampleResponses = implode("\n", $lines);
                }
            }

            $tool_content .= "
               <div class='col-12 mt-4'>
                   <div class='card panelCard card-default px-lg-4 py-lg-3'>
                       <div class='card-header border-0'>
                           <h3>$langAIEvaluation</h3>
                       </div>
                       <div class='card-body'>
                           <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId))? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=" . urlencode($_GET['modifyAnswers']) . "'>
                               <input type='hidden' name='formSent' value='1'>
                               <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                               
                               <div class='form-group mb-3'>
                                   <label class='label-container'>
                                       <input type='checkbox' name='ai_enabled' value='1'" . ($aiEnabled ? " checked='checked'" : "") . ">
                                       <span class='checkmark'></span>
                                       $langEnableAIEvaluation
                                   </label>
                                   <small class='form-text text-muted'>$langAIEvaluationDescription</small>
                               </div>
                               
                               <div class='form-group mb-3'>
                                   <label for='evaluation_prompt' class='form-label'>$langEvaluationCriteria <span class='asterisk'>*</span></label>
                                   <textarea name='evaluation_prompt' id='evaluation_prompt' class='form-control' rows='4' 
                                             placeholder='$langEvaluationCriteriaPlaceholder'>" . q($evaluationPrompt) . "</textarea>
                                   <small class='form-text text-muted'>$langEvaluationCriteriaHelp</small>
                               </div>
                               
                               <div class='form-group mb-3'>
                                   <label class='form-label'>$langMaxPoints</label>
                                   <div class='form-control-static'>
                                       <strong>$maxPoints</strong> <span class='text-muted'>($langDefinedInQuestionStatement)</span>
                                   </div>
                                   <small class='form-text text-muted'>$langMaxPointsHelp</small>
                               </div>
                               
                               <div class='form-group mb-3'>
                                   <label for='sample_responses' class='form-label'>$langSampleResponses</label>
                                   <textarea name='sample_responses' id='sample_responses' class='form-control' rows='6'
                                             placeholder='$langSampleResponsesPlaceholder'>" . q($sampleResponses) . "</textarea>
                                   <small class='form-text text-muted'>$langSampleResponsesHelp</small>
                               </div>
                               
                               <div class='row'>
                                   <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                       <input class='btn submitAdminBtn' type='submit' name='submitAIConfig' value='$langSaveAIConfig'>
                                       <a class='btn cancelAdminBtn' href='" . (isset($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "question_pool.php?course=$course_code") . "'>$langCancel</a>
                                   </div>
                               </div>
                               
                               </fieldset>
                           </form>
                       </div>
                   </div>
               </div>";

            // Show info about existing AI evaluations if any
            if ($aiEnabled && isset($exerciseId)) {
                $evaluationCount = Database::get()->querySingle("SELECT COUNT(*) as count FROM exercise_ai_evaluation WHERE question_id = ?d", $questionId)->count;
                if ($evaluationCount > 0) {
                    $tool_content .= "
                       <div class='col-12 mt-3'>
                           <div class='alert alert-info'>
                               <i class='fa-solid fa-circle-info fa-lg'></i>
                               <span>$langAIEvaluationsFound ($evaluationCount evaluations)</span>
                           </div>
                       </div>";
                }
            }
        } else {
            $tool_content .= "
               <div class='col-12 mt-4'>
                   <div class='alert alert-info'>
                       <i class='fa-solid fa-circle-info fa-lg'></i>
                       <span>$langFreeTextNoAnswerConfig</span>
                   </div>
               </div>";
        }
    }
}
