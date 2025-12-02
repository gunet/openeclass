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

$require_current_course = true;

require_once '../../include/baseTheme.php';

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$toolName = $langResults;

$unit = $_GET['unit'] ?? null;
if ($unit) {
    $unit_name = Database::get()->querySingle('SELECT title FROM course_units WHERE course_id = ?d AND id = ?d',
        $course_id, $unit)->title;
    $navigation[] = ['url' => "index.php?course=$course_code&id=$unit", 'name' => q($unit_name)];
} else {
    $navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langExercices];
}

if (isset($_GET['exerciseId'])) {
    $exerciseId = getDirectReference($_GET['exerciseId']);
    $exerciseIdIndirect = $_GET['exerciseId'];
}

// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
    // construction of Exercise
    $objExercise = new Exercise();
    // if the specified exercise doesn't exist or is disabled
    if (!$objExercise->read($exerciseId) && (!$is_editor)) {
        $tool_content .= "<p>$langExerciseNotFound</p>";
        draw($tool_content, 2);
        exit();
    }
    if(!$objExercise->selectScore() && !$is_editor) {
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}


if ($is_editor && isset($_GET['purgeAttempID'])) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    $eurid = $_GET['purgeAttempID'];
    $objExercise->purgeAttempt($exerciseIdIndirect, $eurid);
    Session::flash('message',$langPurgeExerciseResultsSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/exercise/results.php?course=$course_code&exerciseId=" . getIndirectReference($_GET['exerciseId']));
}

if ($is_editor && isset($_GET['modifyAttempID'])) {
    $eurid = $_GET['modifyAttempID'];
    $status = $_GET['status'];
    $objExercise->modifyAttempt($eurid, $status);
    Session::flash('message',$langChangeAttemptStatus);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/exercise/results.php?course=$course_code&exerciseId=" . getIndirectReference($_GET['exerciseId']));
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$displayScore = $objExercise->selectScore();
$exerciseRange = $objExercise->selectRange();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid = ?d", $exerciseId, $uid)->count;
$cur_date = new DateTime("now");
$end_date = null;
if (!is_null($objExercise->selectEndDate())) {
    $end_date = new DateTime($objExercise->selectEndDate());
}
$showScore = $displayScore == 1
            || $is_course_reviewer
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;

    $action_bar = action_bar([
        [
            'title' => $langCheckGrades,
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label',
            'modal-class' => 'check-grades',
            'button-class' => 'btn-success',
            'show' => $is_editor
        ],
        [
            'title' => "$langResults ($langDumpUser)",
            'url' => "dump_results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect",
            'icon' => 'fa fa-download',
            'button-class' => 'btn-success',
            'show' => $is_course_reviewer
        ],
        [
            'title' => "$langPollFullResults ($langDumpUser)",
            'url' => "dump_results_full.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect",
            'icon' => 'fa fa-download',
            'button-class' => 'btn-success',
            'show' => $is_course_reviewer
        ]
    ]);
    $tool_content .= $action_bar;

$tool_content .= "<div class='col-12'><div class='card panelCard card-default px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h3>" . q_math($exerciseTitle) . "</h3></div>";
if ($exerciseDescription_temp) {
    $tool_content .= "<div class='card-body'>" . standard_text_escape($exerciseDescription_temp) . "</div>";
}
$tool_content .= "</div></div>";


$status = (isset($_GET['status'])) ? intval($_GET['status']) : '';
$tool_content .= "<div class='col-12 mt-4'><select class='form-select' style='margin:0 0 12px 0;' id='status_filtering' aria-label='$langCurrentStatus'>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect'>--- $langCurrentStatus ---</option>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect&amp;status=".ATTEMPT_ACTIVE."' ".(($status === ATTEMPT_ACTIVE)? 'selected' : '').">" . get_exercise_attempt_status_legend(ATTEMPT_ACTIVE) . "</option>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect&amp;status=".ATTEMPT_COMPLETED."' ".(($status === ATTEMPT_COMPLETED)? 'selected' : '').">" . get_exercise_attempt_status_legend(ATTEMPT_COMPLETED) . "</option>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect&amp;status=".ATTEMPT_PENDING."' ".(($status === ATTEMPT_PENDING)? 'selected' : '').">" . get_exercise_attempt_status_legend(ATTEMPT_PENDING) . "</option>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect&amp;status=".ATTEMPT_PAUSED."' ".(($status === ATTEMPT_PAUSED)? 'selected' : '').">" . get_exercise_attempt_status_legend(ATTEMPT_PAUSED) . "</option>
        <option value='results.php?course=$course_code&amp;exerciseId=$exerciseIdIndirect&amp;status=".ATTEMPT_CANCELED."' ".(($status === ATTEMPT_CANCELED)? 'selected' : '').">" . get_exercise_attempt_status_legend(ATTEMPT_CANCELED) . "</option>
        </select></div>";

if ($is_course_reviewer) {
    $result = Database::get()->queryArray("(SELECT DISTINCT uid, surname, givenname FROM `exercise_user_record`
                                                            JOIN user ON exercise_user_record.uid = user.id
                                                            WHERE eid = ?d)
                                                    UNION
                                                        (SELECT 0 as uid, '$langAnonymous' AS surname, '$langUser' AS givenname
                                                            FROM `exercise_user_record`
                                                            WHERE eid = ?d
                                                            AND uid = 0)
                                                        ORDER BY surname, givenname", $exerciseId, $exerciseId);
} else {
    $result[] = (object) array('uid' => $uid);
}
$extra_sql = ($status !== '' ) ? ' AND attempt_status = '.$status : '';

foreach ($result as $row) {
    $sid = $row->uid;
    // check if there is exercise assigned to teacher
    $testTheStudent = Database::get()->querySingle("SELECT assigned_to FROM `exercise_user_record`
                                     WHERE eid = ?d
                                     AND uid = ?d
                                     AND attempt_status = " . ATTEMPT_PENDING . "", $exerciseId, $sid);
    if ($testTheStudent) {
        if ($testTheStudent->assigned_to != $_SESSION['uid'] && isset($testTheStudent->assigned_to)) {
            continue;
        }
    }

    $result2 = Database::get()->queryArray("SELECT
                    DATE_FORMAT(a.record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date,
                    a.record_end_date,
                    IF (attempt_status = 1 OR b.time_constraint = 0,
                        TIME_TO_SEC(TIMEDIFF(a.record_end_date, a.record_start_date)),
                        b.time_constraint*60-a.secs_remaining) AS time_duration, b.passing_grade,
                    a.total_score, a.total_weighting, a.eurid, a.attempt_status, a.assigned_to,
                    MIN(exercise_answer_record.answer_record_id) AS answers_exist
                FROM exercise b
                    JOIN exercise_user_record a ON a.eid = b.id
                    LEFT JOIN exercise_answer_record ON a.eurid = exercise_answer_record.eurid
                WHERE a.uid = ?d AND a.eid = ?d $extra_sql
                GROUP BY a.eurid, record_start_date,
                    record_end_date, attempt_status,
                    time_constraint, secs_remaining,
                    total_score, total_weighting,
                    assigned_to
                ORDER BY record_start_date ASC", $sid, $exerciseId);

    if (count($result2) > 0) { // if users found
        $tool_content .= "<div class='col-12 mt-4'><div class='card panelCard card-default px-lg-4 py-lg-3'><div class='card-body'><div class='table-responsive mt-0'><table class='table-default'>";
        $tool_content .= "<thead><tr><td colspan='".($is_editor ? 5 : 4)."'>";
        if (!$sid) {
            $tool_content .= "$langNoGroupStudents";
        } else {
            $user_group = $studentam = '';
            $ug = user_groups($course_id, $sid, 'txt');
            if ($ug != '-') {
                $user_group = "<p class='form-label mt-4 mb-1'>$langGroup</p>$ug";
            }
            if (uid_to_am($sid) != '') {
                $studentam = "<p class='form-label mt-4 mb-1'>$langAmShort</p>" . uid_to_am($sid);
            }
            $tool_content .= "<p class='form-label mb-1'>$langUser</p> " . q(uid_to_name($sid,'surname')). " " . q(uid_to_name($sid, 'givenname')) . "
                            <div>$studentam<span>$user_group</span></div>";
        }
        $tool_content .= "</td>
                </tr>
                <tr class='list-header'>
                  <th>" . $langStart . "</th>
                  <th>" . $langExerciseDuration . "</th>
                  <th>" . $langScore . "</th>
                  <th>" . $langCurrentStatus. "</th>
                  ". ($is_editor ? "<th>" . icon('fa-gears'). "</th>" : "") ."
                </tr></thead>";

        $grade_icon = '';
        foreach ($result2 as $row2) {
            // check if there is exercise assigned to teacher
            if ($row2->assigned_to != $_SESSION['uid'] && isset($row2->assigned_to)) {
                continue;
            }

            $row_class = "";
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                if (!is_null($row2->passing_grade) && $row2->passing_grade > 0) {
                    if ($objExercise->canonicalize_exercise_score($row2->total_score, $row2->total_weighting) >= $objExercise->canonicalize_exercise_pass_grade($row2->passing_grade, $row2->total_weighting)) {
                        $grade_icon = "<span class='fa-solid fa-check ps-1' style='color: green;' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='$langSuccess'></span>";
                    } else {
                        $grade_icon = "<span class='fa-solid fa-times ps-1' style='color: red;' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='$langFailure'></span>";
                    }
                }// IF ATTEMPT COMPLETED
                $status = $langAttemptCompleted;
                if ($showScore) {
                    $answersCount = Database::get()->querySingle("SELECT count(*) AS answers_cnt FROM `exercise_answer_record` WHERE `eurid` = ?d", $row2->eurid)->answers_cnt;
                    if ($exerciseRange > 0) {
                        $total_score = $objExercise->canonicalize_exercise_score($row2->total_score, $row2->total_weighting);
                        $total_weighting = $exerciseRange;
                    } else {
                        $total_score = q($row2->total_score);
                        $total_weighting = q($row2->total_weighting);
                    }

                    if ($answersCount) {
                        if ($unit) {
                            $results_link = "<a href='view.php?course=$course_code&amp;unit=$unit&amp;res_type=exercise_results&amp;eurId=$row2->eurid'>" . $total_score . "/" . $total_weighting . "</a>";
                        } else {
                            $results_link = "<a href='exercise_result.php?course=$course_code&amp;eurId=$row2->eurid'>" . $total_score . "/" . $total_weighting . "</a>";
                        }
                    } else {
                        $results_link = $total_score . "/" . $total_weighting;
                    }
                } else {
                    switch ($displayScore) {
                        case 2:
                            $results_link = $langScoreNotDisp;
                            break;
                        case 3:
                            $results_link = $langScoreDispLastAttempt;
                            break;
                        case 4:
                            $results_link = $langScoreDispEndDate;
                            break;
                    }
                }
            } else if ($row2->attempt_status == ATTEMPT_PAUSED) {
                $grade_icon = '';
                $results_link = "-/-";
                $status = $langAttemptPaused;
            } else if ($row2->attempt_status == ATTEMPT_ACTIVE) {
                $grade_icon = '';
                $results_link = "-/-";
                $status = $langAttemptActive;
                $now = new DateTime('NOW');
                $estimatedEndTime = DateTime::createFromFormat('Y-m-d / H:i', $row2->record_start_date);
                // in an active exercise if a time constrain passes the exercise can safely be deleted
                // if not it can be deleted after a day
                if ($exerciseTimeConstraint) {
                    $estimatedEndTime->add(new DateInterval('PT' . $exerciseTimeConstraint . 'M'));
                } else {
                    $estimatedEndTime->add(new DateInterval('P1D'));
                }
                if ($now > $estimatedEndTime) {
                    $row_class = " class='warning' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='$langAttemptActiveButDeadMsg'";
                } else {
                    $row_class = " class='success' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='$langAttemptActiveMsg'";
                }
                // IF ATTEMPT PENDING OR CANCELED
            } else if ($row2->attempt_status == ATTEMPT_PENDING) {
                $grade_icon = '';
                $results_link = q($row2->total_score) . "/" . q($row2->total_weighting);
                $status = "<a href='exercise_result.php?course=$course_code&amp;eurId=$row2->eurid'>" . $langAttemptPending . "</a>";
            } else if ($row2->attempt_status == ATTEMPT_CANCELED) {
                $grade_icon = '';
                $results_link = "-/-";
                $status = $langAttemptCanceled;
                $row_class = " class='danger' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='$langAttemptCanceled''";
            }

            $tool_content .= "<tr$row_class><td>" . q($row2->record_start_date) . "</td>";
            if ($row2->time_duration == '00:00:00' || empty($row2->time_duration) || $row2->attempt_status == ATTEMPT_ACTIVE) { // for compatibility
                $tool_content .= "<td>$langNotRecorded</td>";
            } else {
                $tool_content .= "<td>" . format_time_duration($row2->time_duration) . "</td>";
            }
            $tool_content .= "<td>$results_link $grade_icon</td>
                              <td>$status</td>";
            if ($is_editor) {
                $allow_change_status = $row2->attempt_status == ATTEMPT_ACTIVE ||
                    $row2->attempt_status == ATTEMPT_PAUSED ||
                    ($row2->attempt_status == ATTEMPT_CANCELED && $row2->answers_exist);
                $tool_content .= "
                    <td class='option-btn-cell text-end'>" . action_button(array(
                        array(
                            'title' => $langDelete,
                            'url' => "results.php?course=$course_code&exerciseId=$exerciseId&purgeAttempID=$row2->eurid&" . generate_csrf_token_link_parameter(),
                            'icon' => "fa-xmark",
                            'confirm' => $langConfirmPurgeExercises,
                            'class' => 'delete'
                        ),
                        array(
                            'title' => "$langAuthChangeto $langAttemptCompleted",
                            'url' => "results.php?course=$course_code&exerciseId=$exerciseId&modifyAttempID=$row2->eurid&status=" . ATTEMPT_COMPLETED . "",
                            'icon' => "fa-solid fa-check",
                            'show' => $allow_change_status,
                            'class' => 'warning-delete',
                            'confirm' => $langConfirmModifyAttemptText,
                            'confirm_title' => $langConfirmModifyAttemptTitle,
                            'confirm_button' => $langModify
                        ),
                        array(
                            'title' => "$langAuthChangeto $langAttemptPaused",
                            'url' => "results.php?course=$course_code&exerciseId=$exerciseId&modifyAttempID=$row2->eurid&status=" . ATTEMPT_PAUSED . "",
                            'icon' => "fa-solid fa-hourglass-half",
                            'class' => 'warning-delete',
                            'show' => $row2->attempt_status == ATTEMPT_ACTIVE ||
                                ($row2->attempt_status == ATTEMPT_CANCELED && $row2->answers_exist),
                            'confirm' => $langConfirmModifyAttemptText,
                            'confirm_title' => $langConfirmModifyAttemptTitle,
                            'confirm_button' => $langModify
                        )
                    )) . "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div></div></div>";
    }
}

$tool_content .= "
    <script type='text/javascript'>
        $(function () {
          // bind change event to select
          $('#status_filtering').bind('change', function () {
              var url = $(this).val(); // get selected value
              if (url) { // require a URL
                  window.location = url; // redirect
              }
              return false;
          });
        });
    </script>";

if ($is_editor) {
    $tool_content .= "
    <script type='text/javascript'>
        $(function () {
              $('.check-grades').click(function (e) {
                var links = $('td a[href*=exercise_result]'),
                    count = links.length,
                    i = 0,
                    itemsToRegrade = [];
                e.preventDefault();
                var dialog = bootbox.dialog({
                  title: '" . js_escape($langCheckGradesConsistent) . "',
                  message: '<div class=\"progress\">' +
                      '<div class=\"progress-bar progress-bar-striped active\" ' +
                      'role=\"progressbar\" style=\"min-width: 4em; width: 0%;\">' +
                      '0 / ' + count + '</div>'
                  });
                var urls = $('td a[href*=exercise_result]').map(function (i, el) {
                    return el.href + '&check=true';
                }).get();
                var regradeDialog;
                var regradeCallback = function (item) {
                    if (item) {
                        $.post(item.url, { regrade: true },
                            function (data) {
                                i++;
                                regradeDialog.find('.progress-bar')
                                    .css({width: (i / count * 100) + '%'})
                                    .text(i + ' / ' + count);
                                regradeCallback(itemsToRegrade.shift());
                            });
                        } else {
                            window.location.replace('" . str_replace("'", "\\'", $_SERVER['REQUEST_URI']) . "');
                        }
                };
                var gradeCallback = function (url) {
                    if (url) {
                        $.get(url, function (data) {
                            if (data['result'] != 'ok') {
                                itemsToRegrade.push(data);
                            }
                            i++;
                            dialog.find('.progress-bar')
                                .css({width: (i / count * 100) + '%'})
                                .text(i + ' / ' + count);
                            gradeCallback(urls.shift());
                        });
                    } else {
                        dialog.modal('hide');
                        if (itemsToRegrade.length === 0) {
                            bootbox.alert({
                                title: '" . js_escape($langCheckGradesConsistent) . "',
                                message: '<p>" . js_escape($langCheckFinished . ' ' . $langRegradeNotNeeded) . "</p>',
                                backdrop: true
                            });
                        } else {
                            bootbox.confirm({
                                title: '" . js_escape($langCheckGradesConsistent) . "',
                                message: '<p>" . js_escape($langCheckFinished . ' ' . $langRegradeAttemptsList) . "</p><ul>' +
                                    itemsToRegrade.map(function (item) {
                                        return '<li><a href=\"' + item.url + '\" target=\"_blank\">' + item.title + '</a></li>';
                                    }).join('') + '</ul>',
                                buttons: {
                                    cancel: {
                                        label: '" . js_escape($langCancel) . "',
                                        className: 'cancelAdminBtn position-center'
                                    },
                                    confirm: {
                                        label: '" . js_escape($langRegradeAll) . "',
                                        className: 'submitAdminBtn position-center'
                                    }
                                },
                                callback: function (result) {
                                    if (result) {
                                        i = 0;
                                        count = itemsToRegrade.length;
                                        dialog.modal('hide');
                                        regradeDialog = bootbox.dialog({
                                          title: '" . js_escape($langRegradeAll) . "',
                                          message: '<div class=\"progress\">' +
                                              '<div class=\"progress-bar progress-bar-striped active\" ' +
                                              'role=\"progressbar\" style=\"min-width: 4em; width: 0%;\">' +
                                              '0 / ' + count + '</div>'
                                          });
                                        regradeCallback(itemsToRegrade.shift());
                                    }
                                }
                            });
                        }
                    }
                };
                gradeCallback(urls.shift());
              });
        });
    </script>";
}

draw($tool_content, 2, null, $head_content);
