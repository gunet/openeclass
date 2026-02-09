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
 * @file index.php
 * @brief main exercise module script
 */

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
$require_current_course = TRUE;
$guest_allowed = TRUE;
$require_help = TRUE;
$helpTopic = 'exercises';

require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/SearchEngineFactory.php';

ModalBoxHelper::loadModalBox();
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_EXERCISE);

load_js('tools.js');
load_js('datatables');

$toolName = $langExercices;

//Unsetting the redirect cookie which is set in case of the exercise page unload event
//More info in exercise_submit.php comments
if (isset($_COOKIE['inExercise'])) {
    setcookie("inExercise", "", time() - 3600);
}

if ($is_editor) {
    // disable ordering for the action button column
    $columns = 'null, null, null, { orderable: false }';
} elseif ($uid) {
    $columns = 'null, null, null';
} else {
    $columns = 'null, null';
}
$data['columns'] = $columns;

// only for administrator
if ($is_course_reviewer) {
    if (isset($_GET['exerciseId'])) {
        $exerciseId = $_GET['exerciseId'];
    }
    // info about exercises assigned to users and groups
    if (isset($_GET['ex_info_assigned_to'])) {
        echo "<ul>";
        $q = Database::get()->queryArray("SELECT user_id, group_id FROM exercise_to_specific WHERE exercise_id = ?d", $_GET['eid']);
        foreach ($q as $user_data) {
            if (is_null($user_data->user_id)) { // assigned to group
                $group_name = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $user_data->group_id)->name;
                echo "<li>" .q($group_name) . "</li>";
            } else { // assigned to user
                echo "<li>" . q(uid_to_name($user_data->user_id)) . "</li>";
            }
        }
        echo "</ul>";
        exit;
    }


if ($is_editor) {
    if (!empty($_GET['choice'])) {
        // construction of Exercise
        $objExerciseTmp = new Exercise();
        if ($objExerciseTmp->read($exerciseId)) {
            $searchEngine = SearchEngineFactory::create();
            switch ($_GET['choice']) {
                case 'delete': // deletes an exercise
                    if (!resource_belongs_to_progress_data(MODULE_ID_EXERCISE, $exerciseId)) {
                        $objExerciseTmp->delete();
                        $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);
                        Session::flash('message', $langPurgeExerciseSuccess);
                        Session::flash('alert-class', 'alert-success');
                        redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    } else {
                        Session::flash('message', $langResourceBelongsToCert);
                        Session::flash('alert-class', 'alert-warning');
                    }
                    break;
                case 'purge': // purge exercise results
                    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
                    if (!resource_belongs_to_progress_data(MODULE_ID_EXERCISE, $exerciseId)) {
                        $objExerciseTmp->purge();
                        $objExerciseTmp->save();
                        Session::flash('message', $langPurgeExerciseResultsSuccess);
                        Session::flash('alert-class', 'alert-success');
                        redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    } else {
                        Session::flash('message', $langResourceBelongsToCert);
                        Session::flash('alert-class', 'alert-warning');
                    }
                    break;
                case 'enable':  // enables an exercise
                    $objExerciseTmp->enable();
                    $objExerciseTmp->save();
                    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                case 'disable': // disables an exercise
                    if (!resource_belongs_to_progress_data(MODULE_ID_EXERCISE, $exerciseId)) {
                        $objExerciseTmp->disable();
                        $objExerciseTmp->save();
                        $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);
                        redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    } else {
                        Session::flash('message', $langResourceBelongsToCert);
                        Session::flash('alert-class', 'alert-warning');
                    }
                    break;
                case 'public':  // make exercise public
                    $objExerciseTmp->makepublic();
                    $objExerciseTmp->save();
                    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                case 'limited':  // make exercise limited
                    $objExerciseTmp->makelimited();
                    $objExerciseTmp->save();
                    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                case 'clone':  // make exercise limited
                    $objExerciseTmp->duplicate();
                    Session::flash('message', $langCopySuccess);
                    Session::flash('alert-class', 'alert-success');
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                case 'distribution': //distribute answers
                    $objExerciseTmp->distribution($_GET['correction_output']);
                    Session::flash('message', $langDistributionSuccess);
                    Session::flash('alert-class', 'alert-success');
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                case 'cancelDistribution': //canceling distributed answers
                    $objExerciseTmp->cancelDistribution();
                    Session::flash('message', $langCancelDistributionSuccess);
                    Session::flash('alert-class', 'alert-success');
                    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
                    break;
                }
            }
            // destruction of Exercise
            unset($objExerciseTmp);
        }
    }
    $result = Database::get()->queryArray('SELECT * FROM exercise
                                            WHERE course_id = ?d
                                            ORDER BY start_date DESC', $course_id);
} else {
    $gids_sql_ready = "''";
    if ($uid > 0) {
        $gids = user_group_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode("','", array_keys($gids));
        }
    }
    $result = Database::get()->queryArray("SELECT * FROM exercise
        WHERE course_id = ?d AND active = 1 AND
              (assign_to_specific = '0' OR
               (assign_to_specific != '0' AND id IN (
                  SELECT exercise_id FROM exercise_to_specific WHERE user_id = ?d
                    UNION
                   SELECT exercise_id FROM exercise_to_specific WHERE group_id IN ('$gids_sql_ready'))))
        ORDER BY start_date DESC", $course_id, $uid);
}

$courses_options = "";
if ($is_editor) {
    $data['pending_exercises'] = Database::get()->queryArray("SELECT eid, title FROM exercise_user_record a "
            . "JOIN exercise b ON a.eid = b.id WHERE a.attempt_status = ?d AND b.course_id = ?d GROUP BY eid, title", ATTEMPT_PENDING, $course_id);
    $data['action_bar'] = action_bar(array(
        array('title' => $langNewEx,
            'url' => "admin.php?course=$course_code&amp;NewExercise=Yes",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ),
        array('title' => $langQuestionPool,
            'url' => "question_pool.php?course=$course_code",
            'icon' => 'fa-university'
        ),
        array('title' => $langQuestionCats,
            'url' => "question_categories.php?course=$course_code",
            'icon' => 'fa-cubes'
        )
    ), false);
    $my_courses = Database::get()->queryArray("SELECT a.course_id Course_id, b.title Title FROM course_user a, course b WHERE a.course_id = b.id AND a.course_id != ?d AND a.user_id = ?d AND a.status = 1", $course_id, $uid);
    foreach ($my_courses as $row) {
        $courses_options .= "'<option value=\"$row->Course_id\">".js_escape($row->Title)."</option>'+";
    }
}

$data['courses_options'] = $courses_options;
$data['previousResultsAllowed'] = $previousResultsAllowed = !(course_status($course_id) == COURSE_OPEN && $uid == 0);
$data['currentDate'] = $currentDate = new DateTime('NOW');
$data['result'] = $result;

add_units_navigation(TRUE);
view('modules.exercise.index', $data);

/**
 * @brief check if a user has participated in an exercise
 * @return bool
 */
function has_user_participate_in_exercise($eid)
{
    global $uid;

    $data = Database::get()->queryArray("SELECT * FROM exercise_user_record WHERE uid = ?d AND eid = ?d", $uid, $eid);
    if ($data) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief count exercise submissions
 * @param $eid
 * @return int
 */
function count_exercise_submissions($eid): int
{
    // logged in users
    $NumOfResults = Database::get()->queryArray("SELECT DISTINCT(uid) AS count
                                         FROM exercise_user_record
                                         WHERE eid = ?d
                                         AND uid > 0", $eid);
    // anonymous users
    $NumOfResultsAnonymous = Database::get()->querySingle("SELECT COUNT(uid) AS cnt1
                                         FROM exercise_user_record
                                         WHERE eid = ?d
                                         AND uid = 0", $eid);

    return count($NumOfResults) + $NumOfResultsAnonymous->cnt1;
}

/**
 * @brief check if exercise has imcomplete attempts
 * @param $eid
 * @param $uid
 * @param $continue_time_limit
 * @return null
 */
function hasExerciseIncompleteAttempts($eid, $uid, $continue_time_limit) {

    if ($continue_time_limit) {
        $q = Database::get()->querySingle("SELECT eurid, attempt
                                             FROM exercise_user_record
                                             WHERE eid = ?d AND uid = ?d AND
                                             attempt_status = ?d AND
                                             TIME_TO_SEC(TIMEDIFF(NOW(), record_end_date)) < ?d
                                             ORDER BY eurid DESC LIMIT 1",
            $eid, $uid, ATTEMPT_ACTIVE, 60 * $continue_time_limit);
        if ($q) {
            return $q->eurid;
        } else {
            return null;
        }
    } else {
        return null;
    }
}

/** @brief check if exercise has been paused by user
 * @param $eid
 * @param $uid
 * @return null
 */
function isExercisePaused($eid, $uid) {

    if ($uid) {
        $q = Database::get()->querySingle("SELECT eurid, attempt
                                             FROM exercise_user_record
                                             WHERE eid = ?d 
                                             AND uid = ?d 
                                             AND attempt_status = " . ATTEMPT_PAUSED . "",
                            $eid, $uid);
        if ($q) {
            return $q->eurid;
        } else {
            return null;
        }
    } else {
        return null;
    }
}

/**
 * @brief count user exercise attempts
 * @param $eid
 * @param $uid
 * @return mixed
 */
function exerciseUserAttempts($eid, $uid) {

    $currentAttempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record 
                                                     WHERE eid = ?d 
                                                     AND uid = ?d",
                                                $eid, $uid)->count;
    return $currentAttempt;
}

/**
 * @brief count user exercise last score
 * @param $eid
 * @param $uid
 * @return mixed
 */
function exerciseUserLastScore($eid, $uid) {

    $attempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record
                                         WHERE uid = ?d
                                         AND eid = ?d", $uid, $eid)->count;
    return $attempts;
}


/**
 * @brief check if the exercise has questions
 * @param $eid
 * @return bool
 */
function hasExerciseAnswers($eid): bool
{
    $q = Database::get()->querySingle("SELECT question_id
                                        FROM exercise_with_questions WHERE exercise_id = ?d LIMIT 1",
                                        $eid);
    if ($q) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief utility function to convert date string to DateTime object
 * @param $date
 * @return DateTime|null
 * @throws DateMalformedStringException
 */
function dateToObject($date): ?DateTime
{

    if (isset($date)) {
        return new DateTime($date);
    } else {
        return null;
    }
}

// ----------------------------------------
// TO BE FIXED
// ----------------------------------------
/* Exercise grading distribution
    if ($cf_result_data != 0) {

        $ids_array = array_column($cf_result_data, 'id');

        /**
         * @var array $TotalExercises2 Array of <stdClass> objects describing numbers of
         *                             unassigned answers per exercise id
         */

/*$TotalExercises2 = Database::get()->queryArray(
    "SELECT count(eurid) as answers_number, eid
     FROM exercise_user_record
     WHERE  eid
     IN (".implode(',', $ids_array).")
     AND attempt_status=2
     GROUP BY eid");


$courses_options1 = "<table id=\'my-grade-table\' class=\'table-default\'>"
                    . "<thead class=\'list-header\'>"
                    . "<tr><th>$langTeacher</th>"
                    . "<th>$langExerciseNumber</th>"
                    . "</tr></thead><tbody> " ;
    foreach ($my_courses1 as $row){
        $courses_options1 .= "<tr>"
            . "<td><div class=\'teacher-name\' data-id=\'$row->id\'>$row->givenname</div></td>"
            . "<td><input type=\'text\' class=\'grade-number form-control\' style=\'max-width:50px\'>"
            . "</input><strong> / '+results.current+'</strong></td>"
            . "</tr>";
    }

$courses_options1 .= "</tbody></table>";
$countResJs = json_encode($TotalExercises2);

$question_types = Database::get()->queryArray("SELECT DISTINCT eur.eurid "
        . "FROM exercise_question AS exq "
        . "JOIN exercise_answer_record AS ear ON ear.question_id = exq.id "
        . "JOIN exercise_user_record AS eur ON eur.eurid = ear.eurid "
        . "WHERE eur.eid IN (".implode(',', $ids_array).") AND ear.weight IS NULL "
        . "AND (exq.type = " . FREE_TEXT . " OR exq.type = ". ORAL . ")");
$questionsEid = json_encode($question_types, JSON_UNESCAPED_UNICODE);

$questions_table = "<table id=\'my-grade-table\' class=\'table-default\'><thead class=\'list-header\'><tr><th>$langTitle</th><th>$langChoice</th></tr></thead><tbody> " ;
foreach ($question_types as $row){
    $q_position = $row->q_position;
    $questions_table .= "<tr>"
        . "<td>$row->question</td>"
        . "<td><input type='radio' name='q_position' value='$q_position'><strong> $q_position </strong></td>"
        . "</tr>  ";
}
$questions_table .= "</tbody></table>";
if ($TotalExercises > 0) {  // to be fixed !!
    //  distribute exercise grading
    $head_content .= "<script type='text/javascript'>
    $(document).on('click', '.distribution', function() {
        var exerciseid = $(this).data('exerciseid');

        var results = {
            'list': $countResJs,
            'get': function(id) {
                return $.grep(results.list, function(element) { return element.eid == id; })
                        [0].answers_number; // return from the first array element
            }
        };
        results.current = results.get(exerciseid[1]);
        bootbox.dialog({
            title: '<strong>" . js_escape($langDistributeExercise) . "</strong>',
            message: '<h2 class=\"page-subtitle\">" . js_escape($langResults) . " : <strong>'+results.current+'</strong></h2><form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\" id=\"correction_form\"> $courses_options1 </form>',
                buttons: {
                    first: {
                        label : '" . js_escape($langDistribute) . "',
                        className : 'btn submitAdminBtn',
                        callback: function(d) {
                            var row = $('#my-grade-table tbody').find('tr');
                            var output = [];
                            var temp = 0;
                            $.each(row,function(){
                                        //in this scenario, this will be the reference of the row. If we have 5 rows in the table, each $(this) will point at one.
                                        var obj = {};
                                        //this way you get the value of the attribute data-id
                                        obj.teacher = $(this).find('.teacher-name').data('id');
                                        //this way you get the actual value
                                        obj.grade = $(this).find('.grade-number').val();
                                        if (obj.grade != '' ) {
                                            temp = temp + parseInt(obj.grade);
                                        }
                                        output.push(obj);
                                    }
                                );
                                if (temp > results.current)
                                {
                                    // Do not close modal
                                     alert('" . js_escape($langDistributeError) . "');
                                     return false;
                                }
                                else {
                                    $('#correction_form').attr('action', 'index.php?course=$course_code&choice=distribution&exerciseId=' + exerciseid[1]  + '&correction_output='+JSON.stringify(output));
                                    $('#correction_form').submit();
                                }
                            }
                        },
                    second: {
                            label : '" . js_escape($langCancelDistribute) . "',
                            className : 'btn deleteAdminBtn',
                            callback: function(d) {
                                    $('#correction_form').attr('action', 'index.php?course=$course_code&choice=cancelDistribution&exerciseId=' + exerciseid[1]);
                                    $('#correction_form').submit();
                                }
                            },
                    third: {
                        label : '" . js_escape($langCancel) . "',
                        className : 'cancelAdminBtn'
                    }
                }
            });
        });";
    $head_content .= "
    $(document).on('click', '.by_question', function() {
        var exerciseid = $(this).data('exerciseid');
        var results = {
            'list': $questionsEid,
            'get': function(id) {
                return $.grep(results.list, function(element) { return element.eid == id; })
            }
        };
        var res = results.get(exerciseid[1]);
        bootbox.dialog({
            title: '" . js_escape($landQuestionsInExercise) . "',
            message: '" . js_escape($langCorrectionMessage) . "',
                buttons: {
                    cancel: {
                        label: '" . js_escape($langCancel) . "',
                        className: 'cancelAdminBtn'
                    },
                    success: {
                        label: '" . js_escape($langGradeCorrect) . "',
                        className: 'submitAdminBtn',
                        callback: function (a) {
                            window.location.href = 'results_by_question.php?course=$course_code&exerciseId='+ exerciseid[0];
                        }
                    }
                }
        });
    });
    </script>";
}
}

} */
