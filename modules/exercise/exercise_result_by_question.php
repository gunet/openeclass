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

include 'exercise.class.php';
include 'question.class.php';
include 'answer.class.php';

$require_current_course = TRUE;
$require_editor = TRUE;
include '../../include/baseTheme.php';
require_once 'modules/gradebook/functions.php';
$pageName = $langExercicesResult;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

// picture path
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');
if ($_POST && isset($_POST['questionScore'])) {
    $filter_def = [
        'questionScore' => FILTER_VALIDATE_FLOAT,
        'question_id' => FILTER_VALIDATE_INT,
        'questionMaxGrade' => FILTER_VALIDATE_FLOAT,
        'eurId' => FILTER_VALIDATE_INT
    ];
    $data = filter_input_array(INPUT_POST, $filter_def, TRUE);
    unset($filter_def);
    if (
           $data
           && !in_array(FALSE, $data)
           && !in_array(NULL, $data)
           && $data['questionScore'] <= $data['questionMaxGrade']
       ) {
        $val = Database::get()->query("UPDATE exercise_answer_record
                    SET weight = ?f WHERE eurid = ?d AND question_id = ?d",
               $data['questionScore'], $data['eurId'],  $data['question_id']);
        if (isset($val->affectedRows) && $val->affectedRows == 1) {
            $ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count
                    FROM exercise_answer_record WHERE eurid = ?d AND weight IS NULL", $data['eurId'])->count;
            if ($ungraded == 0) {
                // if no more ungraded questions, set attempt as complete and
                // recalculate sum of grades
                Database::get()->query("UPDATE exercise_user_record
                    SET attempt_status = ?d,
                        total_score = (SELECT SUM(weight) FROM exercise_answer_record
                                         WHERE eurid = ?d)
                    WHERE eurid = ?d",
                    ATTEMPT_COMPLETED, $data['eurId'], $data['eurId']);
                $data = Database::get()->querySingle("SELECT eid, uid, total_score, total_weighting FROM exercise_user_record WHERE eurid = ?d", $data['eurId']);
                // update gradebook
                if (is_null($data->total_weighting) or $data->total_weighting == 0) {
                    update_gradebook_book($data->uid, $data->eid, 0, GRADEBOOK_ACTIVITY_EXERCISE);
                } else {
                    update_gradebook_book($data->uid, $data->eid, $data->total_score/$data->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE);
                }
            } else {
                // else increment total by just this grade
                Database::get()->query("UPDATE exercise_user_record
                    SET total_score = total_score + ?f WHERE eurid = ?d",
                    $data['questionScore'], $data['eurId']);
            }
            Session::flash('message',$langUpdateSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    } else {
        Session::flash('message',$langUpdateFailure);
        Session::flash('alert-class', 'alert-warning');
    }
}

if (isset($_GET['exerciseId'])) {
    $exerciseId = getDirectReference($_GET['exerciseId']);
    $exerciseIdIndirect = $_GET['exerciseId'];
    // koitame an exei dothei sigkekrimeni erotisi gia diorthosi kai an mas exei erthei me post i get
    if (isset($_POST['question_id']) || isset($_GET['question_id'])) {
        if (isset($_GET['question_id'])) {
            $question_id = $_GET['question_id'];
        } else if (isset($_POST['question_id'])) {
            $question_id = $_POST['question_id'];
        }
        $eurid = Database::get()->querySingle("SELECT eur.eurid AS eurid "
                . "FROM exercise_question AS exq "
                . "JOIN exercise_answer_record AS ear ON ear.question_id = exq.id "
                . "JOIN exercise_user_record AS eur ON eur.eurid = ear.eurid "
                . "WHERE eur.eid = ?d AND ear.weight IS NULL "
                . "AND exq.type = " . FREE_TEXT . " OR exq.type = " . ORAL . " AND exq.id = ?d "
                . "GROUP BY exq.id, eur.eurid", $exerciseId, $question_id)->eurid;
        $exercise_user_record = Database::get()->querySingle("SELECT * FROM exercise_user_record WHERE eurid = ?d", $eurid);

        //select form db where user designated a specific free text question to grade
        $exercise_question_ids = Database::get()->queryArray("SELECT DISTINCT ear.question_id, ear.weight "
                . "FROM exercise_answer_record AS ear "
                . "JOIN exercise_question AS exq "
                . "ON ear.question_id = exq.id "
                . "WHERE exq.type = ?d OR exq.type = ?d AND ear.eurid = ?d AND exq.id = ?d", FREE_TEXT, ORAL, $eurid, $question_id);
        $objExercise = new Exercise();
        $objExercise->read($exercise_user_record->eid);
    }
    else {
         redirect_to_home_page('modules/exercise/results_by_question.php?course='.$course_code.'&exerciseId='.$exerciseIdIndirect);
         Session::flash('message',$langUpdateFailure);
         Session::flash('alert-class', 'alert-warning');
    }
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = mathfilter(nl2br(make_clickable($exerciseDescription)), 12, "../../courses/mathimg/");
$displayResults = $objExercise->selectResults();
$displayScore = $objExercise->selectScore();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exercise_user_record->eid, $uid)->count;

$cur_date = new DateTime("now");
$end_date = new DateTime($objExercise->selectEndDate());

$TotalExercisesUngraded = Database::get()->querySingle("SELECT COUNT(eurid) AS ungraded_answers, all_answers.answers AS exercises_done"
       ." FROM exercise_user_record,"
       ."(SELECT COUNT(eurid) AS answers FROM exercise_user_record WHERE eid=?d AND attempt_status=1) AS all_answers"
       ." WHERE eid = ?d AND attempt_status=2 "
       . "GROUP BY all_answers.answers", $exerciseId, $exerciseId);

$tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                  <h3>" .$langQuestionCorrectionTitle . "</h3>
                  <div class='text-heading-h6'>" . $langQuestionCorrectionTitle2 . $TotalExercisesUngraded->ungraded_answers . $langUngradedAnswers . "</div>
                </div>
                <div class='card-body'>";

// for each question
if (count($exercise_question_ids) > 0) {
    foreach ($exercise_question_ids as $row) {
        // creates a temporary Question object
        $objQuestionTmp = new Question();
        $is_question = $objQuestionTmp->read($row->question_id);
        // gets the student choice for this question
        $choice = $objQuestionTmp->get_answers_record($eurid);
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        if ($objQuestionTmp->selectType() == CALCULATED) {
            $des_arr = unserialize($objQuestionTmp->selectDescription());
            $questionDescription = $des_arr['question_description'];
        }
        $questionDescription_temp = mathfilter(nl2br(make_clickable($questionDescription)), 12, "../../courses/mathimg/");
        $questionWeighting = $objQuestionTmp->selectWeighting();
        // destruction of the Question object
        unset($objQuestionTmp);
        //check if question has been graded
        $question_weight = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE question_id = ?d AND eurid =?d", $row->question_id, $eurid)->weight;
        $question_graded = is_null($question_weight) ? FALSE : TRUE;
        $tool_content .= "<table class='table-default ".(($question_graded)? 'graded' : 'ungraded')."'>
                <tr class='active'>
                  <td><b>$langQuestionFreeTexτ</b></td>
                </tr>
            <tr><td>";
        if ($is_question) {
            $tool_content .= "<b>" . q_math($questionName) . "</b><br>" . standard_text_escape($questionDescription_temp) . "<br><br>";
        } else {
            $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langQuestionAlreadyDeleted</span></div>";
        }
        $tool_content .= "</td></tr>";
        if (file_exists($picturePath . '/quiz-' . $row->question_id)) {
            $tool_content .= "<tr><td><img src='../../$picturePath/quiz-" . $row->question_id . "'></td></tr>";
        }
        $tool_content .= "<br>
            <table class='table-default'>
            <tr>
                <td><b>$langTotalScore: <span id='total_score'>$exercise_user_record->total_score</span> / $exercise_user_record->total_weighting</b></td>
            </tr>
            </table>";

        $tool_content .= "</table></div></div><table class='table-default ".(($question_graded)? 'graded' : 'ungraded')."'>";
        $questionScore = 0;
        $tool_content .= "<tr class='active'>
                          <td><b>$langAnswer</b></td>
                            </tr>";
        $tool_content .= "<tr class='even'><td>" . purify($choice) . "</td></tr>";
        $tool_content .= "<tr class='active'><th>";

        $choice = purify($choice);
        if (!empty($choice)) {
            if (!$question_graded) {
                $tool_content .= "<span class='text-danger'>$langAnswerUngraded</span>";
            } else {
                $questionScore = $question_weight;
            }
        }

        if ($choice) {
            if (isset($question_graded) && !$question_graded) {
             //show input field
                $action_url = "exercise_result_by_question.php?exerciseId={$exerciseIdIndirect}";
                $tool_content .= "<form id='grade_form' method='POST' action='$action_url'> <span>
                                   $langQuestionScore: <input style='display:inline-block;width:auto;' type='text' class='questionGradeBox form-control' maxlength='3' size='3' name='questionScore'>
                                   <input type='hidden' name='question_id' value='{$row->question_id}'>
                                   <input type='hidden' name='questionMaxGrade' value='$questionWeighting'>
                                   <input type='hidden' name='eurId' value='{$exercise_user_record->eurid}'>";
                                   $tool_content .= "<b>/$questionWeighting</b></span> </form>";
            } else {
                $tool_content .= "<span>
                                $langQuestionScore: <b>".round($questionScore, 2). " / $questionWeighting</b></span>";
            }
        } else {
            $tool_content .= "<span>$langQuestionScore: <b>$question_weight</b></span>";
        }

        $tool_content .= "</th></tr></table>";
        // destruction of Answer
        unset($objAnswerTmp);
    } // end foreach()
} else {
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

//creating buttons at the end of page
$tool_content .= "<br><div align='center'>";
//submit button will only appear when the exercise can be graded
$tool_content .= "<input type='submit' value='$langSubmit' form='grade_form' class='btn submitAdminBtn' id='submitButton'>"
     ."<a class='btn cancelAdminBtn' href='results_by_question.php?course=$course_code&exerciseId=$exerciseIdIndirect'>
           $langReturn
       </a>";
$tool_content .= "</div>";

draw($tool_content, 2, null, $head_content);
