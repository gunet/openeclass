<?php
/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
$guest_allowed = true;
include '../../include/baseTheme.php';
require_once 'modules/gradebook/functions.php';
require_once 'game.php';
require_once 'analytics.php';

$unit = isset($unit)? $unit: null;

if ($unit) {
    $unit_name = Database::get()->querySingle('SELECT title FROM course_units WHERE course_id = ?d AND id = ?d',
        $course_id, $unit)->title;
    $navigation[] = ['url' => "index.php?course=$course_code&amp;id=$unit", 'name' => q($unit_name)];
} else {
    $navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langExercices];
}

# is this an AJAX request to check grades?
$checking = false;
$ajax_regrade = false;

// picture path
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');

$user = null;
if (isset($_GET['eurId'])) {
    $eurid = $_GET['eurId'];
    $exercise_user_record = Database::get()->querySingle("SELECT *, DATE_FORMAT(record_start_date, '%Y-%m-%d %H:%i') AS record_start_date,
                                                                      TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration
                                                                    FROM exercise_user_record WHERE eurid = ?d", $eurid);
    $exercise_question_ids = Database::get()->queryArray("SELECT DISTINCT question_id, q_position FROM exercise_answer_record WHERE eurid = ?d ORDER BY q_position", $eurid);
    if (!$exercise_user_record) {
        // No record matches with this exercise user record id
        Session::Messages($langExerciseNotFound);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $exercise_user_record->uid);
    if (!$is_course_reviewer && $exercise_user_record->uid != $uid || $exercise_user_record->attempt_status == ATTEMPT_PAUSED) {
       // student is not allowed to view other people's exercise results
       // Nobody can see results of a paused exercise
       redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $objExercise = new Exercise();
    $objExercise->read($exercise_user_record->eid);
    if (!$unit) {
        $navigation[] = array('url' => "results.php?course=$course_code&amp;exerciseId=" . getIndirectReference($exercise_user_record->eid), 'name' => $langResults);
    }
} else {
    // exercise user recird id is not set
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

// Handle AJAX requests for course editor
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' and $is_editor) {
    if (isset($_GET['check'])) {
        $checking = true;
        header('Content-Type: application/json');
    } elseif (isset($_POST['regrade'])) {
        $ajax_regrade = true;
    } else {
        $grade = $_POST['question_grade'];
        $question_id = $_POST['question_id'];
        Database::get()->query("UPDATE exercise_answer_record
                    SET weight = ?f WHERE eurid = ?d AND question_id = ?d",
            $grade, $eurid, $question_id);
        $ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM exercise_answer_record WHERE eurid = ?d AND weight IS NULL",
            $eurid)->count;
        $totalScore = $objExercise->calculate_total_score($eurid);
        if ($ungraded == 0) {
            // if no more ungraded questions, set attempt as complete
            $attempt_status = ATTEMPT_COMPLETED;
        } else {
            $attempt_status = ATTEMPT_PENDING;
        }
        Database::get()->query('UPDATE exercise_user_record
            SET attempt_status = ?d, total_score = ?f WHERE eurid = ?d',
            $attempt_status, $totalScore, $eurid);
        $data = Database::get()->querySingle("SELECT eid, uid, total_score, total_weighting
                             FROM exercise_user_record WHERE eurid = ?d", $eurid);
        // update gradebook
        if (is_null($data->total_weighting) or $data->total_weighting == 0) {
            update_gradebook_book($data->uid, $data->eid, 0, GRADEBOOK_ACTIVITY_EXERCISE);
        } else {
            update_gradebook_book($data->uid, $data->eid, $data->total_score / $data->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE);
        }
        triggerGame($course_id, $data->uid, $data->eid);
        triggerExerciseAnalytics($course_id, $data->uid, $data->eid);
        exit();
    }
}

if ($is_editor && ($exercise_user_record->attempt_status == ATTEMPT_PENDING || $exercise_user_record->attempt_status == ATTEMPT_COMPLETED)) {
    $head_content .= "<script type='text/javascript'>
            $(document).ready(function(){
                    function save_grade(elem){
                        var grade = parseFloat($(elem).val().replace(',', '.'));
                        var element_name = $(elem).attr('name');
                        var questionId = parseInt(element_name.substring(14,element_name.length - 1));
                        var questionMaxGrade = parseFloat($(elem).next().val());
                        if (grade > questionMaxGrade) {
                            bootbox.alert('$langGradeTooBig');
                            return false;
                        } else if (isNaN(grade)){
                            $(elem).css({'border-color':'red'});
                            return false;
                        } else {
                            $.ajax({
                              type: 'POST',
                              url: '',
                              data: {question_grade: grade, question_id: questionId},
                            });
                            $(elem).parent().prev().hide();
                            $(elem).prop('disabled', true);
                            $(elem).css({'border-color':'#dfdfdf'});
                            return true;
                        }
                    }
                    $('.questionGradeBox').keyup(function (e) {
                        if (e.keyCode == 13) {
                            save_grade(this);
                            var countnotgraded = $('input.questionGradeBox').not(':disabled').length;
                            if (countnotgraded == 0) {
                                $('a#submitButton').hide();
                                $('a#all').hide();
                                $('a#ungraded').hide();
                                $('table.graded').show('slow');
                            }
                        }
                    });
                    if ($('*').hasClass('questionGradeBox')) {
                        $('a#submitButton').show();
                    } else {
                        $('a#submitButton').hide();
                    }
                    $('a#submitButton').click(function(e){
                        e.preventDefault();
                        var success = true;
                        $('.questionGradeBox').each(function() {
                           success = save_grade(this);
                        });
                        if (success) {
                            $('a#submitButton').removeClass('btn-primary').addClass('btn-success');
                            $('#text_submit').text('".js_escape($langGradebookLimit)."');
                        }
                    });
                    $('a#ungraded').click(function(e){
                        e.preventDefault();
                        $('a#all').removeClass('btn-primary').addClass('btn-default');
                        $(this).removeClass('btn-default').addClass('btn-primary');
                        $('table.graded').hide('slow');
                    });
                    $('a#all').click(function(e){
                        e.preventDefault();
                        $('a#ungraded').removeClass('btn-primary').addClass('btn-default');
                        $(this).removeClass('btn-default').addClass('btn-primary');
                        $('table.graded').show('slow');
                    });
                });
                </script>";
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = mathfilter(nl2br(make_clickable($objExercise->selectDescription())), 12, "../../courses/mathimg/");
$displayResults = $objExercise->selectResults();
$exerciseRange = $objExercise->selectRange();
$canonical_score = $objExercise->canonicalize_exercise_score($exercise_user_record->total_score, $exercise_user_record->total_weighting);
$displayScore = $objExercise->selectScore();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$calc_grade_method = $objExercise->getCalcGradeMethod();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exercise_user_record->eid, $uid)->count;

$cur_date = new DateTime("now");
if (!is_null($objExercise->selectEndDate())) {
    $end_date = new DateTime($objExercise->selectEndDate());
} else {
    $end_date = null;
}

$showResults = $displayResults == 1
               || $is_course_reviewer
               || $displayResults == 3 && $exerciseAttemptsAllowed == $userAttempts
               || $displayResults == 4 && $end_date < $cur_date;

$showScore = $displayScore == 1
            || $is_course_reviewer
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;

$toolName = $langExercicesResult;

if (!isset($_GET['pdf'])) {
    if ($unit) {
        $tool_content .= action_bar([
            [
                'title' => $langDumpPDF,
                'url' => "../units/view.php?course=$course_code&res_type=exercise_results&eurId=$eurid&unit=$unit&pdf=true",
                'icon' => 'fa-file-pdf-o',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ],
            [
                'title' => $langBack,
                'url' => "../units/index.php?course=$course_code&id=$unit",
                'icon' => 'fa fa-reply',
                'level' => 'primary-label'
            ]
        ]);
    } else {
        $tool_content .= action_bar([
            [
                'title' => $langDumpPDF,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&eurId=$eurid&pdf=true",
                'icon' => 'fa-file-pdf-o',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ],
            [
                'title' => $langBack,
                'url' => "results.php?course=$course_code&exerciseId=" . getIndirectReference($exercise_user_record->eid) . "'",
                'icon' => 'fa fa-reply',
                'level' => 'primary-label'
            ]
        ]);
    }
}

$tool_content .= "<div class='alert alert-info text-center'>";
if ($user) { // user details
    $tool_content .= "<span class='panel-title'>" . q($user->surname) . " " . q($user->givenname);
    if ($user->am) {
        $tool_content .= " ($langAmShort: " . q($user->am) . ")";
    }
    $tool_content .= "</span>";
}

$message_range = '';
$canonicalized_message_range = "<strong><span>$exercise_user_record->total_score</span> / $exercise_user_record->total_weighting</strong>";
if ($exerciseRange > 0) { // exercise grade range (if any)
    $canonicalized_message_range = "<strong><span>$canonical_score</span> / $exerciseRange</strong>";
    $message_range = "<small> (<strong>$exercise_user_record->total_score / $exercise_user_record->total_weighting</strong>)</small>";
}

if ($showScore) {
    $tool_content .= "<h4 class='panel-title' style='margin-top: 10px;'>$langTotalScore: $canonicalized_message_range&nbsp;&nbsp;$message_range</h4>";
}
$tool_content .= "
    <h5 style='margin-top: 20px;'>$langStart: <em>" . format_locale_date(strtotime($exercise_user_record->record_start_date), 'short') . "</em>
    $langDuration: <em>" . format_time_duration($exercise_user_record->time_duration) . "</em></h5>" .
    ($user && $exerciseAttemptsAllowed ? "<h5>$langAttempt: <em>{$exercise_user_record->attempt}</em></h5>" : '') . "
  </div>";

$tool_content .= "<div class='panel panel-default'>
                      <div class='panel-heading'>
                            <h3 class='panel-title'>" . q_math($exerciseTitle) . "</h3>
                      </div>";

if (!empty($exerciseDescription)) {
    $tool_content .= "<div class='panel-body'>$exerciseDescription</div>";
}

$tool_content .= "</div>";

$tool_content .= "<div class='row margin-bottom-fat'>
    <div class='col-md-5 col-md-offset-7'>";
if ($is_editor && $exercise_user_record->attempt_status == ATTEMPT_PENDING) {
    $tool_content .= "
            <div class='btn-group btn-group-sm' style='float:right;'>
                <a class='btn btn-primary' id='all'>$langAllExercises</a>
                <a class='btn btn-default' id='ungraded'>$langAttemptPending</a>
            </div>";
}
$tool_content .= "
    </div>
  </div>";


if ($is_editor and in_array($exercise_user_record->attempt_status, [ATTEMPT_COMPLETED, ATTEMPT_PENDING]) and isset($_POST['regrade'])) {
    $regrade = true;
} else {
    $regrade = false;
}

$totalWeighting = $totalScore = 0;
$i = 1;

if (count($exercise_question_ids) > 0) {
    // for each question
    foreach ($exercise_question_ids as $row) {
        if (!$showResults) {
            $tool_content .= "<div class='text-center alert alert-info'>$langExerciseCompleted</div>";
            break;
        }
        // creates a temporary Question object
        $objQuestionTmp = new Question();
        $is_question = $objQuestionTmp->read($row->question_id);
        if (!$is_question) { // no question found
            continue;
        }
        // gets the student choice for this question
        $choice = $objQuestionTmp->get_answers_record($eurid);
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        $questionFeedback = $objQuestionTmp->selectFeedback();
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $answerType = $objQuestionTmp->selectType();
        $questionType = $objQuestionTmp->selectTypeLegend($answerType);

        // destruction of the Question object
        unset($objQuestionTmp);
        // check if question has been graded
        $question_weight = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE question_id = ?d AND eurid =?d", $row->question_id, $eurid)->weight;
        $question_graded = is_null($question_weight) ? FALSE : TRUE;

        $tool_content .= "<div class='panel'>";
        $tool_content .= "<div class='panel-body'>";
        $tool_content .= "
            <table class='table ".(($question_graded)? 'graded' : 'ungraded')."'>
            <tr class='active'>
              <td colspan='2'>
                <strong><u>$langQuestion</u>: $i</strong>";

        if ($answerType == FREE_TEXT) {
            $choice = purify($choice);
            if (!empty($choice)) {
                if (!$question_graded) {
                    $tool_content .= " <small>(<span class='text-danger'>$langAnswerUngraded</span>) </small>";
                } else {
                    $tool_content .= " <small>($langGradebookGrade: <strong>$question_weight</strong></span>)</small>";
                }
            }
        } else {
             if (($showScore) and (!is_null($choice))) {
                 if ($answerType == MULTIPLE_ANSWER && $question_weight < 0 && $calc_grade_method == 1) {
                     $qw_legend1 = "<span style='color: red;'>$question_weight</span>";
                     $qw_legend2 = " $langConvertedTo <strong>0 / $questionWeighting</strong>";
                 } else {
                     $qw_legend1 = "$question_weight";
                     $qw_legend2 = "";
                 }
                 $tool_content .= " <small>($langGradebookGrade: <strong>$qw_legend1 / $questionWeighting</strong></span>$qw_legend2)</small>";
             }
        }
        $tool_content .= "<small class='help-block'>($questionType)</small>"; // question type
        $tool_content .= "</td></tr>";

        $tool_content .= "<tr><td colspan='2'>";
        $tool_content .= "<strong>" . q_math($questionName) . "</strong><br>" . standard_text_escape($questionDescription);
        if (file_exists($picturePath . '/quiz-' . $row->question_id)) {
            $tool_content .= "<div style='padding: 20px;' class='text-center'>
                                <img src='../../$picturePath/quiz-" . $row->question_id . "'>
                              </div>";
        }

        $tool_content .= "</td></tr>";

        if (!is_null($choice)) {
            $tool_content .= "<tr class='active'><th colspan='2'><u>$langAnswer</u></th></tr>";
        }

        $questionScore = 0;
        if ($answerType != FREE_TEXT) { // if NOT FREE TEXT (i.e. question has answers)
            // construction of the Answer object
            $objAnswerTmp = new Answer($row->question_id);
            $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

            for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                $answer = $objAnswerTmp->selectAnswer($answerId);
                $answerComment = standard_text_escape($objAnswerTmp->selectComment($answerId));
                $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

                if ($answerType == FILL_IN_BLANKS or $answerType == FILL_IN_BLANKS_TOLERANT) {
                    list($answer, $answerWeighting) = Question::blanksSplitAnswer($answer);
                } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                    $answer_array = unserialize($answer);
                }  else {
                    $answer = standard_text_escape($answer);
                }
                $grade = 0;
                switch ($answerType) {

                    case TRUE_FALSE:
                    case UNIQUE_ANSWER : $studentChoice = ($choice == $answerId) ? 1 : 0;
                        if ($studentChoice) {
                            $questionScore += $answerWeighting;
                            $grade = $answerWeighting;
                        }
                        break;

                    case MULTIPLE_ANSWER : $studentChoice = @$choice[$answerId];
                        if ($studentChoice) {
                            $questionScore += $answerWeighting;
                            $grade = $answerWeighting;
                        }
                        break;

                    case FILL_IN_BLANKS :
                    case FILL_IN_BLANKS_TOLERANT :
                        // splits weightings that are joined with a comma
                        $answerWeighting = explode(',', $answerWeighting);
                        // we save the answer because it will be modified
                        $temp = $answer;
                        $answer = '';
                        $j = 1;
                        // the loop will stop at the end of the text
                        while (1) {
                            // quits the loop if there are no more blanks
                            if (($pos = strpos($temp, '[')) === false) {
                                // adds the end of the text
                                $answer .= q($temp);
                                break;
                            }
                            // adds the piece of text that is before the blank and ended by [
                            $answer .= substr($temp, 0, $pos + 1);
                            $temp = substr($temp, $pos + 1);
                            // quits the loop if there are no more blanks
                            if (($pos = strpos($temp, ']')) === false) {
                                // adds the end of the text
                                $answer .= q($temp);
                                break;
                            }
                            $choice[$j] = canonicalize_whitespace($choice[$j]);
                            // if the word entered is the same as the one defined by the professor
                            $canonical_choice = $answerType == FILL_IN_BLANKS_TOLERANT ? remove_accents($choice[$j]) : $choice[$j];
                            $canonical_match = $answerType == FILL_IN_BLANKS_TOLERANT ? remove_accents(substr($temp, 0, $pos)) : substr($temp, 0, $pos);
                            $right_answers = array_map('canonicalize_whitespace',
                                preg_split('/\s*\|\s*/', $canonical_match));
                            if (in_array($canonical_choice, $right_answers)) {
                                // gives the related weighting to the student
                                $questionScore += $answerWeighting[$j-1];
                                if ($regrade) {
                                    Database::get()->query('UPDATE exercise_answer_record
                                        SET weight = ?f
                                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                                        $answerWeighting[$j-1], $eurid, $row->question_id, $j);
                                }
                                // increments total score
                                // adds the word in green at the end of the string
                                $answer .= '<strong>' . q($choice[$j]) . '</strong>';
                                if (isset($_GET['pdf'])) {
                                    $icon = "<input type='checkbox' checked='checked'>";
                                } else {
                                    $icon = "<span class='fa fa-check text-success'></span>";
                                }
                            }
                            // else if the word entered is not the same as the one defined by the professor
                            elseif ($choice[$j] !== '') {
                                 // adds the word in red at the end of the string, and strikes it
                                    $answer .= '<span class="text-danger"><s>' . q($choice[$j]) . '</s></span>';
                                    $icon = "<span class='fa fa-times text-danger'></span>";
                            } else {
                                // adds a tabulation if no word has been typed by the student
                                $answer .= '&nbsp;&nbsp;&nbsp;';
                                $icon = "<span class='fa fa-times text-danger'></span>";
                            }
                                // adds the correct word, followed by ] to close the blank
                            $answer .= ' / <span class="text-success"><strong>' .
                                preg_replace('/\s*\|\s*/', " </strong>$langOr<strong> ", q(substr($temp, 0, $pos))) .
                                '</strong></span>';
                            $answer .= "]";
                            $answer .= "&nbsp;&nbsp;$icon";
                            $j++;
                            $temp = substr($temp, $pos + 1);
                        }
                        break;
                    case FILL_IN_FROM_PREDEFINED_ANSWERS :
                        $answer = $answer_array[0]; // answer text
                        // fetch possible answers for all choices
                        preg_match_all('/\[[^]]+\]/', $answer, $out);
                        $possible_answers = [];
                        foreach ($out[0] as $output) {
                            $possible_answers[] = explode("|", str_replace(array('[',']'), ' ', q($output)));
                        }
                        $answer_string = $answer_array[1]; // answers
                        $answerWeighting = $answer_array[2]; // answer weight
                        $temp = $answer;
                        $answer = '';
                        $j = 1;
                        // the loop will stop at the end of the text
                        while (true) {
                            $answer_string = reindex_array_keys_from_one($answer_string); // start from 1
                            // quits the loop if there are no more blanks
                            if (($pos = strpos($temp, '[')) === false) {
                                // adds the end of the text
                                $answer .= q($temp);
                            }
                            // adds the piece of text that is before the blank and ended by [
                            $answer .= substr($temp, 0, $pos + 1);
                            $temp = substr($temp, $pos + 1);
                            // quits the loop if there are no more blanks
                            if (($pos = strpos($temp, ']')) === false) {
                                // adds the end of the text
                                $answer .= q($temp);
                                break;
                            }

                            $possible_answer = $possible_answers[$j-1]; // possible answers for each choice
                            $possible_answer = reindex_array_keys_from_one($possible_answer); // start from 1
                            if ($choice[$j] == $answer_string[$j]) { // correct answer
                                $questionScore += $answerWeighting[$j-1]; // weight assignment
                                if ($regrade) {
                                    Database::get()->query('UPDATE exercise_answer_record
                                        SET weight = ?f
                                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                                        $answerWeighting[$j-1], $eurid, $row->question_id, $j);
                                }
                                // adds the word in green at the end of the string
                                $answer .= '<strong>' . q($possible_answer[$choice[$j]]) . '</strong>';
                                if (isset($_GET['pdf'])) {
                                    $icon = "<input type='checkbox' checked='checked'>";
                                } else {
                                    $icon = "<span class='fa fa-check text-success'></span>";
                                }
                            }  else { // wrong answer
                                if (isset($possible_answer[$choice[$j]])) { // if we have chosen something
                                    // adds the word in red at the end of the string, and strikes it
                                    $answer_choice = '<span class="text-danger"><s>' . q($possible_answer[$choice[$j]]) . '</s></span>';
                                } else {
                                    $answer_choice =  "&nbsp;&mdash;";
                                }
                                $answer .= $answer_choice;
                                $icon = "<span class='fa fa-times text-danger'></span>";
                            }
                            // adds the correct word, followed by ] to close the blank
                            $answer .= ' / <span class="text-success"><strong>' . q($possible_answer[$answer_string[$j]]) . '</strong></span>';
                            $answer .= "]";
                            $answer .= "&nbsp;&nbsp;$icon";
                            $j++;
                            $temp = substr($temp, $pos + 1);
                        }
                        break;
                    case MATCHING : if ($answerCorrect) {
                            $thisChoice = isset($choice[$answerId])? $choice[$answerId]: null;
                            if ($answerCorrect == $thisChoice) {
                                $questionScore += $answerWeighting;
                                $grade = $answerWeighting;
                                $choice[$answerId] = q($matching[$choice[$answerId]]);
                                $icon = "<span class='fa fa-check text-success'></span>";
                                $pdf_icon = "✓";
                            } elseif (!$thisChoice) {
                                $choice[$answerId] = '<del class="text-danger">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</del>';
                                $icon = "<span class='fa fa-times text-danger'></span>";
                                $pdf_icon = "✓";
                            } else {
                                $choice[$answerId] = "<span class='text-danger'><del>" .
                                    q($matching[$choice[$answerId]]) . "</del></span>";
                                $icon = "<span class='fa fa-times text-danger'></span>";
                                $pdf_icon = "✓";
                            }
                        } else {
                            $icon = '';
                            $matching[$answerId] = $answer;
                        }
                        if ($regrade) {
                            Database::get()->query('UPDATE exercise_answer_record
                                SET weight = ?f
                                WHERE eurid = ?d AND question_id = ?d AND answer = ?d',
                                $grade, $eurid, $row->question_id, $answerId);
                        }
                        break;

                } // end switch()

                if ($regrade and !in_array($answerType, [FILL_IN_BLANKS_TOLERANT, FILL_IN_BLANKS, FILL_IN_FROM_PREDEFINED_ANSWERS, MATCHING])) {
                    Database::get()->query('UPDATE exercise_answer_record
                        SET weight = ?f
                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                        $grade, $eurid, $row->question_id, $answerId);
                }

                if ($answerType != MATCHING || $answerCorrect) {
                    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                        $tool_content .= "<tr><td width='100' class='text-center'>";
                        $answer_icon  = '';
                        if ($studentChoice) {
                            $student_choice_icon = "fa fa-fw fa-check-square-o help-block";
                            $pdf_student_choice_icon = "<input type='checkbox' checked='checked'>";
                            $style = '';
                            if ($answerCorrect) {
                                $answer_icon = "fa fa-check text-success";
                            } else {
                                $answer_icon = "fa fa-times text-danger";
                            }
                        } else {
                            $student_choice_icon = "fa fa-fw fa-square-o help-block";
                            $pdf_student_choice_icon = "<input type='checkbox'>";
                            $style = "visibility: hidden;";
                        }
                        if (isset($_GET['pdf'])) {
                            $tool_content .= "<span>$pdf_student_choice_icon</span>";
                        } else {
                            $tool_content .= "<span class='$student_choice_icon'></span>&nbsp;&nbsp;";
                            $tool_content .= "<span style='$style' class='$answer_icon'></span>";
                        }

                        $tool_content .= "</td>";
                        $tool_content .= "<td>" . standard_text_escape($answer);
                        if ($answerCorrect) {
                            $tool_content .= "&nbsp;<span class='text-success'><small>($langCorrectS)</small></span>";
                        } else {
                            $tool_content .= "&nbsp;<span class='text-danger'><small>($langIncorrectS)</small></span>";
                        }
                        if ($studentChoice or $answerCorrect) {
                            $tool_content .= "<small><span class='help-block'>" . standard_text_escape(nl2br(make_clickable($answerComment))) ."</span></small>";
                        }
                        $tool_content .= "</td>";
                        $tool_content .= "</tr>";
                    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                        $tool_content .= "<tr><td>" . standard_text_escape(nl2br($answer)) . "</td></tr>";
                    } elseif ($answerType == MATCHING) {
                        $tool_content .= "<tr><td>" . q($answer) . "</td>";
                        $tool_content .= "<td>" . $choice[$answerId];
                        $tool_content .= " / <span class='text-success'><strong>" . q($matching[$answerCorrect]). "</strong></span>&nbsp;&nbsp;$icon";
                        $tool_content .= "</td></tr>";
                    }
                }
            } // end for()
        } else { // If FREE TEXT type
            $questionScore = $question_weight;
            $tool_content .= "<tr><td>" . purify($choice) . "</td></tr>";
        }

        if (!is_null($questionFeedback)) {
            $tool_content .= "<tr style='background-color:#fffbe3'><td colspan='2'>";
            $tool_content .= "<div style='margin-top: 10px;'><strong>$langQuestionFeedback:</strong><br>" . standard_text_escape($questionFeedback) . "</div>";
            $tool_content .= "</td></tr>";
        }

        if ($showScore) {
            if (!is_null($choice)) {
                if ($answerType == FREE_TEXT && $is_editor) {
                    if (isset($question_graded) && !$question_graded) {
                        $value = '';
                    } else {
                        $value = round($questionScore, 2);
                    }
                    $tool_content .= "<tr><th colspan='2'>";
                    $tool_content .= "<span style='float:right;'>
                                   $langQuestionScore: <input style='display:inline-block;width:auto;' type='text' class='questionGradeBox' maxlength='6' size='6' name='questionScore[$row->question_id]' value='$value'>
                                   <input type='hidden' name='questionMaxGrade' value='$questionWeighting'>
                                   <strong>/$questionWeighting</strong>
                                    </span>";
                    $tool_content .= "</th></tr>";
                }
            }
        }

        if ($answerType == MULTIPLE_ANSWER and $questionScore < 0) {
            $questionScore = 0;
        }
        $rounded_weight = is_null($question_weight)? 0: round($question_weight, 2);

        if ($rounded_weight < 0 and $answerType == MULTIPLE_ANSWER) {
            $rounded_weight = 0;
        }
        $rounded_score = is_null($question_weight)? 0: round($questionScore, 2);
        if ($showScore and $rounded_weight != $rounded_score) {
            $tool_content .= "<tr class='warning'>
                                <th colspan='2' class='text-right'>
                                    $langQuestionStoredScore: $rounded_weight / $questionWeighting
                                </th>
                              </tr>";
        }

        $tool_content .= "</table>";
        $tool_content .= "</div></div>";

        $totalScore += $questionScore;
        $totalWeighting += $questionWeighting;

        // destruction of Answer
        unset($objAnswerTmp);
        $i++;
    } // end foreach()
} else {
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

if ($totalScore < 0) {
    $totalScore = 0;
}

if ($regrade) {
    $totalScore = $objExercise->calculate_total_score($eurid);

    Database::get()->query('UPDATE exercise_user_record
        SET total_score = ?f, total_weighting = ?f
        WHERE eurid = ?d', $totalScore, $totalWeighting, $eurid);
    update_gradebook_book($exercise_user_record->uid,
        $exercise_user_record->eid, $totalScore / $totalWeighting, GRADEBOOK_ACTIVITY_EXERCISE);

    // find all duplicate wrong entries (for questions with type `unique answer)
    $wrong_data = Database::get()->queryArray("SELECT question_id FROM exercise_answer_record
                                            JOIN exercise_question
                                                ON question_id = id
                                                AND `type` = " . UNIQUE_ANSWER . "
                                                AND eurid = ?d
                                            GROUP BY eurid, question_id, answer_id
                                            HAVING COUNT(question_id) > 1", $eurid);
    // delete all duplicate entries
    foreach ($wrong_data as $d) {
        $max_arid = Database::get()->querySingle("SELECT MAX(answer_record_id) AS max_arid FROM exercise_answer_record WHERE eurid=?d AND question_id=?d", $eurid, $d)->max_arid;
        Database::get()->querySingle("DELETE FROM exercise_answer_record WHERE eurid=?d AND question_id=?d AND answer_record_id != ?d", $eurid, $d, $max_arid);
    }
    Session::Messages($langNewScoreRecorded, 'alert-success');
    if ($ajax_regrade) {
        echo json_encode(['result' => 'ok']);
        exit;
    } else {
        redirect_to_home_page("modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid");
    }
}

$totalScore = round($totalScore, 2);
$totalWeighting = round($totalWeighting, 2);
$oldScore = round($exercise_user_record->total_score, 2);
$oldWeighting = round($exercise_user_record->total_weighting, 2);

if ($is_editor and ($totalScore != $oldScore or $totalWeighting != $oldWeighting)) {
    if ($checking) {
        if ($user) {
            echo json_encode(['result' => 'regrade', 'eurid' => $eurid,
                'title' => "$user->surname $user->givenname (" . $exercise_user_record->record_start_date . ')',
                'url' => $urlAppend . "modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid"],
                JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['result' => 'regrade', 'eurid' => $eurid,
                'title' => "$langNoGroupStudents (" . $exercise_user_record->record_start_date . ')',
                'url' => $urlAppend . "modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid"],
                JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else {
        Session::Messages($langScoreDiffers .
            "<form action='exercise_result.php?course=$course_code&amp;eurId=$eurid' method='post'>
                <button class='btn btn-default' type='submit' name='regrade' value='true'>$langRegrade</button>
             </form>", 'alert-warning');
    }
}

if ($checking) {
    echo json_encode(['result' => 'ok']);
    exit;
}

if (!isset($_GET['pdf']) and $is_editor) {
    $tool_content .= "<div class='text-center'><a class='btn btn-primary' href='index.php' id='submitButton'><span id='text_submit'>$langSubmit</span></a></div>";
}

if (isset($_GET['pdf'])) {
    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName - $langExercicesResult") . "</title>
          <style>
            * { font-family: 'opensans'; }
            body { font-family: 'opensans'; font-size: 10pt; }
            small, .small { font-size: 8pt; }
            h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
            h1 { font-size: 16pt; }
            h2 { font-size: 12pt; border-bottom: 1px solid black; }
            h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
            th { text-align: left; border-bottom: 1px solid #999; }
            td { text-align: left; }
          </style>
        </head>
        <body>" . get_platform_logo() .
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($langExercicesResult) . "</h2>";

    $pdf_content .= $tool_content;
    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/default/fonts' ]),
        'fontdata' => $fontData + [
                'opensans' => [
                    'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                    'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                    'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                    'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                ],
                'roboto' => [
                    'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                    'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                ]
            ]
    ]);

    $mpdf->setFooter('{DATE j-n-Y} || {PAGENO} / {nb}');
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code exercise_results.pdf", 'I'); // 'D' or 'I' for download / inline display
} else {
    draw($tool_content, 2, null, $head_content);
}
