<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */
include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('userRecord.class.php');
$require_current_course = TRUE;
$guest_allowed = true;
include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'modules/gradebook/functions.php';
require_once 'game.php';

$pageName = $langExercicesResult;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

// picture path
$data['picturePath'] = $picturePath = "courses/$course_code/image";
//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {
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
        // if no more ungraded quastions, set attempt as complete and
        // recalculate sum of grades
        Database::get()->query("UPDATE exercise_user_record
            SET attempt_status = ?d,
                total_score = (SELECT SUM(weight) FROM exercise_answer_record
                                    WHERE eurid = ?d)
            WHERE eurid = ?d",
            ATTEMPT_COMPLETED, $eurid, $eurid);
        $data = Database::get()->querySingle("SELECT eid, uid, total_score, total_weighting FROM exercise_user_record WHERE eurid = ?d", $eurid);
        // update gradebook            
        update_gradebook_book($data->uid, $data->eid, $data->total_score/$data->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE);
    } else {
        // else increment total by just this grade
        Database::get()->query("UPDATE exercise_user_record
            SET total_score = total_score + ?f WHERE eurid = ?d",
            $grade, $eurid);
    }
    $eur = Database::get()->querySingle("SELECT * FROM exercise_user_record WHERE eurid = ?d", $eurid);
    triggerGame($course_id, $uid, $eur->eid);
    exit();
}
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');

if (isset($_GET['eurId'])) {
    $data['eurid'] = $eurid = $_GET['eurId'];
    $exercise_user_record = new userRecord();
    $exercise_user_record->find($eurid);
    $data['exercise_user_record'] = $exercise_user_record;
    $exercise_question_ids = Database::get()->queryArray("SELECT DISTINCT question_id, answer_record_id
                                                         FROM exercise_answer_record WHERE eurid = ?d
                                                         ORDER BY answer_record_id", $eurid);
    $data['user'] = $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $exercise_user_record->uid);
    if (!$exercise_user_record) {
        //No record matches with this exercise user record id
        Session::Messages($langExerciseNotFound);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    if (!$is_editor && $exercise_user_record->uid != $uid || $exercise_user_record->attempt_status == ATTEMPT_PAUSED) {
       // student is not allowed to view other people's exercise results
       // Nobody can see results of a paused exercise       
       redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $exercise = $exercise_user_record->exercise;
} else {
    //exercise user recird id is not set
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
if ($is_editor && $exercise_user_record->attempt_status == ATTEMPT_PENDING) {
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
                            var prev_grade = parseFloat($('span#total_score').html());
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
$data['exercise'] = $exercise;

$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exercise_user_record->eid, $uid)->count;

$cur_date = new DateTime("now");
$end_date = new DateTime($exercise->selectEndDate());

$data['showResults'] = $showResults = $exercise->results == 1
               || $is_editor
               || $exercise->results == 3 && $exercise->attemptsAllowed == $userAttempts
               || $exercise->results == 4 && $end_date < $cur_date;

$data['showScore'] = $showScore = $exercise->score == 1
            || $is_editor
            || $exercise->score == 3 && $exercise->attemptsAllowed == $userAttempts
            || $exercise->score == 4 && $end_date < $cur_date;

$data['questions'] = $exercise_user_record->questions();

view('modules.exercise.exercise_results', $data);
exit();