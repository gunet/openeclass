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


require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
// Check if AI functionality is available
require_once '../../include/lib/ai/services/AIQuestionBankService.php';

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'exercises';
$helpSubTopic = 'question_bank';

require_once '../../include/baseTheme.php';
require_once 'imsqtilib.php';

// Initialize AI service
$aiService = new AIQuestionBankService($course_id, $uid);

load_js('datatables');
$picturePath = "courses/$course_code/image";
$my_courses = Database::get()->queryArray("SELECT a.course_id Course_id, b.title Title FROM course_user a, course b
                              WHERE a.course_id = b.id
                                  AND a.course_id != ?d
                                  AND a.user_id = ?d
                                  AND a.status = " .USER_TEACHER . "", $course_id, $uid);
$courses_options = "";
foreach ($my_courses as $row) {
    $courses_options .= "'<option value=\"$row->Course_id\">".js_escape($row->Title)."</option>'+";
}

$toolName = $langQuestionPool;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

if (isset($_GET['fromExercise'])) {
    $objExercise = new Exercise();
    $fromExercise = intval($_GET['fromExercise']);
    $objExercise->read($fromExercise);
    $navigation[] = array("url" => "admin.php?course=$course_code&exerciseId=$fromExercise", "name" => $langExerciseManagement);
} else {
    $fromExercise = '';
}

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}
if (isset($_GET['difficultyId'])) {
    $difficultyId = intval($_GET['difficultyId']);
}
if (isset($_GET['categoryId'])) {
    $categoryId = intval($_GET['categoryId']);
}

if (isset($_GET['answerType'])) {
    $answerType = intval($_GET['answerType']);
} else {
    $answerType = -1;
}

// deletes a question from the database and all exercises
if (isset($_GET['delete'])) {
    $delete = intval($_GET['delete']);
    // construction of the Question object
    $objQuestionTmp = new Question();
    // if the question exists
    if ($objQuestionTmp->read($delete)) {
        // deletes the question from all exercises
        $objQuestionTmp->delete();
    }
    // destruction of the Question object
    unset($objQuestionTmp);
    redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code" . ($fromExercise? "&fromExercise=$fromExercise" : '') . "&exerciseId=$exerciseId");
}
// gets an existing question and copies it into a new exercise
elseif (isset($_GET['recup']) and $fromExercise) {
    $recup = intval($_GET['recup']);
    // construction of the Question object
    $objQuestionTmp = new Question();
    // if the question exists, add it into the list of questions for the
    // current exercise
    if ($objQuestionTmp->read($recup) and $objExercise->addToList($recup)) {
        Session::flash('message',$langQuestionReused);
        Session::flash('alert-class', 'alert-success');
        $objExercise->save();
    }
    redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code" . ($fromExercise? "&fromExercise=$fromExercise": '') . "&exerciseId=$exerciseId");
} elseif (isset($_REQUEST['clone_pool'])) {
    clone_question_pool($_POST['clone_pool_to_course_id']);
    Session::flash('message',$langCopySuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/exercise/index.php?course=$course_code");
} elseif (isset($_REQUEST['purge'])) {
    purge_question_pool($course_id);
    Session::flash('message',$langQuestionPoolPurgeSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/exercise/index.php?course=$course_code");
}

$exportUrl = "export.php?course=$course_code" .
    (isset($exerciseId)? "&amp;exerciseId=$exerciseId": '') .
    (isset($difficultyId)? "&amp;difficultyId=$difficultyId": '') .
    (isset($answerType)? "&amp;answerType=$answerType": '') .
    (isset($categoryId)? "&amp;categoryId=$categoryId": '');

if ($fromExercise) {
    $action_bar_options[] = [
        'title' => $langGoBackToEx,
        'url' => "admin.php?course=$course_code&amp;exerciseId=$fromExercise",
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    ];
} else {
    $action_bar_options = [
        [ 'title' => $langNewQu,
          'url' => "admin.php?course=$course_code&amp;newQuestion=yes",
          'icon' => 'fa-plus-circle',
          'level' => 'primary-label',
          'button-class' => 'btn-success' ],
        [ 'title' => $langCreateDuplicate,
          'url' => "question_pool.php?course=$course_code&amp;dup=yes",
          'icon' => 'fa-copy',
          'level' => 'primary-label',
          'modal-class' => 'warnDup',
          'button-class' => 'btn-success' ],
        [ 'title' => $langQuestionPoolPurge,
          'url' => "question_pool.php?course=$course_code&amp;purge=yes",
          'icon' => 'fa-eraser',
          'class' => 'delete',
          'confirm' => $langConfirmQuestionPoolPurge ],
        [ 'title' => $langImportAiken,
            'url' => "admin.php?course=$course_code&amp;importAiken=yes",
            'icon' => 'fa-upload',
            'button-class' => 'btn-success'
        ],
        [ 'title' => $langImportQTI,
          'url' => "admin.php?course=$course_code&amp;importIMSQTI=yes",
          'icon' => 'fa-download',
          'button-class' => 'btn-success'
        ],
        [ 'title' => $langDumpPDF,
            'url' => $exportUrl . '&amp;format=pdf',
            'icon' => 'fa-file-pdf',
            'button-class' => 'btn-success'
        ],
        [ 'title' => $langExportQTI,
          'url' => "question_pool.php?". $_SERVER['QUERY_STRING'] . "&amp;exportIMSQTI=yes",
          'icon' => 'fa-upload',
          'button-class' => 'btn-success'
        ],
        [ 'title' => $langAIGenerateQuestions,
          'url' => "ai_question_generation.php?course=$course_code",
          'icon' => 'fa-magic',
          'button-class' => 'btn-info',
          'show' => $aiService->isEnabledForCourse(AI_MODULE_QUESTION_POOL)
        ]
    ];
}

if ($fromExercise) {
    $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d AND id <> ?d ORDER BY id", $course_id, $fromExercise);
} else {
    $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d ORDER BY id", $course_id);
}
$exercise_options = "<option value = '0'>-- $langAllExercises --</option>\n
                    <option value = '-1' ".(isset($exerciseId) && $exerciseId == -1 ? "selected='selected'": "").">-- $langOrphanQuestions --</option>\n";
foreach ($result as $row) {
    $exercise_options .= "
         <option value='" . $row->id . "' ".(isset($exerciseId) && $exerciseId == $row->id ? "selected='selected'":"").">$row->title</option>\n";
}
//Create exercise category options
$q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d ORDER BY question_cat_name", $course_id);
$q_cat_options = "<option value='-1' ".(isset($categoryId) && $categoryId == -1 ? "selected": "").">-- $langQuestionAllCats --</option>\m
                  <option value='0' ".(isset($categoryId) && $categoryId == 0 ? "selected": "").">-- $langQuestionWithoutCat --</option>\n";
foreach ($q_cats as $q_cat) {
    $q_cat_options .= "<option value='" . $q_cat->question_cat_id . "' ".(isset($categoryId) && $categoryId == $q_cat->question_cat_id ? "selected":"").">$q_cat->question_cat_name</option>\n";
}

//START OF BUILDING QUERIES AND QUERY VARS
if (isset($exerciseId) && $exerciseId > 0) { //If user selected specific exercise
    //Building query vars and query
    $result_query_vars = array($course_id, $exerciseId);
    $extraSql = "";
    if (isset($difficultyId) && $difficultyId!=-1) {
        $result_query_vars[] = $difficultyId;
        $extraSql .= " AND difficulty = ?d";
    }
    if (isset($categoryId) && $categoryId!=-1) {
        $result_query_vars[] = $categoryId;
        $extraSql .= " AND category = ?d";
    }

    if ($fromExercise) {
        $result_query_vars = array_merge($result_query_vars, [$fromExercise, $fromExercise]);
        $result_query = "SELECT exercise_question.id FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                        ON question_id = exercise_question.id WHERE course_id = ?d  AND exercise_id = ?d$extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                        question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                        GROUP BY exercise_question.id ORDER BY question";
    } else {
        $result_query = "SELECT exercise_question.id FROM `exercise_with_questions`, `exercise_question`
                        WHERE course_id = ?d AND question_id = exercise_question.id AND exercise_id = ?d$extraSql
                        ORDER BY q_position";
    }
} else { // question pool
    $result_query_vars[] = $course_id;
    $extraSql = "";
    if (isset($difficultyId) && $difficultyId!=-1) {
        $result_query_vars[] = $difficultyId;
        $extraSql .= " AND difficulty = ?d";
    }
    if (isset($categoryId) && $categoryId!=-1) {
        $result_query_vars[] = $categoryId;
        $extraSql .= " AND category = ?d";
    }
    if (isset($answerType) && $answerType!=-1) {
        $result_query_vars[] = $answerType;
        $extraSql .= " AND type = ?d";
    }
    // If user selected All question and comes to question pool from an exercise
    if ((!isset($exerciseId) || $exerciseId == 0) and $fromExercise) {
        $result_query_vars = array_merge($result_query_vars, [$fromExercise, $fromExercise]);
    }
    //When user selected orphan questions
    if (isset($exerciseId) && $exerciseId == -1) {
        $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                        ON question_id = exercise_question.id WHERE course_id = ?d AND exercise_id IS NULL$extraSql ORDER BY question";
    } else { // if user selected all questions
        if ($fromExercise) { // if it is coming to question pool from an exercise
            $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = exercise_question.id WHERE course_id = ?d$extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                            GROUP BY exercise_question.id, question, `type` ORDER BY question";
        } else {
            $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = exercise_question.id WHERE course_id = ?d$extraSql
                            GROUP BY exercise_question.id, question, type ORDER BY question";
        }
        // forces the value to 0
        $exerciseId = 0;
    }
}

// export to IMS QTI xml format
if (isset($_GET['exportIMSQTI'])) {
    $result = Database::get()->queryArray($result_query, $result_query_vars);
    header('Content-type: text/xml');
    header("Content-Disposition: attachment; filename=" . $course_code . "_questions.xml");
    exportIMSQTI($result);
    exit();
}

$tr_content = '';

$result = Database::get()->queryArray($result_query, $result_query_vars);
foreach ($result as $row) {
    $question_temp = new Question();
    $question_temp->read($row->id);
    $questionWeight = $question_temp->selectWeighting();
    $question_title = q_math($question_temp->selectTitle());
    $question_difficulty_legend = $question_temp->selectDifficultyIcon($question_temp->selectDifficulty());
    $question_category_legend = $question_temp->selectCategoryName($question_temp->selectCategory());
    $question_type = $question_temp->selectType();
    $question_type_legend = $question_temp->selectTypeLegend($question_type);
    $exercise_ids = $question_temp->selectExerciseList();
    $exercises_used_in = '';
    foreach ($exercise_ids as $ex_id) {
        $q = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d", $ex_id);
        $exercises_used_in .= "<h6 class='fw-lighter'>" . q($q->title) . "</h6>";
    }
    if ($fromExercise or !is_object(@$objExercise) or !$objExercise->isInList($row->id)) {
        $tr_content .= "<tr>";
        $class = count($exercise_ids) > 0 ? 'previewQuestion warnLink': 'previewQuestion';
        $nbr = $question_temp->selectNbrExercises();
        $editUrl = "{$urlAppend}modules/exercise/admin.php?course=$course_code&amp;modifyAnswers={$row->id}";
        // check if question has weight
        if (!$questionWeight) {
            $question_excl_legend = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' 
                data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-title='$langNoQuestionWeight'></span>";
        } else {
            $question_excl_legend = '';
        }
        // check if question has answers
        if ($question_type != FREE_TEXT and $question_type != ORAL and $question_type != MATCHING and (!$question_temp->hasAnswers())) {
            $question_excl_legend_2 = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' 
                    data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-title='$langNoQuestionAnswers'></span>";
        } else {
            $question_excl_legend_2 = '';
        }
        $tr_content .= "
            <td>
              <div class='float-end fw-lighter text-heading-h6'>No: {$row->id}</></div>
              <a class='$class' data-qid='{$row->id}' data-nbr='$nbr' data-editurl='$editUrl' href='admin.php?course=$course_code&amp;modifyAnswers={$row->id}&amp;fromExercise=$fromExercise'>$question_title</a>
              $question_excl_legend<br>
              <small>$question_type_legend $question_difficulty_legend $question_category_legend $question_excl_legend_2 $exercises_used_in</small>
            </td>";
        if ($question_temp->hasAnswered()) {
            $warning_message = $langWarnAboutAnsweredQuestion;
        } else {
            $warning_message = $langConfirmYourChoice;
        }
        $tr_content .= "<td class='option-btn-cell text-end'>" .
            action_button([
                [ 'title' => $langEditChange,
                  'url' => "admin.php?course=$course_code&amp;modifyAnswers=" . $row->id,
                  'icon-class' => 'warnLink',
                  'icon-extra' => ((count($exercise_ids)>0)?
                     " data-bs-toggle='modal' data-bs-target='#modalWarning' data-bs-remote='false'" : ''),
                  'icon' => 'fa-edit',
                  'show' => !$fromExercise ],
                [ 'title' => $langReuse,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;recup=$row->id&amp;fromExercise=$fromExercise" .
                     "&amp;exerciseId=$exerciseId",
                  'level' => 'primary',
                  'icon' => 'fa-plus-square',
                  'show' => $fromExercise ],
                [ 'title' => $langDelete,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;delete=$row->id",
                  'icon' => 'fa-xmark',
                  'class' => 'delete',
                  'confirm' => $warning_message,
                  'show' => !$fromExercise ],
             ]) . "</td></tr>";
    }
    unset($question_temp);
}

$data['courses_options'] = $courses_options;
$data['exercise_options'] = $exercise_options;
$data['q_cat_options'] = $q_cat_options;
$data['selection_question_types'] = selection([
    '-1' => "-- $langQuestionAllTypes --",
    UNIQUE_ANSWER => $langUniqueSelect,
    MULTIPLE_ANSWER => $langMultipleSelect,
    TRUE_FALSE => $langTrueFalse,
    FILL_IN_BLANKS_TOLERANT => $langFillBlanks,
    FILL_IN_FROM_PREDEFINED_ANSWERS => $langFillFromSelectedWords,
    MATCHING => $langMatching,
    ORDERING => $langOrdering,
    DRAG_AND_DROP_TEXT => $langDragAndDropText,
    DRAG_AND_DROP_MARKERS => $langDragAndDropMarkers,
    CALCULATED => $langCalculated,
    FREE_TEXT => $langFreeText,
    ORAL => $langOral,
], 'answerType', $answerType, "onChange = 'document.qfilter.submit();'class='form-select'");
$data['action_bar'] = action_bar($action_bar_options);

$data['tr_content'] = $tr_content;

view('modules.exercise.question_pool', $data);

/**
 * @brief clone the question pool to a new course
 * @param $new_course_id
 */
function clone_question_pool($clone_course_id): void
{
    global $course_code, $course_id;

    $cat = [];
    $q = Database::get()->queryArray("SELECT question_cat_id, question_cat_name FROM exercise_question_cats
                                                WHERE course_id = ?d", $course_id);
    if (count($q) > 0) {
        foreach ($q as $data) {
            $new_cat_id = Database::get()->query("INSERT INTO exercise_question_cats (question_cat_name, course_id)
                                                                VALUES (?s, ?d)",
                                                $data->question_cat_name, $clone_course_id)->lastInsertID;
            $cat[$data->question_cat_id] = $new_cat_id;
        }
    }

    $old_path = "courses/$course_code/image/quiz-";
    $new_path = 'courses/' . course_id_to_code($clone_course_id) . '/image/quiz-';
    Database::get()->queryFunc("SELECT id, category FROM exercise_question WHERE course_id = ?d",
        function ($question) use ($clone_course_id, $old_path, $new_path, $cat) {
          if ($question->category == 0) {
                $question_clone_id = Database::get()->query("INSERT INTO exercise_question
                    (course_id, question, description, weight, type, difficulty, category)
                    SELECT ?d, question, description, weight, type, difficulty, 0
                        FROM `exercise_question` WHERE id = ?d", $clone_course_id, $question->id)->lastInsertID;
            } else {
                $question_clone_id = Database::get()->query("INSERT INTO exercise_question
                    (course_id, question, description, weight, type, difficulty, category)
                    SELECT ?d, question, description, weight, type, difficulty, ?d
                        FROM `exercise_question` WHERE id = ?d", $clone_course_id, $cat[$question->category], $question->id)->lastInsertID;
            }
            Database::get()->query("INSERT INTO exercise_answer
                    (question_id, answer, correct, comment, weight, r_position)
                    SELECT ?d, answer, correct, comment, weight, r_position FROM exercise_answer
                        WHERE question_id = ?d",
                $question_clone_id, $question->id);
            $old_image_path = $old_path . $question->id;
            if (file_exists($old_image_path)) {
                copy($old_image_path, $new_path . $question_clone_id);
            }
        },
    $course_id);
}


/**
 * @brief purge orphan questions in the question pool
 * @param $course_id
 */
function purge_question_pool($course_id): void
{
    $orphan = Database::get()->queryArray("SELECT id FROM exercise_question
            WHERE exercise_question.course_id = ?d
            AND exercise_question.id NOT IN
              (SELECT question_id FROM exercise_with_questions
                WHERE question_id IS NOT NULL)", $course_id);

    foreach ($orphan as $orphan_ids) {
        Database::get()->query("DELETE FROM exercise_answer WHERE question_id = ?d", $orphan_ids->id);
        Database::get()->query("DELETE FROM exercise_question WHERE id = ?d", $orphan_ids->id);
    }
}
