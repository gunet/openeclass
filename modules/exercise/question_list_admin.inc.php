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
 * @file question_list_admin.inc.php
 */
require_once 'modules/tags/moduleElement.class.php';
// Check if AI functionality is available
require_once 'include/lib/ai/services/AIQuestionBankService.php';

$aiService = new AIQuestionBankService($course_id, $uid);
$aiAvailable = $aiService->isAvailable() && $aiService->isEnabledForCourse(AI_MODULE_QUESTION_POOL);
$exerciseId = $_GET['exerciseId'];

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { // ajax request
    if (isset($_POST['toReorder'])) {
        reorder_table('exercise_with_questions', 'exercise_id', $exerciseId, $_POST['toReorder'], $_POST['prevReorder'] ?? null, 'id', 'q_position');
        exit;
    }

    $action = $_POST['action'];
    if ($action == 'random_criteria') { // random criteria (based upon difficulty)
        if (isset($_POST['questionRandomDrawn']) and intval($_POST['questionRandomDrawn']) > 0) { // random difficulty questions
            $difficultyId = intval($_POST['difficultyId']);
            $categoryId = intval($_POST['categoryId']);
            $questionRandomDrawn = intval($_POST['questionRandomDrawn']);
            if ($difficultyId > 0 and $categoryId > 0) { // random difficulty and category questions
                $random_criteria = serialize(array('criteria' => 'difficultycategory', $questionRandomDrawn => array($difficultyId, $categoryId)));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria) 
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            } else if ($difficultyId > 0) { // random difficulty questions
                $random_criteria = serialize(array('criteria' => 'difficulty', $questionRandomDrawn => $difficultyId));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria) 
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            } else if ($categoryId > 0) { // random category questions
                $random_criteria = serialize(array('criteria' => 'category', $questionRandomDrawn => $categoryId));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria) 
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            }
            // cancel shuffling (if any)
            Database::get()->query("UPDATE exercise SET shuffle = 0 WHERE id = ?d", $exerciseId);
            Database::get()->query("UPDATE exercise SET random = 0 WHERE id = ?d", $exerciseId);
        }

        $data = array('success' => true);

    } else if ($action == 'add_questions') { // add questions

        $category = $_POST['category'];
        $difficulty = $_POST['difficulty'];
        $query_vars = array($course_id);
        $extraSql = '';
        if ($difficulty > -1) {
            $query_vars[] = $difficulty;
            $extraSql .= " AND difficulty = ?d";
        }
        if ($category > -1) {
            $query_vars[] = $category;
            $extraSql .= " AND category = ?d";
        }
        $query_vars[] = $exerciseId;

        $qnum = $_POST['qnum'];
        $query_vars[] = $qnum;
        if ($qnum > 0) {
            $q_ids = Database::get()->queryArray("SELECT id FROM exercise_question 
                                          WHERE course_id = ?d$extraSql 
                                          AND id NOT IN   
                                            (SELECT question_id FROM exercise_with_questions 
                                              WHERE exercise_id = ?d 
                                              AND question_id IS NOT NULL) 
                                          ORDER BY RAND() 
                                          LIMIT ?d", $query_vars);

            $q_ids_count = count($q_ids);
            $i=1;
            foreach ($q_ids as $q_id) {
                $values .= "(?d, ?d)";
                if ($i!=$q_ids_count) $values .= ",";
                $insert_query_vars[] = $q_id->id;
                $insert_query_vars[] = $exerciseId;
                $i++;
            }
            Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id) VALUES $values", $insert_query_vars);
        }
        $data = array('success' => true);
    } else {
        $results = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_question WHERE course_id = ?d$extraSql AND id NOT IN (SELECT question_id FROM exercise_with_questions WHERE exercise_id = ?d)", $query_vars)->count;
        $data = array('results' => $results);
    }
    echo json_encode($data);
    exit();
} // end of ajax request


if (isset($_POST['shuffleQuestions'])) {  // shuffle (aka random questions)
    if (isset($_POST['enableShuffleQuestions'])) {
        $objExercise->setShuffle(1);
        $objExercise->setRandom(0);
    } else if (isset($_POST['enableRandomQuestions'])) {
        if (isset($_POST['numberOfRandomQuestions']) and ($_POST['numberOfRandomQuestions']) > 0) {
            $objExercise->setRandom($_POST['numberOfRandomQuestions']);  // shuffle some questions
            $objExercise->setShuffle(0);
        }
    } else { // reset everything
        $objExercise->setRandom(0);
        $objExercise->setShuffle(0);
    }
    $objExercise->save();
}

$formRandomQuestions = '';
if ($objExercise->hasQuestionListWithRandomCriteria()) {
    $formRandomQuestions = 'disable';
}

$q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d ORDER BY question_cat_name", $course_id);
$cat_options = "<option value=\"-1\">-- " . js_escape($langQuestionAllCats) . " --</option><option value=\"0\">-- $langQuestionWithoutCat --</option>";
$cat_options_2 = "<option value=\"0\"> ---- </option>";
foreach ($q_cats as $qcat) {
    $cat_options .= "<option value=\"$qcat->question_cat_id\">" . js_escape($qcat->question_cat_name) . "</option>";
    $cat_options_2 .= "<option value=\"$qcat->question_cat_id\">" . js_escape($qcat->question_cat_name) . "</option>";
}

// deletes a question from the exercise
if (isset($_GET['deleteQuestion'])) {
    $deleteQuestion = $_GET['deleteQuestion'];
    $objQuestionTmp = new Question();
    // if the question exists and it is not random
    if ($objQuestionTmp->read($deleteQuestion)) {
        $objQuestionTmp->delete($exerciseId);
        if ($objExercise->removeFromList($deleteQuestion)) {
            $nbrQuestions--;
        }
    } else { // random question
        $objQuestionTmp->removeRandomQuestionsFromList($deleteQuestion, $exerciseId);
    }
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
}

// exercise info
$moduleTag = new ModuleElement($exerciseId);
$randomQuestions = $objExercise->isRandom();
$shuffleQuestions = $objExercise->selectShuffle();
$displayResults = $objExercise->selectResults();
$displayScore = $objExercise->selectScore();
$exerciseAssignToSpecific = $objExercise->selectAssignToSpecific();
$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseType = $objExercise->selectType();
$exerciseStartDate = $objExercise->selectStartDate();
$exerciseEndDate = $objExercise->selectEndDate();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$exerciseTempSave = $objExercise->selectTempSave();
if (is_null($exerciseStartDate)) {
    $exerciseStartDate = '';
} else {
    $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $exerciseStartDate);
    $exerciseStartDate = $startDateTime->format('d-m-Y H:i');
}
if (is_null($exerciseEndDate)) {
    $exerciseEndDate = '';
} else {
    $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $exerciseEndDate);
    $exerciseEndDate = $endDateTime->format('d-m-Y H:i');
}
$enableStartDate = ($exerciseStartDate ? 1 : 0);
$enableEndDate = ($exerciseEndDate ? 1 : 0);
$startParts = explode(' ', $exerciseStartDate);
$endParts = explode(' ', $exerciseEndDate);
$periodInfo = '';
if ($exerciseStartDate and $exerciseEndDate) {
    $startWeekDay = $langDay_of_weekNames['long'][$startDateTime->format('w')];
    $periodLabel = "$langExercisePeriod:";
    if ($startParts[0] == $endParts[0]) { // start and end on same date
        $timeDuration = format_time_duration($endDateTime->getTimestamp() - $startDateTime->getTimestamp());
        $periodInfo = "$startWeekDay, $startParts[0] $startParts[1] -- $endParts[1] <small>($timeDuration)</small>";
    } else {
        $endWeekDay = $langDay_of_weekNames['long'][$endDateTime->format('w')];
        $periodInfo = "$startWeekDay, $exerciseStartDate -- $endWeekDay, $exerciseEndDate";
    }
} elseif ($exerciseStartDate) {
    $periodLabel = "<span class='text-success'>$langStart:</span>";
    $periodInfo = $langDay_of_weekNames['long'][$startDateTime->format('w')] . ', ' . $exerciseStartDate;
} elseif ($exerciseEndDate) {
    $periodLabel = "<span class='text-danger'>$langFinish:</span>";
    $periodInfo = $langDay_of_weekNames['long'][$endDateTime->format('w')] . ', ' . $exerciseEndDate;
} else {
    $periodLabel = null;
}

$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
        'url' => "index.php?course=$course_code",
        'icon' => 'fa-reply',
        'level' => 'primary' ],
    [ 'title' => $langExerciseExecute,
        'url' => "exercise_submit.php?course=$course_code&exerciseId=$exerciseId",
        'icon' => 'fa-play-circle',
        'level' => 'primary',
        'button-class' => 'btn-danger' ],
    [ 'title' => $langCourseInfo,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&exerciseId=$exerciseId&modifyExercise=yes",
        'icon' => 'fa-edit',
        'level' => 'primary',
        'button-class' => 'btn btn-success' ]
]);

// Build action bar buttons array
$actionBarButtons = array(
    array('title' => $langNewQu,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&exerciseId=$exerciseId&newQuestion=yes",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success'),
    array('title' => $langRandomQuestionsWithCriteria,
        'modal-class' => 'randomWithCriteria',
        'url' => "#",
        'level' => 'primary-label',
        'icon' => 'fa-random',
        'show' => !$randomQuestions),
    array('title' => $langWithoutCriteria,
        'url' => "question_pool.php?course=$course_code&fromExercise=$exerciseId",
        'level' => 'primary-label',
        'icon' => 'fa-bank'),
    array('title' => $langWithCriteria,
        'url' => "#",
        'modal-class' => 'questionSelection',
        'level' => 'primary-label',
        'icon' => 'fa-building-flag')
);
// Add AI button if available
if ($aiAvailable) {
    $actionBarButtons[] = array(
        'title' => ($langAIGenerateQuestions ?? 'AI Generate Questions'),
        'url' => "ai_question_generation.php?course=$course_code&exerciseId=$exerciseId",
        'icon' => 'fa-magic',
        'level' => 'primary-label',
        'button-class' => 'btn-info'
    );
}

$pageName = $toolName = '';
if ($nbrQuestions) {
    $i = 1;
    $limit = 0;
    $questionList = $objExercise->selectQuestionList();
    $data['questionList'] = $questionList;
}

$data['cat_options'] = $cat_options;
$data['cat_options_2'] = $cat_options_2;
$data['actionBarButtons'] = action_bar($actionBarButtons, false);
$data['exerciseId'] = $exerciseId;
$data['exerciseAssignToSpecific'] = $exerciseAssignToSpecific;
$data['exerciseTitle'] = $exerciseTitle;
$data['exerciseDescription'] = trim($exerciseDescription);
$data['exerciseType'] = $exerciseType;
$data['exerciseTimeConstraint'] = $exerciseTimeConstraint;
$data['exerciseAttemptsAllowed'] = $exerciseAttemptsAllowed;
$data['exerciseTempSave'] = $exerciseTempSave;
$data['nbrQuestions'] = $nbrQuestions;
$data['displayResults'] = $displayResults;
$data['displayScore'] = $displayScore;
$data['shuffleQuestions'] = $shuffleQuestions;
$data['randomQuestions'] = $randomQuestions;
$data['tags_list'] = $moduleTag->showTags();
$data['periodLabel'] = $periodLabel;
$data['periodInfo'] = $periodInfo;
$data['formRandomQuestions'] = $formRandomQuestions;

view('modules.exercise.question_list_admin', $data);
exit;

/**
 * @brief render table row with question info
 * @param $id
 * @param $exerciseId
 * @return string
 */
function questionLegend($id, $exerciseId) {

    global $course_code, $course_id, $limit, $i, $langFrom2, $langNoQuestionWeight, $langNoQuestionAnswers,
           $langFromRandomDifficultyQuestions, $langFromRandomCategoryQuestions, $langAvailable,
           $langWarnAboutAnsweredQuestion, $langConfirmYourChoice, $langEditChange, $langDelete, $langReorder;

    $content = '';
    $objQuestionTmp = new Question();
    if (!is_array($id)) {
        $objQuestionTmp->read($id);
        $q = Database::get()->querySingle("SELECT id FROM exercise_with_questions
                                        WHERE exercise_id = ?d
                                      AND question_id = ?d", $exerciseId, $id);
        $ewq_id = $q->id;
    } else {
        $next_limit = $limit+1;
        $q = Database::get()->querySingle("SELECT id FROM exercise_with_questions
                                        WHERE exercise_id = ?d
                                      AND question_id IS NULL
                                          ORDER BY q_position
                                          ASC
                                          LIMIT $limit,$next_limit", $exerciseId);
        $ewq_id = $q->id;
        $limit++;
    }
    $questionWeight = $objQuestionTmp->selectWeighting();
    $aType = $objQuestionTmp->selectType();
    $question_difficulty_legend = $objQuestionTmp->selectDifficultyIcon($objQuestionTmp->selectDifficulty());
    $question_category_legend = $objQuestionTmp->selectCategoryName($objQuestionTmp->selectCategory());
    $addon = "&htopic=" . $aType;
    if (!is_array($id)) {
        $editUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$id$addon";
        $deleteUrl = "?course=$course_code&exerciseId=$exerciseId&deleteQuestion=$id";
    }

    if (is_array($id)) {
        if ($id['criteria'] == 'difficulty') {
            next($id);
            $number = key($id);
            $difficulty = $id[$number];
            $available_questions = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `exercise_question` WHERE course_id = ?d AND difficulty = ?d", $course_id, $difficulty)->cnt;
            $color = ($available_questions <= $number)? "red" : "";
            $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) . "'
                    (<span style='color: $color;'>$langFrom2 $available_questions $langAvailable<span>)</em>";
        } else if ($id['criteria'] == 'category') {
            next($id);
            $number = key($id);
            $category = $id[$number];
            $available_questions = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `exercise_question` WHERE course_id = ?d AND category = ?d", $course_id, $category)->cnt;
            $color = ($available_questions <= $number)? "red" : "";
            $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomCategoryQuestions '" . $objQuestionTmp->selectCategoryName($category) . "'
                        (<span style='color: $color;'>$langFrom2 $available_questions $langAvailable</span>)</em>";
        } else if ($id['criteria'] == 'difficultycategory') {
            next($id);
            $number = key($id);
            $difficulty = $id[$number][0];
            $category = $id[$number][1];
            $available_questions = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `exercise_question` WHERE course_id = ?d AND difficulty = ?d AND category = ?d", $course_id, $difficulty, $category)->cnt;
            $color = ($available_questions <= $number)? "red" : "";
            $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span>
                    <em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) ."' $langFrom2 '" . $objQuestionTmp->selectCategoryName($category) . "'
                    (<span style='color: $color;'>$langFrom2 $available_questions $langAvailable</span>)</em>";
        }
    } else {
        // check if a question has weight
        if (!$questionWeight) {
            $question_excl_legend = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' 
                    data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-title='$langNoQuestionWeight'></span>";
        } else {
            $question_excl_legend = '';
        }
        // check if a question has answers
        if ($aType != FREE_TEXT and $aType != ORAL and $aType != MATCHING and (!$objQuestionTmp->hasAnswers())) {
            $question_excl_legend_2 = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' 
                        data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-title='$langNoQuestionAnswers'></span>";
        } else {
            $question_excl_legend_2 = '';
        }

        $legend = "<a class='previewQuestion' data-qid='$id' data-nbr='" .
            $objQuestionTmp->selectNbrExercises() . "' data-editurl='$editUrl' data-deleteurl='$deleteUrl' href='#'>" .
            q_math($objQuestionTmp->selectTitle()) . "</a>$question_excl_legend $question_excl_legend_2<br><small>" .
            $objQuestionTmp->selectTypeLegend($aType) .
            "&nbsp;$question_difficulty_legend $question_category_legend</small>";
    }

    $content .= "<tr data-id='$ewq_id'>
                    <td class='count-col'>" . $i . ".</td>
                    <td>" . $legend . "</td>
                    <td class='option-btn-cell'>    
                        <div class='d-flex justify-content-end align-items-center gap-2'>
                            <div class='reorder-btn'>
                                <span class='fa fa-arrows' style='cursor: pointer;' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-titile='$langReorder'></span>
                            </div>
                        <div>";
    if (!is_array($id)) {
        if ($objQuestionTmp->hasAnswered($exerciseId)) {
            $warning_message = $langWarnAboutAnsweredQuestion;
        } else {
            $warning_message = $langConfirmYourChoice;
        }

        $content .=
            action_button(array(
                array('title' => $langEditChange,
                    'url' => $editUrl,
                    'icon-class' => 'warnLink',
                    'icon-extra' => $objQuestionTmp->selectNbrExercises() > 1 ? "data-bs-toggle='modal' data-bs-target='#modalWarning' data-bs-remote='false'" : "",
                    'icon' => 'fa-edit'),
                array('title' => $langDelete,
                    'url' => $deleteUrl,
                    'icon' => 'fa-xmark',
                    'class' => 'delete',
                    'confirm' => $warning_message,
                    'show' => !isset($fromExercise))
            ));
    } else {
        $content .=
            action_button(array(
                array('title' => $langDelete,
                    'url' => "?course=$course_code&exerciseId=$exerciseId&deleteQuestion=$ewq_id",
                    'icon' => 'fa-xmark',
                    'class' => 'delete',
                    'confirm' => $langConfirmYourChoice,
                    'show' => !isset($fromExercise))
            ));
    }
    $content .= "</div></div>";
    $content .= "</td>";
    $content .= "</tr>";
    if (isset($number) and $number > 0) {
        $i = $i + $number;
        $number = 0;
    } else {
        $i++;
    }
    unset($objQuestionTmp);

    return $content;
}
