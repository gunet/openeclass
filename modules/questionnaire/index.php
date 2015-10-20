<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
$require_help = TRUE;
$helpTopic = 'Questionnaire';
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
        Session::Messages('Η απαντήσεις σας καταμετρήθηκαν με επιτυχία', 'alert-success');
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
                    Database::get()->query("UPDATE poll SET active = 0 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::Messages($langPollDeactivated, 'alert-success');
                    break;
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }

        // delete polls
        if (isset($_GET['delete']) and $_GET['delete'] == 'yes') {
            Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
                        (SELECT pqid FROM poll_question WHERE pid = ?d)", $pid);
            Database::get()->query("DELETE FROM `poll_to_specific` WHERE poll_id = ?d", $pid);
            Database::get()->query("DELETE FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
            Database::get()->query("DELETE FROM poll_question WHERE pid = ?d", $pid);
            Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);
            Session::Messages($langPollDeleted, 'alert-success');
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);       
        // delete poll results
        } elseif (isset($_GET['delete_results']) && $_GET['delete_results'] == 'yes') {
            Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);
            Session::Messages($langPollResultsDeleted, 'alert-success');
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        //clone poll
        } elseif (isset($_POST['clone_to_course_id'])) {
            $clone_course_id = $_POST['clone_to_course_id'];
            $my_courses = Database::get()->queryArray("SELECT course_id FROM course_user WHERE user_id = ?d AND status = 1", $uid);
            $ok = false;
            foreach ($my_courses as $row) {
                if ($row->course_id == $clone_course_id) {
                    $ok = true;
                    continue;
                }
            }
            if ($ok || $is_course_admin) {
                $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
                $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

                if ($clone_course_id == $course_id) $poll->name .= " ($langCopy2)";
                $poll_data = array(
                    $poll->creator_id, 
                    $clone_course_id, 
                    $poll->name, 
                    $poll->creation_date, 
                    $poll->start_date, 
                    $poll->end_date, 
                    $poll->description, 
                    $poll->end_message, 
                    $poll->anonymized,
                    $poll->assign_to_specific
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
                Session::Messages($langCopySuccess, 'alert-success');
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


/* * *************************************************************************************************
 * printPolls()
 * ************************************************************************************************** */

function printPolls() {
    global $tool_content, $course_id, $course_code,
    $langTitle, $langCancel,
    $langPollStart, $langPollEnd, $langPollNone, $is_editor, $langAnswers,
    $langEditChange, $langDelete, $langSurveyNotStarted,
    $langDeactivate, $langPollHasEnded, $langActivate,
    $langParticipate,  $langHasParticipated, $langSee,
    $langHasNotParticipated, $uid, $langConfirmDelete, $langPurgeExercises,
    $langPurgeExercises, $langConfirmPurgeExercises, $langCreateDuplicate, 
    $head_content, $langCreateDuplicateIn, $langCurrentCourse, $langUsage, $langNoAccessPrivilages;
    
    $my_courses = Database::get()->queryArray("SELECT a.course_id Course_id, b.title Title FROM course_user a, course b WHERE a.course_id = b.id AND a.course_id != ?d AND a.user_id = ?d AND a.status = 1", $course_id, $uid);
    $courses_options = "";
    foreach ($my_courses as $row) {
        $courses_options .= "'<option value=\"$row->Course_id\">".q($row->Title)."</option>'+";
    }    
    $head_content .= "
    <script>
        $(document).on('click', '.warnLink', function() {
            var pid = $(this).data('pid');
            bootbox.dialog({
                title: '$langCreateDuplicateIn',
                message: '<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"POST\" id=\"clone_form\">'+
                            '<select class=\"form-control\" id=\"course_id\" name=\"clone_to_course_id\">'+
                                '<option value=\"$course_id\">--- $langCurrentCourse ---</option>'+
                                $courses_options    
                            '</select>'+
                          '</form>',
                    buttons: {
                        success: {
                            label: '$langCreateDuplicate',
                            className: 'btn-success',
                            callback: function (d) {    
                                $('#clone_form').attr('action', '$_SERVER[SCRIPT_NAME]?pid=' + pid);
                                $('#clone_form').submit();  
                            }
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'btn-default'
                        }                        
                    }   
            });
        });
    </script>
    ";
    
    $poll_check = 0;
    $query = "SELECT * FROM poll WHERE course_id = ?d";
    $query_params[] = $course_id;
    //Bring only those assigned to the student
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
    
    $result = Database::get()->queryArray($query, $query_params);
        
    $num_rows = count($result);
    if ($num_rows > 0)
        ++$poll_check;
    if (!$poll_check) {
        $tool_content .= "\n    <div class='alert alert-warning'>" . $langPollNone . "</div><br>";
    } else {
        // Print active polls
        $tool_content .= "
                    <div class='table-repsonsive'>
		      <table class='table-default'>
		      <tr class='list-header'>
			<th><div align='left'>&nbsp;$langTitle</div></th>
			<th class='text-center'>$langPollStart</th>
			<th class='text-center'>$langPollEnd</th>";

        if ($is_editor) {
            $tool_content .= "<th class='text-center' width='16'>$langAnswers</th>";
        } else {
            $tool_content .= "<th class='text-center'>$langParticipate</th>";
        }
        $tool_content .= "<th class='text-center'>".icon('fa-cogs')."</th>";
        $tool_content .= "</tr>";
        $index_aa = 1;
        $k = 0;
        foreach ($result as $thepoll) {
            $visibility = $thepoll->active;

            if (($visibility) or ($is_editor)) {
                if ($visibility) {
                    $visibility_css = "";
                    $visibility_gif = "fa-eye";
                    $visibility_func = "deactivate";
                    $arrow_png = "arrow";
                    $k++;
                } else {
                    $visibility_css = " class=\"not_visible\"";
                    $visibility_gif = "fa-eye-slash";
                    $visibility_func = "activate";
                    $arrow_png = "arrow";
                    $k++;
                }
                $tool_content .= "<tr $visibility_css>";

                $temp_CurrentDate = date("Y-m-d H:i");
                $temp_StartDate = $thepoll->start_date;
                $temp_EndDate = $thepoll->end_date;
                $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
                $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
                $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
                $creator_id = $thepoll->creator_id;
                $theCreator = uid_to_name($creator_id);
                $pid = $thepoll->pid;
                $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
                // check if user has participated
                $has_participated = Database::get()->querySingle("SELECT COUNT(*) as counter FROM poll_user_record
                        WHERE uid = ?d AND pid = ?d", $uid, $pid)->counter;

                // check if poll has ended OR not strarted yet
                $poll_ended = 0;
                $poll_not_started = 0;
                if($temp_CurrentDate < $temp_StartDate) {
                    $poll_not_started = 1;
                } else if ($temp_CurrentDate >= $temp_EndDate) {
                    $poll_ended = 1;
                }
                
                if ($is_editor) {
                    $tool_content .= "
                        <td><a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>".q($thepoll->name)."</a>";
                } else {
                    $tool_content .= "
                        <td>";
                    if ($uid == 0 || $has_participated == 0 && $poll_ended == 0) {
                        $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>".q($thepoll->name)."</a>";
                    } else {
                        $tool_content .= q($thepoll->name);
                    }
                }
                $tool_content .= "                       
                        <td class='text-center'>" . nice_format(date("Y-m-d H:i", strtotime($thepoll->start_date)), true) . "</td>
                        <td class='text-center'>" . nice_format(date("Y-m-d H:i", strtotime($thepoll->end_date)), true) . "</td>";
                if ($is_editor) {
                    $tool_content .= "
                        <td class='text-center'>$total_participants</td>
                        <td class='text-center option-btn-cell'>" .action_button(array(
                            array(
                                'title' => $langEditChange,
                                'icon' => 'fa-edit',
                                'url' => "admin.php?course=$course_code&amp;pid=$pid"                              
                            ),
                            array(
                                'title' => $visibility?  $langDeactivate : $langActivate,
                                'icon' => $visibility ?  'fa-toggle-off' : 'fa-toggle-on',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;visibility=$visibility_func&amp;pid={$pid}"
                            ),
                            array(
                                'title' => $langUsage,
                                'level' => 'primary',
                                'icon' => 'fa-line-chart',
                                'url' => "pollresults.php?course=$course_code&pid=$pid",
                                'disabled' => $total_participants == 0
                            ),
                            array(
                                'title' => $langSee,
                                'icon' => 'fa-search',
                                'url' => "pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid"
                            ),
                            array(
                                'title' => $langCreateDuplicate,
                                'icon' => 'fa-copy',
                                'icon-class' => 'warnLink',
                                'icon-extra' => "data-pid='$pid'",
                                'url' => "#"
                            ),
                            array(
                                'title' => $langPurgeExercises,
                                'icon' => 'fa-eraser',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_results=yes&amp;pid=$pid",
                                'confirm' => $langConfirmPurgeExercises,
                                'show' => $total_participants > 0
                            ),                                        
                            array(
                                'title' => $langDelete,
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=yes&amp;pid=$pid",
                                'confirm' => $langConfirmDelete                               
                            )                                   
                        ))."</td></tr>";
                } else {
                    //!(course_status($course_id) == COURSE_OPEN && $uid ==0)
                    $tool_content .= "
                        <td class='text-center'>";
                    if ($poll_ended == 1) {
                        $tool_content .= $langPollHasEnded;
                    } else if ($poll_not_started == 1) {
                        $tool_content .= $langSurveyNotStarted;
                    } elseif ($has_participated > 0) {
                        $tool_content .= $uid ? $langHasParticipated : 'Οι συμμετοχές είναι ανοιχτές';
                    } else {
                        $tool_content .= $uid ? $langHasNotParticipated : 'Οι συμμετοχές είναι ανοιχτές';
                    }
                    $tool_content .= "</td>";
                    $line_chart_link = ($has_participated && $thepoll->show_results)? "<a href='pollresults.php?course=$course_code&pid=$pid'><span class='fa fa-line-chart'></span></a>" : "<span class='fa fa-line-chart' data-toggle='tooltip' title='$langNoAccessPrivilages'></span>" ;
                    $tool_content .= "<td class='text-center option-btn-cell'><div style='padding-top:7px;padding-bottom:7px;'>$line_chart_link</div></td></tr>";
//                    $tool_content .= "
//                        <td class='text-center option-btn-cell'>" .action_button(array(
//                            array(
//                                'title' => $langUsage,
//                                'icon' => 'fa-line-chart',
//                                'url' => "pollresults.php?course=$course_code&pid=$pid",
//                                'show' => $has_participated
//                            )         
//                        ))."</td></tr>";
                }
            }
            $index_aa ++;
        }
        $tool_content .= "</table></div>";
    }
}
