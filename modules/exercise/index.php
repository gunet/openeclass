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

include '../../include/baseTheme.php';
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

load_js('datatables');

$pageName = $langExercices;

//Unsetting the redirect cookie which is set in case of exercise page unload event
//More info in exercise_submit.php comments
if (isset($_COOKIE['inExercise'])) {
    setcookie("inExercise", "", time() - 3600);
}

if ($is_editor) {
    // disable ordering for action button column
    $columns = 'null, null, null, { orderable: false }';
} elseif ($uid) {
    $columns = 'null, null, null';
} else {
    $columns = 'null, null';
}
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#ex').DataTable ({
                'columns': [ $columns ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'lengthMenu': [10, 20, 30, -1],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '$langAllOfThem'
                    },
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dt-search input').attr({
                  'class' : 'form-control input-sm ms-0 mb-3',
                  'placeholder' : '$langSearch...'
            });
            $('.dt-search label').attr('aria-label', '$langSearch');
            
            $(document).on('click', '.assigned_to', function(e) {
                  e.preventDefault();
                  var eid = $(this).data('eid');
                  url = '$urlAppend' + 'modules/exercise/index.php?ex_info_assigned_to=true&eid=' + eid;                  
                  $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title : '$langWorkAssignTo',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                success: {
                                    label: '$langClose',
                                    className: 'btn-success',
                                }
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                  });
              });
        });
        </script>";

// only for administrator
if ($is_course_reviewer) {
    load_js('tools.js');

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
                echo "<li>$group_name</li>";
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
    $qnum = Database::get()->querySingle("SELECT COUNT(*) as count FROM exercise WHERE course_id = ?d", $course_id)->count;
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
    $qnum = Database::get()->querySingle("SELECT COUNT(*) as count FROM exercise WHERE course_id = ?d AND active = 1", $course_id)->count;
}

$num_of_ex = $qnum; //Getting number of all active exercises of the course
$nbrExercises = count($result); //Getting number of limited (offset and limit) exercises of the course (active and inactive)
if ($is_editor) {
    $pending_exercises = Database::get()->queryArray("SELECT eid, title FROM exercise_user_record a "
            . "JOIN exercise b ON a.eid = b.id WHERE a.attempt_status = ?d AND b.course_id = ?d GROUP BY eid, title", ATTEMPT_PENDING, $course_id);
    if (count($pending_exercises) > 0) {
        $tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langPendingExercise:";
        $tool_content .= "<ul style='margin-top: 10px;'>";
        foreach ($pending_exercises as $row) {
            $tool_content .= "<li>" . q($row->title) . " (<a class='Primary-400-cl' href='results.php?course=$course_code&exerciseId=".getIndirectReference($row->eid)."&status=2'>$langViewShow</a>)</li>";
        }
        $tool_content .= "</ul>";
        $tool_content .= "</span></div>";
    }

    $action_bar = action_bar(array(
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
    $tool_content .= $action_bar;

}

if (!$nbrExercises) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoExercises</span></div></div>";
    //For Correction Script
    $cf_result_data = 0;
} else {
    $tool_content .= "<div class='table-responsive'><table id='ex' class='table-default'><thead><tr class='list-header'>";

    // shows the title bar only for the administrator
    if ($is_editor) {
        $tool_content .= "
                <th>$langExerciseName</th>
                <th>$langInfoExercise</th>
                <th>$langResults</th>
                <th aria-label='$langSettingSelect'></th>
              </tr>";
    } else { // student view
        load_js('tools.js');
        enable_password_bootbox();
        $previousResultsAllowed = !(course_status($course_id) == COURSE_OPEN && $uid ==0);
        $resultsHeader = $previousResultsAllowed ? "<th>$langResults</th>" : "";
        $tool_content .= "
                <th>$langExerciseName</th>
                <th>$langInfoExercise</th>
                $resultsHeader
              </tr>";
    }
    $tool_content .= "</thead><tbody>";

    // For correction Form script
    $cf_result_data = [];
    // display exercise list
    $currentDate = new DateTime('NOW');
    foreach ($result as $row) {
        $temp_StartDate = isset($row->start_date) ? new DateTime($row->start_date) : null;
        $temp_EndDate = isset($row->end_date) ? new DateTime($row->end_date) : null;
        $cf_result_data[] = ['id' => $row->id];
        $row->description = standard_text_escape($row->description);
        $exclamation_icon = $exam_icon = $lock_icon = '';
        $tr_class = $link_class = '';
        $answer_exists = Database::get()->querySingle('SELECT question_id
            FROM exercise_with_questions WHERE exercise_id = ?d LIMIT 1',
            $row->id);
        if (!$answer_exists and !$is_editor) {
            continue;
        }
        if (!$answer_exists or isset($row->password_lock) or isset($row->ip_lock)) {
            $lock_description = "<ul>";
            if ($row->password_lock) {
                $lock_description .= "<li>$langPassCode</li>";
                $link_class = $is_editor? '': 'password_protected';
            }
            if ($row->ip_lock) {
                $lock_description .= "<li>$langIPUnlock</li>";
            }
            if (!$answer_exists) {
                $lock_description .= "<li>$langNoQuestion</li>";
                $tr_class = 'not_visible';
            }
            $lock_description .= "</ul>";
            $exclamation_icon = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-html='true' data-bs-title='$lock_description'></span>";
        }
        if (!$row->public) {
            $lock_icon = "&nbsp;" . icon('fa-lock', $langNonPublicFile);
        }
        if (!$row->active) {
            $tr_class = 'not_visible';
        }
        if ($tr_class) {
            $tool_content .= "<tr class='$tr_class'>";
        } else {
            $tool_content .= '<tr>';
        }
        // prof only
        if ($is_course_reviewer) {
            if (!empty($row->description)) {
                $descr = "<br>$row->description";
            } else {
                $descr = '';
            }

            if ($row->assign_to_specific == 1) {
                $assign_to_users_message = "<a class='assigned_to' data-eid='$row->id'><small class='help-block link-color'>$langWorkAssignTo: $langWorkToUser</small></a>";
            } else if ($row->assign_to_specific == 2) {
                $assign_to_users_message = "<a class='assigned_to' data-eid='$row->id'><small class='help-block link-color'>$langWorkAssignTo: $langWorkToGroup</small></a>";
            } else {
                $assign_to_users_message = '';
            }

            if (isset($row->start_date)) {
                $sort_date = date("Y-m-d H:i", strtotime($row->start_date));
            } else {
                $sort_date = '';
            }
            if ($temp_EndDate and $temp_EndDate < $currentDate) { // exercise has expired
                $exclamation_icon .= "&nbsp;&nbsp;<span class='text-danger'>($langHasExpiredS)</span>";
            }
            if ($row->is_exam == 1) {
                $exam_icon .= "&nbsp;&nbsp;" . icon('fa-solid fa-chalkboard-user', $langExam);
            }
            $tool_content .= "<td><div class='line-height-default'><a href='admin.php?course=$course_code&amp;exerciseId={$row->id}&amp;preview=1'>" . q($row->title) . "</a>
                        $lock_icon$exclamation_icon$exam_icon </div> $descr
                        $assign_to_users_message
                        </td>";
            $tool_content .= "<td data-sort='$sort_date'><small>";
            if (isset($row->start_date)) {
                $tool_content .= "<div class='Success-200-cl'>$langStart: " . format_locale_date(strtotime($row->start_date), 'short') . "</div>";
            }
            if (isset($row->end_date)) {
                $tool_content .= "<div class='Accent-200-cl'>$langFinish: " . format_locale_date(strtotime($row->end_date), 'short') . "</div>";
            }

            if ($row->time_constraint > 0) {
                $tool_content .= "<div>$langDuration: {$row->time_constraint} $langExerciseConstrainUnit</div>";
            }
            // how many attempts we have.
            if ($row->attempts_allowed > 0) {
                $tool_content .= "<div>$langAttempts: $row->attempts_allowed</div>";
            }
            // is temp save enabled?
            if ($row->temp_save == 1) {
                $tool_content .= "<div>$langTemporarySave: <span class='Success-200-cl'>$langYes</span></div>";
            }
            $tool_content .= "</small></td>";

            $eid = getIndirectReference($row->id);
            // logged in users
            $NumOfResults = Database::get()->queryArray("SELECT DISTINCT(uid) AS count
                                                            FROM exercise_user_record
                                                            WHERE eid = ?d
                                                            AND uid > 0", $row->id);

            // anonymous users
            $NumOfResultsAnonymous = Database::get()->querySingle("SELECT COUNT(uid) AS cnt1
                                                    FROM exercise_user_record
                                                    WHERE eid = ?d
                                                    AND uid = 0", $row->id);

            $countNumOfResults = count($NumOfResults) + $NumOfResultsAnonymous->cnt1;

            if ($countNumOfResults > 0) {
                $submissionCount = ($countNumOfResults == 1 ?
                    "1 $langExercisesSubmission":
                    "$countNumOfResults $langExercisesSubmissions");
                $tool_content .= "<td>"
                        . "<div>
                                <a href='results.php?course=$course_code&amp;exerciseId=$eid'>$langViewShow</a>
                            </div>
                            <div>
                                <span class='badge Success-200-bg mt-2'>
                                        $submissionCount
                                </span>
                            </div>"
                        . "</td>";
            } else {
                $tool_content .= "<td>  &mdash; </td>";
            }
            $TotalExercises = Database::get()->queryArray("SELECT eurid FROM exercise_user_record WHERE eid = ?d AND attempt_status= " . ATTEMPT_PENDING . "", $row->id);
            $counter1 = count($TotalExercises);
            $langModify_temp = htmlspecialchars($langModify);
            $langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
            $langDelete_temp = htmlspecialchars($langDelete);
            if ($is_editor) {
                $tool_content .= "<td class='text-end'>".action_button(array(
                        array('title' => $langEditChange,
                              'url' => "admin.php?course=$course_code&amp;exerciseId=$row->id",
                              'icon' => 'fa-edit'),
                        array('title' => $langCorrectByQuestion,
                              'icon-class' => 'by_question',
                              'icon-extra' => "data-exerciseid= [\"$eid\",\"$row->id\"]",
                              'url' => "#",
                              'icon' => 'fa-pencil',
                              'show' => $counter1),
                        array('title' => $langDistributeExercise,
                              'icon-class' => 'distribution',
                              'icon-extra' => "data-exerciseid= [\"$eid\",\"$row->id\"]",
                              'url' => "#",
                              'icon' => 'fa-exchange',
                              'show' => $counter1),
                        array('title' => $row->active ?  $langViewHide : $langViewShow,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($row->active ? "choice=disable" : "choice=enable")."&amp;exerciseId=" . $row->id,
                              'icon' => $row->active ? 'fa-eye-slash' : 'fa-eye' ),
                        array('title' => $row->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($row->public ? "choice=limited" : "choice=public")."&amp;exerciseId=$row->id",
                              'icon' => $row->public ? 'fa-lock' : 'fa-unlock',
                              'show' => course_status($course_id) == COURSE_OPEN),
                        array('title' => $langUsage,
                              'url' => "exercise_stats.php?course=$course_code&amp;exerciseId=$row->id",
                              'icon' => 'fa-line-chart'),
                        array('title' => $langWorkUserGroupNoSubmission,
                              'url' => "users_no_submission.php?course=$course_code&amp;exerciseId=$row->id",
                              'icon' => 'fa-minus-square'),
                        array('title' => $langCreateDuplicate,
                              'icon-class' => 'warnLink',
                              'icon-extra' => "data-exerciseid='$row->id'",
                              'url' => "#",
                              'icon' => 'fa-copy'),
                        array('title' => $langPurgeExerciseResults,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=purge&amp;exerciseId=$row->id&" . generate_csrf_token_link_parameter(),
                              'icon' => 'fa-eraser',
                              'confirm' => $langConfirmPurgeExerciseResults),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=delete&amp;exerciseId=$row->id",
                              'icon' => 'fa-xmark',
                              'class' => 'delete',
                              'confirm' => $langConfirmPurgeExercise)
                        ))."</td></tr>";
            }

        // student only
        } else {
            if (!resource_access($row->active, $row->public)) {
                continue;
            }

            if (($currentDate >= $temp_StartDate) && (!isset($temp_EndDate) || isset($temp_EndDate) && $currentDate <= $temp_EndDate)) {

                $incomplete_attempt = $paused_exercises = null;
                if ($uid) {
                    $paused_exercises = Database::get()->querySingle("SELECT eurid, attempt
                                    FROM exercise_user_record
                                    WHERE eid = ?d AND uid = ?d AND
                                          attempt_status = ?d",
                                    $row->id, $uid, ATTEMPT_PAUSED);
                    if ($row->continue_time_limit) {
                        $incomplete_attempt = Database::get()->querySingle("SELECT eurid, attempt
                                        FROM exercise_user_record
                                        WHERE eid = ?d AND uid = ?d AND
                                              attempt_status = ?d AND
                                              TIME_TO_SEC(TIMEDIFF(NOW(), record_end_date)) < ?d
                                        ORDER BY eurid DESC LIMIT 1",
                            $row->id, $uid, ATTEMPT_ACTIVE, 60 * $row->continue_time_limit);
                    }
                }
                if ($incomplete_attempt) {
                    $tool_content .= "<td><a class='ex_settings active_exercise $link_class' href='exercise_submit.php?course=$course_code&amp;exerciseId=$row->id&amp;eurId=$incomplete_attempt->eurid'>" . q($row->title) . "</a>"
                            . "&nbsp;&nbsp;(<span style='color:darkgrey'>$langAttemptActive</span>)";
                } elseif ($paused_exercises) {
                    $tool_content .= "<td><a class='ex_settings paused_exercise $link_class' href='exercise_submit.php?course=$course_code&amp;exerciseId=$row->id&amp;eurId=$paused_exercises->eurid'>" . q($row->title) . "</a>"
                            . "&nbsp;&nbsp;(<span style='color:darkgrey'>$langAttemptPausedS</span>)";
                } else {
                    $tool_content .= "<td><div class='line-height-default'><a class='ex_settings $link_class' href='exercise_submit.php?course=$course_code&amp;exerciseId=$row->id'>" . q($row->title) . "</a>$lock_icon$exclamation_icon";
                }

            } elseif ($currentDate <= $temp_StartDate) { // exercise has not yet started
                $tool_content .= "<td class='not_visible'>" . q($row->title) . "$lock_icon&nbsp;&nbsp;";
            } else { // exercise has expired
                $tool_content .= "<td>" . q($row->title) . "$lock_icon&nbsp;&nbsp;(<span class='asterisk Accent-200-cl'>$langHasExpiredS</span>)";
            }
            if (has_user_participate_in_exercise($row->id)) {
                $tool_content .= "&nbsp; <span class='fa-solid fa-check' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='$langHasParticipated'></span>";
            }

            $tool_content .= "</div>" . $row->description . "</td>";
            if (isset($row->start_date)) {
                $sort_date = date("Y-m-d H:i", strtotime($row->start_date));
            } else {
                $sort_date = '';
            }
            $tool_content .= "<td data-sort='$sort_date'><small>";
            if (isset($row->start_date)) {
                $tool_content .= "<div class='Success-200-cl'>$langStart: " . format_locale_date(strtotime($row->start_date), 'short') . "</div>";
            }
            if (isset($row->end_date)) {
                $tool_content .= "<div class='Accent-200-cl'>$langFinish: " . format_locale_date(strtotime($row->end_date), 'short') . "</div>";
            }

            if ($row->time_constraint > 0) {
                $tool_content .= "<div>$langDuration: {$row->time_constraint} $langExerciseConstrainUnit</div>";
            }
            // how many attempts we have.
            $currentAttempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid = ?d", $row->id, $uid)->count;
            if ($row->attempts_allowed > 0) {
                $tool_content .= "<div>$langAttempts: $currentAttempt/$row->attempts_allowed</div>";
            }
            // is temp save enabled?
            if ($row->temp_save == 1) {
                $tool_content .= "<div>$langTemporarySave: <span class='Success-200-cl'>$langYes</span></div>";
            }
            $tool_content .= "</small></td>";
            if ($previousResultsAllowed) {
                if ($row->score) {
                    // user last exercise score
                    $attempts = Database::get()->querySingle("SELECT COUNT(*) AS count
                                                FROM exercise_user_record WHERE uid = ?d
                                                AND eid = ?d", $uid, $row->id)->count;
                    if ($attempts > 0) {
                        $eid = getIndirectReference($row->id);
                        $tool_content .= "<td><a href='results.php?course=$course_code&amp;exerciseId=$eid'>$langViewShow</a></td>";
                    } else {
                        $tool_content .= "<td>&dash;</td>";
                    }
                    $tool_content .= "</tr>";
                } else {
                    $tool_content .= "<td>$langNotAvailable</td>";
                }
            }
        }
    } // end while()
    $tool_content .= "</tbody></table></div>";
}
add_units_navigation(TRUE);

if ($is_editor) {
    $my_courses1 = Database::get()->queryArray("SELECT givenname, id FROM user u "
                                                . "JOIN course_user c ON u.id = c.user_id "
                                                . "WHERE c.status = " . USER_TEACHER . " "
                                                . "AND c.course_id = ?d", $course_id);
    if ($cf_result_data != 0) {

        $ids_array = array_column($cf_result_data, 'id');

        /**
         * @var array $TotalExercises2 Array of <stdClass> objects describing numbers of
         *                             unassigned answers per exercise id
         */

        $TotalExercises2 = Database::get()->queryArray(
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

        $question_types = Database::get()->queryArray("SELECT exq.id, exq.question, ear.q_position, eur.eid, eur.eurid as eurid "
                . "FROM exercise_question AS exq "
                . "JOIN exercise_answer_record AS ear ON ear.question_id = exq.id "
                . "JOIN exercise_user_record AS eur ON eur.eurid = ear.eurid "
                . "WHERE eur.eid IN (".implode(',', $ids_array).") AND ear.weight IS NULL "
                . "AND exq.type = " . FREE_TEXT . " OR exq.type = ". ORAL. " "
                . "GROUP BY exq.id, eur.eid, eur.eurid, ear.q_position, exq.question");
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

        if ($counter1 > 0) {
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
                            [0].eurid; // return from the first array element
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

    $my_courses = Database::get()->queryArray("SELECT a.course_id Course_id, b.title Title FROM course_user a, course b WHERE a.course_id = b.id AND a.course_id != ?d AND a.user_id = ?d AND a.status = 1", $course_id, $uid);
    $courses_options = "";
    foreach ($my_courses as $row) {
        $courses_options .= "'<option value=\"$row->Course_id\">".js_escape($row->Title)."</option>'+";
    }



    $head_content .= "<script type='text/javascript'>
        $(document).on('click', '.warnLink', function() {
            var exerciseid = $(this).data('exerciseid');

           

            bootbox.dialog({
                closeButton: false,
                title: '<div class=\"icon-modal-default\"><i class=\"fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl\"></i></div><div class=\"modal-title-default text-center mb-0\">" . js_escape($langCreateDuplicateIn) . "</div>',
                message: '<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\" id=\"clone_form\">'+
                            '<select class=\"form-select\" id=\"course_id\" name=\"clone_to_course_id\">'+
                                '<option value=\"$course_id\">--- " . js_escape($langCurrentCourse) . " ---</option>'+
                                $courses_options
                            '</select>'+
                          '</form>',
                    buttons: {
                        cancel: {
                            label: '" . js_escape($langCancel) . "',
                            className: 'cancelAdminBtn position-center'
                        },
                        success: {
                            label: '" . js_escape($langCreateDuplicate) . "',
                            className: 'submitAdminBtn position-center',
                            callback: function (d) {
                                $('#clone_form').attr('action', 'index.php?course=$course_code&choice=clone&exerciseId=' + exerciseid);
                                $('#clone_form').submit();
                            }
                        }
                    }
            });
        });
        </script>";
}
draw($tool_content, 2, null, $head_content);



/**
 * @brief check if user has participate in exercise
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
