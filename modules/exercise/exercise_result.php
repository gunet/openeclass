<?php
/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
require_once 'include/lib/textLib.inc.php';
require_once 'modules/gradebook/functions.php';
require_once 'game.php';
require_once 'analytics.php';

$pageName = $langExercicesResult;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

# is this an AJAX request to check grades?
$checking = false;
$ajax_regrade = false;

// picture path
$picturePath = "courses/$course_code/image";
// Identifying ajax request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {
    if (isset($_GET['check'])) {
        $checking = true;
        header('Content-Type: application/json');
    } elseif (isset($_POST['regrade'])) {
        $ajax_regrade = true;
    } else {
        $grade = $_POST['question_grade'];
        $question_id = $_POST['question_id'];
        $eurid = $_GET['eurId'];
        Database::get()->query("UPDATE exercise_answer_record
                    SET weight = ?f WHERE eurid = ?d AND question_id = ?d",
            $grade, $eurid, $question_id);
        $ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM exercise_answer_record WHERE eurid = ?d AND weight IS NULL",
            $eurid)->count;
        if ($ungraded == 0) {
            // if no more ungraded questions, set attempt as complete and
            // recalculate sum of grades
            Database::get()->query("UPDATE exercise_user_record
                SET attempt_status = ?d,
                    total_score = (SELECT SUM(weight) FROM exercise_answer_record
                                        WHERE eurid = ?d)
                WHERE eurid = ?d",
                ATTEMPT_COMPLETED, $eurid, $eurid);
        } else {
            // else increment total by just this grade
            Database::get()->query("UPDATE exercise_user_record
                SET total_score = total_score + ?f WHERE eurid = ?d",
                $grade, $eurid);
        }
        $data = Database::get()->querySingle("SELECT eid, uid, total_score, total_weighting
                             FROM exercise_user_record WHERE eurid = ?d", $eurid);
            // update gradebook
        update_gradebook_book($data->uid, $data->eid, $data->total_score/$data->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE);
        triggerGame($course_id, $uid, $data->eid);
        triggerExerciseAnalytics($course_id, $uid, $data->eid);
        exit();
    }
}

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');

if (isset($_GET['eurId'])) {
    $eurid = $_GET['eurId'];
    $exercise_user_record = Database::get()->querySingle("SELECT *, DATE_FORMAT(record_start_date, '%Y-%m-%d %H:%i') AS record_start_date,
                                                                      TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration 
                                                                    FROM exercise_user_record WHERE eurid = ?d", $eurid);
    $exercise_question_ids = Database::get()->queryArray("SELECT DISTINCT question_id
                                                        FROM exercise_answer_record WHERE eurid = ?d", $eurid);
    $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $exercise_user_record->uid);
    if (!$exercise_user_record) {
        // No record matches with this exercise user record id
        Session::Messages($langExerciseNotFound);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    if (!$is_editor && $exercise_user_record->uid != $uid || $exercise_user_record->attempt_status == ATTEMPT_PAUSED) {
       // student is not allowed to view other people's exercise results
       // Nobody can see results of a paused exercise
       redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $objExercise = new Exercise();
    $objExercise->read($exercise_user_record->eid);
} else {
    // exercise user recird id is not set
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
if ($is_editor && ($exercise_user_record->attempt_status == ATTEMPT_PENDING || $exercise_user_record->attempt_status == ATTEMPT_COMPLETED)) {
    $head_content .= "<script type='text/javascript'>
            $(document).ready(function(){
                    function save_grade(elem){
                        var grade = parseFloat($(elem).val());
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
                            var prev_grade = parseInt($('span#total_score').html());
                            var updated_grade = prev_grade + grade;
                            $('span#total_score').html(updated_grade);
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
                    $('a#submitButton').click(function(e){
                        e.preventDefault();
                        var success = true;
                        $('.questionGradeBox').each(function() {
                           success = save_grade(this);
                        });
                        if (success) {
                         $(this).parent().hide();
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
$displayScore = $objExercise->selectScore();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exercise_user_record->eid, $uid)->count;

$cur_date = new DateTime("now");
$end_date = new DateTime($objExercise->selectEndDate());

$showResults = $displayResults == 1
               || $is_editor
               || $displayResults == 3 && $exerciseAttemptsAllowed == $userAttempts
               || $displayResults == 4 && $end_date < $cur_date;

$showScore = $displayScore == 1
            || $is_editor
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;

if (isset($user)) { // user details
    $tool_content .= "<div class='alert alert-info'>" . q($user->surname) . " " . q($user->givenname);
    if ($user->am) {
        $tool_content .= "($langAm:" . q($user->am) . ")";
    }
    if ($showScore) {
        $tool_content .= "<h5>$langYourTotalScore: <span id='total_score'><strong>$exercise_user_record->total_score</span> / $exercise_user_record->total_weighting</strong></h5>";
    }
    $tool_content .= "<h5>$langStart: <em>" . nice_format($exercise_user_record->record_start_date, true) . "</em></h5>";
    $tool_content .= "<h5>$langDuration: <em>" . format_time_duration($exercise_user_record->time_duration) . "</em></h5>";
    /*if ($exerciseAttemptsAllowed > 0) {
        $tool_content .= "<h5>$langAttempt: <em>$exerciseAttemptsAllowed</em></h5>";
    }*/
    $tool_content .= "</div>";
}

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


if ($is_editor and $exercise_user_record->attempt_status == ATTEMPT_COMPLETED and isset($_POST['regrade'])) {
    $regrade = true;
} else {
    $regrade = false;
}

$totalWeighting = $totalScore = 0;
$i = 1;

if (count($exercise_question_ids) > 0) {
    // for each question
    foreach ($exercise_question_ids as $row) {
        // creates a temporary Question object
        $objQuestionTmp = new Question();
        $is_question = $objQuestionTmp->read($row->question_id);
        // gets the student choice for this question
        $choice = $objQuestionTmp->get_answers_record($eurid);
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $answerType = $objQuestionTmp->selectType();
        $questionType = $objQuestionTmp->selectTypeWord($answerType);

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
                    $tool_content .= " <small>(<span class='text-danger'>$langAnswerUngraded</span>) </small";
                } else {
                    $tool_content .= " <small>($langGradebookGrade: <strong>$question_weight</strong></span>)</small>";
                }
            }
        } else {
             if (($showScore) and (!is_null($choice)) and (!$is_editor)) {
                 $tool_content .= " <small>($langGradebookGrade: <strong>$question_weight</strong></span>)</small>";
             }
         }
        $tool_content .= "<small class='help-block'>($questionType)</small>"; // question type
        $tool_content .= "</td></tr>";

        $tool_content .= "<tr><td colspan='2'>";
        if ($is_question) {
            $tool_content .= "<strong>" . q_math($questionName) . "</strong>
                <br>" .
                standard_text_escape($questionDescription);
                if (file_exists($picturePath . '/quiz-' . $row->question_id)) {
                    $tool_content .= "<div style='padding: 20px;' class='text-center'>
                                        <img src='../../$picturePath/quiz-" . $row->question_id . "'>
                                      </div>";
                }
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langQuestionAlreadyDeleted</div>";
        }
        $tool_content .= "</td></tr>";

        if ($showResults && !is_null($choice)) {
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
                } else {
                    $answer = standard_text_escape($answer);
                }
                $grade = 0;
                switch ($answerType) {
                    // for unique answer
                    case UNIQUE_ANSWER : $studentChoice = ($choice == $answerId) ? 1 : 0;
                        if ($studentChoice) {
                            $questionScore += $answerWeighting;
                            $grade = $answerWeighting;
                        }
                        break;
                    // for multiple answers
                    case MULTIPLE_ANSWER : $studentChoice = @$choice[$answerId];
                        if ($studentChoice) {
                            $questionScore += $answerWeighting;
                            $grade = $answerWeighting;
                        }
                        break;
                    // for fill in the blanks
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
                            $canonical_choice = $answerType == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper($choice[$j], 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : $choice[$j];
                            $canonical_match = $answerType == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper(substr($temp, 0, $pos), 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : substr($temp, 0, $pos);
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
                                $icon = "<span class='fa fa-check text-success'></span>";
                            }
                            // else if the word entered is not the same as the one defined by the professor
                            elseif ($choice[$j] !== '') {
                                if ($showResults) { // adds the word in red at the end of the string, and strikes it
                                    $answer .= '<span class="text-danger"><s>' . q($choice[$j]) . '</s></span>';
                                    $icon = "<span class='fa fa-times text-danger'></span>";
                                }  else {
                                    $answer .= '<strong>' . q($choice[$j]) . '</strong>';
                                }
                            } else {
                                // adds a tabulation if no word has been typed by the student
                                $answer .= '&nbsp;&nbsp;&nbsp;';
                                $icon = "<span class='fa fa-times text-danger'></span>";
                            }
                            if ($showResults) { // adds the correct word, followed by ] to close the blank
                                $answer .= ' / <span class="text-success"><strong>' .
                                    q(preg_replace('/\s*,\s*/', " $langOr ", substr($temp, 0, $pos))) .
                                    '</strong></span>';
                            }
                            $answer .= "]";
                            if ($showResults) {
                                $answer .= "&nbsp;&nbsp;$icon";
                            }
                            $j++;
                            $temp = substr($temp, $pos + 1);
                        }
                        break;
                    // for matching
                    case MATCHING : if ($answerCorrect) {
                            $thisChoice = isset($choice[$answerId])? $choice[$answerId]: null;
                            if ($answerCorrect == $thisChoice) {
                                $questionScore += $answerWeighting;
                                $grade = $answerWeighting;
                                $choice[$answerId] = $matching[$choice[$answerId]];
                                $icon = "<span class='fa fa-check text-success'></span>";
                            } elseif (!$thisChoice) {
                                $choice[$answerId] = '&nbsp;&nbsp;&nbsp;';
                            } else {
                                if ($showResults) {
                                    $choice[$answerId] = "<span class='text-danger'><del>" .
                                        $matching[$choice[$answerId]] . "</del></span>";
                                    $icon = "<span class='fa fa-times text-danger'></span>";
                                } else {
                                    $choice[$answerId] = $matching[$choice[$answerId]];
                                }
                            }
                        } else {
                            $matching[$answerId] = $answer;
                        }
                        if ($regrade) {
                            Database::get()->query('UPDATE exercise_answer_record
                                SET weight = ?f
                                WHERE eurid = ?d AND question_id = ?d AND answer = ?d',
                                $grade, $eurid, $row->question_id, $answerId);
                        }
                        break;
                    case TRUE_FALSE : $studentChoice = ($choice == $answerId) ? 1 : 0;
                        if ($studentChoice) {
                            $questionScore += $answerWeighting;
                            $grade = $answerWeighting;
                        }
                        break;
                } // end switch()

                if ($regrade and !in_array($answerType, [FILL_IN_BLANKS_TOLERANT, FILL_IN_BLANKS, MATCHING])) {
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
                            $style = '';
                            if ($answerCorrect) {
                                $answer_icon = "fa fa-check text-success";
                            } else {
                                $answer_icon = "fa fa-times text-danger";
                            }
                        } else {
                            $student_choice_icon = "fa fa-fw fa-square-o help-block";
                            $style = "visibility: hidden;";
                        }
                        $tool_content .= "<span class='$student_choice_icon'></span>&nbsp;&nbsp;";

                        if ($showResults) {
                            $tool_content .= "<span style='$style' class='$answer_icon'></span>";
                        }
                        $tool_content .= "</td>";
                        $tool_content .= "<td>" . standard_text_escape($answer);
                        if ($showResults) {
                            if ($answerCorrect) {
                                $tool_content .= "&nbsp;<span class='text-success'><small>($langCorrectS)</small></span>";
                            } else {
                                $tool_content .= "&nbsp;<span class='text-danger'><small>($langIncorrectS)</small></span>";
                            }
                            if ($studentChoice or $answerCorrect) {
                                $tool_content .= "<small><span class='help-block'>" . standard_text_escape(nl2br(make_clickable($answerComment))) ."</span></small>";
                            }
                        }
                        $tool_content .= "</td>";
                        $tool_content .= "</tr>";
                    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                        $tool_content .= "<tr><td>" . standard_text_escape(nl2br($answer)) . "</td></tr>";
                    } else { // matching
                        $tool_content .= "<tr><td>" . standard_text_escape($answer) . "</td>";
                        $tool_content .= "<td>" . $choice[$answerId] . "";
                        if ($showResults) {
                            $tool_content .= " / <span class='text-success'><strong>" . $matching[$answerCorrect] . "</strong></span>&nbsp;&nbsp;$icon";
                        }
                       $tool_content .= "</td></tr>";
                    }
                }

            } // end for()
        } else { // If FREE TEXT type
            $tool_content .= "<tr class='even'><td>" . purify($choice) . "</td></tr>";
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
                                   $langQuestionScore: <input style='display:inline-block;width:auto;' type='text' class='questionGradeBox' maxlength='3' size='3' name='questionScore[$row->question_id]' value='$value'>
                                   <input type='hidden' name='questionMaxGrade' value='$questionWeighting'>
                                   <strong>/$questionWeighting</strong>
                                    </span>";
                    $tool_content .= "</th></tr>";
                }
            }
        }

        if ($showScore and $question_weight != $questionScore) {
            $tool_content .= "<tr class='warning'>
                                <th colspan='2' class='text-right'>
                                    $langQuestionStoredScore: " . round($question_weight, 2) . " / $questionWeighting
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

if ($regrade) {
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

if ($is_editor and ($totalScore != $exercise_user_record->total_score or $totalWeighting != $exercise_user_record->total_weighting)) {
    if ($checking) {
        echo json_encode(['result' => 'regrade', 'eurid' => $eurid,
            'title' => "$user->surname $user->givenname (" .
                       $exercise_user_record->record_start_date . ')',
            'url' => $urlAppend . "modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid"],
            JSON_UNESCAPED_UNICODE);
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

$tool_content .= "
  <div class='text-center'>";
    if ($is_editor && ($exercise_user_record->attempt_status == ATTEMPT_PENDING || $exercise_user_record->attempt_status == ATTEMPT_COMPLETED)) {
        $tool_content .= "<a class='btn btn-primary' href='index.php' id='submitButton'>$langSubmit</a>";
    }
    if (isset($_REQUEST['unit'])) {
        $tool_content .= "<a class='btn btn-default' href='../units/index.php?course=$course_code&id=$_REQUEST[unit]'>$langBack</a>";
    } else {
        $tool_content .= "<a class='btn btn-default' href='index.php?course=$course_code'>$langBack</a>";
    }

$tool_content .= "</div>";

draw($tool_content, 2, null, $head_content);
