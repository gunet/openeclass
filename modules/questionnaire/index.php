<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

/**
 * @file index.php
 * @brief main script for the questionnaire tool
 */

$require_current_course = TRUE;
$require_user_registration = true;
$require_help = TRUE;
$helpTopic = 'questionnaire';
require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_QUESTIONNAIRE);
/* * *********************************** */

$toolName = $langQuestionnaire;


load_js('tools.js');
if (isset($_GET['verification_code'])) {
    $afftected_rows = Database::get()->query("UPDATE poll_user_record SET email_verification = 1, verification_code = NULL WHERE verification_code = ?s", $_GET['verification_code'])->affectedRows;
    if ($afftected_rows > 0) {
        Session::Messages("$langPollParticipationValid", 'alert-success');
    }
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
if ($is_editor) {
    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        $p = Database::get()->querySingle("SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
        if(!$p){
            redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
        }
        // activate / dectivate polls
        if (isset($_GET['visibility'])) {
            switch ($_GET['visibility']) {
                case 'activate':
                    Database::get()->query("UPDATE poll SET active = 1 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::Messages($langPollActivated, 'alert-success');
                    break;
                case 'deactivate':
                    if (!resource_belongs_to_progress_data(MODULE_ID_QUESTIONNAIRE, $pid)) {
                        Database::get()->query("UPDATE poll SET active = 0 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                        Session::Messages($langPollDeactivated, 'alert-success');
                    } else {
                        Session::Messages($langResourceBelongsToCert, 'alert-warning');
                    }
                    break;
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
        if (isset($_GET['access'])) {
            switch ($_GET['access']) {
                case 'public':
                    Database::get()->query("UPDATE poll SET public = 1 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::Messages($langPollUnlocked, 'alert-success');
                    break;
                case 'limited':
                    Database::get()->query("UPDATE poll SET public = 0 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::Messages($langPollLocked, 'alert-success');
                    break;
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
        // delete polls
        if (isset($_GET['delete']) and $_GET['delete'] == 'yes') {
            if (!resource_belongs_to_progress_data(MODULE_ID_QUESTIONNAIRE, $pid)) {
                $poll_title = Database::get()->querySingle("SELECT name FROM poll WHERE course_id = ?d", $course_id)->name;
                Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
                            (SELECT pqid FROM poll_question WHERE pid = ?d)", $pid);
                Database::get()->query("DELETE FROM `poll_to_specific` WHERE poll_id = ?d", $pid);
                $deleted_rows = Database::get()->query("DELETE FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                Database::get()->query("DELETE FROM poll_question WHERE pid = ?d", $pid);
                Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);
                if ($deleted_rows > 0) {
                    Log::record($course_id, MODULE_ID_QUESTIONNAIRE, LOG_DELETE, array('title' => $poll_title));
                }

                Session::Messages($langPollDeleted, 'alert-success');
                redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
            } else {
                Session::Messages($langResourceBelongsToCert, "alert-warning");
            }
        // delete poll results
        } elseif (isset($_GET['delete_results']) && $_GET['delete_results'] == 'yes') {
            Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);
            $poll_title = Database::get()->querySingle("SELECT name FROM poll WHERE course_id = ?d", $course_id)->name;
            Log::record($course_id, MODULE_ID_QUESTIONNAIRE, LOG_DELETE, array('title' => $poll_title, 'legend' => 'purge_results'));
            Session::Messages($langPollResultsDeleted, 'alert-success');
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        //clone poll
        } elseif (isset($_POST['clone_to_course_id'])) {
            $clone_course_id = $_POST['clone_to_course_id'];
            if ($is_admin) {
                $ok = true;
            } else {
                $ok = Database::get()->querySingle("SELECT course_id FROM course_user
                    WHERE user_id = ?d AND course_id = ?d AND
                          (status = ?d OR editor = 1)",
                    $uid, $clone_course_id, USER_TEACHER);
            }
            if ($ok) {
                $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
                $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

                if ($clone_course_id == $course_id) {
                    $poll->name .= " ($langCopy2)";
                } else {
                    $clone_course = Database::get()->querySingle('SELECT title, code FROM course WHERE id = ?d', $clone_course_id);
                }
                $poll_data = array(
                    $poll->creator_id,
                    $clone_course_id,
                    $poll->name,
                    $poll->creation_date,
                    $poll->start_date,
                    $poll->end_date,
                    purify($poll->description),
                    purify($poll->end_message),
                    $poll->anonymized,
                    $poll->assign_to_specific,
                    $poll->show_results,
                    $poll->multiple_submissions,
                    $poll->default_answer
                );
                $new_pid = Database::get()->query("INSERT INTO poll
                                    SET creator_id = ?d,
                                        course_id = ?d,
                                        name = ?s,
                                        creation_date = ?t,
                                        start_date = ?t,
                                        end_date = ?t,
                                        description = ?s,
                                        end_message = ?s,
                                        anonymized = ?d,
                                        assign_to_specific = ?d,
                                        show_results = ?d,
                                        multiple_submissions = ?d,
                                        default_answer = ?d,
                                        active = 1", $poll_data)->lastInsertID;
                if ($poll->assign_to_specific) {
                    Database::get()->query("INSERT INTO `poll_to_specific` (user_id, group_id, poll_id)
                                            SELECT user_id, group_id, ?d FROM `poll_to_specific`
                                            WHERE poll_id = ?d", $new_pid, $pid)->lastInsertID;
                }
                foreach ($questions as $question) {
                    $new_pqid = Database::get()->query("INSERT INTO poll_question
                                               SET pid = ?d,
                                                   question_text = ?s,
                                                   qtype = ?d, q_position = ?d, q_scale = ?d", $new_pid, $question->question_text, $question->qtype, $question->q_position, $question->q_scale)->lastInsertID;
                    $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d ORDER BY pqaid", $question->pqid);
                    foreach ($answers as $answer) {
                        Database::get()->query("INSERT INTO poll_question_answer
                                                SET pqid = ?d,
                                                    answer_text = ?s", $new_pqid, $answer->answer_text);
                    }
                }
                $message = $langCopySuccess;
                if (isset($clone_course)) {
                    $clone_code = q($clone_course->code);
                    $message .= "<br><a href='{$urlAppend}modules/questionnaire/?course=$clone_code'>$langShow</a>";
                } else {
                    $show_link = '';
                }
                Session::Messages($message, 'alert-success');
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
    }
    $tool_content .= action_bar(array(
            array('title' => $langCreatePoll,
                  'url' => "admin.php?course=$course_code&amp;newPoll=yes",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success')
            ));

}

printPolls();
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);


/**
 * @brief print polls
 */
function printPolls() {
    global $tool_content, $course_id, $course_code, $urlAppend,
        $langTitle, $langCancel, $langOpenParticipation,
        $langStart, $langPollEnd, $langPollNone, $is_editor, $langAnswers,
        $langEditChange, $langDelete, $langSurveyNotStarted, $langResourceAccessLock,
        $langDeactivate, $langHasExpired, $langActivate, $langResourceAccessUnlock,
        $langParticipate,  $langHasParticipated, $langSee,
        $langHasNotParticipated, $uid, $langConfirmDelete,
        $langPurgeExercises, $langConfirmPurgeExercises, $langCreateDuplicate,
        $langCreateDuplicateIn, $langCurrentCourse, $langUsage, $langDate,
        $langUserDuration;


    $poll_check = 0;
    $query = "SELECT * FROM poll WHERE course_id = ?d";
    $query_params[] = $course_id;
    // Bring only those assigned to the student
    if (!$is_editor) {
        $gids = user_group_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode(',',array_keys($gids));
        } else {
            $gids_sql_ready = "''";
        }
        $query .= " AND
                    (assign_to_specific = '0' OR assign_to_specific != '0' AND pid IN
                       (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d UNION SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                    )";
        $query_params[] = $uid;
    }
    $query .= " ORDER BY start_date DESC";

    $result = Database::get()->queryArray($query, $query_params);

    $num_rows = count($result);
    if ($num_rows > 0) {
        ++$poll_check;
    }
    if (!$poll_check) {
        $tool_content .= "<div class='alert alert-warning'>" . $langPollNone . "</div><br>";
    } else {
        // Print active polls
        $tool_content .= "<div class='row'><div class='col-md-12'>
                    <div class='table-responsive'>
              <table class='table-default'>
                <tr class='list-header'>
                    <th style='min-width: 55%;'><div align='left'>&nbsp;$langTitle</div></th>
                    <th class='text-center'>$langDate</th>";

        if ($is_editor) {
            $tool_content .= "<th class='text-center' width='16'>$langAnswers</th>";
        } else {
            $tool_content .= "<th class='text-center'>$langParticipate</th>";
        }
        $tool_content .= "<th class='text-center'>".icon('fa-cogs')."</th>";
        $tool_content .= "</tr>";
        $k = 0;
        foreach ($result as $thepoll) {
            if (!$is_editor && !resource_access($thepoll->active, $thepoll->public)) {
                continue;
            }
            $visibility = $thepoll->active;

            if (($visibility) or ($is_editor)) {
                if ($visibility) {
                    $visibility_css = "";
                    $visibility_func = "deactivate";
                } else {
                    $visibility_css = " class=\"not_visible\"";
                    $visibility_func = "activate";
                }
                $k++;
                $tool_content .= "<tr $visibility_css>";

                $temp_CurrentDate = date("Y-m-d H:i");
                $temp_StartDate = $thepoll->start_date;
                $temp_EndDate = $thepoll->end_date;
                $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
                $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
                $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
                $pid = $thepoll->pid;
                $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
                // check if user has participated
                $has_participated = Database::get()->querySingle("SELECT COUNT(*) as counter FROM poll_user_record
                        WHERE uid = ?d AND pid = ?d", $uid, $pid)->counter;

                // check if poll has ended OR not started yet
                $poll_ended = 0;
                $poll_not_started = 0;
                if($temp_CurrentDate < $temp_StartDate) {
                    $poll_not_started = 1;
                } else if ($temp_CurrentDate >= $temp_EndDate) {
                    $poll_ended = 1;
                }

                $tool_content .= "<td><div class='table_td'><div class='table_td_header clearfix'>";
                if ($is_editor) {
                    $lock_icon = "";
                    if (!$thepoll->public) {
                        $lock_icon = "&nbsp;&nbsp;&nbsp;<span class='fa fa-lock'></span>";
                    }
                    $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>".q($thepoll->name)."</a>$lock_icon";
                } else {
                    if  ($poll_ended == 1 || $poll_not_started == 1) { // poll out of date
                        $tool_content .= q($thepoll->name);
                    } else {
                        if ($uid == 0 || $has_participated == 0 || $thepoll->multiple_submissions) {
                            $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>" . q($thepoll->name) . "</a>";
                        } else {
                            $tool_content .= q($thepoll->name);
                        }
                    }
                }

                $tool_content .= "</div>
                                    <div class='table_td_body'>" . standard_text_escape($thepoll->description) . "</div>
                                    </div></td>";
                $tool_content .= "
                        <td class='text-center'>
                            <div style='padding-top: 7px;'><span class='text-success'>$langStart</span>: &nbsp;&nbsp;" . nice_format(date("d/m/Y H:i", strtotime($thepoll->start_date)), true) . "</div>
                            <div style='padding-top: 7px;'><span class='text-danger'>$langPollEnd</span>: &nbsp;&nbsp;" . nice_format(date("d/m/Y H:i", strtotime($thepoll->end_date)), true) . "</div>
                        </td>";

                if ($is_editor) {
                    $tool_content .= "
                    <td class='text-center'>$total_participants</td>
                    <td class='text-center option-btn-cell'>" .
                    action_button([
                        [ 'title' => $langEditChange,
                          'icon' => 'fa-edit',
                          'url' => "admin.php?course=$course_code&amp;pid=$pid" ],
                        [ 'title' => $visibility?  $langDeactivate : $langActivate,
                          'icon' => $visibility ?  'fa-toggle-off' : 'fa-toggle-on',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;visibility=$visibility_func&amp;pid={$pid}" ],
                        [ 'title' => $langUsage,
                          'level' => 'primary',
                          'icon' => 'fa-line-chart',
                          'url' => "pollresults.php?course=$course_code&amp;pid=$pid",
                          'disabled' => $total_participants == 0 ],
                        [ 'title' => $langSee,
                          'icon' => 'fa-search',
                          'url' => "pollparticipate.php?course=$course_code&amp;UseCase=1&amp;pid=$pid" ],
                        [ 'title' => $langUserDuration,
                          'icon' => 'fa-users',
                          'url' => "participation.php?course=$course_code&amp;pid=$pid",
                          'disabled' => $total_participants == 0,
                          'show' => !$thepoll->anonymized ],
                        [ 'title' => $thepoll->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($thepoll->public ? "access=limited" : "access=public")."&amp;pid=$pid",
                          'icon' => $thepoll->public ? 'fa-lock' : 'fa-unlock',
                          'show' => course_status($course_id) == COURSE_OPEN],
                        [ 'title' => $langCreateDuplicate,
                          'icon' => 'fa-copy',
                          'icon-class' => 'warnLink',
                          'icon-extra' => "data-pid='$pid'",
                          'show' => $thepoll->type == POLL_NORMAL ],
                        [ 'title' => $langPurgeExercises,
                          'icon' => 'fa-eraser',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_results=yes&amp;pid=$pid",
                          'confirm' => $langConfirmPurgeExercises,
                          'show' => $total_participants > 0 ],
                        [ 'title' => $langDelete,
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=yes&amp;pid=$pid",
                          'confirm' => $langConfirmDelete ],
                    ]) . "
                    </td></tr>";
                } else {
                    $tool_content .= "<td class='text-center'>";
                    if ($poll_not_started == 1) {
                        $tool_content .= $langSurveyNotStarted;
                    } elseif ($has_participated > 0) {
                        $tool_content .= $uid ? $langHasParticipated : $langOpenParticipation;
                    } else if ($poll_ended == 1) {
                        $tool_content .= $langHasExpired;
                    } else {
                        $tool_content .= $uid ? $langHasNotParticipated : $langOpenParticipation;
                    }
                    $tool_content .= "</td>";
                    $line_chart_link = ($has_participated && $thepoll->show_results && $thepoll->type==0)? "<a href='pollresults.php?course=$course_code&pid=$pid'><span class='fa fa-line-chart'></span></a>" : "&mdash;" ;
                    $tool_content .= "<td class='text-center option-btn-cell'>
                                        <div style='padding-top:7px;padding-bottom:7px;'>$line_chart_link</div>
                                      </td></tr>";
                }
            }
        }
        $tool_content .= "</table></div></div></div>

            <div class='modal fade' tabindex='-1' role='dialog' id='cloneModal'>
              <div class='modal-dialog' role='document'>
                <div class='modal-content'>
                  <form action='$_SERVER[SCRIPT_NAME]' method='POST' id='clone_form'>
                    <div class='modal-header'>
                      <button type='button' class='close' data-dismiss='modal' aria-label='$langCancel'><span aria-hidden='true'>&times;</span></button>
                      <h4 class='modal-title'>$langCreateDuplicateIn</h4>
                    </div>
                    <div class='modal-body'>
                        <div class='form-group'>
                          <select class='form-control' id='course_id' name='clone_to_course_id'>
                            <option value='$course_id' selected>--- $langCurrentCourse ---</option>
                          </select>
                        </div>
                    </div>
                    <div class='modal-footer'>
                      <button type='button' class='btn btn-default' data-dismiss='modal'>$langCancel</button>
                      <button type='submit' class='btn btn-success'>$langCreateDuplicate</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <script>
              $(function () {
                $(document).on('click', '.warnLink', function(e) {
                    var pid = $(this).data('pid');
                    $('#clone_form').attr('action', '" . js_escape($_SERVER['SCRIPT_NAME']) . "?pid=' + pid);
                    $('#cloneModal').modal('show').on('hide.bs.modal', function () {
                      if ($('#course_id').hasClass('select2-hidden-accessible')) {
                        $('#course_id').select2('destroy');
                      }
                    });
                    $('#course_id').select2({
                      width: '100%',
                      selectOnClose: true,
                      dropdownParent: $('#cloneModal'),
                      ajax: {
                        url: '" . js_escape($urlAppend . 'main/coursefeed.php') .  "',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                          return {
                            term: params.term,
                            page: params.page || 1
                          };
                        }
                      }
                    });
                    e.preventDefault();
                });
              });
            </script>";
        load_js('select2');
    }
}
