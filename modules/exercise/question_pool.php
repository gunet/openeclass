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


include 'exercise.class.php';
include 'question.class.php';
include 'answer.class.php';

$require_course_admin = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'exercises';
$helpSubTopic = 'question_bank';

include '../../include/baseTheme.php';
require_once 'imsqtilib.php';

$head_content .= "
<script>
$(function() {
    $('.menu-popover').on('shown.bs.popover', function () {
          $('.warnLink').click( function(e){
                var modifyAllLink = $(this).attr('href');
                var modifyOneLink = modifyAllLink.concat('&clone=true');
                $('a#modifyAll').attr('href', modifyAllLink);
                $('a#modifyOne').attr('href', modifyOneLink);
          });
    });
});
</script>";
$tool_content .= "<div id='dialog' style='display:none;'>$langUsedInSeveralExercises</div>";
$toolName = $langExercices;
$pageName = $langQuestionPool;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

if (isset($_GET['fromExercise'])) {
    $objExercise = new Exercise();
    $fromExercise = intval($_GET['fromExercise']);
    $objExercise->read($fromExercise);
    $navigation[] = array("url" => "admin.php?course=$course_code&amp;exerciseId=$fromExercise", "name" => $langExerciseManagement);
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

// maximum number of questions on a same page
define('QUESTIONS_PER_PAGE', 15);

if (!isset($_GET['page'])) {
    $page = 0;
} else {
    $page = $_GET['page'];
}
    // deletes a question from the data base and all exercises
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
        //Session::set_flashdata($message, $class);
        redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code".(isset($fromExercise) ? "&fromExercise=$fromExercise" : "")."&exerciseId=$exerciseId");
    }
    // gets an existing question and copies it into a new exercise
    elseif (isset($_GET['recup']) && isset($fromExercise)) {
        $recup = intval($_GET['recup']);
        // construction of the Question object
        $objQuestionTmp = new Question();
        // if the question exists
        if ($objQuestionTmp->read($recup)) {
            // adds the exercise ID into the list of exercises for the current question
            $objQuestionTmp->addToList($fromExercise);
        }
        // destruction of the Question object
        unset($objQuestionTmp);
        // adds the question ID into the list of questions for the current exercise
        $objExercise->addToList($recup);
        Session::Messages($langQuestionReused, 'alert-success');
        redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code".(isset($fromExercise) ? "&fromExercise=$fromExercise" : "")."&exerciseId=$exerciseId");
    }

    if (isset($fromExercise)) {
        $action_bar_options[] = array('title' => $langGoBackToEx,
                'url' => "admin.php?course=$course_code&amp;exerciseId=$fromExercise",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
         );
    } else {
        $action_bar_options = array(
            array('title' => $langNewQu,
                'url' => "admin.php?course=$course_code&amp;newQuestion=yes",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langImportQTI,
                'url' => "admin.php?course=$course_code&amp;importIMSQTI=yes",
                'icon' => 'fa-download',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langExportQTI,
                'url' => "question_pool.php?". $_SERVER['QUERY_STRING'] . "&amp;exportIMSQTI=yes",
                'icon' => 'fa-upload',
                'level' => 'primary-label',
                'button-class' => 'btn-success')
         );
    }

    $tool_content .= action_bar($action_bar_options);

    if (isset($fromExercise)) {
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
    $q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d", $course_id);
    $q_cat_options = "<option value='-1' ".(isset($categoryId) && $categoryId == -1 ? "selected": "").">-- $langQuestionAllCats --</option>\m
                      <option value='0' ".(isset($categoryId) && $categoryId == 0 ? "selected": "").">-- $langQuestionWithoutCat --</option>\n";
    foreach ($q_cats as $q_cat) {
        $q_cat_options .= "<option value='" . $q_cat->question_cat_id . "' ".(isset($categoryId) && $categoryId == $q_cat->question_cat_id ? "selected":"").">$q_cat->question_cat_name</option>\n";
    }
    //Start of filtering Component
    $tool_content .= "<div class='form-wrapper'><form class='form-inline' role='form' name='qfilter' method='get' action='$_SERVER[REQUEST_URI]'><input type='hidden' name='course' value='$course_code'>
                        ".(isset($fromExercise)? "<input type='hidden' name='fromExercise' value='$fromExercise'>" : "")."
                        <div class='form-group'>
                            <select onChange = 'document.qfilter.submit();' name='exerciseId' class='form-control'>
                                $exercise_options
                            </select>
                    </div>
                    <div class='form-group'>
                        <select onChange = 'document.qfilter.submit();' name='difficultyId' class='form-control'>
                            <option value='-1' ".(isset($difficultyId) && $difficultyId == -1 ? "selected='selected'": "").">-- $langQuestionAllDiffs --</option>
                            <option value='0' ".(isset($difficultyId) && $difficultyId == 0 ? "selected='selected'": "").">-- $langQuestionNotDefined --</option>
                            <option value='1' ".(isset($difficultyId) && $difficultyId == 1 ? "selected='selected'": "").">$langQuestionVeryEasy</option>
                            <option value='2' ".(isset($difficultyId) && $difficultyId == 2 ? "selected='selected'": "").">$langQuestionEasy</option>
                            <option value='3' ".(isset($difficultyId) && $difficultyId == 3 ? "selected='selected'": "").">$langQuestionModerate</option>
                            <option value='4' ".(isset($difficultyId) && $difficultyId == 4 ? "selected='selected'": "").">$langQuestionDifficult</option>
                            <option value='5' ".(isset($difficultyId) && $difficultyId == 5 ? "selected='selected'": "").">$langQuestionVeryDifficult</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <select onChange = 'document.qfilter.submit();' name='categoryId' class='form-control'>
                            $q_cat_options
                        </select>
                    </div>
                </form>
            </div>";
    //End of filtering Component

    if (isset($fromExercise)) {
        $tool_content .= "<input type='hidden' name='fromExercise' value='$fromExercise'>";
    }

    $tool_content .= "<table class='table-default'>";

    $from = $page * QUESTIONS_PER_PAGE;

    //START OF BUILDING QUERIES AND QUERY VARS
    if (isset($exerciseId) && $exerciseId > 0) { //If user selected specific exercise
        //Building query vars and query
        $total_query_vars = array($course_id, $exerciseId);
        $extraSql = "";
        if(isset($difficultyId) && $difficultyId!=-1) {
            $total_query_vars[] = $difficultyId;
            $extraSql .= " AND difficulty = ?d";
        }
        if(isset($categoryId) && $categoryId!=-1) {
            $total_query_vars[] = $categoryId;
            $extraSql .= " AND category = ?d";
        }
        $total_query_vars = isset($fromExercise) ? array_merge($total_query_vars, array($fromExercise, $fromExercise)) : $total_query_vars;
        $result_query_vars = array_merge($total_query_vars, array($from, QUESTIONS_PER_PAGE));
<<<<<<< local
        if (isset($fromExercise)) {
            $result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
=======
        if (isset($fromExercise)) {            
            $export_result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
>>>>>>> graft
                            ON question_id = id WHERE course_id = ?d  AND exercise_id = ?d$extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                            GROUP BY id ORDER BY question";
            $result_query = $export_result_query .  " LIMIT ?d, ?d";
            $total_query = "SELECT COUNT(id) AS total FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d  AND exercise_id = ?d$extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))";
        } else {
            $export_result_query = "SELECT id, question, type FROM `exercise_with_questions`, `exercise_question`
                            WHERE course_id = ?d AND question_id = id AND exercise_id = ?d$extraSql
                            ORDER BY q_position";
            $result_query = $export_result_query .  " LIMIT ?d, ?d";
            $total_query = "SELECT COUNT(id) AS total FROM `exercise_with_questions`, `exercise_question`
                            WHERE course_id = ?d AND question_id = id AND exercise_id = ?d$extraSql";
        }
    } else { // if user selected either Orphan Question or All Questions
        $total_query_vars[] = $course_id;
        $extraSql = "";
        if(isset($difficultyId) && $difficultyId!=-1) {
            $total_query_vars[] = $difficultyId;
            $extraSql .= " AND difficulty = ?d";
        }
        if(isset($categoryId) && $categoryId!=-1) {
            $total_query_vars[] = $categoryId;
            $extraSql .= " AND category = ?d";
        }
        // If user selected All question and comes to question pool from an exercise
        if ((!isset($exerciseId) || $exerciseId == 0) && isset($fromExercise)) {
            $total_query_vars = array_merge($total_query_vars, array($fromExercise, $fromExercise));
        }
        $result_query_vars = array_merge($total_query_vars, array($from, QUESTIONS_PER_PAGE));
        // When user selected orphan questions
        if (isset($exerciseId) && $exerciseId == -1) {
            $result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d AND exercise_id IS NULL$extraSql ORDER BY question
                            LIMIT ?d, ?d";
            $total_query = "SELECT COUNT(id) AS total FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d AND exercise_id IS NULL$extraSql";
        } else { // if user selected all questions
<<<<<<< local
            if (isset($fromExercise)) { // if coming to question pool from an exercise
                $result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d $extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
=======
            if (isset($fromExercise)) { // if is coming to question pool from an exercise
                $export_result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d$extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
>>>>>>> graft
                                question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
<<<<<<< local
                                GROUP BY id, question, type ORDER BY question LIMIT ?d, ?d";
=======
                                GROUP BY id ORDER BY question";
                $result_query = $export_result_query .  " LIMIT ?d, ?d";
>>>>>>> graft
                $total_query = "SELECT COUNT(id) AS total FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d $extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                                question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))";
            } else {
<<<<<<< local
                $result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d $extraSql
                                GROUP BY id, question, type ORDER BY question LIMIT ?d, ?d";
=======
                $export_result_query = "SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d$extraSql
                                GROUP BY id ORDER BY question";
                $result_query = $export_result_query .  " LIMIT ?d, ?d";
>>>>>>> graft
                $total_query = "SELECT COUNT(id) AS total FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                                ON question_id = id WHERE course_id = ?d $extraSql";
            }
            // forces the value to 0
            $exerciseId = 0;
        }
    }

    if (isset($_GET['exportIMSQTI'])) {
<<<<<<< local

        $result = Database::get()->queryArray($result_query, $result_query_vars);
=======
        
        $result = Database::get()->queryArray($export_result_query, $total_query_vars);
>>>>>>> graft
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="exportQTI.xml"');
        exportIMSQTI($result);
        exit();

    } else {

        //END OF BUILDING QUERIES AND QUERY VARS
        $result = Database::get()->queryArray($result_query, $result_query_vars);
        $total_questions = Database::get()->querySingle($total_query, $total_query_vars)->total;

        $nbrQuestions = count($result);
        $tool_content .= "
    <tr>
      <th>$langQuesList</th>
          <th class='text-center'>".icon('fa-gears')."</th>
        </tr>";
        foreach ($result as $row) {
            $exercise_ids = Database::get()->queryArray("SELECT exercise_id FROM `exercise_with_questions` WHERE question_id = ?d", $row->id);
            if (isset($fromExercise) || !is_object(@$objExercise) || !$objExercise->isInList($row->id)) {
                if ($row->type == UNIQUE_ANSWER) {
                    $answerType = $langUniqueSelect;
                } elseif ($row->type == MULTIPLE_ANSWER) {
                    $answerType = $langMultipleSelect;
                } elseif ($row->type == FILL_IN_BLANKS) {
                    $answerType = "$langFillBlanks ($langFillBlanksStrict)";
                } elseif ($row->type == MATCHING) {
                    $answerType = $langMatching;
                } elseif ($row->type == TRUE_FALSE) {
                    $answerType = $langTrueFalse;
                } elseif ($row->type == FREE_TEXT) {
                    $answerType = $langFreeText;
                } elseif ($row->type == FILL_IN_BLANKS_TOLERANT) {
                    $answerType = "$langFillBlanks ($langFillBlanksTolerant)";
                }
                $tool_content .= "<tr>";
                if (!isset($fromExercise)) {
                    $tool_content .= "<td><a ".((count($exercise_ids)>0)? "class='warnLink' data-toggle='modal' data-target='#modalWarning' data-remote='false'" : "")."href=\"admin.php?course=$course_code&amp;editQuestion=" . $row->id . "&amp;fromExercise=\">" . q($row->question) . "</a><br/>" . $answerType . "</td>";
                } else {
                    $tool_content .= "<td><a href=\"admin.php?course=$course_code&amp;editQuestion=" . $row->id . "&amp;fromExercise=" . $fromExercise . "\">" . q($row->question) . "</a><br>" . $answerType . "</td>";
                }
                $tool_content .= "<td class='option-btn-cell'>".
                    action_button(array(
                        array('title' => $langEditChange,
                              'url' => "admin.php?course=$course_code&amp;editQuestion=" . $row->id,
                              'icon-class' => 'warnLink',
                              'icon-extra' => ((count($exercise_ids)>0)?
                                    " data-toggle='modal' data-target='#modalWarning' data-remote='false'" : ""),
                              'icon' => 'fa-edit',
                              'show' => !isset($fromExercise)),
                        array('title' => $langReuse,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;recup=$row->id&amp;fromExercise=" .
                                    (isset($fromExercise) ? $fromExercise : '') .
                                    "&amp;exerciseId=$exerciseId",
                              'level' => 'primary',
                              'icon' => 'fa-plus-square',
                              'show' => isset($fromExercise)),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;delete=$row->id",
                              'icon' => 'fa-times',
                              'class' => 'delete',
                              'confirm' => $langConfirmYourChoice,
                              'show' => !isset($fromExercise))
                     )) .
                     "</td></tr>";
            }
        }
        if (!$nbrQuestions) {
            $tool_content .= "<tr>";
            if (isset($fromExercise) && ($fromExercise)) {
                $tool_content .= "<td colspan='3'>";
            } else {
                $tool_content .= "<td colspan='4'>";
            }
            $tool_content .= $langNoQuestion . "</td></tr>";
        }
        // questions pagination
        $numpages = intval(($total_questions-1) / QUESTIONS_PER_PAGE);
        if ($numpages > 0) {
            $tool_content .= "<tr>";
            if (isset($fromExercise)) {
                $tool_content .= "<th align='right' colspan='3'>";
            } else {
                $tool_content .= "<th align='right' colspan='4'>";
            }
            if ($page > 0) {
                $prevpage = $page - 1;
                $query_string_url = removeGetVar($_SERVER['REQUEST_URI'], 'page');
<<<<<<< local
                $tool_content .= "<small>&lt;&lt; <a href='$query_string_url&amp;page=$prevpage'>$langPreviousPage</a></small>";
=======
                $tool_content .= "<small>&lt;&lt; <a href='$query_string_url&amp;page=$prevpage'>$langPreviousPage</a>&nbsp;&nbsp;</small>";
>>>>>>> graft
            }
            if ($page < $numpages) {
                $nextpage = $page + 1;
                $query_string_url = removeGetVar($_SERVER['REQUEST_URI'], 'page');
                $tool_content .= "<small><a href='$query_string_url&amp;page=$nextpage'>$langNextPage</a> &gt;&gt;</small>";
            }
        }
        $tool_content .= "</table>";
    }

$tool_content .= "
<!-- Modal -->
<div class='modal fade' id='modalWarning' tabindex='-1' role='dialog' aria-labelledby='modalWarningLabel' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      </div>
      <div class='modal-body'>
        $langUsedInSeveralExercises
      </div>
      <div class='modal-footer'>
        <a href='#' id='modifyAll' class='btn btn-primary'>$langModifyInAllExercises</a>
        <a href='#' id='modifyOne' class='btn btn-success'>$langModifyInQuestionPool</a>
      </div>
    </div>
  </div>
</div>
";

draw($tool_content, 2, null, $head_content);
